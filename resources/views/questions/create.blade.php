<x-layouts.app>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">
            {{ __('Crear problema') }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
            <form method="POST" action="{{ route('questions.store') }}" enctype="multipart/form-data"
                  x-data="{
                    phase: '{{ old('phase','local') }}',
                    session: '{{ old('session') }}',
                    imagePreview: null,
                    onImageChange(e) {
                      const file = e.target.files[0];
                      if (!file) { this.imagePreview = null; return; }
                      const reader = new FileReader();
                      reader.onload = (ev) => this.imagePreview = ev.target.result;
                      reader.readAsDataURL(file);
                    }
                  }"
                  class="bg-white shadow-sm sm:rounded-lg overflow-hidden">

                @csrf

                <!-- Mensajes de validación generales -->
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
                                    <h3 class="text-sm font-medium text-red-800">Revisi els camps:</h3>
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
                    <!-- Grupo: Título y Año -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Títol</label>
                            <input type="text" name="title" value="{{ old('title') }}"
                                   class="block w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
                                   placeholder="Títol del problema" required>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Any</label>
                            <input type="number" name="year" value="{{ old('year') }}"
                                   class="block w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
                                   placeholder="2024" required>
                        </div>
                    </div>

                    <!-- Grupo: Nivel -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="md:col-span-1">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nivell</label>
                            <input type="text" name="level" value="{{ old('level') }}"
                                   class="block w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
                                   placeholder="p.ej. Cadet (1ESO-2ESO)" required>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Fase</label>
                            <select name="phase" x-model="phase"
                                    class="block w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500" required>
                                <option value="local">Local</option>
                                <option value="semifinal">Semifinal</option>
                                <option value="final">Final</option>
                            </select>
                        </div>

                        <div x-show="phase === 'local'" x-cloak>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Sessió</label>
                            <select name="session" x-model="session"
                                    class="block w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="" disabled selected hidden>Selecciona...</option>
                                <option value="mati">Matí</option>
                                <option value="tarda">Tarda</option>
                            </select>
                            <p class="mt-1 text-xs text-gray-500">La sessió només s'aplica a la fase local.</p>
                        </div>
                    </div>

                    <!-- Bloc (opcional) -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Bloc (opcional)</label>
                        <select name="block_id" class="block w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">– Cap –</option>
                            @foreach($blocks ?? [] as $b)
                                <option value="{{ $b->id }}" @selected(old('block_id', request('block_id'))==$b->id)>{{ $b->name }}</option>
                            @endforeach
                        </select>
                        @error('block_id')<p class="text-sm text-red-600">{{ $message }}</p>@enderror
                        <p class="mt-1 text-xs text-gray-500">Pots arxivar el problema dins un bloc ara o més tard des del gestor de blocs.</p>
                    </div>

                    <!-- Enunciado -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Enunciat</label>
                        <textarea name="statement" rows="6"
                                  class="block w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
                                  placeholder="Describe el problema..." required>{{ old('statement') }}</textarea>
                    </div>

                    <!-- Imagen -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Imatge (opcional)</label>
                            <div class="flex items-center justify-center w-full">
                                <label
                                    class="flex flex-col items-center justify-center w-full h-32 border-2 border-dashed rounded-md cursor-pointer
                                           border-gray-300 hover:border-indigo-400">
                                    <div class="flex flex-col items-center justify-center pt-5 pb-6">
                                        <svg class="w-8 h-8 mb-2 text-gray-400" aria-hidden="true" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                  d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6H16a5 5 0 011 9.9M15 13l-3-3-4 4m0 0l-1-1m1 1h12"/>
                                        </svg>
                                        <p class="text-sm text-gray-600"><span class="font-semibold">Click per pujar</span> o arrossega</p>
                                        <p class="text-xs text-gray-500">PNG, JPG, GIF o SVG (máx. 2MB)</p>
                                    </div>
                                    <input id="image" name="image" type="file" class="hidden" accept="image/*" @change="onImageChange">
                                </label>
                            </div>
                        </div>
                        <div x-show="imagePreview" x-cloak>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Previsualització</label>
                            <img :src="imagePreview" alt="Preview" class="w-full rounded-md border border-gray-200">
                        </div>
                    </div>
                </div>

                <!-- Footer acciones -->
                <div class="bg-gray-50 px-6 py-4 flex items-center justify-end gap-3">
                    <a href="{{ route('questions.index') }}"
                       class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                        Cancel·la
                    </a>
                    <button type="submit"
                            class="inline-flex items-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        Puja
                    </button>
                </div>
            </form>
        </div>
    </div>

    <style>[x-cloak]{display:none !important}</style>
</x-layouts.app>
