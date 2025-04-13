import { User } from './user';

export interface MeasurementUnit {
    value: string;
    label: string;
}

export interface Recipe {
    id: number;
    title: string;
    name?: string;
    description: string;
    servings: number;
    prep_time: number;
    cooking_time: number;
    total_time?: number;
    difficulty_level?: string;
    created_at: string;
    updated_at?: string;
    url: string | null;
    author: string | null;
    is_public: boolean;
    images: string[] | null;
    slug: string;
    user?: User;
    instructions: string;
    categories: Array<{
        id: number;
        name: string;
        slug: string;
    }>;
    ingredients: Array<{
        id: number;
        name: string;
        pivot: {
            amount: number;
            unit: string;
            description: string;
        };
    }>;
    nutrition_information?: {
        calories?: string;
        carbohydrate_content?: string;
        cholesterol_content?: string;
        fat_content?: string;
        fiber_content?: string;
        protein_content?: string;
        saturated_fat_content?: string;
        serving_size?: string;
        sodium_content?: string;
        sugar_content?: string;
        trans_fat_content?: string;
        unsaturated_fat_content?: string;
    } | null;
}

export interface RecipeWithPivot extends Recipe {
    pivot: {
        scale_factor: number;
        servings_available: number;
    };
}
