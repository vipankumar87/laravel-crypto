@extends('adminlte::page')

@section('title', 'All Transactions')

@section('content_header')
    <h1>Transaction Management</h1>
@stop

@section('content')
<div class="container-fluid">
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <!-- Filters -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Filter Transactions</h3>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('admin.transactions.index') }}">
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Transaction Type</label>
                            <select class="form-control" name="type">
                                <option value="">All Types</option>
                                <option value="deposit" {{ request('type') === 'deposit' ? 'selected' : '' }}>Deposit</option>
                                <option value="withdrawal" {{ request('type') === 'withdrawal' ? 'selected' : '' }}>Withdrawal</option>
                                <option value="investment" {{ request('type') === 'investment' ? 'selected' : '' }}>Investment</option>
                                <option value="referral_bonus" {{ request('type') === 'referral_bonus' ? 'selected' : '' }}>Referral Bonus</option>
                                <option value="earnings" {{ request('type') === 'earnings' ? 'selected' : '' }}>Earnings</option>
                                <option value="doge_bonus" {{ request('type') === 'doge_bonus' ? 'selected' : '' }}>DOGE Bonus</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Status</label>
                            <select class="form-control" name="status">
                                <option value="">All Status</option>
                                <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completed</option>
                                <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                                <option value="failed" {{ request('status') === 'failed' ? 'selected' : '' }}>Failed</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Search User</label>
                            <input type="text" class="form-control" name="user_search" value="{{ request('user_search') }}" placeholder="Name or email">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label>&nbsp;</label>
                            <button type="submit" class="btn btn-primary btn-block">Filter</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Bulk Approve Button -->
    <div class="mb-3" id="bulkApproveContainer" style="display: none;">
        <button type="button" class="btn btn-success" id="bulkApproveBtn" onclick="bulkApprove()">
            <i class="fas fa-check-double"></i> Bulk Approve Selected (<span id="selectedCount">0</span>)
        </button>
    </div>

    <!-- Transactions Table -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">All Transactions</h3>
        </div>
        <div class="card-body table-responsive">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th style="width: 40px;">
                            <input type="checkbox" id="selectAll" onclick="toggleSelectAll()">
                        </th>
                        <th>ID</th>
                        <th>Transaction ID</th>
                        <th>User</th>
                        <th>Type</th>
                        <th>Currency</th>
                        <th>Amount</th>
                        <th>Net Amount</th>
                        <th>Status</th>
                        <th>Tx Hash</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($transactions ?? [] as $transaction)
                        <tr>
                            <td>
                                @if(($transaction->status ?? 'pending') === 'pending' && ($transaction->type ?? '') === 'withdrawal')
                                    <input type="checkbox" class="txn-checkbox" value="{{ $transaction->id }}" onchange="updateSelectedCount()">
                                @endif
                            </td>
                            <td>{{ $transaction->id ?? 'N/A' }}</td>
                            <td>
                                <code>{{ $transaction->transaction_id ?? 'N/A' }}</code>
                            </td>
                            <td>
                                <a href="{{ route('admin.users.show', $transaction->user ?? 1) }}">
                                    {{ $transaction->user->name ?? 'Unknown User' }}
                                </a>
                            </td>
                            <td>
                                <span class="badge badge-{{
                                    ($transaction->type ?? 'deposit') === 'deposit' ? 'success' :
                                    (($transaction->type ?? 'deposit') === 'withdrawal' ? 'warning' :
                                    (($transaction->type ?? 'deposit') === 'investment' ? 'info' :
                                    (($transaction->type ?? 'deposit') === 'doge_bonus' ? 'dark' : 'primary')))
                                }}">
                                    {{ ucfirst(str_replace('_', ' ', $transaction->type ?? 'deposit')) }}
                                </span>
                            </td>
                            <td>
                                <span class="badge badge-{{ ($transaction->currency ?? 'USDT') === 'DOGE' ? 'warning' : 'light' }}">
                                    {{ $transaction->currency ?? 'USDT' }}
                                </span>
                            </td>
                            <td>
                                <span class="{{ in_array($transaction->type ?? 'deposit', ['withdrawal', 'investment']) ? 'text-danger' : 'text-success' }}">
                                    {{ in_array($transaction->type ?? 'deposit', ['withdrawal', 'investment']) ? '-' : '+' }}{{ ($transaction->currency ?? 'USDT') === 'DOGE' ? '' : '$' }}{{ number_format($transaction->amount ?? 0, ($transaction->currency ?? 'USDT') === 'DOGE' ? 8 : 2) }}
                                </span>
                            </td>
                            <td>{{ ($transaction->currency ?? 'USDT') === 'DOGE' ? '' : '$' }}{{ number_format($transaction->net_amount ?? 0, ($transaction->currency ?? 'USDT') === 'DOGE' ? 8 : 2) }}</td>
                            <td>
                                <span class="badge badge-{{
                                    ($transaction->status ?? 'pending') === 'completed' ? 'success' :
                                    (($transaction->status ?? 'pending') === 'pending' ? 'warning' :
                                    (($transaction->status ?? 'pending') === 'cancelled' ? 'secondary' : 'danger'))
                                }}">
                                    {{ ucfirst($transaction->status ?? 'pending') }}
                                </span>
                            </td>
                            <td>
                                @if($transaction->tx_hash)
                                    <a href="https://bscscan.com/tx/{{ $transaction->tx_hash }}" target="_blank" title="{{ $transaction->tx_hash }}">
                                        <code>{{ substr($transaction->tx_hash, 0, 10) }}...</code>
                                        <i class="fas fa-external-link-alt fa-xs"></i>
                                    </a>
                                @elseif($transaction->type === 'withdrawal' && $transaction->status === 'completed')
                                    <span class="badge badge-info"><i class="fas fa-clock"></i> Awaiting transfer</span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>{{ ($transaction->created_at ?? now())->format('M d, Y H:i') }}</td>
                            <td>
                                <div class="btn-group">
                                    @if(($transaction->status ?? 'pending') === 'pending')
                                        @if(($transaction->type ?? 'withdrawal') === 'withdrawal')
                                            <form action="{{ route('admin.transactions.approve', $transaction ?? 1) }}" method="POST" style="display: inline;">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-success" onclick="return confirm('Approve this withdrawal?')">
                                                    <i class="fas fa-check"></i>
                                                </button>
                                            </form>
                                            <button type="button" class="btn btn-sm btn-danger" onclick="rejectTransaction({{ $transaction->id ?? 1 }})">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        @endif
                                    @endif
                                    <button type="button" class="btn btn-sm btn-info" onclick="viewTransaction({{ $transaction->id ?? 1 }})">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="12" class="text-center text-muted">No transactions found</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if(isset($transactions) && method_exists($transactions, 'hasPages') && $transactions->hasPages())
            <div class="card-footer">
                {{ $transactions->links() }}
            </div>
        @endif
    </div>

    <!-- Statistics Row -->
    <div class="row">
        <div class="col-md-3">
            <div class="info-box">
                <span class="info-box-icon bg-info"><i class="fas fa-receipt"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Total Transactions</span>
                    <span class="info-box-number">{{ $stats['total_count'] ?? 0 }}</span>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="info-box">
                <span class="info-box-icon bg-warning"><i class="fas fa-clock"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Pending</span>
                    <span class="info-box-number">{{ $stats['pending_count'] ?? 0 }}</span>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="info-box">
                <span class="info-box-icon bg-success"><i class="fas fa-check"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Completed</span>
                    <span class="info-box-number">{{ $stats['completed_count'] ?? 0 }}</span>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="info-box">
                <span class="info-box-icon bg-primary"><i class="fas fa-dollar-sign"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Total Volume</span>
                    <span class="info-box-number">${{ number_format($stats['total_volume'] ?? 0, 2) }}</span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Reject Transaction Modal -->
<div class="modal fade" id="rejectModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Reject Transaction</h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <form id="rejectForm" method="POST">
                @csrf
                <div class="modal-body">
                    <p>Are you sure you want to reject this transaction?</p>
                    <div class="form-group">
                        <label>Reason for rejection:</label>
                        <textarea class="form-control" name="reason" rows="3" required placeholder="Please provide a reason for rejecting this transaction..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Reject Transaction</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Transaction Details Modal -->
<div class="modal fade" id="transactionModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Transaction Details</h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <div id="transactionDetails">
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
function rejectTransaction(transactionId) {
    $('#rejectForm').attr('action', `/admin/transactions/${transactionId}/reject`);
    $('#rejectModal').modal('show');
}

function viewTransaction(transactionId) {
    $('#transactionModal').modal('show');
    $('#transactionDetails').html('Loading transaction details...');

    setTimeout(() => {
        $('#transactionDetails').html(`
            <table class="table table-bordered">
                <tr><th>Transaction ID:</th><td>TXN_${transactionId}</td></tr>
                <tr><th>User:</th><td>John Doe</td></tr>
                <tr><th>Type:</th><td>Withdrawal</td></tr>
                <tr><th>Amount:</th><td>$500.00</td></tr>
                <tr><th>Status:</th><td><span class="badge badge-warning">Pending</span></td></tr>
                <tr><th>Created:</th><td>Dec 24, 2024 10:30</td></tr>
                <tr><th>Description:</th><td>Withdrawal request</td></tr>
            </table>
        `);
    }, 500);
}

function toggleSelectAll() {
    const selectAll = document.getElementById('selectAll');
    const checkboxes = document.querySelectorAll('.txn-checkbox');
    checkboxes.forEach(cb => cb.checked = selectAll.checked);
    updateSelectedCount();
}

function updateSelectedCount() {
    const checked = document.querySelectorAll('.txn-checkbox:checked');
    const count = checked.length;
    document.getElementById('selectedCount').textContent = count;
    document.getElementById('bulkApproveContainer').style.display = count > 0 ? 'block' : 'none';
}

function bulkApprove() {
    const checked = document.querySelectorAll('.txn-checkbox:checked');
    const ids = Array.from(checked).map(cb => parseInt(cb.value));

    if (ids.length === 0) {
        alert('No transactions selected');
        return;
    }

    if (!confirm(`Are you sure you want to approve ${ids.length} transaction(s)?`)) {
        return;
    }

    const btn = document.getElementById('bulkApproveBtn');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';

    fetch('{{ route("admin.transactions.bulk-approve") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json',
        },
        body: JSON.stringify({ transaction_ids: ids })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
            window.location.reload();
        } else {
            alert('Error: ' + data.message);
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-check-double"></i> Bulk Approve Selected (<span id="selectedCount">' + ids.length + '</span>)';
        }
    })
    .catch(error => {
        alert('Request failed. Please try again.');
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-check-double"></i> Bulk Approve Selected (<span id="selectedCount">' + ids.length + '</span>)';
    });
}
</script>
@stop
