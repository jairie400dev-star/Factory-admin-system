<?php

namespace App\Http\Controllers;

use App\Services\ActivityLogService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

class LogController extends Controller
{
    public function __construct(
        private ActivityLogService $activityLogService,
    ) {}

    /**
     * Get model activity logs.
     *
     * Query params:
     * - model:  Filter by model name (Factory, Employee)
     * - action: Filter by action (created, updated, deleted)
     */
    public function index(Request $request): JsonResponse
    {
        try {
            // Parse the log file and attach the acting user's email to each entry.
            $logs = $this->activityLogService->parse();
            $logs = $this->activityLogService->withUserEmails($logs);

            // Optional filtering by model and/or action.
            if ($model = $request->input('model')) {
                $logs = array_filter($logs, fn ($log) => ($log['model'] ?? null) === $model);
            }

            if ($action = $request->input('action')) {
                $logs = array_filter($logs, fn ($log) => ($log['action'] ?? null) === $action);
            }

            // Newest entries first.
            $logs = array_reverse(array_values($logs));

            return response()->json([
                'success' => true,
                'total' => count($logs),
                'data' => $logs,
            ]);
        } catch (Throwable $exception) {
            return response()->json([
                'success' => false,
                'message' => 'Unable to retrieve logs.',
            ], 500);
        }
    }
}
