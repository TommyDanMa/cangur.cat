<?php

namespace App\Policies;

use App\Models\Answer;
use App\Models\User;

class AnswerPolicy
{
    /**
     * Determine whether the user can view the model.
     */
    public function view(?User $user, Answer $answer): bool
    {
        if (!$user) return false;

        if ($answer->user_id === $user->id) return true;

        $question = $answer->question;
        $hasAnswered = $question->answers()->where('user_id', $user->id)->exists();

        return $hasAnswered || ($user->role === 'teacher');
    }

    /**
     * Determine whether the user can update the answer.
     */
    public function update(User $user, Answer $answer): bool
    {
        if ($user->role === 'teacher') return true; // professors can edit any answer
        if ($answer->user_id !== $user->id) return false;
        // owners can edit within 15 minutes of submission
        return now()->diffInMinutes($answer->created_at) <= 15;
    }

    /**
     * Determine whether the user can delete the answer.
     */
    public function delete(User $user, Answer $answer): bool
    {
        if ($user->role === 'teacher') return true; // professors can delete any answer
        if ($answer->user_id !== $user->id) return false;
        // owners can delete within 15 minutes of submission
        return now()->diffInMinutes($answer->created_at) <= 15;
    }
}
