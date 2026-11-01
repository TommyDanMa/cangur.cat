@props(['answer'])

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
