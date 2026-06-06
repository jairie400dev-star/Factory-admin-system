<?php

namespace App\Services;

use App\Models\User;
use Carbon\Carbon;

/**
 * Reads and parses the "Model event logger" entries that ModelEventService
 * writes to the application log file.
 *
 * Centralising the parsing here keeps the controllers thin and avoids the
 * log-format logic being duplicated between the dashboard and log endpoints.
 */
class ActivityLogService
{
    /**
     * Marker string used to identify activity log lines.
     */
    private const MARKER = 'Model event logger';

    /**
     * Parse every activity log entry from the log file in chronological order.
     *
     * Each entry is the decoded JSON payload, enriched with an ISO-8601
     * "timestamp" key when the log line carries a readable timestamp.
     *
     * @return array<int, array<string, mixed>>
     */
    public function parse(): array
    {
        $logPath = storage_path('logs/laravel.log');

        if (! file_exists($logPath)) {
            return [];
        }

        $lines = array_filter(array_map('trim', explode("\n", file_get_contents($logPath))));

        $events = [];

        foreach ($lines as $line) {
            // Skip anything that is not one of our model event log lines.
            if (strpos($line, self::MARKER) === false) {
                continue;
            }

            // Typical Laravel log format:
            // [2026-06-05 12:34:56] local.INFO: Model event logger {"action":"updated",...}
            if (! preg_match('/^\[([^\]]+)\].*' . self::MARKER . '.*(\{.*\})$/', $line, $matches)) {
                continue;
            }

            $payload = json_decode($matches[2], true);

            if (json_last_error() !== JSON_ERROR_NONE || ! is_array($payload)) {
                continue;
            }

            $payload['timestamp'] = $this->normaliseTimestamp($matches[1]);
            $events[] = $payload;
        }

        return $events;
    }

    /**
     * Attach the acting user's email to each log entry by resolving the
     * stored user_id. Emails are fetched in a single query to avoid N+1.
     *
     * @param  array<int, array<string, mixed>>  $logs
     * @return array<int, array<string, mixed>>
     */
    public function withUserEmails(array $logs): array
    {
        $userIds = array_filter(array_unique(array_column($logs, 'user_id')));

        $emailsById = $userIds
            ? User::whereIn('id', $userIds)->pluck('email', 'id')->all()
            : [];

        foreach ($logs as &$log) {
            $log['user_email'] = $emailsById[$log['user_id'] ?? null] ?? null;
        }
        unset($log);

        return $logs;
    }

    /**
     * Return the most recent activity entries (newest first), each normalised
     * to a compact shape with a human-readable "time_ago" value.
     *
     * @return array<int, array<string, mixed>>
     */
    public function recent(int $limit = 5): array
    {
        $events = array_reverse($this->parse());
        $events = array_slice($events, 0, $limit);

        return array_map(function (array $event): array {
            return [
                'action' => $event['action'] ?? null,
                'model' => $event['model'] ?? null,
                'record_id' => $event['record_id'] ?? null,
                'user_id' => $event['user_id'] ?? null,
                'changes' => $event['changes'] ?? null,
                'time_ago' => $this->timeAgo($event['timestamp'] ?? null),
            ];
        }, $events);
    }

    /**
     * Total number of activity log entries recorded.
     */
    public function count(): int
    {
        return count($this->parse());
    }

    /**
     * Convert a raw "Y-m-d H:i:s" log timestamp into an ISO-8601 string,
     * falling back to the raw value when it cannot be parsed.
     */
    private function normaliseTimestamp(string $raw): string
    {
        try {
            return Carbon::createFromFormat('Y-m-d H:i:s', $raw)->toIso8601String();
        } catch (\Exception $e) {
            return $raw;
        }
    }

    /**
     * Produce a "2 minutes ago" style string for the given timestamp.
     */
    private function timeAgo(?string $timestamp): ?string
    {
        if (empty($timestamp)) {
            return null;
        }

        try {
            return Carbon::parse($timestamp)->diffForHumans();
        } catch (\Exception $e) {
            return null;
        }
    }
}
