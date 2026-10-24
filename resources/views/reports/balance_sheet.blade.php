@extends('layouts.app')

@section('content')
<div class="content-header">
    <div class="container-fluid d-flex justify-content-between align-items-center">
        <h1 class="m-0">Balance Sheet & Financial Summary</h1>
    </div>
</div>

<div class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-6">
                <div class="card card-outline card-success">
                    <div class="card-header"><h5 class="card-title m-0"><i class="bi bi-wallet2 me-1"></i> Assets</h5></div>
                    <div class="card-body fs-4 d-flex justify-content-between">
                        <span>Total Assets:</span>
                        <strong class="text-success">${{ number_format($reportData['ASSET'], 2) }}</strong>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card card-outline card-danger">
                    <div class="card-header"><h5 class="card-title m-0"><i class="bi bi-credit-card me-1"></i> Liabilities & Equity</h5></div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-2"><span>Total Liabilities:</span> <strong>${{ number_format($reportData['LIABILITY'], 2) }}</strong></div>
                        <div class="d-flex justify-content-between mb-2"><span>Retained Equity:</span> <strong>${{ number_format($reportData['EQUITY'], 2) }}</strong></div>
                        <div class="d-flex justify-content-between mb-2"><span>Current Period Net Income:</span> <strong class="text-info">${{ number_format($netIncome, 2) }}</strong></div>
                        <hr>
                        <div class="d-flex justify-content-between fs-5 fw-bold"><span>Total Liabilities & Equity:</span> <span class="text-danger">${{ number_format($totalEquityAndLiabilities, 2) }}</span></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection