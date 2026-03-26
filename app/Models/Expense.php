<?php

namespace App\Models;

use App\Enums\DigitalSealStatus;
use App\Enums\ExpenseStatus;
use App\Enums\ReconciliationStatus;
use App\Observers\ExpenseObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

#[ObservedBy([ExpenseObserver::class])]
class Expense extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia, SoftDeletes;

    protected $fillable = [
        'user_id',
        'category_id',
        'vehicle_id',
        'title',
        'description',
        'expensed_at',
        'amount_total',
        'tax_rate',
        'amount_taxe',
        'site_reference',
        'status',
        'odometer',
        'bank_transaction_id',
        'reconciliation_status',
        'bank_transaction_id',
        'digital_seal_status',
        'sealed_at',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function bankTransaction(): BelongsTo
    {
        return $this->belongsTo(BankTransaction::class);
    }

    protected function casts(): array
    {
        return [
            'expensed_at' => 'date',
            'status' => ExpenseStatus::class,
            'amount_total' => 'decimal:2',
            'amount_taxe' => 'decimal:2',
            'tax_rate' => 'decimal:2',
            'reconciliation_status' => ReconciliationStatus::class,
            'digital_seal_status' => DigitalSealStatus::class,
            'sealed_at' => 'datetime',
        ];
    }

    /**
     * Enregistrement de la collection média pour les reçus.
     */
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('receipts')
            ->singleFile() // Une note de frais = un justificatif principal
            ->useDisk('public');
    }
}
