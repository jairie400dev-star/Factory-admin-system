<?php

namespace App\Http\Controllers;

use App\DTO\FactoryData;
use App\Helpers\PaginationHelper;
use App\Http\Requests\StoreFactoryRequest;
use App\Http\Requests\UpdateFactoryRequest;
use App\Models\Factory;
use App\Services\FactoryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Throwable;

class FactoryController extends Controller
{
    public function __construct(
        private FactoryService $factoryService,
    ) {}

    /**
     * Display a paginated listing of factories.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            // Querying is delegated to the service; the controller shapes the response.
            $factories = $this->factoryService->paginate(
                $request->only(['search'])
            );

            $data = $factories->getCollection()
                ->map(fn (Factory $factory) => FactoryData::fromModel($factory)->toArray());

            return response()->json([
                'success' => true,
                'pagination' => PaginationHelper::format($factories),
                'data' => $data,
            ]);
        } catch (Throwable $exception) {
            Log::error('Factory index error', ['exception' => $exception]);

            return response()->json([
                'success' => false,
                'message' => 'Unable to list factories at this time.',
            ], 500);
        }
    }

    /**
     * Store a newly created factory.
     */
    public function store(StoreFactoryRequest $request): JsonResponse
    {
        try {
            $factory = $this->factoryService->create($request->validated());

            return response()->json([
                'success' => true,
                'data' => FactoryData::fromModel($factory)->toArray(),
            ], 201);
        } catch (Throwable $exception) {
            Log::error('Factory store error', ['exception' => $exception]);

            return response()->json([
                'success' => false,
                'message' => 'Unable to create factory.',
            ], 500);
        }
    }

    /**
     * Display the specified factory.
     */
    public function show(Factory $factory): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => FactoryData::fromModel($factory)->toArray(),
        ]);
    }

    /**
     * Update the specified factory.
     */
    public function update(UpdateFactoryRequest $request, Factory $factory): JsonResponse
    {
        try {
            $factory = $this->factoryService->update($factory, $request->validated());

            return response()->json([
                'success' => true,
                'data' => FactoryData::fromModel($factory)->toArray(),
            ]);
        } catch (Throwable $exception) {
            Log::error('Factory update error', ['exception' => $exception]);

            return response()->json([
                'success' => false,
                'message' => 'Unable to update factory.',
            ], 500);
        }
    }

    /**
     * Remove the specified factory.
     */
    public function destroy(Factory $factory): JsonResponse
    {
        try {
            $this->factoryService->delete($factory);

            return response()->json([
                'success' => true,
                'message' => 'Factory deleted successfully.',
            ]);
        } catch (Throwable $exception) {
            Log::error('Factory destroy error', ['exception' => $exception]);

            return response()->json([
                'success' => false,
                'message' => 'Unable to delete factory.',
            ], 500);
        }
    }
}
