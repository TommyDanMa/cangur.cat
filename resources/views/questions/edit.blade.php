<x-layouts.app>
    <x-slot:header>
        <h2 class="text-xl font-semibold leading-tight text-gray-800">
            {{ __('Editar problema') }}
        </h2>
    </x-slot:header>

    <div class="py-8">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
            <form method="POST" action="{{ route('questions.update', $question) }}" enctype="multipart/form-data" class="bg-white shadow-sm sm:rounded-lg overflow-hidden">
                @csrf
                @method('PUT')

                @if ($errors->any())
                    <div class="px-6 pt-6">
                        <div class="rounded-md bg-red-50 p-4">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16Zm-.75-11.5a.75.75 0 011.5 0v5a.75.75 0 01-1.5 0v-5Zm.75 8a1 1 0 100-2 1 1 0 000 2Z" clip-rule="evenodd"/>
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <h3 class="text-sm font-medium text-red-800">{{ __('Revisi els camps:') }}</h3>
                                    <div class="mt-2 text-sm text-red-700">
                                        <ul class="list-disc pl-5 space-y-1">
                                            @foreach ($errors->all() as $error)
                                                <li>{{ $error }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                <div class="p-6 space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Títol</label>
                            <input type="text" name="title" value="{{ old('title', $question->title) }}"
                                   class="block w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Any</label>
                            <input type="number" name="year" value="{{ old('year', $question->year) }}"
                                   class="block w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500" required>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="md:col-span-1">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nivell</label>
                            <input type="text" name="level" value="{{ old('level', $question->level) }}"
                                   class="block w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Fase</label>
                            <select name="phase" class="block w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500" required>
                                <option value="local" {{ old('phase', $question->phase)==='local' ? 'selected' : '' }}>Local</option>
                                <option value="semifinal" {{ old('phase', $question->phase)==='semifinal' ? 'selected' : '' }}>Semifinal</option>
                                <option value="final" {{ old('phase', $question->phase)==='final' ? 'selected' : '' }}>Final</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Sessió</label>
                            <select name="session" class="block w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="" {{ old('session', $question->session)===null || old('session', $question->session)==='' ? 'selected' : '' }} hidden>Selecciona...</option>
                                <option value="mati" {{ old('session', $question->session)==='mati' ? 'selected' : '' }}>Matí</option>
                                <option value="tarda" {{ old('session', $question->session)==='tarda' ? 'selected' : '' }}>Tarda</option>
                            </select>
                            <p class="mt-1 text-xs text-gray-500">{{ __('La sessió només s\'aplica a la fase local.') }}</p>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Enunciat</label>
                        <textarea name="statement" rows="6" class="block w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500" required>{{ old('statement', $question->statement) }}</textarea>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Imatge (opcional)</label>
                            <input id="image" name="image" type="file" accept="image/*" class="block w-full text-sm text-gray-700 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                            @if($question->image_path)
                                <p class="mt-2 text-xs text-gray-500">{{ __('Imatge actual:') }} <span class="font-mono">{{ $question->image_path }}</span></p>
                            @endif
                        </div>
                        @if($question->image_path)
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Previsualització actual') }}</label>
                                <img src="{{ asset('storage/'.$question->image_path) }}" alt="Imatge actual" class="w-full rounded-md border border-gray-200">
                            </div>
                        @endif
                    </div>
                </div>

                <div class="bg-gray-50 px-6 py-4 flex items-center justify-end gap-3">
                    <x-button href="{{ route('questions.show', $question) }}" variant="secondary">
                        {{ __('Cancel·la') }}
                    </x-button>
                    <x-button>
                        {{ __('Desa') }}
                    </x-button>
                </div>
            </form>
        </div>
    </div>
</x-layouts.app>
