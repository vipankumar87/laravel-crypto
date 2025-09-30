@extends('layouts.user')

@section('title', 'Investment History')

@section('content_header')
    <h1>Investment History</h1>
@stop

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">All Investments</h3>
                </div>
                <div class="card-body table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>Plan</th>
                                <th>Amount</th>
                                <th>Daily Rate</th>
                                <th>Expected Return</th>
                                <th>Duration</th>
                                <th>Date</th>
                                <th>Status</th>
                                <th>Earnings</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($investments as $investment)
                                <tr>
                                    <td>{{ $investment->investment_plan }}</td>
                                    <td>${{ number_format($investment->amount, 2) }}</td>
                                    <td>{{ number_format($investment->daily_return_rate, 2) }}%</td>
                                    <td>${{ number_format($investment->expected_return, 2) }}</td>
                                    <td>{{ $investment->duration_days }} days</td>
                                    <td>{{ $investment->created_at->format('M d, Y H:i') }}</td>
                                    <td>
                                        <span class="badge badge-{{
                                            $investment->status === 'active' ? 'success' :
                                            ($investment->status === 'completed' ? 'primary' : 'warning')
                                        }}">
                                            {{ ucfirst($investment->status) }}
                                        </span>
                                    </td>
                                    <td>${{ number_format($investment->current_return ?? 0, 2) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center text-muted">No investment history</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if(method_exists($investments, 'hasPages') && $investments->hasPages())
                    <div class="card-footer">
                        {{ $investments->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@stop