<?php

namespace App\Events;

use App\Models\CustomerDebt;

use Illuminate\Support\Facades\Log;
use Illuminate\Queue\SerializesModels;

use Illuminate\Broadcasting\InteractsWithSockets;

class DebtProcessed
{
    use InteractsWithSockets;
    use SerializesModels;

    public $CustomerDebt;
    public $customerId;
    public function __construct(?CustomerDebt $CustomerDebt = null, $customerId)
    {
        $this->CustomerDebt = $CustomerDebt;
        $this->customerId = $customerId;
    }

}
