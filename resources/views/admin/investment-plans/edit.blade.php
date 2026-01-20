@extends('adminlte::page')

@section('title', 'Edit Investment Plan')

@section('content_header')
    <h1>Edit Investment Plan: {{ $plan->name ?? 'Plan' }}</h1>
@stop

@section('content')
<div class="container-fluid">
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
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

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Plan Details</h3>
        </div>
        <form action="{{ route('admin.investment-plans.update', $plan ?? 1) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="name">Plan Name *</label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror"
                                   id="name" name="name" value="{{ old('name', $plan->name ?? '') }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="status">Status *</label>
                            <select class="form-control @error('status') is-invalid @enderror" id="status" name="status" required>
                                <option value="active" {{ old('status', $plan->status ?? '') === 'active' ? 'selected' : '' }}>Active</option>
                                <option value="inactive" {{ old('status', $plan->status ?? '') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                            </select>
                            @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="min_amount">Minimum Amount ($) *</label>
                            <input type="number" class="form-control @error('min_amount') is-invalid @enderror"
                                   id="min_amount" name="min_amount" value="{{ old('min_amount', $plan->min_amount ?? '') }}"
                                   step="0.01" min="0.01" required>
                            @error('min_amount')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="max_amount">Maximum Amount ($) *</label>
                            <input type="number" class="form-control @error('max_amount') is-invalid @enderror"
                                   id="max_amount" name="max_amount" value="{{ old('max_amount', $plan->max_amount ?? '') }}"
                                   step="0.01" min="0.01" required>
                            @error('max_amount')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="daily_return_rate">Daily Return Rate (%) *</label>
                            <input type="number" class="form-control @error('daily_return_rate') is-invalid @enderror"
                                   id="daily_return_rate" name="daily_return_rate" value="{{ old('daily_return_rate', $plan->daily_return_rate ?? '') }}"
                                   step="0.01" min="0.01" max="100" required>
                            @error('daily_return_rate')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="total_return_rate">Total Return Rate (%) *</label>
                            <input type="number" class="form-control @error('total_return_rate') is-invalid @enderror"
                                   id="total_return_rate" name="total_return_rate" value="{{ old('total_return_rate', $plan->total_return_rate ?? '') }}"
                                   step="0.01" min="0.01" max="1000" required>
                            @error('total_return_rate')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="duration_days">Duration (Days) *</label>
                            <input type="number" class="form-control @error('duration_days') is-invalid @enderror"
                                   id="duration_days" name="duration_days" value="{{ old('duration_days', $plan->duration_days ?? '') }}"
                                   min="1" max="3650" required>
                            @error('duration_days')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="max_investors">Max Investors (0 = unlimited)</label>
                            <input type="number" class="form-control @error('max_investors') is-invalid @enderror"
                                   id="max_investors" name="max_investors" value="{{ old('max_investors', $plan->max_investors ?? 0) }}"
                                   min="0">
                            @error('max_investors')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea class="form-control @error('description') is-invalid @enderror"
                              id="description" name="description" rows="4">{{ old('description', $plan->description ?? '') }}</textarea>
                    @error('description')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            <div class="card-footer">
                <button type="submit" class="btn btn-warning">
                    <i class="fas fa-save"></i> Update Plan
                </button>
                <a href="{{ route('admin.investment-plans.show', $plan ?? 1) }}" class="btn btn-info">
                    <i class="fas fa-eye"></i> View Plan
                </a>
                <a href="{{ route('admin.investment-plans.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Cancel
                </a>
            </div>
        </form>
    </div>
</div>
@stop