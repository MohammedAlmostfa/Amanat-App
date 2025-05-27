<?php

namespace App\Models;

use App\Models\Debt;
use App\Models\Receipt;
use App\Models\CustomerDebt;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Customer extends Model
{
    protected $fillable = ['name', 'phone', 'notes','address', ];

    protected $casts = [
        'name' => 'string',
        'phone' => 'integer',
        'notes' => 'string',
        "address"=>'string',

    ];

    public function customerdebts()
    {
        return $this->hasMany(CustomerDebt::class);
    }
    public function latestdebt(): HasOne
    {
        return $this->hasOne(CustomerDebt::class)->latestOfMany('id');
    }


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
