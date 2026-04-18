<?php

namespace Modules\Messaging\Livewire;

use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Modules\Messaging\Enums\MessageTypeEnum;
use Modules\Messaging\Models\Conversation;
use Modules\Messaging\Models\QuickReply;
use Modules\Messaging\Services\MessagingService;

class MessageComposer extends Component
{
    public ?int $conversationId = null;

    public string $content = '';

    // Attachment data (uploaded via JavaScript)
    public ?string $attachmentPath = null;

    public ?string $attachmentName = null;

    public ?string $attachmentMimeType = null;

    public ?int $attachmentSize = null;

    public bool $showQuickReplies = false;

    public $quickReplies = [];

    public bool $isSending = false;

    public bool $isUploading = false;

    protected $rules = [
        'content' => 'required_without:attachmentPath|string|max:4000',
    ];

    public function mount(?int $conversationId = null): void
    {
        $this->conversationId = $conversationId;
        $this->loadQuickReplies();
    }

    /**
     * Test method to verify Livewire calls are working.
     */
    public function testCall(): array
    {
        logger()->info('testCall() invoked', ['conversationId' => $this->conversationId]);

        return ['success' => true, 'conversationId' => $this->conversationId, 'timestamp' => now()->toISOString()];
    }

    public function loadQuickReplies(): void
    {
        if (! auth()->check()) {
            return;
        }

        $conversation = $this->conversationId ? Conversation::find($this->conversationId) : null;
        $channelId = $conversation?->channel_id;

        $this->quickReplies = QuickReply::active()
            ->forUser(auth()->id())
            ->when($channelId, fn ($q) => $q->forChannel($channelId))
            ->orderBy('usage_count', 'desc')
            ->limit(10)
            ->get();
    }

    public function toggleQuickReplies(): void
    {
        $this->showQuickReplies = ! $this->showQuickReplies;
    }

    public function selectQuickReply(int $quickReplyId): void
    {
        $quickReply = QuickReply::find($quickReplyId);

        if ($quickReply) {
            $this->content = $quickReply->content;
            $quickReply->incrementUsage();
            $this->showQuickReplies = false;
        }
    }

    /**
     * Set attachment data from JavaScript upload.
     */
    public function setAttachment(array $data): void
    {
        $this->attachmentPath = $data['path'] ?? null;
        $this->attachmentName = $data['name'] ?? null;
        $this->attachmentMimeType = $data['mime_type'] ?? null;
        $this->attachmentSize = $data['size'] ?? null;
        $this->isUploading = false;
    }

    /**
     * Set attachment and send immediately (for voice notes).
     */
    public function setAttachmentAndSend(array $data): array
    {
        try {
            logger()->info('setAttachmentAndSend called', $data);

            $this->attachmentPath = $data['path'] ?? null;
            $this->attachmentName = $data['name'] ?? null;
            $this->attachmentMimeType = $data['mime_type'] ?? null;
            $this->attachmentSize = $data['size'] ?? null;
            $this->isUploading = false;

            logger()->info('Attachment set', [
                'path' => $this->attachmentPath,
                'name' => $this->attachmentName,
                'mime' => $this->attachmentMimeType,
                'conversationId' => $this->conversationId,
            ]);

            // Send immediately
            $this->send();

            return [
                'success' => true,
                'conversationId' => $this->conversationId,
                'attachmentPath' => $this->attachmentPath,
            ];
        } catch (\Throwable $e) {
            logger()->error('setAttachmentAndSend error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Clear attachment.
     */
    public function clearAttachment(): void
    {
        // Delete temp file if exists
        if ($this->attachmentPath && Storage::disk('public')->exists($this->attachmentPath)) {
            Storage::disk('public')->delete($this->attachmentPath);
        }

        $this->attachmentPath = null;
        $this->attachmentName = null;
        $this->attachmentMimeType = null;
        $this->attachmentSize = null;
    }

    public function send(): void
    {
        try {
            logger()->info('send() called', [
                'conversationId' => $this->conversationId,
                'content' => $this->content,
                'attachmentPath' => $this->attachmentPath,
                'attachmentMimeType' => $this->attachmentMimeType,
            ]);

            if (! $this->conversationId) {
                logger()->warning('No conversationId, returning');

                return;
            }

            // Custom validation - need either content or attachment
            if (empty(trim($this->content)) && ! $this->attachmentPath) {
                logger()->warning('No content and no attachment, returning');
                $this->addError('content', 'Please enter a message or attach a file.');

                return;
            }

            $this->isSending = true;

            $conversation = Conversation::find($this->conversationId);

            if (! $conversation) {
                logger()->warning('Conversation not found', ['id' => $this->conversationId]);
                $this->isSending = false;

                return;
            }

            logger()->info('Found conversation', ['id' => $conversation->id, 'channel' => $conversation->channel->type->value ?? 'unknown']);

            $service = app(MessagingService::class);

            $options = [
                'async' => false, // Send synchronously for immediate feedback
            ];

            // Handle attachment
            if ($this->attachmentPath) {
                $messageType = $this->determineMessageTypeFromMime($this->attachmentMimeType);
                logger()->info('Processing attachment', ['type' => $messageType->value, 'path' => $this->attachmentPath]);

                // Move from temp to permanent location
                $permanentPath = str_replace('messaging-temp/', 'messaging/attachments/', $this->attachmentPath);
                if (Storage::disk('public')->exists($this->attachmentPath)) {
                    Storage::disk('public')->move($this->attachmentPath, $permanentPath);
                    logger()->info('Moved file to permanent location', ['from' => $this->attachmentPath, 'to' => $permanentPath]);
                } else {
                    logger()->warning('Temp file does not exist', ['path' => $this->attachmentPath]);
                }

                $options['media_path'] = $permanentPath;
                $options['media_url'] = asset('storage/'.$permanentPath);
                $options['mime_type'] = $this->attachmentMimeType;
                $options['file_name'] = $this->attachmentName;
                $options['message_type'] = $messageType;
            }

            logger()->info('Calling MessagingService::send', ['options' => $options]);

            $result = $service->send(
                $conversation,
                $this->content ?: '',
                $options['message_type'] ?? MessageTypeEnum::TEXT,
                auth()->id(),
                $options
            );

            logger()->info('Send result', [
                'success' => $result->isSuccess(),
                'messageId' => $result->message?->id ?? null,
                'error' => $result->errorMessage ?? null,
            ]);

            // Increment unread for recipient
            if ($result->isSuccess() || $result->message) {
                $conversation->incrementUnread();
            }

            $this->content = '';
            $this->attachmentPath = null;
            $this->attachmentName = null;
            $this->attachmentMimeType = null;
            $this->attachmentSize = null;
            $this->isSending = false;

            $this->dispatch('message-sent');
        } catch (\Throwable $e) {
            logger()->error('send() error', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
            $this->isSending = false;
            throw $e;
        }
    }

    protected function determineMessageTypeFromMime(?string $mimeType): MessageTypeEnum
    {
        if (! $mimeType) {
            return MessageTypeEnum::DOCUMENT;
        }

        if (str_starts_with($mimeType, 'image/')) {
            return MessageTypeEnum::IMAGE;
        }

        if (str_starts_with($mimeType, 'audio/')) {
            return MessageTypeEnum::AUDIO;
        }

        if (str_starts_with($mimeType, 'video/')) {
            return MessageTypeEnum::VIDEO;
        }

        return MessageTypeEnum::DOCUMENT;
    }

    public function updatedContent(): void
    {
        // Check for quick reply shortcut
        if (str_starts_with($this->content, '/')) {
            $shortcut = $this->content;
            $quickReply = QuickReply::active()
                ->forUser(auth()->id())
                ->where('shortcut', $shortcut)
                ->first();

            if ($quickReply) {
                $this->content = $quickReply->content;
                $quickReply->incrementUsage();
            }
        }
    }

    public function render()
    {
        return view('messaging::livewire.message-composer');
    }
}
