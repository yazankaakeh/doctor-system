<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Make patient profile fields nullable to support self-registration
     * where patients may not have all their medical information available.
     */
    public function up(): void
    {
        Schema::table('patients', function (Blueprint $table) {
            $table->integer('gender')->nullable()->change();
            $table->integer('blood_type')->nullable()->change();
            $table->integer('marital_status')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('patients', function (Blueprint $table) {
            $table->integer('gender')->nullable(false)->change();
            $table->integer('blood_type')->nullable(false)->change();
            $table->integer('marital_status')->nullable(false)->change();
        });
    }
};
