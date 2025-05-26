<?php

namespace App\Models;

use App\Models\Debt;
use App\Models\Receipt;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    protected $fillable = ['name', 'phone', 'notes', 'details'];

    protected $casts = [
        'name' => 'string',
        'phone' => 'integer',
        'notes' => 'string',
        'details' => 'string',
    ];

    public function customerdebts()
    {
        return $this->hasMany(CustomerDebts::class);
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
