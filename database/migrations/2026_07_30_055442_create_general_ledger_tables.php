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
        // 1. Journal Entry Header (The Transaction Event)
        Schema::create('journal_entries', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('company_id')->constrained('companies')->onDelete('cascade');
            $table->foreignUuid('period_id')->constrained('accounting_periods')->onDelete('restrict');
            $table->string('entry_number', 100);  // Sequential audit identifier (e.g., JE-2026-0001)
            $table->date('posting_date');
            $table->date('document_date');
            $table->string('source_document_type', 50)->nullable(); // INVOICE, PAYMENT, MANUAL, REVALUATION
            $table->uuid('source_document_id')->nullable();
            $table->string('status', 20)->default('POSTED');       // DRAFT, POSTED, VOIDED
            $table->text('description')->nullable();
            $table->foreignUuid('created_by_user_id')->constrained('users')->onDelete('restrict');
            $table->timestamp('created_at')->useCurrent();

            // Constraints & Indexes
            $table->unique(['company_id', 'entry_number'], 'idx_company_entry_num');
            $table->index(['company_id', 'posting_date'], 'idx_entries_posting_date');
            $table->index(['source_document_type', 'source_document_id'], 'idx_source_document');
        });

        // 2. Journal Lines (Line-level Debits and Credits)
        Schema::create('journal_lines', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('entry_id')->constrained('journal_entries')->onDelete('cascade');
            $table->foreignUuid('account_id')->constrained('accounts')->onDelete('restrict');
            $table->foreignUuid('party_id')->nullable()->constrained('parties')->onDelete('set null');
            $table->foreignUuid('tax_rate_id')->nullable()->constrained('tax_rates')->onDelete('set null');

            // Transaction / Document Currency Amounts
            $table->char('txn_currency_code', 3);
            $table->decimal('txn_debit', 18, 4)->default(0.0000);
            $table->decimal('txn_credit', 18, 4)->default(0.0000);

            // Base / Functional Currency Amounts
            $table->decimal('fx_rate', 12, 6)->default(1.000000);
            $table->decimal('base_debit', 18, 4)->default(0.0000);
            $table->decimal('base_credit', 18, 4)->default(0.0000);

            $table->string('line_description', 255)->nullable();

            // Indexes
            $table->index('account_id', 'idx_journal_lines_account');
            $table->index('party_id', 'idx_journal_lines_party');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('journal_lines');
        Schema::dropIfExists('journal_entries');
    }
};