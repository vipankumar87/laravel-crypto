@extends('layouts.user')

@section('title', 'Quick Invest')

@section('content_header')
    <h1>Quick Invest</h1>
@stop

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <x-data-card title="Choose Investment Method" type="primary">
                <div class="row">
                    <div class="col-md-6 mb-4">
                        <div class="card h-100">
                            <div class="card-body text-center">
                                <div class="mb-4">
                                    <i class="fas fa-wallet fa-5x text-primary"></i>
                                </div>
                                <h4>Invest from Wallet</h4>
                                <p class="text-muted">Use your existing wallet balance to make an investment</p>
                                <p>Available Balance: <strong>${{ number_format(auth()->user()->wallet->balance ?? 0, 2) }}</strong></p>
                                
                                @if((auth()->user()->wallet->balance ?? 0) > 0)
                                    <x-action-button 
                                        href="{{ route('investments.plans', ['source' => 'wallet']) }}" 
                                        color="primary" 
                                        icon="fas fa-arrow-right" 
                                        block="true">
                                        Continue with Wallet Balance
                                    </x-action-button>
                                @else
                                    <x-action-button 
                                        href="{{ route('wallet.index') }}" 
                                        color="warning" 
                                        icon="fas fa-plus-circle" 
                                        block="true">
                                        Add Funds to Wallet First
                                    </x-action-button>
                                @endif
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6 mb-4">
                        <div class="card h-100">
                            <div class="card-body text-center">
                                <div class="mb-4">
                                    <i class="fab fa-bitcoin fa-5x text-warning"></i>
                                </div>
                                <h4>Invest with Cryptocurrency</h4>
                                <p class="text-muted">Make a direct investment using cryptocurrency</p>
                                <p>Supported: <strong>BTC, ETH, USDT, BNB</strong></p>
                                
                                <x-action-button 
                                    href="{{ route('investments.plans', ['source' => 'crypto']) }}" 
                                    color="warning" 
                                    icon="fas fa-arrow-right" 
                                    block="true">
                                    Continue with Crypto
                                </x-action-button>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="mt-4">
                    <x-user-notification type="info" dismissible="false">
                        <h5 class="mb-1">Investment Information</h5>
                        <ul class="mb-0">
                            <li>Minimum investment amount is $10</li>
                            <li>All investments are processed instantly</li>
                            <li>Returns are calculated and distributed daily</li>
                            <li>Refer friends to earn additional bonuses</li>
                        </ul>
                    </x-user-notification>
                </div>
            </x-data-card>
        </div>
    </div>
</div>
@stop
