<?php

namespace Database\Factories;

use App\Models\Answer;
use App\Models\Question;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Answer>
 */
class AnswerFactory extends Factory
{
    protected $model = Answer::class;

    public function definition(): array
    {
        $format = $this->faker->randomElement(['text','video','image']);
        return [
            'user_id' => User::factory(),
            'question_id' => Question::factory(),
            'format' => $format,
            'content_text' => $format === 'text' ? $this->faker->sentence() : null,
            'video_url' => $format === 'video' ? $this->faker->url() : null,
            'image_path' => $format === 'image' ? 'answers/'.$this->faker->uuid.'.jpg' : null,
            'solution' => $this->faker->numberBetween(1, 9999),
            'no_solution' => false,
        ];
    }

    public function noSolution(): static
    {
        return $this->state(fn () => ['solution' => null, 'no_solution' => true]);
    }
}
