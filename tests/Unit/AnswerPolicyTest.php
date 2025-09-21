<?php

namespace Tests\Unit;

use App\Models\Answer;
use App\Models\Question;
use App\Models\User;
use App\Policies\AnswerPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Unit tests for AnswerPolicy: who can view, update, and delete answers.
 */
class AnswerPolicyTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function owner_can_view_their_answer_and_teacher_can_view_any_answer(): void
    {
        $student = User::factory()->create(['role' => 'student']);
        $teacher = User::factory()->create(['role' => 'teacher']);
        $answer = Answer::factory()->create(['user_id' => $student->id]);

        $policy = new AnswerPolicy();

        $this->assertTrue($policy->view($student, $answer), 'Owners should be able to view their own answers.');
        $this->assertTrue($policy->view($teacher, $answer), 'Teachers should be able to view any answer.');
    }

    /** @test */
    public function student_who_also_answered_same_question_can_view_other_students_answers(): void
    {
        $q = Question::factory()->create();
        $alice = User::factory()->create(['role' => 'student']);
        $bob = User::factory()->create(['role' => 'student']);
        $answerAlice = Answer::factory()->create(['user_id' => $alice->id, 'question_id' => $q->id]);
        $answerBob = Answer::factory()->create(['user_id' => $bob->id, 'question_id' => $q->id]);

        $policy = new AnswerPolicy();

        // Alice can view Bob's answer because she also answered the same question
        $this->assertTrue($policy->view($alice, $answerBob));
        // And Bob can view Alice's
        $this->assertTrue($policy->view($bob, $answerAlice));
    }

    /** @test */
    public function stranger_student_cannot_view_others_answers_without_answering_the_question(): void
    {
        $student = User::factory()->create(['role' => 'student']);
        $answer = Answer::factory()->create();

        $policy = new AnswerPolicy();
        $this->assertFalse($policy->view($student, $answer));
    }

    /** @test */
    public function owner_can_update_or_delete_within_15_minutes_only(): void
    {
        $student = User::factory()->create(['role' => 'student']);
        $recent = Answer::factory()->create(['user_id' => $student->id]);
        $old = Answer::factory()->create(['user_id' => $student->id]);
        $old->update(['created_at' => now()->subMinutes(16)]);

        $policy = new AnswerPolicy();

        $this->assertTrue($policy->update($student, $recent));
        $this->assertTrue($policy->delete($student, $recent));
        $this->assertFalse($policy->update($student, $old));
        $this->assertFalse($policy->delete($student, $old));
    }

    /** @test */
    public function teachers_can_update_and_delete_any_answer_regardless_of_time(): void
    {
        $teacher = User::factory()->create(['role' => 'teacher']);
        $answer = Answer::factory()->create();
        $answer->update(['created_at' => now()->subDays(10)]);

        $policy = new AnswerPolicy();

        $this->assertTrue($policy->update($teacher, $answer));
        $this->assertTrue($policy->delete($teacher, $answer));
    }
}
