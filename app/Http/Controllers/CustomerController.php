<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use GuzzleHttp\Psr7\Request;
use App\Services\CustomerService;
use Illuminate\Http\JsonResponse;
use App\Http\Resources\customerResource;
use App\Http\Resources\CustomerDataResource;
use App\Http\Requests\CustomerRequest\FilteringData;
use App\Http\Requests\DebetRequest\FilteringDebtData;
use App\Http\Requests\CustomerRequest\StoreCustomerData;
use App\Http\Requests\CustomerRequest\UpdateCustomerData;
use App\Http\Resources\CustomerResource as ResourcesCustomerResource;

/**
 * CustomerController: Manages customer-related operations.
 *
 * Operations included:
 * - Retrieve customer lists with optional filters
 * - Show customer details along with debts
 * - Create, update, and delete customer records
 */
class CustomerController extends Controller
{
    /**
     * @var CustomerService Handles customer business logic.
     */
    protected CustomerService $customerService;

    /**
     * Constructor: Injects CustomerService for handling business logic.
     *
     * @param CustomerService $customerService Dependency injected service for customer operations.
     */
    public function __construct(CustomerService $customerService)
    {
        $this->customerService = $customerService;
    }

    /**
     * Retrieve a list of customers with optional filtering.
     *
     * @param FilteringData $request Validated filter data for customers.
     * @return JsonResponse Returns paginated customer list or error message.
     */
    public function index(FilteringData $request): JsonResponse
    {
        $result = $this->customerService->getAllCustomers($request->validated());

        return $result['status'] === 200
            ? $this->paginated($result['data'], CustomerDataResource::class, $result['message'], $result['status'])
            : $this->error(null, $result['message'], $result['status']);
    }

    /**
     * Retrieve all customers along with their latest unpaid debts.
     *
     * @param FilteringData $request Validated filter data for customers.
     * @return JsonResponse Returns customers with debts or error message.
     */
    public function getAllCustomersWithDebt(FilteringData $request): JsonResponse
    {
        $result = $this->customerService->getAllCustomersWithDebt($request->validated());

        return $result['status'] === 200
            ? $this->successshow(ResourcesCustomerResource::collection($result['data']), $result['message'], $result['status'])
            : $this->error(null, $result['message'], $result['status']);
    }

    /**
     * Create a new customer record in the database.
     *
     * @param StoreCustomerData $request Validated request data containing:
     *    - name (string, required): Customer's name
     *    - phone (string, required): Customer's phone number
     *    - notes (string, optional): Additional customer notes
     * @return JsonResponse Returns JSON response with operation success or failure.
     */
    public function store(StoreCustomerData $request): JsonResponse
    {
        $result = $this->customerService->createCustomer($request->validated());

        return $result['status'] === 200
            ? $this->success($result['data'], $result['message'], $result['status'])
            : $this->error(null, $result['message'], $result['status']);
    }

    /**
     * Update an existing customer record.
     *
     * @param UpdateCustomerData $request Validated customer update data:
     *    - name (string, optional): Updated name
     *    - phone (string, optional): Updated phone number
     *    - notes (string, optional): Updated customer notes
     * @param Customer $customer The customer instance being updated.
     * @return JsonResponse Returns JSON response indicating success or failure.
     */
    public function update(UpdateCustomerData $request, Customer $customer): JsonResponse
    {
        $result = $this->customerService->updateCustomer(
            $request->validated(),
            $customer
        );

        return $result['status'] === 200
            ? $this->success($result['data'], $result['message'], $result['status'])
            : $this->error(null, $result['message'], $result['status']);
    }

    /**
     * Display detailed information about a customer along with their debts.
     *
     * @param int $id The ID of the customer to retrieve.
     * @return JsonResponse Returns customer debts or error message.
     */
    public function show($id): JsonResponse
    {
        $result = $this->customerService->getCustomerDebts($id);

        return $result['status'] === 200
            ? $this->successshow($result['data'], $result['message'], $result['status'])
            : $this->error(null, $result['message'], $result['status']);
    }

    /**
     * Delete a customer record from the database.
     *
     * @param Customer $customer The customer instance to be deleted.
     * @return JsonResponse Returns JSON response indicating success or failure.
     */
    public function destroy(Customer $customer): JsonResponse
    {
        $result = $this->customerService->deleteCustomer($customer);

        return $result['status'] === 200
            ? $this->success($result['data'], $result['message'], $result['status'])
            : $this->error(null, $result['message'], $result['status']);
    }
}
