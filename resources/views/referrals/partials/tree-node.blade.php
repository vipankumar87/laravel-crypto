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
                        ${{ number_format($node['user']->wallet->balance ?? 0, 2) }}
                    </span>
                </div>
                <small class="text-muted d-block">
                    Joined: {{ $node['user']->created_at->format('M d, Y') }}
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