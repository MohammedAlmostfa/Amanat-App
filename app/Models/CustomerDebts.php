<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerDebts extends Model
{

    protected $fillable = ['customer_id', 'debit',"credit", 'debt_date', 'total_balance', 'receipt_id'];


    protected $casts = [
        'customer_id' => 'integer',
        'credit' => 'integer',
        'debit' => 'integer',
        'debt_date' => 'date',
        'total_balance' => 'integer',
        'receipt_id' => 'integer',
    ];
    public function customer()
    {
        return $this->belongsTo(Store::class);
    }
    public function scopeFilterBy($query, array $filteringData)
    {
        // Filter where 'credit' is null
        if (isset($filteringData['debit'])) {
            $query->whereNull('credit');

        }
        // Filter by debt_date if provided
        if (isset($filteringData['debt_date'])) {
            $query->whereDate('debt_date', '=', $filteringData['debt_date']);
        }
        if (isset($filteringData['receipt_id'])) {
            $query->whereDate('receipt_id', '=', $filteringData['receipt_id']);
        }


        return $query;
    }

}
