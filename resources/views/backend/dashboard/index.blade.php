@extends('backend.template.main')

@section('title', 'Enhanced Dashboard')

@push('css')
<style>
:root {
    --primary-color: #FFB6C1;
    --secondary-color: #B0E0E6;
    --background-color: #F9F9F9;
}

body {
    font-family: 'Open Sans', sans-serif;
    background-color: var(--background-color);
}

.card {
    border-radius: 15px;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
    position: relative;
    overflow: hidden;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.card::before {
    content: '';
    position: absolute;
    top: -30px;
    left: 0;
    right: 0;
    height: 60px;
    border-radius: 50%;
    transform: scaleX(1.5);
}

.card:hover {
    transform: translateY(-5px) rotate(1deg);
    box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
}

.card-title {
    color: #fff;
    font-size: 1.2rem;
    font-weight: bold;
    position: relative;
    z-index: 1;
}

.card-body {
    padding: 20px;
    color: #fff;
    text-align: center;
    font-size: 1.2rem;
    position: relative;
    z-index: 1;
}

.stat-number {
    font-size: 2rem;
    font-weight: bold;
    margin-top: 10px;
}

.trend-icon {
    font-size: 1.5rem;
    margin-left: 5px;
}

.table {
    border-radius: 10px;
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
    background-color: #fff;
}

.table th,
.table td {
    padding: 15px;
    border: none;
    border-bottom: 1px solid #f0f0f0;
}

.table th {
    background-color: var(--secondary-color);
    color: #333;
}

.status-badge {
    padding: 5px 10px;
    border-radius: 20px;
    font-weight: bold;
}

.status-pending { background-color: #FFA500; color: #fff; }
.status-success { background-color: #32CD32; color: #fff; }
.status-failed { background-color: #FF6347; color: #fff; }

.chart-container {
    height: 300px;
    margin-top: 20px;
}

</style>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
@endpush

@section('content')
<div class="py-4">
    <!-- Enhanced Statistics Section -->
    <div class="row mb-4">
        <div class="col-12 col-md-3 mb-3">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Total Transactions</h5>
                    <div class="stat-number">{{ $totalTransactions }}</div>
                    <div class="trend">
                        @if($transactionTrend > 0)
                            <span class="trend-icon text-success">↑</span>
                        @elseif($transactionTrend < 0)
                            <span class="trend-icon text-danger">↓</span>
                        @else
                            <span class="trend-icon text-muted">→</span>
                        @endif
                        {{ abs($transactionTrend) }}% from last week
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-3 mb-3">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Total Revenue</h5>
                    <div class="stat-number">Rp. {{ number_format($totalRevenue, 0, ',', '.') }}</div>
                    <div class="trend">
                        @if($revenueTrend > 0)
                            <span class="trend-icon text-success">↑</span>
                        @elseif($revenueTrend < 0)
                            <span class="trend-icon text-danger">↓</span>
                        @else
                            <span class="trend-icon text-muted">→</span>
                        @endif
                        {{ abs($revenueTrend) }}% from last week
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-3 mb-3">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Average Transaction Value</h5>
                    <div class="stat-number">Rp. {{ number_format($averageTransactionValue, 0, ',', '.') }}</div>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-3 mb-3">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Success Rate</h5>
                    <div class="stat-number">{{ number_format($successRate, 1) }}%</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Transaction Status Chart -->
    <div class="row">
        <div class="col-12 col-md-6 mb-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Transaction Status Distribution</h5>
                    <div class="chart-container">
                        <canvas id="transactionStatusChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-6 mb-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Daily Transaction Volume (Last 7 Days)</h5>
                    <div class="chart-container">
                        <canvas id="dailyTransactionChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Latest Transactions Table -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Latest Transactions</h5>
                </div>
                <div class="table-responsive">
                    <table class="table align-items-center">
                        <thead>
                            <tr>
                                <th>Transaction ID</th>
                                <th>Name</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th>Date & Time</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($latestTransactions as $transaction)
                            <tr>
                                <td>{{ $transaction->uuid }}</td>
                                <td>{{ $transaction->name }}</td>
                                <td>Rp. {{ number_format($transaction->amount, 0, ',', '.') }}</td>
                                <td>
                                    <span class="status-badge status-{{ $transaction->status }}">
                                        {{ ucfirst($transaction->status) }}
                                    </span>
                                </td>
                                <td>{{ $transaction->created_at->format('Y-m-d H:i') }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

@push('js')
<script>
// Transaction Status Chart
let statusCtx = document.getElementById('transactionStatusChart').getContext('2d');
let statusChart = new Chart(statusCtx, {
    type: 'doughnut',
    data: {
        labels: ['Pending', 'Success', 'Failed'],
        datasets: [{
            data: [{{ $pendingTransactions }}, {{ $successfulTransactions }}, {{ $failedTransactions }}],
            backgroundColor: ['#FFA500', '#32CD32', '#FF6347']
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false
    }
});

// Daily Transaction Volume Chart
let dailyCtx = document.getElementById('dailyTransactionChart').getContext('2d');
let dailyChart = new Chart(dailyCtx, {
    type: 'line',
    data: {
        labels: {!! json_encode($dailyTransactionLabels) !!},
        datasets: [{
            label: 'Transactions',
            data: {!! json_encode($dailyTransactionData) !!},
            borderColor: '#4e73df',
            tension: 0.1
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
            y: {
                beginAtZero: true
            }
        }
    }
});
</script>
@endpush

@endsection
