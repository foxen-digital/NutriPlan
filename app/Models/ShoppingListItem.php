<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShoppingListItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'quantity',
        'unit',
        'category',
        'is_custom',
        'is_purchased',
        'order',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'is_custom' => 'boolean',
        'is_purchased' => 'boolean',
        'order' => 'integer',
    ];

    public function shoppingList(): BelongsTo
    {
        return $this->belongsTo(ShoppingList::class);
    }

    public function ingredient(): BelongsTo
    {
        return $this->belongsTo(Ingredient::class);
    }
}
