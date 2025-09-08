<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\QuestionController;
use App\Http\Controllers\AnswerController;
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

Route::middleware('auth')->group(function () {
    Route::resource('questions', QuestionController::class)->only(['create','store']);
    Route::get('/answers/{answer}', [AnswerController::class, 'show'])->name('answers.show');

    Route::get('/home', function () { return view('home'); })->name('home');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/questions/{question}/answers/create', [AnswerController::class, 'create'])->name('answers.create');
    Route::post('/questions/{question}/answers', [AnswerController::class, 'store'])->name('answers.store');
});

Route::resource('questions', QuestionController::class)->only(['index','show']);

require __DIR__.'/auth.php';
