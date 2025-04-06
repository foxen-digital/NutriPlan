<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Models\MealPlan;
use App\Services\ShoppingListService;
use Illuminate\Support\Carbon;
use ReflectionClass;
use ReflectionMethod;
use Tests\TestCase;

class ShoppingListServiceTest extends TestCase
{
    private ShoppingListService $service;
    private ReflectionMethod $calculatePeriodDatesMethod;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new ShoppingListService();

        // Use reflection to access the private method
        $reflectionClass = new ReflectionClass(ShoppingListService::class);
        $this->calculatePeriodDatesMethod = $reflectionClass->getMethod('calculatePeriodDates');
        $this->calculatePeriodDatesMethod->setAccessible(true);
    }

    public function test_calculate_period_dates_returns_correct_dates_for_full_period(): void
    {
        $mealPlan = new MealPlan();
        $mealPlan->start_date = Carbon::parse('2023-08-01');
        $mealPlan->duration = 7;

        $result = $this->calculatePeriodDatesMethod->invoke($this->service, $mealPlan, 'full');

        $this->assertEquals('2023-08-01', $result['start_date']->format('Y-m-d'));
        $this->assertEquals('2023-08-07', $result['end_date']->format('Y-m-d'));
    }

    public function test_calculate_period_dates_returns_correct_dates_for_week1(): void
    {
        $mealPlan = new MealPlan();
        $mealPlan->start_date = Carbon::parse('2023-08-01');
        $mealPlan->duration = 14;

        $result = $this->calculatePeriodDatesMethod->invoke($this->service, $mealPlan, 'week1');

        $this->assertEquals('2023-08-01', $result['start_date']->format('Y-m-d'));
        $this->assertEquals('2023-08-07', $result['end_date']->format('Y-m-d'));
    }

    public function test_calculate_period_dates_returns_correct_dates_for_week2(): void
    {
        $mealPlan = new MealPlan();
        $mealPlan->start_date = Carbon::parse('2023-08-01');
        $mealPlan->duration = 14;

        $result = $this->calculatePeriodDatesMethod->invoke($this->service, $mealPlan, 'week2');

        $this->assertEquals('2023-08-08', $result['start_date']->format('Y-m-d'));
        $this->assertEquals('2023-08-14', $result['end_date']->format('Y-m-d'));
    }
}
