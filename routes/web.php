<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\QuestionController;
use App\Http\Controllers\AnswerController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn() => redirect()->route('questions.index'));

// Endpoint de sugerencias
Route::get('/questions/suggest', [QuestionController::class, 'suggest'])->name('questions.suggest');

Route::middleware('auth')->group(function () {
    Route::middleware('can:teacher')->group(function () {
        // Questions CRUD (teachers): create, store, edit, update, destroy
        Route::resource('questions', QuestionController::class)->only(['create','store', 'edit', 'update', 'destroy']);
    });
    // Answers: view, create, edit, update, destroy
    Route::resource('answers', AnswerController::class)->only(['show','edit','update','destroy']);

    Route::view('/home', 'home')->name('home');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/questions/{question}/answers/create', [AnswerController::class, 'create'])->name('answers.create');
    Route::post('/questions/{question}/answers', [AnswerController::class, 'store'])->name('answers.store');
});

Route::get('/questions', [QuestionController::class, 'index'])->name('questions.index');
Route::get('/questions/{question}', [QuestionController::class, 'show'])->name('questions.show');

require __DIR__.'/auth.php';
