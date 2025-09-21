<x-layouts.app>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">{{ __('Blocs de problemes') }}</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm sm:rounded-lg p-6 space-y-4">
                <div class="flex items-center justify-between">
                    <form method="GET" action="{{ route('blocks.index') }}" class="flex flex-wrap items-end gap-2">
                        <div class="js-react-typeahead"
                             data-name="year"
                             data-endpoint="{{ route('blocks.suggest') }}"
                             data-field="year"
                             data-initial="{{ $filters['year'] ?? '' }}"
                             data-placeholder="Any"
                             data-width-class="w-28"></div>

                        <div class="js-react-typeahead"
                             data-name="level"
                             data-endpoint="{{ route('blocks.suggest') }}"
                             data-field="level"
                             data-initial="{{ $filters['level'] ?? '' }}"
                             data-placeholder="Nivell"
                             data-width-class="w-32"></div>

                        @auth
                            @if(auth()->user()->role === 'teacher')
                                <select name="visible" class="border rounded px-2 h-9 w-28 text-center">
                                    <option value="">Tots</option>
                                    <option value="1" @selected(($filters['visible'] ?? '')==='1')>Visibles</option>
                                    <option value="0" @selected(($filters['visible'] ?? '')==='0')>Ocults</option>
                                </select>
                            @endif
                        @endauth
                        <button class="px-3 h-9 bg-indigo-600 text-white rounded">Filtra</button>
                    </form>

                    @auth
                        @if(auth()->user()->role === 'teacher')
                            <div class="flex items-center gap-2">
                                <a href="{{ route('blocks.create') }}" class="px-3 py-1.5 bg-indigo-600 text-white rounded hover:bg-indigo-700">Nou bloc</a>
                                <a href="{{ route('questions.create') }}" class="px-3 py-1.5 bg-indigo-500 text-white rounded hover:bg-indigo-600">Nou problema</a>
                            </div>
                        @endif
                    @endauth
                </div>

                @auth
                    @if(auth()->user()->role === 'teacher')
                        <p class="text-sm text-gray-600">Arrossega per reordenar, deixa anar sobre un altre element per canviar el pare. Fes clic a "Desar ordre" per persistir canvis.</p>
                    @else
                        <p class="text-sm text-gray-600">Vista de blocs en mode lectura. Els docents poden reordenar i organitzar.</p>
                    @endif
                @else
                    <p class="text-sm text-gray-600">Vista de blocs en mode lectura.</p>
                @endauth

                <div id="blocks-tree" class="space-y-2">
                    @include('blocks._tree', ['blocks' => $blocks, 'editable' => (auth()->check() && auth()->user()->role === 'teacher')])
                </div>

                @auth
                    @if(auth()->user()->role === 'teacher')
                        <div class="flex items-center gap-2">
                            <button id="save-order" class="px-3 py-1.5 bg-green-600 text-white rounded hover:bg-green-700">Desar ordre</button>
                            <span id="save-status" class="text-sm text-gray-600"></span>
                        </div>
                    @endif
                @endauth
            </div>
        </div>
    </div>

    <script>
        // Minimal drag & drop for nested lists
        (function() {
            const container = document.getElementById('blocks-tree');
            if (!container) return;
            let dragged = null;

            container.addEventListener('dragstart', function(e){
                const li = e.target.closest('li[data-id]');
                if (!li) return;
                dragged = li;
                e.dataTransfer.effectAllowed = 'move';
                e.dataTransfer.setData('text/plain', li.dataset.id);
            });

            container.addEventListener('dragover', function(e){
                const li = e.target.closest('li[data-id]');
                if (!li) return;
                e.preventDefault();
                e.dataTransfer.dropEffect = 'move';
            });

            container.addEventListener('drop', function(e){
                const target = e.target.closest('li[data-id]');
                if (!target || !dragged || target === dragged) return;
                e.preventDefault();
                // If dropping on the top half, insert before; if on the bottom half, insert after
                const rect = target.getBoundingClientRect();
                const before = (e.clientY - rect.top) < (rect.height / 2);
                if (before) {
                    target.parentElement.insertBefore(dragged, target);
                } else {
                    target.parentElement.insertBefore(dragged, target.nextSibling);
                }
            });

            // Allow dropping ON an item to reparent (append as child)
            container.addEventListener('dblclick', function(e){
                // Tip: double click toggles expand/collapse in more advanced UIs; skipped here for KISS
            });

            // On ALT-drop, append as child (simple modifier gesture)
            container.addEventListener('drop', function(e){
                if (!e.altKey) return; // use Alt to force nesting
                const target = e.target.closest('li[data-id]');
                if (!target || !dragged || target === dragged) return;
                e.preventDefault();
                // Find/create the nested UL right after the target li
                let sibling = target.nextElementSibling;
                if (!sibling || !sibling.matches('li > ul, ul')) {
                    // our template uses: <li>item</li><li class="ml-6">(children)</li>
                    let nextLi = target.nextElementSibling;
                    if (!nextLi || !nextLi.querySelector('ul[data-tree]')) {
                        nextLi = document.createElement('li');
                        nextLi.className = 'ml-6';
                        const ul = document.createElement('ul');
                        ul.className = 'space-y-1';
                        ul.setAttribute('data-tree','');
                        nextLi.appendChild(ul);
                        target.parentElement.insertBefore(nextLi, target.nextElementSibling);
                    }
                    sibling = nextLi.querySelector('ul[data-tree]');
                } else if (sibling.tagName.toLowerCase() === 'li') {
                    sibling = sibling.querySelector('ul[data-tree]');
                }
                sibling.appendChild(dragged);
            }, true);

            function buildPayload() {
                const result = [];
                function walk(ul, parentId = null) {
                    Array.from(ul.children).forEach((li, index) => {
                        if (!li.matches('li')) return;
                        const idLi = li.matches('li[data-id]') ? li : null;
                        if (idLi) {
                            result.push({ id: parseInt(idLi.dataset.id, 10), parent_id: parentId, position: index });
                        }
                        // If next sibling is a nested container <li><ul data-tree>...</ul></li>
                        const nextLi = idLi ? idLi.nextElementSibling : null;
                        const nestedUl = nextLi ? nextLi.querySelector('ul[data-tree]') : null;
                        if (nestedUl) {
                            walk(nestedUl, idLi ? parseInt(idLi.dataset.id, 10) : parentId);
                        }
                    });
                }
                const rootUls = container.querySelectorAll('ul[data-tree]:first-of-type');
                if (rootUls.length) walk(rootUls[0], null);
                return result;
            }

            const saveBtn = document.getElementById('save-order');
            const statusEl = document.getElementById('save-status');
            if (saveBtn) {
                saveBtn.addEventListener('click', async function(){
                    const items = buildPayload();
                    statusEl.textContent = 'Desant...';
                    try {
                        const res = await fetch('{{ route('blocks.reorder') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({ items })
                        });
                        if (!res.ok) throw new Error('Error ' + res.status);
                        statusEl.textContent = 'Canvis desats';
                        setTimeout(()=> statusEl.textContent = '', 2000);
                    } catch (e) {
                        statusEl.textContent = 'No s\'ha pogut desar';
                    }
                });
            }
        })();
    </script>
</x-layouts.app>
