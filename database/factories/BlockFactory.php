<?php

namespace Database\Factories;

use App\Models\Block;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Block>
 */
class BlockFactory extends Factory
{
    protected $model = Block::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->words(2, true),
            'description' => $this->faker->optional()->sentence(),
            'parent_id' => null,
            'position' => 0,
            'year' => $this->faker->optional()->numberBetween(2010, 2030),
            'level' => $this->faker->optional()->randomElement(['A','B','C','D','E']),
            'visible' => true,
        ];
    }

    public function hidden(): static
    {
        return $this->state(fn () => ['visible' => false]);
    }
}
