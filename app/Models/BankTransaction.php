<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BankTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'external_id',
        'transaction_date',
        'label',
        'amount_total',
        'currency',
        'is_reconciled',
        'expense_id',
    ];

    public function expense(): BelongsTo
    {
        return $this->belongsTo(Expense::class);
    }

    protected function casts(): array
    {
        return [
            'transaction_date' => 'date',
            'is_reconciled' => 'boolean',
        ];
    }
}
