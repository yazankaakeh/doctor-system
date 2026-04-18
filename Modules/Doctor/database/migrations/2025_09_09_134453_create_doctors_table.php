<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('doctors', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('phone')->nullable();
            $table->string('password');
            $table->integer('gender');
            $table->integer('age');
            $table->integer('is_active');
            $table->bigInteger('medical_specialty_id')->unsigned();
            $table->foreign('medical_specialty_id')->references('id')->on('medical_specialties');
            $table->unsignedBigInteger('nationality_id')->nullable();
            /* $table->foreign('nationality_id')->references('id')->on('countries'); */
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('doctors');
    }
};
