@extends('adminlte::page')

@section('title', 'Investment Plans')

@section('content_header')
    <h1>Investment Plans</h1>
@stop

@section('content')
<div class="container-fluid">
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="row">
        @foreach($plans as $plan)
            <div class="col-md-6 col-lg-4">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">{{ $plan->name }}</h3>
                        <span class="badge badge-success float-right">${{ number_format($plan->min_amount) }} - ${{ number_format($plan->max_amount) }}</span>
                    </div>
                    <div class="card-body">
                        <p>{{ $plan->description }}</p>

                        <ul class="list-unstyled">
                            <li><strong>Daily Return:</strong> {{ $plan->daily_return_rate }}%</li>
                            <li><strong>Duration:</strong> {{ $plan->duration_days }} days</li>
                            <li><strong>Total Return:</strong> {{ $plan->total_return_rate }}%</li>
                            <li><strong>Referral Bonus:</strong> {{ $plan->referral_bonus_rate }}%</li>
                        </ul>

                        <div class="progress mb-3">
                            @php
                                $percentage = $plan->max_investors ?
                                    min(($plan->total_investors / $plan->max_investors) * 100, 100) :
                                    0;
                            @endphp
                            <div class="progress-bar" role="progressbar" style="width: {{ $percentage }}%">
                                {{ $plan->total_investors }} {{ $plan->max_investors ? '/ ' . $plan->max_investors : '' }} investors
                            </div>
                        </div>

                        <form method="POST" action="{{ route('investments.invest') }}">
                            @csrf
                            <input type="hidden" name="plan_id" value="{{ $plan->id }}">
                            <div class="input-group mb-3">
                                <input type="number" name="amount" class="form-control"
                                       placeholder="Investment Amount"
                                       min="{{ $plan->min_amount }}"
                                       max="{{ $plan->max_amount }}"
                                       step="0.01" required>
                                <div class="input-group-append">
                                    <span class="input-group-text">$</span>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary btn-block">
                                Invest Now
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>
@stop