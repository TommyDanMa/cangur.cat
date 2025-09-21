@php
/**
 * Renders a nested UL list of blocks with drag handles.
 * Expects $blocks (collection) and optional $editable (bool).
 */
@endphp

<ul class="space-y-1" data-tree>
    @foreach($blocks as $b)
        @php $hasContent = (($b->children && $b->children->count() > 0) || (($b->questions_count ?? 0) > 0)); @endphp
        <li class="{{ ($editable ?? false) ? 'border rounded px-2 py-1 bg-white flex items-center gap-2' : 'px-2 py-1 flex items-center gap-2' }}" data-id="{{ $b->id }}" draggable="{{ ($editable ?? false) ? 'true' : 'false' }}">
            @if(($editable ?? false))
                <span class="cursor-move select-none text-gray-500" title="Arrossega">≡</span>
            @else
                <span aria-hidden="true" class="text-yellow-500">📁</span>
            @endif
            <div class="flex-1">
                <a href="{{ route('blocks.show', $b) }}" class="font-medium hover:underline">{{ $b->name }}</a>
                @if(($editable ?? false) && ($b->year || $b->level))
                    <span class="text-xs text-gray-500">{{ $b->year }} {{ $b->level }}</span>
                @endif
                @if(($editable ?? false))
                    @unless($b->visible)
                        <span class="ml-1 text-xs text-gray-500">(ocult)</span>
                    @endunless
                @endif
            </div>
            <button type="button" class="toggle-contents ml-2 text-gray-600 hover:text-gray-800 {{ $hasContent ? '' : 'hidden' }}" data-block-id="{{ $b->id }}" aria-expanded="false" title="Veure contingut">
                <svg class="w-4 h-4 transition-transform" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                    <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 10.94l3.71-3.71a.75.75 0 111.06 1.06l-4.24 4.24a.75.75 0 01-1.06 0L5.21 8.29a.75.75 0 01.02-1.08z" clip-rule="evenodd" />
                </svg>
            </button>
            @if(($editable ?? false))
                <div class="text-sm flex items-center gap-2">
                    <a href="{{ route('blocks.edit', $b) }}" class="text-blue-600 hover:underline">Editar</a>
                    <form method="POST" action="{{ route('blocks.destroy', $b) }}" onsubmit="return confirm('Eliminar aquest bloc?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-red-600 hover:underline">Eliminar</button>
                    </form>
                </div>
            @endif
        </li>
        <li class="ml-6 hidden" data-contents-for="{{ $b->id }}"></li>
        @if($b->children && $b->children->count())
            <li class="ml-6">
                @include('blocks._tree', ['blocks' => $b->children, 'editable' => $editable ?? false])
            </li>
        @else
            <li class="ml-6" data-empty></li>
        @endif
    @endforeach
</ul>
