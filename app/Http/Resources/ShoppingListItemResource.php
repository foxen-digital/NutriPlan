<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ShoppingListItemResource extends JsonResource
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
            'shopping_list_id' => $this->shopping_list_id,
            'ingredient_id' => $this->ingredient_id,
            'name' => $this->name,
            'quantity' => $this->quantity,
            'unit' => $this->unit,
            'category' => $this->category,
            'is_custom' => $this->is_custom,
            'is_purchased' => $this->is_purchased,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'ingredient' => $this->whenLoaded('ingredient'),
        ];
    }
}
