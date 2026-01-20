@extends('adminlte::page')

@section('title', 'Investment Plan Details')

@section('content_header')
    <h1>Investment Plan: {{ $plan->name ?? 'Plan Details' }}</h1>
@stop

@section('content')
<div class="container-fluid">
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Plan Information</h3>
                    <div class="card-tools">
                        <a href="{{ route('admin.investment-plans.edit', $plan ?? 1) }}" class="btn btn-warning btn-sm">
                            <i class="fas fa-edit"></i> Edit Plan
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <table class="table table-bordered">
                        <tr>
                            <th width="30%">Name:</th>
                            <td>{{ $plan->name ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <th>Status:</th>
                            <td>
                                <span class="badge badge-{{ ($plan->status ?? 'inactive') === 'active' ? 'success' : 'secondary' }}">
                                    {{ ucfirst($plan->status ?? 'inactive') }}
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <th>Minimum Investment:</th>
                            <td>${{ number_format($plan->min_amount ?? 0, 2) }}</td>
                        </tr>
                        <tr>
                            <th>Maximum Investment:</th>
                            <td>${{ number_format($plan->max_amount ?? 0, 2) }}</td>
                        </tr>
                        <tr>
                            <th>Daily Return Rate:</th>
                            <td>{{ number_format($plan->daily_return_rate ?? 0, 2) }}%</td>
                        </tr>
                        <tr>
                            <th>Total Return Rate:</th>
                            <td>{{ number_format($plan->total_return_rate ?? 0, 2) }}%</td>
                        </tr>
                        <tr>
                            <th>Duration:</th>
                            <td>{{ $plan->duration_days ?? 0 }} days</td>
                        </tr>
                        <tr>
                            <th>Max Investors:</th>
                            <td>{{ $plan->max_investors ?? 0 == 0 ? 'Unlimited' : number_format($plan->max_investors ?? 0) }}</td>
                        </tr>
                        <tr>
                            <th>Created:</th>
                            <td>{{ ($plan->created_at ?? now())->format('M d, Y H:i') }}</td>
                        </tr>
                        <tr>
                            <th>Last Updated:</th>
                            <td>{{ ($plan->updated_at ?? now())->format('M d, Y H:i') }}</td>
                        </tr>
                    </table>

                    @if(($plan->description ?? ''))
                        <div class="mt-3">
                            <h5>Description:</h5>
                            <p class="text-muted">{{ $plan->description }}</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Plan Statistics</h3>
                </div>
                <div class="card-body">
                    <div class="info-box mb-3">
                        <span class="info-box-icon bg-info"><i class="fas fa-users"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Total Investors</span>
                            <span class="info-box-number">{{ $stats['total_investors'] ?? 0 }}</span>
                        </div>
                    </div>

                    <div class="info-box mb-3">
                        <span class="info-box-icon bg-success"><i class="fas fa-dollar-sign"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Total Invested</span>
                            <span class="info-box-number">${{ number_format($stats['total_invested'] ?? 0, 2) }}</span>
                        </div>
                    </div>

                    <div class="info-box mb-3">
                        <span class="info-box-icon bg-warning"><i class="fas fa-chart-line"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Active Investments</span>
                            <span class="info-box-number">{{ $stats['active_investments'] ?? 0 }}</span>
                        </div>
                    </div>

                    <div class="info-box">
                        <span class="info-box-icon bg-primary"><i class="fas fa-clock"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Avg. Duration</span>
                            <span class="info-box-number">{{ $plan->duration_days ?? 0 }} days</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Actions</h3>
                </div>
                <div class="card-body">
                    <a href="{{ route('admin.investment-plans.index') }}" class="btn btn-secondary btn-block">
                        <i class="fas fa-arrow-left"></i> Back to Plans
                    </a>
                    <a href="{{ route('admin.investment-plans.edit', $plan ?? 1) }}" class="btn btn-warning btn-block">
                        <i class="fas fa-edit"></i> Edit Plan
                    </a>
                    <form action="{{ route('admin.investment-plans.destroy', $plan ?? 1) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this plan?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger btn-block">
                            <i class="fas fa-trash"></i> Delete Plan
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@stop