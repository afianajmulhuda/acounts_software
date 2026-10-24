<?php

namespace Database\Seeders;

use App\Models\Account;
use App\Models\Company;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ChartOfAccountsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get target company or create a fallback company
        $company = Company::first() ?? Company::create([
            'id' => (string) Str::uuid(),
            'legal_name' => 'Acme Global Ltd',
            'tax_identifier' => 'GB123456789',
            'base_currency_code' => 'USD',
            'country_code' => 'US',
        ]);

        // Standard IFRS Hierarchical Chart of Accounts Structure
        $chartOfAccounts = [
            // -------------------------------------------------------------
            // 1. ASSETS (Normal Balance: DEBIT)
            // -------------------------------------------------------------
            [
                'code' => '1000',
                'name' => 'Assets',
                'account_type' => 'ASSET',
                'normal_balance' => 'DEBIT',
                'children' => [
                    [
                        'code' => '1100',
                        'name' => 'Current Assets',
                        'account_type' => 'ASSET',
                        'normal_balance' => 'DEBIT',
                        'children' => [
                            [
                                'code' => '1110',
                                'name' => 'Cash & Cash Equivalents',
                                'account_type' => 'ASSET',
                                'normal_balance' => 'DEBIT',
                                'children' => [
                                    ['code' => '1111', 'name' => 'Operating Bank Account (USD)', 'account_type' => 'ASSET', 'normal_balance' => 'DEBIT'],
                                    ['code' => '1112', 'name' => 'Petty Cash', 'account_type' => 'ASSET', 'normal_balance' => 'DEBIT'],
                                ]
                            ],
                            [
                                'code' => '1120',
                                'name' => 'Trade Receivables (Accounts Receivable)',
                                'account_type' => 'ASSET',
                                'normal_balance' => 'DEBIT',
                                'children' => [
                                    ['code' => '1121', 'name' => 'Trade Debtors', 'account_type' => 'ASSET', 'normal_balance' => 'DEBIT'],
                                    ['code' => '1129', 'name' => 'Allowance for Expected Credit Losses (ECL)', 'account_type' => 'ASSET', 'normal_balance' => 'CREDIT'], // Contra-Asset
                                ]
                            ],
                            [
                                'code' => '1130',
                                'name' => 'Inventories',
                                'account_type' => 'ASSET',
                                'normal_balance' => 'DEBIT',
                                'children' => [
                                    ['code' => '1131', 'name' => 'Finished Goods', 'account_type' => 'ASSET', 'normal_balance' => 'DEBIT'],
                                    ['code' => '1132', 'name' => 'Raw Materials', 'account_type' => 'ASSET', 'normal_balance' => 'DEBIT'],
                                ]
                            ],
                        ]
                    ],
                    [
                        'code' => '1200',
                        'name' => 'Non-Current Assets',
                        'account_type' => 'ASSET',
                        'normal_balance' => 'DEBIT',
                        'children' => [
                            [
                                'code' => '1210',
                                'name' => 'Property, Plant and Equipment (PPE)',
                                'account_type' => 'ASSET',
                                'normal_balance' => 'DEBIT',
                                'children' => [
                                    ['code' => '1211', 'name' => 'Computer Hardware & Servers', 'account_type' => 'ASSET', 'normal_balance' => 'DEBIT'],
                                    ['code' => '1219', 'name' => 'Accumulated Depreciation - PPE', 'account_type' => 'ASSET', 'normal_balance' => 'CREDIT'], // Contra-Asset
                                ]
                            ],
                            [
                                'code' => '1220',
                                'name' => 'Intangible Assets',
                                'account_type' => 'ASSET',
                                'normal_balance' => 'DEBIT',
                                'children' => [
                                    ['code' => '1221', 'name' => 'Software Licenses', 'account_type' => 'ASSET', 'normal_balance' => 'DEBIT'],
                                    ['code' => '1229', 'name' => 'Accumulated Amortization - Intangibles', 'account_type' => 'ASSET', 'normal_balance' => 'CREDIT'],
                                ]
                            ]
                        ]
                    ]
                ]
            ],

            // -------------------------------------------------------------
            // 2. LIABILITIES (Normal Balance: CREDIT)
            // -------------------------------------------------------------
            [
                'code' => '2000',
                'name' => 'Liabilities',
                'account_type' => 'LIABILITY',
                'normal_balance' => 'CREDIT',
                'children' => [
                    [
                        'code' => '2100',
                        'name' => 'Current Liabilities',
                        'account_type' => 'LIABILITY',
                        'normal_balance' => 'CREDIT',
                        'children' => [
                            ['code' => '2110', 'name' => 'Trade Payables (Accounts Payable)', 'account_type' => 'LIABILITY', 'normal_balance' => 'CREDIT'],
                            ['code' => '2120', 'name' => 'Accrued Expenses & Provisions', 'account_type' => 'LIABILITY', 'normal_balance' => 'CREDIT'],
                            ['code' => '2130', 'name' => 'Output VAT / GST Payable', 'account_type' => 'LIABILITY', 'normal_balance' => 'CREDIT'],
                            ['code' => '2140', 'name' => 'Payroll Taxes Payable', 'account_type' => 'LIABILITY', 'normal_balance' => 'CREDIT'],
                        ]
                    ],
                    [
                        'code' => '2200',
                        'name' => 'Non-Current Liabilities',
                        'account_type' => 'LIABILITY',
                        'normal_balance' => 'CREDIT',
                        'children' => [
                            ['code' => '2210', 'name' => 'Long-Term Bank Borrowings', 'account_type' => 'LIABILITY', 'normal_balance' => 'CREDIT'],
                            ['code' => '2220', 'name' => 'Lease Liabilities (IFRS 16)', 'account_type' => 'LIABILITY', 'normal_balance' => 'CREDIT'],
                        ]
                    ]
                ]
            ],

            // -------------------------------------------------------------
            // 3. EQUITY (Normal Balance: CREDIT)
            // -------------------------------------------------------------
            [
                'code' => '3000',
                'name' => 'Equity',
                'account_type' => 'EQUITY',
                'normal_balance' => 'CREDIT',
                'children' => [
                    ['code' => '3100', 'name' => 'Share Capital', 'account_type' => 'EQUITY', 'normal_balance' => 'CREDIT'],
                    ['code' => '3200', 'name' => 'Retained Earnings', 'account_type' => 'EQUITY', 'normal_balance' => 'CREDIT'],
                    ['code' => '3300', 'name' => 'Other Comprehensive Income (OCI) Reserve', 'account_type' => 'EQUITY', 'normal_balance' => 'CREDIT'],
                ]
            ],

            // -------------------------------------------------------------
            // 4. REVENUE (Normal Balance: CREDIT)
            // -------------------------------------------------------------
            [
                'code' => '4000',
                'name' => 'Revenue',
                'account_type' => 'REVENUE',
                'normal_balance' => 'CREDIT',
                'children' => [
                    [
                        'code' => '4100',
                        'name' => 'Operating Revenue',
                        'account_type' => 'REVENUE',
                        'normal_balance' => 'CREDIT',
                        'children' => [
                            ['code' => '4110', 'name' => 'SaaS Subscription Revenue', 'account_type' => 'REVENUE', 'normal_balance' => 'CREDIT'],
                            ['code' => '4120', 'name' => 'Professional Services Income', 'account_type' => 'REVENUE', 'normal_balance' => 'CREDIT'],
                        ]
                    ],
                    [
                        'code' => '4200',
                        'name' => 'Other Income',
                        'account_type' => 'REVENUE',
                        'normal_balance' => 'CREDIT',
                        'children' => [
                            ['code' => '4210', 'name' => 'Interest Income', 'account_type' => 'REVENUE', 'normal_balance' => 'CREDIT'],
                            ['code' => '4220', 'name' => 'Foreign Exchange Gains (Realized)', 'account_type' => 'REVENUE', 'normal_balance' => 'CREDIT'],
                        ]
                    ]
                ]
            ],

            // -------------------------------------------------------------
            // 5. EXPENSES (Normal Balance: DEBIT)
            // -------------------------------------------------------------
            [
                'code' => '5000',
                'name' => 'Expenses',
                'account_type' => 'EXPENSE',
                'normal_balance' => 'DEBIT',
                'children' => [
                    [
                        'code' => '5100',
                        'name' => 'Cost of Goods Sold (COGS)',
                        'account_type' => 'EXPENSE',
                        'normal_balance' => 'DEBIT',
                        'children' => [
                            ['code' => '5110', 'name' => 'Cloud Hosting & Infrastructure (AWS/GCP)', 'account_type' => 'EXPENSE', 'normal_balance' => 'DEBIT'],
                            ['code' => '5120', 'name' => 'Direct Third-Party API Costs', 'account_type' => 'EXPENSE', 'normal_balance' => 'DEBIT'],
                        ]
                    ],
                    [
                        'code' => '5200',
                        'name' => 'Operating Expenses (OPEX)',
                        'account_type' => 'EXPENSE',
                        'normal_balance' => 'DEBIT',
                        'children' => [
                            ['code' => '5210', 'name' => 'Salaries & Wages', 'account_type' => 'EXPENSE', 'normal_balance' => 'DEBIT'],
                            ['code' => '5220', 'name' => 'Software & Tool Subscriptions', 'account_type' => 'EXPENSE', 'normal_balance' => 'DEBIT'],
                            ['code' => '5230', 'name' => 'Legal & Professional Fees', 'account_type' => 'EXPENSE', 'normal_balance' => 'DEBIT'],
                            ['code' => '5240', 'name' => 'Depreciation & Amortization Expense', 'account_type' => 'EXPENSE', 'normal_balance' => 'DEBIT'],
                        ]
                    ],
                    [
                        'code' => '5300',
                        'name' => 'Finance Costs',
                        'account_type' => 'EXPENSE',
                        'normal_balance' => 'DEBIT',
                        'children' => [
                            ['code' => '5310', 'name' => 'Bank Service Charges', 'account_type' => 'EXPENSE', 'normal_balance' => 'DEBIT'],
                            ['code' => '5320', 'name' => 'Interest Expense', 'account_type' => 'EXPENSE', 'normal_balance' => 'DEBIT'],
                            ['code' => '5330', 'name' => 'Foreign Exchange Losses (Realized)', 'account_type' => 'EXPENSE', 'normal_balance' => 'DEBIT'],
                        ]
                    ]
                ]
            ]
        ];

        // Seed Chart of Accounts iteratively across company
        foreach ($chartOfAccounts as $accountData) {
            $this->createAccountRecursive($company->id, $accountData, null);
        }
    }

    /**
     * Recursively inserts accounts along with their parent-child relationships.
     */
    private function createAccountRecursive(string $companyId, array $data, ?string $parentId): void
    {
        $account = Account::create([
            'id' => (string) Str::uuid(),
            'company_id' => $companyId,
            'code' => $data['code'],
            'name' => $data['name'],
            'parent_account_id' => $parentId,
            'account_type' => $data['account_type'],
            'normal_balance' => $data['normal_balance'],
            'is_active' => true,
        ]);

        if (!empty($data['children'])) {
            foreach ($data['children'] as $childData) {
                $this->createAccountRecursive($companyId, $childData, $account->id);
            }
        }
    }
}