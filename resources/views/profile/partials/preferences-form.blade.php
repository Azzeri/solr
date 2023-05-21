<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900">
            {{ __('Your preferences') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600">
            {{ __("Tell us what you're into, so we can personalize your search results.") }}
        </p>
    </header>

    <form method="post" action="{{ route('preferences.update') }}" class="mt-6 space-y-6">
        @csrf
        @method('put')

        <div>
            <x-input-label for="preferences" :value="__('Preferences')" />
            <x-text-input id="preferences" name="preferences" type="text" class="mt-1 block w-full" :value="Auth::user()->interests"
                required autofocus autocomplete="preferences" />
            <x-input-error class="mt-2" :messages="$errors->get('preferences')" />
        </div>

        <div class="flex items-center gap-4">
            <x-primary-button>{{ __('Save') }}</x-primary-button>

            @if (session('status') === 'success')
                <p x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 2000)"
                    class="text-sm text-gray-600">{{ __('Saved.') }}</p>
            @endif
        </div>
    </form>
</section>
