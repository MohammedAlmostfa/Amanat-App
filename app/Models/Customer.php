<?php

namespace App\Models;

use App\Models\CustomerDebt;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * Class Customer
 *
 * Represents a customer with associated debts and receipts.
 */
class Customer extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = ['name', 'phone', 'notes', 'address'];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'name' => 'string',
        'phone' => 'integer',
        'notes' => 'string',
        'address' => 'string',
    ];

    /**
     * Get the customer's debts.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function customerdebts()
    {
        return $this->hasMany(CustomerDebt::class);
    }

    /**
     * Get the latest debt record for the customer.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function latestdebt(): HasOne
    {
        return $this->hasOne(CustomerDebt::class)->latestOfMany('id');
    }

    /**
     * Scope function to filter customers based on name and phone.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param array $filteringData The filtering criteria
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeFilterBy($query, array $filteringData)
    {
        if (isset($filteringData['name'])) {
            $query->where('name', 'LIKE', "%{$filteringData['name']}%");
        }

        if (isset($filteringData['phone'])) {
            $query->where('phone', '=', $filteringData['phone']);
        }

        return $query;
    }
}
