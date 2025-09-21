<?php

namespace App\Http\Controllers;

use App\Models\Block;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class BlockController extends Controller
{
    /** List blocks with optional filters */
    public function index(Request $request)
    {
        $filters = $request->only(['year','level','visible']);

        if (!Schema::hasTable('blocks') || !Schema::hasColumn('blocks', 'parent_id') || !Schema::hasColumn('blocks', 'position')) {
            $roots = collect();
        } else {
            $roots = Block::with(['children' => function ($q) { $q->with('children'); }])
                ->whereNull('parent_id')
                ->orderBy('position')
                ->filter($filters)
                ->get();
        }

        return view('blocks.index', [
            'blocks' => $roots,
            'filters' => $filters,
        ]);
    }

    /** Show create form */
    public function create()
    {
        abort_unless(auth()->user()->role === 'teacher', 403);
        // Order by name when available; fall back to id for legacy schemas
        $parents = Schema::hasColumn('blocks', 'name') ? Block::orderBy('name')->get() : Block::orderBy('id')->get();
        return view('blocks.create', compact('parents'));
    }

    /** Store new block */
    public function store(Request $request)
    {
        abort_unless(auth()->user()->role === 'teacher', 403);
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'parent_id' => 'nullable|exists:blocks,id',
            'year' => 'nullable|integer',
            'level' => 'nullable|string|max:20',
            'visible' => 'nullable|boolean',
        ]);
        $data['visible'] = (bool) ($data['visible'] ?? true);
        $data['position'] = (int) (Block::where('parent_id', $data['parent_id'] ?? null)->max('position') ?? 0) + 1;
        $block = Block::create($data);
        return redirect()->route('blocks.show', $block)->with('ok', 'Bloc creat');
    }

    /** Show single block */
    public function show(Block $block)
    {
        $block->load([
            'children' => function ($q) { $q->with('children'); },
            'questions' => function ($q) { $q->orderBy('position')->orderBy('created_at'); }
        ]);
        $allBlocks = Block::orderBy('name')->get(['id','name']);
        return view('blocks.show', compact('block','allBlocks'));
    }

    /** Edit form */
    public function edit(Block $block)
    {
        abort_unless(auth()->user()->role === 'teacher', 403);
        $parentsQuery = Block::where('id', '!=', $block->id);
        $parents = Schema::hasColumn('blocks', 'name') ? $parentsQuery->orderBy('name')->get() : $parentsQuery->orderBy('id')->get();
        return view('blocks.edit', compact('block','parents'));
    }

    /** Update */
    public function update(Request $request, Block $block)
    {
        abort_unless(auth()->user()->role === 'teacher', 403);
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'parent_id' => 'nullable|exists:blocks,id',
            'year' => 'nullable|integer',
            'level' => 'nullable|string|max:20',
            'visible' => 'nullable|boolean',
        ]);
        $data['visible'] = (bool) ($data['visible'] ?? true);
        $block->update($data);
        return redirect()->route('blocks.show', $block)->with('ok', 'Bloc actualitzat');
    }

    /** Destroy */
    public function destroy(Block $block)
    {
        abort_unless(auth()->user()->role === 'teacher', 403);
        // On delete: move children to parent and keep positions at end; move questions to parent (or root)
        DB::transaction(function () use ($block) {
            $parentId = $block->parent_id;

            // Reparent child blocks to this block's parent
            $children = $block->children()->orderBy('position')->get();
            foreach ($children as $child) {
                $child->parent_id = $parentId;
                $child->position = (int) (Block::where('parent_id', $parentId)->max('position') ?? 0) + 1;
                $child->save();
            }

            // Move questions to parent block if exists, else unassign to root (index)
            if ($parentId) {
                $pos = (int) (\App\Models\Question::where('block_id', $parentId)->max('position') ?? 0);
                foreach ($block->questions()->orderBy('position')->get() as $q) {
                    $q->block_id = $parentId;
                    $q->position = ++$pos;
                    $q->save();
                }
            } else {
                // Move questions to root (unassigned) and append at the end preserving order
                $pos = (int) (\App\Models\Question::whereNull('block_id')->max('position') ?? 0);
                foreach ($block->questions()->orderBy('position')->get() as $q) {
                    $q->block_id = null;
                    $q->position = ++$pos;
                    $q->save();
                }
            }

            $block->delete();
        });
        return redirect()->route('questions.index')->with('ok', 'Bloc eliminat');
    }

    /** Reorder & reparent endpoint
     * Expects JSON: [{id:number, parent_id:number|null, position:number}, ...]
     */
    public function reorder(Request $request)
    {
        abort_unless(auth()->user()->role === 'teacher', 403);
        $items = $request->validate([
            'items' => 'required|array',
            'items.*.id' => 'required|integer|exists:blocks,id',
            'items.*.parent_id' => 'nullable|integer|exists:blocks,id',
            'items.*.position' => 'required|integer|min:0',
        ])['items'];

        DB::transaction(function () use ($items) {
            foreach ($items as $row) {
                Block::where('id', $row['id'])->update([
                    'parent_id' => $row['parent_id'] ?? null,
                    'position' => $row['position'],
                ]);
            }
        });

        return response()->json(['ok' => true]);
    }

    /** Public JSON contents of a block: direct children and questions */
    public function contents(Block $block)
    {
        // Keep it simple and public; students will use links, teachers can DnD questions
        $children = $block->children()->orderBy('position')->get(['id','name']);
        $questions = $block->questions()->orderBy('position')->orderBy('created_at')->get(['id','title','year','level']);
        return response()->json([
            'children' => $children,
            'questions' => $questions,
        ]);
    }
}
