<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('messaging_attachments')) {
            Schema::create('messaging_attachments', function (Blueprint $table) {
                $table->id();
                $table->foreignId('message_id')->constrained('messaging_messages')->cascadeOnDelete();
                $table->string('type'); // image, document, audio, video
                $table->string('file_name');
                $table->string('file_path');
                $table->unsignedBigInteger('file_size')->nullable();
                $table->string('mime_type')->nullable();
                $table->string('external_media_id')->nullable(); // External system media ID
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('messaging_attachments');
    }
};
