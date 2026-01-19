@extends('layouts.user')

@section('title', 'Crypto Payment')

@section('content_header')
    <h1>Complete Crypto Payment</h1>
@stop

@section('content')
<div class="container-fluid">
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif
    @if(session('info'))
        <div class="alert alert-info">{{ session('info') }}</div>
    @endif
    @php
        $amount = $investment->amount+1; // Amount in USDT (decimal format)
        $token_contract = '0x55d398326f99059ff775485246999027b3197955'; // USDT BEP-20 contract address
        $amount_in_wei = bcmul($amount, bcpow('10', '18')); // Convert to Wei format
        // Construct the EIP-681 deep link that works with both MetaMask and Trust Wallet
        $address = Auth::user()->crypto_address ?? '';
        $qr_data = "ethereum:$token_contract/transfer?address={$address}&uint256={$amount_in_wei}";
        $encoded_qr_data = urlencode($qr_data);
    
    @endphp
    <!-- QR Code Payment Card - Full Width Above -->
    <div class="row">
        <div class="col-12">
            <div class="card card-primary card-outline">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-qrcode"></i> Scan QR Code to Pay
                    </h3>
                    <div class="card-tools">
                        <span class="badge badge-warning">
                            <i class="fas fa-clock"></i> Pending Payment
                        </span>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <!-- QR Code Section -->
                        <div class="col-md-4 text-center">
                            <div id="qrCodeContainer">
                                <!-- Loading State -->
                                <div id="qrLoading" class="mb-3">
                                    <div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem;">
                                        <span class="sr-only">Loading...</span>
                                    </div>
                                    <p class="text-muted mt-3">We are checking transaction in background do not close or refresh...</p>
                                </div>

                                <!-- QR Code (hidden initially, shown after loading) -->
                                <div id="qrCodeDisplay">
                                    <div class="border rounded p-3 bg-white d-inline-block">
                                        <img src="https://quickchart.io/qr?text={{ $encoded_qr_data }}&size=250"
                                            alt="BSC USDT Payment QR Code"
                                            class="img-fluid border rounded-3 shadow w-100">

                                    </div>
                                    <p class="text-muted mt-2 small">Scan with your crypto wallet</p>
                                </div>
                            </div>
                        </div>

                        <!-- Payment Details Section -->
                        <div class="col-md-8">
                            <div class="row">
                                <div class="col-12">
                                    <h5 class="mb-3">Payment Information</h5>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="font-weight-bold">Amount to Pay</label>
                                <div class="input-group input-group-lg">
                                    <input type="text" class="form-control" value="{{ number_format($investment->amount, 2) }} USDT" readonly>
                                    <div class="input-group-append">
                                        <span class="input-group-text bg-warning">
                                            <i class="fas fa-coins"></i>
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="font-weight-bold">Wallet Address</label>
                                <div class="input-group">
                                    <input type="text" class="form-control" id="walletAddress" value="Loading wallet address..." readonly>
                                    <div class="input-group-append">
                                        <button class="btn btn-outline-secondary" type="button" onclick="copyWalletAddress()">
                                            <i class="fas fa-copy"></i> Copy
                                        </button>
                                    </div>
                                </div>
                                <small class="text-muted">Send exactly the amount shown above to this address</small>
                            </div>

                            <div class="form-group">
                                <label class="font-weight-bold">Network</label>
                                <div class="input-group">
                                    <input type="text" class="form-control" id="networkType" value="Loading network..." readonly>
                                    <div class="input-group-append">
                                        <span class="input-group-text">
                                            <i class="fas fa-network-wired"></i>
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <div class="alert alert-warning mb-0">
                                <i class="fas fa-exclamation-triangle"></i>
                                <strong>Important:</strong>
                                <ul class="mb-0 mt-2">
                                    <li>Only send USDT on the specified network</li>
                                    <li>Double-check the wallet address before sending</li>
                                    <li>Minimum confirmations required: 3</li>
                                    <li>Payment will be verified automatically</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <div class="row">
                        <div class="col-md-6">
                            <small class="text-muted">
                                <i class="fas fa-shield-alt"></i>
                                Secure payment via blockchain
                            </small>
                        </div>
                        <div class="col-md-6 text-right">
                            <small class="text-muted">
                                <i class="fas fa-sync-alt"></i>
                                Auto-refresh: <span id="refreshCounter">30s</span>
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h3 class="card-title">
                        <i class="fas fa-coins"></i> Crypto Payment Details
                    </h3>
                </div>
                <div class="card-body">
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i>
                        <strong>Important:</strong> Your investment is currently pending. Please complete the crypto payment to activate it.
                    </div>

                    <!-- Investment Details -->
                    <div class="mb-4">
                        <h5>Investment Summary</h5>
                        <table class="table table-bordered">
                            <tr>
                                <th width="40%">Investment Plan</th>
                                <td>{{ $investment->investment_plan }}</td>
                            </tr>
                            <tr>
                                <th>Amount</th>
                                <td>${{ number_format($investment->amount, 2) }}</td>
                            </tr>
                            <tr>
                                <th>Daily Return Rate</th>
                                <td>{{ $investment->daily_return_rate }}%</td>
                            </tr>
                            <tr>
                                <th>Duration</th>
                                <td>{{ $investment->duration_days }} days</td>
                            </tr>
                            <tr>
                                <th>Expected Return</th>
                                <td>${{ number_format($investment->expected_return, 2) }}</td>
                            </tr>
                            <tr>
                                <th>Status</th>
                                <td>
                                    <span class="badge badge-warning">
                                        <i class="fas fa-clock"></i> Pending Payment
                                    </span>
                                </td>
                            </tr>
                        </table>
                    </div>

                    <!-- Crypto Payment Instructions Section -->
                    <div class="card bg-light">
                        <div class="card-body">
                            <h5 class="card-title">
                                <i class="fas fa-wallet"></i> Payment Instructions
                            </h5>
                            <p class="text-muted">Complete your payment using cryptocurrency to activate your investment.</p>

                            <!-- YOU CAN CODE YOUR CRYPTO PAYMENT LOGIC HERE -->
                            <div class="mt-3">
                                <p><strong>TODO: Implement crypto payment gateway</strong></p>
                                <ul class="text-muted">
                                    <li>Display wallet address for payment</li>
                                    <li>Show QR code for easy scanning</li>
                                    <li>Add payment confirmation mechanism</li>
                                    <li>Integrate with blockchain verification</li>
                                    <li>Update investment status after payment confirmation</li>
                                </ul>
                            </div>

                            <!-- Placeholder for crypto payment form/widget -->
                            <div class="mt-4 p-4 border rounded text-center">
                                <i class="fas fa-bitcoin fa-3x text-warning mb-3"></i>
                                <p class="lead">Crypto Payment Gateway</p>
                                <p class="text-muted">This is where you will integrate your crypto payment solution</p>
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="mt-4">
                        <a href="{{ route('investments.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Back to Investments
                        </a>
                        <button type="button" class="btn btn-success" onclick="checkPaymentNow()">
                            <i class="fas fa-sync-alt"></i> Check Payment Now
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar with Instructions -->
        <div class="col-md-4">
            <div class="card card-info">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-info-circle"></i> Payment Guide
                    </h3>
                </div>
                <div class="card-body">
                    <h6 class="font-weight-bold">Step 1: Choose Network</h6>
                    <p class="text-muted small">Select the blockchain network you want to use for payment (e.g., BEP20, TRC20, ERC20).</p>

                    <h6 class="font-weight-bold mt-3">Step 2: Send Payment</h6>
                    <p class="text-muted small">Send exactly ${{ number_format($investment->amount, 2) }} USDT to the provided wallet address.</p>

                    <h6 class="font-weight-bold mt-3">Step 3: Verify Transaction</h6>
                    <p class="text-muted small">Wait for blockchain confirmation. This usually takes 1-5 minutes.</p>

                    <h6 class="font-weight-bold mt-3">Step 4: Activation</h6>
                    <p class="text-muted small">Once confirmed, your investment will be automatically activated.</p>

                    <div class="alert alert-warning mt-3">
                        <small>
                            <i class="fas fa-shield-alt"></i>
                            <strong>Security Tip:</strong> Always double-check the wallet address before sending.
                        </small>
                    </div>
                </div>
            </div>

            <!-- Investment Timer (Optional) -->
            <div class="card card-warning mt-3">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-clock"></i> Payment Window
                    </h3>
                </div>
                <div class="card-body text-center">
                    <p class="text-muted small">Complete payment within:</p>
                    <h3 class="text-warning" id="timer">30:00</h3>
                    <small class="text-muted">Investment created: {{ $investment->created_at->diffForHumans() }}</small>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Payment Result Modal -->
<div class="modal fade" id="paymentResultModal" tabindex="-1" role="dialog" aria-labelledby="paymentResultModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="paymentResultModalLabel">
                    <i class="fas fa-check-circle"></i> Payment Processed
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="text-center mb-3">
                    <i class="fas fa-coins fa-3x text-success mb-3"></i>
                    <h4 id="modalMessage">Processing...</h4>
                </div>
                <div class="card">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-6">
                                <p class="text-muted mb-1">Amount Found (USDT)</p>
                                <h4 class="text-primary" id="amountFound">0.00</h4>
                            </div>
                            <div class="col-6">
                                <p class="text-muted mb-1">Dogecoin Invested</p>
                                <h4 class="text-warning" id="dogeInvested">0.00000000</h4>
                            </div>
                        </div>
                        <hr>
                        <div class="row">
                            <div class="col-6">
                                <p class="text-muted mb-1">Transactions</p>
                                <h5 id="transactionsCount">0</h5>
                            </div>
                            <div class="col-6">
                                <p class="text-muted mb-1">Investments</p>
                                <h5 id="investmentsCount">0</h5>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <a href="{{ route('investments.index') }}" class="btn btn-primary">
                    <i class="fas fa-list"></i> View Investments
                </a>
            </div>
        </div>
    </div>
</div>
@stop

@section('js')
<!-- QR Code Library -->
<script src="https://cdn.jsdelivr.net/npm/qrcode@1.5.3/build/qrcode.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Simulate loading wallet address and generating QR code
    setTimeout(function() {
        // Get wallet address from authenticated user (this is the public address)
        const walletAddress = '{{ Auth::user()->crypto_address ?? "Address not generated" }}';
        const networkType = 'BNB (BEP20)'; // Network type
        const amount = '{{ $investment->amount }}';

        // Update wallet address display
        document.getElementById('walletAddress').value = walletAddress;
        document.getElementById('networkType').value = networkType;

        // Create payment URI for better wallet app compatibility
        // Format: ethereum:address@chainId?value=amount
        // For BNB Smart Chain, we just use the address (most wallets support direct address scanning)
        const qrData = walletAddress;

        // Alternative with amount (some wallets support this):
        // const qrData = `ethereum:${walletAddress}@56?value=${amount}`;

        // Generate QR Code with the public crypto address
        const canvas = document.getElementById('qrCanvas');
        QRCode.toCanvas(canvas, qrData, {
            width: 200,
            margin: 2,
            color: {
                dark: '#000000',
                light: '#ffffff'
            },
            errorCorrectionLevel: 'H' // High error correction for better scanning
        }, function(error) {
            if (error) {
                console.error('QR Code generation error:', error);
                document.getElementById('qrLoading').innerHTML = '<p class="text-danger">Error generating QR code</p>';
            } else {
                // Hide loading, show QR code
                document.getElementById('qrLoading').style.display = 'none';
                document.getElementById('qrCodeDisplay').style.display = 'block';

                console.log('✅ QR Code generated for address:', walletAddress);
                console.log('📱 Amount to pay:', amount, 'USDT');
            }
        });
    }, 2000); // 2 second simulated loading

    // Copy wallet address function
    window.copyWalletAddress = function() {
        const walletInput = document.getElementById('walletAddress');
        walletInput.select();
        walletInput.setSelectionRange(0, 99999); // For mobile

        try {
            document.execCommand('copy');
            // Show success feedback
            const copyBtn = event.target.closest('button');
            const originalHTML = copyBtn.innerHTML;
            copyBtn.innerHTML = '<i class="fas fa-check"></i> Copied!';
            copyBtn.classList.add('btn-success');
            copyBtn.classList.remove('btn-outline-secondary');

            setTimeout(function() {
                copyBtn.innerHTML = originalHTML;
                copyBtn.classList.remove('btn-success');
                copyBtn.classList.add('btn-outline-secondary');
            }, 2000);
        } catch (err) {
            alert('Failed to copy. Please copy manually.');
        }
    };

    // Demo button to simulate payment confirmation
    const confirmBtn = document.getElementById('confirmPaymentBtn');
    if (confirmBtn) {
        confirmBtn.addEventListener('click', function() {
            if (confirm('This is a demo function. In production, this should only trigger after actual crypto payment verification.\n\nProceed to mark investment as paid?')) {
                alert('Payment confirmation logic not yet implemented.\n\nYou need to:\n1. Create a route for payment confirmation\n2. Verify the blockchain transaction\n3. Update investment status to "active"\n4. Credit referral bonus if applicable');
            }
        });
    }

    // Payment timer
    let timeLeft = 30 * 60; // 30 minutes in seconds
    const timerElement = document.getElementById('timer');

    function updateTimer() {
        const minutes = Math.floor(timeLeft / 60);
        const seconds = timeLeft % 60;
        timerElement.textContent = `${minutes}:${seconds.toString().padStart(2, '0')}`;

        if (timeLeft > 0) {
            timeLeft--;
        } else {
            timerElement.textContent = 'Expired';
            timerElement.classList.add('text-danger');
        }
    }

    setInterval(updateTimer, 1000);
    updateTimer();

    // Auto-refresh counter
    let refreshCount = 30;
    const refreshCounter = document.getElementById('refreshCounter');

    function updateRefreshCounter() {
        refreshCounter.textContent = refreshCount + 's';
        if (refreshCount > 0) {
            refreshCount--;
        } else {
            // TODO: Check payment status via API
            console.log('Checking payment status...');
            refreshCount = 30; // Reset counter
        }
    }

    setInterval(updateRefreshCounter, 1000);
    updateRefreshCounter();

    // Check for new transactions automatically
    let checkInterval;
    let isChecking = false;

    // Function to check for new transactions and process payment
    function checkForNewTransactions() {
        if (isChecking) {
            console.log('Already checking, skipping...');
            return;
        }
        
        isChecking = true;
        console.log('Checking for new transactions...');
        
        // Show checking message
        showNotification('info', 'Checking for new transactions...');
        
        $.ajax({
            url: '{{ route('investments.process-payment') }}',
            type: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            success: function(response) {
                isChecking = false;
                
                if (response.success === true) {
                    console.log('Payment processed:', response);
                    
                    // Update modal with data
                    $('#modalMessage').text(response.message);
                    $('#amountFound').text(response.data.amount_found + ' USDT');
                    $('#dogeInvested').text(response.data.doge_invested + ' DOGE');
                    $('#transactionsCount').text(response.data.transactions_count);
                    $('#investmentsCount').text(response.data.investments_count);
                    
                    // Show the modal
                    $('#paymentResultModal').modal('show');
                    
                    // Stop checking after successful payment
                    clearInterval(checkInterval);
                    
                    // Show success message on screen
                    showNotification('success', response.message);
                } else {
                    console.log('No new transactions found');
                }
            },
            error: function(xhr, status, error) {
                isChecking = false;
                console.error('Error checking transactions:', error);
                showNotification('error', 'Error checking transactions. Will retry...');
            }
        });
    }

    // Start checking after 5 seconds
    setTimeout(function() {
        showNotification('info', 'Starting automatic payment detection...');
        checkForNewTransactions();
        
        // Then check every 5 seconds
        checkInterval = setInterval(checkForNewTransactions, 5000);
    }, 5000);

    // Manual check button
    window.checkPaymentNow = function() {
        showNotification('info', 'Checking for new transactions...');
        checkForNewTransactions();
    };

    // Function to show notification
    function showNotification(type, message) {
        const alertClass = type === 'success' ? 'alert-success' : type === 'error' ? 'alert-danger' : 'alert-info';
        const icon = type === 'success' ? 'fa-check-circle' : type === 'error' ? 'fa-exclamation-circle' : 'fa-info-circle';
        
        const notification = $(`
            <div class="alert ${alertClass} alert-dismissible fade show" role="alert" style="position: fixed; top: 20px; right: 20px; z-index: 9999; min-width: 300px;">
                <i class="fas ${icon}"></i> ${message}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        `);
        
        $('body').append(notification);
        
        setTimeout(function() {
            notification.fadeOut(function() {
                $(this).remove();
            });
        }, 5000);
    }
});
</script>
@stop
