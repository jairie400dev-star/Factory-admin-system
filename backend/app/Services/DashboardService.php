<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\Factory;

/**
 * Builds the aggregate statistics shown on the admin dashboard.
 */
class DashboardService
{
    public function __construct(
        private ActivityLogService $activityLog,
    ) {}

    /**
     * Assemble the full set of dashboard metrics.
     *
     * @return array<string, mixed>
     */
    public function stats(): array
    {
        $factoriesCount = Factory::count();
        $employeesCount = Employee::count();

        return [
            'factories' => $factoriesCount,
            'employees' => $employeesCount,
            'logged_events' => $this->activityLog->count(),
            'avg_per_factory' => $this->averageEmployeesPerFactory($employeesCount, $factoriesCount),
            'top_factories' => $this->topFactories(),
            'recent_activity' => $this->activityLog->recent(5),
        ];
    }

    /**
     * Average headcount per factory, guarding against division by zero.
     */
    private function averageEmployeesPerFactory(int $employees, int $factories): float
    {
        return $factories > 0 ? round($employees / $factories, 1) : 0.0;
    }

    /**
     * The five factories with the highest employee headcount.
     *
     * @return array<int, array<string, mixed>>
     */
    private function topFactories(): array
    {
        return Factory::withCount('employees')
            ->orderByDesc('employees_count')
            ->take(5)
            ->get(['id', 'factory_name'])
            ->map(fn (Factory $factory) => [
                'id' => $factory->id,
                'factory_name' => $factory->factory_name,
                'employees_count' => $factory->employees_count,
            ])
            ->values()
            ->all();
    }
}
