@props([
    'title', 
    'tools' => null, 
    'type' => 'primary', 
    'outline' => false,
    'collapsible' => false,
    'collapsed' => false,
    'removable' => false,
    'maximizable' => false,
    'footer' => null,
    'bodyClass' => ''
])

@php
    $cardClass = $outline ? "card-outline card-$type" : "card-$type";
    $cardId = 'card_' . uniqid();
@endphp

<div {{ $attributes->merge(['class' => "card $cardClass"]) }}>
    <div class="card-header">
        <h3 class="card-title">{{ $title }}</h3>

        <div class="card-tools">
            @if($tools)
                {{ $tools }}
            @endif
            
            @if($maximizable)
                <button type="button" class="btn btn-tool" data-card-widget="maximize">
                    <i class="fas fa-expand"></i>
                </button>
            @endif
            
            @if($collapsible)
                <button type="button" class="btn btn-tool" data-card-widget="collapse" data-toggle="tooltip" title="Collapse">
                    <i class="fas {{ $collapsed ? 'fa-plus' : 'fa-minus' }}"></i>
                </button>
            @endif
            
            @if($removable)
                <button type="button" class="btn btn-tool" data-card-widget="remove" data-toggle="tooltip" title="Remove">
                    <i class="fas fa-times"></i>
                </button>
            @endif
        </div>
    </div>
    
    <div class="card-body {{ $bodyClass }} {{ $collapsed ? 'collapse' : '' }}">
        {{ $slot }}
    </div>
    
    @if($footer)
        <div class="card-footer">
            {{ $footer }}
        </div>
    @endif
</div>
