@extends('adminlte::page')

@section('title', 'Investment Plans')

@section('content_header')
    <h1>Investment Plans Management</h1>
@stop

@section('content')
<div class="container-fluid">
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">All Investment Plans</h3>
            <div class="card-tools">
                <a href="{{ route('admin.investment-plans.create') }}" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus"></i> Create New Plan
                </a>
            </div>
        </div>
        <div class="card-body table-responsive">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Min Amount</th>
                        <th>Max Amount</th>
                        <th>Daily Rate</th>
                        <th>Total Rate</th>
                        <th>Duration</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($plans ?? [] as $plan)
                        <tr>
                            <td>{{ $plan->name }}</td>
                            <td>${{ number_format($plan->min_amount, 2) }}</td>
                            <td>${{ number_format($plan->max_amount, 2) }}</td>
                            <td>{{ number_format($plan->daily_return_rate, 2) }}%</td>
                            <td>{{ number_format($plan->total_return_rate, 2) }}%</td>
                            <td>{{ $plan->duration_days }} days</td>
                            <td>
                                <span class="badge badge-{{ $plan->status === 'active' ? 'success' : 'secondary' }}">
                                    {{ ucfirst($plan->status) }}
                                </span>
                            </td>
                            <td>
                                <div class="btn-group">
                                    <a href="{{ route('admin.investment-plans.show', $plan) }}" class="btn btn-sm btn-info">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('admin.investment-plans.edit', $plan) }}" class="btn btn-sm btn-warning">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('admin.investment-plans.destroy', $plan) }}" method="POST" style="display: inline;" onsubmit="return confirm('Are you sure?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted">No investment plans found</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@stop