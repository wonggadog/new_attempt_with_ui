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
        Schema::create('recipients', function (Blueprint $table) {
            $table->id(); // Auto-incrementing primary key
            $table->string('name'); // Recipient's name
            $table->string('department'); // Associated department
            $table->timestamps(); // Created at and Updated at timestamps
        });
    }

    public function down()
    {
        Schema::dropIfExists('recipients');
    }
};
