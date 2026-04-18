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
        // 🗺️ Countries table
        /*Schema::create('countries', function (Blueprint $table) {
          $table->id();
          $table->string('name')->unique();
          $table->char('iso2', 2)->nullable()->unique(); // e.g., TR, IQ
          $table->char('iso3', 3)->nullable()->unique(); // e.g., TUR, IRQ
          $table->string('phone_code', 10)->nullable();  // e.g., +90, +964
          $table->string('capital')->nullable();
          $table->string('currency', 10)->nullable();
          $table->string('region', 100)->nullable();     // e.g., Asia
          $table->string('subregion', 100)->nullable();  // e.g., Western Asia
          $table->decimal('latitude', 10, 7)->nullable();
          $table->decimal('longitude', 10, 7)->nullable();
          $table->timestamps();
        });*/
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('countries');
    }
};
