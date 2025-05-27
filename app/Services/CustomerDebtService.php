<?php
namespace App\Services;

use Exception;
use App\Services\Service;
use App\Models\CustomerDebt;
use App\Events\DebtProcessed;
use App\Models\CustomerDebts;
use Illuminate\Support\Carbon;
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
                $newRemainingAmount += $data['amount_due'] + ($data['commission_amount'] ?? 0);

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
                'commission_amount' => $data['commission_amount'] ?? 0,
                'due_date' => $data['due_date'] ?? now(),
                'remaining_amount' =>  $newRemainingAmount,
                'notes'=> $data['notes'] ?? null,
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



            $latestDebt = CustomerDebt::where("customer_id", $CustomerDebt->customer_id)
                ->where('id', '<', $CustomerDebt->id)
                ->latest('id')
                ->first();

            $remainingAmount = $latestDebt->remaining_amount ?? 0;
            $newRemainingAmount = $remainingAmount;

            if ((!empty($data['amount_due']) || isset($data['commission_amount']) && $data['commission_amount'] === 0) && isset($CustomerDebt->amount_due)) {

                $commissionAmount = $data['commission_amount'] ?? ($CustomerDebt->commission_amount ?? 0);
                $amountDue = $data['amount_due'] ?? ($CustomerDebt->amount_due ?? 0);
                $newRemainingAmount += $amountDue + $commissionAmount;

            } elseif (!empty($data['amount_paid']) && isset($CustomerDebt->amount_paid)) {
                $newRemainingAmount = max(0, $remainingAmount - ($data['amount_paid'] ?? 0));

            }



            $CustomerDebt->update([
                'amount_due' => $data['amount_due'] ?? $CustomerDebt->amount_due,
                'amount_paid' => $data['amount_paid'] ?? $CustomerDebt->amount_paid,
                'due_date' => $data['due_date'] ?? $CustomerDebt->due_date,
                'commission_amount' => $data['commission_amount'] ?? $CustomerDebt->commission_amount,
                'remaining_amount' => $newRemainingAmount ??$CustomerDebt->remaining_amount,
                'notes' => $data['notes'] ?? $CustomerDebt->notes,
            ]);


            event(new DebtProcessed($latestDebt, $CustomerDebt->customer_id));


            DB::commit();
            return $this->successResponse('تم التحديث بنجاح');

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
            $customerId= $CustomerDebt->customer_id;
            $CustomerDebt->delete();

            event(new DebtProcessed($latestDebt, $customerId));

            DB::commit();

            return $this->successResponse($message);

        } catch (Exception $e) {
            DB::rollBack();

            Log::error('حدث خطأ أثناء حذف الدين: ' . $e->getMessage());
            return $this->errorResponse('حدث خطأ أثناء حذف الدين. يرجى المحاولة لاحقًا.');
        }
    }
}
