@props([
    'title', 
    'value', 
    'icon' => 'fas fa-chart-bar', 
    'color' => 'primary', 
    'link' => null, 
    'linkText' => 'More info'
])

@php
    $bgColors = [
        'primary' => 'bg-primary',
        'secondary' => 'bg-secondary',
        'success' => 'bg-success',
        'info' => 'bg-info',
        'warning' => 'bg-warning',
        'danger' => 'bg-danger',
        'light' => 'bg-light text-dark',
        'dark' => 'bg-dark'
    ];
    
    $bgClass = $bgColors[$color] ?? 'bg-primary';
@endphp

<div {{ $attributes->merge(['class' => 'small-box ' . $bgClass]) }}>
    <div class="inner">
        <h3>{{ $value }}</h3>
        <p>{{ $title }}</p>
    </div>
    <div class="icon">
        <i class="{{ $icon }}"></i>
    </div>
    @if($link)
        <a href="{{ $link }}" class="small-box-footer">
            {{ $linkText }} <i class="fas fa-arrow-circle-right"></i>
        </a>
    @endif
</div>
