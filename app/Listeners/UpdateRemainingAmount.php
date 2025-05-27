<?php

namespace App\Listeners;

use Throwable;
use App\Models\CustomerDebt;
use App\Events\DebtProcessed;
use Illuminate\Queue\InteractsWithQueue;

/**
 * Class UpdateRemainingAmount
 *
 * Listener responsible for updating the remaining balance (`remaining_amount`)
 * for a customer’s debts after a debt record is processed (created, updated, or deleted).
 */
class UpdateRemainingAmount
{
    use InteractsWithQueue;

    /**
     * Handle the event when a debt record is processed.
     *
     * This function ensures that the remaining balance of subsequent debts
     * is updated correctly based on payments and dues.
     *
     * @param DebtProcessed $event The event containing the debt details.
     * @return void
     */
    public function handle(DebtProcessed $event): void
    {
        $customerId = $event->customerId; // Customer ID associated with the processed debt
        $CustomerDebt = $event->CustomerDebt; // The processed debt record

        // Check if CustomerDebt is null (i.e., first debt record in the database)
        if (is_null($CustomerDebt)) {
            // Retrieve the first debt record for the customer (oldest debt)
            $CustomerDebt = CustomerDebt::where("customer_id", $customerId)
                ->orderBy('id', 'asc')
                ->first();

            $lastRemainingAmount = 0; // Initialize remaining amount to zero

            // Retrieve all debts from the first record onwards
            $CustomerDebts = CustomerDebt::where("customer_id", $customerId)
                ->where('id', '>=', $CustomerDebt->id)
                ->orderBy('id', 'asc')
                ->get();

            // If there are subsequent debts, update their remaining balance
            if ($CustomerDebts->isNotEmpty()) {
                foreach ($CustomerDebts as $CustomerDebt) {
                    // Calculate new remaining amount based on payments and dues
                    $CustomerDebt->remaining_amount = $lastRemainingAmount - $CustomerDebt->amount_paid + $CustomerDebt->amount_due;

                    // Save the updated debt record
                    $CustomerDebt->save();

                    // Update the last remaining amount for the next record
                    $lastRemainingAmount = $CustomerDebt->remaining_amount;
                }
            }
        } else {
            // If there is an existing debt record, use its remaining amount
            $lastRemainingAmount = $CustomerDebt->remaining_amount;

            // Retrieve debts that occur after the processed debt record
            $CustomerDebts = CustomerDebt::where("customer_id", $customerId)
                ->where('id', '>', $CustomerDebt->id)
                ->orderBy('id', 'asc')
                ->get();

            // Update remaining balance for subsequent debts
            if ($CustomerDebts->isNotEmpty()) {
                foreach ($CustomerDebts as $CustomerDebt) {
                    $CustomerDebt->remaining_amount = $lastRemainingAmount - $CustomerDebt->amount_paid + $CustomerDebt->amount_due;
                    $CustomerDebt->save();
                    $lastRemainingAmount = $CustomerDebt->remaining_amount;
                }
            }
        }
    }
}
