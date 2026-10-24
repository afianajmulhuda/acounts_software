<?php

namespace Database\Seeders;

use App\Models\Account;
use App\Models\Company;
use App\Models\TaxRate;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class TaxRatesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Fetch Target Company
        $company = Company::first();

        if (!$company) {
            $this->command->error('No company found. Please run Company/ChartOfAccountsSeeder first.');
            return;
        }

        // 2. Fetch Relevant Liability/Asset GL Accounts created by ChartOfAccountsSeeder
        $outputVatAccount = Account::where('company_id', $company->id)
            ->where('code', '2130') // Output VAT / GST Payable
            ->first();

        $operatingExpenseAccount = Account::where('company_id', $company->id)
            ->where('code', '5200') // Default fallback account if tax account isn't isolated
            ->first();

        $taxAccountId = $outputVatAccount?->id ?? $operatingExpenseAccount?->id;

        // 3. International Standard Tax Codes Matrix
        $standardTaxRates = [
            // --- Zero & Exempt Standard Codes (Global) ---
            [
                'code' => 'ZERO_0',
                'rate' => 0.0000, // 0%
                'is_active' => true,
            ],
            [
                'code' => 'EXEMPT',
                'rate' => 0.0000, // 0% (Tax Exempt)
                'is_active' => true,
            ],

            // --- European Union & UK (VAT System) ---
            [
                'code' => 'VAT_20',
                'rate' => 0.2000, // 20.00% (UK, FR, DE standard range)
                'is_active' => true,
            ],
            [
                'code' => 'VAT_5',
                'rate' => 0.0500, // 5.00% (Reduced Rate)
                'is_active' => true,
            ],

            // --- Asia / APAC (GST System) ---
            [
                'code' => 'GST_18',
                'rate' => 0.1800, // 18.00% (India / APAC standard)
                'is_active' => true,
            ],
            [
                'code' => 'GST_10',
                'rate' => 0.1000, // 10.00% (Australia / NZ standard)
                'is_active' => true,
            ],

            // --- United States & Americas (Sales Tax System) ---
            [
                'code' => 'US_CA_825',
                'rate' => 0.0825, // 8.25% (State + Local Combined Sales Tax)
                'is_active' => true,
            ],
            [
                'code' => 'US_NY_8875',
                'rate' => 0.08875, // 8.875% (New York State & City)
                'is_active' => true,
            ],
        ];

        // 4. Seed Tax Rates into Database
        foreach ($standardTaxRates as $taxData) {
            TaxRate::updateOrCreate(
                [
                    'company_id' => $company->id,
                    'code' => $taxData['code'],
                ],
                [
                    'id' => (string) Str::uuid(),
                    'rate' => $taxData['rate'],
                    'tax_account_id' => $taxAccountId,
                    'is_active' => $taxData['is_active'],
                ]
            );
        }

        $this->command->info('Standard International Tax Rates successfully seeded and linked to GL Account Code 2130!');
    }
}