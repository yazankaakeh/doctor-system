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
        Schema::create('theme_settings', function (Blueprint $table) {
            $table->id();
            $table->string('scope')->default('admin'); // 'admin' or 'website'

            // Colors
            $table->string('primary_color')->default('#0F0F2D');
            $table->string('secondary_color')->default('#808390');
            $table->string('success_color')->default('#28c76f');
            $table->string('info_color')->default('#00bad1');
            $table->string('warning_color')->default('#ff9f43');
            $table->string('danger_color')->default('#ff4c51');

            // Typography
            $table->string('font_family')->default('Public Sans');
            $table->string('font_size_base')->default('0.9375rem');
            $table->string('headings_font_family')->nullable();
            $table->string('headings_font_weight')->default('500');

            // Layout
            $table->string('body_bg')->default('#f8f7fa');
            $table->string('card_bg')->default('#ffffff');
            $table->string('border_radius')->default('0.375rem');

            // Logos & Branding
            $table->string('logo_path')->nullable();
            $table->string('logo_dark_path')->nullable();
            $table->string('favicon_path')->nullable();
            $table->string('site_title')->nullable();

            // Advanced Settings
            $table->json('custom_css_variables')->nullable();
            $table->text('custom_css')->nullable();

            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique('scope');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('theme_settings');
    }
};
