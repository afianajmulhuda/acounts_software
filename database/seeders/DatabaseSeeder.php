<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Disable foreign key checks for clean re-seeding (PostgreSQL specific or standard truncation)
        DB::transaction(function () {

            // ----------------------------------------------------
            // 1. ROLES & PERMISSIONS
            // ----------------------------------------------------
            $adminRoleId = Str::uuid();
            $accountantRoleId = Str::uuid();

            DB::table('roles')->insert([
                [
                    'id' => $adminRoleId,
                    'name' => 'System Administrator',
                    'description' => 'Full administrative access to entity configuration and security',
                    'is_system_role' => true,
                    'created_at' => now(),
                ],
                [
                    'id' => $accountantRoleId,
                    'name' => 'General Ledger Accountant',
                    'description' => 'Can post journals, view financial statements, and manage CoA',
                    'is_system_role' => true,
                    'created_at' => now(),
                ],
            ]);

            $permissionId = Str::uuid();
            DB::table('permissions')->insert([
                'id' => $permissionId,
                'code' => 'journal_entry:post',
                'module' => 'GL',
                'description' => 'Permission to post manual double-entry journals',
            ]);

            DB::table('role_permissions')->insert([
                'role_id' => $adminRoleId,
                'permission_id' => $permissionId,
            ]);

            // ----------------------------------------------------
            // 2. USERS
            // ----------------------------------------------------
            $userId = Str::uuid();
            DB::table('users')->insert([
                'id' => $userId,
                'email' => 'admin@enterprise.com',
                'password_hash' => Hash::make('password123'),
                'first_name' => 'Chief',
                'last_name' => 'Accountant',
                'job_title' => 'Financial Controller',
                'department' => 'Finance',
                'status' => 'ACTIVE',
                'is_super_admin' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // ----------------------------------------------------
            // 3. COMPANIES / LEGAL ENTITIES
            // ----------------------------------------------------
            $companyIdUs = Str::uuid();
            $companyIdUk = Str::uuid();

            DB::table('companies')->insert([
                [
                    'id' => $companyIdUs,
                    'legal_name' => 'Acme Corporation Inc.',
                    'tax_identifier' => 'EIN-12-3456789',
                    'base_currency_code' => 'USD',
                    'country_code' => 'US',
                    'created_at' => now(),
                ],
                [
                    'id' => $companyIdUk,
                    'legal_name' => 'Acme UK Holdings Ltd',
                    'tax_identifier' => 'GB999999973',
                    'base_currency_code' => 'GBP',
                    'country_code' => 'GB',
                    'created_at' => now(),
                ],
            ]);

            // ----------------------------------------------------
            // 4. USER COMPANY ACCESS (SCOPING)
            // ----------------------------------------------------
            DB::table('user_company_access')->insert([
                [
                    'id' => Str::uuid(),
                    'user_id' => $userId,
                    'company_id' => $companyIdUs,
                    'role_id' => $adminRoleId,
                    'max_approval_limit' => 1000000.0000,
                    'can_post_journals' => true,
                    'granted_at' => now(),
                    'granted_by_user_id' => $userId,
                ],
                [
                    'id' => Str::uuid(),
                    'user_id' => $userId,
                    'company_id' => $companyIdUk,
                    'role_id' => $accountantRoleId,
                    'max_approval_limit' => 500000.0000,
                    'can_post_journals' => true,
                    'granted_at' => now(),
                    'granted_by_user_id' => $userId,
                ],
            ]);

            // ----------------------------------------------------
            // 5. FISCAL YEARS & ACCOUNTING PERIODS
            // ----------------------------------------------------
            $fiscalYearId = Str::uuid();
            DB::table('fiscal_years')->insert([
                'id' => $fiscalYearId,
                'company_id' => $companyIdUs,
                'name' => 'FY ' . date('Y'),
                'start_date' => date('Y-01-01'),
                'end_date' => date('Y-12-31'),
                'is_closed' => false,
            ]);

            // Seed Monthly Period for Current Month
            $periodId = Str::uuid();
            DB::table('accounting_periods')->insert([
                'id' => $periodId,
                'fiscal_year_id' => $fiscalYearId,
                'period_number' => (int)date('n'),
                'start_date' => date('Y-m-01'),
                'end_date' => date('Y-m-t'),
                'is_closed' => false,
            ]);

            // ----------------------------------------------------
            // 6. CHART OF ACCOUNTS (CoA)
            // ----------------------------------------------------
            $cashAccount = Str::uuid();
            $arAccount = Str::uuid();
            $salesAccount = Str::uuid();
            $vatAccount = Str::uuid();

            DB::table('accounts')->insert([
                [
                    'id' => $cashAccount,
                    'company_id' => $companyIdUs,
                    'code' => '1010',
                    'name' => 'Bank Operating Account',
                    'parent_account_id' => null,
                    'account_type' => 'ASSET',
                    'normal_balance' => 'DEBIT',
                    'is_active' => true,
                ],
                [
                    'id' => $arAccount,
                    'company_id' => $companyIdUs,
                    'code' => '1200',
                    'name' => 'Accounts Receivable',
                    'parent_account_id' => null,
                    'account_type' => 'ASSET',
                    'normal_balance' => 'DEBIT',
                    'is_active' => true,
                ],
                [
                    'id' => $salesAccount,
                    'company_id' => $companyIdUs,
                    'code' => '4010',
                    'name' => 'Product Sales Revenue',
                    'parent_account_id' => null,
                    'account_type' => 'REVENUE',
                    'normal_balance' => 'CREDIT',
                    'is_active' => true,
                ],
                [
                    'id' => $vatAccount,
                    'company_id' => $companyIdUs,
                    'code' => '2200',
                    'name' => 'Sales Tax / VAT Payable',
                    'parent_account_id' => null,
                    'account_type' => 'LIABILITY',
                    'normal_balance' => 'CREDIT',
                    'is_active' => true,
                ],
            ]);

            // ----------------------------------------------------
            // 7. PARTIES & TAX RATES
            // ----------------------------------------------------
            $partyId = Str::uuid();
            DB::table('parties')->insert([
                'id' => $partyId,
                'company_id' => $companyIdUs,
                'party_type' => 'CUSTOMER',
                'legal_name' => 'Global Retail Partners Enterprises',
                'tax_number' => 'US-TAX-99812',
                'country_code' => 'US',
                'currency_code' => 'USD',
            ]);

            $taxRateId = Str::uuid();
            DB::table('tax_rates')->insert([
                'id' => $taxRateId,
                'company_id' => $companyIdUs,
                'code' => 'VAT_20',
                'rate' => 0.2000,
                'tax_account_id' => $vatAccount,
                'is_active' => true,
            ]);

            // ----------------------------------------------------
            // 8. DOUBLE-ENTRY JOURNAL ENTRY SAMPLE
            // ----------------------------------------------------
            $journalEntryId = Str::uuid();
            $entryNumber = 'JE-' . date('Ym') . '-0001';

            DB::table('journal_entries')->insert([
                'id' => $journalEntryId,
                'company_id' => $companyIdUs,
                'period_id' => $periodId,
                'entry_number' => $entryNumber,
                'posting_date' => date('Y-m-d'),
                'document_date' => date('Y-m-d'),
                'source_document_type' => 'INVOICE',
                'status' => 'POSTED',
                'description' => 'Initial Sales Revenue Recognition Invoice #1001',
                'created_by_user_id' => $userId,
                'created_at' => now(),
            ]);

            // Debit Line: Accounts Receivable ($1,200)
            DB::table('journal_lines')->insert([
                'id' => Str::uuid(),
                'entry_id' => $journalEntryId,
                'account_id' => $arAccount,
                'party_id' => $partyId,
                'tax_rate_id' => null,
                'txn_currency_code' => 'USD',
                'txn_debit' => 1200.0000,
                'txn_credit' => 0.0000,
                'fx_rate' => 1.000000,
                'base_debit' => 1200.0000,
                'base_credit' => 0.0000,
                'line_description' => 'Invoice #1001 Customer Receivable',
            ]);

            // Credit Line 1: Sales Revenue ($1,000)
            DB::table('journal_lines')->insert([
                'id' => Str::uuid(),
                'entry_id' => $journalEntryId,
                'account_id' => $salesAccount,
                'party_id' => $partyId,
                'tax_rate_id' => $taxRateId,
                'txn_currency_code' => 'USD',
                'txn_debit' => 0.0000,
                'txn_credit' => 1000.0000,
                'fx_rate' => 1.000000,
                'base_debit' => 0.0000,
                'base_credit' => 1000.0000,
                'line_description' => 'Product Sales Amount',
            ]);

            // Credit Line 2: VAT Tax Payable ($200)
            DB::table('journal_lines')->insert([
                'id' => Str::uuid(),
                'entry_id' => $journalEntryId,
                'account_id' => $vatAccount,
                'party_id' => $partyId,
                'tax_rate_id' => $taxRateId,
                'txn_currency_code' => 'USD',
                'txn_debit' => 0.0000,
                'txn_credit' => 200.0000,
                'fx_rate' => 1.000000,
                'base_debit' => 0.0000,
                'base_credit' => 200.0000,
                'line_description' => '20% Output VAT Collection',
            ]);

            // ----------------------------------------------------
            // 9. AUDIT LOG (SOX COMPLIANCE LOG)
            // ----------------------------------------------------
            DB::table('audit_logs')->insert([
                'id' => Str::uuid(),
                'company_id' => $companyIdUs,
                'user_id' => $userId,
                'action_type' => 'POST_JOURNAL',
                'table_name' => 'journal_entries',
                'record_id' => $journalEntryId,
                'new_state' => json_encode(['entry_number' => $entryNumber, 'amount' => 1200.00]),
                'ip_address' => '127.0.0.1',
                'timestamp' => now(),
            ]);
        });
    }
}