@if ($paginator->hasPages())
    <nav role="navigation"
         aria-label="{{ __('Pagination Navigation') }}"
         class="flex justify-between border-t border-gray-200">

        {{-- Previous Page Link --}}
        @if ($paginator->onFirstPage())
            <span
                class="inline-flex items-center px-4 py-3 text-sm font-medium text-gray-300
                       border-t-2 border-transparent
                       cursor-not-allowed opacity-40 select-none">
                {!! __('pagination.previous') !!}
            </span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}"
               rel="prev"
               class="inline-flex items-center px-4 py-3 text-sm font-medium text-gray-600
                      border-t-2 border-transparent
                      hover:border-indigo-400 hover:text-indigo-400
                      transition-colors duration-300">
                {!! __('pagination.previous') !!}
            </a>
        @endif

        {{-- Next Page Link --}}
        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}"
               rel="next"
               class="inline-flex items-center px-4 py-3 text-sm font-medium text-gray-600
                      border-t-2 border-transparent
                      hover:border-indigo-400 hover:text-indigo-400
                      transition-colors duration-300">
                {!! __('pagination.next') !!}
            </a>
        @else
            <span
                class="inline-flex items-center px-4 py-3 text-sm font-medium text-gray-400
                       border-t-2 border-transparent
                       cursor-not-allowed opacity-40 select-none">
                {!! __('pagination.next') !!}
            </span>
        @endif

    </nav>
@endif
