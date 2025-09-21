<?php

namespace Tests\Feature\Questions;

use App\Models\Block;
use App\Models\Question;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Feature tests for questions index filters and visibility.
 */
class QuestionsFilterTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function index_lists_only_unassigned_questions(): void
    {
        // Given an authenticated student
        $student = User::factory()->create(['role' => 'student']);

        // And two questions: one unassigned, one inside a block
        $unassigned = Question::factory()->create(['title' => 'Unassigned Q', 'block_id' => null]);
        $block = Block::factory()->create();
        $inBlock = Question::factory()->create(['title' => 'In Block Q', 'block_id' => $block->id]);

        $resp = $this->actingAs($student)->get(route('questions.index'));
        $resp->assertOk();

        // Assert the controller passed a paginator with the unassigned question only
        $resp->assertViewHas('questions', function ($paginator) use ($unassigned, $inBlock) {
            $ids = $paginator->getCollection()->pluck('id')->all();
            return in_array($unassigned->id, $ids) && !in_array($inBlock->id, $ids);
        });
    }

    /** @test */
    public function index_can_filter_by_year_level_phase_and_session(): void
    {
        $student = User::factory()->create(['role' => 'student']);

        // Create a mix of questions (all unassigned)
        $q1 = Question::factory()->create(['year' => 2020, 'level' => 'A', 'phase' => 'local', 'session' => 'tarda']);
        $q2 = Question::factory()->create(['year' => 2020, 'level' => 'B', 'phase' => 'semifinal', 'session' => null]);
        $q3 = Question::factory()->create(['year' => 2021, 'level' => 'A', 'phase' => 'final', 'session' => null]);

        // Filter by year
        $this->actingAs($student)
            ->get(route('questions.index', ['year' => 2020]))
            ->assertOk()
            ->assertViewHas('questions', function ($p) use ($q1, $q2) {
                $ids = $p->getCollection()->pluck('id')->all();
                return in_array($q1->id, $ids) && in_array($q2->id, $ids) && count($ids) === 2;
            });

        // Filter by level
        $this->actingAs($student)
            ->get(route('questions.index', ['level' => 'A']))
            ->assertOk()
            ->assertViewHas('questions', function ($p) use ($q1, $q3) {
                $ids = $p->getCollection()->pluck('id')->all();
                return in_array($q1->id, $ids) && in_array($q3->id, $ids) && count($ids) === 2;
            });

        // Filter by phase
        $this->actingAs($student)
            ->get(route('questions.index', ['phase' => 'final']))
            ->assertOk()
            ->assertViewHas('questions', function ($p) use ($q3) {
                $ids = $p->getCollection()->pluck('id')->all();
                return $ids === [$q3->id];
            });

        // Filter by session (we use 'tarda' because DB enum for 'matí' has accent)
        $this->actingAs($student)
            ->get(route('questions.index', ['session' => 'tarda']))
            ->assertOk()
            ->assertViewHas('questions', function ($p) use ($q1) {
                $ids = $p->getCollection()->pluck('id')->all();
                return $ids === [$q1->id];
            });

        // Combine filters
        $this->actingAs($student)
            ->get(route('questions.index', ['year' => 2020, 'level' => 'A', 'phase' => 'local', 'session' => 'tarda']))
            ->assertOk()
            ->assertViewHas('questions', function ($p) use ($q1) {
                $ids = $p->getCollection()->pluck('id')->all();
                return $ids === [$q1->id];
            });
    }

    /** @test */
    public function unassigned_json_endpoint_applies_same_filters(): void
    {
        $student = User::factory()->create(['role' => 'student']);

        // One unassigned and one in a block for year 2022
        $unassigned = Question::factory()->create(['year' => 2022, 'level' => 'C', 'phase' => 'local', 'session' => 'tarda', 'block_id' => null]);
        $block = Block::factory()->create();
        $inBlock = Question::factory()->create(['year' => 2022, 'level' => 'C', 'phase' => 'local', 'session' => 'tarda', 'block_id' => $block->id]);

        $resp = $this->actingAs($student)->get(route('questions.unassigned', ['year' => 2022, 'level' => 'C', 'phase' => 'local', 'session' => 'tarda']));
        $resp->assertOk()->assertJsonStructure(['children', 'questions']);

        // Ensure only unassigned question is included
        $resp->assertJsonMissing(['id' => $inBlock->id]);
        $resp->assertJsonFragment(['id' => $unassigned->id]);
    }
}
