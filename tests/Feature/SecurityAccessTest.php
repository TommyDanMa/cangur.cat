<?php

namespace Tests\Feature;

use App\Models\Answer;
use App\Models\Question;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Security and permissions smoke tests.
 */
class SecurityAccessTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function guest_is_redirected_to_login_on_protected_routes(): void
    {
        $q = Question::factory()->create();
        $this->get(route('questions.create'))->assertRedirect('/login');
        $this->post(route('questions.store'))->assertRedirect('/login');
        $this->get(route('answers.create', $q))->assertRedirect('/login');
    }

    /** @test */
    public function student_cannot_toggle_teacher_mode_or_manage_questions(): void
    {
        $student = User::factory()->create(['role' => 'student']);
        $q = Question::factory()->create();

        $this->actingAs($student)->post(route('ui.teacher_mode'), ['mode' => 'edit'])->assertForbidden();
        $this->actingAs($student)->get(route('questions.create'))->assertForbidden();
        $this->actingAs($student)->get(route('questions.edit', $q))->assertForbidden();
        $this->actingAs($student)->delete(route('questions.destroy', $q))->assertForbidden();
    }

    /** @test */
    public function student_cannot_view_others_answer_unless_they_answered_the_same_question(): void
    {
        $alice = User::factory()->create(['role' => 'student']);
        $bob = User::factory()->create(['role' => 'student']);
        $q = Question::factory()->create();
        $bobsAnswer = Answer::factory()->create(['user_id' => $bob->id, 'question_id' => $q->id]);

        // Alice hasn't answered this question yet -> 403
        $this->actingAs($alice)->get(route('answers.show', $bobsAnswer))->assertForbidden();

        // After Alice answers the same question, she can view Bob's answer
        $alicesAnswer = Answer::factory()->create(['user_id' => $alice->id, 'question_id' => $q->id]);
        $this->actingAs($alice)->get(route('answers.show', $bobsAnswer))->assertOk();
    }
}
