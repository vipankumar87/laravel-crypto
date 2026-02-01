@extends('adminlte::page')

@section('title', 'Withdrawal Settings')

@section('content_header')
    <h1>Withdrawal Settings</h1>
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

    <!-- Stats Cards -->
    <div class="row">
        @php
            $autoApprove = $settings->firstWhere('name', 'auto_approve_enabled');
            $autoThreshold = $settings->firstWhere('name', 'auto_approve_threshold');
            $minUsdt = $settings->firstWhere('name', 'min_usdt_threshold');
        @endphp
        <div class="col-md-3">
            <div class="info-box">
                <span class="info-box-icon bg-{{ $autoApprove && $autoApprove->value ? 'success' : 'secondary' }}">
                    <i class="fas fa-robot"></i>
                </span>
                <div class="info-box-content">
                    <span class="info-box-text">Auto Approve</span>
                    <span class="info-box-number">{{ $autoApprove && $autoApprove->value ? 'Enabled' : 'Disabled' }}</span>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="info-box">
                <span class="info-box-icon bg-info"><i class="fas fa-sliders-h"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Auto Approve Threshold</span>
                    <span class="info-box-number">${{ number_format($autoThreshold->value ?? 100, 2) }}</span>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="info-box">
                <span class="info-box-icon bg-warning"><i class="fas fa-dollar-sign"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Min USDT Threshold</span>
                    <span class="info-box-number">${{ number_format($minUsdt->value ?? 50, 2) }}</span>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="info-box">
                <span class="info-box-icon bg-danger"><i class="fas fa-clock"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Pending Withdrawals</span>
                    <span class="info-box-number">{{ $pendingCount }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Settings Card -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-cog mr-2"></i>
                Configure Withdrawal Settings
            </h3>
        </div>
        <form action="{{ route('admin.withdrawal-settings.update') }}" method="POST">
            @csrf
            @method('PUT')
            <div class="card-body">
                <div class="alert alert-info">
                    <i class="fas fa-info-circle mr-2"></i>
                    <strong>How It Works:</strong> These settings control withdrawal limits, auto-approval, fees, and DOGE bonus awards for all users.
                </div>
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead class="bg-primary">
                            <tr>
                                <th style="width: 200px;">Setting</th>
                                <th style="width: 200px;">Value</th>
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
                                        @if($setting->type === 'boolean')
                                            <select class="form-control"
                                                    name="settings[{{ $loop->index }}][value]">
                                                <option value="0" {{ $setting->value == '0' ? 'selected' : '' }}>No / Disabled</option>
                                                <option value="1" {{ $setting->value == '1' ? 'selected' : '' }}>Yes / Enabled</option>
                                            </select>
                                        @elseif($setting->name === 'withdrawal_fee_type')
                                            <select class="form-control"
                                                    name="settings[{{ $loop->index }}][value]">
                                                <option value="flat" {{ $setting->value === 'flat' ? 'selected' : '' }}>Flat (Fixed Amount)</option>
                                                <option value="percentage" {{ $setting->value === 'percentage' ? 'selected' : '' }}>Percentage (%)</option>
                                            </select>
                                        @else
                                            <input type="number"
                                                   class="form-control"
                                                   name="settings[{{ $loop->index }}][value]"
                                                   value="{{ old('settings.'.$loop->index.'.value', $setting->value) }}"
                                                   step="0.01"
                                                   min="0">
                                        @endif
                                    </td>
                                    <td>
                                        <input type="text"
                                               class="form-control"
                                               name="settings[{{ $loop->index }}][description]"
                                               value="{{ old('settings.'.$loop->index.'.description', $setting->description) }}"
                                               placeholder="Setting description">
                                    </td>
                                    <td class="text-center">
                                        <div class="custom-control custom-switch">
                                            <input type="checkbox"
                                                   class="custom-control-input"
                                                   id="active_{{ $setting->id }}"
                                                   name="settings[{{ $loop->index }}][is_active]"
                                                   value="1"
                                                   {{ $setting->is_active ? 'checked' : '' }}>
                                            <label class="custom-control-label" for="active_{{ $setting->id }}"></label>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted">
                                        No withdrawal settings configured.
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

    <!-- Add New Setting Card -->
    <div class="card card-outline card-success collapsed-card">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-plus-circle mr-2"></i>
                Add Custom Setting (Advanced)
            </h3>
            <div class="card-tools">
                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                    <i class="fas fa-plus"></i>
                </button>
            </div>
        </div>
        <form action="{{ route('admin.withdrawal-settings.store') }}" method="POST">
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
                                   placeholder="e.g., custom_setting"
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
                                   placeholder="e.g., Custom Setting"
                                   required>
                            @error('label')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label for="new_value">Value *</label>
                            <input type="text"
                                   class="form-control @error('value') is-invalid @enderror"
                                   id="new_value"
                                   name="value"
                                   value="{{ old('value', '0') }}"
                                   required>
                            @error('value')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label for="new_type">Value Type *</label>
                            <select class="form-control @error('type') is-invalid @enderror"
                                    id="new_type"
                                    name="type"
                                    required>
                                <option value="number" {{ old('type') == 'number' ? 'selected' : '' }}>Number</option>
                                <option value="boolean" {{ old('type') == 'boolean' ? 'selected' : '' }}>Boolean</option>
                                <option value="string" {{ old('type') == 'string' ? 'selected' : '' }}>String</option>
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
                                <i class="fas fa-plus"></i> Add Setting
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <!-- Delete Custom Settings Card -->
    @php
        $coreNames = \App\Models\WithdrawalSetting::CORE_SETTINGS;
        $customSettings = $settings->filter(function($s) use ($coreNames) {
            return !in_array($s->name, $coreNames);
        });
    @endphp
    @if($customSettings->count() > 0)
    <div class="card card-outline card-danger collapsed-card">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-trash-alt mr-2"></i>
                Delete Custom Settings
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
                <strong>Warning:</strong> Core settings cannot be deleted. Only custom settings can be removed.
            </div>
            <div class="row">
                @foreach($customSettings as $customSetting)
                    <div class="col-md-4 col-lg-3 mb-2">
                        <form action="{{ route('admin.withdrawal-settings.destroy', $customSetting) }}"
                              method="POST"
                              onsubmit="return confirm('Are you sure you want to delete {{ $customSetting->label }}?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-outline-danger btn-block">
                                <i class="fas fa-trash"></i> Delete {{ $customSetting->label }}
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
</style>
@stop
