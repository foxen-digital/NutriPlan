### AI Recipe parsing
* For sites which do not use structured data for their recipes, we should build an AI based recipe parser to try and extract Ingredients and Instructions (nutrition info / categories if possible?)
* Use https://github.com/fivefilters/readability.php to extract the text content of teh page
* send this to an LLM, along with a detailed system message
* have the LLM return structured data matching what we already get for Ingredients & Instructions

### AI Recipe Parsing from images
* Allow users to scan / upload recipes from books / magazines / grandma's recipe cards
* send this to an LLM, along with a detailed system message
* have the LLM return structured data matching what we already get for Ingredients & Instructions

### Bulk imports
* Allow importing from competing apps
  * Recipesage
  * pepperplate
  * Paprika
  * Etc
* allow pasting a list of URLs to be imported
* Where to we add this? Not on main UI. Somewhere in user settings pages?


### Recipe exports
* Allow users to export their recipe collection (manually created only?)
    - What format? json-ld? Custom

### Enhanced Nutrition & Dietary Features

*   **Automatic Nutrition Calculation:** Integrate with a nutrition API (e.g., USDA FoodData Central, Open Food Facts) to automatically calculate and display estimated nutritional information (calories, macros, etc.) for recipes.
    *   *Benefit:* Helps users track intake, meet dietary goals, and make informed choices.
*   **Advanced Dietary Filtering & Tagging:** Allow tagging recipes (user & community) with specific dietary needs (Gluten-Free, Vegan, Keto, etc.) and allergens. Enable filtering by these tags.
    *   *Benefit:* Increases utility for users with specific dietary requirements.
*   **Goal-Oriented Meal Planning:** Allow users to set dietary goals (calories, macros) and receive recipe/plan suggestions, potentially using AI for personalization.
    *   *Benefit:* Provides proactive support for health-focused users.

### Pantry & Inventory Management

*   **Digital Pantry:** Allow users to list ingredients they have in stock, optionally with quantities.
    *   *Benefit:* Reduces food waste and unnecessary purchases.
*   **Pantry Integration with Shopping List:** Cross-reference generated shopping lists with the pantry to remove or flag available items.
    *   *Benefit:* Creates smarter, more efficient shopping lists.
*   **"Use What You Have" Recipe Suggestions:** Suggest recipes based on selected pantry ingredients.
    *   *Benefit:* Inspires cooking with existing ingredients and minimizes waste.

### Improved Cooking Experience

*   **Dedicated Cooking Mode:** Create a view optimized for cooking (larger fonts, step highlighting, timers, screen awake).
    *   *Benefit:* Makes following recipes in the kitchen easier.
*   **Instructional Media:** Allow embedding step-by-step photos or short videos in recipe instructions.
    *   *Benefit:* Provides clearer guidance, especially for complex techniques.
*   **User Ratings & Reviews:** Implement a system for rating and reviewing community recipes.
    *   *Benefit:* Builds trust and helps users identify reliable recipes.
*   **Recipe Scaling Notes:** Allow notes on how instructions change when scaling (cooking times, pan sizes).
    *   *Benefit:* Improves success rate when scaling recipes.

### Deeper Community & Social Interaction

*   **User Profiles & Following:** Enhance profiles and allow users to follow others, creating a personalized feed.
    *   *Benefit:* Fosters community and personalized discovery.
*   **Recipe Recreations & Photos:** Allow users to upload photos of their results when making community recipes.
    *   *Benefit:* Increases engagement and provides real-world examples.
*   **Shared Collections/Meal Plans:** Enable collaborative recipe collections or meal plans.
    *   *Benefit:* Makes the tool more useful for families or groups.

### Advanced Planning & Discovery

*   **Meal Plan Templates:** Offer pre-built templates for various needs (e.g., "Budget Dinners," "Quick Weeknight Meals").
    *   *Benefit:* Provides starting points and inspiration.
*   **Leftover Integration:** Feature to easily incorporate planned leftovers into subsequent meals.
    *   *Benefit:* Streamlines planning for efficient food use.
*   **AI-Powered Recommendations:** Use user data for personalized recipe suggestions.
    *   *Benefit:* Improves discovery and engagement.
*   **Calendar Integration:** Allow exporting meal plans to external calendars (Google Calendar, iCal, etc.).
    *   *Benefit:* Integrates meal planning into users' broader schedules. 