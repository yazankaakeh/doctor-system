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
        // 🏙️ Cities table
        /*Schema::create('cities', function (Blueprint $table) {
          $table->id();
          $table->foreignId('country_id')->constrained('countries')->cascadeOnDelete();
          $table->foreignId('state_id')->nullable()->constrained('states')->nullOnDelete();
          $table->string('name');
          $table->decimal('latitude', 10, 7)->nullable();
          $table->decimal('longitude', 10, 7)->nullable();
          $table->timestamps();

          $table->unique(['state_id', 'name']);
          $table->index(['country_id']);
          $table->index(['state_id']);
        });*/
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cities');
    }
};
