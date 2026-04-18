<?php

namespace Modules\Messaging\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Modules\CRM\Models\Lead;
use Modules\Messaging\Enums\ChannelTypeEnum;
use Modules\Messaging\Models\Conversation;

class ContactLinkingService
{
    /**
     * Models to search for matching contacts.
     */
    protected array $searchableModels = [
        Lead::class,
    ];

    /**
     * Auto-link a conversation to an existing contact/lead.
     */
    public function autoLink(Conversation $conversation): ?Model
    {
        if ($conversation->conversable_id) {
            return $conversation->conversable;
        }

        $identifier = $conversation->participant_identifier;

        if (! $identifier) {
            return null;
        }

        // Try to find a matching contact
        $contact = $this->findContact($identifier, $conversation->channel->type);

        if ($contact) {
            $conversation->update([
                'conversable_type' => get_class($contact),
                'conversable_id' => $contact->id,
            ]);

            Log::channel('messaging')->info('Conversation auto-linked to contact', [
                'conversation_id' => $conversation->id,
                'contact_type' => get_class($contact),
                'contact_id' => $contact->id,
            ]);

            return $contact;
        }

        return null;
    }

    /**
     * Find a contact by identifier.
     */
    public function findContact(string $identifier, ChannelTypeEnum $channelType): ?Model
    {
        // Clean the identifier
        $cleanIdentifier = $this->normalizeIdentifier($identifier, $channelType);

        foreach ($this->searchableModels as $modelClass) {
            if (! class_exists($modelClass)) {
                continue;
            }

            $contact = $this->searchInModel($modelClass, $cleanIdentifier, $channelType);

            if ($contact) {
                return $contact;
            }
        }

        return null;
    }

    /**
     * Search for contact in a specific model.
     */
    protected function searchInModel(string $modelClass, string $identifier, ChannelTypeEnum $channelType): ?Model
    {
        $query = $modelClass::query();

        // Search by phone fields for WhatsApp/Telegram
        if (in_array($channelType, [ChannelTypeEnum::WHATSAPP, ChannelTypeEnum::TELEGRAM])) {
            $phoneFields = ['phone', 'mobile', 'full_mobile', 'whatsapp_number'];

            $query->where(function ($q) use ($phoneFields, $identifier) {
                foreach ($phoneFields as $field) {
                    // Check if column exists
                    try {
                        $q->orWhere($field, 'LIKE', '%'.$identifier);
                        $q->orWhere($field, 'LIKE', '%'.ltrim($identifier, '+'));
                    } catch (\Exception $e) {
                        // Column doesn't exist, skip
                        continue;
                    }
                }
            });
        }

        // For webchat, search by session or user relation
        if ($channelType === ChannelTypeEnum::WEBCHAT) {
            // Check if identifier matches a user ID or session
            if (method_exists($modelClass, 'user')) {
                $query->orWhereHas('user', function ($q) use ($identifier) {
                    $q->where('id', $identifier);
                });
            }
        }

        return $query->first();
    }

    /**
     * Normalize phone number or identifier.
     */
    protected function normalizeIdentifier(string $identifier, ChannelTypeEnum $channelType): string
    {
        if (in_array($channelType, [ChannelTypeEnum::WHATSAPP, ChannelTypeEnum::TELEGRAM])) {
            // Remove common prefixes and non-numeric characters
            $clean = preg_replace('/[^0-9]/', '', $identifier);

            // Remove country code if it's just the last 9-10 digits needed
            if (strlen($clean) > 10) {
                return substr($clean, -10);
            }

            return $clean;
        }

        return $identifier;
    }

    /**
     * Create a new lead from conversation.
     */
    public function createLeadFromConversation(Conversation $conversation): ?Lead
    {
        if (! class_exists(Lead::class)) {
            return null;
        }

        try {
            $lead = Lead::create([
                'full_mobile' => $conversation->participant_identifier,
                'name' => $conversation->participant_name ?? 'Unknown',
                'source' => 'messaging_'.$conversation->channel->type->value,
                'status' => 'new',
            ]);

            $conversation->update([
                'conversable_type' => Lead::class,
                'conversable_id' => $lead->id,
            ]);

            Log::channel('messaging')->info('Lead created from conversation', [
                'conversation_id' => $conversation->id,
                'lead_id' => $lead->id,
            ]);

            return $lead;
        } catch (\Exception $e) {
            Log::channel('messaging')->error('Failed to create lead from conversation', [
                'conversation_id' => $conversation->id,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Manually link a conversation to a contact.
     */
    public function linkToContact(Conversation $conversation, Model $contact): bool
    {
        try {
            $conversation->update([
                'conversable_type' => get_class($contact),
                'conversable_id' => $contact->id,
            ]);

            Log::channel('messaging')->info('Conversation manually linked', [
                'conversation_id' => $conversation->id,
                'contact_type' => get_class($contact),
                'contact_id' => $contact->id,
            ]);

            return true;
        } catch (\Exception $e) {
            Log::channel('messaging')->error('Failed to link conversation', [
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Unlink a conversation from its contact.
     */
    public function unlinkConversation(Conversation $conversation): bool
    {
        try {
            $conversation->update([
                'conversable_type' => null,
                'conversable_id' => null,
            ]);

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}
