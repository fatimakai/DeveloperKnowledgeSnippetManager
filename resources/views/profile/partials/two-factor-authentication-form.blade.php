<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">Two-factor authentication</h2>
        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
            Protect your account with a time-based code from an authenticator app.
        </p>
    </header>

    @if (session('status') && str_starts_with(session('status'), 'two-factor'))
        <p class="mt-4 rounded-md border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700 dark:border-green-900 dark:bg-green-950 dark:text-green-200" role="status">
            {{ match (session('status')) {
                'two-factor-setup-started' => 'Scan the QR code, then confirm setup with a current code.',
                'two-factor-enabled' => 'Two-factor authentication is now enabled.',
                'two-factor-disabled' => 'Two-factor authentication has been disabled.',
                'two-factor-recovery-codes-regenerated' => 'Your recovery codes have been replaced.',
                default => 'Your two-factor settings were updated.',
            } }}
        </p>
    @endif

    @if (session('two_factor_recovery_codes'))
        <div class="mt-6 rounded-lg border border-amber-300 bg-amber-50 p-4 dark:border-amber-800 dark:bg-amber-950">
            <h3 class="font-semibold text-amber-900 dark:text-amber-100">Save these recovery codes now</h3>
            <p class="mt-1 text-sm text-amber-800 dark:text-amber-200">Each code works once. They will not be shown again.</p>
            <div class="mt-4 grid grid-cols-2 gap-2 font-mono text-sm text-amber-950 dark:text-amber-50">
                @foreach (session('two_factor_recovery_codes') as $recoveryCode)
                    <code>{{ $recoveryCode }}</code>
                @endforeach
            </div>
        </div>
    @endif

    @if ($user->twoFactorEnabled())
        <div class="mt-6 flex items-center gap-2 text-sm font-medium text-green-700 dark:text-green-300">
            <span class="h-2.5 w-2.5 rounded-full bg-green-500"></span>
            Enabled since {{ $user->two_factor_confirmed_at->format('M j, Y') }}
        </div>

        <form method="POST" action="{{ route('two-factor.recovery-codes') }}" class="mt-6 space-y-4">
            @csrf
            <div>
                <x-input-label for="recovery_codes_password" :value="__('Current password')" />
                <x-text-input id="recovery_codes_password" name="password" type="password" class="mt-1 block w-full" required autocomplete="current-password" />
                <x-input-error :messages="$errors->twoFactorRecoveryCodes->get('password')" class="mt-2" />
            </div>
            <x-secondary-button type="submit">Generate new recovery codes</x-secondary-button>
        </form>

        <form method="POST" action="{{ route('two-factor.destroy') }}" class="mt-8 space-y-4 border-t border-gray-200 pt-6 dark:border-gray-700">
            @csrf
            @method('DELETE')
            <div>
                <x-input-label for="disable_two_factor_password" :value="__('Current password')" />
                <x-text-input id="disable_two_factor_password" name="password" type="password" class="mt-1 block w-full" required autocomplete="current-password" />
                <x-input-error :messages="$errors->twoFactorDisable->get('password')" class="mt-2" />
            </div>
            <x-danger-button type="submit">Disable two-factor authentication</x-danger-button>
        </form>
    @elseif ($user->two_factor_secret)
        <div class="mt-6 rounded-lg border border-indigo-200 bg-indigo-50 p-5 dark:border-indigo-900 dark:bg-indigo-950">
            <h3 class="font-semibold text-indigo-950 dark:text-indigo-100">Finish authenticator setup</h3>
            <p class="mt-1 text-sm text-indigo-800 dark:text-indigo-200">Scan this QR code with your authenticator app. You can also enter the secret manually.</p>

            @if ($twoFactorQrCode)
                <img src="{{ $twoFactorQrCode }}" alt="Authenticator setup QR code" class="mt-4 h-56 w-56 rounded-md bg-white p-2">
            @endif

            <p class="mt-4 text-xs font-semibold uppercase tracking-wide text-indigo-700 dark:text-indigo-300">Manual setup key</p>
            <code class="mt-1 block break-all rounded bg-white px-3 py-2 text-sm text-gray-900 dark:bg-gray-900 dark:text-gray-100">{{ $user->two_factor_secret }}</code>
        </div>

        <form method="POST" action="{{ route('two-factor.confirm') }}" class="mt-6 space-y-4">
            @csrf
            <div>
                <x-input-label for="two_factor_code" :value="__('Six-digit authentication code')" />
                <x-text-input id="two_factor_code" name="code" type="text" class="mt-1 block w-full" required autocomplete="one-time-code" inputmode="numeric" maxlength="6" />
                <x-input-error :messages="$errors->twoFactorConfirmation->get('code')" class="mt-2" />
            </div>
            <x-primary-button>Confirm and enable</x-primary-button>
        </form>

        <form method="POST" action="{{ route('two-factor.destroy') }}" class="mt-6 space-y-4">
            @csrf
            @method('DELETE')
            <div>
                <x-input-label for="cancel_two_factor_password" :value="__('Current password')" />
                <x-text-input id="cancel_two_factor_password" name="password" type="password" class="mt-1 block w-full" required autocomplete="current-password" />
                <x-input-error :messages="$errors->twoFactorDisable->get('password')" class="mt-2" />
            </div>
            <x-secondary-button type="submit">Cancel setup</x-secondary-button>
        </form>
    @else
        <form method="POST" action="{{ route('two-factor.store') }}" class="mt-6 space-y-4">
            @csrf
            <div>
                <x-input-label for="enable_two_factor_password" :value="__('Current password')" />
                <x-text-input id="enable_two_factor_password" name="password" type="password" class="mt-1 block w-full" required autocomplete="current-password" />
                <x-input-error :messages="$errors->twoFactorSetup->get('password')" class="mt-2" />
            </div>
            <x-primary-button>Set up two-factor authentication</x-primary-button>
        </form>
    @endif
</section>
