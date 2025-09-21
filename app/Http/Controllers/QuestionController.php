<?php

namespace App\Http\Controllers;

use App\Models\Question;
use App\Models\Block;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class QuestionController extends Controller
{
    /** List questions with optional filters */
    public function index()
    {
        $q = Question::query();
        if (request('year'))  $q->where('year', (int) request('year'));
        if (request('level')) $q->where('level', request('level'));
        if (request('phase')) $q->where('phase', request('phase'));
        if (request('session')) $q->where('session', request('session'));

        // Only show questions that are not inside any block on the index
        $q->whereNull('block_id');

        // Load blocks tree for index page (like Drive sidebar). Read-only for students, editable for teachers.
        $blocks = collect();
        if (Schema::hasTable('blocks') && Schema::hasColumn('blocks','parent_id') && Schema::hasColumn('blocks','position')) {
            $blocks = Block::withCount('questions')
                ->with(['children' => function ($q) {
                    $q->withCount('questions')->with(['children' => function ($qq) {
                        $qq->withCount('questions'); // deeper levels get count too
                    }]);
                }])
                ->whereNull('parent_id')
                ->orderBy('position')
                ->get();
        }

        return view('questions.index', [
            'questions' => $q->orderBy('position')->orderBy('created_at')->paginate(10),
            'blocks' => $blocks,
        ]);
    }

    /** Show the form to create a new question. */
    public function create()
    {
        abort_unless(auth()->user()->role === 'teacher', 403);
        $blocks = Block::orderBy('name')->get(['id','name']);
        return view('questions.create', compact('blocks'));
    }

    /** Store a newly created Question. */
    public function store(Request $request)
    {
        abort_unless(auth()->user()->role === 'teacher', 403);

        $data = $request->validate([
            'title'     => 'required|string|max:255',
            'statement' => 'required|string',
            'year'      => 'required|integer',
            'level'     => 'required|string|max:20',
            'phase'     => 'required|in:local,semifinal,final',
            'session'   => 'nullable|in:mati,tarda|required_if:phase,local',
            'block_id'  => 'nullable|exists:blocks,id',
            'image'     => 'nullable|image|mimes:jpg,jpeg,png,gif,svg|max:2048',
        ]);

        if ($request->hasFile('image')) {
            $data['image_path'] = $request->file('image')->store('questions', 'public');
        }

        $data['created_by'] = auth()->id();
        // Assign position at end within selected block (or within unassigned group if none)
        if (!empty($data['block_id'])) {
            $data['position'] = (int) (Question::where('block_id', $data['block_id'])->max('position') ?? 0) + 1;
        } else {
            $data['position'] = (int) (Question::whereNull('block_id')->max('position') ?? 0) + 1;
        }
        Question::create($data);

        return redirect()->route('questions.index')->with('ok', 'Problema creat');
    }

    /** Show a single question and, if authorized, its answers. */
    public function show(Question $question)
    {
        $canSeeAnswers = auth()->check() && auth()->user()->can('viewAnswers', $question);

        $question->loadCount('answers');
        $answers = collect();
        if ($canSeeAnswers) {
            // Teacher answers first, then oldest first
            $answers = $question->answers()
                ->join('users', 'answers.user_id', '=', 'users.id')
                ->with('user')
                ->orderByRaw("CASE WHEN users.role = 'teacher' THEN 0 ELSE 1 END")
                ->orderBy('answers.created_at', 'asc')
                ->select('answers.*')
                ->get();
        }
        return view('questions.show', compact('question', 'answers', 'canSeeAnswers'));
    }

    /** Show edit form */
    public function edit(Question $question)
    {
        $this->authorize('update', $question);
        $blocks = Block::orderBy('name')->get(['id','name']);
        return view('questions.edit', compact('question','blocks'));
    }

    /** Update a question */
    public function update(Request $request, Question $question)
    {
        $this->authorize('update', $question);

        $data = $request->validate([
            'title'     => 'required|string|max:255',
            'statement' => 'required|string',
            'year'      => 'required|integer',
            'level'     => 'required|string|max:20',
            'phase'     => 'required|in:local,semifinal,final',
            'session'   => 'nullable|in:mati,tarda|required_if:phase,local',
            'block_id'  => 'nullable|exists:blocks,id',
            'image'     => 'nullable|image|mimes:jpg,jpeg,png,gif,svg|max:2048',
        ]);

        if ($request->hasFile('image')) {
            $data['image_path'] = $request->file('image')->store('questions', 'public');
        }

        // If block changed, move to end of target block (or 0 if unset)
        $oldBlock = $question->block_id;
        $newBlock = $data['block_id'] ?? null;
        if ($oldBlock !== $newBlock) {
            $data['position'] = $newBlock !== null
                ? ((int) (Question::where('block_id', $newBlock)->max('position') ?? 0) + 1)
                : ((int) (Question::whereNull('block_id')->max('position') ?? 0) + 1);
        }

        $question->update($data);

        return redirect()->route('questions.show', $question)->with('ok', 'Problema actualitzat');
    }

    /** Delete a question */
    public function destroy(Question $question)
    {
        $this->authorize('delete', $question);
        $question->delete();
        return redirect()->route('questions.index')->with('ok', 'Problema eliminat');
    }

    /** Reorder & reassign Questions (teacher only)
     * Expects JSON: { items: [{ id:number, block_id:number|null, position:number }, ...] }
     */
    public function reorder(Request $request)
    {
        abort_unless(auth()->check() && auth()->user()->role === 'teacher', 403);

        $items = $request->validate([
            'items' => 'required|array',
            'items.*.id' => 'required|integer|exists:questions,id',
            'items.*.block_id' => 'nullable|integer|exists:blocks,id',
            'items.*.position' => 'required|integer|min:0',
        ])['items'];

        DB::transaction(function () use ($items) {
            foreach ($items as $row) {
                Question::where('id', $row['id'])->update([
                    'block_id' => $row['block_id'] ?? null,
                    'position' => $row['position'],
                ]);
            }
        });

        return response()->json(['ok' => true]);
    }
}
