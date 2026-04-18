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
        Schema::create('seo_meta', function (Blueprint $table) {
            $table->id();
            $table->morphs('seoable'); // seoable_type, seoable_id (indexed)

            $table->json('title')->nullable();
            $table->json('meta_description')->nullable();
            $table->json('canonical_url')->nullable();

            $table->boolean('robots_index')->default(true);
            $table->boolean('robots_follow')->default(true);

            // OG / Twitter / JSON-LD تقدر تخليها per-locale أو global:
            // لو تحتاجها حسب اللغة، خزنها JSON متعدد اللغات أيضاً:
            $table->json('og')->nullable();       // إما {"ar": {...}, "en": {...}} أو global object
            $table->json('twitter')->nullable();  // نفس الفكرة
            $table->json('jsonld')->nullable();   // تقدر تخلي {"ar":[...], "en":[...]} أو global array

            $table->timestamps();

            $table->unique(['seoable_type', 'seoable_id']); // صف واحد لكل كيان
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('seo_metas');
    }
};
