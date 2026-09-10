<x-guest-layout>
    <div class="mb-6">
        <h1 class="text-xl font-semibold text-gray-900 dark:text-white">Two-factor authentication</h1>
        <p class="mt-2 text-sm leading-6 text-gray-600 dark:text-gray-300">
            Enter the six-digit code from your authenticator app, or use one of your recovery codes.
        </p>
    </div>

    <form method="POST" action="{{ route('two-factor.challenge.store') }}">
        @csrf

        <div>
            <x-input-label for="code" :value="__('Authentication or recovery code')" />
            <x-text-input id="code" class="mt-1 block w-full" type="text" name="code" :value="old('code')" required autofocus autocomplete="one-time-code" inputmode="text" />
            <x-input-error :messages="$errors->get('code')" class="mt-2" />
        </div>

        <div class="mt-6 flex items-center justify-end">
            <x-primary-button>{{ __('Verify and continue') }}</x-primary-button>
        </div>
    </form>

    <form method="POST" action="{{ route('two-factor.challenge.cancel') }}" class="mt-4 text-center">
        @csrf
        @method('DELETE')
        <button type="submit" class="text-sm text-gray-600 underline hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-100">
            Cancel and return to login
        </button>
    </form>
</x-guest-layout>
