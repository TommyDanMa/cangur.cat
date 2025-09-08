<?php

namespace App\Http\Controllers;

use App\Models\Question;
use Illuminate\Http\Request;

class QuestionController extends Controller
{
    public function index()
    {
        $q = Question::query();
        if (request('year'))  $q->where('year', (int) request('year'));
        if (request('level')) $q->where('level', request('level'));
        if (request('phase')) $q->where('phase', request('phase'));
        if (request('session')) $q->where('session', request('session'));

        return view('questions.index', ['questions' => $q->latest()->paginate(10)]);
    }

    public function create()
    {
        abort_unless(auth()->user()->role === 'teacher', 403);
        return view('questions.create');
    }

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
            'image'     => 'nullable|image|mimes:jpg,jpeg,png,gif,svg|max:2048',
        ]);

        if ($request->hasFile('image')) {
            $data['image_path'] = $request->file('image')->store('questions', 'public');
        }

        $data['created_by'] = auth()->id();

        Question::create($data);

        return redirect()->route('questions.index')->with('ok', 'Problema creat');
    }

    public function show(Question $question)
    {
        $question->load(['answers.user']);
        return view('questions.show', compact('question'));
    }
}
