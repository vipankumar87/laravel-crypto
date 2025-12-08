@props([
    'href' => '#',
    'color' => 'primary',
    'icon' => null,
    'size' => 'md',
    'block' => false,
    'disabled' => false,
    'onclick' => null
])

@php
    $btnClass = "btn btn-$color btn-$size";
    if ($block) $btnClass .= ' btn-block';
    
    $attributes = $attributes->merge([
        'class' => $btnClass,
        'href' => $disabled ? 'javascript:void(0);' : $href,
    ]);
    
    if ($disabled) {
        $attributes = $attributes->merge(['disabled' => 'disabled']);
        $attributes = $attributes->merge(['class' => $attributes->get('class') . ' disabled']);
    }
    
    if ($onclick && !$disabled) {
        $attributes = $attributes->merge(['onclick' => $onclick]);
    }
@endphp

<a {{ $attributes }}>
    @if($icon)
        <i class="{{ $icon }} mr-1"></i>
    @endif
    {{ $slot }}
</a>
