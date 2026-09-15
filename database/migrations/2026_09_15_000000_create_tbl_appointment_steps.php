<?php
// Place in: database/migrations/
// Converts the Configuration -> Appointment Process editor from numbered
// SystemSetting key/value rows (appt_step_1_title, appt_step_1_desc, ...)
// into real persisted rows, so steps can be archived/unarchived with
// Active/Archived tabs the same way Services already work.

use App\Models\SystemSetting;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('tbl_appointment_steps', function (Blueprint $table) {
            $table->id('StepID');
            $table->string('Title');
            $table->text('Description');
            $table->unsignedInteger('DisplayOrder')->default(0);
            $table->boolean('IsArchived')->default(false);
            $table->timestamps();
        });

        // Carry over whatever is already saved (or the built-in defaults)
        // so existing "How to Book Your Appointment" copy isn't lost.
        $now = now();
        $rows = [];
        foreach (SystemSetting::appointmentSteps() as $n => $step) {
            $rows[] = [
                'Title' => $step['title'],
                'Description' => $step['desc'],
                'DisplayOrder' => $n,
                'IsArchived' => false,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }
        if ($rows) {
            DB::table('tbl_appointment_steps')->insert($rows);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('tbl_appointment_steps');
    }
};
