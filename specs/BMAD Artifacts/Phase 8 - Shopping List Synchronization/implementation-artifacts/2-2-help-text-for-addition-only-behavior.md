# Story 2.2: Help Text for Addition-Only Behavior

Status: review

<!-- Note: Validation is optional. Run validate-create-story for quality check before dev-story. -->

## Story

As a user,
I want to understand how shopping list synchronization works,
So that I know what to expect when I add new meals to my plan.

## Acceptance Criteria

1. **Given** the shopping list detail page, **When** the help section is displayed, **Then** explanatory text states: "When you add new meals to your plan, ingredients are automatically added to existing shopping lists. Ingredients are never automatically removed."

2. **Given** the help text is displayed, **When** the text is rendered, **Then** the text is formatted for readability **And** the text is positioned in a visible location on the shopping list page

3. **Given** a user viewing the shopping list, **When** the user reads the help text, **Then** the addition-only behavior is clearly communicated **And** the user understands that manual adjustments are preserved

## Tasks / Subtasks

- [x] Add help text section to `resources/js/pages/ShoppingLists/Show.vue` (AC: 1, 2, 3)
  - [x] Import `InfoIcon` from `lucide-vue-next`
  - [x] Add info callout box after header section, before items list
  - [x] Use Tailwind classes for styling (info blue background, dark mode compatible)
  - [x] Include the exact required text from acceptance criteria

- [x] Verify visual placement and formatting (AC: 2)
  - [x] Text is clearly visible and readable
  - [x] Works in both light and dark modes
  - [x] Does not interfere with existing UI elements

- [x] Create/update frontend tests if applicable (AC: 3)
  - [x] Verify help text renders on shopping list page (no specific test file exists for this page - verified manually)

- [x] Run full test suite and linting
  - [x] Run `npm run test` (375 tests passed)
  - [x] Run `npm run lint` (passed)

## Dev Notes

### What Already Exists (Do NOT re-implement)

**Shopping List Page:**
- `resources/js/pages/ShoppingLists/Show.vue` — Full shopping list detail page with:
  - Header section with list name and created date (lines 5-23)
  - Add Item and Scan Item buttons
  - Mobile floating action button
  - Shopping list items display (categorized or uncategorized)
  - Modals for add/edit/delete items and barcode scanner

**UI Components Available:**
- Radix Vue tooltip system at `resources/js/components/ui/tooltip/`
- Tailwind CSS 3.4.1 with custom HSL color system
- Lucide Vue Next icons (already used: `PlusIcon`, `BarcodeIcon`, `EyeIcon`, etc.)

### What Needs to Be Created

**Add info callout box to Show.vue:**

A simple info callout section positioned between the header and the items list. The design should:
1. Be subtle but visible (not intrusive)
2. Use a light blue background with info icon (standard info callout pattern)
3. Support dark mode
4. Be positioned prominently but not interfere with the main content

### Recommended Implementation

Add this section after the header controls (after line 23) and before the empty state/items list:

```vue
<!-- Help Text: Auto-sync behavior explanation -->
<div class="mt-4 rounded-md border border-blue-200 bg-blue-50 p-4 dark:border-blue-800 dark:bg-blue-950/30">
    <div class="flex">
        <div class="flex-shrink-0">
            <InfoIcon class="h-5 w-5 text-blue-600 dark:text-blue-400" />
        </div>
        <div class="ml-3">
            <p class="text-sm text-blue-700 dark:text-blue-300">
                When you add new meals to your plan, ingredients are automatically added to existing shopping lists.
                Ingredients are never automatically removed.
            </p>
        </div>
    </div>
</div>
```

**Import required icon:**
```typescript
import { InfoIcon } from 'lucide-vue-next';
```

### Placement Location

The help text should be added in the template section of `Show.vue`:

```
Line 23:  </div>  <!-- End of header controls -->
           ↓
         [ADD HELP TEXT HERE]
           ↓
Line 52:  <div v-if="!shoppingList.items || shoppingList.items.length === 0" ...>  <!-- Empty state -->
```

This places the help text prominently at the top of the page, visible immediately after the header.

### Alternative: Collapsible Help (Optional Enhancement)

If the info box feels too prominent, consider using a collapsible alert or tooltip pattern. However, the simple inline info callout is recommended for clarity and accessibility.

### Styling Reference

The Tailwind classes used:
- `mt-4` — Top margin to separate from header
- `rounded-md` — Rounded corners
- `border border-blue-200` — Blue border (light mode)
- `dark:border-blue-800` — Darker blue border (dark mode)
- `bg-blue-50` — Light blue background (light mode)
- `dark:bg-blue-950/30` — Semi-transparent dark blue (dark mode)
- `text-blue-700 dark:text-blue-300` — Readable text in both modes

### Testing Strategy

**Manual Testing:**
1. Navigate to a shopping list detail page
2. Verify help text appears below header, above items list
3. Toggle dark mode and verify colors are readable
4. Test on mobile viewport for responsiveness

**Automated Testing (Optional):**
If there's an existing test file for `Show.vue`, add a simple assertion to verify the help text renders:

```typescript
// Example test assertion
expect(wrapper.text()).toContain('ingredients are automatically added');
expect(wrapper.text()).toContain('never automatically removed');
```

### Project Structure Notes

**Files to MODIFY:**
- `resources/js/pages/ShoppingLists/Show.vue` — Add help text section

**Files NOT to modify:**
- Any backend files (this is a frontend-only change)
- Toast system (already implemented in Story 2.1)
- Any other Vue components

### Previous Story Intelligence (Story 2.1)

From Story 2.1 (Toast Notification on List Update):
- The toast notification system is fully functional via `useShoppingListSync.ts`
- `AppShell.vue` has Echo listener initialization pattern
- Users now see toast notifications when lists are auto-updated
- **Connection:** This story complements the toast by providing persistent, always-visible context

**Key Learning:** Users receive transient feedback (toast) when lists are updated. This help text provides permanent, always-available context about the addition-only behavior.

### Git Intelligence (Recent Work)

Recent commits show Phase 8 is nearly complete:
- `9e03042` — Show toast when shopping list updated (Story 2.1)
- `fbc1aa1` — Add MealAssignment synchronization tests
- `caf177a` — Add shopping list update job, event, tests

This story (2.2) is the final story in Epic 2 and Phase 8.

### Critical: Exact Text Required

The acceptance criteria specifies the exact text that must be displayed:

> "When you add new meals to your plan, ingredients are automatically added to existing shopping lists. Ingredients are never automatically removed."

**Do not modify this text.** It was carefully crafted to:
1. Explain the automatic addition behavior
2. Explicitly state ingredients are never removed
3. Reassure users that manual adjustments are preserved

### References

- Epic 2 Story 2.2 Requirements [Source: _bmad-output/planning-artifacts/epics.md#Story 2.2 lines 349–369]
- Architecture: Addition-only constraint (NFR6) [Source: _bmad-output/planning-artifacts/architecture.md]
- Shopping List Page [Source: resources/js/pages/ShoppingLists/Show.vue]
- Project Context: Tailwind CSS patterns [Source: _bmad-output/project-context.md]
- Story 2.1 (toast notifications) [Source: _bmad-output/implementation-artifacts/2-1-toast-notification-on-list-update.md]

## Dev Agent Record

### Agent Model Used

claude-sonnet-4-6

### Debug Log References

N/A - Implementation was straightforward

### Completion Notes List

- Help text info callout added to `resources/js/pages/ShoppingLists/Show.vue`
- `InfoIcon` imported from `lucide-vue-next`
- Info callout positioned after header section, before items list
- Tailwind classes used: blue background with dark mode support
- Exact text from acceptance criteria included
- All frontend tests pass (375 passed, 1 skipped)
- All backend tests pass (573 passed, 1 skipped)
- ESLint passes with no errors
- This is the final story in Epic 2 and Phase 8

### File List

- `resources/js/pages/ShoppingLists/Show.vue` (modified - added InfoIcon import and help text callout)
