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
        $messages = [
            'format.required' => 'Has d\'escollir un format.',
            'format.in'       => 'Format invàlid.',
            'content_text.required_if' => 'Escriu la resposta en text.',
            'video_url.required_if'    => 'Indica la URL del vídeo.',
            'video_url.url'            => 'La URL del vídeo no és vàlida.',
            'image.required_if'        => 'Puja una imatge amb la teva resposta.',
            'image.image'              => 'El fitxer ha de ser una imatge.',
            'image.mimes'              => 'Formats permesos: JPG, JPEG, PNG, GIF, SVG.',
            'image.max'                => 'La imatge és massa gran (màx. 2MB).',
            'solution.regex'           => 'La solució ha de ser "0000" o un enter entre 1 i 9999.',
        ];

        $data = $request->validate([
            'solution'     => ['nullable','regex:/^(0000|[1-9]\d{0,3})$/'],
            'format'       => 'required|in:text,video,image',
            'content_text' => 'nullable|required_if:format,text|string',
            'video_url'    => 'nullable|required_if:format,video|url',
            'image'        => 'nullable|required_if:format,image|image|mimes:jpg,jpeg,png,gif,svg|max:2048',
        ], $messages);

        $payload = [
            'user_id'     => auth()->id(),
            'question_id' => $question->id,
            'format'      => $data['format'],
            'content_text'=> $data['content_text'] ?? null,
            'video_url'   => $data['video_url'] ?? null,
            'image_path'  => null,
        ];

        if ($request->hasFile('image')) {
            $payload['image_path'] = $request->file('image')->store('answers', 'public');
        }

        $rawSolution = $data['solution'] ?? null;
        if ($rawSolution === '0000') {
            $payload['no_solution'] = true;
            $payload['solution'] = null;
        } elseif ($rawSolution !== null) {
            $payload['no_solution'] = false;
            $payload['solution'] = (int) $rawSolution;
        }

        $answer = Answer::create($payload);

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

        $messages = [
            'format.required' => 'Has d\'escollir un format.',
            'format.in'       => 'Format invàlid.',
            'content_text.required_if' => 'Escriu la resposta en text.',
            'video_url.required_if'    => 'Indica la URL del vídeo.',
            'video_url.url'            => 'La URL del vídeo no és vàlida.',
            'image.image'              => 'El fitxer ha de ser una imatge.',
            'image.mimes'              => 'Formats permesos: JPG, JPEG, PNG, GIF, SVG.',
            'image.max'                => 'La imatge és massa gran (màx. 2MB).',
            'solution.regex'           => 'La solució ha de ser "0000" o un enter entre 1 i 9999.',
        ];

        $data = $request->validate([
            'solution'     => ['nullable','regex:/^(0000|[1-9]\d{0,3})$/'],
            'format'       => 'required|in:text,video,image',
            'content_text' => 'nullable|required_if:format,text|string',
            'video_url'    => 'nullable|required_if:format,video|url',
            'image'        => 'nullable|image|mimes:jpg,jpeg,png,gif,svg|max:2048',
        ], $messages);

        $payload = [
            'format'      => $data['format'],
            'content_text'=> $data['content_text'] ?? null,
            'video_url'   => $data['video_url'] ?? null,
        ];

        if ($request->hasFile('image')) {
            $payload['image_path'] = $request->file('image')->store('answers', 'public');
        }

        $rawSolution = $data['solution'] ?? null;
        if ($rawSolution === '0000') {
            $payload['no_solution'] = true;
            $payload['solution'] = null;
        } elseif ($rawSolution !== null) {
            $payload['no_solution'] = false;
            $payload['solution'] = (int) $rawSolution;
        }

        $answer->update($payload);

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
