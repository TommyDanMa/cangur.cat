<?php

namespace Tests\Feature\Questions;

use App\Models\Block;
use App\Models\Question;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Feature tests that exercise QuestionController (create/store/edit/update/destroy/reorder).
 */
class QuestionCrudTest extends TestCase
{
    use RefreshDatabase;

    private function teacher(): User
    {
        return User::factory()->create(['role' => 'teacher']);
    }

    private function student(): User
    {
        return User::factory()->create(['role' => 'student']);
    }

    /** @test */
    public function student_cannot_access_create_store_update_destroy_or_reorder(): void
    {
        $student = $this->student();
        $q = Question::factory()->create();

        $this->actingAs($student)->get(route('questions.create'))->assertForbidden();

        $this->actingAs($student)->post(route('questions.store'), [])->assertForbidden();

        $this->actingAs($student)->get(route('questions.edit', $q))->assertForbidden();
        $this->actingAs($student)->put(route('questions.update', $q), [])->assertForbidden();
        $this->actingAs($student)->delete(route('questions.destroy', $q))->assertForbidden();

        $this->actingAs($student)->post(route('questions.reorder'), [
            'items' => [
                ['id' => $q->id, 'block_id' => null, 'position' => 0],
            ],
        ])->assertForbidden();
    }

    /** @test */
    public function teacher_can_create_question_with_image_and_is_positioned_at_end_of_block(): void
    {
        Storage::fake('public');
        $teacher = $this->teacher();

        $block = Block::factory()->create();
        // Existing question to occupy position 1 in that block
        Question::factory()->create(['block_id' => $block->id, 'position' => 1]);

        $payload = [
            'title' => 'New Question',
            'statement' => 'Solve x+y.',
            'year' => 2025,
            'level' => 'A',
            'phase' => 'local',
            'session' => 'tarda', // controller expects 'tarda' or 'mati'
            'block_id' => $block->id,
            'image' => UploadedFile::fake()->image('q.jpg'),
        ];

        $resp = $this->actingAs($teacher)->post(route('questions.store'), $payload);
        $resp->assertRedirect(route('questions.index'));

        // File stored
        Storage::disk('public')->assertExists('questions');
        $created = Question::latest('id')->first();
        $this->assertNotNull($created->image_path);
        $this->assertEquals($block->id, $created->block_id);
        $this->assertEquals(2, $created->position, 'New question should be appended to end of block.');
    }

    /** @test */
    public function validation_enforces_required_fields_and_session_rule_for_local_phase(): void
    {
        $teacher = $this->teacher();

        // Missing title and statement, and session missing while phase is local -> expect validation errors
        $payload = [
            'title' => '',
            'statement' => '',
            'year' => 2025,
            'level' => 'A',
            'phase' => 'local',
            // 'session' omitted intentionally
        ];

        $this->actingAs($teacher)
            ->post(route('questions.store'), $payload)
            ->assertSessionHasErrors(['title', 'statement', 'session']);

        // With phase semifinal, session not required
        $payload['title'] = 'Valid title';
        $payload['statement'] = 'Valid statement';
        $payload['phase'] = 'semifinal';
        $this->actingAs($teacher)
            ->post(route('questions.store'), $payload)
            ->assertRedirect(route('questions.index'));
    }

    /** @test */
    public function update_moves_question_to_end_when_block_changes(): void
    {
        $teacher = $this->teacher();
        $blockA = Block::factory()->create();
        $blockB = Block::factory()->create();

        // In block A: q1 (pos 1), q2 (pos 2)
        $q1 = Question::factory()->create(['block_id' => $blockA->id, 'position' => 1]);
        $q2 = Question::factory()->create(['block_id' => $blockA->id, 'position' => 2]);
        // In block B: qb1 (pos 1)
        $qb1 = Question::factory()->create(['block_id' => $blockB->id, 'position' => 1]);

        // Move q1 from A to B -> should become position 2 in B
        $payload = [
            'title' => $q1->title,
            'statement' => $q1->statement,
            'year' => $q1->year,
            'level' => $q1->level,
            'phase' => $q1->phase,
            'session' => $q1->session,
            'block_id' => $blockB->id,
        ];

        $this->actingAs($teacher)->put(route('questions.update', $q1), $payload)->assertRedirect(route('questions.show', $q1));
        $q1->refresh();
        $this->assertEquals($blockB->id, $q1->block_id);
        $this->assertEquals(2, $q1->position);
    }

    /** @test */
    public function teacher_can_delete_question(): void
    {
        $teacher = $this->teacher();
        $q = Question::factory()->create();

        $this->actingAs($teacher)->delete(route('questions.destroy', $q))->assertRedirect(route('questions.index'));
        $this->assertDatabaseMissing('questions', ['id' => $q->id]);
    }

    /** @test */
    public function reorder_endpoint_requires_teacher_and_updates_positions_and_block(): void
    {
        $teacher = $this->teacher();
        $block = Block::factory()->create();
        $q1 = Question::factory()->create(['block_id' => null, 'position' => 0]);
        $q2 = Question::factory()->create(['block_id' => null, 'position' => 1]);

        // Non-teacher blocked
        $this->actingAs($this->student())
            ->postJson(route('questions.reorder'), ['items' => [['id' => $q1->id, 'block_id' => $block->id, 'position' => 3]]])
            ->assertStatus(403);

        // Teacher can reorder and move between blocks
        $this->actingAs($teacher)
            ->postJson(route('questions.reorder'), [
                'items' => [
                    ['id' => $q1->id, 'block_id' => $block->id, 'position' => 2],
                    ['id' => $q2->id, 'block_id' => $block->id, 'position' => 1],
                ],
            ])
            ->assertOk()
            ->assertJson(['ok' => true]);

        $this->assertDatabaseHas('questions', ['id' => $q1->id, 'block_id' => $block->id, 'position' => 2]);
        $this->assertDatabaseHas('questions', ['id' => $q2->id, 'block_id' => $block->id, 'position' => 1]);
    }
}
