<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Profile photos live in the database, not in public/images/profiles: the
 * hosted server's disk is wiped on every deploy, which made every uploaded
 * photo fall back to the default avatar. PatientInfo / StaffInfo
 * ProfilePicture now holds "photo:{PhotoID}" (see App\Models\ProfilePhoto).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tbl_profile_photos', function (Blueprint $table) {
            $table->id('PhotoID');
            $table->string('MimeType', 50);
            $table->unsignedInteger('SizeBytes');
            $table->binary('Data');
            $table->timestamps();
        });

        // binary() is a 64 KB BLOB on MySQL — photos need more room.
        if (DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE tbl_profile_photos MODIFY Data MEDIUMBLOB NOT NULL');
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('tbl_profile_photos');
    }
};
