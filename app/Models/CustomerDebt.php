<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class CustomerDebt
 *
 * Represents a customer's debt record, including due amounts, paid amounts, and remaining balance.
 */
class CustomerDebt extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = ['customer_id', 'amount_due', 'amount_paid', 'notes', 'due_date', 'remaining_amount'];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'customer_id' => 'integer',
        'amount_due' => 'integer',
        'amount_paid' => 'integer',
        'due_date' => 'date',
        'notes' => 'string',
        'remaining_amount' => 'integer'
    ];

    /**
     * Get the associated customer for the debt record.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function customer()
    {
        return $this->hasOne(Customer::class);
    }
}
