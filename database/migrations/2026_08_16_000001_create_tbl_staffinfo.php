<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('tbl_staffInfo', function (Blueprint $table) {
            $table->id('StaffInfoID');
            $table->foreignId('UserID')->constrained('tbl_useraccount', 'UserID')->cascadeOnDelete();
            $table->string('LastName');
            $table->string('FirstName');
            $table->string('MiddleName')->nullable();
            $table->string('PhoneNumber', 20);
            $table->date('DateOfBirth');
            $table->unsignedTinyInteger('Age')->nullable();
            $table->string('Gender');
            $table->string('Religion')->nullable();
            $table->string('Nationality');
            $table->string('Address');
            $table->string('ProfilePicture')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tbl_staffInfo');
    }
};
