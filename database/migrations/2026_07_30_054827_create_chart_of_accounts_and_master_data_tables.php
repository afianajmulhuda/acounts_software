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
        // 1. Chart of Accounts Table
        Schema::create('accounts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('company_id')->constrained('companies')->onDelete('cascade');
            $table->string('code', 50);            // Standard Account Code (e.g., "1010")
            $table->string('name', 255);
            $table->foreignUuid('parent_account_id')->nullable()->constrained('accounts')->onDelete('restrict');
            $table->string('account_type', 50);    // ASSET, LIABILITY, EQUITY, REVENUE, EXPENSE
            $table->string('normal_balance', 10);  // DEBIT or CREDIT
            $table->boolean('is_active')->default(true);

            $table->unique(['company_id', 'code'], 'idx_company_account_code');
            $table->index('parent_account_id', 'idx_accounts_parent');
            $table->index('account_type', 'idx_accounts_type');
        });

        // 2. Counterparties / Sub-ledger Entities (Customers, Vendors)
        Schema::create('parties', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('company_id')->constrained('companies')->onDelete('cascade');
            $table->string('party_type', 20);      // CUSTOMER, VENDOR, BOTH
            $table->string('legal_name', 255);
            $table->string('tax_number', 100)->nullable();
            $table->char('country_code', 2);       // ISO 3166-1 alpha-2
            $table->char('currency_code', 3);      // ISO 4217

            $table->index(['company_id', 'party_type'], 'idx_parties_company_type');
        });

        // 3. Tax Rates Engine
        Schema::create('tax_rates', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('company_id')->constrained('companies')->onDelete('cascade');
            $table->string('code', 50);            // e.g., VAT_20, GST_18
            $table->decimal('rate', 5, 4);          // e.g., 0.2000 for 20%
            $table->foreignUuid('tax_account_id')->nullable()->constrained('accounts')->onDelete('set null');
            $table->boolean('is_active')->default(true);

            $table->unique(['company_id', 'code'], 'idx_company_tax_code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tax_rates');
        Schema::dropIfExists('parties');
        Schema::dropIfExists('accounts');
    }
};