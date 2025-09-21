<?php

namespace Tests\Feature\Questions;

use App\Models\Answer;
use App\Models\Question;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Feature tests verifying who can see a question's answers and in what order.
 */
class QuestionShowAnswersTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function student_who_answered_can_see_all_answers_ordered_with_teachers_first_then_oldest(): void
    {
        $teacher = User::factory()->create(['role' => 'teacher']);
        $alice = User::factory()->create(['role' => 'student']);
        $bob = User::factory()->create(['role' => 'student']);
        $q = Question::factory()->create();

        // Create answers: make timestamps explicit to test ordering
        $a_teacher = Answer::factory()->create([
            'user_id' => $teacher->id,
            'question_id' => $q->id,
            'format' => 'text',
            'content_text' => 'Teacher answer',
            'created_at' => now()->subMinutes(2),
        ]);
        $a_alice = Answer::factory()->create([
            'user_id' => $alice->id,
            'question_id' => $q->id,
            'format' => 'text',
            'content_text' => 'Alice answer',
            'created_at' => now()->subMinutes(3),
        ]);
        $a_bob = Answer::factory()->create([
            'user_id' => $bob->id,
            'question_id' => $q->id,
            'format' => 'text',
            'content_text' => 'Bob answer',
            'created_at' => now()->subMinute(),
        ]);

        $resp = $this->actingAs($alice)->get(route('questions.show', $q));
        $resp->assertOk();

        // Can see answers
        $resp->assertViewHas('canSeeAnswers', true);
        $resp->assertViewHas('answers', function ($answers) use ($a_teacher, $a_alice, $a_bob) {
            $ids = $answers->pluck('id')->all();
            // Expected order: teacher first (regardless of time), then students oldest first (Alice then Bob)
            return $ids === [$a_teacher->id, $a_alice->id, $a_bob->id];
        });
    }

    /** @test */
    public function student_who_did_not_answer_cannot_see_answers(): void
    {
        $student = User::factory()->create(['role' => 'student']);
        $q = Question::factory()->create();
        Answer::factory()->create(['question_id' => $q->id]);

        $resp = $this->actingAs($student)->get(route('questions.show', $q));
        $resp->assertOk();
        $resp->assertViewHas('canSeeAnswers', false);
        $resp->assertViewHas('answers', function ($answers) {
            return $answers->count() === 0;
        });
    }

    /** @test */
    public function teacher_can_see_all_answers(): void
    {
        $teacher = User::factory()->create(['role' => 'teacher']);
        $q = Question::factory()->create();
        Answer::factory()->count(3)->create(['question_id' => $q->id]);

        $resp = $this->actingAs($teacher)->get(route('questions.show', $q));
        $resp->assertOk();
        $resp->assertViewHas('canSeeAnswers', true);
        $resp->assertViewHas('answers', function ($answers) {
            return $answers->count() === 3;
        });
    }
}
