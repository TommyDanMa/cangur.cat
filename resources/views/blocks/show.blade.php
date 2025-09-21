<x-layouts.app>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">{{ $block->name }}</h2>
    </x-slot>

    @php
        $teacher = auth()->check() && auth()->user()->role === 'teacher';
        $teacherMode = $teacher ? session('teacher_mode', 'edit') : 'view';
        $teacherEditable = $teacher && $teacherMode === 'edit';
    @endphp

    <div class="py-8">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm sm:rounded-lg p-6 space-y-4">
                <div class="flex items-center justify-between">
                    <div>
                        @if(!empty($block->description))
                            <p class="text-gray-700">{{ $block->description }}</p>
                        @endif
                        @auth
                            @if($teacherEditable)
                                <p class="text-sm text-gray-500">Any: {{ $block->year ?? '—' }} · Nivell: {{ $block->level ?? '—' }} · {{ $block->visible ? 'Visible' : 'Ocult' }}</p>
                            @endif
                        @endauth
                    </div>
                    @auth
                        @if($teacherEditable)
                            <div class="flex items-center gap-2">
                                <a href="{{ route('blocks.edit',$block) }}" class="text-blue-600 hover:underline">Editar</a>
                                <form method="POST" action="{{ route('blocks.destroy', $block) }}" onsubmit="return confirm('Eliminar aquest bloc?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:underline">Eliminar</button>
                                </form>
                            </div>
                        @endif
                    @endauth
                </div>

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
                        <h3 class="font-semibold">Subblocs</h3>
                    @endif
                @endauth
                @if($block->children->isEmpty())
                    @auth
                        @if($teacherEditable)
                            <p class="text-sm text-gray-500">Cap subbloc.</p>
                        @endif
                    @endauth
                @else
                    <ul class="list-none pl-0 space-y-1">
                        @foreach($block->children as $child)
                            <li class="px-2 py-1 flex items-center gap-2">
                                <span aria-hidden="true" class="text-yellow-500">📁</span>
                                <a href="{{ route('blocks.show', $child) }}" class="text-indigo-700 hover:underline">{{ $child->name }}</a>
                            </li>
                        @endforeach
                    </ul>
                @endif

                @auth
                    @if(auth()->user()->role === 'teacher')
                        <h3 class="font-semibold mt-6">Problemes dins el bloc</h3>
                        <p class="text-sm text-gray-600 mb-2">Arrossega per reordenar els problemes dins aquest bloc. Per moure un problema a un altre bloc, usa el menú «Mou a…» del costat.</p>
                    @endif
                @endauth
                @if($block->questions->isEmpty())
                    <p class="text-sm text-gray-500">Cap problema assignat.</p>
                @else
                    <ul id="questions-list" data-block-id="{{ $block->id }}" class="space-y-1">
                        @foreach($block->questions as $q)
                            <li class="{{ ($teacherEditable) ? 'border rounded px-2 py-1 bg-white flex items-center gap-2' : 'px-2 py-1 flex items-center gap-2' }}" data-qid="{{ $q->id }}" draggable="{{ $teacherEditable ? 'true' : 'false' }}">
                                @auth
                                    @if($teacherEditable)
                                        <span class="cursor-move select-none text-gray-500">≡</span>
                                    @endif
                                @endauth
                                <span aria-hidden="true" class="text-blue-500">❓</span>
                                <div class="flex-1">
                                    <a href="{{ route('questions.show', $q) }}" class="hover:underline">{{ $q->title }}</a>
                                    <span class="text-xs text-gray-500">({{ $q->year }} · {{ $q->level }})</span>
                                </div>
                                @auth
                                    @if($teacherEditable)
                                        <div class="flex items-center gap-1 text-sm">
                                            <label class="sr-only">Mou a…</label>
                                            <form class="move-form inline-flex items-center gap-1" onsubmit="return false;">
                                                <button type="button" class="move-btn px-2 py-1 border rounded">Mou a…</button>
                                                <div class="relative">
                                                    <select class="move-select appearance-none border rounded pl-2 pr-8 py-1 min-w-[10rem]">
                                                        <option value="">– Cap –</option>
                                                        @foreach($allBlocks as $b)
                                                            <option value="{{ $b->id }}" @selected($b->id === $block->id)>{{ $b->name }}</option>
                                                        @endforeach
                                                    </select>
                                                    <span class="pointer-events-none absolute inset-y-0 right-2 flex items-center text-gray-500">▾</span>
                                                </div>
                                            </form>
                                        </div>
                                    @endif
                                @endauth
                            </li>
                        @endforeach
                    </ul>
                    @auth
                        @if($teacherEditable)
                            <div class="flex items-center gap-2 mt-2">
                                <button id="save-q-order" class="px-3 py-1.5 bg-green-600 text-white rounded hover:bg-green-700">Desar ordre</button>
                                <span id="save-q-status" class="text-sm text-gray-600"></span>
                            </div>
                        @endif
                    @endauth
                @endif

                <div>
                    <a href="{{ route('questions.index') }}" class="inline-block mt-4 px-4 py-2 border rounded">Tornar</a>
                </div>

                @auth
                    @if($teacherEditable)
                        <script>
                            (function(){
                                const list = document.getElementById('questions-list');
                                if (!list) return;
                                let dragged = null;
                                list.addEventListener('dragstart', function(e){
                                    const li = e.target.closest('li[data-qid]');
                                    if (!li) return; dragged = li; e.dataTransfer.effectAllowed = 'move'; e.dataTransfer.setData('text/plain', li.dataset.qid);
                                });
                                list.addEventListener('dragover', function(e){
                                    const li = e.target.closest('li[data-qid]');
                                    if (!li) return; e.preventDefault(); e.dataTransfer.dropEffect = 'move';
                                });
                                list.addEventListener('drop', function(e){
                                    const target = e.target.closest('li[data-qid]');
                                    if (!target || !dragged || target === dragged) return; e.preventDefault();
                                    const rect = target.getBoundingClientRect(); const before = (e.clientY - rect.top) < (rect.height/2);
                                    if (before) target.parentElement.insertBefore(dragged, target); else target.parentElement.insertBefore(dragged, target.nextSibling);
                                });

                                function buildItems(){
                                    const items = []; const blockId = parseInt(list.dataset.blockId, 10) || null;
                                    Array.from(list.children).forEach((li, idx) => {
                                        if (!li.matches('li[data-qid]')) return; items.push({ id: parseInt(li.dataset.qid,10), block_id: blockId, position: idx });
                                    });
                                    return items;
                                }
                                const saveBtn = document.getElementById('save-q-order'); const statusEl = document.getElementById('save-q-status');
                                if (saveBtn) {
                                    saveBtn.addEventListener('click', async function(){
                                        const items = buildItems(); statusEl.textContent = 'Desant...';
                                        try {
                                            const res = await fetch('{{ route('questions.reorder') }}', { method:'POST', headers:{ 'Content-Type':'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' }, body: JSON.stringify({ items }) });
                                            if (!res.ok) throw new Error('Error '+res.status);
                                            statusEl.textContent = 'Canvis desats'; setTimeout(()=> statusEl.textContent = '', 2000);
                                        } catch(e) { statusEl.textContent = 'No s\'ha pogut desar'; }
                                    });
                                }

                                // Move to via select + button
                                list.addEventListener('click', async function(e){
                                    if (!e.target.classList.contains('move-btn')) return;
                                    const li = e.target.closest('li[data-qid]');
                                    const select = li.querySelector('.move-select');
                                    const blockId = select.value ? parseInt(select.value,10) : null;
                                    const payload = { items: [{ id: parseInt(li.dataset.qid,10), block_id: blockId, position: 999999 }] };
                                    e.target.disabled = true;
                                    try {
                                        const res = await fetch('{{ route('questions.reorder') }}', { method:'POST', headers:{ 'Content-Type':'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' }, body: JSON.stringify(payload) });
                                        if (!res.ok) throw new Error('Error '+res.status);
                                        // Remove from current list if moved elsewhere
                                        if (blockId !== (parseInt(list.dataset.blockId,10) || null)) {
                                            li.parentElement.removeChild(li);
                                        }
                                    } catch (err) {
                                        alert('No s\'ha pogut moure.');
                                    } finally {
                                        e.target.disabled = false;
                                    }
                                });
                            })();
                        </script>
                    @endif
                @endauth
            </div>
        </div>
    </div>
</x-layouts.app>
