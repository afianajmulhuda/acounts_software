<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;

class AccountingPeriod extends Model
{
    use HasFactory, HasUuids;

    public $timestamps = false;

    protected $fillable = [
        'fiscal_year_id',
        'period_number',
        'start_date',
        'end_date',
        'is_closed',
    ];

    protected $casts = [
        'period_number' => 'integer',
        'start_date' => 'date',
        'end_date' => 'date',
        'is_closed' => 'boolean',
    ];

    public function fiscalYear(): BelongsTo
    {
        return $this->belongsTo(FiscalYear::class, 'fiscal_year_id');
    }

    // Direct access to the associated legal entity
    public function company(): HasOneThrough
    {
        return $this->hasOneThrough(
            Company::class,
            FiscalYear::class,
            'id',             // Foreign key on fiscal_years table...
            'id',             // Foreign key on companies table...
            'fiscal_year_id', // Local key on accounting_periods table...
            'company_id'      // Local key on fiscal_years table...
        );
    }
}