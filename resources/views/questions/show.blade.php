<x-layouts.app>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                {{ $question->title }}
            </h2>
            @auth
                <div class="flex items-center gap-2">
                    @can('update', $question)
                        <a href="{{ route('questions.edit', $question) }}" class="inline-flex items-center px-3 py-1.5 bg-indigo-600 text-white rounded hover:bg-indigo-700">✏️ {{ __('Edita') }}</a>
                    @endcan
                    @can('delete', $question)
                        <form method="POST" action="{{ route('questions.destroy', $question) }}" onsubmit="return confirm('Segur que vols esborrar el problema i totes les seves respostes?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="inline-flex items-center px-3 py-1.5 bg-red-600 text-white rounded hover:bg-red-700">🗑️ {{ __('Elimina') }}</button>
                        </form>
                    @endcan
                </div>
            @endauth
        </div>
    </x-slot>

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
                        <a  href="{{ route('answers.create', $question) }}"
                            class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">
                            Enviar resposta
                        </a>
                    </div>
                @endauth

                @guest
                    <div>
                        <a href="{{ route('answers.create', $question) }}"
                           class="inline-flex items-center px-4 py-2 bg-indigo-50 text-indigo-700 border border-indigo-200 rounded-md hover:bg-indigo-100">
                            Inicia sessió per respondre el problema
                        </a>
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
                        <a href="{{ route('answers.show', $answer) }}" class="block bg-white shadow-sm sm:rounded-lg p-4 mb-3 hover:shadow transition">
                            <div class="text-sm text-gray-700 font-semibold">
                                {{ $answer->user->name ?? 'Anònim' }}
                                <span class="text-gray-400">·</span>
                                <x-solution-badge :no-solution="$answer->no_solution" :solution="$answer->solution" />
                                <span class="text-gray-400">·</span>
                                <span class="text-xs text-gray-500">{{ optional($answer->created_at)->format('d/m/Y H:i') }}</span>
                                @if($answer->updated_at && $answer->updated_at->gt($answer->created_at) && optional($answer->user)->role !== 'teacher')
                                    <span class="ml-2 text-xs text-gray-500">{{ __('Editada') }}</span>
                                @endif
                            </div>
                            <div class="text-sm text-gray-600 mt-1">
                                @php
                                    $snippet = '';
                                    if ($answer->format === 'text') {
                                        $snippet = \Illuminate\Support\Str::limit((string) $answer->content_text, 120);
                                    } elseif ($answer->format === 'video') {
                                        $snippet = 'Vídeo: '.$answer->video_url;
                                    } elseif ($answer->format === 'image') {
                                        $snippet = 'Resposta en imatge';
                                    }
                                @endphp
                                {{ $snippet }}
                            </div>
                        </a>
                    @empty
                        <p class="text-gray-500">{{ __('Encara no hi ha respostes.') }}</p>
                    @endforelse
                @endif
            </div>
        </div>
    </div>
</x-layouts.app>
