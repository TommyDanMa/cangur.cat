<x-layouts.app>
    <x-slot:header>
        <h2 class="text-xl font-semibold leading-tight text-gray-800">
            {{ __('Editar resposta') }}
        </h2>
    </x-slot:header>

    <div class="py-8">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                @if ($errors->any())
                    <div class="mb-4 rounded border border-red-200 bg-red-50 text-red-700 p-3">
                        @foreach ($errors->all() as $e) <div>{{ $e }}</div> @endforeach
                    </div>
                @endif

                <form method="POST" action="{{ route('answers.update', $answer) }}" enctype="multipart/form-data" class="space-y-6">
                    @csrf
                    @method('PUT')

                    <div>
                        <label for="solution" class="block text-sm font-medium text-gray-700">
                            <strong>Solució</strong> (0000 = sense solució / 1–9999)
                        </label>
                        <input
                            id="solution"
                            name="solution"
                            value="{{ old('solution', $answer->no_solution ? '0000' : ($answer->solution ?? '')) }}"
                            inputmode="numeric"
                            pattern="^(0000|[1-9]\d{0,3})$"
                            placeholder="0000 o 1-9999"
                            class="mt-1 block w-40 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                        >
                        <p class="mt-1 text-xs text-gray-500">Escriu “0000” si no hi ha solució.</p>
                    </div>

                    <fieldset>
                        <legend class="text-sm font-medium text-gray-700">Format</legend>
                        <div class="mt-2 space-x-4">
                            <label class="inline-flex items-center">
                                <input type="radio" name="format" value="text" class="form-radio" {{ old('format',$answer->format)==='text' ? 'checked' : '' }}>
                                <span class="ms-2">Text</span>
                            </label>
                            <label class="inline-flex items-center">
                                <input type="radio" name="format" value="video" class="form-radio" {{ old('format',$answer->format)==='video' ? 'checked' : '' }}>
                                <span class="ms-2">Vídeo (URL)</span>
                            </label>
                            <label class="inline-flex items-center">
                                <input type="radio" name="format" value="image" class="form-radio" {{ old('format',$answer->format)==='image' ? 'checked' : '' }}>
                                <span class="ms-2">Imatge</span>
                            </label>
                        </div>
                    </fieldset>

                    <div id="textFields" {{ old('format',$answer->format)==='text' ? '' : 'style=display:none' }}>
                        <label class="block text-sm font-medium text-gray-700">Resposta en text</label>
                        <textarea name="content_text" rows="6" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('content_text', $answer->content_text) }}</textarea>
                    </div>

                    <div id="videoFields" {{ old('format',$answer->format)==='video' ? '' : 'style=display:none' }}>
                        <label class="block text-sm font-medium text-gray-700">URL del vídeo</label>
                        <input name="video_url" placeholder="https://..." value="{{ old('video_url', $answer->video_url) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>

                    <div id="imageFields" {{ old('format',$answer->format)==='image' ? '' : 'style=display:none' }}>
                        <label class="block text-sm font-medium text-gray-700">Imatge (JPG, PNG, JPEG, GIF, SVG) — màx. 2MB</label>
                        <input type="file" name="image" accept="image/*" class="mt-1 block w-full text-sm text-gray-700 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                        @if($answer->image_path)
                            <p class="text-xs text-gray-500 mt-1">Imatge actual: <a class="text-indigo-600 hover:underline" href="{{ asset('storage/'.$answer->image_path) }}" target="_blank">veure</a></p>
                        @endif
                    </div>

                    <div class="flex items-center gap-3">
                        <x-button>
                            {{ __('Actualitza') }}
                        </x-button>
                        <x-button variant="secondary" href="{{ route('answers.show', $answer) }}">
                            {{ __('Cancel·lar') }}
                        </x-button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        const radios = document.querySelectorAll('input[name="format"]');
        const textFields = document.getElementById('textFields');
        const videoFields = document.getElementById('videoFields');
        const imageFields = document.getElementById('imageFields');

        function sync(){
            const el = document.querySelector('input[name="format"]:checked');
            const v = el ? el.value : 'text';
            textFields.style.display  = (v === 'text')  ? '' : 'none';
            videoFields.style.display = (v === 'video') ? '' : 'none';
            imageFields.style.display = (v === 'image') ? '' : 'none';
        }
        radios.forEach(r => r.addEventListener('change', sync));
        sync();
    </script>
</x-layouts.app>
