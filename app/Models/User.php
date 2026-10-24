<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, HasUuids, Notifiable;

    protected $fillable = [
        'email',
        'password_hash',
        'first_name',
        'last_name',
        'job_title',
        'department',
        'status',
        'is_super_admin',
        'mfa_enabled',
        'mfa_secret',
        'failed_login_attempts',
        'locked_until',
        'password_changed_at',
        'preferred_language',
        'timezone',
        'last_login_at',
    ];

    protected $hidden = [
        'password_hash',
        'mfa_secret',
    ];

    protected $casts = [
        'is_super_admin' => 'boolean',
        'mfa_enabled' => 'boolean',
        'locked_until' => 'datetime',
        'password_changed_at' => 'datetime',
        'last_login_at' => 'datetime',
    ];

    // Password Mutator to comply with Laravel's Auth System
    public function getAuthPasswordName(): string
    {
        return 'password_hash';
    }

    public function companyAccess(): HasMany
    {
        return $this->hasMany(UserCompanyAccess::class, 'user_id');
    }

    public function sessions(): HasMany
    {
        return $this->hasMany(UserSession::class, 'user_id');
    }
    public function getAuthPassword()
    {
        return $this->password_hash;
    }
    public function companies()
    {
        return $this->belongsToMany(
            Company::class, 
            'user_company_access', 
            'user_id', 
            'company_id'
        )->withPivot(['role_id', 'max_approval_limit', 'can_post_journals']);
    }
}