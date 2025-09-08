<x-layouts.app>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">
            {{ __('Problemes') }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm sm:rounded-lg p-6 space-y-4">
                <form method="GET" action="{{ route('questions.index') }}" class="space-y-3">
                    <div class="flex flex-wrap items-end gap-3">
                        <div class="js-react-typeahead"
                             data-name="year"
                             data-endpoint="{{ url('/questions/suggest') }}"
                             data-field="year"
                             data-initial="{{ request('year') }}"
                             data-placeholder="Any"
                             data-width-class="w-40"></div>

                        <div class="js-react-typeahead"
                             data-name="level"
                             data-endpoint="{{ url('/questions/suggest') }}"
                             data-field="level"
                             data-initial="{{ request('level') }}"
                             data-placeholder="Nivell"
                             data-width-class="w-44"></div>

                        <div class="js-react-typeahead"
                             data-name="phase"
                             data-endpoint="{{ url('/questions/suggest') }}"
                             data-field="phase"
                             data-initial="{{ request('phase') }}"
                             data-placeholder="Fase"
                             data-width-class="w-44"></div>

                        <div class="js-react-typeahead"
                             data-name="session"
                             data-endpoint="{{ url('/questions/suggest') }}"
                             data-field="session"
                             data-initial="{{ request('session') }}"
                             data-placeholder="Sessió"
                             data-width-class="w-44"></div>

                        <button type="submit" class="px-4 h-10 bg-indigo-600 text-white rounded-md">Cerca</button>
                    </div>
                </form>

                @auth
                    @if(auth()->user()->role === 'teacher')
                        <p><a href="{{ route('questions.create') }}"> + Nou problema</a></p>
                    @endif
                @endauth

                <ul>
                    @foreach($questions as $q)
                        <li>
                            <a href="{{ route('questions.show',$q) }}">{{ $q->title }}
                            ({{ $q->year }} · {{ $q->level }})
                            </a>
                        </li>
                    @endforeach
                </ul>

                {{ $questions->links() }}
            </div>
        </div>
    </div>
</x-layouts.app>
