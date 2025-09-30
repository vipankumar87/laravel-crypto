<x-guest-layout>
    <form method="POST" action="{{ route('register') }}">
        @csrf

        @if(isset($referralCode) && $referralCode)
            <input type="hidden" name="referral_code" value="{{ $referralCode }}">
            <div class="mb-4 p-3 bg-green-100 border border-green-400 text-green-700 rounded">
                <small><strong>Referral Code:</strong> {{ $referralCode }} - You're being referred by someone!</small>
            </div>
        @endif

        <!-- Name -->
        <div>
            <x-input-label for="name" :value="__('Full Name')" />
            <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <!-- Username -->
        <div class="mt-4">
            <x-input-label for="username" :value="__('Username')" />
            <x-text-input id="username" class="block mt-1 w-full" type="text" name="username" :value="old('username')" required autocomplete="username" />
            <x-input-error :messages="$errors->get('username')" class="mt-2" />
            <small class="text-gray-600">Only letters, numbers, and underscores allowed. Used for login.</small>
        </div>

        <!-- Email Address -->
        <div class="mt-4">
            <x-input-label for="email" :value="__('Email Address')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autocomplete="email" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
            <small class="text-gray-600">Multiple accounts allowed with same email</small>
        </div>

        <!-- Country -->
        <div class="mt-4">
            <x-input-label for="country" :value="__('Country')" />
            <select id="country" name="country" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                <option value="">Select your country</option>
                <option value="United States" {{ old('country') == 'United States' ? 'selected' : '' }}>United States</option>
                <option value="Canada" {{ old('country') == 'Canada' ? 'selected' : '' }}>Canada</option>
                <option value="United Kingdom" {{ old('country') == 'United Kingdom' ? 'selected' : '' }}>United Kingdom</option>
                <option value="Australia" {{ old('country') == 'Australia' ? 'selected' : '' }}>Australia</option>
                <option value="Germany" {{ old('country') == 'Germany' ? 'selected' : '' }}>Germany</option>
                <option value="France" {{ old('country') == 'France' ? 'selected' : '' }}>France</option>
                <option value="India" {{ old('country') == 'India' ? 'selected' : '' }}>India</option>
                <option value="Japan" {{ old('country') == 'Japan' ? 'selected' : '' }}>Japan</option>
                <option value="China" {{ old('country') == 'China' ? 'selected' : '' }}>China</option>
                <option value="Brazil" {{ old('country') == 'Brazil' ? 'selected' : '' }}>Brazil</option>
                <option value="Mexico" {{ old('country') == 'Mexico' ? 'selected' : '' }}>Mexico</option>
                <option value="Nigeria" {{ old('country') == 'Nigeria' ? 'selected' : '' }}>Nigeria</option>
                <option value="South Africa" {{ old('country') == 'South Africa' ? 'selected' : '' }}>South Africa</option>
                <option value="Pakistan" {{ old('country') == 'Pakistan' ? 'selected' : '' }}>Pakistan</option>
                <option value="Bangladesh" {{ old('country') == 'Bangladesh' ? 'selected' : '' }}>Bangladesh</option>
                <option value="Indonesia" {{ old('country') == 'Indonesia' ? 'selected' : '' }}>Indonesia</option>
                <option value="Philippines" {{ old('country') == 'Philippines' ? 'selected' : '' }}>Philippines</option>
                <option value="Other" {{ old('country') == 'Other' ? 'selected' : '' }}>Other</option>
            </select>
            <x-input-error :messages="$errors->get('country')" class="mt-2" />
        </div>

        <!-- BEP Wallet Address -->
        <div class="mt-4">
            <x-input-label for="bep_wallet_address" :value="__('BEP-20 Wallet Address (Optional)')" />
            <x-text-input id="bep_wallet_address" class="block mt-1 w-full" type="text" name="bep_wallet_address" :value="old('bep_wallet_address')" autocomplete="off" />
            <x-input-error :messages="$errors->get('bep_wallet_address')" class="mt-2" />
            <small class="text-gray-600">For receiving withdrawals (BEP-20 compatible address)</small>
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-input-label for="password" :value="__('Password')" />

            <x-text-input id="password" class="block mt-1 w-full"
                            type="password"
                            name="password"
                            required autocomplete="new-password" />

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
            <div class="mt-1 text-sm text-gray-600">
                Password must be at least 12 characters and include:
                <ul class="list-disc ml-5 mt-1">
                    <li>Uppercase and lowercase letters</li>
                    <li>At least one number</li>
                    <li>At least one symbol (e.g., !@#$%^&*)</li>
                </ul>
            </div>
        </div>

        <!-- Confirm Password -->
        <div class="mt-4">
            <x-input-label for="password_confirmation" :value="__('Confirm Password')" />

            <x-text-input id="password_confirmation" class="block mt-1 w-full"
                            type="password"
                            name="password_confirmation" required autocomplete="new-password" />

            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="flex items-center justify-end mt-4">
            <a class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" href="{{ route('login') }}">
                {{ __('Already registered?') }}
            </a>

            <x-primary-button class="ms-4">
                {{ __('Register') }}
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
