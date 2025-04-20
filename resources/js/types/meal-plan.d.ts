import { Recipe } from './recipe';
import { User } from './user';

export interface MealAssignment {
    id: number;
    meal_plan_day_id: number;
    meal_plan_recipe_id: number;
    servings: number;
    to_cook: boolean;
    order?: number;
    created_at: string;
    updated_at: string;
    meal_plan_recipe: {
        id: number;
        recipe: Recipe;
        scale_factor: number;
        servings_available: number;
    };
}

export interface MealPlanDay {
    id: number;
    meal_plan_id: number;
    day_number: number;
    date: string;
    created_at: string;
    updated_at: string;
    meal_assignments: MealAssignment[];
}

export interface MealPlan {
    id: number;
    name: string | null;
    start_date: string;
    end_date: string;
    duration: number;
    people_count: number;
    created_at: string;
    updated_at: string;
    user_id: number;
    user?: User;
    recipes?: Array<
        Recipe & {
            pivot: {
                scale_factor: number;
                servings_available: number;
            };
        }
    >;
    days?: MealPlanDay[];
}
