<?php

namespace Modules\Messaging\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Modules\Messaging\Enums\ChannelTypeEnum;
use Modules\Messaging\Models\Channel;

class AdminOnlyChannelMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, ?string $channelParam = null)
    {
        $user = $request->user();

        if (! $user) {
            abort(401, 'Unauthorized');
        }

        // Get channel from route parameter or request
        $channel = $this->resolveChannel($request, $channelParam);

        if (! $channel) {
            return $next($request);
        }

        // Check if channel is admin-only and user is not admin
        if ($channel->is_admin_only && ! $user->isAdmin()) {
            abort(403, 'This channel is restricted to administrators.');
        }

        return $next($request);
    }

    /**
     * Resolve the channel from the request.
     */
    protected function resolveChannel(Request $request, ?string $channelParam): ?Channel
    {
        // Try route parameter
        if ($channelParam) {
            $value = $request->route($channelParam);

            if ($value instanceof Channel) {
                return $value;
            }

            if (is_numeric($value)) {
                return Channel::find($value);
            }

            // Try as channel type
            $type = ChannelTypeEnum::tryFrom($value);
            if ($type) {
                return Channel::where('type', $type)->first();
            }
        }

        // Try common route parameters
        $channel = $request->route('channel');
        if ($channel instanceof Channel) {
            return $channel;
        }

        // Try request input
        $channelId = $request->input('channel_id');
        if ($channelId) {
            return Channel::find($channelId);
        }

        $channelType = $request->input('channel_type');
        if ($channelType) {
            $type = ChannelTypeEnum::tryFrom($channelType);
            if ($type) {
                return Channel::where('type', $type)->first();
            }
        }

        return null;
    }
}
