@props(['type' => null, 'status' => null])

@if($type)
    @php
        $typeClasses = [
            'deposit' => 'badge-success',
            'withdrawal' => 'badge-warning',
            'investment' => 'badge-info',
            'return' => 'badge-primary',
            'referral_bonus' => 'badge-secondary',
            'default' => 'badge-light'
        ];
        
        $typeClass = $typeClasses[$type] ?? $typeClasses['default'];
        $displayType = ucfirst(str_replace('_', ' ', $type));
    @endphp
    
    <span class="badge {{ $typeClass }}">{{ $displayType }}</span>
@endif

@if($status)
    @php
        $statusClasses = [
            'completed' => 'badge-success',
            'pending' => 'badge-warning',
            'failed' => 'badge-danger',
            'processing' => 'badge-info',
            'default' => 'badge-secondary'
        ];
        
        $statusClass = $statusClasses[$status] ?? $statusClasses['default'];
        $displayStatus = ucfirst($status);
    @endphp
    
    <span class="badge {{ $statusClass }}">{{ $displayStatus }}</span>
@endif
