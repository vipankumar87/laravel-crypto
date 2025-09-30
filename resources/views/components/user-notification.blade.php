@props(['type' => 'info', 'icon' => null, 'dismissible' => true])

@php
    $typeClasses = [
        'info' => 'alert-info',
        'success' => 'alert-success',
        'warning' => 'alert-warning',
        'danger' => 'alert-danger',
        'primary' => 'alert-primary',
        'secondary' => 'alert-secondary',
        'light' => 'alert-light',
        'dark' => 'alert-dark'
    ];
    
    $icons = [
        'info' => 'fas fa-info-circle',
        'success' => 'fas fa-check-circle',
        'warning' => 'fas fa-exclamation-triangle',
        'danger' => 'fas fa-times-circle',
        'primary' => 'fas fa-bell',
        'secondary' => 'fas fa-comment',
        'light' => 'fas fa-lightbulb',
        'dark' => 'fas fa-moon'
    ];
    
    $alertClass = $typeClasses[$type] ?? 'alert-info';
    $iconClass = $icon ?? $icons[$type] ?? 'fas fa-info-circle';
@endphp

<div {{ $attributes->merge(['class' => "alert $alertClass" . ($dismissible ? ' alert-dismissible fade show' : '')]) }}>
    @if($dismissible)
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    @endif
    
    <div class="d-flex align-items-center">
        <div class="mr-3">
            <i class="{{ $iconClass }} fa-lg"></i>
        </div>
        <div>
            {{ $slot }}
        </div>
    </div>
</div>
