<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Vehicle extends Model
{
    use HasFactory;

    protected $fillable = [
        'brand',
        'model',
        'plaque',
        'actual_odometer',
    ];

    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class);
    }
}
