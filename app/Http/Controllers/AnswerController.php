<?php

namespace App\Http\Controllers;

use App\Models\Answer;
use App\Models\Question;
use Illuminate\Http\Request;

/**
 * AnswerController
 */
class AnswerController extends Controller
{
    /**
     * Show the form to create an answer for a given Question.
     */
    public function create(Question $question)
    {
        return view('answers.create', compact('question'));
    }

    /**
     * Persist a new Answer related to the given Question.
     */
    public function store(Request $request, Question $question)
    {
        $validated = $request->validate([
            'solution'     => ['nullable','regex:/^(0000|[1-9]\d{0,3})$/'],
            'format'       => ['required', 'in:text,video,image'],
            'content_text' => ['nullable', 'required_if:format,text', 'string'],
            'video_url'    => ['nullable' , 'required_if:format,video', 'url'],
            'image'        => ['nullable', 'required_if:format,image', 'image', 'mimes:jpg,jpeg,png,gif,svg', 'max:2048'],
        ]);

        $attributes = [
            'user_id'     => auth()->id(),
            'question_id' => $question->id,
            'format'      => $validated['format'],
            'content_text'=> $validated['content_text'] ?? null,
            'video_url'   => $validated['video_url'] ?? null,
            'image_path'  => null,
        ];

        if ($attributes['format'] === 'image') {
            $attributes['image_path'] = $request->file('image')->store('answers', 'public');
        }

        if ($validated['solution'] === '0000') {
            $attributes['no_solution'] = true;
            $attributes['solution'] = null;
        } elseif ($validated['solution'] !== null) {
            $attributes['no_solution'] = false;
            $attributes['solution'] = (int) $validated['solution'];
        }

        $answer = Answer::create($attributes);

        return redirect()->route('answers.show', $answer)->with('ok', 'Resposta enviada!');
    }

    /** Display an Answer. */
    public function show(Answer $answer)
    {
        $this->authorize('view', $answer);
        $answer->load(['user','question']);
        return view('answers.show', compact('answer'));
    }

    /** Show edit form */
    public function edit(Answer $answer)
    {
        $this->authorize('update', $answer);
        $answer->load('question');
        return view('answers.edit', compact('answer'));
    }

    /** Update an answer */
    public function update(Request $request, Answer $answer)
    {
        $this->authorize('update', $answer);

        $validated = $request->validate([
            'solution'     => ['nullable','regex:/^(0000|[1-9]\d{0,3})$/'],
            'format'       => ['required', 'in:text,video,image'],
            'content_text' => ['nullable', 'required_if:format,text', 'string'],
            'video_url'    => ['nullable' , 'required_if:format,video', 'url'],
            'image'        => ['nullable', 'required_if:format,image', 'image', 'mimes:jpg,jpeg,png,gif,svg', 'max:2048'],
        ]);

        $attributes = [
            'format'      => $validated['format'],
            'content_text'=> $validated['content_text'] ?? null,
            'video_url'   => $validated['video_url'] ?? null,
        ];

        if ($attributes['format'] === 'image') {
            $attributes['image_path'] = $request->file('image')->store('answers', 'public');
        }

        if ($validated['solution'] === '0000') {
            $attributes['no_solution'] = true;
            $attributes['solution'] = null;
        } elseif ($validated['solution'] !== null) {
            $attributes['no_solution'] = false;
            $attributes['solution'] = (int) $validated['solution'];
        }

        $answer->update($attributes);

        return redirect()->route('answers.show', $answer)->with('ok', 'Resposta actualitzada!');
    }

    /** Delete an answer */
    public function destroy(Answer $answer)
    {
        $this->authorize('delete', $answer);
        $question = $answer->question;
        $answer->delete();
        return redirect()->route('questions.show', $question)->with('ok', 'Resposta eliminada');
    }
}
