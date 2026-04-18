<?php

namespace Modules\AdminManagement\Http\Controllers;

use App\Enum\Pagination;
use App\Http\Controllers\Controller;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\AdminManagement\Models\AuditLog;

class AuditLogController extends Controller
{
    public function index(Request $request): Factory|View|Application
    {
        $data = AuditLog::query()->with('auditable');
        $data = AuditLog::IndexFilter($data, $request->all());
        $data = $data->orderByDesc('created_at')->paginate(Pagination::PAG->value);

        // Get auditable types for filter dropdown
        $auditableTypes = AuditLog::GetAuditableTypes();

        return view('adminmanagement::audit_log.index', compact('data', 'auditableTypes'));
    }

    public function getPayload($id): JsonResponse
    {
        $auditing = AuditLog::query()->find($id);

        if (! $auditing) {
            return response()->json([
                'status' => false,
                'message' => 'Audit log not found.',
            ], 404);
        }

        $rawPayload = is_array($auditing->payload)
            ? $auditing->payload
            : (json_decode((string) $auditing->payload, true) ?: []);

        $payload_html = "<table class='table table-vcenter' style='direction: ltr !important;'>";
        foreach ($rawPayload as $index => $payload) {
            if (is_string($payload) && $index !== '_token') {
                $payload_html .= '<tr>';
                $payload_html .= "<td>{$index} : </td><td>{$payload}</td>";
                $payload_html .= '</tr>';
            }
        }
        $payload_html .= '</table>';

        return response()->json([
            'status' => true,
            // Both keys are returned so legacy callers (which read `payload`)
            // and the test suite (which reads `html`) keep working.
            'html' => $payload_html,
            'payload' => $payload_html,
        ]);
    }

    /**
     * Get audit logs for a specific auditable model
     */
    public function getAuditLogsForModel(Request $request): JsonResponse
    {
        $auditableType = $request->get('auditable_type');
        $auditableId = $request->get('auditable_id');

        if (! $auditableType || ! $auditableId) {
            return response()->json(['error' => 'Missing auditable_type or auditable_id'], 400);
        }

        $auditLogs = AuditLog::query()
            ->where('auditable_type', $auditableType)
            ->where('auditable_id', $auditableId)
            ->with('auditable')
            ->orderByDesc('created_at')
            ->paginate(15);

        return response()->json($auditLogs);
    }

    /**
     * Get audit log statistics
     */
    public function getStatistics(): JsonResponse
    {
        $stats = [
            'total_logs' => AuditLog::count(),
            'logs_by_type' => AuditLog::selectRaw('auditable_type, COUNT(*) as count')
                ->groupBy('auditable_type')
                ->get(),
            'logs_by_method' => AuditLog::selectRaw('method, COUNT(*) as count')
                ->groupBy('method')
                ->get(),
            'recent_activity' => AuditLog::with('auditable')
                ->latest()
                ->limit(10)
                ->get(),
        ];

        return response()->json($stats);
    }
}
