<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Optional photos a patient attaches when booking (teeth, X-rays, old
     * dental records…) for the dentist to review on Appointment Approval.
     *
     * Stored in the database, not on disk: Railway's filesystem is wiped on
     * every redeploy, and these are private medical images that must never
     * be reachable by a public URL — they're served only through an
     * access-checked route. The browser shrinks each photo to a JPEG under
     * ~900 KB before upload so a row always fits MySQL's packet limit.
     */
    public function up(): void
    {
        Schema::create('tbl_appointment_attachments', function (Blueprint $table) {
            $table->id('AttachmentID');
            $table->unsignedBigInteger('AppointmentID');
            $table->string('FileName', 150);
            $table->string('MimeType', 50);
            $table->unsignedInteger('SizeBytes');
            $table->binary('Data');
            $table->timestamps();

            $table->index('AppointmentID');
        });

        // binary() is a 64 KB BLOB on MySQL — widen it to MEDIUMBLOB (16 MB).
        DB::statement('ALTER TABLE tbl_appointment_attachments MODIFY Data MEDIUMBLOB NOT NULL');
    }

    public function down(): void
    {
        Schema::dropIfExists('tbl_appointment_attachments');
    }
};
