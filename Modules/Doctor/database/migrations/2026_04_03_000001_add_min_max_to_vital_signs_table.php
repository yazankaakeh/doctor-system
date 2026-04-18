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
        Schema::table('vital_signs', function (Blueprint $table) {
            $table->decimal('min_value', 10, 2)->nullable()->after('name');
            $table->decimal('max_value', 10, 2)->nullable()->after('min_value');
            $table->string('unit', 50)->nullable()->after('max_value');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vital_signs', function (Blueprint $table) {
            $table->dropColumn(['min_value', 'max_value', 'unit']);
        });
    }
};
