<?php

namespace App\Listeners;

use App\Models\Debt;
use App\Events\DebtProcessed;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use Throwable;

class UpdatetotalBalance
{
    use InteractsWithQueue;

    /**
     * Handle the event.
     *
     * @param DebtProcessed $event
     * @return void
     */
    public function handle(DebtProcessed $event): void
    {
        try {
            Log::info("Processing DebtProcessed event with Debt ID: {$event->debtId}, Difference: {$event->difference}");

            Debt::where([
                ['id', '>', $event->debtId],
                ['store_id', '=', $event->storeId],
            ])->increment('total_balance', $event->difference);

            Log::info("Successfully updated total_balance for debts after Debt ID: {$event->debtId}");
        } catch (Throwable $e) {
            Log::error("Failed to update debts balance: " . $e->getMessage());
            $this->fail($e);
        }
    }

    /**
     * Handle a job failure.
     *
     * @param DebtProcessed $event
     * @param Throwable $exception
     * @return void
     */
    public function failed(DebtProcessed $event, Throwable $exception): void
    {
        Log::critical("Debt balance update job failed for Debt ID: {$event->debtId}", [
            'error' => $exception->getMessage(),
            'difference' => $event->difference,
        ]);
    }
}
