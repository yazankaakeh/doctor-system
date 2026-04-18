<?php

namespace Modules\Messaging\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Modules\Messaging\Models\Conversation;

class ConversationPolicy
{
    use HandlesAuthorization;

    /**
     * Determine if the user can view the conversation.
     */
    public function view(User $user, Conversation $conversation): bool
    {
        // Admins can view all conversations
        if ($user->isAdmin()) {
            return true;
        }

        // Check if channel is admin-only
        if ($conversation->channel->is_admin_only) {
            return $user->isAdmin();
        }

        // Assigned user can view
        if ($conversation->assigned_user_id === $user->id) {
            return true;
        }

        // Check if user is team lead and conversation is assigned to team member
        if (method_exists($user, 'isTeamLeader') && $user->isTeamLeader()) {
            $teamMemberIds = $user->getTeamMemberIds();
            if (in_array($conversation->assigned_user_id, $teamMemberIds)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Determine if the user can send a message in the conversation.
     */
    public function sendMessage(User $user, Conversation $conversation): bool
    {
        // Check channel access first
        if ($conversation->channel->is_admin_only && ! $user->isAdmin()) {
            return false;
        }

        // Can't send to closed conversations
        if (! $conversation->canSendMessage()) {
            return false;
        }

        return $this->view($user, $conversation);
    }

    /**
     * Determine if the user can assign the conversation.
     */
    public function assign(User $user, Conversation $conversation): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine if the user can change the conversation status.
     */
    public function changeStatus(User $user, Conversation $conversation): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        // Assigned user can change status
        return $conversation->assigned_user_id === $user->id;
    }

    /**
     * Determine if the user can view any conversations.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine if the user can create conversations.
     */
    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine if the user can update the conversation.
     */
    public function update(User $user, Conversation $conversation): bool
    {
        return $this->view($user, $conversation);
    }

    /**
     * Determine if the user can delete the conversation.
     */
    public function delete(User $user, Conversation $conversation): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine if the user can add notes to the conversation.
     */
    public function addNote(User $user, Conversation $conversation): bool
    {
        return $this->view($user, $conversation);
    }
}
