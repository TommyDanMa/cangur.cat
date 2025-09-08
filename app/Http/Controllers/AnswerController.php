<?php

namespace App\Http\Controllers;

use App\Models\Answer;
use App\Models\Question;
use Illuminate\Http\Request;

class AnswerController extends Controller
{
    public function create(Question $question)
    {
        return view('answers.create', compact('question'));
    }

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

        // Map solution field according to regex rule
        $rawSolution = $data['solution'] ?? null;
        if ($rawSolution === '0000') {
            $payload['no_solution'] = true;
            $payload['solution'] = null;
        } elseif ($rawSolution !== null) {
            $payload['no_solution'] = false;
            $payload['solution'] = (int) $rawSolution;
        }

        $answer = Answer::create($payload);

        return redirect()
            ->route('answers.show', $answer)
            ->with('ok', 'Resposta enviada!');
    }


    public function show(Answer $answer)
    {
        $answer->load(['user','question']);
        return view('answers.show', compact('answer'));
    }
}
