<?php

namespace App\Support;

/**
 * Turns the plain-text Privacy Policy / Legal Terms the admin types in
 * Configuration into readable HTML — no markup needed from them:
 *
 *   ALL-CAPS lines at the very top   → document title (+ subtitle)
 *   "Last Updated: …"                → a small date badge
 *   "1. Privacy Policy" on its own   → numbered section heading
 *   a short line on its own          → sub-heading ("Information We Collect")
 *   several lines with no blank line → bullet list (a leading -, • or * is dropped)
 *   a paragraph right after one that ends with ":" → highlighted callout
 *   anything else                    → paragraph
 *
 * Every piece of text is escaped; only the wrapping tags are ours.
 */
class LegalText
{
    public static function toHtml(?string $text): string
    {
        $text = trim(str_replace(["\r\n", "\r"], "\n", (string) $text));
        if ($text === '') {
            return '';
        }

        $blocks = array_map(
            fn ($b) => array_values(array_filter(array_map('trim', explode("\n", $b)), fn ($l) => $l !== '')),
            preg_split('/\n\s*\n/', $text)
        );

        $html = [];
        $atTop = true;
        $prevEndsWithColon = false;

        foreach ($blocks as $lines) {
            if (!$lines) {
                continue;
            }

            // Title block: leading ALL-CAPS lines.
            if ($atTop && self::allCaps($lines)) {
                $html[] = '<div class="legal-title">' . e($lines[0]) . '</div>';
                foreach (array_slice($lines, 1) as $sub) {
                    $html[] = '<div class="legal-subtitle">' . e($sub) . '</div>';
                }
                $prevEndsWithColon = false;
                continue;
            }
            $atTop = false;

            if (count($lines) === 1) {
                $line = $lines[0];

                if (preg_match('/^(last updated|effective date|effective)\s*:\s*(.+)$/i', $line, $m)) {
                    $html[] = '<div class="legal-meta"><i class="bi bi-calendar3"></i> ' . e(ucfirst(strtolower($m[1]))) . ': <strong>' . e($m[2]) . '</strong></div>';
                } elseif (preg_match('/^(\d+)[.)]\s+(.{2,80})$/u', $line, $m) && !self::endsSentence($m[2])) {
                    $html[] = '<h5 class="legal-section"><span class="legal-num">' . e($m[1]) . '</span>' . e(self::titleCase($m[2])) . '</h5>';
                } elseif (self::isHeading($line)) {
                    $html[] = '<h6 class="legal-heading">' . e($line) . '</h6>';
                } elseif ($prevEndsWithColon && mb_strlen($line) > 120) {
                    $html[] = '<div class="legal-callout">' . e($line) . '</div>';
                } else {
                    $html[] = '<p>' . e($line) . '</p>';
                }

                $prevEndsWithColon = str_ends_with($line, ':');
                continue;
            }

            // Several lines together = a list. A trailing short line with no
            // punctuation after punctuated items is really the next heading
            // (a missing blank line), so split it off.
            $trailingHeading = null;
            $last = end($lines);
            $punctuated = count(array_filter(array_slice($lines, 0, -1), fn ($l) => preg_match('/[.;:,]$|\band$/', $l)));
            if ($punctuated >= 2 && self::isHeading($last)) {
                $trailingHeading = array_pop($lines);
            }

            $items = array_map(fn ($l) => '<li>' . e(preg_replace('/^[-•*]\s*/u', '', $l)) . '</li>', $lines);
            $html[] = '<ul class="legal-list">' . implode('', $items) . '</ul>';

            if ($trailingHeading !== null) {
                $html[] = '<h6 class="legal-heading">' . e($trailingHeading) . '</h6>';
            }
            $prevEndsWithColon = false;
        }

        return implode("\n", $html);
    }

    protected static function allCaps(array $lines): bool
    {
        foreach ($lines as $l) {
            if (!preg_match('/\p{Lu}/u', $l) || $l !== mb_strtoupper($l) || mb_strlen($l) > 90 || preg_match('/^\d+[.)]\s/', $l)) {
                return false;
            }
        }

        return true;
    }

    /** A short line with no sentence punctuation at the end, e.g. "Information We Collect". */
    protected static function isHeading(string $line): bool
    {
        return mb_strlen($line) <= 60
            && !self::endsSentence($line)
            && preg_match('/^\p{Lu}/u', $line)
            && !preg_match('/^[-•*]/u', $line);
    }

    protected static function endsSentence(string $line): bool
    {
        return (bool) preg_match('/[.;:,!?]$/u', $line);
    }

    /** "TERMS OF USE" → "Terms of Use"; mixed-case input is left alone. */
    protected static function titleCase(string $s): string
    {
        if ($s !== mb_strtoupper($s)) {
            return $s;
        }
        $small = ['of', 'and', 'the', 'to', 'for', 'in', 'on', 'a', 'an', 'or'];
        $words = explode(' ', mb_strtolower($s));
        foreach ($words as $i => $w) {
            $words[$i] = ($i > 0 && in_array($w, $small, true)) ? $w : mb_convert_case($w, MB_CASE_TITLE);
        }

        return implode(' ', $words);
    }
}
