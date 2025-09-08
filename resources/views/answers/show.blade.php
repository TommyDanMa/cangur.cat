<x-layouts.app>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">
            {{ __('Resposta') }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm sm:rounded-lg p-6 space-y-4">
                <p class="text-sm text-gray-700">
                    {{ __('Per') }} <strong>{{ $answer->user->name ?? 'Anònim' }}</strong>
                    · {{ __('Problema') }}:
                    <a class="text-indigo-600 hover:underline" href="{{ route('questions.show', $answer->question) }}">
                        {{ $answer->question->title }}
                    </a>
                </p>

                <p class="text-sm">
                    <strong class="text-gray-700">{{ __('Solució:') }}</strong>
                    <x-solution-badge :no-solution="$answer->no_solution" :solution="$answer->solution" />
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
            </div>
        </div>
    </div>
</x-layouts.app>
