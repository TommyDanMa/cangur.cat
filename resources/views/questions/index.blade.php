<x-layouts.app>
    <x-slot:header>
        <h2 class="text-xl font-semibold leading-tight text-gray-800">
            {{ __('Problemes') }}
        </h2>
    </x-slot:header>

    <div class="py-8">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm sm:rounded-lg p-6 space-y-4">
                <form method="GET" action="{{ route('questions.index') }}" class="space-y-3">
                    <div class="flex flex-wrap items-end gap-3" x-data="{ open: true }">
                        <div class="js-react-typeahead"
                             data-name="year"
                             data-endpoint="{{ route('questions.suggest') }}"
                             data-field="year"
                             data-initial="{{ request('year') }}"
                             data-placeholder="Any"
                             data-width-class="w-40"></div>

                        <div class="js-react-typeahead"
                             data-name="level"
                             data-endpoint="{{ route('questions.suggest') }}"
                             data-field="level"
                             data-initial="{{ request('level') }}"
                             data-placeholder="Nivell"
                             data-width-class="w-44"></div>

                        <div class="js-react-typeahead"
                             data-name="phase"
                             data-endpoint="{{ route('questions.suggest') }}"
                             data-field="phase"
                             data-initial="{{ request('phase') }}"
                             data-placeholder="Fase"
                             data-width-class="w-44"></div>

                        <div class="js-react-typeahead"
                             data-name="session"
                             data-endpoint="{{ route('questions.suggest') }}"
                             data-field="session"
                             data-initial="{{ request('session') }}"
                             data-placeholder="Sessió"
                             data-width-class="w-44"></div>

                        <x-button>Cerca</x-button>
                    </div>
                </form>


                @auth
                    @can('teacher')
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <x-button href="{{ route('questions.create') }}">Nou problema</x-button>
                            </div>
                        </div>
                    @endcan
                @endauth

                @foreach($questions as $q)
                    <div class="space-y-2">
                        <x-question-card :$q />
                    </div>
                @endforeach

                {{ $questions->links() }}

            </div>
        </div>
    </div>
</x-layouts.app>
