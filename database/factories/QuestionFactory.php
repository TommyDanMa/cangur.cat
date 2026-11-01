<?php

namespace Database\Factories;

use App\Models\Question;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Question>
 */
class QuestionFactory extends Factory
{
    protected $model = Question::class;

    public function definition(): array
    {
        return [
            'title' => $this->faker->sentence(3),
            'statement' => $this->faker->paragraph(),
            'year' => $this->faker->numberBetween(2010, 2030),
            'level' => $this->faker->randomElement(['Cadet(1ESO-2ESO)','Junior(3ESO-4ESO)']),
            'phase' => $this->faker->randomElement(['local','semifinal','final']),
            // Use 'tarda' to avoid enum accent mismatch in tests
            'session' => 'tarda',
            'image_path' => null,
            'created_by' => User::factory(),
        ];
    }
}
