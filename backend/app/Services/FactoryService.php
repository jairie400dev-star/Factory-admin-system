<?php

namespace App\Services;

use App\Models\Factory;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

/**
 * Encapsulates all Eloquent/data-access logic for factories so the
 * controller only deals with the request/response cycle.
 */
class FactoryService
{
    /**
     * Return a paginated, optionally filtered list of factories.
     *
     * Supported filters:
     * - search: matches factory_name or location (partial)
     *
     * @param  array<string, mixed>  $filters
     */
    public function paginate(array $filters, int $perPage = 10): LengthAwarePaginator
    {
        $query = Factory::query();

        if ($search = trim((string) ($filters['search'] ?? ''))) {
            $query->where(function ($subQuery) use ($search) {
                $subQuery->where('factory_name', 'LIKE', "%{$search}%")
                    ->orWhere('location', 'LIKE', "%{$search}%");
            });
        }

        // withCount keeps the employee tally available for the DTO; newest records first.
        return $query->withCount('employees')->latest()->paginate($perPage);
    }

    /**
     * Persist a new factory record.
     *
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Factory
    {
        return Factory::create($data);
    }

    /**
     * Update an existing factory record.
     *
     * @param  array<string, mixed>  $data
     */
    public function update(Factory $factory, array $data): Factory
    {
        $factory->update($data);

        return $factory;
    }

    /**
     * Delete the given factory record.
     */
    public function delete(Factory $factory): void
    {
        $factory->delete();
    }
}
