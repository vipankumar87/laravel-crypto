@extends('adminlte::page')

@section('title', 'Referral Level Settings')

@section('content_header')
    <h1>Referral Level Settings</h1>
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
        <div class="col-lg-4 col-6">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>{{ $stats['total_levels'] }}</h3>
                    <p>Total Levels</p>
                </div>
                <div class="icon">
                    <i class="fas fa-layer-group"></i>
                </div>
            </div>
        </div>
        <div class="col-lg-4 col-6">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>{{ $stats['active_levels'] }}</h3>
                    <p>Active Levels</p>
                </div>
                <div class="icon">
                    <i class="fas fa-check-circle"></i>
                </div>
            </div>
        </div>
        <div class="col-lg-4 col-6">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3>{{ number_format($stats['total_percentage'], 2) }}%</h3>
                    <p>Total Commission</p>
                </div>
                <div class="icon">
                    <i class="fas fa-percentage"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Level Settings Card -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-sitemap mr-2"></i>
                Configure Referral Levels & Bonuses
            </h3>
        </div>
        <form action="{{ route('admin.referral-settings.update') }}" method="POST">
            @csrf
            @method('PUT')
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead class="bg-primary">
                            <tr>
                                <th style="width: 80px;">Level</th>
                                <th style="width: 180px;">Bonus Percentage</th>
                                <th>Description</th>
                                <th style="width: 100px;">Active</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($levels as $level)
                                <tr>
                                    <td>
                                        <input type="hidden" name="levels[{{ $loop->index }}][id]" value="{{ $level->id }}">
                                        <span class="badge badge-primary badge-lg" style="font-size: 1.1em;">
                                            Level {{ $level->level }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="input-group">
                                            <input type="number"
                                                   class="form-control @error('levels.'.$loop->index.'.percentage') is-invalid @enderror"
                                                   name="levels[{{ $loop->index }}][percentage]"
                                                   value="{{ old('levels.'.$loop->index.'.percentage', $level->percentage) }}"
                                                   step="0.01"
                                                   min="0"
                                                   max="100"
                                                   required>
                                            <div class="input-group-append">
                                                <span class="input-group-text">%</span>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <input type="text"
                                               class="form-control"
                                               name="levels[{{ $loop->index }}][description]"
                                               value="{{ old('levels.'.$loop->index.'.description', $level->description) }}"
                                               placeholder="e.g., Direct Referral Bonus">
                                    </td>
                                    <td class="text-center">
                                        <div class="custom-control custom-switch">
                                            <input type="checkbox"
                                                   class="custom-control-input"
                                                   id="active_{{ $level->id }}"
                                                   name="levels[{{ $loop->index }}][is_active]"
                                                   value="1"
                                                   {{ $level->is_active ? 'checked' : '' }}>
                                            <label class="custom-control-label" for="active_{{ $level->id }}"></label>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted">
                                        No referral levels configured. Add your first level below.
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

    <!-- Add New Level Card -->
    <div class="card card-outline card-success">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-plus-circle mr-2"></i>
                Add New Referral Level
            </h3>
        </div>
        <form action="{{ route('admin.referral-settings.store') }}" method="POST">
            @csrf
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="new_level">Level Number *</label>
                            <input type="number"
                                   class="form-control @error('level') is-invalid @enderror"
                                   id="new_level"
                                   name="level"
                                   value="{{ old('level', $levels->max('level') + 1) }}"
                                   min="1"
                                   required>
                            @error('level')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="new_percentage">Bonus Percentage *</label>
                            <div class="input-group">
                                <input type="number"
                                       class="form-control @error('percentage') is-invalid @enderror"
                                       id="new_percentage"
                                       name="percentage"
                                       value="{{ old('percentage', 0.5) }}"
                                       step="0.01"
                                       min="0"
                                       max="100"
                                       required>
                                <div class="input-group-append">
                                    <span class="input-group-text">%</span>
                                </div>
                            </div>
                            @error('percentage')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="new_description">Description</label>
                            <input type="text"
                                   class="form-control"
                                   id="new_description"
                                   name="description"
                                   value="{{ old('description') }}"
                                   placeholder="e.g., Level 6 Bonus">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label>&nbsp;</label>
                            <button type="submit" class="btn btn-success btn-block">
                                <i class="fas fa-plus"></i> Add Level
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <!-- Delete Levels Card -->
    @if($levels->count() > 0)
    <div class="card card-outline card-danger collapsed-card">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-trash-alt mr-2"></i>
                Delete Referral Levels
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
                <strong>Warning:</strong> Deleting a level will not affect existing bonus records, but new bonuses will not be distributed for deleted levels.
            </div>
            <div class="row">
                @foreach($levels as $level)
                    <div class="col-md-4 col-lg-3 mb-2">
                        <form action="{{ route('admin.referral-settings.destroy', $level) }}"
                              method="POST"
                              onsubmit="return confirm('Are you sure you want to delete Level {{ $level->level }}?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-outline-danger btn-block">
                                <i class="fas fa-trash"></i> Delete Level {{ $level->level }}
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
