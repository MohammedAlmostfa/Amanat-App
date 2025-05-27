<?php

namespace App\Services;

use Exception;
use App\Models\Customer;
use App\Models\CustomerDebt;
use App\Services\Service;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

/**
 * Class CustomerService
 *
 * Service class responsible for handling customer-related operations such as:
 * - Retrieving customer lists (with filtering)
 * - Managing customer debts
 * - Creating, updating, and deleting customers
 */
class CustomerService extends Service
{
    /**
     * Retrieve all customers.
     *
     * Fetches a list of all customers, optionally filtered by criteria.
     *
     * @param array $filteringData Filters to apply on customer data
     * @return array Response containing status, message, and list of customers.
     */
    public function getAllCustomers($filteringData)
    {
        try {
            // Fetch customers with optional filtering
            $customers = Customer::query()
                ->when(!empty($filteringData), function ($query) use ($filteringData) {
                    $query->filterBy($filteringData);
                })
                ->get();

            return $this->successResponse('تم استرجاع العملاء بنجاح', $customers);
        } catch (Exception $e) {
            // Log the error for debugging
            Log::error('خطأ أثناء استرجاع العملاء: ' . $e->getMessage());

            return $this->errorResponse('فشل في استرجاع العملاء');
        }
    }

    /**
     * Retrieve all customers along with their latest unpaid debts.
     *
     * @param array $filteringData Filters for customer retrieval
     * @return array Response containing customers and their latest debts.
     */
    public function getAllCustomersWithDebt($filteringData)
    {
        try {
            // Fetch customers along with their latest unpaid debts
            $customers = Customer::with(['customerdebts' => function ($query) use ($filteringData) {
                $query->select('id', 'customer_id', 'remaining_amount', 'due_date')
                      ->where('amount_paid', 0)
                      ->latest('id')
                      ->when(!empty($filteringData), function ($query) use ($filteringData) {
                          $query->filterBy($filteringData);
                      });
            }])->get()->map(function ($customer) {
                // Default customer status
                $customer->status = 1;

                // Retrieve latest debt
                $latestDebt = $customer->customerdebts->first();

                if ($latestDebt) {
                    // Calculate days difference
                    $daysDifference = Carbon::parse($latestDebt->due_date)->diffInDays(now());

                    // Determine status based on overdue period
                    $customer->status = ($daysDifference > 20) ? 0 : 1;
                }

                return $customer;
            });

            return $this->successResponse('تم استرجاع العملاء بنجاح', $customers);
        } catch (Exception $e) {
            // Log any errors
            Log::error('خطأ أثناء استرجاع العملاء: ' . $e->getMessage());

            return $this->errorResponse('فشل في استرجاع العملاء');
        }
    }

    /**
     * Retrieve debts of a specific customer.
     *
     * @param int $id Customer ID
     * @return array Response containing status, message, and customer debts.
     */
    public function getCustomerDebts($id)
    {
        try {
            // Fetch customer along with debts
            $customerdebts = CustomerDebt::where('customer_id', $id)->get();
            return $this->successResponse('تم استرجاع ديون العميل بنجاح', $customerdebts);
        } catch (Exception $e) {
            // Log any errors for debugging
            Log::error('خطأ أثناء استرجاع ديون العميل: ' . $e->getMessage());

            return $this->errorResponse('فشل في استرجاع ديون العميل');
        }
    }

    /**
     * Create a new customer.
     *
     * @param array $data Customer details: ['name', 'phone', 'notes']
     * @return array Response containing status and created customer data.
     */
    public function createCustomer(array $data): array
    {
        try {
            // Create new customer record
            $customer = Customer::create($data);

            return $this->successResponse('تم إنشاء العميل بنجاح', $customer);
        } catch (Exception $e) {
            // Log error details for debugging
            Log::error('خطأ أثناء إنشاء العميل: ' . $e->getMessage());

            return $this->errorResponse('فشل في إنشاء العميل');
        }
    }

    /**
     * Update an existing customer.
     *
     * @param array $data Updated customer details ['name', 'phone', 'notes']
     * @param Customer $customer Customer instance to be updated
     * @return array Response containing status and updated customer data.
     */
    public function updateCustomer(array $data, Customer $customer): array
    {
        try {
            // Update customer record
            $customer->update($data);

            return $this->successResponse('تم تحديث العميل بنجاح', $customer);
        } catch (Exception $e) {
            // Log error details for debugging
            Log::error('خطأ أثناء تحديث العميل: ' . $e->getMessage());

            return $this->errorResponse('فشل في تحديث العميل');
        }
    }

    /**
     * Delete a customer record.
     *
     * @param Customer $customer Customer instance to be deleted
     * @return array Response indicating success or failure.
     */
    public function deleteCustomer(Customer $customer): array
    {
        try {
            // Delete customer record
            $customer->delete();

            return $this->successResponse('تم حذف العميل بنجاح');
        } catch (Exception $e) {
            // Log error details for debugging
            Log::error('خطأ أثناء حذف العميل: ' . $e->getMessage());

            return $this->errorResponse('فشل في حذف العميل');
        }
    }
}
