<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCommunicationFormsTable extends Migration
{
    public function up()
    {
        Schema::create('communication_forms', function (Blueprint $table) {
            $table->id();  // Auto-incrementing primary key
            $table->string('to');  // 'To' field
            $table->string('attention');  // 'Attention' field
            $table->json('departments')->nullable();  // Array of selected departments
            $table->json('action_items')->nullable();  // Array of selected action items
            $table->json('additional_actions')->nullable();  // Array of additional actions
            $table->string('file_type')->nullable();  // The file type
            $table->json('files')->nullable();  // Array of uploaded file names
            $table->timestamps();  // Created at and Updated at timestamps
        });
    }

    public function down()
    {
        Schema::dropIfExists('communication_forms');
    }
}
