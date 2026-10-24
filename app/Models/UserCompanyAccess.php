<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserCompanyAccess extends Model
{
    use HasUuids;

    protected $table = 'user_company_access';
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'company_id',
        'role_id',
        'max_approval_limit',
        'can_post_journals',
        'granted_by_user_id',
    ];

    protected $casts = [
        'max_approval_limit' => 'decimal:4',
        'can_post_journals' => 'boolean',
        'granted_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

    public function grantedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'granted_by_user_id');
    }
}