@extends('layouts.user')

@section('title', 'Level ' . $level . ' Referral Details')

@section('content_header')
    <h1>Level {{ $level }} Referral Details</h1>
@stop

@section('content')
<div class="container-fluid">
    <!-- Back Button -->
    <div class="row mb-3">
        <div class="col-12">
            <a href="{{ route('referrals.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to Dashboard
            </a>
        </div>
    </div>

    <!-- Referral URL Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card" style="background: linear-gradient(135deg, #2d3561 0%, #1a1f3a 100%); border-radius: 15px;">
                <div class="card-body">
                    <h5 class="text-white mb-3">Referral URL</h5>
                    <div class="input-group">
                        <input type="text" class="form-control form-control-lg" id="referralUrl" value="{{ auth()->user()->getReferralUrl() }}" readonly style="background: rgba(255,255,255,0.1); border: 1px solid rgba(255,255,255,0.2); color: white;">
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

    <!-- Level Summary Table -->
    <div class="row mb-4">
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
                                <tr style="border-bottom: 1px solid rgba(255,255,255,0.1);">
                                    <td style="padding: 20px;">1</td>
                                    <td style="padding: 20px;">
                                        <span class="badge badge-{{ 
                                            $level == 1 ? 'success' : 
                                            ($level == 2 ? 'info' : 
                                            ($level == 3 ? 'warning' : 
                                            ($level == 4 ? 'primary' : 
                                            ($level == 5 ? 'secondary' : 'dark'))))
                                        }}" style="font-size: 14px; padding: 8px 15px;">
                                            Level {{ $level }}
                                        </span>
                                    </td>
                                    <td style="padding: 20px;">
                                        <strong>{{ count($referralDetails) }}</strong>
                                    </td>
                                    <td style="padding: 20px;">
                                        <strong>{{ number_format(array_sum(array_column($referralDetails, 'total_invest')), 0) }}</strong>
                                    </td>
                                    <td style="padding: 20px;">
                                        <strong>{{ count($referralDetails) > 0 ? number_format(array_sum(array_column($referralDetails, 'total_invest')) / count($referralDetails), 0) : 0 }}</strong>
                                    </td>
                                    <td style="padding: 20px;">
                                        <button class="btn btn-outline-light btn-sm" disabled style="padding: 8px 20px;">
                                            View Details
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Detailed Referrals Table -->
    <div class="row">
        <div class="col-12">
            <div class="card" style="background: linear-gradient(135deg, #2d3561 0%, #1a1f3a 100%); border-radius: 15px;">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0" style="color: white;">
                            <thead style="background: rgba(0,0,0,0.3);">
                                <tr>
                                    <th style="border: none; padding: 20px;">S.No</th>
                                    <th style="border: none; padding: 20px;">Sponsor</th>
                                    <th style="border: none; padding: 20px;">UserName</th>
                                    <th style="border: none; padding: 20px;">Email</th>
                                    <th style="border: none; padding: 20px;">Total Invest</th>
                                    <th style="border: none; padding: 20px;">Average Monthly Income</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($referralDetails as $index => $detail)
                                    <tr style="border-bottom: 1px solid rgba(255,255,255,0.1);">
                                        <td style="padding: 20px;">{{ $index + 1 }}</td>
                                        <td style="padding: 20px;">
                                            <strong>{{ $detail['sponsor'] }}</strong>
                                        </td>
                                        <td style="padding: 20px;">
                                            <strong>{{ $detail['username'] }}</strong>
                                        </td>
                                        <td style="padding: 20px;">
                                            {{ $detail['email'] }}
                                        </td>
                                        <td style="padding: 20px;">
                                            <strong>{{ number_format($detail['total_invest'], 0) }}</strong>
                                        </td>
                                        <td style="padding: 20px;">
                                            <strong>{{ number_format($detail['monthly_income'], 2) }}</strong>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center" style="padding: 40px;">
                                            <i class="fas fa-users fa-3x mb-3" style="opacity: 0.3;"></i>
                                            <h5 style="opacity: 0.7;">No referrals at this level</h5>
                                            <p style="opacity: 0.5;">Build your team to see referrals here!</p>
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

    <!-- Statistics Summary -->
    @if(count($referralDetails) > 0)
    <div class="row mt-4">
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h6>Total Members</h6>
                    <h3>{{ count($referralDetails) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <h6>Total Investment</h6>
                    <h3>{{ number_format(array_sum(array_column($referralDetails, 'total_invest')), 2) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <h6>Average Investment</h6>
                    <h3>{{ number_format(array_sum(array_column($referralDetails, 'total_invest')) / count($referralDetails), 2) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <h6>Total Monthly Income</h6>
                    <h3>{{ number_format(array_sum(array_column($referralDetails, 'monthly_income')), 2) }}</h3>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
@stop

@section('js')
<script>
function copyReferralUrl() {
    const urlInput = document.getElementById('referralUrl');
    urlInput.select();
    urlInput.setSelectionRange(0, 99999);
    
    document.execCommand('copy');
    
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
