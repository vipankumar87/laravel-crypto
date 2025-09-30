@extends('layouts.user')

@section('title', 'Investment Plans')

@section('content_header')
    <h1>
        Investment Plans
        @if(request()->get('source'))
            <small class="text-muted">
                - Quick Invest {{ request()->get('source') == 'wallet' ? 'from Wallet' : 'via Crypto' }}
            </small>
        @endif
    </h1>
@stop

@section('content')
<div class="container-fluid">
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    @if(request()->get('source'))
        <div class="alert alert-info">
            <i class="fas fa-info-circle"></i>
            <strong>Quick Investment Mode:</strong>
            @if(request()->get('source') == 'wallet')
                You're investing from your wallet balance.
                @if(request()->get('amount'))
                    Pre-filled amount: ${{ number_format(request()->get('amount'), 2) }}
                @endif
            @else
                You're making a crypto investment with a 1 USDT processing fee.
            @endif
        </div>
    @endif

    <div class="row">
        @foreach($plans as $plan)
            <div class="col-md-6 col-lg-4 mb-4">
                <x-investment-plan-card :plan="$plan" :source="request()->get('source')" />
            </div>
        @endforeach
    </div>
    
    <div class="row mt-4">
        <div class="col-12">
            <x-data-card title="Investment Information" type="info" outline="true">
                <div class="row">
                    <div class="col-md-6">
                        <h5>How Our Investments Work</h5>
                        <ul class="text-muted">
                            <li>Choose an investment plan that suits your goals</li>
                            <li>Invest any amount within the plan's limits</li>
                            <li>Receive daily returns directly to your wallet</li>
                            <li>Track your investments in real-time</li>
                            <li>Refer others to earn additional bonuses</li>
                        </ul>
                    </div>
                    <div class="col-md-6">
                        <h5>Investment Security</h5>
                        <ul class="text-muted">
                            <li>All investments are secured by our reserve fund</li>
                            <li>Transparent blockchain-based transactions</li>
                            <li>Real-time monitoring of investment performance</li>
                            <li>24/7 customer support for any questions</li>
                        </ul>
                    </div>
                </div>
            </x-data-card>
        </div>
    </div>
</div>
@stop

@section('js')
<script>
// Additional JavaScript for the investment plans page
document.addEventListener('DOMContentLoaded', function() {
    // Handle pre-filled amounts from query parameters if needed
    const sourceParam = new URLSearchParams(window.location.search).get('source');
    const amountParam = new URLSearchParams(window.location.search).get('amount');
    
    if (sourceParam && amountParam) {
        console.log(`Quick invest mode: ${sourceParam} with amount: ${amountParam}`);
    }
    
    // Add any additional page-specific JavaScript here
});
</script>
@stop