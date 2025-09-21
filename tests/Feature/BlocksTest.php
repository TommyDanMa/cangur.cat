<?php

namespace Tests\Feature;

use App\Models\Block;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BlocksTest extends TestCase
{
    use RefreshDatabase;

    protected function makeTeacher(): User
    {
        return User::factory()->create(['role' => 'teacher']);
    }

    protected function makeStudent(): User
    {
        return User::factory()->create(['role' => 'student']);
    }

    public function test_student_cannot_access_block_create_edit_delete_reorder(): void
    {
        $student = $this->makeStudent();
        $this->actingAs($student);

        $this->get(route('blocks.create'))->assertStatus(403);
        $block = Block::create(['name' => 'A']);
        $this->get(route('blocks.edit', $block))->assertStatus(403);
        $this->delete(route('blocks.destroy', $block))->assertStatus(403);
        $this->postJson(route('blocks.reorder'), ['items' => []])->assertStatus(403);
    }

    public function test_teacher_can_create_update_delete_block(): void
    {
        $teacher = $this->makeTeacher();
        $this->actingAs($teacher);

        // Create
        $resp = $this->post(route('blocks.store'), [
            'name' => 'Bloc 1',
            'description' => 'Desc',
            'year' => 2024,
            'level' => 'A',
            'visible' => 1,
        ]);
        $resp->assertRedirect();
        $this->assertDatabaseHas('blocks', ['name' => 'Bloc 1', 'year' => 2024, 'level' => 'A']);

        $block = Block::first();

        // Update
        $resp = $this->put(route('blocks.update', $block), [
            'name' => 'Bloc 1x',
            'description' => 'Desc2',
            'year' => 2023,
            'level' => 'B',
            'visible' => 0,
        ]);
        $resp->assertRedirect();
        $this->assertDatabaseHas('blocks', ['id' => $block->id, 'name' => 'Bloc 1x', 'year' => 2023, 'level' => 'B', 'visible' => 0]);

        // Delete
        $resp = $this->delete(route('blocks.destroy', $block));
        $resp->assertRedirect();
        $this->assertDatabaseMissing('blocks', ['id' => $block->id]);
    }

    public function test_blocks_index_redirects_to_questions_index(): void
    {
        $resp = $this->get(route('blocks.index'));
        $resp->assertRedirect(route('questions.index'));
    }

    public function test_reorder_persists_parent_and_position(): void
    {
        $teacher = $this->makeTeacher();
        $this->actingAs($teacher);

        $a = Block::create(['name' => 'A', 'position' => 0]);
        $b = Block::create(['name' => 'B', 'position' => 1]);
        $c = Block::create(['name' => 'C', 'position' => 2]);

        // Make B child of A, and order A, C at root (A pos 0, C pos 1)
        $payload = [
            'items' => [
                ['id' => $a->id, 'parent_id' => null, 'position' => 0],
                ['id' => $c->id, 'parent_id' => null, 'position' => 1],
                ['id' => $b->id, 'parent_id' => $a->id, 'position' => 0],
            ]
        ];

        $this->postJson(route('blocks.reorder'), $payload)->assertOk();

        $this->assertDatabaseHas('blocks', ['id' => $a->id, 'parent_id' => null, 'position' => 0]);
        $this->assertDatabaseHas('blocks', ['id' => $c->id, 'parent_id' => null, 'position' => 1]);
        $this->assertDatabaseHas('blocks', ['id' => $b->id, 'parent_id' => $a->id, 'position' => 0]);
    }
}
