<?php

/**
 * -----------------------------------------------------------------------------
 * Attachment Model
 * -----------------------------------------------------------------------------
 *
 * Represents a file uploaded alongside a Message (image, document, audio or
 * video). Each Message may have many Attachments.
 *
 * Important fields:
 *   - `file_path`          → relative path inside the storage disk.
 *   - `mime_type`          → detected mime so the UI can render it correctly.
 *   - `external_media_id`  → provider-side media reference when relayed via
 *                             WhatsApp / Telegram (lets us re-send without
 *                             re-uploading).
 *
 * Overriding delete() guarantees the underlying file is also removed from
 * storage when the row is deleted.
 * -----------------------------------------------------------------------------
 */

namespace Modules\Messaging\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class Attachment extends Model
{
    protected $table = 'messaging_attachments';

    /**
     * Mass-assignable columns.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'message_id',         // FK → messaging_messages.id
        'type',               // Logical type: image / document / audio / video
        'file_name',          // Original filename as uploaded
        'file_path',          // Relative path within the storage disk
        'file_size',          // Size in bytes
        'mime_type',          // Detected MIME
        'external_media_id',  // Provider-side media id (WhatsApp/Telegram)
    ];

    protected $casts = [
        'file_size' => 'integer',
    ];

    // =========================================================================
    // RELATIONSHIPS
    // =========================================================================

    /** The message this file is attached to. */
    public function message(): BelongsTo
    {
        return $this->belongsTo(Message::class, 'message_id');
    }

    // =========================================================================
    // HELPERS
    // =========================================================================

    /** Public URL usable in <img src="..."> etc. */
    public function getUrl(): string
    {
        return Storage::url($this->file_path);
    }

    /** Absolute filesystem path (useful for server-side processing). */
    public function getFullPath(): string
    {
        return Storage::path($this->file_path);
    }

    /**
     * Human-readable size (e.g. "2.34 MB"). The thresholds use binary units
     * (1024-based), matching what desktop OSes typically display.
     */
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

    /** Is this attachment an image (by logical type or MIME prefix)? */
    public function isImage(): bool
    {
        return in_array($this->type, ['image']) ||
            str_starts_with($this->mime_type ?? '', 'image/');
    }

    /** Is this a document (PDF/Office/etc)? */
    public function isDocument(): bool
    {
        return $this->type === 'document';
    }

    /** Is this an audio file? */
    public function isAudio(): bool
    {
        return $this->type === 'audio' ||
            str_starts_with($this->mime_type ?? '', 'audio/');
    }

    /** Is this a video file? */
    public function isVideo(): bool
    {
        return $this->type === 'video' ||
            str_starts_with($this->mime_type ?? '', 'video/');
    }

    /** Does the underlying file still exist in storage? */
    public function exists(): bool
    {
        return Storage::exists($this->file_path);
    }

    /**
     * Delete the DB row AND the underlying file, so removing an attachment
     * through Eloquent never leaves orphaned files behind.
     */
    public function delete(): bool
    {
        // First remove the actual file if present, then delete the record.
        if ($this->exists()) {
            Storage::delete($this->file_path);
        }

        return parent::delete();
    }
}
