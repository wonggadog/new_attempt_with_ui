<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCommunicationFormsTable extends Migration
{
    public function up()
    {
        Schema::create('communication_forms', function (Blueprint $table) {
            $table->id();
            $table->string('to');
            $table->string('from'); // added from column
            $table->string('attention');
            $table->json('departments')->nullable();
            $table->json('action_items')->nullable();
            $table->json('additional_actions')->nullable();
            $table->string('file_type')->nullable();
            $table->json('files')->nullable()->comment('Array of objects: [{"path": ..., "original": ...}]');
            $table->date('due_date')->nullable(); // Add due_date column after files
            $table->text('additional_notes')->nullable(); // New field
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('communication_forms');
    }
}