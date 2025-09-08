<x-guest-layout>
    <div class="mb-4 text-sm text-gray-600">
        {{ __('Gràcies per registrar-te! Abans de començar, podries verificar la teva adreça electrònica fent clic a l\'enllaç que t\'acabem d\'enviar? Si no has rebut el correu electrònic, te n\'enviarem un altre amb molt de gust.') }}
    </div>

    @if (session('status') == 'verification-link-sent')
        <div class="mb-4 font-medium text-sm text-green-600">
            {{ __('S\'ha enviat un nou enllaç de verificació a l\'adreça electrònica que vau proporcionar durant el registre.') }}
        </div>
    @endif

    <div class="mt-4 flex items-center justify-between">
        <form method="POST" action="{{ route('verification.send') }}">
            @csrf

            <div>
                <x-primary-button>
                    {{ __('Torna a enviar el correu de verificació') }}
                </x-primary-button>
            </div>
        </form>

        <form method="POST" action="{{ route('logout') }}">
            @csrf

            <button type="submit" class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                {{ __('Tanca la Sessió') }}
            </button>
        </form>
    </div>
</x-guest-layout>
