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
        Schema::create('blog_post_tags_posts', function (Blueprint $table) {
            $table->id();
            $table
                ->bigInteger('post_id')
                ->unsigned();
            $table
                ->foreign('post_id')
                ->references('id')
                ->on('blog_posts');

            $table
                ->bigInteger('tag_id')
                ->unsigned();
            $table
                ->foreign('tag_id')
                ->references('id')
                ->on('blog_post_tags');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('blog_post_tags_posts');
    }
};
