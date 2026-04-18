<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('blog_categories', function (Blueprint $table) {
            if (! Schema::hasColumn('blog_categories', 'deleted_at')) {
                $table->softDeletes();
            }
            if (Schema::hasColumn('blog_categories', 'active') && ! Schema::hasColumn('blog_categories', 'is_active')) {
                $table->renameColumn('active', 'is_active');
            }
            if (! Schema::hasColumn('blog_categories', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('description');
            }
            if (Schema::hasColumn('blog_categories', 'title') && Schema::getColumnType('blog_categories', 'title') !== 'json') {
                // If initial schema used string, change to json for translations
                $table->json('title')->change();
            }
            if (Schema::hasColumn('blog_categories', 'description') && Schema::getColumnType('blog_categories', 'description') !== 'json') {
                $table->json('description')->change();
            }
        });
    }

    public function down(): void
    {
        Schema::table('blog_categories', function (Blueprint $table) {
            // Keeping soft deletes and is_active; no destructive down to avoid data loss
        });
    }
};
