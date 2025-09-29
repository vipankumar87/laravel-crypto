@extends('adminlte::page')

@section('title', 'Manage Users')

@section('content_header')
    <h1>User Management</h1>
    <meta name="csrf-token" content="{{ csrf_token() }}">
@stop


@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">All Users</h3>
        </div>
        <div class="card-body table-responsive">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Wallet Balance</th>
                        <th>Referrals</th>
                        <th>Investments</th>
                        <th>Status</th>
                        <th>Joined</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($users as $user)
                        <tr>
                            <td>{{ $user->id }}</td>
                            <td>{{ $user->name }}</td>
                            <td><code>{{ $user->username }}</code></td>
                            <td>{{ $user->email }}</td>
                            <td>
                                @foreach($user->roles as $role)
                                    <span class="badge badge-primary">{{ $role->name }}</span>
                                @endforeach
                            </td>
                            <td>${{ number_format($user->wallet->balance ?? 0, 2) }}</td>
                            <td>{{ $user->referrals_count }}</td>
                            <td>{{ $user->investments_count }}</td>
                            <td>
                                @if($user->is_banned)
                                    <span class="badge badge-danger">Banned</span>
                                @else
                                    <span class="badge badge-success">Active</span>
                                @endif
                            </td>
                            <td>{{ $user->created_at->format('M d, Y') }}</td>
                            <td>
                                <div class="btn-group-vertical">
                                    <div class="btn-group mb-1">
                                        <button type="button" class="btn btn-sm btn-success" onclick="addFunds({{ $user->id }}, '{{ $user->name }}')">
                                            <i class="fas fa-plus"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-warning" onclick="deductFunds({{ $user->id }}, '{{ $user->name }}', {{ $user->wallet->balance ?? 0 }})">
                                            <i class="fas fa-minus"></i>
                                        </button>
                                        <a href="{{ route('admin.users.show', $user) }}" class="btn btn-sm btn-info">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <button type="button" class="btn btn-sm btn-secondary" onclick="showPassword({{ $user->id }}, '{{ $user->name }}')">
                                            <i class="fas fa-key"></i>
                                        </button>
                                    </div>
                                    <div class="btn-group">
                                        @if(!$user->hasRole(['admin', 'system']))
                                            @if($user->is_banned)
                                                <button type="button" class="btn btn-sm btn-success" onclick="unbanUser({{ $user->id }}, '{{ $user->name }}')">
                                                    <i class="fas fa-unlock"></i>
                                                </button>
                                            @else
                                                <button type="button" class="btn btn-sm btn-danger" onclick="banUser({{ $user->id }}, '{{ $user->name }}')">
                                                    <i class="fas fa-ban"></i>
                                                </button>
                                                <button type="button" class="btn btn-sm btn-primary" onclick="impersonateUser({{ $user->id }}, '{{ $user->name }}')">
                                                    <i class="fas fa-user-secret"></i>
                                                </button>
                                            @endif
                                        @endif
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @if($users->hasPages())
            <div class="card-footer">
                {{ $users->links() }}
            </div>
        @endif
    </div>
</div>

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

<!-- Ban User Modal -->
<div class="modal fade" id="banUserModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Ban User</h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <form id="banUserForm">
                @csrf
                <div class="modal-body">
                    <p>Are you sure you want to ban <strong id="banUserName"></strong>?</p>
                    <div class="form-group">
                        <label>Reason for ban *</label>
                        <textarea class="form-control" name="reason" required placeholder="Please provide a reason for banning this user..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Ban User</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Show Password Modal -->
<div class="modal fade" id="passwordModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">User Password</h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <p><strong id="passwordUserName"></strong>'s password information:</p>
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle"></i>
                    <strong>Security Notice:</strong> In a production system, passwords should never be retrievable in plain text.
                </div>
                <div class="form-group">
                    <label>Password (Demo):</label>
                    <input type="text" class="form-control" value="••••••••" readonly>
                    <small class="text-muted">For security, actual passwords are hashed and cannot be retrieved.</small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
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

let currentUserId = null;

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

    // Simple approach - serialize form data normally
    const formData = $(this).serialize() + '&_token=' + $('meta[name="csrf-token"]').attr('content');

    $.ajax({
        url: `/admin/users/${currentUserId}/add-funds`,
        method: 'POST',
        data: formData,
        success: function(data) {
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
        },
        error: function(xhr, status, error) {
            console.error('Error:', error);
            $(document).Toasts('create', {
                class: 'bg-danger',
                title: 'Error',
                body: 'An error occurred while processing the request.',
                delay: 5000
            });
        }
    });
});

$('#deductFundsForm').on('submit', function(e) {
    e.preventDefault();

    const formData = new FormData(this);
    // Explicitly add CSRF token to FormData
    formData.append('_token', '{{ csrf_token() }}');

    $.ajax({
        url: `/admin/users/${currentUserId}/deduct-funds`,
        method: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function(data) {
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
        },
        error: function(xhr, status, error) {
            console.error('Error:', error);
            $(document).Toasts('create', {
                class: 'bg-danger',
                title: 'Error',
                body: 'An error occurred while processing the request.',
                delay: 5000
            });
        }
    });
});

// Ban user functions
function banUser(userId, userName) {
    currentUserId = userId;
    $('#banUserName').text(userName);
    $('#banUserForm').attr('action', `/admin/users/${userId}/ban`);
    $('#banUserModal').modal('show');
}

function unbanUser(userId, userName) {
    if (confirm(`Are you sure you want to unban ${userName}?`)) {
        fetch(`/admin/users/${userId}/unban`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json',
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
        });
    }
}

$('#banUserForm').on('submit', function(e) {
    e.preventDefault();

    const formData = new FormData(this);
    // Explicitly add CSRF token to FormData
    formData.append('_token', '{{ csrf_token() }}');

    $.ajax({
        url: this.action,
        method: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function(data) {
            if (data.success) {
                $(document).Toasts('create', {
                    class: 'bg-success',
                    title: 'Success',
                    body: data.message,
                    delay: 5000
                });
                $('#banUserModal').modal('hide');
                location.reload();
            } else {
                $(document).Toasts('create', {
                    class: 'bg-danger',
                    title: 'Error',
                    body: data.message,
                    delay: 5000
                });
            }
        },
        error: function(xhr, status, error) {
            console.error('Error:', error);
            $(document).Toasts('create', {
                class: 'bg-danger',
                title: 'Error',
                body: 'An error occurred while processing the request.',
                delay: 5000
            });
        }
    });
});

// Impersonation function
function impersonateUser(userId, userName) {
    if (confirm(`Are you sure you want to impersonate ${userName}? You will be logged in as this user.`)) {
        fetch(`/admin/users/${userId}/impersonate`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json',
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                $(document).Toasts('create', {
                    class: 'bg-info',
                    title: 'Impersonation Started',
                    body: data.message,
                    delay: 3000
                });
                setTimeout(() => {
                    window.location.href = data.redirect;
                }, 1000);
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
        });
    }
}

// Show password function
function showPassword(userId, userName) {
    $('#passwordUserName').text(userName);
    $('#passwordModal').modal('show');
}
</script>
@stop