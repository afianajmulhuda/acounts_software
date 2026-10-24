<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Account extends Model
{
    use HasFactory, HasUuids;

    public $timestamps = false;

    protected $fillable = [
        'company_id',
        'code',
        'name',
        'parent_account_id',
        'account_type',
        'normal_balance',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    // Hierarchical Parent Relationship
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'parent_account_id');
    }

    // Hierarchical Children Accounts (Sub-accounts)
    public function children(): HasMany
    {
        return $this->hasMany(Account::class, 'parent_account_id');
    }

    // Recursive Relationship to load full tree
    public function childrenRecursive(): HasMany
    {
        return $this->children()->with('childrenRecursive');
    }

    public function taxRates(): HasMany
    {
        return $this->hasMany(TaxRate::class, 'tax_account_id');
    }
}