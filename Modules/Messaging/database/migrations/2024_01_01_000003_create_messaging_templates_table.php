<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('messaging_templates')) {
            Schema::create('messaging_templates', function (Blueprint $table) {
                $table->id();
                $table->foreignId('channel_id')->constrained('messaging_channels')->cascadeOnDelete();
                $table->string('name');
                $table->string('external_template_id')->nullable()->index();
                $table->string('language')->default('en');
                $table->string('category')->nullable(); // marketing, utility, authentication

                // Template structure
                $table->string('header_type')->nullable(); // text, image, document, video
                $table->text('header_content')->nullable();
                $table->text('body');
                $table->string('footer')->nullable();
                $table->json('buttons')->nullable(); // Call to action, quick reply buttons

                // Parameter format (for variable substitution)
                $table->string('parameter_format')->default('{{1}}'); // {{1}}, {name}, etc.

                // Status
                $table->string('status')->default('pending'); // pending, approved, rejected
                $table->boolean('is_active')->default(true);

                $table->timestamps();

                $table->unique(['channel_id', 'name', 'language']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('messaging_templates');
    }
};
