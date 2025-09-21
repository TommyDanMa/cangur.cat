<?php

namespace Tests\Feature;

use App\Models\Block;
use App\Models\Question;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QuestionsDnDTest extends TestCase
{
    use RefreshDatabase;

    protected function teacher(): User
    {
        return User::factory()->create(['role' => 'teacher']);
    }

    public function test_create_form_has_block_selector_and_allows_null(): void
    {
        $user = $this->teacher();
        $this->actingAs($user);

        $resp = $this->get(route('questions.create'));
        $resp->assertOk()->assertSee('name="block_id"', false);

        // Submit without block_id
        $payload = [
            'title' => 'Q1',
            'statement' => 'S',
            'year' => 2024,
            'level' => 'A',
            'phase' => 'local',
            'session' => 'mati',
        ];
        $this->post(route('questions.store'), $payload)->assertRedirect();
        $this->assertDatabaseHas('questions', ['title' => 'Q1', 'block_id' => null]);
    }

    public function test_questions_reorder_and_move_between_blocks(): void
    {
        $user = $this->teacher();
        $this->actingAs($user);

        $a = Block::create(['name' => 'A']);
        $b = Block::create(['name' => 'B']);

        $q1 = Question::create(['title' => 'Q1','statement'=>'S','year'=>2024,'level'=>'A','phase'=>'local','created_by'=>$user->id,'block_id'=>$a->id,'position'=>0]);
        $q2 = Question::create(['title' => 'Q2','statement'=>'S','year'=>2024,'level'=>'A','phase'=>'local','created_by'=>$user->id,'block_id'=>$a->id,'position'=>1]);
        $q3 = Question::create(['title' => 'Q3','statement'=>'S','year'=>2024,'level'=>'A','phase'=>'local','created_by'=>$user->id,'block_id'=>$a->id,'position'=>2]);

        // Move Q2 to block B and reorder Q1,Q3 within A
        $payload = [
            'items' => [
                ['id' => $q1->id, 'block_id' => $a->id, 'position' => 0],
                ['id' => $q3->id, 'block_id' => $a->id, 'position' => 1],
                ['id' => $q2->id, 'block_id' => $b->id, 'position' => 0],
            ]
        ];

        $this->postJson(route('questions.reorder'), $payload)->assertOk();

        $this->assertDatabaseHas('questions', ['id' => $q1->id, 'block_id' => $a->id, 'position' => 0]);
        $this->assertDatabaseHas('questions', ['id' => $q3->id, 'block_id' => $a->id, 'position' => 1]);
        $this->assertDatabaseHas('questions', ['id' => $q2->id, 'block_id' => $b->id, 'position' => 0]);
    }
    public function test_reorder_unassigned_questions(): void
    {
        $user = $this->teacher();
        $this->actingAs($user);

        $q1 = Question::create(['title' => 'Q1','statement'=>'S','year'=>2024,'level'=>'A','phase'=>'local','created_by'=>$user->id,'block_id'=>null,'position'=>0]);
        $q2 = Question::create(['title' => 'Q2','statement'=>'S','year'=>2024,'level'=>'A','phase'=>'local','created_by'=>$user->id,'block_id'=>null,'position'=>1]);
        $q3 = Question::create(['title' => 'Q3','statement'=>'S','year'=>2024,'level'=>'A','phase'=>'local','created_by'=>$user->id,'block_id'=>null,'position'=>2]);

        $payload = [
            'items' => [
                ['id' => $q3->id, 'block_id' => null, 'position' => 0],
                ['id' => $q1->id, 'block_id' => null, 'position' => 1],
                ['id' => $q2->id, 'block_id' => null, 'position' => 2],
            ]
        ];

        $this->postJson(route('questions.reorder'), $payload)->assertOk();

        $this->assertDatabaseHas('questions', ['id' => $q3->id, 'block_id' => null, 'position' => 0]);
        $this->assertDatabaseHas('questions', ['id' => $q1->id, 'block_id' => null, 'position' => 1]);
        $this->assertDatabaseHas('questions', ['id' => $q2->id, 'block_id' => null, 'position' => 2]);
    }
}
