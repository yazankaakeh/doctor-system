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
        Schema::create('final_diagnosis_patients', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('patient_id')->unsigned();
            $table->foreign('patient_id')->references('id')->on('patients');
            $table->bigInteger('final_diagnosis_id')->unsigned();
            $table->foreign('final_diagnosis_id')->references('id')->on('final_diagnoses');
            $table->bigInteger('medical_examination_id')->unsigned();
            $table->foreign('medical_examination_id')->references('id')->on('medical_examinations');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('final_diagnosis_patients');
    }
};
