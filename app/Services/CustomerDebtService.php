<?php
namespace App\Services;

use Exception;
use App\Models\Customer;
use App\Events\DebtProcessed;
use App\Models\CustomerDebts;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Services\Service;

/**
 * Service class responsible for managing customer debts.
 * It handles creation, updating, and deletion of debt records,
 * including balance calculations and adjustments.
 */

class CustomerDebtService extends Service
{
    /**
     * Create a new debt record for a customer and calculate updated balance.
     *
     * @param array $data Debt details including credit/debit, customer ID, and optional receipt.
     * @return array Response array containing status, message, and data.
     */

    public function createCustomerDebt($data)
    {
        try {
            // Get the current balance from the most recent debt record
            $currentBalance = CustomerDebts::where("customer_id", $data['customer_id'])
                                  ->latest('created_at')
                                  ->value('total_balance') ?? 0;

            // Validate and calculate new balance
            if (!empty($data['credit'])) {
                $newBalance = $currentBalance + $data['credit'];
            } elseif (!empty($data['debit'])) {
                // Prevent over-withdrawal
                $newBalance = $currentBalance - $data['debit'];
            }

            // Create the new debt record
            $debt = CustomerDebts::create([
                'customer_id' => $data['customer_id'],
                'credit' => $data['credit'] ?? 0,
                'debit' => $data['debit'] ?? 0,
                'debt_date' => $data['debt_date'] ?? now(),
                'total_balance' => $newBalance,
                'receipt_id' => $data['receipt_id'] ?? null,
            ]);

            return $this->successResponse($debt, 'تم تسجيل الدين بنجاح.');

        } catch (Exception $e) {
            Log::error('Create debt error: ' . $e->getMessage());
            return $this->errorResponse('حدث خطأ أثناء عملية التسجيل. يرجى المحاولة لاحقًا.');
        }
    }


    /**
      * Update an existing debt record and recalculate the balance history.
      *
      * @param array $data Updated debt details.
      * @param CustomerDebts $customerDebt The existing debt record to update.
      * @return array Response array containing status, message, and updated data.
      */

    public function updateCustomerDebt($data, CustomerDebts $CustomerDebts)
    {
        DB::beginTransaction();
        try {
            $CustomerDebts->update([
                'credit' => $data['credit'] ?? 0,
                'debit' => $data['debit'] ?? 0,
                'debt_date' => $data['debt_date'] ?? $CustomerDebts->debt_date,
                'receipt_id' => $data['receipt_id'] ?? $CustomerDebts->receipt_id,
            ]);

            $debts = CustomerDebts::where('customer_id', $CustomerDebts->customer_id)
                ->orderBy('id')
                ->get();

            $total = 0;
            foreach ($debts as $d) {
                $total += $d->credit - $d->debit;
                $d->update(['total_balance' => $total]);
            }

            DB::commit();
            return $this->successResponse($CustomerDebts, 'تم تحديث الدين بنجاح.');
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Update debt error: ' . $e->getMessage());
            return $this->errorResponse('حدث خطأ أثناء تحديث الدين. يرجى المحاولة لاحقًا.');
        }
    }


    /**
         * Delete a debt record and trigger balance adjustment via event.
         *
         * @param CustomerDebts $customerDebt The debt record to be deleted.
         * @return array Response array indicating deletion success or failure.
         */

    public function deleteCustomerDebt(CustomerDebts $CustomerDebts)
    {
        try {
            Log::error('Delete debt error:', ['CustomerDebts' => $CustomerDebts->toArray()]);

            // Handle balance adjustment before deletion
            if (!empty($CustomerDebts->credit)) {
                $adjustment = -$CustomerDebts->credit;
                event(new DebtProcessed($CustomerDebts->id, $CustomerDebts->customer_id, $adjustment));
            } elseif (!empty($debt->debit)) {
                $adjustment = $CustomerDebts->debit;
                event(new DebtProcessed($CustomerDebts->id, $CustomerDebts->customer_id, $adjustment));
            }

            // Delete the record
            $CustomerDebts->delete();
            return $this->successResponse('تم حذف الدين بنجاح.');
        } catch (Exception $e) {
            Log::error('Delete debt error: ' . $e->getMessage());
            return $this->errorResponse('حدث خطأ أثناء حذف الدين. يرجى المحاولة لاحقًا.');
        }
    }

}
