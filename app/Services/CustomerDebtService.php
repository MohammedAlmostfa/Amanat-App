<?php
namespace App\Services;

use App\Events\DebtProcessed;
use Exception;
use App\Services\Service;
use App\Models\CustomerDebt;
use App\Models\CustomerDebts;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * CustomerDebtService: Handles customer debt operations.
 * - Create new debts
 * - Update existing debts
 * - Delete debts while adjusting balances
 */
class CustomerDebtService extends Service
{
    /**
     * Create a new customer debt record.
     * This method calculates and updates the remaining balance.
     *
     * @param array $data Customer debt details (amount_due, amount_paid, customer_id, etc.)
     * @return array Response containing success or failure message
     */
    public function createCustomerDebt($data)
    {
        try {
            // Retrieve the latest debt record for the customer
            $latestDebt = CustomerDebt::where("customer_id", $data['customer_id'])
                ->latest('id')
                ->first();

            $remainingAmount = $latestDebt->remaining_amount ?? 0;
            $newRemainingAmount = $remainingAmount;

            // Adjust remaining balance based on debt or payment
            if (!empty($data['amount_due'])) {
                $newRemainingAmount += $data['amount_due'];
                $message = 'تم تسجيل دين بنجاح'; // Arabic success message
            } elseif (!empty($data['amount_paid'])) {
                $newRemainingAmount -= $data['amount_paid'];
                $message = 'تم تسجيل تسديد بنجاح';
            }

            // Create the new debt record
            $data = CustomerDebt::create([
                'customer_id' => $data['customer_id'],
                'amount_due' => $data['amount_due'] ?? 0,
                'amount_paid' => $data['amount_paid'] ?? 0,
                'due_date' => $data['due_date'] ?? now(),
                'remaining_amount' =>  $newRemainingAmount,
            ]);

            return $this->successResponse($message);

        } catch (Exception $e) {
            Log::error('حدث خطأ أثناء عملية التسجيل :' . $e->getMessage());
            return $this->errorResponse('حدث خطأ أثناء عملية التسجيل. يرجى المحاولة لاحقًا.');
        }
    }

    /**
     * Update an existing customer debt record.
     * This method recalculates the balance based on the new amounts provided.
     *
     * @param array $data Updated debt details (amount_due, amount_paid, etc.)
     * @param CustomerDebts $CustomerDebts Existing customer debt record
     * @return array Response indicating success or failure
     */
    public function updateCustomerDebt($data, CustomerDebt $CustomerDebt)
    {
        DB::beginTransaction();
        try {
            $message = 'لم يتم تعديل أي بيانات'; // Default message in Arabic

            // Retrieve the latest previous debt record
            $latestDebt = CustomerDebt::where("customer_id", $CustomerDebt->customer_id)
                ->where('id', '<', $CustomerDebt->id)
                ->latest('id')
                ->first();

            $remainingAmount = $latestDebt->remaining_amount ?? 0;
            $newRemainingAmount = $remainingAmount;

            // Update balance based on debt or payment changes
            if (!empty($data['amount_due']) && isset($CustomerDebt->amount_due) && $CustomerDebt->amount_due > 0) {
                $newRemainingAmount += $data['amount_due'];
                $message = 'تم تحديث الدين بنجاح';
            } elseif (!empty($data['amount_paid']) && isset($CustomerDebt->amount_paid) && $CustomerDebt->amount_paid > 0) {
                $newRemainingAmount = max(0, $remainingAmount - $data['amount_paid']);
                $message = 'تم تحديث التسديد بنجاح';
            }

            // If no changes were made, return without updating
            if ($message === 'لم يتم تعديل أي بيانات') {
                DB::commit();
                return $this->successResponse($message);
            }

            // Update the existing debt record
            $CustomerDebt->update([
                'amount_due' => $data['amount_due'] ?? 0,
                'amount_paid' => $data['amount_paid'] ?? 0,
                'due_date' => $data['due_date'] ?? $CustomerDebt->due_date,
                'remaining_amount' => $newRemainingAmount,
            ]);
            if(! $latestDebt) {
                event(new DebtProcessed());

            } else {
                event(new DebtProcessed($latestDebt));
            }

            DB::commit();
            return $this->successResponse($message);

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('حدث خطأ أثناء التحديث: ' . $e->getMessage());
            return $this->errorResponse('حدث خطأ أثناء التحديث. يرجى المحاولة لاحقًا.');
        }
    }

    /**
     * Delete a customer debt record.
     * This method removes the debt entry and triggers balance adjustments if needed.
     *
     * @param CustomerDebts $CustomerDebts Debt record to be deleted
     * @return array Response indicating success or failure
     */
    public function deleteCustomerDebt(CustomerDebt $CustomerDebt)
    {
        DB::beginTransaction();

        try {
            // Determine type of deletion (debt or payment)
            if (isset($CustomerDebt->amount_due) && $CustomerDebt->amount_due > 0) {
                $message = 'تم حذف الدين بنجاح';
            } elseif (isset($CustomerDebt->amount_paid) && $CustomerDebt->amount_paid > 0) {
                $message = 'تم حذف التسديد بنجاح';
            }

            $latestDebt = CustomerDebt::where("customer_id", $CustomerDebt->customer_id)
            ->where('id', '<', $CustomerDebt->id)
            ->latest('id')
            ->first();

            $CustomerDebt->delete();
            if(! $latestDebt) {
                event(new DebtProcessed());

            } else {
                event(new DebtProcessed($latestDebt));
            }
            DB::commit();

            return $this->successResponse($message);

        } catch (Exception $e) {
            DB::rollBack();

            Log::error('حدث خطأ أثناء حذف الدين: ' . $e->getMessage());
            return $this->errorResponse('حدث خطأ أثناء حذف الدين. يرجى المحاولة لاحقًا.');
        }
    }
}
