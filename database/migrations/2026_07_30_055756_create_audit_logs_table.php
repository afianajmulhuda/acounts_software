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
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('company_id')->constrained('companies')->onDelete('cascade');
            $table->foreignUuid('user_id')->constrained('users')->onDelete('restrict');
            $table->string('action_type', 50); // e.g., POST_JOURNAL, CLOSE_PERIOD, REVERSE_ENTRY
            $table->string('table_name', 50);  // Target model table (e.g., journal_entries)
            $table->uuid('record_id');          // Primary key of affected record
            
            // Native JSONB columns for state diff tracking
            $table->string('previous_state')->nullable();
            $table->string('new_state')->nullable();
            
            $table->string('ip_address', 45)->nullable();
            $table->timestamp('timestamp')->useCurrent();

            // Audit indexes for fast legal search and compliance queries
            $table->index(['company_id', 'timestamp'], 'idx_audit_company_timestamp');
            $table->index(['table_name', 'record_id'], 'idx_audit_record_lookup');
            $table->index('action_type', 'idx_audit_action_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};