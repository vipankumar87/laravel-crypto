@props(['plan', 'source' => null])

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
function showInvestmentModal(planId, planName, minAmount, maxAmount, source) {
    // Create modal HTML
    const modalId = `investModal_${planId}`;
    
    // Remove existing modal if any
    $(`#${modalId}`).remove();
    
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
                    <form action="{{ route('investments.invest') }}" method="POST">
                        @csrf
                        <div class="modal-body">
                            <input type="hidden" name="plan_id" value="${planId}">
                            <input type="hidden" name="source" value="${source || 'direct'}">
                            
                            <div class="form-group">
                                <label for="amount">Investment Amount ($)</label>
                                <input type="number" class="form-control" id="amount" name="amount" 
                                       min="${minAmount}" max="${maxAmount}" step="0.01" required
                                       value="${minAmount}">
                                <small class="form-text text-muted">
                                    Min: $${minAmount.toFixed(2)} | Max: $${maxAmount.toFixed(2)}
                                </small>
                            </div>
                            
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle"></i> 
                                Your investment will be active immediately after confirmation.
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary">Confirm Investment</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    `;
    
    // Append modal to body
    $('body').append(modalHtml);
    
    // Show modal
    $(`#${modalId}`).modal('show');
}
</script>
@endpush
@endonce
