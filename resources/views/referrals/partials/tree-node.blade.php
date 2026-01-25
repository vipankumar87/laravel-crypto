<div class="tree-node level-{{ $node['level'] }}">
    <div class="user-card">
        <div class="d-flex align-items-center">
            <div class="user-avatar mr-3">
                <i class="fas fa-user-circle fa-2x text-primary"></i>
            </div>
            <div class="user-info">
                <h6 class="mb-1">{{ $node['user']->name }}</h6>
                <small class="text-muted">{{ $node['user']->username }} | {{ $node['user']->email }}</small>
                <div class="mt-1">
                    <span class="badge badge-sm badge-info">Level {{ $node['level'] }}</span>
                    <span class="badge badge-sm badge-success">
                        {{ number_format($node['user']->wallet->balance ?? 0, 2) }} DOGE
                    </span>
                </div>
                <div class="mt-2">
                    <small class="d-block">
                        <i class="fas fa-chart-line text-primary"></i>
                        <strong>Investment:</strong> {{ number_format($node['total_investment'] ?? 0, 2) }} DOGE
                        @if(($node['active_investments'] ?? 0) > 0)
                            <span class="badge badge-sm badge-primary ml-1">{{ $node['active_investments'] }} active</span>
                        @endif
                    </small>
                    <small class="d-block mt-1">
                        <i class="fas fa-gift text-warning"></i>
                        <strong>Bonus Earned:</strong>
                        <span class="text-success">{{ number_format($node['referral_bonus_earned'] ?? 0, 2) }} DOGE</span>
                    </small>
                </div>
                <small class="text-muted d-block mt-1">
                    <i class="fas fa-calendar-alt"></i> Joined: {{ $node['user']->created_at->format('M d, Y') }}
                </small>
            </div>
        </div>
    </div>

    @if(count($node['children']) > 0)
        @foreach($node['children'] as $childNode)
            @include('referrals.partials.tree-node', ['node' => $childNode])
        @endforeach
    @endif
</div>