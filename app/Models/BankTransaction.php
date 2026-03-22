<?php

namespace App\Models;

use App\Enums\ReconciliationStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class BankTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'bank_account_id',
        'external_id',
        'transaction_date',
        'vendor_name',
        'amount',
        'currency',
        'reconciliation_status',
    ];

    public function bankAccount(): BelongsTo
    {
        return $this->belongsTo(BankAccount::class);
    }

    public function expense(): HasOne
    {
        return $this->hasOne(Expense::class);
    }

    protected function casts(): array
    {
        return [
            'transaction_date' => 'date',
            'amount' => 'decimal:2',
            'reconciliation_status' => ReconciliationStatus::class,
        ];
    }
}
