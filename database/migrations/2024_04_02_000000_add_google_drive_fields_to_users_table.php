<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->text('google_drive_token')->nullable();
            $table->text('google_drive_refresh_token')->nullable();
            $table->string('google_drive_folder_id')->nullable();
            $table->boolean('google_drive_connected')->default(false);
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'google_drive_token',
                'google_drive_refresh_token',
                'google_drive_folder_id',
                'google_drive_connected'
            ]);
        });
    }
}; 