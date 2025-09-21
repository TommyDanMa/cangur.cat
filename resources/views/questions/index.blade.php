<x-layouts.app>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">
            {{ __('Problemes') }}
        </h2>
    </x-slot>

    @php
        $teacher = auth()->check() && auth()->user()->role === 'teacher';
        $teacherMode = $teacher ? session('teacher_mode', 'edit') : 'view';
        $teacherEditable = $teacher && $teacherMode === 'edit';
    @endphp

    <div class="py-8">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm sm:rounded-lg p-6 space-y-4">
                <form method="GET" action="{{ route('questions.index') }}" class="space-y-3">
                    <div class="flex flex-wrap items-end gap-3">
                        <div class="js-react-typeahead"
                             data-name="year"
                             data-endpoint="{{ url('/questions/suggest') }}"
                             data-field="year"
                             data-initial="{{ request('year') }}"
                             data-placeholder="Any"
                             data-width-class="w-40"></div>

                        <div class="js-react-typeahead"
                             data-name="level"
                             data-endpoint="{{ url('/questions/suggest') }}"
                             data-field="level"
                             data-initial="{{ request('level') }}"
                             data-placeholder="Nivell"
                             data-width-class="w-44"></div>

                        <div class="js-react-typeahead"
                             data-name="phase"
                             data-endpoint="{{ url('/questions/suggest') }}"
                             data-field="phase"
                             data-initial="{{ request('phase') }}"
                             data-placeholder="Fase"
                             data-width-class="w-44"></div>

                        <div class="js-react-typeahead"
                             data-name="session"
                             data-endpoint="{{ url('/questions/suggest') }}"
                             data-field="session"
                             data-initial="{{ request('session') }}"
                             data-placeholder="Sessió"
                             data-width-class="w-44"></div>

                        <button type="submit" class="px-4 h-10 bg-indigo-600 text-white rounded-md">Cerca</button>
                    </div>
                </form>

                {{-- Nou problema button moved next to Nou bloc inside the blocks panel for teachers --}}

                {{-- Blocks (folders) panel on index: read-only for students, editable for teachers --}}
                <div id="blocks-panel" class="space-y-2">
                    @auth
                        @if($teacher)
                            <div class="flex items-center justify-end">
                                <form method="POST" action="{{ route('ui.teacher_mode') }}" class="inline-flex bg-gray-100 rounded-md overflow-hidden">
                                    @csrf
                                    <button type="submit" name="mode" value="view" class="px-3 py-1 text-sm {{ $teacherMode==='view' ? 'bg-white text-gray-900' : 'text-gray-600 hover:text-gray-800' }}">Veure</button>
                                    <button type="submit" name="mode" value="edit" class="px-3 py-1 text-sm {{ $teacherMode==='edit' ? 'bg-white text-gray-900' : 'text-gray-600 hover:text-gray-800' }}">Edita</button>
                                </form>
                            </div>
                        @endif
                    @endauth
                    @auth
                        @if($teacherEditable)
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <a href="{{ route('blocks.create') }}" class="px-3 py-1.5 bg-indigo-600 text-white rounded hover:bg-indigo-700">Nou bloc</a>
                                    <a href="{{ route('questions.create') }}" class="px-3 py-1.5 bg-indigo-500 text-white rounded hover:bg-indigo-600">Nou problema</a>
                                </div>
                                <div class="flex items-center gap-2">
                                    <button id="save-blocks-order" class="px-3 py-1.5 bg-green-600 text-white rounded hover:bg-green-700">Desar ordre</button>
                                    <span id="blocks-save-status" class="text-sm text-gray-600"></span>
                                </div>
                            </div>
                        @endif
                    @endauth

                    <div id="blocks-tree-index" class="space-y-2">
                        @include('blocks._tree', ['blocks' => $blocks ?? collect(), 'editable' => ($teacherEditable)])
                    </div>
                </div>

                @auth
                    @if($teacherEditable)
                        <h3 class="font-semibold mt-4">Previsualització de problemes (sense bloc)</h3>
                        <div id="root-dropzone" class="mt-2 border-2 border-dashed border-gray-300 rounded p-2 text-sm text-gray-600">
                            Deixa aquí per treure un problema d'un bloc
                        </div>
                    @endif
                @endauth
                <ul id="questions-list-index" class="space-y-1">
                    @foreach($questions as $q)
                        <li class="{{ ($teacherEditable) ? 'border rounded px-2 py-1 bg-white flex items-center gap-2' : 'px-2 py-1 flex items-center gap-2' }}" data-qid="{{ $q->id }}" draggable="{{ $teacherEditable ? 'true' : 'false' }}">
                            @auth
                                @if($teacherEditable)
                                    <span class="cursor-move select-none text-gray-500" title="Arrossega">≡</span>
                                @endif
                            @endauth
                            <span aria-hidden="true" class="text-blue-500">❓</span>
                            <div class="flex-1">
                                <a href="{{ route('questions.show',$q) }}">{{ $q->title }}
                                ({{ $q->year }} · {{ $q->level }})
                                </a>
                            </div>
                        </li>
                    @endforeach
                </ul>

                @auth
                    @if($teacherEditable)
                        <div class="flex items-center gap-2 mt-2">
                            <button id="save-unassigned-order" class="px-3 py-1.5 bg-green-600 text-white rounded hover:bg-green-700">Desar ordre (sense bloc)</button>
                            <span id="unassigned-save-status" class="text-sm text-gray-600"></span>
                        </div>
                    @endif
                @endauth

                {{ $questions->links() }}

            </div>
        </div>
    </div>
    <script>
        (function(){
            const isTeacher = {{ auth()->check() && auth()->user()->role === 'teacher' ? 'true' : 'false' }};
            const isEditable = {{ (auth()->check() && auth()->user()->role === 'teacher' && session('teacher_mode','edit')==='edit') ? 'true' : 'false' }};

            // Blocks DnD (reorder/reparent) for teachers
            const blocksContainer = document.getElementById('blocks-tree-index');
            if (blocksContainer && isEditable) {
                let dragged = null;
                blocksContainer.addEventListener('dragstart', function(e){
                    const li = e.target.closest('li[data-id]');
                    if (!li) return; dragged = li; e.dataTransfer.effectAllowed = 'move'; e.dataTransfer.setData('text/plain', li.dataset.id);
                });
                blocksContainer.addEventListener('dragover', function(e){
                    const li = e.target.closest('li[data-id]');
                    if (!li) return; e.preventDefault(); e.dataTransfer.dropEffect = 'move';
                });
                // Insert before/after among siblings
                blocksContainer.addEventListener('drop', function(e){
                    const target = e.target.closest('li[data-id]');
                    if (!target || !dragged || target === dragged) return; e.preventDefault();
                    // Alt+drop => reparent as child
                    if (e.altKey) {
                        let nextLi = target.nextElementSibling;
                        if (!nextLi || !nextLi.querySelector('ul[data-tree]')) {
                            nextLi = document.createElement('li');
                            nextLi.className = 'ml-6';
                            const ul = document.createElement('ul'); ul.className = 'space-y-1'; ul.setAttribute('data-tree','');
                            nextLi.appendChild(ul); target.parentElement.insertBefore(nextLi, target.nextElementSibling);
                        }
                        const nestedUl = nextLi.querySelector('ul[data-tree]'); nestedUl.appendChild(dragged);
                        return;
                    }
                    const rect = target.getBoundingClientRect(); const before = (e.clientY - rect.top) < (rect.height/2);
                    if (before) target.parentElement.insertBefore(dragged, target); else target.parentElement.insertBefore(dragged, target.nextSibling);
                });

                function buildPayload() {
                    const result = [];
                    function walk(ul, parentId = null) {
                        Array.from(ul.children).forEach((li, index) => {
                            if (!li.matches('li')) return;
                            const idLi = li.matches('li[data-id]') ? li : null;
                            if (idLi) {
                                result.push({ id: parseInt(idLi.dataset.id,10), parent_id: parentId, position: index });
                            }
                            const nextLi = idLi ? idLi.nextElementSibling : null;
                            const nestedUl = nextLi ? nextLi.querySelector('ul[data-tree]') : null;
                            if (nestedUl) walk(nestedUl, idLi ? parseInt(idLi.dataset.id,10) : parentId);
                        });
                    }
                    const root = blocksContainer.querySelector('ul[data-tree]');
                    if (root) walk(root, null);
                    return result;
                }

                const saveBtn = document.getElementById('save-blocks-order');
                const statusEl = document.getElementById('blocks-save-status');
                if (saveBtn) {
                    saveBtn.addEventListener('click', async function(){
                        const items = buildPayload();
                        if (statusEl) statusEl.textContent = 'Desant...';
                        try {
                            const res = await fetch('{{ route('blocks.reorder') }}', { method:'POST', headers:{ 'Content-Type':'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' }, body: JSON.stringify({ items }) });
                            if (!res.ok) throw new Error('HTTP '+res.status);
                            if (statusEl) { statusEl.textContent = 'Canvis desats'; setTimeout(()=> statusEl.textContent='', 2000); }
                        } catch (err) {
                            if (statusEl) statusEl.textContent = 'No s\'ha pogut desar';
                        }
                    });
                }
            }

            // Drag question from index and drop onto a block to move it there (teacher only)
            const qList = document.getElementById('questions-list-index');
            if (qList && isEditable) {
                let draggedQ = null;
                qList.addEventListener('dragstart', function(e){
                    const li = e.target.closest('li[data-qid]');
                    if (!li) return; draggedQ = li; e.dataTransfer.effectAllowed = 'move'; e.dataTransfer.setData('application/x-question-id', li.dataset.qid);
                });
                // Enable reordering within the unassigned list
                qList.addEventListener('dragover', function(e){
                    const li = e.target.closest('li[data-qid]');
                    if (!li) return; e.preventDefault(); e.dataTransfer.dropEffect = 'move';
                });
                qList.addEventListener('drop', function(e){
                    const target = e.target.closest('li[data-qid]');
                    if (!target || !draggedQ || target === draggedQ) return; e.preventDefault();
                    const rect = target.getBoundingClientRect(); const before = (e.clientY - rect.top) < (rect.height/2);
                    if (before) target.parentElement.insertBefore(draggedQ, target); else target.parentElement.insertBefore(draggedQ, target.nextSibling);
                });
            }
            if (blocksContainer && isEditable) {
                blocksContainer.addEventListener('dragenter', function(e){
                    const li = e.target.closest('li[data-id]'); if (!li) return; li.classList.add('bg-indigo-50');
                });
                blocksContainer.addEventListener('dragleave', function(e){
                    const li = e.target.closest('li[data-id]'); if (!li) return; li.classList.remove('bg-indigo-50');
                });
                blocksContainer.addEventListener('drop', async function(e){
                    const target = e.target.closest('li[data-id]'); if (!target) return;
                    const qid = e.dataTransfer.getData('application/x-question-id');
                    if (!qid) return; e.preventDefault(); target.classList.remove('bg-indigo-50');
                    const blockId = parseInt(target.dataset.id,10);
                    try {
                        const res = await fetch('{{ route('questions.reorder') }}', { method:'POST', headers:{ 'Content-Type':'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' }, body: JSON.stringify({ items: [{ id: parseInt(qid,10), block_id: blockId, position: 999999 }] }) });
                        if (!res.ok) throw new Error('HTTP '+res.status);
                        // Remove the question from index list since it moved into a block
                        const item = qList ? qList.querySelector(`li[data-qid="${qid}"]`) : null; if (item) item.remove();
                        // Also remove from any open dropdown where it might be
                        const prev = document.querySelector(`li[data-qid="${qid}"]`);
                        if (prev && prev.closest('[data-dropdown-block]')) prev.remove();
                        // Reveal caret for target block (in case it was hidden before because it was empty)
                        const caretBtn = blocksContainer.querySelector(`.toggle-contents[data-block-id="${blockId}"]`);
                        if (caretBtn) caretBtn.classList.remove('hidden');
                        // If the target block dropdown is currently open, refresh its contents; otherwise mark it stale
                        const slot = blocksContainer.querySelector(`li[data-contents-for="${blockId}"]`);
                        if (slot) {
                            const isOpen = !slot.classList.contains('hidden');
                            if (isOpen) {
                                try {
                                    const r2 = await fetch(`{{ url('/blocks') }}/${blockId}/contents`);
                                    if (r2.ok) {
                                        const data2 = await r2.json();
                                        slot.innerHTML = renderDropdown(blockId, data2);
                                        slot.dataset.loaded = '1';
                                        slot.classList.remove('hidden');
                                        const icon = caretBtn ? caretBtn.querySelector('svg') : null;
                                        if (icon) icon.style.transform = 'rotate(180deg)';
                                    }
                                } catch (e) { /* noop */ }
                            } else {
                                delete slot.dataset.loaded; // force a fresh load next time
                            }
                        }
                    } catch (err) {
                        alert('No s\'ha pogut moure el problema.');
                    }
                });
            }

            // Toggle caret to show inline dropdown with contents
            if (blocksContainer) {
                blocksContainer.addEventListener('click', async function(e){
                    const btn = e.target.closest('.toggle-contents');
                    if (!btn) return;
                    const blockId = parseInt(btn.dataset.blockId,10);
                    const slot = blocksContainer.querySelector(`li[data-contents-for="${blockId}"]`);
                    const icon = btn.querySelector('svg');
                    if (!slot) return;
                    // If visible, hide
                    if (!slot.classList.contains('hidden')) {
                        slot.classList.add('hidden');
                        if (icon) icon.style.transform = 'rotate(0deg)';
                        return;
                    }
                    // Load if empty
                    if (!slot.dataset.loaded) {
                        try {
                            const res = await fetch(`{{ url('/blocks') }}/${blockId}/contents`);
                            if (!res.ok) throw new Error('HTTP '+res.status);
                            const data = await res.json();
                            slot.innerHTML = renderDropdown(blockId, data);
                            slot.dataset.loaded = '1';
                        } catch (err) {
                            slot.innerHTML = '<div class="text-sm text-gray-500">No s\'ha pogut carregar el contingut.</div>';
                        }
                    }
                    slot.classList.remove('hidden');
                    slot.setAttribute('data-dropdown-block', blockId);
                    if (icon) icon.style.transform = 'rotate(180deg)';
                });
            }

            function renderDropdown(blockId, data) {
                const isT = isEditable;
                let html = '<div class="p-2 border rounded bg-gray-50">';
                if (Array.isArray(data.children) && data.children.length) {
                    html += '<div class="mb-1 text-xs text-gray-500">Subblocs</div><ul class="space-y-1">';
                    data.children.forEach(ch => {
                        html += `<li class="px-2 py-1 flex items-center gap-2"><span class="text-yellow-500">📁</span><a class="hover:underline" href="{{ url('/blocks') }}/${ch.id}">${ch.name}</a></li>`;
                    });
                    html += '</ul>';
                }
                if (Array.isArray(data.questions) && data.questions.length) {
                    html += '<div class="mt-2 mb-1 text-xs text-gray-500">Problemes</div><ul class="space-y-1">';
                    data.questions.forEach(q => {
                        const drag = isT ? 'draggable="true"' : '';
                        const border = isT ? 'border rounded bg-white' : '';
                        const year = (q.year ?? '') === '' ? '' : q.year;
                        const level = (q.level ?? '') === '' ? '' : q.level;
                        const meta = (year || level) ? ` (${year}${year && level ? ' · ' : ''}${level})` : '';
                        html += `<li class="${border} px-2 py-1 flex items-center gap-2" data-qid="${q.id}" ${drag}>
                            ${isT ? '<span class="cursor-move select-none text-gray-500">≡</span>' : ''}
                            <span class="text-blue-500">❓</span>
                            <div class="flex-1"><a class="hover:underline" href="{{ url('/questions') }}/${q.id}">${q.title}${meta}</a></div>
                        </li>`;
                    });
                    html += '</ul>';
                }
                if ((!data.children || !data.children.length) && (!data.questions || !data.questions.length)) {
                    html += '<div class="text-sm text-gray-500">Aquest bloc està buit.</div>';
                }
                html += '</div>';
                return html;
            }

            // Enable dragging from dropdown lists for teachers
            if (isTeacher) {
                document.addEventListener('dragstart', function(e){
                    const li = e.target.closest('li[data-qid]');
                    if (!li) return;
                    if (li.hasAttribute('draggable')) {
                        e.dataTransfer.effectAllowed = 'move';
                        e.dataTransfer.setData('application/x-question-id', li.dataset.qid);
                    }
                });
            }

            // Root dropzone to unassign from a block
            const rootDrop = document.getElementById('root-dropzone');
            if (rootDrop && isEditable) {
                rootDrop.addEventListener('dragover', function(e){ e.preventDefault(); rootDrop.classList.add('bg-indigo-50'); });
                rootDrop.addEventListener('dragleave', function(){ rootDrop.classList.remove('bg-indigo-50'); });
                rootDrop.addEventListener('drop', async function(e){
                    const qid = e.dataTransfer.getData('application/x-question-id');
                    if (!qid) return; e.preventDefault(); rootDrop.classList.remove('bg-indigo-50');
                    try {
                        const res = await fetch('{{ route('questions.reorder') }}', { method:'POST', headers:{ 'Content-Type':'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' }, body: JSON.stringify({ items: [{ id: parseInt(qid,10), block_id: null, position: 999999 }] }) });
                        if (!res.ok) throw new Error('HTTP '+res.status);
                        // Find any existing DOM nodes for this question
                        const list = document.getElementById('questions-list-index');
                        const nodes = Array.from(document.querySelectorAll(`li[data-qid="${qid}"]`));

                        if (list) {
                            // Move the first found node into the unassigned list and ensure proper styling
                            const node = nodes[0] || null;
                            if (node) {
                                node.setAttribute('draggable', 'true');
                                node.classList.add('border','rounded','bg-white');
                                if (!node.querySelector('.cursor-move')) {
                                    const handle = document.createElement('span');
                                    handle.className = 'cursor-move select-none text-gray-500';
                                    handle.textContent = '≡';
                                    node.insertBefore(handle, node.firstElementChild);
                                }
                                list.appendChild(node);
                            }
                            // Remove any remaining duplicates not in the index list
                            nodes.forEach((node, idx) => { if (!list.contains(node) || idx > 0) node.remove(); });
                        }
                    } catch(err) {
                        alert('No s\'ha pogut treure del bloc.');
                    }
                });
            }

            // Save unassigned order
            if (isEditable && qList) {
                function buildUnassignedItems(){
                    const items = [];
                    Array.from(qList.children).forEach((li, idx) => {
                        if (!li.matches('li[data-qid]')) return; items.push({ id: parseInt(li.dataset.qid,10), block_id: null, position: idx });
                    });
                    return items;
                }
                const saveUnBtn = document.getElementById('save-unassigned-order');
                const unStatus = document.getElementById('unassigned-save-status');
                if (saveUnBtn) {
                    saveUnBtn.addEventListener('click', async function(){
                        const items = buildUnassignedItems(); if (unStatus) unStatus.textContent = 'Desant...';
                        try {
                            const res = await fetch('{{ route('questions.reorder') }}', { method:'POST', headers:{ 'Content-Type':'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' }, body: JSON.stringify({ items }) });
                            if (!res.ok) throw new Error('HTTP '+res.status);
                            if (unStatus) { unStatus.textContent = 'Canvis desats'; setTimeout(()=> unStatus.textContent='', 2000); }
                        } catch(err) {
                            if (unStatus) unStatus.textContent = 'No s\'ha pogut desar';
                        }
                    });
                }
            }
        })();
    </script>
</x-layouts.app>
