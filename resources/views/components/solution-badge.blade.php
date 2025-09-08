@props(['noSolution' =>false, 'solution' => null])

@if($noSolution)
    <span class="inline-flex items-center text-xs text-gray-700 bg-gray-100 rounded px-2 py-0.5">sense solució (0000)</span>
@elseif(!is_null($solution))
    <span class="inline-flex items-center text-xs text-indigo-700 bg-indigo-100 rounded px-2 py-0.5">#{{ $solution }}</span>
@else
    <span class="inline-flex items-center text-xs text-gray-600 bg-gray-100 rounded px-2 py-0.5">—</span>
@endif
