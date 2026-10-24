<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JournalEntry extends Model
{
    use HasFactory, HasUuids;

    public $timestamps = false; // Schema uses explicit created_at only

    protected $fillable = [
        'company_id',
        'period_id',
        'entry_number',
        'posting_date',
        'document_date',
        'source_document_type',
        'source_document_id',
        'status',
        'description',
        'created_by_user_id',
    ];

    protected $casts = [
        'posting_date' => 'date',
        'document_date' => 'date',
        'created_at' => 'datetime',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    public function period(): BelongsTo
    {
        return $this->belongsTo(AccountingPeriod::class, 'period_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function lines(): HasMany
    {
        return $this->hasMany(JournalLine::class, 'entry_id');
    }
}