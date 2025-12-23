@extends('layouts.user')

@section('title', 'Investment Analytics')

@section('content_header')
    <h1>Investment Analytics</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-chart-line"></i> Comprehensive Investment Analytics
                    </h3>
                    <div class="card-tools">
                        <span class="badge badge-success" id="live-indicator">
                            <i class="fas fa-circle"></i> Live
                        </span>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Summary Cards -->
                    <div class="row mb-4">
                        <div class="col-lg-3 col-md-6">
                            <div class="info-box bg-gradient-info">
                                <span class="info-box-icon"><i class="fas fa-wallet"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Current Balance</span>
                                    <span class="info-box-number" id="current-balance">{{ number_format($analytics['wallet']['balance'], 2) }} USDT</span>
                                    <div class="progress">
                                        <div class="progress-bar" style="width: 100%"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-6">
                            <div class="info-box bg-gradient-success">
                                <span class="info-box-icon"><i class="fas fa-chart-line"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Total Invested</span>
                                    <span class="info-box-number" id="total-invested-main">{{ number_format($analytics['wallet']['invested_amount'], 2) }} USDT</span>
                                    <div class="progress">
                                        <div class="progress-bar" style="width: 100%"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-6">
                            <div class="info-box bg-gradient-warning">
                                <span class="info-box-icon"><i class="fas fa-coins"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Total Earned</span>
                                    <span class="info-box-number" id="total-earned-main">{{ number_format($analytics['wallet']['earned_amount'], 2) }} USDT</span>
                                    <div class="progress">
                                        <div class="progress-bar" style="width: 100%"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-6">
                            <div class="info-box bg-gradient-danger">
                                <span class="info-box-icon"><i class="fas fa-percentage"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Growth Rate</span>
                                    <span class="info-box-number" id="growth-rate-main">{{ number_format($analytics['realtime']['growth_percentage'], 2) }}%</span>
                                    <div class="progress">
                                        <div class="progress-bar" style="width: {{ min($analytics['realtime']['growth_percentage'], 100) }}%"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Interest Generation Overview -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="card card-outline card-primary">
                                <div class="card-header">
                                    <h3 class="card-title"><i class="fas fa-money-bill-wave"></i> Interest Generation Overview</h3>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-3 col-sm-6 mb-3">
                                            <div class="text-center">
                                                <h2 class="text-primary" id="daily-interest-main">{{ number_format($analytics['investments']['daily_interest'], 2) }}</h2>
                                                <p class="text-muted">Daily Interest (USDT)</p>
                                            </div>
                                        </div>
                                        <div class="col-md-3 col-sm-6 mb-3">
                                            <div class="text-center">
                                                <h2 class="text-success" id="weekly-interest-main">{{ number_format($analytics['investments']['daily_interest'] * 7, 2) }}</h2>
                                                <p class="text-muted">Weekly Interest (USDT)</p>
                                            </div>
                                        </div>
                                        <div class="col-md-3 col-sm-6 mb-3">
                                            <div class="text-center">
                                                <h2 class="text-warning" id="monthly-interest-main">{{ number_format($analytics['investments']['monthly_interest'], 2) }}</h2>
                                                <p class="text-muted">Monthly Interest (USDT)</p>
                                            </div>
                                        </div>
                                        <div class="col-md-3 col-sm-6 mb-3">
                                            <div class="text-center">
                                                <h2 class="text-info" id="yearly-interest-main">{{ number_format($analytics['investments']['yearly_interest'], 2) }}</h2>
                                                <p class="text-muted">Yearly Interest (USDT)</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Charts Section -->
                    <div class="row mb-4">
                        <div class="col-lg-6">
                            <div class="card">
                                <div class="card-header">
                                    <h3 class="card-title"><i class="fas fa-chart-area"></i> Weekly Earnings Trend</h3>
                                </div>
                                <div class="card-body">
                                    <canvas id="weeklyEarningsChart" height="250"></canvas>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="card">
                                <div class="card-header">
                                    <h3 class="card-title"><i class="fas fa-chart-bar"></i> Monthly Earnings Breakdown</h3>
                                </div>
                                <div class="card-body">
                                    <canvas id="monthlyEarningsChart" height="250"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Detailed Interest Breakdown -->
                    <div class="row mb-4">
                        <div class="col-lg-6">
                            <div class="card">
                                <div class="card-header">
                                    <h3 class="card-title"><i class="fas fa-table"></i> Interest Breakdown by Period</h3>
                                </div>
                                <div class="card-body p-0">
                                    <table class="table table-hover">
                                        <thead class="bg-light">
                                            <tr>
                                                <th>Period</th>
                                                <th>Earnings (USDT)</th>
                                                <th>Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td><strong>Today</strong></td>
                                                <td id="interest-today-detail">{{ number_format($analytics['interest']['today'], 2) }}</td>
                                                <td><span class="badge badge-success"><i class="fas fa-check-circle"></i> Active</span></td>
                                            </tr>
                                            <tr>
                                                <td><strong>This Week</strong></td>
                                                <td id="interest-week-detail">{{ number_format($analytics['interest']['this_week'], 2) }}</td>
                                                <td><span class="badge badge-info">{{ number_format($analytics['weekly']['total'], 2) }} Total</span></td>
                                            </tr>
                                            <tr>
                                                <td><strong>This Month</strong></td>
                                                <td id="interest-month-detail">{{ number_format($analytics['interest']['this_month'], 2) }}</td>
                                                <td><span class="badge badge-primary">{{ number_format($analytics['monthly']['total'], 2) }} Total</span></td>
                                            </tr>
                                            <tr>
                                                <td><strong>All Time</strong></td>
                                                <td id="interest-alltime-detail">{{ number_format($analytics['interest']['all_time'], 2) }}</td>
                                                <td><span class="badge badge-warning"><i class="fas fa-infinity"></i> Lifetime</span></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="card">
                                <div class="card-header">
                                    <h3 class="card-title"><i class="fas fa-info-circle"></i> Investment Statistics</h3>
                                </div>
                                <div class="card-body">
                                    <dl class="row">
                                        <dt class="col-sm-6">Active Investments:</dt>
                                        <dd class="col-sm-6" id="active-investments">{{ $analytics['investments']['active_count'] }}</dd>

                                        <dt class="col-sm-6">Total Expected Return:</dt>
                                        <dd class="col-sm-6" id="expected-return">{{ number_format($analytics['investments']['total_expected_return'], 2) }} USDT</dd>

                                        <dt class="col-sm-6">Average Daily Rate:</dt>
                                        <dd class="col-sm-6" id="avg-daily-rate">{{ number_format($analytics['investments']['average_daily_rate'], 2) }}%</dd>

                                        <dt class="col-sm-6">Days Active:</dt>
                                        <dd class="col-sm-6" id="days-active-detail">{{ $analytics['realtime']['days_active'] }}</dd>

                                        <dt class="col-sm-6">Days Remaining:</dt>
                                        <dd class="col-sm-6" id="days-remaining-detail">{{ $analytics['realtime']['days_remaining'] }}</dd>

                                        <dt class="col-sm-6">Current Value:</dt>
                                        <dd class="col-sm-6" id="current-value">{{ number_format($analytics['realtime']['current_value'], 2) }} USDT</dd>

                                        <dt class="col-sm-6">Projected Value:</dt>
                                        <dd class="col-sm-6" id="projected-value">{{ number_format($analytics['realtime']['projected_value'], 2) }} USDT</dd>
                                    </dl>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="row">
                        <div class="col-12 text-center">
                            <a href="{{ route('dashboard') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Back to Dashboard
                            </a>
                            <a href="{{ route('investments.index') }}" class="btn btn-primary">
                                <i class="fas fa-chart-line"></i> View Investments
                            </a>
                            <a href="{{ route('wallet.transactions') }}" class="btn btn-info">
                                <i class="fas fa-list"></i> View Transactions
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

@section('js')
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
<script>
let weeklyChart, monthlyChart;
let updateInterval;

// Initialize charts
function initCharts() {
    const weeklyData = @json($analytics['weekly']['data']);
    const monthlyData = @json($analytics['monthly']['data']);

    // Weekly Earnings Chart
    const weeklyCtx = document.getElementById('weeklyEarningsChart');
    if (weeklyCtx) {
        weeklyChart = new Chart(weeklyCtx, {
            type: 'line',
            data: {
                labels: weeklyData.map(d => d.day),
                datasets: [{
                    label: 'Daily Earnings (USDT)',
                    data: weeklyData.map(d => d.earnings),
                    backgroundColor: 'rgba(54, 162, 235, 0.2)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4,
                    pointRadius: 5,
                    pointHoverRadius: 7
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: true,
                        position: 'top'
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return 'Earnings: ' + context.parsed.y.toFixed(2) + ' USDT';
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return value.toFixed(2) + ' USDT';
                            }
                        }
                    }
                }
            }
        });
    }

    // Monthly Earnings Chart
    const monthlyCtx = document.getElementById('monthlyEarningsChart');
    if (monthlyCtx) {
        monthlyChart = new Chart(monthlyCtx, {
            type: 'bar',
            data: {
                labels: monthlyData.map(d => d.week_label),
                datasets: [{
                    label: 'Weekly Earnings (USDT)',
                    data: monthlyData.map(d => d.earnings),
                    backgroundColor: 'rgba(75, 192, 192, 0.7)',
                    borderColor: 'rgba(75, 192, 192, 1)',
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: true,
                        position: 'top'
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return 'Earnings: ' + context.parsed.y.toFixed(2) + ' USDT';
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return value.toFixed(2) + ' USDT';
                            }
                        }
                    }
                }
            }
        });
    }
}

// Update analytics data
function updateAnalytics() {
    fetch('{{ route("analytics.get") }}', {
        method: 'GET',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success && data.data) {
            const analytics = data.data;
            
            // Update main stats
            updateElement('current-balance', analytics.wallet.balance.toFixed(2) + ' USDT');
            updateElement('total-invested-main', analytics.wallet.invested_amount.toFixed(2) + ' USDT');
            updateElement('total-earned-main', analytics.wallet.earned_amount.toFixed(2) + ' USDT');
            updateElement('growth-rate-main', analytics.realtime.growth_percentage.toFixed(2) + '%');
            
            // Update interest generation
            updateElement('daily-interest-main', analytics.investments.daily_interest.toFixed(2));
            updateElement('weekly-interest-main', (analytics.investments.daily_interest * 7).toFixed(2));
            updateElement('monthly-interest-main', analytics.investments.monthly_interest.toFixed(2));
            updateElement('yearly-interest-main', analytics.investments.yearly_interest.toFixed(2));
            
            // Update interest breakdown
            updateElement('interest-today-detail', analytics.interest.today.toFixed(2));
            updateElement('interest-week-detail', analytics.interest.this_week.toFixed(2));
            updateElement('interest-month-detail', analytics.interest.this_month.toFixed(2));
            updateElement('interest-alltime-detail', analytics.interest.all_time.toFixed(2));
            
            // Update investment stats
            updateElement('active-investments', analytics.investments.active_count);
            updateElement('expected-return', analytics.investments.total_expected_return.toFixed(2) + ' USDT');
            updateElement('avg-daily-rate', analytics.investments.average_daily_rate.toFixed(2) + '%');
            updateElement('days-active-detail', analytics.realtime.days_active);
            updateElement('days-remaining-detail', analytics.realtime.days_remaining);
            updateElement('current-value', analytics.realtime.current_value.toFixed(2) + ' USDT');
            updateElement('projected-value', analytics.realtime.projected_value.toFixed(2) + ' USDT');
            
            // Update charts
            if (analytics.weekly && weeklyChart) {
                weeklyChart.data.datasets[0].data = analytics.weekly.data.map(d => d.earnings);
                weeklyChart.update();
            }
            
            if (analytics.monthly && monthlyChart) {
                monthlyChart.data.datasets[0].data = analytics.monthly.data.map(d => d.earnings);
                monthlyChart.update();
            }
            
            // Pulse live indicator
            const liveIndicator = document.getElementById('live-indicator');
            if (liveIndicator) {
                liveIndicator.style.animation = 'pulse 1s ease-in-out';
                setTimeout(() => {
                    liveIndicator.style.animation = '';
                }, 1000);
            }
        }
    })
    .catch(error => {
        console.error('Error updating analytics:', error);
    });
}

// Helper function to update element with animation
function updateElement(id, value) {
    const element = document.getElementById(id);
    if (element) {
        const oldValue = element.textContent;
        if (oldValue !== value) {
            element.style.transition = 'all 0.3s ease';
            element.style.color = '#28a745';
            element.textContent = value;
            setTimeout(() => {
                element.style.color = '';
            }, 1000);
        }
    }
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    initCharts();
    
    // Update analytics every 30 seconds
    updateInterval = setInterval(updateAnalytics, 30000);
    
    // Initial update after 5 seconds
    setTimeout(updateAnalytics, 5000);
});

// Clean up on page unload
window.addEventListener('beforeunload', function() {
    if (updateInterval) {
        clearInterval(updateInterval);
    }
});
</script>

<style>
@keyframes pulse {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.5; }
}
</style>
@stop
