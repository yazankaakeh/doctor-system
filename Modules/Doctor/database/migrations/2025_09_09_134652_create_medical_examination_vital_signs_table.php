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
        Schema::create('medical_examination_vital_sign', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('vital_sign_id')->unsigned();
            $table->foreign('vital_sign_id')->references('id')->on('vital_signs');
            $table->bigInteger('medical_examination_id')->unsigned();
            $table->foreign('medical_examination_id')->references('id')->on('medical_examinations');
            $table->string('value');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vital_signs');
    }
};
