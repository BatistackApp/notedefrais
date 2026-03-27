<?php

namespace App\Models;

use App\Observers\BankAccountObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[ObservedBy([BankAccountObserver::class])]
class BankAccount extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'iban',
        'currency',
        'is_active',
        'bridge_item_id',
        'bridge_account_id',
        'last_synced_at',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'last_synced_at' => 'datetime',
        ];
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(BankTransaction::class);
    }
}
