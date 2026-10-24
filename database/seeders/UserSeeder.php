<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Create System Super Admin
        User::updateOrCreate(
            ['email' => 'admin@enterprise.com'],
            [
                'id' => (string) Str::uuid(),
                'password_hash' => Hash::make('AdminSecret123!'),
                'first_name' => 'System',
                'last_name' => 'Administrator',
                'job_title' => 'Global Systems Admin',
                'department' => 'IT Governance',
                'status' => 'ACTIVE',
                'is_super_admin' => true,
                'mfa_enabled' => true,
                'failed_login_attempts' => 0,
                'preferred_language' => 'en',
                'timezone' => 'UTC',
                'password_changed_at' => now(),
                'last_login_at' => now(),
            ]
        );

        // 2. Create Standard Demo Finance User
        User::updateOrCreate(
            ['email' => 'finance@enterprise.com'],
            [
                'id' => (string) Str::uuid(),
                'password_hash' => Hash::make('FinanceUser123!'),
                'first_name' => 'Sarah',
                'last_name' => 'Connor',
                'job_title' => 'Financial Controller',
                'department' => 'Accounting',
                'status' => 'ACTIVE',
                'is_super_admin' => false,
                'mfa_enabled' => true,
                'failed_login_attempts' => 0,
                'preferred_language' => 'en',
                'timezone' => 'America/New_York',
                'password_changed_at' => now(),
                'last_login_at' => now(),
            ]
        );

        // 3. Generate 20 Random Active Users
        User::factory()->count(20)->create();

        // 4. Generate 3 Suspended Test Accounts
        User::factory()->count(3)->suspended()->create();
    }
}