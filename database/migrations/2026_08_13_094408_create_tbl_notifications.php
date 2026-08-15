<?php
// Place in: database/migrations/
// NOTE: This table is not on your ERD screenshot. It's a necessary addition
// to make the notification bell/dropdown actually work off real data instead
// of hardcoded HTML. Mention this to your adviser if the ERD needs updating.

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('tbl_notifications', function (Blueprint $table) {
            $table->id('NotificationID');
            $table->unsignedBigInteger('UserID');
            $table->string('Title');
            $table->text('Message');
            $table->string('Type')->default('info'); // success | warning | danger | info
            $table->boolean('IsRead')->default(false);
            $table->timestamps();

            $table->foreign('UserID')->references('UserID')->on('tbl_useraccount')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tbl_notifications');
    }
};