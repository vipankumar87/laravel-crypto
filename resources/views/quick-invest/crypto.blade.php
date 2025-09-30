@extends('layouts.user')

@section('title', 'Crypto Wallet Investment')

@section('content_header')
    <h1>
        <i class="fab fa-bitcoin"></i> Crypto Wallet Investment
        <small class="text-muted">Add your investment code</small>
    </h1>
@stop

@section('content')
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card card-outline card-warning">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fab fa-bitcoin"></i> Enter Your Investment Code
                    </h3>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i>
                        <strong>Note:</strong> Crypto investments include a 1 USDT processing fee.
                    </div>

                    <form id="cryptoInvestForm">
                        @csrf
                        <div class="form-group">
                            <label for="investment_code">Investment Code</label>
                            <input type="text"
                                   class="form-control form-control-lg"
                                   id="investment_code"
                                   name="investment_code"
                                   placeholder="Enter your investment code here..."
                                   required>
                            <small class="form-text text-muted">
                                Please enter the code provided by your crypto wallet or payment provider.
                            </small>
                        </div>

                        <div class="form-group">
                            <label for="amount">Investment Amount (USDT)</label>
                            <input type="number"
                                   class="form-control form-control-lg"
                                   id="amount"
                                   name="amount"
                                   placeholder="0.00"
                                   min="1"
                                   step="0.01"
                                   required>
                            <small class="form-text text-muted">
                                Minimum investment: 1 USDT
                            </small>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <button type="button" class="btn btn-secondary btn-lg btn-block" onclick="window.history.back()">
                                    <i class="fas fa-arrow-left"></i> Back to Dashboard
                                </button>
                            </div>
                            <div class="col-md-6">
                                <button type="submit" class="btn btn-warning btn-lg btn-block">
                                    <i class="fab fa-bitcoin"></i> Confirm Crypto Investment
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Information Card -->
            <div class="card card-outline card-info">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-question-circle"></i> How it works
                    </h3>
                </div>
                <div class="card-body">
                    <ol>
                        <li>Enter your investment code from your crypto wallet</li>
                        <li>Specify the amount you want to invest</li>
                        <li>Review the total amount including the 1 USDT processing fee</li>
                        <li>Confirm your investment</li>
                    </ol>

                    <div class="alert alert-warning mt-3">
                        <i class="fas fa-exclamation-triangle"></i>
                        <strong>Important:</strong> All crypto investments are subject to a 1 USDT processing fee.
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

@section('js')
<script>
document.getElementById('cryptoInvestForm').addEventListener('submit', function(e) {
    e.preventDefault();

    const code = document.getElementById('investment_code').value.trim();
    const amount = parseFloat(document.getElementById('amount').value);

    if (!code) {
        alert('Please enter your investment code.');
        return;
    }

    if (isNaN(amount) || amount < 1) {
        alert('Please enter a valid amount (minimum 1 USDT).');
        return;
    }

    const fee = 1.00;
    const total = amount + fee;

    // Show confirmation popup with fee
    const confirmed = confirm(`Confirm Crypto Investment:\n\nInvestment Code: ${code}\nAmount: ${amount.toFixed(2)} USDT\nProcessing Fee: ${fee.toFixed(2)} USDT\nTotal: ${total.toFixed(2)} USDT\n\nProceed with this investment?`);

    if (confirmed) {
        // Here you would normally process the crypto investment
        alert('Crypto investment submitted for processing. You will be notified once confirmed.');

        // Redirect to dashboard after successful submission
        setTimeout(() => {
            window.location.href = '{{ route("dashboard") }}';
        }, 2000);
    }
});
</script>
@stop