@extends('adminlte::page')

@section('title', 'User Details')

@section('content_header')
    <h1>User Details - {{ $user->name }}</h1>
@stop

@section('adminlte_css')
@stop


@section('content')
<div class="container-fluid">
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="row">
        <!-- User Information -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">User Information</h3>
                </div>
                <div class="card-body">
                    <table class="table table-bordered">
                        <tr>
                            <th>Name:</th>
                            <td>{{ $user->name }}</td>
                        </tr>
                        <tr>
                            <th>Email:</th>
                            <td>{{ $user->email }}</td>
                        </tr>
                        <tr>
                            <th>Username:</th>
                            <td><code>{{ $user->username }}</code> <small class="text-muted">(for login)</small></td>
                        </tr>
                        <tr>
                            <th>Password:</th>
                            <td>
                                <code>{{ $user->getPlaintextPassword() }}</code>
                                <small class="text-muted d-block">For admin viewing only</small>
                            </td>
                        </tr>
                        <tr>
                            <th>Roles:</th>
                            <td>
                                @foreach($user->roles as $role)
                                    <span class="badge badge-primary">{{ $role->name }}</span>
                                @endforeach
                            </td>
                        </tr>
                        <tr>
                            <th>Referral Code:</th>
                            <td>{{ $user->referral_code ?? 'Not set' }}</td>
                        </tr>
                        <tr>
                            <th>Referred By:</th>
                            <td>{{ $user->referrer->name ?? 'Direct signup' }}</td>
                        </tr>
                        <tr>
                            <th>Joined Date:</th>
                            <td>{{ $user->created_at->format('M d, Y H:i') }}</td>
                        </tr>
                        <tr>
                            <th>Last Updated:</th>
                            <td>{{ $user->updated_at->format('M d, Y H:i') }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <!-- Wallet Information -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Wallet Information</h3>
                </div>
                <div class="card-body">
                    @if($user->wallet)
                        <table class="table table-bordered">
                            <tr>
                                <th>Balance:</th>
                                <td>${{ number_format($user->wallet->balance, 2) }}</td>
                            </tr>
                            <tr>
                                <th>Invested Amount:</th>
                                <td>${{ number_format($user->wallet->invested_amount, 2) }}</td>
                            </tr>
                            <tr>
                                <th>Earned Amount:</th>
                                <td>${{ number_format($user->wallet->earned_amount, 2) }}</td>
                            </tr>
                            <tr>
                                <th>Referral Earnings:</th>
                                <td>${{ number_format($user->wallet->referral_earnings, 2) }}</td>
                            </tr>
                            <tr>
                                <th>Withdrawn Amount:</th>
                                <td>${{ number_format($user->wallet->withdrawn_amount, 2) }}</td>
                            </tr>
                            <tr>
                                <th>Status:</th>
                                <td>
                                    <span class="badge badge-{{ $user->wallet->status === 'active' ? 'success' : 'warning' }}">
                                        {{ ucfirst($user->wallet->status) }}
                                    </span>
                                </td>
                            </tr>
                        </table>
                    @else
                        <div class="text-center text-muted">
                            <i class="fas fa-wallet fa-3x mb-3"></i>
                            <p>No wallet created for this user</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Row -->
    <div class="row">
        <div class="col-md-3">
            <div class="info-box">
                <span class="info-box-icon bg-info"><i class="fas fa-chart-line"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Total Invested</span>
                    <span class="info-box-number">${{ number_format($stats['total_invested'], 2) }}</span>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="info-box">
                <span class="info-box-icon bg-success"><i class="fas fa-dollar-sign"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Total Earned</span>
                    <span class="info-box-number">${{ number_format($stats['total_earned'], 2) }}</span>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="info-box">
                <span class="info-box-icon bg-warning"><i class="fas fa-handshake"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Referral Earnings</span>
                    <span class="info-box-number">${{ number_format($stats['referral_earnings'], 2) }}</span>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="info-box">
                <span class="info-box-icon bg-primary"><i class="fas fa-receipt"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Total Transactions</span>
                    <span class="info-box-number">{{ $stats['transaction_count'] }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Investments -->
    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Recent Investments</h3>
                </div>
                <div class="card-body">
                    @if($user->investments->count() > 0)
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Plan</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($user->investments->take(5) as $investment)
                                    <tr>
                                        <td>{{ $investment->investment_plan }}</td>
                                        <td>${{ number_format($investment->amount, 2) }}</td>
                                        <td>
                                            <span class="badge badge-{{ $investment->status === 'active' ? 'success' : 'secondary' }}">
                                                {{ ucfirst($investment->status) }}
                                            </span>
                                        </td>
                                        <td>{{ $investment->created_at->format('M d, Y') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <div class="text-center text-muted">
                            <i class="fas fa-chart-line fa-2x mb-2"></i>
                            <p>No investments yet</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Recent Transactions -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Recent Transactions</h3>
                </div>
                <div class="card-body">
                    @if($user->transactions->count() > 0)
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Type</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($user->transactions->take(5) as $transaction)
                                    <tr>
                                        <td>
                                            <span class="badge badge-info">
                                                {{ ucfirst(str_replace('_', ' ', $transaction->type)) }}
                                            </span>
                                        </td>
                                        <td>${{ number_format($transaction->amount, 2) }}</td>
                                        <td>
                                            <span class="badge badge-{{ $transaction->status === 'completed' ? 'success' : 'warning' }}">
                                                {{ ucfirst($transaction->status) }}
                                            </span>
                                        </td>
                                        <td>{{ $transaction->created_at->format('M d, Y') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <div class="text-center text-muted">
                            <i class="fas fa-receipt fa-2x mb-2"></i>
                            <p>No transactions yet</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Referrals -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Referrals ({{ $user->referrals->count() }})</h3>
                </div>
                <div class="card-body">
                    @if($user->referrals->count() > 0)
                        <div class="row">
                            @foreach($user->referrals as $referral)
                                <div class="col-md-4 mb-3">
                                    <div class="card bg-light">
                                        <div class="card-body p-3">
                                            <h6 class="card-title mb-1">{{ $referral->name }}</h6>
                                            <p class="card-text small text-muted mb-1">{{ $referral->email }}</p>
                                            <p class="card-text small">
                                                <strong>Balance:</strong> ${{ number_format($referral->wallet->balance ?? 0, 2) }}
                                            </p>
                                            <p class="card-text small">
                                                <strong>Joined:</strong> {{ $referral->created_at->format('M d, Y') }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center text-muted">
                            <i class="fas fa-users fa-3x mb-3"></i>
                            <p>No referrals yet</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Action Buttons -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Actions</h3>
                </div>
                <div class="card-body">
                    <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back to Users List
                    </a>

                    @if($user->wallet)
                        <button type="button" class="btn btn-success" onclick="addFunds({{ $user->id }}, '{{ $user->name }}')">
                            <i class="fas fa-plus"></i> Add Funds
                        </button>

                        <button type="button" class="btn btn-warning" onclick="deductFunds({{ $user->id }}, '{{ $user->name }}', {{ $user->wallet->balance }})">
                            <i class="fas fa-minus"></i> Deduct Funds
                        </button>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Include the same modals from the users index page -->
<!-- Add Funds Modal -->
<div class="modal fade" id="addFundsModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Add Funds</h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <form id="addFundsForm">
                @csrf
                <div class="modal-body">
                    <p>Add funds to <strong id="addFundsUserName"></strong>'s wallet:</p>
                    <div class="form-group">
                        <label>Amount ($)</label>
                        <input type="number" class="form-control" name="amount" step="0.01" min="0.01" required>
                    </div>
                    <div class="form-group">
                        <label>Description (optional)</label>
                        <input type="text" class="form-control" name="description" placeholder="Reason for adding funds">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">Add Funds</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Deduct Funds Modal -->
<div class="modal fade" id="deductFundsModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Deduct Funds</h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <form id="deductFundsForm">
                @csrf
                <div class="modal-body">
                    <p>Deduct funds from <strong id="deductFundsUserName"></strong>'s wallet:</p>
                    <p>Current Balance: $<span id="currentBalance"></span></p>
                    <div class="form-group">
                        <label>Amount ($)</label>
                        <input type="number" class="form-control" name="amount" step="0.01" min="0.01" id="deductAmount" required>
                    </div>
                    <div class="form-group">
                        <label>Description (optional)</label>
                        <input type="text" class="form-control" name="description" placeholder="Reason for deducting funds">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning">Deduct Funds</button>
                </div>
            </form>
        </div>
    </div>
</div>
@stop

@section('adminlte_js')
<script>
// Set CSRF token globally for AJAX requests
$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': '{{ csrf_token() }}'
    }
});

let currentUserId = {{ $user->id }};

function addFunds(userId, userName) {
    currentUserId = userId;
    $('#addFundsUserName').text(userName);
    $('#addFundsModal').modal('show');
}

function deductFunds(userId, userName, balance) {
    currentUserId = userId;
    $('#deductFundsUserName').text(userName);
    $('#currentBalance').text(balance.toFixed(2));
    $('#deductAmount').attr('max', balance);
    $('#deductFundsModal').modal('show');
}

$('#addFundsForm').on('submit', function(e) {
    e.preventDefault();

    const formData = new FormData(this);

    fetch(`/admin/users/${currentUserId}/add-funds`, {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': $('input[name="_token"]', this).val() || '{{ csrf_token() }}'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            $(document).Toasts('create', {
                class: 'bg-success',
                title: 'Success',
                body: data.message,
                delay: 5000
            });
            $('#addFundsModal').modal('hide');
            location.reload();
        } else {
            $(document).Toasts('create', {
                class: 'bg-danger',
                title: 'Error',
                body: data.message,
                delay: 5000
            });
        }
    })
    .catch(error => {
        console.error('Error:', error);
        $(document).Toasts('create', {
            class: 'bg-danger',
            title: 'Error',
            body: 'An error occurred while processing the request.',
            delay: 5000
        });
    });
});

$('#deductFundsForm').on('submit', function(e) {
    e.preventDefault();

    const formData = new FormData(this);

    fetch(`/admin/users/${currentUserId}/deduct-funds`, {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': $('input[name="_token"]', this).val() || '{{ csrf_token() }}'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            $(document).Toasts('create', {
                class: 'bg-success',
                title: 'Success',
                body: data.message,
                delay: 5000
            });
            $('#deductFundsModal').modal('hide');
            location.reload();
        } else {
            $(document).Toasts('create', {
                class: 'bg-danger',
                title: 'Error',
                body: data.message,
                delay: 5000
            });
        }
    })
    .catch(error => {
        console.error('Error:', error);
        $(document).Toasts('create', {
            class: 'bg-danger',
            title: 'Error',
            body: 'An error occurred while processing the request.',
            delay: 5000
        });
    });
});
</script>
@stop