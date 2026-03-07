<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\MealAssignment;
use App\Models\ShoppingList;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class UpdateShoppingListJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * Create a new job instance.
     *
     * @param  ShoppingList  $shoppingList  The shopping list to update
     * @param  MealAssignment  $meal  The meal assignment that triggered the update
     */
    public function __construct(
        public readonly ShoppingList $shoppingList,
        public readonly MealAssignment $meal
    ) {
    }

    /**
     * Execute the job.
     *
     * Implementation will be added in Story 1.4: Background List Update Job
     */
    public function handle(): void
    {
        // Implementation in Story 1.4
    }
}
