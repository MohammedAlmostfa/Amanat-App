<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerDebt extends Model
{
    protected $fillable = ['customer_id', 'amount_due', "amount_paid", 'due_date', 'remaining_amount'];
    protected $casts = [
        'customer_id' => 'integer',
        'amount_due' => 'integer',
        'amount_paid' => 'integer',
        'due_date' => 'date',
        'remaining_amount' => 'integer'
    ];
    public function customer()
    {
        return $this->hasOne(Customer::class);
    }
}
