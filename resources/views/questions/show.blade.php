<x-layouts.app>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">
            {{ $question->title }}
        </h2>
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
                            ✍️ Enviar resposta
                        </a>
                    </div>
                @endauth
            </div>

            <div class="mt-8">
                <h3 class="text-lg font-medium text-gray-900 mb-3">{{ __('Respostes') }}</h3>
                @forelse($question->answers as $answer)
                    <a href="{{ route('answers.show', $answer) }}" class="block bg-white shadow-sm sm:rounded-lg p-4 mb-3 hover:shadow transition">
                        <div class="text-sm text-gray-700 font-semibold">
                            {{ $answer->user->name ?? 'Anònim' }}
                            <span class="text-gray-400">·</span>
                            <x-solution-badge :no-solution="$answer->no_solution" :solution="$answer->solution" />
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
            </div>
        </div>
    </div>
</x-layouts.app>
