@extends('layouts.admin')

@section('title', 'Investment Management')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">Investment Management</h3>
                    <div class="card-tools">
                        <div class="input-group input-group-sm" style="width: 250px;">
                            <input type="text" name="table_search" class="form-control float-right" placeholder="Search investments...">
                            <div class="input-group-append">
                                <button type="submit" class="btn btn-default">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-body table-responsive p-0">
                    <table class="table table-hover text-nowrap">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>User</th>
                                <th>Plan</th>
                                <th>Amount</th>
                                <th>Expected Return</th>
                                <th>Status</th>
                                <th>Progress</th>
                                <th>Start Date</th>
                                <th>End Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($investments as $investment)
                                <tr>
                                    <td>{{ $investment->id }}</td>
                                    <td>
                                        <a href="{{ route('admin.users.show', $investment->user) }}" class="text-decoration-none">
                                            {{ $investment->user->username }}
                                        </a>
                                    </td>
                                    <td>
                                        @if($investment->plan)
                                            <span class="badge badge-info">{{ $investment->plan->name }}</span>
                                        @else
                                            <span class="text-muted">Plan not found</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="font-weight-bold text-primary">
                                            ${{ number_format($investment->amount, 2) }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="font-weight-bold text-success">
                                            ${{ number_format($investment->expected_return ?? ($investment->amount * 1.2), 2) }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge badge-{{ $investment->status === 'active' ? 'success' : ($investment->status === 'completed' ? 'primary' : 'warning') }}">
                                            {{ ucfirst($investment->status) }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($investment->status === 'active')
                                            @php
                                                $progress = $investment->progress ?? 0;
                                            @endphp
                                            <div class="progress" style="height: 20px;">
                                                <div class="progress-bar" role="progressbar"
                                                     style="width: {{ $progress }}%"
                                                     aria-valuenow="{{ $progress }}"
                                                     aria-valuemin="0"
                                                     aria-valuemax="100">
                                                    {{ $progress }}%
                                                </div>
                                            </div>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>{{ $investment->created_at->format('M d, Y') }}</td>
                                    <td>
                                        @if($investment->end_date)
                                            {{ $investment->end_date->format('M d, Y') }}
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            @if($investment->status === 'active')
                                                <button type="button" class="btn btn-sm btn-outline-warning"
                                                        title="Pause Investment">
                                                    <i class="fas fa-pause"></i>
                                                </button>
                                                <button type="button" class="btn btn-sm btn-outline-success"
                                                        title="Complete Investment">
                                                    <i class="fas fa-check"></i>
                                                </button>
                                            @endif
                                            <button type="button" class="btn btn-sm btn-outline-primary"
                                                    title="View Details">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="10" class="text-center text-muted py-4">
                                        <i class="fas fa-chart-line fa-2x mb-2"></i>
                                        <br>
                                        No investments found
                                    </td>
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

    <!-- Investment Summary Stats -->
    <div class="row mt-4">
        <div class="col-lg-3 col-6">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>{{ $investments->where('status', 'active')->count() }}</h3>
                    <p>Active Investments</p>
                </div>
                <div class="icon">
                    <i class="fas fa-chart-line"></i>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>{{ $investments->where('status', 'completed')->count() }}</h3>
                    <p>Completed</p>
                </div>
                <div class="icon">
                    <i class="fas fa-check-circle"></i>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3>${{ number_format($investments->sum('amount'), 2) }}</h3>
                    <p>Total Invested</p>
                </div>
                <div class="icon">
                    <i class="fas fa-dollar-sign"></i>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-danger">
                <div class="inner">
                    <h3>{{ $investments->where('status', 'pending')->count() }}</h3>
                    <p>Pending</p>
                </div>
                <div class="icon">
                    <i class="fas fa-clock"></i>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection