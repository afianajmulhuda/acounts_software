@extends('layouts.app')

@section('title', 'Financial Dashboard')
@section('page_title', 'Financial Overview')

@section('breadcrumb')
    <li class="breadcrumb-item active">Dashboard</li>
@endsection

@section('content')
<div class="row">
    <!-- Total Revenue -->
    <div class="col-lg-3 col-6">
        <div class="small-box text-bg-success">
            <div class="inner">
                <h3>$124,500</h3>
                <p>Total Revenue (MTD)</p>
            </div>
            <i class="small-box-icon bi bi-currency-dollar"></i>
        </div>
    </div>

    <!-- Accounts Receivable -->
    <div class="col-lg-3 col-6">
        <div class="small-box text-bg-warning">
            <div class="inner">
                <h3>$45,210</h3>
                <p>Accounts Receivable</p>
            </div>
            <i class="small-box-icon bi bi-journal-arrow-down"></i>
        </div>
    </div>

    <!-- Accounts Payable -->
    <div class="col-lg-3 col-6">
        <div class="small-box text-bg-danger">
            <div class="inner">
                <h3>$18,400</h3>
                <p>Accounts Payable</p>
            </div>
            <i class="small-box-icon bi bi-journal-arrow-up"></i>
        </div>
    </div>

    <!-- Net Cash Flow -->
    <div class="col-lg-3 col-6">
        <div class="small-box text-bg-primary">
            <div class="inner">
                <h3>$60,890</h3>
                <p>Net Cash Flow</p>
            </div>
            <i class="small-box-icon bi bi-bank"></i>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card mb-4">
            <div class="card-header"><h3 class="card-title">Cash Flow Trends</h3></div>
            <div class="card-body">
                <div id="financial-chart"></div>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card mb-4">
            <div class="card-header"><h3 class="card-title">Quick Actions</h3></div>
            <div class="card-body d-grid gap-2">
                <a href="#" class="btn btn-outline-primary"><i class="bi bi-plus-circle me-2"></i> Create Journal Entry</a>
                <a href="#" class="btn btn-outline-success"><i class="bi bi-file-earmark-plus me-2"></i> Generate Invoice</a>
                <a href="#" class="btn btn-outline-secondary"><i class="bi bi-download me-2"></i> Export Trial Balance</a>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/apexcharts@3.37.1/dist/apexcharts.min.js"></script>
<script>
    const options = {
        series: [
            { name: 'Income', data: [31000, 40000, 28000, 51000, 42000, 109000] },
            { name: 'Expenses', data: [11000, 32000, 45000, 32000, 34000, 52000] }
        ],
        chart: { height: 300, type: 'area' },
        colors: ['#198754', '#dc3545'],
        xaxis: { categories: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'] }
    };
    new ApexCharts(document.querySelector("#financial-chart"), options).render();
</script>
@endpush