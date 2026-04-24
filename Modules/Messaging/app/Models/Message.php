<?php

/**
 * -----------------------------------------------------------------------------
 * Message Model
 * -----------------------------------------------------------------------------
 *
 * A single message inside a Conversation. Messages can be:
 *
 *   - INBOUND  → the customer/participant sent it (received via webhook/polling).
 *   - OUTBOUND → an internal user or system sent it (sent through a Channel driver).
 *
 * Each message has:
 *   - `message_type` (TEXT, IMAGE, VIDEO, FILE, TEMPLATE, …) backed by an enum.
 *   - `sender_type`  (USER, CONTACT, SYSTEM) which specifies who authored it —
 *     SYSTEM messages come from the app itself (auto-replies, notices).
 *   - `status` life-cycle: PENDING → SENT → DELIVERED → READ (or FAILED).
 *   - Optional media_url (single-file messages) and many-to-many attachments
 *     for multi-file uploads.
 *   - Optional Template link when the message was composed from a template.
 *
 * Integrations with external providers (WhatsApp, Telegram) round-trip an
 * `external_message_id` for webhook correlation.
 * -----------------------------------------------------------------------------
 */

namespace Modules\Messaging\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Modules\Messaging\Enums\MessageDirectionEnum;
use Modules\Messaging\Enums\MessageStatusEnum;
use Modules\Messaging\Enums\MessageTypeEnum;
use Modules\Messaging\Enums\SenderTypeEnum;

class Message extends Model
{
    use SoftDeletes;

    /** Namespaced table so the messaging module stays self-contained. */
    protected $table = 'messaging_messages';

    /**
     * Mass-assignable columns. See the migration for full semantics.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'uuid',
        'conversation_id',
        'sender_type',
        'sender_id',
        'direction',
        'message_type',
        'content',
        'media_url',
        'template_id',
        'template_variables',
        'status',
        'error_code',
        'error_message',
        'sent_at',
        'delivered_at',
        'read_at',
        'external_message_id',
        'metadata',
    ];

    protected $casts = [
        'sender_type' => SenderTypeEnum::class,
        'direction' => MessageDirectionEnum::class,
        'message_type' => MessageTypeEnum::class,
        'status' => MessageStatusEnum::class,
        'template_variables' => 'array',
        'sent_at' => 'datetime',
        'delivered_at' => 'datetime',
        'read_at' => 'datetime',
        'metadata' => 'array',
    ];

    // =========================================================================
    // BOOT
    // =========================================================================

    /**
     * Auto-generate a UUID per message — used as an idempotency key when
     * retrying outbound sends and to correlate with provider webhooks.
     */
    protected static function boot(): void
    {
        parent::boot();

        static::creating(function ($message) {
            if (empty($message->uuid)) {
                $message->uuid = (string) Str::uuid();
            }
        });
    }

    // =========================================================================
    // RELATIONSHIPS
    // =========================================================================

    /** Parent thread. */
    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class, 'conversation_id');
    }

    /** The internal user who sent this message (null for CONTACT/SYSTEM). */
    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    /** Template used to generate this message, if any. */
    public function template(): BelongsTo
    {
        return $this->belongsTo(Template::class, 'template_id');
    }

    /** File attachments linked to this message (images, docs, audio…). */
    public function attachments(): HasMany
    {
        return $this->hasMany(Attachment::class, 'message_id');
    }

    // =========================================================================
    // SCOPES
    // =========================================================================

    /** Messages received from the customer. */
    public function scopeInbound($query)
    {
        return $query->where('direction', MessageDirectionEnum::INBOUND);
    }

    /** Messages sent by agents / system. */
    public function scopeOutbound($query)
    {
        return $query->where('direction', MessageDirectionEnum::OUTBOUND);
    }

    /** Filter by message type (text/image/template/…). */
    public function scopeOfType($query, MessageTypeEnum $type)
    {
        return $query->where('message_type', $type);
    }

    /** Filter by delivery status. */
    public function scopeWithStatus($query, MessageStatusEnum $status)
    {
        return $query->where('status', $status);
    }

    /** Only messages that permanently failed to deliver. */
    public function scopeFailed($query)
    {
        return $query->where('status', MessageStatusEnum::FAILED);
    }

    /** Only messages still queued for delivery. */
    public function scopePending($query)
    {
        return $query->where('status', MessageStatusEnum::PENDING);
    }

    /**
     * Unread = inbound messages without a read_at timestamp. Used by the
     * unread-count counter on Conversation.
     */
    public function scopeUnread($query)
    {
        return $query->whereNull('read_at')
            ->where('direction', MessageDirectionEnum::INBOUND);
    }

    // =========================================================================
    // HELPERS — convenience wrappers used in Blade templates and services.
    // =========================================================================

    /** True if the message was received from the customer. */
    public function isInbound(): bool
    {
        return $this->direction === MessageDirectionEnum::INBOUND;
    }

    /** True if the message was sent to the customer. */
    public function isOutbound(): bool
    {
        return $this->direction === MessageDirectionEnum::OUTBOUND;
    }

    /** Authored by an internal user (agent/doctor). */
    public function isFromUser(): bool
    {
        return $this->sender_type === SenderTypeEnum::USER;
    }

    /** Authored by the customer on the other side of the thread. */
    public function isFromContact(): bool
    {
        return $this->sender_type === SenderTypeEnum::CONTACT;
    }

    /** Authored by the system itself (auto-replies, status notices). */
    public function isFromSystem(): bool
    {
        return $this->sender_type === SenderTypeEnum::SYSTEM;
    }

    /** True when the message type represents media (image, video, file, …). */
    public function isMedia(): bool
    {
        return $this->message_type->isMedia();
    }

    /** True when this message was generated from a Template. */
    public function isTemplate(): bool
    {
        return $this->message_type === MessageTypeEnum::TEMPLATE;
    }

    /** Delivery permanently failed. */
    public function hasFailed(): bool
    {
        return $this->status === MessageStatusEnum::FAILED;
    }

    /** Whether the current status permits retrying delivery. */
    public function canRetry(): bool
    {
        return $this->status->canRetry();
    }

    /** Transition the message to SENT + stamp sent_at. */
    public function markAsSent(): self
    {
        $this->update([
            'status' => MessageStatusEnum::SENT,
            'sent_at' => now(),
        ]);

        return $this;
    }

    /** Transition to DELIVERED (confirmed landed on the recipient device). */
    public function markAsDelivered(): self
    {
        $this->update([
            'status' => MessageStatusEnum::DELIVERED,
            'delivered_at' => now(),
        ]);

        return $this;
    }

    /** Transition to READ (recipient opened it). */
    public function markAsRead(): self
    {
        $this->update([
            'status' => MessageStatusEnum::READ,
            'read_at' => now(),
        ]);

        return $this;
    }

    /**
     * Transition to FAILED and store the provider-reported error code/text
     * so agents can see why delivery failed.
     */
    public function markAsFailed(?string $errorCode = null, ?string $errorMessage = null): self
    {
        $this->update([
            'status' => MessageStatusEnum::FAILED,
            'error_code' => $errorCode,
            'error_message' => $errorMessage,
        ]);

        return $this;
    }

    /**
     * Human-friendly label for the message author, used in the chat bubble.
     * Falls back to "System" for machine-generated messages.
     */
    public function getSenderName(): string
    {
        if ($this->sender_type === SenderTypeEnum::USER && $this->sender) {
            return $this->sender->name;
        }

        if ($this->sender_type === SenderTypeEnum::CONTACT) {
            return $this->conversation->getDisplayName();
        }

        return 'System';
    }

    /**
     * Short preview text displayed in the conversation list.
     * For non-text messages (image, file, template…) returns a bracketed
     * placeholder like "[Image]" rather than the raw content.
     */
    public function getPreviewContent(int $maxLength = 50): string
    {
        if ($this->message_type !== MessageTypeEnum::TEXT) {
            return '['.$this->message_type->label().']';
        }

        if (strlen($this->content) <= $maxLength) {
            return $this->content;
        }

        return Str::limit($this->content, $maxLength);
    }
}
