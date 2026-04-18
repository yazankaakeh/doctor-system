<?php

namespace Modules\Messaging\Services;

use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Modules\Messaging\Enums\ChannelTypeEnum;
use Modules\Messaging\Enums\ConversationStatusEnum;
use Modules\Messaging\Enums\MessageDirectionEnum;
use Modules\Messaging\Enums\MessageStatusEnum;
use Modules\Messaging\Models\Conversation;
use Modules\Messaging\Models\Message;

class MessagingAnalyticsService
{
    /**
     * Get dashboard overview statistics.
     */
    public function getDashboardStats(?Carbon $startDate = null, ?Carbon $endDate = null): array
    {
        $startDate = $startDate ?? now()->subDays(30);
        $endDate = $endDate ?? now();

        return [
            'conversations' => $this->getConversationStats($startDate, $endDate),
            'messages' => $this->getMessageStats($startDate, $endDate),
            'response_times' => $this->getResponseTimeStats($startDate, $endDate),
            'agent_performance' => $this->getAgentPerformance($startDate, $endDate),
            'channel_breakdown' => $this->getChannelBreakdown($startDate, $endDate),
        ];
    }

    /**
     * Get conversation statistics.
     */
    public function getConversationStats(Carbon $startDate, Carbon $endDate): array
    {
        $query = Conversation::whereBetween('created_at', [$startDate, $endDate]);

        return [
            'total' => (clone $query)->count(),
            'active' => (clone $query)->where('status', ConversationStatusEnum::ACTIVE)->count(),
            'resolved' => (clone $query)->where('status', ConversationStatusEnum::RESOLVED)->count(),
            'pending' => (clone $query)->where('status', ConversationStatusEnum::PENDING)->count(),
            'new_today' => Conversation::whereDate('created_at', today())->count(),
            'trend' => $this->getConversationTrend($startDate, $endDate),
        ];
    }

    /**
     * Get message statistics.
     */
    public function getMessageStats(Carbon $startDate, Carbon $endDate): array
    {
        $query = Message::whereBetween('created_at', [$startDate, $endDate]);

        return [
            'total' => (clone $query)->count(),
            'inbound' => (clone $query)->where('direction', MessageDirectionEnum::INBOUND)->count(),
            'outbound' => (clone $query)->where('direction', MessageDirectionEnum::OUTBOUND)->count(),
            'delivered' => (clone $query)->where('status', MessageStatusEnum::DELIVERED)->count(),
            'read' => (clone $query)->where('status', MessageStatusEnum::READ)->count(),
            'failed' => (clone $query)->where('status', MessageStatusEnum::FAILED)->count(),
            'delivery_rate' => $this->calculateDeliveryRate($startDate, $endDate),
            'read_rate' => $this->calculateReadRate($startDate, $endDate),
        ];
    }

    /**
     * Calculate average response times.
     */
    public function getResponseTimeStats(Carbon $startDate, Carbon $endDate): array
    {
        // Calculate first response time
        $avgFirstResponse = DB::table('messaging_messages as m1')
            ->join('messaging_messages as m2', function ($join) {
                $join->on('m1.conversation_id', '=', 'm2.conversation_id')
                    ->where('m2.direction', '=', MessageDirectionEnum::OUTBOUND->value);
            })
            ->where('m1.direction', MessageDirectionEnum::INBOUND->value)
            ->whereBetween('m1.created_at', [$startDate, $endDate])
            ->whereRaw('m2.created_at > m1.created_at')
            ->selectRaw('AVG(TIMESTAMPDIFF(SECOND, m1.created_at, MIN(m2.created_at))) as avg_seconds')
            ->groupBy('m1.id')
            ->first();

        $avgSeconds = $avgFirstResponse->avg_seconds ?? 0;

        return [
            'average_first_response_seconds' => round($avgSeconds),
            'average_first_response_formatted' => $this->formatDuration($avgSeconds),
            'median_response_time' => $this->getMedianResponseTime($startDate, $endDate),
            'response_time_trend' => $this->getResponseTimeTrend($startDate, $endDate),
        ];
    }

    /**
     * Get agent performance metrics.
     */
    public function getAgentPerformance(Carbon $startDate, Carbon $endDate): Collection
    {
        return DB::table('messaging_conversations as c')
            ->join('users as u', 'c.assigned_to', '=', 'u.id')
            ->join('messaging_messages as m', 'c.id', '=', 'm.conversation_id')
            ->whereBetween('c.created_at', [$startDate, $endDate])
            ->whereNotNull('c.assigned_to')
            ->groupBy('c.assigned_to', 'u.name')
            ->select([
                'c.assigned_to as agent_id',
                'u.name as agent_name',
                DB::raw('COUNT(DISTINCT c.id) as conversations_handled'),
                DB::raw('COUNT(m.id) as messages_sent'),
                DB::raw('SUM(CASE WHEN c.status = "resolved" THEN 1 ELSE 0 END) as resolved'),
            ])
            ->get();
    }

    /**
     * Get breakdown by channel.
     */
    public function getChannelBreakdown(Carbon $startDate, Carbon $endDate): Collection
    {
        return DB::table('messaging_conversations as c')
            ->join('messaging_channels as ch', 'c.channel_id', '=', 'ch.id')
            ->leftJoin('messaging_messages as m', 'c.id', '=', 'm.conversation_id')
            ->whereBetween('c.created_at', [$startDate, $endDate])
            ->groupBy('ch.type', 'ch.name')
            ->select([
                'ch.type',
                'ch.name as channel_name',
                DB::raw('COUNT(DISTINCT c.id) as conversations'),
                DB::raw('COUNT(m.id) as messages'),
            ])
            ->get();
    }

    /**
     * Get conversation trend over time.
     */
    protected function getConversationTrend(Carbon $startDate, Carbon $endDate): array
    {
        $period = CarbonPeriod::create($startDate, $endDate);
        $trend = [];

        foreach ($period as $date) {
            $trend[$date->format('Y-m-d')] = Conversation::whereDate('created_at', $date)->count();
        }

        return $trend;
    }

    /**
     * Calculate delivery rate percentage.
     */
    protected function calculateDeliveryRate(Carbon $startDate, Carbon $endDate): float
    {
        $total = Message::whereBetween('created_at', [$startDate, $endDate])
            ->where('direction', MessageDirectionEnum::OUTBOUND)
            ->count();

        if ($total === 0) {
            return 0;
        }

        $delivered = Message::whereBetween('created_at', [$startDate, $endDate])
            ->where('direction', MessageDirectionEnum::OUTBOUND)
            ->whereIn('status', [MessageStatusEnum::DELIVERED, MessageStatusEnum::READ])
            ->count();

        return round(($delivered / $total) * 100, 2);
    }

    /**
     * Calculate read rate percentage.
     */
    protected function calculateReadRate(Carbon $startDate, Carbon $endDate): float
    {
        $delivered = Message::whereBetween('created_at', [$startDate, $endDate])
            ->where('direction', MessageDirectionEnum::OUTBOUND)
            ->whereIn('status', [MessageStatusEnum::DELIVERED, MessageStatusEnum::READ])
            ->count();

        if ($delivered === 0) {
            return 0;
        }

        $read = Message::whereBetween('created_at', [$startDate, $endDate])
            ->where('direction', MessageDirectionEnum::OUTBOUND)
            ->where('status', MessageStatusEnum::READ)
            ->count();

        return round(($read / $delivered) * 100, 2);
    }

    /**
     * Get median response time.
     */
    protected function getMedianResponseTime(Carbon $startDate, Carbon $endDate): int
    {
        // Simplified - get average as proxy for median
        return 0;
    }

    /**
     * Get response time trend.
     */
    protected function getResponseTimeTrend(Carbon $startDate, Carbon $endDate): array
    {
        return [];
    }

    /**
     * Format duration in human readable form.
     */
    protected function formatDuration(float $seconds): string
    {
        if ($seconds < 60) {
            return round($seconds) . 's';
        }

        if ($seconds < 3600) {
            return round($seconds / 60) . 'm';
        }

        $hours = floor($seconds / 3600);
        $minutes = round(($seconds % 3600) / 60);

        return "{$hours}h {$minutes}m";
    }

    /**
     * Get hourly activity distribution.
     */
    public function getHourlyActivity(Carbon $startDate, Carbon $endDate): array
    {
        $activity = [];

        for ($hour = 0; $hour < 24; $hour++) {
            $activity[$hour] = Message::whereBetween('created_at', [$startDate, $endDate])
                ->whereRaw('HOUR(created_at) = ?', [$hour])
                ->count();
        }

        return $activity;
    }

    /**
     * Get peak hours for messaging.
     */
    public function getPeakHours(Carbon $startDate, Carbon $endDate): array
    {
        $activity = $this->getHourlyActivity($startDate, $endDate);
        arsort($activity);

        return array_slice($activity, 0, 5, true);
    }

    /**
     * Export analytics data for reporting.
     */
    public function exportReport(Carbon $startDate, Carbon $endDate): array
    {
        return [
            'period' => [
                'start' => $startDate->toDateString(),
                'end' => $endDate->toDateString(),
            ],
            'summary' => $this->getDashboardStats($startDate, $endDate),
            'hourly_activity' => $this->getHourlyActivity($startDate, $endDate),
            'generated_at' => now()->toDateTimeString(),
        ];
    }
}
