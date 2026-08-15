<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('tbl_patientInfo', function (Blueprint $table) {
            $table->id('PatientID');

            $table->unsignedBigInteger('UserID');
            $table->foreign('UserID')->references('UserID')->on('tbl_useraccount')->onDelete('cascade');

            // ---- fields that ARE on your ERD ----
            $table->string('LastName');
            $table->string('FirstName');
            $table->string('MiddleName')->nullable();
            $table->string('PhoneNumber');
            $table->date('DateOfBirth');
            $table->string('Nationality');
            $table->string('Address');
            $table->string('ParentsName')->nullable();
            $table->string('ParentsOccupation')->nullable();

            // ---- fields your signup form collects but the ERD screenshot
            //      didn't show — added so the data isn't thrown away.
            //      Drop any of these if they're genuinely not needed. ----
            $table->unsignedTinyInteger('Age')->nullable();
            $table->string('Gender')->nullable();
            $table->string('Religion')->nullable();
            $table->string('Occupation')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tbl_patientInfo');
    }
};
