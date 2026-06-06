<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\Factory;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

/**
 * Encapsulates all Eloquent/data-access logic for employees so the
 * controller only deals with the request/response cycle.
 */
class EmployeeService
{
    /**
     * Return a paginated, optionally filtered list of employees.
     *
     * Supported filters:
     * - search:     matches firstname or lastname (partial)
     * - factory_id: restricts to a single factory
     *
     * @param  array<string, mixed>  $filters
     */
    public function paginate(array $filters, int $perPage = 10): LengthAwarePaginator
    {
        // Eager load the factory relation to avoid N+1 when mapping DTOs.
        $query = Employee::query()->with('factorys');

        if ($search = trim((string) ($filters['search'] ?? ''))) {
            $query->where(function ($subQuery) use ($search) {
                $subQuery->where('firstname', 'LIKE', "%{$search}%")
                    ->orWhere('lastname', 'LIKE', "%{$search}%");
            });
        }

        if (! empty($filters['factory_id'])) {
            $query->where('factory_id', $filters['factory_id']);
        }

        // Newest records first.
        return $query->latest()->paginate($perPage);
    }

    /**
     * Lightweight factory list (id + name) used to populate dropdowns.
     */
    public function factoryOptions(): Collection
    {
        return Factory::query()
            ->select(['id', 'factory_name'])
            ->orderBy('factory_name')
            ->get();
    }

    /**
     * Persist a new employee record.
     *
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Employee
    {
        return Employee::create($data);
    }

    /**
     * Update an existing employee record.
     *
     * @param  array<string, mixed>  $data
     */
    public function update(Employee $employee, array $data): Employee
    {
        $employee->update($data);

        return $employee;
    }

    /**
     * Delete the given employee record.
     */
    public function delete(Employee $employee): void
    {
        $employee->delete();
    }
}
