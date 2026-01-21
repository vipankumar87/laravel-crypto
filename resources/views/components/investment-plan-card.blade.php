@props(['plan', 'source' => null])

@php
    $feeBreakdown = \App\Models\FeeSetting::getFeeBreakdown();
@endphp

<div class="card card-pricing">
    <div class="card-header bg-{{ $plan->featured ? 'warning' : 'primary' }} text-white">
        <h3 class="card-title">{{ $plan->name }}</h3>
        @if($plan->featured)
            <div class="ribbon-wrapper ribbon-lg">
                <div class="ribbon bg-success">
                    Popular
                </div>
            </div>
        @endif
    </div>
    <div class="card-body">
        <div class="text-center mb-4">
            <h2 class="pricing-amount">
                {{ $plan->daily_return_rate }}% <small>daily</small>
            </h2>
            <p class="text-muted">{{ $plan->duration_days }} days ({{ $plan->total_return_rate }}% total)</p>
        </div>

        <ul class="list-unstyled">
            <li class="py-2 border-bottom">
                <i class="fas fa-check-circle text-success mr-2"></i> Min: ${{ number_format($plan->min_amount, 2) }}
            </li>
            <li class="py-2 border-bottom">
                <i class="fas fa-check-circle text-success mr-2"></i> Max: ${{ number_format($plan->max_amount, 2) }}
            </li>
            <li class="py-2 border-bottom">
                <i class="fas fa-check-circle text-success mr-2"></i> {{ $plan->capital_returned ? 'Capital returned at end' : 'Capital included in returns' }}
            </li>
            <li class="py-2 border-bottom">
                <i class="fas fa-check-circle text-success mr-2"></i> {{ $plan->referral_bonus_rate }}% referral bonus
            </li>
            @if($plan->description)
                <li class="py-2">
                    <i class="fas fa-info-circle text-info mr-2"></i> {{ $plan->description }}
                </li>
            @endif
        </ul>
    </div>
    <div class="card-footer">
        <button type="button" class="btn btn-{{ $plan->featured ? 'warning' : 'primary' }} btn-block"
                onclick="showInvestmentModal('{{ $plan->id }}', '{{ $plan->name }}', {{ $plan->min_amount }}, {{ $plan->max_amount }}, '{{ $source }}')">
            <i class="fas fa-chart-line mr-2"></i> Invest Now
        </button>
    </div>
</div>

@once
@push('js')
<script>
// Fee settings from server
const feeSettings = @json(\App\Models\FeeSetting::getAll());

function calculateFees(amount) {
    let platformFee = 0;
    let transactionFee = 0;

    // Calculate platform fee
    const platformSetting = feeSettings['platform_fee'];
    if (platformSetting && platformSetting.is_active) {
        if (platformSetting.type === 'percentage') {
            platformFee = amount * (parseFloat(platformSetting.value) / 100);
        } else {
            platformFee = parseFloat(platformSetting.value);
        }
    }

    // Calculate transaction fee
    const transactionSetting = feeSettings['transaction_fee'];
    if (transactionSetting && transactionSetting.is_active) {
        if (transactionSetting.type === 'percentage') {
            transactionFee = amount * (parseFloat(transactionSetting.value) / 100);
        } else {
            transactionFee = parseFloat(transactionSetting.value);
        }
    }

    const totalFees = platformFee + transactionFee;
    const netAmount = amount - totalFees;

    return {
        platformFee: platformFee,
        transactionFee: transactionFee,
        totalFees: totalFees,
        netAmount: netAmount
    };
}

function updateFeeDisplay(planId) {
    const amount = parseFloat($(`#amount_${planId}`).val()) || 0;
    const fees = calculateFees(amount);

    $(`#fee_platform_${planId}`).text('$' + fees.platformFee.toFixed(2));
    $(`#fee_transaction_${planId}`).text('$' + fees.transactionFee.toFixed(2));
    $(`#fee_total_${planId}`).text('$' + fees.totalFees.toFixed(2));
    $(`#fee_net_${planId}`).text('$' + fees.netAmount.toFixed(2));

    // Update color based on net amount
    if (fees.netAmount < 0) {
        $(`#fee_net_${planId}`).removeClass('text-success').addClass('text-danger');
    } else {
        $(`#fee_net_${planId}`).removeClass('text-danger').addClass('text-success');
    }
}

function showInvestmentModal(planId, planName, minAmount, maxAmount, source) {
    // Create modal HTML
    const modalId = `investModal_${planId}`;

    // Remove existing modal if any
    $(`#${modalId}`).remove();

    // Get fee type labels
    const platformType = feeSettings['platform_fee']?.type === 'percentage' ? '%' : 'flat';
    const transactionType = feeSettings['transaction_fee']?.type === 'percentage' ? '%' : 'flat';

    // Create new modal
    const modalHtml = `
        <div class="modal fade" id="${modalId}" tabindex="-1" role="dialog" aria-labelledby="${modalId}Label" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="${modalId}Label">Invest in ${planName}</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <form id="investForm_${planId}" action="{{ route('investments.invest') }}" method="POST">
                        @csrf
                        <div class="modal-body">
                            <input type="hidden" name="plan_id" value="${planId}">
                            <input type="hidden" name="source" value="${source || 'direct'}">
                            <input type="hidden" id="payment_method_${planId}" name="payment_method" value="wallet">

                            <div class="form-group">
                                <label for="amount_${planId}">Investment Amount ($)</label>
                                <input type="number" class="form-control" id="amount_${planId}" name="amount"
                                       min="${minAmount}" max="${maxAmount}" step="0.01" required
                                       value="${minAmount}" onchange="updateFeeDisplay('${planId}')" oninput="updateFeeDisplay('${planId}')">
                                <small class="form-text text-muted">
                                    Min: $${minAmount.toFixed(2)} | Max: $${maxAmount.toFixed(2)}
                                </small>
                            </div>

                            <div class="card bg-light mb-3">
                                <div class="card-header py-2">
                                    <i class="fas fa-calculator mr-2"></i>Fee Breakdown
                                </div>
                                <div class="card-body py-2">
                                    <table class="table table-sm table-borderless mb-0">
                                        <tr>
                                            <td>Platform Fee <small class="text-muted">(${platformType})</small></td>
                                            <td class="text-right" id="fee_platform_${planId}">$0.00</td>
                                        </tr>
                                        <tr>
                                            <td>Transaction Fee <small class="text-muted">(${transactionType})</small></td>
                                            <td class="text-right" id="fee_transaction_${planId}">$0.00</td>
                                        </tr>
                                        <tr class="border-top">
                                            <td><strong>Total Fees</strong></td>
                                            <td class="text-right text-danger" id="fee_total_${planId}"><strong>$0.00</strong></td>
                                        </tr>
                                        <tr class="bg-success-light">
                                            <td><strong>Net Investment</strong></td>
                                            <td class="text-right text-success" id="fee_net_${planId}"><strong>$0.00</strong></td>
                                        </tr>
                                    </table>
                                </div>
                            </div>

                            <div class="alert alert-info mb-0">
                                <i class="fas fa-info-circle"></i>
                                Fees are deducted from your investment amount. The net investment amount will be used to calculate your returns.
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                            ${source === 'crypto' ?
                                '<button type="button" class="btn btn-primary" onclick="submitCryptoInvestment(' + planId + ')">Invest through crypto</button>' :
                                source === 'direct' ?
                                    '<button type="submit" class="btn btn-primary">Invest through wallet</button>' :
                                    '<button type="submit" class="btn btn-primary">Invest through wallet</button><button type="button" class="btn btn-primary ml-2" onclick="submitCryptoInvestment(' + planId + ')">Invest through crypto</button>'
                            }
                        </div>
                    </form>
                </div>
            </div>
        </div>
    `;

    // Append modal to body
    $('body').append(modalHtml);

    // Show modal and calculate initial fees
    $(`#${modalId}`).modal('show');
    updateFeeDisplay(planId);
}

function submitCryptoInvestment(planId) {
    // Set payment method to crypto
    $(`#payment_method_${planId}`).val('crypto');

    // Submit the form
    $(`#investForm_${planId}`).submit();
}
</script>
@endpush
@endonce
