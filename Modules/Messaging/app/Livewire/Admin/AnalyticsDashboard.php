<?php

namespace Modules\Messaging\Livewire\Admin;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Modules\Messaging\Models\Conversation;
use Modules\Messaging\Models\Message;

class AnalyticsDashboard extends Component
{
    public string $period = '7days';

    public array $periodOptions = [
        '7days' => 'Last 7 Days',
        '30days' => 'Last 30 Days',
        '90days' => 'Last 90 Days',
    ];

    public array $stats = [];

    public array $messagesByChannel = [];

    public array $conversationsByDay = [];

    public array $responseTimesByAgent = [];

    public function mount(): void
    {
        $this->loadStats();
    }

    public function updatedPeriod(): void
    {
        $this->loadStats();
    }

    protected function getDateRange(): array
    {
        $end = now();

        $start = match ($this->period) {
            '7days' => now()->subDays(7),
            '30days' => now()->subDays(30),
            '90days' => now()->subDays(90),
            default => now()->subDays(7),
        };

        return [$start, $end];
    }

    public function loadStats(): void
    {
        [$start, $end] = $this->getDateRange();

        // General stats
        $this->stats = [
            'total_conversations' => Conversation::whereBetween('created_at', [$start, $end])->count(),
            'total_messages' => Message::whereBetween('created_at', [$start, $end])->count(),
            'inbound_messages' => Message::whereBetween('created_at', [$start, $end])
                ->where('direction', 'inbound')->count(),
            'outbound_messages' => Message::whereBetween('created_at', [$start, $end])
                ->where('direction', 'outbound')->count(),
            'avg_response_time' => $this->calculateAverageResponseTime($start, $end),
            'resolved_conversations' => Conversation::whereBetween('updated_at', [$start, $end])
                ->where('status', 'resolved')->count(),
        ];

        // Messages by channel
        $this->messagesByChannel = Message::query()
            ->join('messaging_conversations', 'messaging_messages.conversation_id', '=', 'messaging_conversations.id')
            ->join('messaging_channels', 'messaging_conversations.channel_id', '=', 'messaging_channels.id')
            ->whereBetween('messaging_messages.created_at', [$start, $end])
            ->select('messaging_channels.type', DB::raw('count(*) as count'))
            ->groupBy('messaging_channels.type')
            ->pluck('count', 'type')
            ->toArray();

        // Conversations by day
        $this->conversationsByDay = Conversation::query()
            ->whereBetween('created_at', [$start, $end])
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as count'))
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderBy('date')
            ->pluck('count', 'date')
            ->toArray();

        // Response times by agent
        $this->responseTimesByAgent = $this->calculateResponseTimesByAgent($start, $end);
    }

    protected function calculateAverageResponseTime(Carbon $start, Carbon $end): ?string
    {
        // Get conversations with their first inbound and first outbound message times
        $avgSeconds = DB::table('messaging_messages as m1')
            ->join('messaging_messages as m2', function ($join) {
                $join->on('m1.conversation_id', '=', 'm2.conversation_id')
                    ->where('m2.direction', '=', 'outbound')
                    ->whereRaw('m2.created_at > m1.created_at');
            })
            ->where('m1.direction', 'inbound')
            ->whereBetween('m1.created_at', [$start, $end])
            ->selectRaw('AVG(TIMESTAMPDIFF(SECOND, m1.created_at, MIN(m2.created_at))) as avg_response')
            ->groupBy('m1.id')
            ->avg('avg_response');

        if (! $avgSeconds) {
            return null;
        }

        // Convert to human readable
        if ($avgSeconds < 60) {
            return round($avgSeconds).'s';
        }

        if ($avgSeconds < 3600) {
            return round($avgSeconds / 60).'m';
        }

        return round($avgSeconds / 3600, 1).'h';
    }

    protected function calculateResponseTimesByAgent(Carbon $start, Carbon $end): array
    {
        return DB::table('messaging_messages as m')
            ->join('users', 'm.sender_id', '=', 'users.id')
            ->where('m.direction', 'outbound')
            ->where('m.sender_type', 'user')
            ->whereBetween('m.created_at', [$start, $end])
            ->select('users.name', DB::raw('count(*) as message_count'))
            ->groupBy('users.id', 'users.name')
            ->orderByDesc('message_count')
            ->limit(10)
            ->get()
            ->toArray();
    }

    public function render()
    {
        return view('messaging::livewire.admin.analytics-dashboard')
            ->layout('messaging::layouts.admin');
    }
}
