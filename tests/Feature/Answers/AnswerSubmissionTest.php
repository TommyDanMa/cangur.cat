<?php

namespace Tests\Feature\Answers;

use App\Models\Answer;
use App\Models\Question;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Feature tests for submitting, editing, and deleting answers in text/video/image formats.
 * Includes validation and authorization edge cases.
 */
class AnswerSubmissionTest extends TestCase
{
    use RefreshDatabase;

    private function student(): User
    {
        return User::factory()->create(['role' => 'student']);
    }

    private function teacher(): User
    {
        return User::factory()->create(['role' => 'teacher']);
    }

    /** @test */
    public function student_can_submit_text_answer_and_mark_no_solution(): void
    {
        $student = $this->student();
        $q = Question::factory()->create();

        $resp = $this->actingAs($student)->post(route('answers.store', $q), [
            'format' => 'text',
            'content_text' => 'My reasoning...',
            'solution' => '0000', // special code meaning: “I don’t know the solution”
        ]);

        $answer = Answer::first();
        $resp->assertRedirect(route('answers.show', $answer));
        $this->assertDatabaseHas('answers', [
            'id' => $answer->id,
            'user_id' => $student->id,
            'question_id' => $q->id,
            'format' => 'text',
            'no_solution' => true,
        ]);
        $this->assertNull($answer->solution);
    }

    /** @test */
    public function student_can_submit_video_answer_with_valid_url_and_validation_fails_for_invalid_url(): void
    {
        $student = $this->student();
        $q = Question::factory()->create();

        // Invalid URL -> validation error
        $this->actingAs($student)
            ->post(route('answers.store', $q), [
                'format' => 'video',
                'video_url' => 'not-a-url',
            ])
            ->assertSessionHasErrors(['video_url']);

        // Valid URL -> success
        $resp = $this->actingAs($student)
            ->post(route('answers.store', $q), [
                'format' => 'video',
                'video_url' => 'https://youtu.be/xyz',
            ]);
        $answer = Answer::latest('id')->first();
        $resp->assertRedirect(route('answers.show', $answer));
        $this->assertSame('https://youtu.be/xyz', $answer->video_url);
    }

    /** @test */
    public function student_can_submit_image_answer_and_file_is_stored(): void
    {
        Storage::fake('public');
        $student = $this->student();
        $q = Question::factory()->create();

        $resp = $this->actingAs($student)
            ->post(route('answers.store', $q), [
                'format' => 'image',
                'image' => UploadedFile::fake()->image('work.png', 200, 200),
            ]);

        $answer = Answer::latest('id')->first();
        $resp->assertRedirect(route('answers.show', $answer));
        $this->assertNotNull($answer->image_path);
        Storage::disk('public')->assertExists($answer->image_path);
    }

    /** @test */
    public function empty_text_answer_is_rejected_and_invalid_format_is_rejected(): void
    {
        $student = $this->student();
        $q = Question::factory()->create();

        // Empty text when format=text
        $this->actingAs($student)
            ->post(route('answers.store', $q), [
                'format' => 'text',
                'content_text' => '',
            ])
            ->assertSessionHasErrors(['content_text']);

        // Invalid format
        $this->actingAs($student)
            ->post(route('answers.store', $q), [
                'format' => 'pdf',
            ])
            ->assertSessionHasErrors(['format']);
    }

    /** @test */
    public function guest_cannot_access_answer_routes(): void
    {
        $q = Question::factory()->create();
        $this->get(route('answers.create', $q))->assertRedirect('/login');
        $this->post(route('answers.store', $q), [])->assertRedirect('/login');
    }

    /** @test */
    public function teacher_can_edit_any_answer_and_student_cannot_after_window(): void
    {
        $teacher = $this->teacher();
        $student = $this->student();
        $q = Question::factory()->create();
        $answer = Answer::factory()->create(['user_id' => $student->id, 'question_id' => $q->id, 'format' => 'text', 'content_text' => 'Old']);

        // Teacher can update any answer
        $this->actingAs($teacher)
            ->put(route('answers.update', $answer), [
                'format' => 'text',
                'content_text' => 'Edited by teacher',
                'solution' => '42',
            ])
            ->assertRedirect(route('answers.show', $answer));
        $answer->refresh();
        $this->assertSame('Edited by teacher', $answer->content_text);
        $this->assertSame(42, $answer->solution);

        // Move time beyond 15-minute window and try student update -> forbidden
        $answer->update(['created_at' => now()->subMinutes(16), 'content_text' => 'Old again']);
        $this->actingAs($student)
            ->put(route('answers.update', $answer), [
                'format' => 'text',
                'content_text' => 'Student tries late edit',
            ])
            ->assertForbidden();
    }

    /** @test */
    public function student_can_delete_own_answer_within_window_but_not_after_and_teacher_can_delete_any(): void
    {
        $teacher = $this->teacher();
        $student = $this->student();
        $q = Question::factory()->create();

        $recent = Answer::factory()->create(['user_id' => $student->id, 'question_id' => $q->id]);
        $old = Answer::factory()->create(['user_id' => $student->id, 'question_id' => $q->id]);
        $old->update(['created_at' => now()->subMinutes(16)]);

        // Student can delete recent
        $this->actingAs($student)->delete(route('answers.destroy', $recent))->assertRedirect(route('questions.show', $q));
        $this->assertDatabaseMissing('answers', ['id' => $recent->id]);

        // Student cannot delete old
        $this->actingAs($student)->delete(route('answers.destroy', $old))->assertForbidden();

        // Teacher can delete any
        $this->actingAs($teacher)->delete(route('answers.destroy', $old))->assertRedirect(route('questions.show', $q));
        $this->assertDatabaseMissing('answers', ['id' => $old->id]);
    }
}
