<?php

declare(strict_types=1);

namespace Tests\Unit\Http\Requests;

use App\Http\Requests\StoreCollectionRecipeRequest;
use App\Models\Collection;
use App\Models\Recipe;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use Mockery;
use Tests\TestCase;

class StoreCollectionRecipeRequestTest extends TestCase
{
    use RefreshDatabase;

    private StoreCollectionRecipeRequest $request;

    protected function setUp(): void
    {
        parent::setUp();
        $this->request = new StoreCollectionRecipeRequest();
    }

    /**
     * @dataProvider validationDataProvider
     */
    public function test_validation_rules(array $data, bool $shouldPass, array $expectedErrors = []): void
    {
        // Create models needed for 'exists' rules within the test method
        $collection = Collection::factory()->create();
        $recipe = Recipe::factory()->create();

        // Replace placeholder IDs with actual created IDs if needed
        if (isset($data['collection_id']) && $data['collection_id'] === 'valid_collection_id') {
            $data['collection_id'] = $collection->id;
        }
        if (isset($data['recipe_id']) && $data['recipe_id'] === 'valid_recipe_id') {
            $data['recipe_id'] = $recipe->id;
        }

        $validator = Validator::make($data, $this->request->rules());

        $this->assertEquals($shouldPass, !$validator->fails());

        if (!$shouldPass) {
            $this->assertEquals($expectedErrors, $validator->errors()->keys());
        }
    }

    public static function validationDataProvider(): array
    {
        // Data provider now only returns data arrays, models created in test method
        return [
            'pass_case' => [
                'data' => ['collection_id' => 'valid_collection_id', 'recipe_id' => 'valid_recipe_id'],
                'shouldPass' => true,
            ],
            'fail_missing_collection_id' => [
                'data' => ['recipe_id' => 'valid_recipe_id'],
                'shouldPass' => false,
                'expectedErrors' => ['collection_id'],
            ],
            'fail_missing_recipe_id' => [
                'data' => ['collection_id' => 'valid_collection_id'],
                'shouldPass' => false,
                'expectedErrors' => ['recipe_id'],
            ],
            'fail_non_existent_collection_id' => [
                'data' => ['collection_id' => 999, 'recipe_id' => 'valid_recipe_id'],
                'shouldPass' => false,
                'expectedErrors' => ['collection_id'],
            ],
            'fail_non_existent_recipe_id' => [
                'data' => ['collection_id' => 'valid_collection_id', 'recipe_id' => 999],
                'shouldPass' => false,
                'expectedErrors' => ['recipe_id'],
            ],
            'fail_both_missing' => [
                'data' => [],
                'shouldPass' => false,
                'expectedErrors' => ['collection_id', 'recipe_id'],
            ],
        ];
    }

    public function test_authorize_returns_true_when_user_can_update_collection(): void
    {
        $user = User::factory()->create();
        $collection = Collection::factory()->create(['user_id' => $user->id]);

        // Use Mockery::on to match any Collection instance with the correct ID
        Gate::shouldReceive('allows')
            ->once()
            ->with('update', Mockery::on(function ($arg) use ($collection) {
                return $arg instanceof Collection && $arg->id === $collection->id;
            }))
            ->andReturn(true);

        // Set the user making the request
        $this->actingAs($user);

        // Set the input data for the request
        $this->request->merge(['collection_id' => $collection->id]);

        $this->assertTrue($this->request->authorize());
    }

    public function test_authorize_returns_false_when_collection_does_not_exist(): void
    {
        $user = User::factory()->create();

        // Mock Gate facade - it should not be called
        Gate::shouldReceive('allows')->never();

        // Set the user making the request
        $this->actingAs($user);

        // Set non-existent collection_id
        $this->request->merge(['collection_id' => 999]);

        $this->assertFalse($this->request->authorize());
    }

    public function test_authorize_returns_false_when_user_cannot_update_collection(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $collection = Collection::factory()->create(['user_id' => $otherUser->id]);

        // Use Mockery::on to match any Collection instance with the correct ID
        Gate::shouldReceive('allows')
            ->once()
            ->with('update', Mockery::on(function ($arg) use ($collection) {
                return $arg instanceof Collection && $arg->id === $collection->id;
            }))
            ->andReturn(false);

        // Set the user making the request
        $this->actingAs($user);

        // Set the input data for the request
        $this->request->merge(['collection_id' => $collection->id]);

        $this->assertFalse($this->request->authorize());
    }
}
