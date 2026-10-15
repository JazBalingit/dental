<?php

namespace App\Services;

use App\Exceptions\BackupException;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Throwable;
use ZipArchive;

/**
 * Configuration → Backup & Restore (super admin only).
 *
 * A backup is one ZIP:
 *   manifest.json        — when/who/what, plus the migration list ("system version")
 *   tables/<name>.jsonl  — one JSON row per line; binary values (appointment
 *                          photos) are wrapped as {"__b64": "..."}
 *   files/images/...     — admin-uploaded images under public/images
 *
 * Saved copies live in storage/app/private/backups — never web-reachable,
 * since they hold account and patient data. On Railway that folder is wiped
 * on redeploy, which is why the page pushes "Save to computer".
 */
class BackupService
{
    public const FORMAT = 1;
    public const APP_TAG = 'puspus-dental-backup';

    /** Framework plumbing — never backed up, never wiped by a restore. */
    protected const SKIP_TABLES = [
        'migrations', 'sessions', 'cache', 'cache_locks',
        'jobs', 'job_batches', 'failed_jobs', 'password_reset_tokens',
    ];

    /** Rows per INSERT, and a byte cap so photo rows stay under max_allowed_packet. */
    protected const BATCH_ROWS = 200;
    protected const BATCH_BYTES = 4 * 1024 * 1024;

    public const TYPE_LABELS = [
        'manual' => 'Manual backup',
        'pre_restore' => 'Automatic (before restore)',
    ];

    public function directory(): string
    {
        $dir = storage_path('app/private/backups');
        if (!is_dir($dir)) {
            mkdir($dir, 0775, true);
        }

        return $dir;
    }

    /** Only names this service generated — blocks path tricks on download/delete/restore. */
    public function isValidName(string $name): bool
    {
        return (bool) preg_match('/^backup-\d{8}-\d{6}-[a-z_]+-[a-z0-9]{6}\.zip$/', $name);
    }

    public function path(string $name): string
    {
        if (!$this->isValidName($name) || !is_file($this->directory() . '/' . $name)) {
            throw new BackupException('That backup copy no longer exists.');
        }

        return $this->directory() . '/' . $name;
    }

    /** Saved copies, newest first, each with its manifest summary. */
    public function list(): array
    {
        $copies = [];
        foreach (glob($this->directory() . '/backup-*.zip') ?: [] as $file) {
            $manifest = $this->readManifest($file);
            if (!$manifest) {
                continue;
            }
            $copies[] = [
                'name' => basename($file),
                'size' => filesize($file),
                'created_at' => Carbon::parse($manifest['created_at'])->timezone(config('app.timezone')),
                'type' => $manifest['type'] ?? 'manual',
                'created_by' => $manifest['created_by'] ?? null,
                'table_count' => count($manifest['tables'] ?? []),
                'file_count' => count($manifest['files'] ?? []),
            ];
        }

        usort($copies, fn ($a, $b) => $b['created_at'] <=> $a['created_at']);

        return $copies;
    }

    /** Creates a backup ZIP and returns its file name. */
    public function create(string $type, ?string $createdBy): string
    {
        return $this->withLock(fn () => $this->createUnlocked($type, $createdBy));
    }

    /**
     * Replaces every record (and restores the uploaded images) from a backup
     * ZIP. A safety copy of the current data is saved first. Returns the
     * backup's manifest.
     */
    public function restore(string $zipPath, ?string $restoredBy): array
    {
        return $this->withLock(function () use ($zipPath, $restoredBy) {
            $zip = new ZipArchive();
            if ($zip->open($zipPath) !== true) {
                throw new BackupException('That file is not a valid backup ZIP.');
            }

            try {
                $manifest = $this->validate($zip);

                // Safety net: whatever is live right now becomes a saved copy.
                $this->createUnlocked('pre_restore', $restoredBy);

                $this->restoreTables($zip, $manifest);
                $this->restoreFiles($zip, $manifest);
            } finally {
                $zip->close();
            }

            return $manifest;
        });
    }

    public function delete(string $name): void
    {
        @unlink($this->path($name));
    }

    // ------------------------------------------------------------------

    protected function withLock(callable $work)
    {
        $lock = Cache::lock('system-backup-restore', 900);
        if (!$lock->get()) {
            throw new BackupException('A backup or restore is already running. Please wait a moment and try again.');
        }

        @set_time_limit(600);

        try {
            return $work();
        } finally {
            $lock->release();
        }
    }

    protected function createUnlocked(string $type, ?string $createdBy): string
    {
        $type = array_key_exists($type, self::TYPE_LABELS) ? $type : 'manual';
        $now = now();
        $name = 'backup-' . $now->format('Ymd-His') . '-' . $type . '-' . Str::lower(Str::random(6)) . '.zip';
        $final = $this->directory() . '/' . $name;
        $tmp = $final . '.part';

        $zip = new ZipArchive();
        if ($zip->open($tmp, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new BackupException('Could not create the backup file on the server.');
        }

        $tempFiles = [];
        try {
            $tables = [];
            foreach ($this->tables() as $table) {
                $tempFile = tempnam(sys_get_temp_dir(), 'bk');
                $tempFiles[] = $tempFile;
                $tables[$table] = $this->dumpTable($table, $tempFile);
                $zip->addFile($tempFile, 'tables/' . $table . '.jsonl');
            }

            $files = [];
            foreach ($this->uploadedFiles() as $relative) {
                $zip->addFile(public_path($relative), 'files/' . $relative);
                $files[] = $relative;
            }

            $zip->addFromString('manifest.json', json_encode([
                'app' => self::APP_TAG,
                'format' => self::FORMAT,
                'created_at' => $now->toIso8601String(),
                'type' => $type,
                'created_by' => $createdBy,
                'migrations' => $this->migrations(),
                'tables' => $tables,
                'files' => $files,
            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

            if (!$zip->close()) {
                throw new BackupException('Could not finish writing the backup file.');
            }
        } catch (Throwable $e) {
            @$zip->close();
            @unlink($tmp);
            throw $e;
        } finally {
            foreach ($tempFiles as $f) {
                @unlink($f);
            }
        }

        rename($tmp, $final);

        return $name;
    }

    /** Writes the table as JSON lines; returns the row count. */
    protected function dumpTable(string $table, string $file): int
    {
        $handle = fopen($file, 'wb');
        $count = 0;

        foreach (DB::table($table)->cursor() as $row) {
            $encoded = [];
            foreach ((array) $row as $column => $value) {
                $encoded[$column] = is_string($value) && !mb_check_encoding($value, 'UTF-8')
                    ? ['__b64' => base64_encode($value)]
                    : $value;
            }
            fwrite($handle, json_encode($encoded, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . "\n");
            $count++;
        }

        fclose($handle);

        return $count;
    }

    protected function validate(ZipArchive $zip): array
    {
        $manifest = json_decode((string) $zip->getFromName('manifest.json'), true);
        if (!is_array($manifest) || ($manifest['app'] ?? null) !== self::APP_TAG) {
            throw new BackupException('That file is not a backup from this system.');
        }
        if ((int) ($manifest['format'] ?? 0) !== self::FORMAT) {
            throw new BackupException('That backup was made with an unsupported backup format.');
        }

        $theirs = $manifest['migrations'] ?? [];
        $ours = $this->migrations();
        sort($theirs);
        if ($theirs !== $ours) {
            throw new BackupException('That backup was made on a different system version, so it cannot be restored safely here.');
        }

        $current = $this->tables();
        foreach (array_keys($manifest['tables'] ?? []) as $table) {
            if (!in_array($table, $current, true)) {
                throw new BackupException("The backup contains an unknown table ({$table}).");
            }
            if ($zip->locateName('tables/' . $table . '.jsonl') === false) {
                throw new BackupException("The backup is incomplete — table {$table} is missing.");
            }
        }

        return $manifest;
    }

    protected function restoreTables(ZipArchive $zip, array $manifest): void
    {
        $connection = DB::connection();
        $connection->statement('SET FOREIGN_KEY_CHECKS=0');

        try {
            // DELETE (not TRUNCATE) so the whole swap is one transaction:
            // if any insert fails, the live data is left untouched.
            $connection->transaction(function () use ($zip, $manifest, $connection) {
                foreach ($this->tables() as $table) {
                    $connection->table($table)->delete();
                }

                foreach (array_keys($manifest['tables']) as $table) {
                    $stream = $zip->getStream('tables/' . $table . '.jsonl');
                    if (!$stream) {
                        throw new BackupException("Could not read table {$table} from the backup.");
                    }

                    $batch = [];
                    $bytes = 0;
                    while (($line = fgets($stream)) !== false) {
                        $line = trim($line);
                        if ($line === '') {
                            continue;
                        }
                        $batch[] = $this->decodeRow(json_decode($line, true));
                        $bytes += strlen($line);
                        if (count($batch) >= self::BATCH_ROWS || $bytes >= self::BATCH_BYTES) {
                            $connection->table($table)->insert($batch);
                            $batch = [];
                            $bytes = 0;
                        }
                    }
                    fclose($stream);

                    if ($batch) {
                        $connection->table($table)->insert($batch);
                    }
                }
            });
        } finally {
            $connection->statement('SET FOREIGN_KEY_CHECKS=1');
        }
    }

    protected function decodeRow(?array $row): array
    {
        if ($row === null) {
            throw new BackupException('The backup contains a damaged record.');
        }

        foreach ($row as $column => $value) {
            if (is_array($value) && array_key_exists('__b64', $value)) {
                $row[$column] = base64_decode($value['__b64']);
            }
        }

        return $row;
    }

    protected function restoreFiles(ZipArchive $zip, array $manifest): void
    {
        foreach ($manifest['files'] ?? [] as $relative) {
            if (!$this->isAllowedUploadPath($relative)) {
                continue;
            }
            $contents = $zip->getFromName('files/' . $relative);
            if ($contents === false) {
                continue;
            }
            $target = public_path($relative);
            if (!is_dir(dirname($target))) {
                mkdir(dirname($target), 0775, true);
            }
            file_put_contents($target, $contents);
        }
    }

    /** Every app table this backup covers, in a stable order. */
    protected function tables(): array
    {
        $names = Schema::getTableListing(DB::connection()->getDatabaseName(), false);
        $names = array_values(array_filter($names, fn ($n) => !in_array(strtolower($n), self::SKIP_TABLES, true)));
        sort($names);

        return $names;
    }

    protected function migrations(): array
    {
        $list = DB::table('migrations')->pluck('migration')->all();
        sort($list);

        return $list;
    }

    /** Admin-uploaded images (logo, landing photos, profile pictures). */
    protected function uploadedFiles(): array
    {
        $patterns = ['images/puspus_logo.png', 'images/hero_*', 'images/about_*', 'images/profiles/*'];
        $files = [];
        foreach ($patterns as $pattern) {
            foreach (glob(public_path($pattern)) ?: [] as $abs) {
                if (is_file($abs)) {
                    $files[] = 'images/' . ltrim(str_replace('\\', '/', substr($abs, strlen(public_path('images')))), '/');
                }
            }
        }

        return array_values(array_unique($files));
    }

    /** Restored files may only land back in the same upload locations. */
    protected function isAllowedUploadPath(string $relative): bool
    {
        return (bool) preg_match('#^images/(puspus_logo\.png|(hero|about)_[A-Za-z0-9_.-]+|profiles/[A-Za-z0-9_.-]+)$#', $relative)
            && !str_contains($relative, '..')
            && preg_match('/\.(png|jpe?g|gif|webp|svg)$/i', $relative);
    }

    protected function readManifest(string $file): ?array
    {
        $zip = new ZipArchive();
        if ($zip->open($file) !== true) {
            return null;
        }
        $manifest = json_decode((string) $zip->getFromName('manifest.json'), true);
        $zip->close();

        return is_array($manifest) && ($manifest['app'] ?? null) === self::APP_TAG ? $manifest : null;
    }
}
