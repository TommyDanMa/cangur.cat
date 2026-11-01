<?php

namespace App\Http\Controllers;

use App\Models\Question;
use App\Models\Block;
use Illuminate\Http\Request;

class QuestionController extends Controller
{
    /** List questions with optional filters */
    public function index()
    {
        $q = Question::query();
        if (request('year'))  $q->where('year', 'LIKE', (int) request('year') . '%');
        if (request('level')) $q->where('level', 'LIKE', '%' . request('level') . '%');
        if (request('phase')) $q->where('phase', 'LIKE', '%' . request('phase') . '%');
        if (request('session')) $q->where('session', 'LIKE', '%' . request('session') . '%');

        return view('questions.index', [
            'questions' => $q->orderBy('created_at')->simplePaginate(10),
        ]);
    }

    /** Show the form to create a new question. */
    public function create()
    {
        return view('questions.create');
    }

    /** Store a newly created Question. */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title'     => 'required|string|max:255',
            'statement' => 'required|string',
            'year'      => 'required|integer',
            'level'     => 'required|string|max:20',
            'phase'     => 'required|in:local,semifinal,final',
            'session'   => 'nullable|in:mati,tarda|required_if:phase,local',
            'image'     => 'nullable|image|mimes:jpg,jpeg,png,gif,svg|max:2048',
        ]);

        if ($request->hasFile('image')) {
            $validated['image_path'] = $request->file('image')->store('questions', 'public');
        }

        $validated['created_by'] = auth()->id();

        Question::create($validated);

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
        return view('questions.edit', compact('question'));
    }

    /** Update a question */
    public function update(Request $request, Question $question)
    {
        $validated = $request->validate([
            'title'     => 'required|string|max:255',
            'statement' => 'required|string',
            'year'      => 'required|integer',
            'level'     => 'required|string|max:20',
            'phase'     => 'required|in:local,semifinal,final',
            'session'   => 'nullable|in:mati,tarda|required_if:phase,local',
            'image'     => 'nullable|image|mimes:jpg,jpeg,png,gif,svg|max:2048',
        ]);

        if ($request->hasFile('image')) {
            $validated['image_path'] = $request->file('image')->store('questions', 'public');
        }

        $question->update($validated);

        return redirect()->route('questions.show', $question)->with('ok', 'Problema actualitzat');
    }

    /** Delete a question */
    public function destroy(Question $question)
    {
        $question->delete();
        return redirect()->route('questions.index')->with('ok', 'Problema eliminat');
    }

    /**
     * Get suggestions for question fields (year, level, phase, session).
     */
    public function suggest(Request $request)
    {
        $field = $request->query('field');
        $q = (string) $request->query('q', '');

        abort_unless(in_array($field, ['year', 'level', 'phase', 'session'], true), 400);

        $query = Question::query()
            ->select($field)
            ->whereNotNull($field)
            ->distinct();

        if ($q !== '') {
            if ($field === 'year') {
                $query->where($field, 'like', "{$q}%");
            } else {
                $query->where($field, 'like', "%{$q}%");
            }
        }

        $values = $query->orderBy($field)->limit(20)->pluck($field);

        return response()->json(
            $values->map(fn($v) => ['id' => (string) $v, 'label' => (string) $v])->toArray()
        );
    }
}
