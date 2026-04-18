<?php

namespace Modules\Messaging\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class Attachment extends Model
{
    protected $table = 'messaging_attachments';

    protected $fillable = [
        'message_id',
        'type',
        'file_name',
        'file_path',
        'file_size',
        'mime_type',
        'external_media_id',
    ];

    protected $casts = [
        'file_size' => 'integer',
    ];

    // =========================================================================
    // RELATIONSHIPS
    // =========================================================================

    public function message(): BelongsTo
    {
        return $this->belongsTo(Message::class, 'message_id');
    }

    // =========================================================================
    // HELPERS
    // =========================================================================

    public function getUrl(): string
    {
        return Storage::url($this->file_path);
    }

    public function getFullPath(): string
    {
        return Storage::path($this->file_path);
    }

    public function getFormattedSize(): string
    {
        $bytes = $this->file_size;

        if ($bytes >= 1073741824) {
            return number_format($bytes / 1073741824, 2).' GB';
        }

        if ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2).' MB';
        }

        if ($bytes >= 1024) {
            return number_format($bytes / 1024, 2).' KB';
        }

        return $bytes.' bytes';
    }

    public function isImage(): bool
    {
        return in_array($this->type, ['image']) ||
            str_starts_with($this->mime_type ?? '', 'image/');
    }

    public function isDocument(): bool
    {
        return $this->type === 'document';
    }

    public function isAudio(): bool
    {
        return $this->type === 'audio' ||
            str_starts_with($this->mime_type ?? '', 'audio/');
    }

    public function isVideo(): bool
    {
        return $this->type === 'video' ||
            str_starts_with($this->mime_type ?? '', 'video/');
    }

    public function exists(): bool
    {
        return Storage::exists($this->file_path);
    }

    public function delete(): bool
    {
        // Delete file from storage
        if ($this->exists()) {
            Storage::delete($this->file_path);
        }

        return parent::delete();
    }
}
