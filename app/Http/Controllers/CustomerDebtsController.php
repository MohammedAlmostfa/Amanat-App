<?php

namespace App\Http\Controllers;

use App\Http\Requests\CustomerDebetRequest\StoreCustomerDebetData;
use App\Http\Requests\CustomerDebetRequest\UpdateCustomerDebetData;
use Illuminate\Http\Request;
use App\Models\CustomerDebts;
use Illuminate\Http\JsonResponse;
use App\Services\CustomerDebtService;
use App\Http\Requests\CustomerRequest\StoreCustomerData;
use App\Http\Requests\CustomerRequest\UpdateCustomerData;

class CustomerDebtsController extends Controller
{
    /**
     * Service layer instance used for managing customer debts.
     *
     * @var CustomerDebtService
     */
    protected CustomerDebtService $CustomerDebtService;

    /**
     * Constructor to inject the service dependency.
     *
     * @param CustomerDebtService $CustomerDebtService
     */
    public function __construct(CustomerDebtService $CustomerDebtService)
    {
        $this->CustomerDebtService = $CustomerDebtService;
    }

    /**
     * Store a new debt record.
     *
     * @param StoreCustomerData $request Validated debt creation data.
     * @return JsonResponse
     */
    public function store(StoreCustomerDebetData $request): JsonResponse
    {
        // Process the request using the DebtService
        $result = $this->CustomerDebtService->createCustomerDebt($request->validated());

        // Return an appropriate response based on the result status
        return $result['status'] === 200
            ? $this->success($result['data'], $result['message'], $result['status'])
            : $this->error($result['data'], $result['message'], $result['status']);
    }
    /**
     * Update an existing debt record.
     *
     * @param UpdateCustomerData $request Validated update data.
     * @param CustomerDebts $CustomerDebts The debt record to update.
     * @return JsonResponse
     */


    public function update(UpdateCustomerDebetData $request, CustomerDebts $CustomerDebts): JsonResponse
    {
        // Process the update using the DebtService
        $result = $this->CustomerDebtService->updateCustomerDebt($request->validated(), $CustomerDebts);

        // Return an appropriate response based on the result status
        return $result['status'] === 200
            ? $this->success($result['data'], $result['message'], $result['status'])
            : $this->error($result['data'], $result['message'], $result['status']);
    }


    /**
     * Delete a customer debt record.
     *
     * @param CustomerDebts $CustomerDebts The debt record to delete.
     * @return JsonResponse
     */

    public function destroy($id): JsonResponse
    {
        $customerDebts = CustomerDebts::findOrFail($id);

        $result = $this->CustomerDebtService->deleteCustomerDebt($customerDebts);

        // Return an appropriate response based on the result status
        return $result['status'] === 200
            ? $this->success($result['data'], $result['message'], $result['status'])
            : $this->error($result['data'], $result['message'], $result['status']);
    }
}
