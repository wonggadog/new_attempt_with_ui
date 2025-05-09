<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('communication_form_statuses', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('communication_form_id');
            $table->string('status'); // sent, delivered, read, acknowledged, etc.
            $table->timestamp('created_at')->useCurrent();
            $table->foreign('communication_form_id')->references('id')->on('communication_forms')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('communication_form_statuses');
    }
};
