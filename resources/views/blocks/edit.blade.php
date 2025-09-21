<x-layouts.app>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">Editar bloc</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <form method="POST" action="{{ route('blocks.update', $block) }}" class="space-y-4">
                    @csrf
                    @method('PUT')

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Nom</label>
                        <input type="text" name="name" value="{{ old('name', $block->name) }}" class="mt-1 block w-full border rounded px-3 py-2" required>
                        @error('name')<p class="text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Descripció</label>
                        <textarea name="description" class="mt-1 block w-full border rounded px-3 py-2" rows="3">{{ old('description', $block->description) }}</textarea>
                        @error('description')<p class="text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Pare (opcional)</label>
                            <select name="parent_id" class="mt-1 block w-full border rounded px-3 py-2">
                                <option value="">– Cap –</option>
                                @foreach($parents as $p)
                                    <option value="{{ $p->id }}" @selected(old('parent_id', $block->parent_id)==$p->id)>{{ $p->name }}</option>
                                @endforeach
                            </select>
                            @error('parent_id')<p class="text-sm text-red-600">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Any</label>
                            <input type="number" name="year" value="{{ old('year', $block->year) }}" class="mt-1 block w-full border rounded px-3 py-2">
                            @error('year')<p class="text-sm text-red-600">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Nivell</label>
                            <input type="text" name="level" value="{{ old('level', $block->level) }}" class="mt-1 block w-full border rounded px-3 py-2">
                            @error('level')<p class="text-sm text-red-600">{{ $message }}</p>@enderror
                        </div>
                    </div>

                    <div class="flex items-center gap-2">
                        <label class="inline-flex items-center">
                            <input type="checkbox" name="visible" value="1" class="mr-2" @checked(old('visible', $block->visible))>
                            Visible
                        </label>
                        @error('visible')<p class="text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <div class="flex items-center gap-2">
                        <button class="px-4 py-2 bg-indigo-600 text-white rounded">Desar</button>
                        <a href="{{ route('blocks.show', $block) }}" class="px-4 py-2 border rounded">Cancel·lar</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-layouts.app>
