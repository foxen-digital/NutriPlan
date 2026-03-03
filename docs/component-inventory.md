# Component Inventory - NutriPlan

This document catalogs all Vue components in the NutriPlan application.

## Component Organization

Components are organized by feature and reusability.

---

## Layout Components

### AppShell
**Location:** `resources/js/components/AppShell.vue`
**Purpose:** Main application layout shell with sidebar
**Dependencies:** Radix Vue navigation components

### AppContent
**Location:** `resources/js/components/AppContent.vue`
**Purpose:** Main content area wrapper

### AppHeader
**Location:** `resources/js/components/AppHeader.vue`
**Purpose:** Application header with mobile menu toggle

### AppSidebar
**Location:** `resources/js/components/AppSidebar.vue`
**Purpose:** Navigation sidebar with menu items

### AppSidebarHeader
**Location:** `resources/js/components/AppSidebarHeader.vue`
**Purpose:** Sidebar header section

### AppLogo / AppLogoIcon
**Location:** `resources/js/components/AppLogo.vue`, `AppLogoIcon.vue`
**Purpose:** Application logo component

---

## Navigation Components

### NavMain
**Location:** `resources/js/components/NavMain.vue`
**Purpose:** Main navigation menu

### NavFooter
**Location:** `resources/js/components/NavFooter.vue`
**Purpose:** Footer navigation

### NavUser
**Location:** `resources/js/components/NavUser.vue`
**Purpose:** User menu with profile/settings links

### Breadcrumbs
**Location:** `resources/js/components/Breadcrumbs.vue`
**Purpose:** Breadcrumb navigation

---

## UI Components (Design System)

### Button
**Location:** `resources/js/components/ui/button/Button.vue`
**Purpose:** Reusable button component
**Features:** Variants, sizes, icons
**Based on:** Radix Vue + Tailwind

### Input
**Location:** `resources/js/components/ui/input/Input.vue`
**Purpose:** Text input field
**Based on:** Radix Vue

### Label
**Location:** `resources/js/components/ui/label/Label.vue`
**Purpose:** Form label component
**Based on:** Radix Vue

### Textarea
**Location:** `resources/js/components/ui/textarea/Textarea.vue`
**Purpose:** Multi-line text input
**Based on:** Radix Vue

### Select
**Location:** `resources/js/components/ui/select/` (multiple files)
**Purpose:** Dropdown select component
**Based on:** Radix Vue Select

### Checkbox
**Location:** `resources/js/components/ui/checkbox/Checkbox.vue`
**Purpose:** Checkbox input
**Based on:** Radix Vue Checkbox

### Dialog / DialogModal
**Location:** `resources/js/components/ui/dialog/` (multiple files)
**Purpose:** Modal dialog component
**Based on:** Radix Vue Dialog

### Toast / Toaster
**Location:** `resources/js/components/ui/toast/` (multiple files)
**Purpose:** Notification toasts
**Based on:** Radix Vue Toast
**Used by:** Notification system for user feedback

### Card
**Location:** `resources/js/components/ui/card/` (multiple files)
**Purpose:** Card container component
**Based on:** Radix Vue + Tailwind

### Tabs
**Location:** `resources/js/components/ui/tabs/` (multiple files)
**Purpose:** Tabbed content component
**Based on:** Radix Vue Tabs

---

## Feature Components

### Recipe Components

#### RecipeCard
**Location:** `resources/js/components/Recipes/RecipeCard.vue`
**Purpose:** Display recipe summary card
**Props:** recipe object
**Features:** Image, title, cooking time, servings, favorite indicator

#### RecipeForm
**Location:** `resources/js/components/Recipes/RecipeForm.vue`
**Purpose:** Recipe creation/editing form
**Features:** Ingredients, instructions, nutrition, categories

#### RecipeImportForm
**Location:** `resources/js/components/Recipes/RecipeImportForm.vue`
**Purpose:** Import recipe from URL
**Features:** URL input, AI-powered parsing

### Meal Plan Components

#### MealPlanCalendar
**Location:** `resources/js/components/MealPlans/MealPlanCalendar.vue`
**Purpose:** Calendar view of meal plan
**Features:** Day columns, drag-and-drop, meal assignments

#### MealPlanDay
**Location:** `resources/js/components/MealPlans/MealPlanDay.vue`
**Purpose:** Single day in meal plan
**Features:** Meal slots, servings display

#### MealAssignmentCard
**Location:** `resources/js/components/MealPlans/MealAssignmentCard.vue`
**Purpose:** Individual meal assignment
**Features:** Recipe info, servings, to-cook flag

### Shopping List Components

#### ShoppingListCard
**Location:** `resources/js/components/ShoppingLists/ShoppingListCard.vue`
**Purpose:** Shopping list summary card
**Features:** Item count, purchased indicator

#### ShoppingListItem
**Location:** `resources/js/components/ShoppingLists/ShoppingListItem.vue`
**Purpose:** Individual shopping list item
**Features:** Checkbox, quantity, delete button

#### BarcodeScanner
**Location:** `resources/js/components/ShoppingLists/BarcodeScanner.vue`
**Purpose:** Mobile barcode scanning
**Library:** Quagga2
**Features:** Camera access, product lookup

### Collection Components

#### CollectionCard
**Location:** `resources/js/components/Collections/CollectionCard.vue`
**Purpose:** Collection summary card
**Features:** Recipe count, name, thumbnail

---

## Utility Components

### Heading / HeadingSmall
**Location:** `resources/js/components/Heading.vue`, `HeadingSmall.vue`
**Purpose:** Semantic heading components with consistent styling

### Icon
**Location:** `resources/js/components/Icon.vue`
**Purpose:** Icon wrapper component
**Library:** Lucide Vue Next

### InputError
**Location:** `resources/js/components/InputError.vue`
**Purpose:** Validation error message display

### Pagination
**Location:** `resources/js/components/Pagination.vue`
**Purpose:** Pagination controls

### DeleteUser
**Location:** `resources/js/components/DeleteUser.vue`
**Purpose:** User account deletion confirmation

---

## Authentication Components

### Auth Pages
**Location:** `resources/js/pages/auth/`
- `Login.vue` - User login
- `Register.vue` - User registration
- `PasswordReset.vue` - Password reset flow

---

## Settings Components

### Settings Pages
**Location:** `resources/js/pages/settings/`
- `Profile.vue` - User profile settings
- `Password.vue` - Password change
- `ApiTokens.vue` - API token management

### AppearanceTabs
**Location:** `resources/js/components/AppearanceTabs.vue`
**Purpose:** Theme/appearance settings (light/dark mode)

---

## Page Components (Inertia)

### Landing
**Location:** `resources/js/pages/Landing.vue`
**Purpose:** Landing page

### Dashboard
**Location:** `resources/js/pages/Dashboard.vue`
**Purpose:** User dashboard

### Demo/Toasts
**Location:** `resources/js/pages/Demo/Toasts.vue`
**Purpose:** Toast notification demo page

---

## Component Patterns

### Reusability
- **Highly Reusable:** UI components (Button, Input, Dialog, etc.)
- **Feature-Specific:** Recipe, Meal Plan, Shopping List components
- **Page-Level:** Inertia page components

### Design System
Based on **Radix Vue** with **Tailwind CSS** styling:
- Unstyled, accessible component primitives
- Custom styling via Tailwind utility classes
- Consistent spacing, colors, typography

### State Management
- **Vue 3 Composition API** throughout
- **Composables** for shared logic
- **Prop drilling** for simple cases
- **Provide/inject** for theme/settings

### Icons
**Library:** Lucide Vue Next (0.468.0)
- Wrapped in `Icon.vue` component
- Consistent sizing and coloring

---

## Component Count Summary

| Category | Count |
|----------|-------|
| Layout | 8 |
| UI/Base | 20+ |
| Recipe | 5+ |
| Meal Plan | 6+ |
| Shopping List | 5+ |
| Collection | 3+ |
| Authentication | 4+ |
| Settings | 4+ |
| Utility | 8+ |
| **Total** | **65+** |

---

## Notable Third-Party Libraries

- **Radix Vue** (1.9.11) - Unstyled component primitives
- **Lucide Vue Next** (0.468.0) - Icons
- **VueUse** (12.0.0) - Composition utilities
- **Motion-v** (1.0.0-beta.2) - Animations
- **Quagga2** (1.8.4) - Barcode scanning
- **Vuedraggable** (4.1.0) - Drag and drop
