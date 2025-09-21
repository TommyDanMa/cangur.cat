<?php

namespace Tests\Unit;

use App\Models\Answer;
use App\Models\Block;
use App\Models\Question;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * These unit tests focus on Eloquent relationships and model casting.
 * They are small, fast, and don't hit the HTTP layer.
 */
class ModelsRelationshipsTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function question_has_many_answers_and_belongs_to_author_and_block(): void
    {
        $teacher = User::factory()->create(['role' => 'teacher']);
        $block = Block::factory()->create();
        $q = Question::factory()->create(['created_by' => $teacher->id, 'block_id' => $block->id]);

        // Create answers for that question
        $a1 = Answer::factory()->create(['question_id' => $q->id]);
        $a2 = Answer::factory()->create(['question_id' => $q->id]);

        // Relationship: author
        $this->assertTrue($q->author->is($teacher), 'Question author relation should return the creator user.');

        // Relationship: block
        $this->assertTrue($q->block->is($block), 'Question block relation should return assigned block.');

        // Relationship: answers
        $this->assertCount(2, $q->answers, 'Question should return its related answers.');
        $this->assertTrue($q->answers->contains($a1));
        $this->assertTrue($q->answers->contains($a2));
    }

    /** @test */
    public function answer_belongs_to_user_and_question_and_casts_fields(): void
    {
        $answer = Answer::factory()->create(['solution' => 7, 'no_solution' => false]);

        // Relationships
        $this->assertInstanceOf(User::class, $answer->user, 'Answer->user should be a User instance.');
        $this->assertInstanceOf(Question::class, $answer->question, 'Answer->question should be a Question instance.');

        // Casts
        $this->assertIsInt($answer->solution, 'Answer->solution should be cast to integer.');
        $this->assertIsBool($answer->no_solution, 'Answer->no_solution should be cast to boolean.');
    }

    /** @test */
    public function block_can_have_children_and_questions(): void
    {
        $parent = Block::factory()->create();
        $childA = Block::factory()->create(['parent_id' => $parent->id, 'position' => 1]);
        $childB = Block::factory()->create(['parent_id' => $parent->id, 'position' => 0]);

        $q1 = Question::factory()->create(['block_id' => $parent->id, 'position' => 2]);
        $q2 = Question::factory()->create(['block_id' => $parent->id, 'position' => 1]);

        // Children should be ordered by position
        $this->assertSame($childB->id, $parent->children->first()->id);
        $this->assertSame($childA->id, $parent->children->last()->id);

        // Questions should be ordered by position then created_at
        $this->assertSame([$q2->id, $q1->id], $parent->questions->pluck('id')->toArray());
    }
}
