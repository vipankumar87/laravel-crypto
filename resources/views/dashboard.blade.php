<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-medium mb-4">{{ __('Welcome back') }}, {{ $user->name }}!</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                        <div class="bg-blue-50 p-4 rounded-lg shadow">
                            <h4 class="font-bold text-blue-800">Wallet Balance</h4>
                            <p class="text-2xl">{{ number_format($wallet->balance ?? 0, 2) }} USDT</p>
                        </div>
                        
                        <div class="bg-green-50 p-4 rounded-lg shadow">
                            <h4 class="font-bold text-green-800">Active Investments</h4>
                            <p class="text-2xl">{{ $activeInvestments ?? 0 }}</p>
                        </div>
                        
                        <div class="bg-purple-50 p-4 rounded-lg shadow">
                            <h4 class="font-bold text-purple-800">Total Earnings</h4>
                            <p class="text-2xl">{{ number_format($totalEarnings ?? 0, 2) }} USDT</p>
                        </div>
                    </div>
                    
                    <div class="mb-6">
                        <h4 class="font-bold mb-2">Your Referral Link</h4>
                        <div class="flex items-center">
                            <input type="text" readonly value="{{ $user->getReferralUrl() ?? 'Invest to activate your referral link' }}" 
                                class="flex-grow p-2 border rounded-l bg-gray-50" />
                            <button onclick="copyToClipboard()" class="bg-blue-500 text-white px-4 py-2 rounded-r hover:bg-blue-600">
                                Copy
                            </button>
                        </div>
                        <p class="text-sm text-gray-600 mt-1">Referrals: {{ $referralCount ?? 0 }}</p>
                    </div>
                    
                    @if(count($recentTransactions ?? []))
                    <div>
                        <h4 class="font-bold mb-2">Recent Transactions</h4>
                        <div class="overflow-x-auto">
                            <table class="min-w-full bg-white">
                                <thead class="bg-gray-100">
                                    <tr>
                                        <th class="py-2 px-4 text-left">Type</th>
                                        <th class="py-2 px-4 text-left">Amount</th>
                                        <th class="py-2 px-4 text-left">Status</th>
                                        <th class="py-2 px-4 text-left">Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($recentTransactions as $transaction)
                                    <tr class="border-t">
                                        <td class="py-2 px-4">{{ ucfirst($transaction->type) }}</td>
                                        <td class="py-2 px-4">{{ number_format($transaction->amount, 2) }} USDT</td>
                                        <td class="py-2 px-4">
                                            <span class="px-2 py-1 rounded text-xs
                                                {{ $transaction->status == 'completed' ? 'bg-green-100 text-green-800' : '' }}
                                                {{ $transaction->status == 'pending' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                                {{ $transaction->status == 'failed' ? 'bg-red-100 text-red-800' : '' }}
                                            ">
                                                {{ ucfirst($transaction->status) }}
                                            </span>
                                        </td>
                                        <td class="py-2 px-4">{{ $transaction->created_at->format('M d, Y') }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    @endif
                    
                    <script>
                    function copyToClipboard() {
                        const referralLink = document.querySelector('input[readonly]');
                        referralLink.select();
                        document.execCommand('copy');
                        alert('Referral link copied to clipboard!');
                    }
                    </script>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
