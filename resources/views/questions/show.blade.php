<x-layouts.app>
    <x-slot:header>
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                {{ $question->title }}
            </h2>
            @auth
                <div class="flex items-center gap-2">
                    @can('teacher')
                        <x-button href="{{ route('questions.edit', $question) }}">✏️ {{ __('Edita') }}</x-button>

                        <form method="POST" action="{{ route('questions.destroy', $question) }}" onsubmit="return confirm('Segur que vols esborrar el problema i totes les seves respostes?');">
                            @csrf
                            @method('DELETE')
                            <x-button variant="danger">🗑️ {{ __('Elimina') }}</x-button>
                        </form>
                    @endcan
                </div>
            @endauth
        </div>
    </x-slot:header>

    <div class="py-8">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm sm:rounded-lg p-6 space-y-4">
                <p class="text-sm text-gray-600">
                    <strong>Any:</strong> {{ $question->year }} · <strong>Nivell:</strong> {{ $question->level }}
                </p>
                <p class="text-gray-800">{{ $question->statement }}</p>
                @if ($question->image_path)
                    <img src="{{ asset('storage/'.$question->image_path) }}" alt="Imatge del problema" class="rounded max-w-full h-auto">
                @endif

                @auth
                    <div>
                        <x-button href="{{ route('answers.create', $question) }}">Enviar resposta</x-button>
                    </div>
                @endauth

                @guest
                    <div>
                        <x-button href="{{ route('answers.create', $question) }}" variant="secondary">Inicia sessió per respondre el problema</x-button>
                    </div>
                @endguest
            </div>

            <div class="mt-8">
                <h3 class="text-lg font-medium text-gray-900 mb-3">{{ __('Respostes') }}</h3>

                @if(!$canSeeAnswers)
                    @if($question->answers_count > 0)
                        <div class="bg-yellow-50 text-yellow-800 border border-yellow-200 rounded p-3">
                            {{ __('Publica la teva resposta per veure les dels altres.') }}
                            <span class="text-gray-600">({{ $question->answers_count }} {{ __('respostes ocultes') }})</span>
                        </div>
                    @else
                        <p class="text-gray-500">{{ __('Encara no hi ha respostes.') }}</p>
                    @endif
                @else
                    @forelse($answers as $answer)
                        <x-answer-card :$answer />
                    @empty
                        <p class="text-gray-500">{{ __('Encara no hi ha respostes.') }}</p>
                    @endforelse
                @endif
            </div>
        </div>
    </div>
</x-layouts.app>
