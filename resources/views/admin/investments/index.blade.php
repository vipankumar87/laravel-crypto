@extends('adminlte::page')

@section('title', 'All Investments')

@section('content_header')
    <h1>All Investments</h1>
@stop

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Investment Overview</h3>
        </div>
        <div class="card-body table-responsive">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>User</th>
                        <th>Plan</th>
                        <th>Amount</th>
                        <th>Expected Return</th>
                        <th>Current Return</th>
                        <th>Daily Rate</th>
                        <th>Start Date</th>
                        <th>End Date</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($investments ?? [] as $investment)
                        <tr>
                            <td>{{ $investment->id }}</td>
                            <td>
                                <a href="{{ route('admin.users.show', $investment->user ?? 1) }}">
                                    {{ $investment->user->name ?? 'Unknown User' }}
                                </a>
                            </td>
                            <td>{{ $investment->investment_plan ?? 'N/A' }}</td>
                            <td>${{ number_format($investment->amount ?? 0, 2) }}</td>
                            <td>${{ number_format($investment->expected_return ?? 0, 2) }}</td>
                            <td>${{ number_format($investment->current_return ?? 0, 2) }}</td>
                            <td>{{ number_format($investment->daily_return_rate ?? 0, 2) }}%</td>
                            <td>{{ ($investment->start_date ?? now())->format('M d, Y') }}</td>
                            <td>{{ ($investment->end_date ?? now())->format('M d, Y') }}</td>
                            <td>
                                <span class="badge badge-{{
                                    ($investment->status ?? 'pending') === 'active' ? 'success' :
                                    (($investment->status ?? 'pending') === 'completed' ? 'primary' : 'warning')
                                }}">
                                    {{ ucfirst($investment->status ?? 'pending') }}
                                </span>
                            </td>
                            <td>
                                <div class="btn-group">
                                    <button type="button" class="btn btn-sm btn-info" onclick="viewInvestment({{ $investment->id ?? 1 }})">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    @if(($investment->status ?? 'active') === 'active')
                                        <button type="button" class="btn btn-sm btn-warning" onclick="pauseInvestment({{ $investment->id ?? 1 }})">
                                            <i class="fas fa-pause"></i>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="11" class="text-center text-muted">No investments found</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if(isset($investments) && $investments->hasPages())
            <div class="card-footer">
                {{ $investments->links() }}
            </div>
        @endif
    </div>

    <!-- Investment Statistics -->
    <div class="row">
        <div class="col-md-3">
            <div class="info-box">
                <span class="info-box-icon bg-info"><i class="fas fa-chart-line"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Total Investments</span>
                    <span class="info-box-number">{{ $stats['total_count'] ?? 0 }}</span>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="info-box">
                <span class="info-box-icon bg-success"><i class="fas fa-dollar-sign"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Total Amount</span>
                    <span class="info-box-number">${{ number_format($stats['total_amount'] ?? 0, 2) }}</span>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="info-box">
                <span class="info-box-icon bg-warning"><i class="fas fa-clock"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Active Investments</span>
                    <span class="info-box-number">{{ $stats['active_count'] ?? 0 }}</span>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="info-box">
                <span class="info-box-icon bg-primary"><i class="fas fa-check-circle"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Completed</span>
                    <span class="info-box-number">{{ $stats['completed_count'] ?? 0 }}</span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Investment Details Modal -->
<div class="modal fade" id="investmentModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Investment Details</h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <div id="investmentDetails">
                    Loading...
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
@stop

@section('js')
<script>
function viewInvestment(investmentId) {
    $('#investmentModal').modal('show');
    $('#investmentDetails').html('Loading investment details...');

    // In a real implementation, you would fetch investment details via AJAX
    setTimeout(() => {
        $('#investmentDetails').html(`
            <table class="table table-bordered">
                <tr><th>Investment ID:</th><td>${investmentId}</td></tr>
                <tr><th>Status:</th><td><span class="badge badge-success">Active</span></td></tr>
                <tr><th>Progress:</th><td>Day 15 of 30</td></tr>
                <tr><th>Returns Generated:</th><td>$125.50</td></tr>
            </table>
        `);
    }, 500);
}

function pauseInvestment(investmentId) {
    if (confirm('Are you sure you want to pause this investment?')) {
        // In a real implementation, you would send an AJAX request
        $(document).Toasts('create', {
            class: 'bg-warning',
            title: 'Investment Paused',
            body: `Investment ${investmentId} has been paused.`,
            delay: 3000
        });
    }
}
</script>
@stop