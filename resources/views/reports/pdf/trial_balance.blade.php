<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Trial Balance</title>
    <style>
        body { font-family: sans-serif; font-size: 11px; color: #333; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #000; padding-bottom: 10px; }
        .table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        .table th, .table td { border: 1px solid #ddd; padding: 6px 8px; }
        .table th { background-color: #f2f2f2; text-align: left; }
        .text-right { text-align: right; }
        .fw-bold { font-weight: bold; }
    </style>
</head>
<body>
    <div class="header">
        <h2>{{ $company->legal_name ?? 'Enterprise ERP' }}</h2>
        <h3>TRIAL BALANCE STATEMENT</h3>
        <p>As of Date: {{ $asOfDate }}</p>
        <div class="content-header">
            <div class="container-fluid d-flex justify-content-between align-items-center">
                <h1 class="m-0">Trial Balance Report</h1>
                <div class="btn-group">
                    <a href="{{ route('reports.trial-balance.csv', ['as_of_date' => $asOfDate]) }}" class="btn btn-outline-success">
                        <i class="bi bi-file-earmark-spreadsheet me-1"></i> Export CSV
                    </a>
                    <a href="{{ route('reports.trial-balance.pdf', ['as_of_date' => $asOfDate]) }}" class="btn btn-outline-danger">
                        <i class="bi bi-file-earmark-pdf me-1"></i> Download PDF
                    </a>
                </div>
            </div>
        </div>
    </div>

    <table class="table">
        <thead>
            <tr>
                <th>Code</th>
                <th>Account Name</th>
                <th>Type</th>
                <th class="text-right">Debit ($)</th>
                <th class="text-right">Credit ($)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($accounts as $acc)
            <tr>
                <td><code>{{ $acc->code }}</code></td>
                <td>{{ $acc->name }}</td>
                <td>{{ $acc->account_type }}</td>
                <td class="text-right">{{ number_format($acc->total_debit, 2) }}</td>
                <td class="text-right">{{ number_format($acc->total_credit, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="fw-bold">
                <td colspan="3" class="text-right">Total Balances:</td>
                <td class="text-right">${{ number_format($grandTotalDebit, 2) }}</td>
                <td class="text-right">${{ number_format($grandTotalCredit, 2) }}</td>
            </tr>
        </tfoot>
    </table>
</body>
</html>