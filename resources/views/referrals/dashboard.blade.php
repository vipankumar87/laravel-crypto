@extends('layouts.user')

@section('title', 'Referral Dashboard')

@section('content_header')
    <h1>Referral Dashboard</h1>
@stop

@section('content')
<div class="container-fluid">
    @if(isset($message))
        <div class="alert alert-info">
            <i class="fas fa-info-circle"></i> {{ $message }}
        </div>
    @endif

    <!-- Info Blocks Row -->
    <div class="row mb-4">
        <!-- Wallet Balance -->
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card text-white" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 15px;">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="rounded-circle bg-white p-3 mr-3">
                            <i class="fas fa-wallet text-purple" style="font-size: 24px; color: #667eea;"></i>
                        </div>
                        <div>
                            <h6 class="mb-1" style="opacity: 0.9;">Wallet Balance</h6>
                            <h4 class="mb-0 font-weight-bold">{{ number_format($walletBalance, 4) }} USDT</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Mining/Interest -->
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card text-white" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); border-radius: 15px;">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="rounded-circle bg-white p-3 mr-3">
                            <i class="fas fa-chart-line" style="font-size: 24px; color: #f5576c;"></i>
                        </div>
                        <div>
                            <h6 class="mb-1" style="opacity: 0.9;">Mining</h6>
                            <h4 class="mb-0 font-weight-bold">{{ number_format($totalInterest, 10) }} USDT</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Investment -->
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card text-white" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); border-radius: 15px;">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="rounded-circle bg-white p-3 mr-3">
                            <i class="fas fa-coins" style="font-size: 24px; color: #00f2fe;"></i>
                        </div>
                        <div>
                            <h6 class="mb-1" style="opacity: 0.9;">Total Invest</h6>
                            <h4 class="mb-0 font-weight-bold">{{ number_format($totalInvestment, 2) }} USDT</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Affiliate Bonus -->
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card text-white" style="background: linear-gradient(135deg, #fa709a 0%, #fee140 100%); border-radius: 15px;">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="rounded-circle bg-white p-3 mr-3">
                            <i class="fas fa-users" style="font-size: 24px; color: #fa709a;"></i>
                        </div>
                        <div>
                            <h6 class="mb-1" style="opacity: 0.9;">Total Affiliate Bonus</h6>
                            <h4 class="mb-0 font-weight-bold">{{ number_format($totalAffiliateBonus, 10) }} USDT</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Referral URL Section -->
    @if($referralUrl)
    <div class="row mb-4">
        <div class="col-12">
            <div class="card" style="background: linear-gradient(135deg, #2d3561 0%, #1a1f3a 100%); border-radius: 15px;">
                <div class="card-body">
                    <h5 class="text-white mb-3">Referral URL</h5>
                    <div class="input-group">
                        <input type="text" class="form-control form-control-lg" id="referralUrl" value="{{ $referralUrl }}" readonly style="background: rgba(255,255,255,0.1); border: 1px solid rgba(255,255,255,0.2); color: white;">
                        <div class="input-group-append">
                            <button class="btn btn-primary btn-lg" type="button" onclick="copyReferralUrl()">
                                <i class="fas fa-copy"></i> Copy
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Referral Hierarchy Table -->
    <div class="row">
        <div class="col-12">
            <div class="card" style="background: linear-gradient(135deg, #2d3561 0%, #1a1f3a 100%); border-radius: 15px;">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0" style="color: white;">
                            <thead style="background: rgba(0,0,0,0.3);">
                                <tr>
                                    <th style="border: none; padding: 20px;">S.No</th>
                                    <th style="border: none; padding: 20px;">Level</th>
                                    <th style="border: none; padding: 20px;">Team Size</th>
                                    <th style="border: none; padding: 20px;">Total Invest</th>
                                    <th style="border: none; padding: 20px;">Avg Bonus</th>
                                    <th style="border: none; padding: 20px;">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($levelStats as $index => $stat)
                                    <tr style="border-bottom: 1px solid rgba(255,255,255,0.1);">
                                        <td style="padding: 20px;">{{ $index + 1 }}</td>
                                        <td style="padding: 20px;">
                                            <span class="badge badge-{{ 
                                                $stat['level'] == 1 ? 'success' : 
                                                ($stat['level'] == 2 ? 'info' : 
                                                ($stat['level'] == 3 ? 'warning' : 
                                                ($stat['level'] == 4 ? 'primary' : 
                                                ($stat['level'] == 5 ? 'secondary' : 'dark'))))
                                            }}" style="font-size: 14px; padding: 8px 15px;">
                                                Level {{ $stat['level'] }}
                                            </span>
                                        </td>
                                        <td style="padding: 20px;">
                                            <strong>{{ $stat['team_size'] }}</strong>
                                        </td>
                                        <td style="padding: 20px;">
                                            <strong>{{ number_format($stat['total_invest'], 0) }}</strong>
                                        </td>
                                        <td style="padding: 20px;">
                                            <strong>{{ number_format($stat['avg_bonus'], 0) }}</strong>
                                        </td>
                                        <td style="padding: 20px;">
                                            @if($stat['team_size'] > 0)
                                                <a href="{{ route('referrals.level-details', $stat['level']) }}" 
                                                   class="btn btn-outline-light btn-sm" 
                                                   style="border: 1px solid rgba(255,255,255,0.3); padding: 8px 20px;">
                                                    View Details
                                                </a>
                                            @else
                                                <button class="btn btn-outline-secondary btn-sm" disabled style="padding: 8px 20px;">
                                                    View Details
                                                </button>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center" style="padding: 40px;">
                                            <i class="fas fa-users fa-3x mb-3" style="opacity: 0.3;"></i>
                                            <h5 style="opacity: 0.7;">No referrals yet</h5>
                                            <p style="opacity: 0.5;">Share your referral link to start building your team!</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Additional Info Section -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="alert alert-info">
                <h5><i class="fas fa-info-circle"></i> How the Referral System Works:</h5>
                <ul class="mb-0">
                    <li><strong>Level 1:</strong> Your direct referrals - People you personally invite</li>
                    <li><strong>Level 2:</strong> Referrals made by your Level 1 team members</li>
                    <li><strong>Level 3-6:</strong> Deeper levels of your network hierarchy</li>
                </ul>
                <p class="mt-2 mb-0"><strong>Note:</strong> You earn bonuses from investments made by all members in your downline network!</p>
            </div>
        </div>
    </div>
</div>
@stop

@section('js')
<script>
function copyReferralUrl() {
    const urlInput = document.getElementById('referralUrl');
    urlInput.select();
    urlInput.setSelectionRange(0, 99999); // For mobile devices
    
    document.execCommand('copy');
    
    // Show success message
    const btn = event.target.closest('button');
    const originalHtml = btn.innerHTML;
    btn.innerHTML = '<i class="fas fa-check"></i> Copied!';
    btn.classList.add('btn-success');
    btn.classList.remove('btn-primary');
    
    setTimeout(() => {
        btn.innerHTML = originalHtml;
        btn.classList.remove('btn-success');
        btn.classList.add('btn-primary');
    }, 2000);
}
</script>
@stop
