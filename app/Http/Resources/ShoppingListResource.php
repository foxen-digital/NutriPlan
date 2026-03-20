<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\ShoppingList;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin ShoppingList
 */
class ShoppingListResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'user_id' => $this->user_id,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'items' => $this->whenLoaded('items', fn () => ShoppingListItemResource::collection($this->items)),
            'items_count' => $this->whenCounted('items'),
        ];
    }
}
