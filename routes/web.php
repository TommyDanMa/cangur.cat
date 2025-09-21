<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\QuestionController;
use App\Http\Controllers\AnswerController;
use App\Http\Controllers\BlockController;
use Illuminate\Http\Request;
use App\Models\Question;

Route::get('/', fn() => redirect()->route('questions.index'));

// Endpoint de sugerencias: DEBE ir antes del resource para no ser atrapado por /questions/{question}
Route::get('/questions/suggest', function (Request $request) {
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
})->name('questions.suggest');

// Blocks suggestions for typeahead (year, level)
Route::get('/blocks/suggest', function (\Illuminate\Http\Request $request) {
    $field = $request->query('field');
    $q = (string) $request->query('q', '');

    abort_unless(in_array($field, ['year', 'level'], true), 400);

    $query = \App\Models\Block::query()
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
})->name('blocks.suggest');

// Remove Blocks index: redirect /blocks to questions.index; keep /blocks/{block} show
Route::get('/blocks', fn() => redirect()->route('questions.index'))->name('blocks.index');
Route::resource('blocks', BlockController::class)->only(['show']);
// Public JSON endpoint to load a block's direct contents (children + questions)
Route::get('/blocks/{block}/contents', [BlockController::class, 'contents'])->name('blocks.contents');

// Unassigned questions JSON (public). Mirrors blocks.contents shape with only questions
Route::get('/questions/unassigned', function (Request $request) {
    $query = Question::query()->whereNull('block_id');
    if ($request->filled('year'))  $query->where('year', (int) $request->query('year'));
    if ($request->filled('level')) $query->where('level', $request->query('level'));
    if ($request->filled('phase')) $query->where('phase', $request->query('phase'));
    if ($request->filled('session')) $query->where('session', $request->query('session'));

    $questions = $query->orderBy('created_at', 'desc')->limit(100)->get(['id','title','year','level']);
    return response()->json([
        'children' => [],
        'questions' => $questions,
    ]);
})->name('questions.unassigned');

Route::middleware('auth')->group(function () {
    // Teacher UI mode toggle (edit/view)
    Route::post('/ui/teacher-mode', function (Request $request) {
        abort_unless(auth()->check() && auth()->user()->role === 'teacher', 403);
        $data = $request->validate(['mode' => 'required|in:edit,view']);
        session(['teacher_mode' => $data['mode']]);
        return back();
    })->name('ui.teacher_mode');
    // Questions CRUD (auth): create, edit, update, destroy
    Route::resource('questions', QuestionController::class)->only(['create','store']);
    Route::get('/questions/{question}/edit', [QuestionController::class, 'edit'])->name('questions.edit');
    Route::put('/questions/{question}', [QuestionController::class, 'update'])->name('questions.update');
    Route::delete('/questions/{question}', [QuestionController::class, 'destroy'])->name('questions.destroy');

    // Blocks CRUD (teachers)
    Route::resource('blocks', BlockController::class)->except(['index','show']);
    Route::post('/blocks/reorder', [BlockController::class, 'reorder'])->name('blocks.reorder');

    // Questions reorder (teachers): move between blocks and order within
    Route::post('/questions/reorder', [QuestionController::class, 'reorder'])->name('questions.reorder');

    // Answers: view, create, edit, update, destroy
    Route::get('/answers/{answer}', [AnswerController::class, 'show'])->name('answers.show');
    Route::get('/answers/{answer}/edit', [AnswerController::class, 'edit'])->name('answers.edit');
    Route::put('/answers/{answer}', [AnswerController::class, 'update'])->name('answers.update');
    Route::delete('/answers/{answer}', [AnswerController::class, 'destroy'])->name('answers.destroy');

    Route::get('/home', function () { return view('home'); })->name('home');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/questions/{question}/answers/create', [AnswerController::class, 'create'])->name('answers.create');
    Route::post('/questions/{question}/answers', [AnswerController::class, 'store'])->name('answers.store');
});

Route::resource('questions', QuestionController::class)->only(['index','show']);

require __DIR__.'/auth.php';
