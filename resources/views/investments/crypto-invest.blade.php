@extends('layouts.user')

@section('title', 'Crypto Wallet Investment')

@section('content')
<div class="container-fluid p-0">
    <div class="d-flex align-items-center mb-3">
        <div class="mr-auto">
            <h1 class="m-0"><i class="fas fa-coins mr-2"></i> Crypto Wallet Investment</h1>
            <p class="text-muted">Add your investment code</p>
        </div>
    </div>

    <div class="card border mb-4">
        <div class="card-body p-0">
            <!-- Investment Code Section -->
            <div class="p-3 border-bottom bg-light">
                <h5 class="font-weight-bold mb-0">
                    <i class="fas fa-key mr-2"></i> Enter Your Investment Code
                </h5>
            </div>

            <div class="alert alert-info m-3" style="background-color: #17a2b8; color: white; border: none;">
                <i class="fas fa-info-circle"></i> Note: Crypto investments include a 1 USDT processing fee.
            </div>

            <form id="cryptoInvestmentForm" method="POST" action="{{ route('investments.crypto.process') }}" class="p-3">
                @csrf
                <div class="form-group">
                    <label for="investmentCode">Investment Code</label>
                    <input type="text" class="form-control" id="investmentCode" name="investment_code" placeholder="Enter your investment code here..." required>
                    <small class="form-text text-muted">Please enter the code provided by your crypto wallet or payment provider.</small>
                </div>

                <div class="form-group">
                    <label for="investmentAmount">Investment Amount (USDT)</label>
                    <input type="number" class="form-control" id="investmentAmount" name="amount" placeholder="0.00" min="10" step="0.01" required>
                    <small class="form-text text-muted">Minimum investment: 10 USDT</small>
                </div>

                <div class="d-flex mt-4">
                    <a href="{{ route('dashboard') }}" class="btn btn-secondary flex-grow-1 mr-2" style="background-color: #6c757d; border-color: #6c757d;">
                        <i class="fas fa-arrow-left mr-1"></i> Back to Dashboard
                    </a>
                    <button type="submit" class="btn btn-warning flex-grow-1" style="background-color: #ffc107; border-color: #ffc107; color: #212529;">
                        <i class="fas fa-check-circle mr-1"></i> Confirm Crypto Investment
                    </button>
                </div>
            </form>

            <!-- How it works section -->
            <div class="border-top mt-3">
                <div class="p-3 bg-light">
                    <h5 class="font-weight-bold mb-0">
                        <i class="fas fa-question-circle mr-2"></i> How it works
                    </h5>
                </div>
                
                <div class="p-3">
                    <ol>
                        <li>Enter your investment code from your crypto wallet</li>
                        <li>Specify the amount you wish to invest</li>
                        <li>Review the total amount including the 1 USDT processing fee</li>
                        <li>Confirm your investment</li>
                    </ol>

                    <div class="alert alert-warning" style="background-color: #ffc107; color: #212529; border: none;">
                        <i class="fas fa-exclamation-triangle"></i> <strong>Important:</strong> All crypto investments are subject to a 1 USDT processing fee.
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="text-muted mt-3 small text-center">
        Copyright © {{ date('Y') }} CryptoInvest. All rights reserved.
        <span class="ml-2">Version 1.0.0</span>
    </div>
</div>

@stop

@section('js')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('cryptoInvestmentForm');
    const amountInput = document.getElementById('investmentAmount');
    
    // Set focus to the investment code input
    document.getElementById('investmentCode').focus();
    
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const amount = parseFloat(amountInput.value);
        if (isNaN(amount) || amount < 10) {
            alert('Please enter a valid investment amount (minimum 10 USDT)');
            return;
        }
        
        const fee = 1;
        const total = amount + fee;
        
        const confirmMsg = `Confirm Investment Details:

Amount: ${amount.toFixed(2)} USDT
Processing Fee: ${fee.toFixed(2)} USDT
Total: ${total.toFixed(2)} USDT

Do you want to proceed with this investment?`;
        
        if (confirm(confirmMsg)) {
            this.submit();
        }
    });
});
</script>
@stop
