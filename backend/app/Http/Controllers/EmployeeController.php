<?php

namespace App\Http\Controllers;

use App\DTO\EmployeeData;
use App\Helpers\PaginationHelper;
use App\Http\Requests\StoreEmployeeRequest;
use App\Http\Requests\UpdateEmployeeRequest;
use App\Models\Employee;
use App\Services\EmployeeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Throwable;

class EmployeeController extends Controller
{
    public function __construct(
        private EmployeeService $employeeService,
    ) {}

    /**
     * Display a paginated listing of employees.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            // All querying lives in the service; the controller only shapes the response.
            $employees = $this->employeeService->paginate(
                $request->only(['search', 'factory_id'])
            );

            $data = $employees->getCollection()
                ->map(fn (Employee $employee) => EmployeeData::fromModel($employee)->toArray());

            return response()->json([
                'success' => true,
                'pagination' => PaginationHelper::format($employees),
                'data' => $data,
                'factories' => $this->employeeService->factoryOptions(),
            ]);
        } catch (Throwable $exception) {
            Log::error('Employee index error', ['exception' => $exception]);

            return response()->json([
                'success' => false,
                'message' => 'Unable to list employees.',
            ], 500);
        }
    }

    /**
     * Store a newly created employee.
     */
    public function store(StoreEmployeeRequest $request): JsonResponse
    {
        try {
            $employee = $this->employeeService->create($request->validated());

            return response()->json([
                'success' => true,
                'data' => EmployeeData::fromModel($employee)->toArray(),
            ], 201);
        } catch (Throwable $exception) {
            Log::error('Employee store error', ['exception' => $exception]);

            return response()->json([
                'success' => false,
                'message' => 'Unable to create employee.',
            ], 500);
        }
    }

    /**
     * Display the specified employee.
     */
    public function show(Employee $employee): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => EmployeeData::fromModel($employee)->toArray(),
        ]);
    }

    /**
     * Update the specified employee.
     */
    public function update(UpdateEmployeeRequest $request, Employee $employee): JsonResponse
    {
        try {
            $employee = $this->employeeService->update($employee, $request->validated());

            return response()->json([
                'success' => true,
                'data' => EmployeeData::fromModel($employee)->toArray(),
            ]);
        } catch (Throwable $exception) {
            Log::error('Employee update error', ['exception' => $exception]);

            return response()->json([
                'success' => false,
                'message' => 'Unable to update employee.',
            ], 500);
        }
    }

    /**
     * Remove the specified employee.
     */
    public function destroy(Employee $employee): JsonResponse
    {
        try {
            $this->employeeService->delete($employee);

            return response()->json([
                'success' => true,
                'message' => 'Employee deleted successfully.',
            ]);
        } catch (Throwable $exception) {
            Log::error('Employee destroy error', ['exception' => $exception]);

            return response()->json([
                'success' => false,
                'message' => 'Unable to delete employee.',
            ], 500);
        }
    }
}
