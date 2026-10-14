<?php

declare(strict_types=1);

namespace App\Events;

use App\Models\Recipe;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class RecipeImportCompleted implements ShouldBroadcast
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    /**
     * Create a new event instance.
     *
     * @param int $userId The ID of the user to notify
     * @param string $status The status of the import ('success' or 'error')
     * @param string $message User-friendly status message
     * @param Recipe|null $recipe The successfully imported recipe (nullable)
     */
    public function __construct(
        public readonly int $userId,
        public readonly string $status,
        public readonly string $message,
        public readonly ?Recipe $recipe
    ) {
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('user.' . $this->userId),
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'recipe.import.completed';
    }

    /**
     * Get the data to broadcast.
     *
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        return [
            'status' => $this->status,
            'message' => $this->message,
            ...($this->recipe !== null ? [
                'recipeId' => $this->recipe->id,
                'recipeUrl' => route('recipes.show', $this->recipe->slug),
            ] : []),
        ];
    }
}
