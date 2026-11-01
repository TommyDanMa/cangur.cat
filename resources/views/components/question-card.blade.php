@props(['q'])

<a href="{{ route('questions.show', $q) }}">
    <div class="p-4 bg-white/5 rounded-xl border border-transparent group hover:border-indigo-400 transition-colors duration-300 flex gap-x-6">
        <div class="flex-1 flex flex-col">
            <div class="group-hover:text-indigo-400 transition-colors duration-300">
                    <h3 class="font-bold">{{ $q->title }}</h3>
                    ({{ $q->year }} · {{ $q->level }})
                </div>
        </div>
    </div>
</a>
