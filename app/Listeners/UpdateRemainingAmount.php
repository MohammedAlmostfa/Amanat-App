<?php

namespace App\Listeners;

use Throwable;
use App\Models\CustomerDebt;
use App\Events\DebtProcessed;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class UpdateRemainingAmount
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
        $CustomerDebt = $event->CustomerDebt;
        $lastRemainingAmount = optional($CustomerDebt)->remaining_amount ?? 0;


        $CustomerDebts = CustomerDebt::where("customer_id", $CustomerDebt->customer_id)
            ->where('id', '>', $CustomerDebt->id)
            ->orderBy('id', 'asc')
            ->get();
        if($CustomerDebts) {

            DB::transaction(function () use ($CustomerDebts, $lastRemainingAmount) {
                foreach ($CustomerDebts as $CustomerDebt) {
                    $CustomerDebt->remaining_amount = $lastRemainingAmount - $CustomerDebt->amount_paid + $CustomerDebt->amount_due;
                    $CustomerDebt->save();
                }
            });
        }
    }
}
