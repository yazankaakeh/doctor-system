<?php

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

    protected $table = 'messaging_messages';

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

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class, 'conversation_id');
    }

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(Template::class, 'template_id');
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(Attachment::class, 'message_id');
    }

    // =========================================================================
    // SCOPES
    // =========================================================================

    public function scopeInbound($query)
    {
        return $query->where('direction', MessageDirectionEnum::INBOUND);
    }

    public function scopeOutbound($query)
    {
        return $query->where('direction', MessageDirectionEnum::OUTBOUND);
    }

    public function scopeOfType($query, MessageTypeEnum $type)
    {
        return $query->where('message_type', $type);
    }

    public function scopeWithStatus($query, MessageStatusEnum $status)
    {
        return $query->where('status', $status);
    }

    public function scopeFailed($query)
    {
        return $query->where('status', MessageStatusEnum::FAILED);
    }

    public function scopePending($query)
    {
        return $query->where('status', MessageStatusEnum::PENDING);
    }

    public function scopeUnread($query)
    {
        return $query->whereNull('read_at')
            ->where('direction', MessageDirectionEnum::INBOUND);
    }

    // =========================================================================
    // HELPERS
    // =========================================================================

    public function isInbound(): bool
    {
        return $this->direction === MessageDirectionEnum::INBOUND;
    }

    public function isOutbound(): bool
    {
        return $this->direction === MessageDirectionEnum::OUTBOUND;
    }

    public function isFromUser(): bool
    {
        return $this->sender_type === SenderTypeEnum::USER;
    }

    public function isFromContact(): bool
    {
        return $this->sender_type === SenderTypeEnum::CONTACT;
    }

    public function isFromSystem(): bool
    {
        return $this->sender_type === SenderTypeEnum::SYSTEM;
    }

    public function isMedia(): bool
    {
        return $this->message_type->isMedia();
    }

    public function isTemplate(): bool
    {
        return $this->message_type === MessageTypeEnum::TEMPLATE;
    }

    public function hasFailed(): bool
    {
        return $this->status === MessageStatusEnum::FAILED;
    }

    public function canRetry(): bool
    {
        return $this->status->canRetry();
    }

    public function markAsSent(): self
    {
        $this->update([
            'status' => MessageStatusEnum::SENT,
            'sent_at' => now(),
        ]);

        return $this;
    }

    public function markAsDelivered(): self
    {
        $this->update([
            'status' => MessageStatusEnum::DELIVERED,
            'delivered_at' => now(),
        ]);

        return $this;
    }

    public function markAsRead(): self
    {
        $this->update([
            'status' => MessageStatusEnum::READ,
            'read_at' => now(),
        ]);

        return $this;
    }

    public function markAsFailed(?string $errorCode = null, ?string $errorMessage = null): self
    {
        $this->update([
            'status' => MessageStatusEnum::FAILED,
            'error_code' => $errorCode,
            'error_message' => $errorMessage,
        ]);

        return $this;
    }

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
