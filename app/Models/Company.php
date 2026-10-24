<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Company extends Model
{
    use HasFactory, HasUuids;

    public $timestamps = false; // Only created_at exists in table schema

    protected $fillable = [
        'legal_name',
        'tax_identifier',
        'base_currency_code',
        'country_code',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function fiscalYears(): HasMany
    {
        return $this->hasMany(FiscalYear::class, 'company_id');
    }

    // Direct relationship to access all accounting periods across fiscal years
    public function accountingPeriods(): HasManyThrough
    {
        return $this->hasManyThrough(
            AccountingPeriod::class,
            FiscalYear::class,
            'company_id',     // Foreign key on fiscal_years table
            'fiscal_year_id', // Foreign key on accounting_periods table
            'id',             // Local key on companies table
            'id'              // Local key on fiscal_years table
        );
    }
}