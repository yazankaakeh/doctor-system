<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('messaging_template_parameters')) {
            Schema::create('messaging_template_parameters', function (Blueprint $table) {
                $table->id();
                $table->foreignId('template_id')->constrained('messaging_templates')->cascadeOnDelete();
                $table->string('name');
                $table->unsignedTinyInteger('position');

                // Parameter source configuration
                $table->string('source_type')->default('static'); // static, model_field, custom
                $table->string('source_model')->nullable(); // Lead, User, etc.
                $table->string('source_field')->nullable(); // name, email, phone, etc.
                $table->string('default_value')->nullable();

                $table->timestamps();

                $table->unique(['template_id', 'position']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('messaging_template_parameters');
    }
};
