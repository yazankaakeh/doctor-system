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
        Schema::create('addresses', function (Blueprint $table) {
            $table->id();
            // Polymorphic Relation
            $table->unsignedBigInteger('addressable_id');
            $table->string('addressable_type');

            $table->string('type');
            // Country & City Relations
            $table->unsignedBigInteger('country_id')->nullable();
            $table->unsignedBigInteger('city_id')->nullable();

            // Address Fields
            /*$table->string('street')->nullable();
            $table->string('building')->nullable();     // optional building number/name
            $table->string('floor')->nullable();        // optional floor info
            $table->string('apartment')->nullable();    // optional apartment number
            $table->string('postal_code')->nullable();*/
            $table->text('full_address')->nullable();   // text field for entire address if needed

            // Coordinates (optional but useful for maps)
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();

            // Status
            $table->boolean('is_primary')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('addresses');
    }
};
