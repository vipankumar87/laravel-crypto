@extends('layouts.user')

@section('title', 'Referral Earnings - 5 Level System')

@section('content_header')
    <h1>Referral Earnings <small>5-Level Bonus System</small></h1>
@stop

@section('content')
<div class="container-fluid">
    <!-- Stats Row -->
    <div class="row">
        <div class="col-md-4">
            <div class="info-box">
                <span class="info-box-icon bg-success"><i class="fas fa-dollar-sign"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Total Referral Earnings</span>
                    <span class="info-box-number">${{ number_format($totalEarnings, 2) }}</span>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="info-box">
                <span class="info-box-icon bg-info"><i class="fas fa-chart-line"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Total Bonuses</span>
                    <span class="info-box-number">{{ $bonuses->total() }}</span>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="info-box">
                <span class="info-box-icon bg-warning"><i class="fas fa-users"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Active Referrals</span>
                    <span class="info-box-number">{{ auth()->user()->referrals()->count() }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- 5-Level Bonus Breakdown -->
    <div class="row">
        <div class="col-12">
            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-layer-group"></i> 5-Level Bonus Breakdown</h3>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead class="bg-light">
                                <tr>
                                    <th>Level</th>
                                    <th>Bonus Rate</th>
                                    <th>Total Earned</th>
                                    <th>Number of Bonuses</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($earningsByLevel as $level => $data)
                                    <tr>
                                        <td>
                                            <strong class="text-primary">Level {{ $level }}</strong>
                                            @if($level == 1)
                                                <span class="badge badge-success ml-2">Direct</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge badge-info">{{ number_format($data['percentage'], 1) }}%</span>
                                        </td>
                                        <td>
                                            <strong class="text-success">${{ number_format($data['total'], 2) }}</strong>
                                        </td>
                                        <td>{{ $data['count'] }} bonuses</td>
                                        <td>
                                            @if($data['count'] > 0)
                                                <span class="badge badge-success"><i class="fas fa-check-circle"></i> Active</span>
                                            @else
                                                <span class="badge badge-secondary"><i class="fas fa-minus-circle"></i> No Earnings</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                                <tr class="bg-light font-weight-bold">
                                    <td colspan="2"><strong>TOTAL EARNINGS</strong></td>
                                    <td colspan="3">
                                        <strong class="text-success" style="font-size: 1.2em;">
                                            ${{ number_format($totalEarnings, 2) }}
                                        </strong>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Level Explanation -->
                    <div class="alert alert-info mt-3">
                        <h5><i class="fas fa-info-circle"></i> How the 5-Level System Works:</h5>
                        <ul class="mb-0">
                            <li><strong>Level 1 ({{ $levelPercentages[1] }}%):</strong> Direct referrals - People you personally invite</li>
                            <li><strong>Level 2 ({{ $levelPercentages[2] }}%):</strong> Referrals made by your Level 1 referrals</li>
                            <li><strong>Level 3 ({{ $levelPercentages[3] }}%):</strong> Referrals made by your Level 2 referrals</li>
                            <li><strong>Level 4 ({{ $levelPercentages[4] }}%):</strong> Referrals made by your Level 3 referrals</li>
                            <li><strong>Level 5 ({{ $levelPercentages[5] }}%):</strong> Referrals made by your Level 4 referrals</li>
                        </ul>
                        <p class="mt-2 mb-0"><strong>Note:</strong> You earn a percentage of each investment made by users in your downline network up to 5 levels deep!</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Detailed Bonus History -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-history"></i> Detailed Bonus History</h3>
                </div>
                <div class="card-body table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>Level</th>
                                <th>From User</th>
                                <th>Investment Amount</th>
                                <th>Bonus Rate</th>
                                <th>Bonus Earned</th>
                                <th>Date</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($bonuses as $bonus)
                                <tr>
                                    <td>
                                        <span class="badge badge-{{ 
                                            $bonus->level == 1 ? 'success' : 
                                            ($bonus->level == 2 ? 'info' : 
                                            ($bonus->level == 3 ? 'warning' : 
                                            ($bonus->level == 4 ? 'primary' : 'secondary')))
                                        }}">
                                            Level {{ $bonus->level }}
                                        </span>
                                    </td>
                                    <td>
                                        <strong>{{ $bonus->user->name }}</strong>
                                        <br>
                                        <small class="text-muted">{{ $bonus->user->username }}</small>
                                    </td>
                                    <td>
                                        <span class="text-primary">
                                            ${{ number_format($bonus->investment_amount, 2) }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge badge-info">{{ number_format($bonus->bonus_percentage, 1) }}%</span>
                                    </td>
                                    <td>
                                        <strong class="text-success">
                                            +${{ number_format($bonus->amount, 2) }}
                                        </strong>
                                    </td>
                                    <td>{{ $bonus->created_at->format('M d, Y H:i') }}</td>
                                    <td>
                                        <span class="badge badge-{{
                                            $bonus->status === 'completed' ? 'success' :
                                            ($bonus->status === 'pending' ? 'warning' : 'danger')
                                        }}">
                                            {{ ucfirst($bonus->status) }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted">
                                        <div class="py-4">
                                            <i class="fas fa-chart-line fa-3x mb-3"></i>
                                            <h4>No bonuses yet</h4>
                                            <p>Start referring people to earn 5-level commissions!</p>
                                            <a href="{{ route('referrals.index') }}" class="btn btn-primary">
                                                <i class="fas fa-share-alt"></i> Get Referral Link
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if(method_exists($bonuses, 'hasPages') && $bonuses->hasPages())
                    <div class="card-footer">
                        {{ $bonuses->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@stop