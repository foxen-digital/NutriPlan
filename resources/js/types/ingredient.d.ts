export interface Ingredient {
    id: number;
    name: string;
    slug: string;
    is_common: boolean;
}

export interface NormalizedIngredient {
    ingredient_id: number;
    name: string;
    amount: number | null;
    unit: string | null;
    description: string | null;
    original_string: string;
}
