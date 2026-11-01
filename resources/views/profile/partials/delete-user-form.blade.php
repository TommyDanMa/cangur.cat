<section class="space-y-6">
    <header>
        <h2 class="text-lg font-medium text-gray-900">
            {{ __('Elimina el teu compte') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600">
            {{ __('Un cop suprimit el vostre compte, tots els seus recursos i dades s\'eliminaran permanentment. Abans de suprimir el vostre compte, baixeu les dades o la informació que vulgueu conservar.') }}
        </p>
    </header>

    <x-button variant="danger"
        x-data=""
        x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')"
    >{{ __('Elimina el compte') }}</x-button>

    <x-modal name="confirm-user-deletion" :show="$errors->userDeletion->isNotEmpty()" focusable>
        <form method="post" action="{{ route('profile.destroy') }}" class="p-6">
            @csrf
            @method('delete')

            <h2 class="text-lg font-medium text-gray-900">
                {{ __('Esteu segur que voleu eliminar el vostre compte?') }}
            </h2>

            <p class="mt-1 text-sm text-gray-600">
                {{ __('Un cop s\'hagi eliminat el vostre compte, tots els seus recursos i dades s\'eliminaran permanentment. Introduïu la vostra contrasenya per confirmar que voleu eliminar el vostre compte permanentment.') }}
            </p>

            <div class="mt-6">
                <x-input-label for="password" value="{{ __('Contrasenya') }}" class="sr-only" />

                <x-text-input
                    id="password"
                    name="password"
                    type="password"
                    class="mt-1 block w-3/4"
                    placeholder="{{ __('Contrasenya') }}"
                />

                <x-input-error :messages="$errors->userDeletion->get('password')" class="mt-2" />
            </div>

            <div class="mt-6 flex justify-end">
                <x-button variant="secondary" type="button" x-on:click="$dispatch('close')">
                    {{ __('Cancel·la') }}
                </x-button>

                <x-button variant="danger" class="ms-3">
                    {{ __('Elimina el compte') }}
                </x-button>
            </div>
        </form>
    </x-modal>
</section>
