<x-guest-layout>
    <div class="mb-4 text-sm text-gray-600">
        {{ __('Has oblidat la contrasenya? Cap problema. Només cal que ens facis saber la teva adreça electrònica i t\'enviarem un enllaç per restablir la contrasenya que et permetrà triar-ne una de nova.') }}
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('password.email') }}">
        @csrf

        <!-- Email Address -->
        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autofocus />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div class="flex items-center justify-end mt-4">
            <x-primary-button>
                {{ __('Enllaç de restabliment de contrasenya del correu electrònic') }}
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
