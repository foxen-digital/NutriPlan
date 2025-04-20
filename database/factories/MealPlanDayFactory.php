<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\MealPlan;
use App\Models\MealPlanDay;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MealPlanDay>
 */
class MealPlanDayFactory extends Factory
{
    protected $model = MealPlanDay::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'meal_plan_id' => MealPlan::factory(),
            'day_number' => $this->faker->unique()->numberBetween(1, 14), // Support both 7-day and 14-day meal plans
        ];
    }

    /**
     * Set a specific day number.
     *
     * @param int $dayNumber
     * @return self
     */
    public function withDayNumber(int $dayNumber): self
    {
        return $this->state(function (array $attributes) use ($dayNumber) {
            return [
                'day_number' => $dayNumber,
            ];
        });
    }
}
