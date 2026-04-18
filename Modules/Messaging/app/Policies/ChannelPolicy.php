<?php

namespace Modules\Messaging\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Modules\Messaging\Models\Channel;

class ChannelPolicy
{
    use HandlesAuthorization;

    /**
     * Determine if the user can access this channel.
     */
    public function access(User $user, Channel $channel): bool
    {
        // Admin-only channels require admin role
        if ($channel->is_admin_only) {
            return $user->isAdmin();
        }

        return true;
    }

    /**
     * Determine if the user can view the channel.
     */
    public function view(User $user, Channel $channel): bool
    {
        return $this->access($user, $channel);
    }

    /**
     * Determine if the user can send messages on this channel.
     */
    public function send(User $user, Channel $channel): bool
    {
        if (! $channel->is_active) {
            return false;
        }

        return $this->access($user, $channel);
    }

    /**
     * Determine if the user can manage (configure) the channel.
     */
    public function manage(User $user, Channel $channel): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine if the user can view any channels.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine if the user can create channels.
     */
    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine if the user can update the channel.
     */
    public function update(User $user, Channel $channel): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine if the user can delete the channel.
     */
    public function delete(User $user, Channel $channel): bool
    {
        return $user->isAdmin();
    }
}
