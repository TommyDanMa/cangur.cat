<?php

use Illuminate\Support\Facades\Route;
use App\Models\Question;
use Illuminate\Http\Request;

Route::get('/questions/suggest', function (Request $request) {
    $field = $request->query('field');
    $q = (string) $request->query('q', '');

    abort_unless(in_array($field, ['year', 'level', 'phase', 'session'], true), 400);

    $query = Question::query()->select($field)->whereNotNull($field)->distinct();

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
})->name('api.questions.suggest');
