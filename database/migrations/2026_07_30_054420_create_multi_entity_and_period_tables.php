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
        // 1. Companies / Legal Entities
        Schema::create('companies', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('legal_name', 255);
            $table->string('tax_identifier', 100); // e.g., VAT ID, EIN
            $table->char('base_currency_code', 3); // ISO 4217 (e.g., USD, EUR, JPY)
            $table->char('country_code', 2);       // ISO 3166-1 alpha-2
            $table->timestamp('created_at')->useCurrent();
        });

        // 2. Fiscal Years (IAS 1 reporting windows)
        Schema::create('fiscal_years', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('company_id')->constrained('companies')->onDelete('cascade');
            $table->string('name', 50);            // e.g., "FY 2026"
            $table->date('start_date');
            $table->date('end_date');
            $table->boolean('is_closed')->default(false); // Prevents posting in closed years

            $table->index('company_id', 'idx_fiscal_years_company');
        });

        // 3. Accounting Periods (Monthly/Quarterly sub-periods)
        Schema::create('accounting_periods', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('fiscal_year_id')->constrained('fiscal_years')->onDelete('cascade');
            $table->integer('period_number');       // e.g., 1 to 12
            $table->date('start_date');
            $table->date('end_date');
            $table->boolean('is_closed')->default(false); // Hard lock on monthly posting

            $table->index('fiscal_year_id', 'idx_accounting_periods_fy');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('accounting_periods');
        Schema::dropIfExists('fiscal_years');
        Schema::dropIfExists('companies');
    }
};