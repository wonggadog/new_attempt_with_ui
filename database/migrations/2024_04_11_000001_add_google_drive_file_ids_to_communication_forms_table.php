<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('communication_forms', function (Blueprint $table) {
            $table->json('google_drive_file_ids')->nullable()->after('files');
        });
    }

    public function down()
    {
        Schema::table('communication_forms', function (Blueprint $table) {
            $table->dropColumn('google_drive_file_ids');
        });
    }
}; 