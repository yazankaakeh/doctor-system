<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

/**
 * Per-user notification channel. The `{hash}` segment is the first 10
 * chars of md5 of the authenticated user's class, so the channel is
 * partitioned by morph type (Admin / Doctor / Patient / User).
 *
 * Authorization succeeds only when:
 *   - the id matches the current user, AND
 *   - the hash matches the class of the current user.
 * This prevents cross-guard subscription (e.g. Patient trying to
 * subscribe to Admin's channel with the same id).
 */
Broadcast::channel('notifications.{hash}.{id}', function ($user, string $hash, int $id) {
    if ((int) $user->id !== (int) $id) {
        return false;
    }

    $expected = substr(md5(get_class($user)), 0, 10);

    return hash_equals($expected, $hash);
});
