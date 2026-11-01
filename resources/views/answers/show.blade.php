<x-layouts.app>
    <x-slot:header>
        <h2 class="text-xl font-semibold leading-tight text-gray-800">
            {{ __('Resposta') }}
        </h2>
    </x-slot:header>

    <div class="py-8">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            @if (session('ok'))
                <div class="mb-4 rounded border border-green-200 bg-green-50 text-green-800 p-3">{{ session('ok') }}</div>
            @endif
            <div class="bg-white shadow-sm sm:rounded-lg p-6 space-y-4">
                <p class="text-sm text-gray-700">
                    {{ __('Per') }} <strong>{{ $answer->user->name ?? 'Anònim' }}</strong>
                    · {{ __('Problema') }}:
                    <a class="text-indigo-600 hover:underline" href="{{ route('questions.show', $answer->question) }}">
                        {{ $answer->question->title }}
                    </a>
                    · <span class="text-gray-500">{{ optional($answer->created_at)->format('d/m/Y H:i') }}</span>
                </p>

                <p class="text-sm">
                    <strong class="text-gray-700">{{ __('Solució:') }}</strong>
                    <x-solution-badge :no-solution="$answer->no_solution" :solution="$answer->solution" />
                    @if($answer->updated_at && $answer->updated_at->gt($answer->created_at) && optional($answer->user)->role !== 'teacher')
                        <span class="text-xs text-gray-500 ml-2">— {{ __('Editada') }}</span>
                    @endif
                </p>

                @if ($answer->format === 'text' && $answer->content_text)
                    <div>
                        <h3 class="font-medium text-gray-900 mb-1">{{ __('Explicació') }}</h3>
                        <div class="prose max-w-none">{!! nl2br(e($answer->content_text)) !!}</div>
                    </div>
                @endif

                @if ($answer->format === 'video' && $answer->video_url)
                    <p>Vídeo: <a class="text-indigo-600 hover:underline" href="{{ $answer->video_url }}" target="_blank" rel="noopener">Obrir</a></p>
                @endif

                @if ($answer->format === 'image' && $answer->image_path)
                    <img src="{{ asset('storage/'.$answer->image_path) }}" alt="Resposta en imatge" class="rounded max-w-full h-auto">
                @endif

                @auth
                    <div class="pt-4 flex items-center gap-3">
                        @can('update', $answer)
                            <x-button href="{{ route('answers.edit', $answer) }}">{{ __('Edita') }}</x-button>
                        @endcan
                        @can('delete', $answer)
                            <form method="POST" action="{{ route('answers.destroy', $answer) }}" onsubmit="return confirm('Segur que vols esborrar la resposta?');">
                                @csrf
                                @method('DELETE')
                                <x-button variant="danger">{{ __('Elimina') }}</x-button>
                            </form>
                        @endcan
                    </div>
                @endauth
            </div>
        </div>
    </div>
</x-layouts.app>
