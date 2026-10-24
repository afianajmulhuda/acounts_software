<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JournalLine extends Model
{
    use HasFactory, HasUuids;

    public $timestamps = false;

    protected $fillable = [
        'entry_id',
        'account_id',
        'party_id',
        'tax_rate_id',
        'txn_currency_code',
        'txn_debit',
        'txn_credit',
        'fx_rate',
        'base_debit',
        'base_credit',
        'line_description',
    ];

    protected $casts = [
        'txn_debit' => 'decimal:4',
        'txn_credit' => 'decimal:4',
        'fx_rate' => 'decimal:6',
        'base_debit' => 'decimal:4',
        'base_credit' => 'decimal:4',
    ];

    public function entry(): BelongsTo
    {
        return $this->belongsTo(JournalEntry::class, 'entry_id');
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'account_id');
    }

    public function party(): BelongsTo
    {
        return $this->belongsTo(Party::class, 'party_id');
    }

    public function taxRate(): BelongsTo
    {
        return $this->belongsTo(TaxRate::class, 'tax_rate_id');
    }
}