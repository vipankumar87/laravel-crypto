@extends('layouts.user')

@section('title', 'My Referrals')

@section('content_header')
    <h1>My Referrals</h1>
@stop

@section('content')
<div class="container-fluid">
    @if(isset($hasInvested) && !$hasInvested)
        <!-- Investment Required Message -->
        <div class="row">
            <div class="col-12">
                <div class="alert alert-warning">
                    <h4><i class="icon fas fa-exclamation-triangle"></i> Investment Required!</h4>
                    {{ $message }}
                    <br><br>
                    <a href="{{ route('investments.plans') }}" class="btn btn-primary">
                        <i class="fas fa-chart-line"></i> View Investment Plans
                    </a>
                </div>
            </div>
        </div>
    @else
        <!-- Stats Row -->
        <div class="row">
            <div class="col-md-4">
                <div class="info-box">
                    <span class="info-box-icon bg-info"><i class="fas fa-users"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Total Referrals</span>
                        <span class="info-box-number">{{ $stats['total_referrals'] }}</span>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="info-box">
                    <span class="info-box-icon bg-success"><i class="fas fa-dollar-sign"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Total Earnings</span>
                        <span class="info-box-number">${{ number_format($stats['total_earnings'], 2) }}</span>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Your Referral Link</h3>
                    </div>
                    <div class="card-body">
                        <div class="input-group">
                            <input type="text" class="form-control" id="referralLink" value="{{ $stats['referral_url'] }}" readonly>
                            <div class="input-group-append">
                                <button class="btn btn-outline-secondary" type="button" onclick="copyReferralLink()">
                                    <i class="fas fa-copy"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Referrals List -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">My Referrals List</h3>
                </div>
                <div class="card-body table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Username</th>
                                <th>Email</th>
                                <th>Joined Date</th>
                                <th>Wallet Balance</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($referrals as $referral)
                                <tr>
                                    <td>{{ $referral->name }}</td>
                                    <td><code>{{ $referral->username }}</code></td>
                                    <td>{{ $referral->email }}</td>
                                    <td>{{ $referral->created_at->format('M d, Y') }}</td>
                                    <td>${{ number_format($referral->wallet->balance ?? 0, 2) }}</td>
                                    <td>
                                        <span class="badge badge-{{ $referral->wallet && $referral->wallet->status === 'active' ? 'success' : 'warning' }}">
                                            {{ $referral->wallet ? ucfirst($referral->wallet->status) : 'No Wallet' }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted">No referrals yet</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if(method_exists($referrals, 'hasPages') && $referrals->hasPages())
                    <div class="card-footer">
                        {{ $referrals->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@stop

@section('js')
<script>
function copyReferralLink() {
    const referralLink = document.getElementById('referralLink');
    referralLink.select();
    referralLink.setSelectionRange(0, 99999);
    navigator.clipboard.writeText(referralLink.value);

    $(document).Toasts('create', {
        class: 'bg-success',
        title: 'Success',
        body: 'Referral link copied to clipboard!',
        delay: 3000
    });
}
</script>
@stop