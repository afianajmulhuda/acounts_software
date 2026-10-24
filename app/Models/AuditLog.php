<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditLog extends Model
{
    use HasFactory, HasUuids;

    // Audit logs are strictly append-only
    public $timestamps = false;
    
    protected $fillable = [
        'company_id',
        'user_id',
        'action_type',
        'table_name',
        'record_id',
        'previous_state',
        'new_state',
        'ip_address',
        'timestamp',
    ];

    protected $casts = [
        'previous_state' => 'array',
        'new_state' => 'array',
        'timestamp' => 'datetime',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}