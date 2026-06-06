<?php

namespace App\Helpers;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;

/**
 * Small utility for shaping paginator metadata into the consistent
 * structure used across the API responses.
 */
class PaginationHelper
{
    /**
     * Build the standard pagination payload from a paginator instance.
     *
     * @return array{total: int, current_page: int, total_page: int}
     */
    public static function format(LengthAwarePaginator $paginator): array
    {
        return [
            'total' => $paginator->total(),
            'current_page' => $paginator->currentPage(),
            'total_page' => $paginator->lastPage(),
        ];
    }
}
