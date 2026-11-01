<?php

namespace App\Policies;

use App\Models\Question;
use App\Models\User;

class QuestionPolicy
{
    /**
     * Determine whether the user can view answers of a question.
     */
    public function viewAnswers(?User $user, Question $question): bool
    {
        if (!$user) return false;

        $hasAnswered = $question->answers()->where('user_id', $user->id)->exists();
        return $hasAnswered || $user->can('teacher');
    }
}
