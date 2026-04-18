<?php

namespace Modules\Messaging\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Modules\Messaging\Models\Message;

class MessagePolicy
{
    use HandlesAuthorization;

    /**
     * Determine if the user can view the message.
     */
    public function view(User $user, Message $message): bool
    {
        // If user can view the conversation, they can view its messages
        return $user->can('view', $message->conversation);
    }

    /**
     * Determine if the user can retry sending the message.
     */
    public function retry(User $user, Message $message): bool
    {
        // Only failed messages can be retried
        if (! $message->canRetry()) {
            return false;
        }

        return $user->can('sendMessage', $message->conversation);
    }

    /**
     * Determine if the user can delete the message.
     */
    public function delete(User $user, Message $message): bool
    {
        // Admins can delete any message
        if ($user->isAdmin()) {
            return true;
        }

        // Users can only delete their own outbound messages
        return $message->sender_id === $user->id;
    }

    /**
     * Determine if the user can view any messages.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }
}
