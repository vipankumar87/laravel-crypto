@extends('adminlte::page')

@section('title', 'Fee Settings')

@section('content_header')
    <h1>Investment Fee Settings</h1>
@stop

@section('content')
<div class="container-fluid">
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert">&times;</button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            {{ session('error') }}
            <button type="button" class="close" data-dismiss="alert">&times;</button>
        </div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Fee Calculator Card -->
    <div class="card card-outline card-info">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-calculator mr-2"></i>
                Fee Calculator Preview
            </h3>
        </div>
        <div class="card-body">
            <div class="row align-items-end">
                <div class="col-md-3">
                    <div class="form-group mb-0">
                        <label for="preview_amount">Investment Amount</label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text">$</span>
                            </div>
                            <input type="number" class="form-control" id="preview_amount" value="{{ $sampleAmount }}" min="0" step="0.01">
                        </div>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="small-box bg-warning mb-0">
                        <div class="inner py-2">
                            <h4 id="preview_platform_fee">${{ number_format($sampleFees['platform_fee'], 2) }}</h4>
                            <p class="mb-0">Platform Fee</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="small-box bg-info mb-0">
                        <div class="inner py-2">
                            <h4 id="preview_transaction_fee">${{ number_format($sampleFees['transaction_fee'], 2) }}</h4>
                            <p class="mb-0">Transaction Fee</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="small-box bg-danger mb-0">
                        <div class="inner py-2">
                            <h4 id="preview_total_fees">${{ number_format($sampleFees['total_fees'], 2) }}</h4>
                            <p class="mb-0">Total Fees</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="small-box bg-success mb-0">
                        <div class="inner py-2">
                            <h4 id="preview_net_amount">${{ number_format($sampleFees['net_amount'], 2) }}</h4>
                            <p class="mb-0">Net Investment</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Fee Settings Card -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-cog mr-2"></i>
                Configure Investment Fees
            </h3>
        </div>
        <form action="{{ route('admin.fee-settings.update') }}" method="POST">
            @csrf
            @method('PUT')
            <div class="card-body">
                <div class="alert alert-info">
                    <i class="fas fa-info-circle mr-2"></i>
                    <strong>How Fees Work:</strong> Fees are deducted from the investment amount before processing.
                    If a user invests $100 with a total fee of $5, the actual investment will be $95.
                </div>
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead class="bg-primary">
                            <tr>
                                <th style="width: 180px;">Fee Name</th>
                                <th style="width: 150px;">Value</th>
                                <th style="width: 180px;">Fee Type</th>
                                <th>Description</th>
                                <th style="width: 100px;">Active</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($settings as $setting)
                                <tr>
                                    <td>
                                        <input type="hidden" name="settings[{{ $loop->index }}][id]" value="{{ $setting->id }}">
                                        <span class="badge badge-primary badge-lg" style="font-size: 1.1em;">
                                            {{ $setting->label }}
                                        </span>
                                        <br>
                                        <small class="text-muted">{{ $setting->name }}</small>
                                    </td>
                                    <td>
                                        <input type="number"
                                               class="form-control fee-value @error('settings.'.$loop->index.'.value') is-invalid @enderror"
                                               name="settings[{{ $loop->index }}][value]"
                                               value="{{ old('settings.'.$loop->index.'.value', $setting->value) }}"
                                               data-fee-name="{{ $setting->name }}"
                                               step="0.0001"
                                               min="0"
                                               required>
                                    </td>
                                    <td>
                                        <select class="form-control fee-type @error('settings.'.$loop->index.'.type') is-invalid @enderror"
                                                name="settings[{{ $loop->index }}][type]"
                                                data-fee-name="{{ $setting->name }}">
                                            <option value="flat" {{ (old('settings.'.$loop->index.'.type', $setting->type) == 'flat') ? 'selected' : '' }}>
                                                Flat (Fixed Amount)
                                            </option>
                                            <option value="percentage" {{ (old('settings.'.$loop->index.'.type', $setting->type) == 'percentage') ? 'selected' : '' }}>
                                                Percentage (%)
                                            </option>
                                        </select>
                                    </td>
                                    <td>
                                        <input type="text"
                                               class="form-control"
                                               name="settings[{{ $loop->index }}][description]"
                                               value="{{ old('settings.'.$loop->index.'.description', $setting->description) }}"
                                               placeholder="Fee description">
                                    </td>
                                    <td class="text-center">
                                        <div class="custom-control custom-switch">
                                            <input type="checkbox"
                                                   class="custom-control-input fee-active"
                                                   id="active_{{ $setting->id }}"
                                                   name="settings[{{ $loop->index }}][is_active]"
                                                   value="1"
                                                   data-fee-name="{{ $setting->name }}"
                                                   {{ $setting->is_active ? 'checked' : '' }}>
                                            <label class="custom-control-label" for="active_{{ $setting->id }}"></label>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted">
                                        No fee settings configured.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save mr-1"></i> Save Changes
                </button>
            </div>
        </form>
    </div>

    <!-- Add New Fee Card -->
    <div class="card card-outline card-success collapsed-card">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-plus-circle mr-2"></i>
                Add Custom Fee (Advanced)
            </h3>
            <div class="card-tools">
                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                    <i class="fas fa-plus"></i>
                </button>
            </div>
        </div>
        <form action="{{ route('admin.fee-settings.store') }}" method="POST">
            @csrf
            <div class="card-body">
                <div class="row">
                    <div class="col-md-2">
                        <div class="form-group">
                            <label for="new_name">System Name *</label>
                            <input type="text"
                                   class="form-control @error('name') is-invalid @enderror"
                                   id="new_name"
                                   name="name"
                                   value="{{ old('name') }}"
                                   placeholder="e.g., custom_fee"
                                   pattern="[a-z_]+"
                                   required>
                            <small class="text-muted">lowercase, underscores only</small>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label for="new_label">Display Label *</label>
                            <input type="text"
                                   class="form-control @error('label') is-invalid @enderror"
                                   id="new_label"
                                   name="label"
                                   value="{{ old('label') }}"
                                   placeholder="e.g., Custom Fee"
                                   required>
                            @error('label')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label for="new_value">Value *</label>
                            <input type="number"
                                   class="form-control @error('value') is-invalid @enderror"
                                   id="new_value"
                                   name="value"
                                   value="{{ old('value', 0) }}"
                                   step="0.0001"
                                   min="0"
                                   required>
                            @error('value')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label for="new_type">Fee Type *</label>
                            <select class="form-control @error('type') is-invalid @enderror"
                                    id="new_type"
                                    name="type"
                                    required>
                                <option value="flat" {{ old('type') == 'flat' ? 'selected' : '' }}>Flat</option>
                                <option value="percentage" {{ old('type') == 'percentage' ? 'selected' : '' }}>Percentage</option>
                            </select>
                            @error('type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label for="new_description">Description</label>
                            <input type="text"
                                   class="form-control"
                                   id="new_description"
                                   name="description"
                                   value="{{ old('description') }}"
                                   placeholder="Optional">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label>&nbsp;</label>
                            <button type="submit" class="btn btn-success btn-block">
                                <i class="fas fa-plus"></i> Add Fee
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <!-- Delete Custom Fees Card -->
    @php
        $customFees = $settings->filter(function($s) {
            return !in_array($s->name, ['platform_fee', 'transaction_fee']);
        });
    @endphp
    @if($customFees->count() > 0)
    <div class="card card-outline card-danger collapsed-card">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-trash-alt mr-2"></i>
                Delete Custom Fees
            </h3>
            <div class="card-tools">
                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                    <i class="fas fa-plus"></i>
                </button>
            </div>
        </div>
        <div class="card-body">
            <div class="alert alert-warning">
                <i class="fas fa-exclamation-triangle mr-2"></i>
                <strong>Warning:</strong> Core fees (Platform Fee, Transaction Fee) cannot be deleted. Only custom fees can be removed.
            </div>
            <div class="row">
                @foreach($customFees as $fee)
                    <div class="col-md-4 col-lg-3 mb-2">
                        <form action="{{ route('admin.fee-settings.destroy', $fee) }}"
                              method="POST"
                              onsubmit="return confirm('Are you sure you want to delete {{ $fee->label }}?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-outline-danger btn-block">
                                <i class="fas fa-trash"></i> Delete {{ $fee->label }}
                            </button>
                        </form>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif
</div>
@stop

@section('css')
<style>
    .badge-lg {
        padding: 0.5em 0.75em;
    }
    .custom-switch .custom-control-label::before {
        width: 2.5rem;
    }
    .custom-switch .custom-control-label::after {
        width: 1rem;
        height: 1rem;
    }
    .custom-switch .custom-control-input:checked ~ .custom-control-label::after {
        transform: translateX(1.25rem);
    }
    .small-box .inner {
        padding: 10px;
    }
    .small-box .inner h4 {
        font-size: 1.5rem;
        margin-bottom: 0;
    }
    .small-box .inner p {
        font-size: 0.85rem;
    }
</style>
@stop

@section('js')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const previewAmount = document.getElementById('preview_amount');
    const feeValues = document.querySelectorAll('.fee-value');
    const feeTypes = document.querySelectorAll('.fee-type');
    const feeActives = document.querySelectorAll('.fee-active');

    function calculatePreview() {
        const amount = parseFloat(previewAmount.value) || 0;
        let platformFee = 0;
        let transactionFee = 0;

        // Get current form values
        feeValues.forEach((input, index) => {
            const feeName = input.dataset.feeName;
            const value = parseFloat(input.value) || 0;
            const typeSelect = document.querySelector(`.fee-type[data-fee-name="${feeName}"]`);
            const activeCheckbox = document.querySelector(`.fee-active[data-fee-name="${feeName}"]`);
            const type = typeSelect ? typeSelect.value : 'flat';
            const isActive = activeCheckbox ? activeCheckbox.checked : true;

            if (!isActive) return;

            let calculatedFee = 0;
            if (type === 'percentage') {
                calculatedFee = amount * (value / 100);
            } else {
                calculatedFee = value;
            }

            if (feeName === 'platform_fee') {
                platformFee = calculatedFee;
            } else if (feeName === 'transaction_fee') {
                transactionFee = calculatedFee;
            } else {
                // Add custom fees to transaction fee for simplicity
                transactionFee += calculatedFee;
            }
        });

        const totalFees = platformFee + transactionFee;
        const netAmount = amount - totalFees;

        // Update display
        document.getElementById('preview_platform_fee').textContent = '$' + platformFee.toFixed(2);
        document.getElementById('preview_transaction_fee').textContent = '$' + transactionFee.toFixed(2);
        document.getElementById('preview_total_fees').textContent = '$' + totalFees.toFixed(2);
        document.getElementById('preview_net_amount').textContent = '$' + netAmount.toFixed(2);

        // Update color based on whether net is positive or negative
        const netBox = document.getElementById('preview_net_amount').closest('.small-box');
        if (netAmount < 0) {
            netBox.classList.remove('bg-success');
            netBox.classList.add('bg-danger');
        } else {
            netBox.classList.remove('bg-danger');
            netBox.classList.add('bg-success');
        }
    }

    // Bind events
    previewAmount.addEventListener('input', calculatePreview);
    feeValues.forEach(input => input.addEventListener('input', calculatePreview));
    feeTypes.forEach(select => select.addEventListener('change', calculatePreview));
    feeActives.forEach(checkbox => checkbox.addEventListener('change', calculatePreview));
});
</script>
@stop
