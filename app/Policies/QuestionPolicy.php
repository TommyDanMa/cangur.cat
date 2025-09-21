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
        return $hasAnswered || ($user->role === 'teacher');
    }

    /**
     * Determine whether the user can update the question.
     */
    public function update(User $user, Question $question): bool
    {
        // Professors can edit questions
        return $user->role === 'teacher';
    }

    /**
     * Determine whether the user can delete the question.
     */
    public function delete(User $user, Question $question): bool
    {
        // Professors can delete any question
        return $user->role === 'teacher';
    }
}
