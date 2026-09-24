<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Clinic-wide closures (holidays, etc.) — separate from the per-dentist
 * Not Available slots in tbl_dentistschedule. A row with IsArchived=false
 * blocks booking for every dentist on that Date; archiving one reopens it.
 * Table name intentionally lower-snake-case (see 2026_09_22 migration's
 * note on Linux MySQL being case-sensitive about table names).
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::create('tbl_closed_dates', function (Blueprint $table) {
            $table->id('ClosedDateID');
            $table->date('Date');
            $table->string('Reason')->nullable();
            $table->unsignedBigInteger('ClosedBy')->nullable();
            $table->boolean('IsArchived')->default(false);
            $table->text('ArchiveReason')->nullable();
            $table->timestamp('ArchivedAt')->nullable();
            $table->timestamps();

            $table->foreign('ClosedBy')->references('UserID')->on('tbl_useraccount')->nullOnDelete();
            $table->index('Date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tbl_closed_dates');
    }
};
