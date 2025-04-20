export interface Recipe {
    id: number;
    // ... other recipe properties
}

export interface RecipeFilters {
    category?: number | string | null;
    show_mine?: boolean | string | null;
    search_term?: string | null;
    search_mode?: 'name_description' | 'ingredient' | null;
    // Add other potential filters here
}

// Add other recipe-related types as needed 