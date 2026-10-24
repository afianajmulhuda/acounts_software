<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Users Table
        Schema::create('users', function (Blueprint $table) {
            $table->uuid('id')->primary();
            
            // Identity Info
            $table->string('email', 191)->unique();
            $table->string('password_hash', 255);
            $table->string('first_name', 100);
            $table->string('last_name', 100);
            $table->string('job_title', 100)->nullable();
            $table->string('department', 100)->nullable();
            
            // System Status & Access Flags
            $table->string('status', 20)->default('ACTIVE'); // ACTIVE, SUSPENDED, DEACTIVATED
            $table->boolean('is_super_admin')->default(false);
            
            // MFA & Security Compliance
            $table->boolean('mfa_enabled')->default(false);
            $table->string('mfa_secret', 255)->nullable();
            $table->integer('failed_login_attempts')->default(0);
            $table->timestamp('locked_until')->nullable();
            $table->timestamp('password_changed_at')->nullable();
            
            // Localization
            $table->string('preferred_language', 10)->default('en');
            $table->string('timezone', 50)->default('UTC');
            
            // Audit Timestamps
            $table->timestamp('last_login_at')->nullable();
            $table->timestamps();

            // Indexes
            $table->index('email', 'idx_users_email');
            $table->index('status', 'idx_users_status');
        });

        // 2. Roles Table
        Schema::create('roles', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name', 50)->unique();
            $table->string('description', 255)->nullable();
            $table->boolean('is_system_role')->default(false);
            $table->timestamp('created_at')->useCurrent();
        });

        // 3. User Company Access (Scoping & SOX Controls)
        Schema::create('user_company_access', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->constrained('users')->onDelete('cascade');
            $table->uuid('company_id'); // FK to companies table
            $table->foreignUuid('role_id')->constrained('roles');
            
            $table->decimal('max_approval_limit', 18, 4)->default(0.0000);
            $table->boolean('can_post_journals')->default(false);
            
            $table->timestamp('granted_at')->useCurrent();
            $table->foreignUuid('granted_by_user_id')->nullable()->constrained('users');
            
            $table->unique(['user_id', 'company_id', 'role_id'], 'idx_user_company_role');
        });

        // 4. Permissions Table
        Schema::create('permissions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('code', 100)->unique();
            $table->string('module', 50);
            $table->string('description', 255)->nullable();
        });

        // 5. Role Permissions (Pivot Table)
        Schema::create('role_permissions', function (Blueprint $table) {
            $table->foreignUuid('role_id')->constrained('roles')->onDelete('cascade');
            $table->foreignUuid('permission_id')->constrained('permissions')->onDelete('cascade');
            
            $table->primary(['role_id', 'permission_id']);
        });

        // 6. User Sessions Table
        Schema::create('user_sessions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->constrained('users')->onDelete('cascade');
            $table->string('session_token_hash', 191)->unique();
            $table->string('ip_address', 45);
            $table->text('user_agent')->nullable();
            $table->timestamp('expires_at');
            $table->timestamp('created_at')->useCurrent();

            $table->index(['user_id', 'session_token_hash'], 'idx_sessions_user_token');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_sessions');
        Schema::dropIfExists('role_permissions');
        Schema::dropIfExists('permissions');
        Schema::dropIfExists('user_company_access');
        Schema::dropIfExists('roles');
        Schema::dropIfExists('users');
    }
};