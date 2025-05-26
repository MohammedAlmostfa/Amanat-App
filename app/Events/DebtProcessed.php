<?php

namespace App\Events;

use Illuminate\Support\Facades\Log;
use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;

class DebtProcessed
{
    use InteractsWithSockets;
    use SerializesModels;

    /**
     * The debt ID that triggered the event
     *
     * @var int
     */
    public $debtId;

    /**
     * The amount difference to apply
     *
     * @var float
     */
    public $difference;

    /**
     * The store ID associated with the debt
     *
     * @var int
     */
    public $storeId;

    /**
     * Create a new event instance.
     *
     * @param int $debtId
     * @param int $storeId
     * @param float $difference
     */
    public function __construct($debtId, $storeId, $difference)
    {
        $this->debtId = $debtId;
        $this->storeId = $storeId;
        $this->difference = $difference;
    }
}
