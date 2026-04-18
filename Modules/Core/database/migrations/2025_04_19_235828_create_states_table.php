<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 🧭 States table (provinces / governorates / regions)
        /*Schema::create('states', function (Blueprint $table) {
          $table->id();
          $table->foreignId('country_id')->constrained('countries')->cascadeOnDelete();
          $table->string('name');
          $table->string('state_code', 50)->nullable();  // e.g., IST, BG
          $table->string('type', 50)->nullable();        // e.g., province, region
          $table->decimal('latitude', 10, 7)->nullable();
          $table->decimal('longitude', 10, 7)->nullable();
          $table->timestamps();

          $table->unique(['country_id', 'name']);
          $table->index(['country_id']);
        });*/
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('states');
    }
};
