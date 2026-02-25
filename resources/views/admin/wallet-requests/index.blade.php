@extends('adminlte::page')

@section('title', 'Wallet Update Requests')

@section('content_header')
    <h1>Wallet Update Requests
        @if($pendingCount > 0)
            <span class="badge badge-warning ml-2">{{ $pendingCount }} Pending</span>
        @endif
    </h1>
@stop

@section('content')
<div class="container-fluid">
    @if(session('success'))
        <div class="alert alert-success alert-dismissible">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            {{ session('error') }}
        </div>
    @endif

    <div class="card">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-wallet mr-2"></i>Wallet Address Change Requests</h3>
        </div>
        <div class="card-body p-0">
            <table class="table table-hover table-striped mb-0">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>User</th>
                        <th>Current Address</th>
                        <th>Requested Address</th>
                        <th>Status</th>
                        <th>Submitted</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($requests as $req)
                        <tr>
                            <td>{{ $req->id }}</td>
                            <td>
                                <a href="{{ route('admin.users.show', $req->user) }}">{{ $req->user->name }}</a>
                                <br><small class="text-muted">{{ $req->user->email }}</small>
                            </td>
                            <td>
                                @if($req->user->bep_wallet_address)
                                    <code class="small">{{ $req->user->bep_wallet_address }}</code>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td>
                                <code class="small">{{ $req->new_wallet_address }}</code>
                                <a href="https://bscscan.com/address/{{ $req->new_wallet_address }}" target="_blank" class="ml-1">
                                    <i class="fas fa-external-link-alt fa-xs"></i>
                                </a>
                            </td>
                            <td>
                                @if($req->status === 'pending')
                                    <span class="badge badge-warning">Pending</span>
                                @elseif($req->status === 'approved')
                                    <span class="badge badge-success">Approved</span>
                                    @if($req->reviewer)
                                        <br><small class="text-muted">by {{ $req->reviewer->name }}</small>
                                    @endif
                                @else
                                    <span class="badge badge-danger">Rejected</span>
                                    @if($req->admin_note)
                                        <br><small class="text-muted">{{ $req->admin_note }}</small>
                                    @endif
                                    @if($req->reviewer)
                                        <br><small class="text-muted">by {{ $req->reviewer->name }}</small>
                                    @endif
                                @endif
                            </td>
                            <td>{{ $req->created_at->format('M d, Y H:i') }}</td>
                            <td>
                                @if($req->isPending())
                                    <form method="POST" action="{{ route('admin.wallet-requests.approve', $req) }}" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-success btn-sm" onclick="return confirm('Approve this wallet address change?')">
                                            <i class="fas fa-check"></i> Approve
                                        </button>
                                    </form>
                                    <button type="button" class="btn btn-danger btn-sm" data-toggle="modal" data-target="#rejectModal{{ $req->id }}">
                                        <i class="fas fa-times"></i> Reject
                                    </button>

                                    <!-- Reject Modal -->
                                    <div class="modal fade" id="rejectModal{{ $req->id }}" tabindex="-1">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h4 class="modal-title">Reject Wallet Update Request</h4>
                                                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                                                </div>
                                                <form method="POST" action="{{ route('admin.wallet-requests.reject', $req) }}">
                                                    @csrf
                                                    <div class="modal-body">
                                                        <p>Reject wallet update request for <strong>{{ $req->user->name }}</strong>?</p>
                                                        <div class="form-group">
                                                            <label>Reason (optional)</label>
                                                            <textarea class="form-control" name="admin_note" rows="3" placeholder="Reason for rejection..."></textarea>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                                                        <button type="submit" class="btn btn-danger">Reject Request</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">
                                <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
                                No wallet update requests found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($requests->hasPages())
            <div class="card-footer">
                {{ $requests->links() }}
            </div>
        @endif
    </div>
</div>
@stop

@section('adminlte_js')
<script>
$(document).ready(function() {
    $('.alert-dismissible').delay(5000).fadeOut(500);
});
</script>
@stop
