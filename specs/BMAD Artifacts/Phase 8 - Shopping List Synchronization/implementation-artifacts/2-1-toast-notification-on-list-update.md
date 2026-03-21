# Story 2.1: Toast Notification on List Update

Status: done

<!-- Note: Validation is optional. Run validate-create-story for quality check before dev-story. -->

## Story

As a user,
I want to see a notification when my shopping list is automatically updated,
So that I know when ingredients have been added to my list.

## Acceptance Criteria

1. **Given** a ShoppingListUpdated event is dispatched, **When** the event is broadcast to the user's private channel, **Then** the frontend receives the event with message and shopping list ID

2. **Given** the frontend receives a ShoppingListUpdated event, **When** the user is viewing any page in the application, **Then** a toast notification appears with the message from the event **And** the toast is displayed for a minimum of 3 seconds **And** the toast automatically dismisses after the duration

3. **Given** the toast notification is displayed, **When** the toast is visible, **Then** the notification uses the existing toast component styling **And** the notification appears in the standard toast position

4. **Given** multiple ShoppingListUpdated events fire in quick succession, **When** multiple toasts are triggered, **Then** each toast is displayed independently **And** toasts are stacked appropriately

## Tasks / Subtasks

- [x] Create composable: `resources/js/composables/useShoppingListSync.ts` (AC: 1, 2, 3)
  - [x] Define TypeScript interface for ShoppingListUpdatedEvent
  - [x] Implement Echo.private listener for `shopping.list.updated` event
  - [x] Call `useToast()` to show notification with 3-second duration
  - [x] Handle missing/null userId gracefully

- [x] Initialize listener in AppShell.vue (AC: 2)
  - [x] Import `useShoppingListSync` composable
  - [x] Call `initializeListeners()` in `onMounted` hook
  - [x] Ensure listener is only initialized once (use module-level flag pattern from `useRecipeImport`)

- [x] Create feature test: `tests/Feature/Jobs/UpdateShoppingListJobTest.php` update (AC: 1)
  - [x] Add test to verify `ShoppingListUpdated` event is dispatched with correct payload (already exists - verify coverage)

- [x] Create frontend test: `resources/js/composables/__tests__/useShoppingListSync.test.ts` (AC: 2, 3, 4)
  - [x] Test: listener initializes only when userId exists
  - [x] Test: toast is called with correct options on event receipt
  - [x] Test: multiple events result in multiple toasts

- [x] Run full test suite and verify no regressions
  - [x] Run `composer test:php`
  - [x] Run `vendor/bin/pint --dirty --format agent`

## Dev Notes

### What Already Exists (Do NOT re-implement)

**Backend (from Stories 1.3 and 1.4):**
- `app/Events/ShoppingListUpdated.php` — Event class already implemented with:
  - `broadcastOn()`: PrivateChannel `user.{userId}`
  - `broadcastAs()`: `shopping.list.updated`
  - `broadcastWith()`: `{ message, shoppingListId }`
  - Constructor takes: `userId`, `message`, `shoppingListId`

- `app/Jobs/UpdateShoppingListJob.php` — Dispatches `ShoppingListUpdated` event on completion

**Frontend Infrastructure:**
- `resources/js/components/ui/toast/` — Full toast component system using Radix Vue
- `resources/js/plugins/toast.ts` — Flash message toast plugin (handles server-side flash)
- `resources/js/composables/useRecipeImport.ts` — **Exact pattern to follow** for Echo event listening
- `resources/js/components/AppShell.vue` — Where Echo listeners are initialized via `onMounted`
- `resources/js/echo.ts` — Laravel Echo initialization (Reverb/Pusher)

### What Needs to Be Created

**New Composable:** `resources/js/composables/useShoppingListSync.ts`

This composable should follow the exact pattern from `useRecipeImport.ts`:
1. Import `useToast` from `@/components/ui/toast`
2. Get `userId` from Inertia page props via `usePage()`
3. Define `initializeListeners()` function that:
   - Checks if userId exists (return early if not)
   - Uses module-level flag to prevent duplicate initialization
   - Listens on `window.Echo.private('user.{userId}')` for `.shopping.list.updated`
   - Calls `toast()` with message from event payload

### Pattern to Follow: useRecipeImport.ts

```typescript
// resources/js/composables/useRecipeImport.ts — COPY THIS PATTERN

import { useToast } from '@/components/ui/toast';
import { router, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import '../echo';

declare global {
    interface Window {
        Echo: {
            private: (channel: string) => {
                listen: (event: string, callback: (data: any) => void) => void;
            };
        };
    }
}

interface CustomPageProps {
    auth: { user: { id: number } | null };
}

let listenersInitialized = false; // Module-level flag to prevent duplicates

export function useRecipeImport() {
    const { toast } = useToast();
    const page = usePage<CustomPageProps>();
    const userId = computed(() => page.props.auth?.user?.id);

    function initializeListeners() {
        if (!userId.value) return;
        if (listenersInitialized) return;
        listenersInitialized = true;

        window.Echo.private(`user.${userId.value}`)
            .listen('.recipe.import.completed', (e) => {
                toast({ title: 'Success', description: e.message, variant: 'success', duration: 3000 });
            });
    }

    return { initializeListeners };
}
```

### New Composable Structure

```typescript
// resources/js/composables/useShoppingListSync.ts

import { useToast } from '@/components/ui/toast';
import type { ToastProps } from '@/components/ui/toast/use-toast';
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import '../echo';

declare global {
    interface Window {
        Echo: {
            private: (channel: string) => {
                listen: (event: string, callback: (data: any) => void) => void;
            };
        };
    }
}

interface CustomPageProps {
    auth: { user: { id: number } | null };
}

interface ShoppingListUpdatedEvent {
    message: string;
    shoppingListId: number;
}

let shoppingListListenersInitialized = false;

export function useShoppingListSync() {
    const { toast } = useToast();
    const page = usePage<CustomPageProps>();
    const userId = computed(() => page.props.auth?.user?.id);

    function initializeListeners() {
        if (!userId.value) return;
        if (shoppingListListenersInitialized) return;
        shoppingListListenersInitialized = true;

        window.Echo.private(`user.${userId.value}`)
            .listen('.shopping.list.updated', (e: ShoppingListUpdatedEvent) => {
                const toastOptions: ToastProps = {
                    title: 'Shopping List Updated',
                    description: e?.message ?? 'Your shopping list has been updated',
                    variant: 'success',
                    duration: 3000,
                };

                toast(toastOptions);
            });
    }

    return { initializeListeners };
}
```

### AppShell.vue Integration

```typescript
// resources/js/components/AppShell.vue — ADD THESE LINES

import { useRecipeImport } from '@/composables/useRecipeImport';
import { useShoppingListSync } from '@/composables/useShoppingListSync'; // ADD THIS

onMounted(() => {
    isOpen.value = localStorage.getItem('sidebar') !== 'false';

    // Initialize Echo listeners
    useRecipeImport().initializeListeners();
    useShoppingListSync().initializeListeners(); // ADD THIS
});
```

### Event Payload Structure

The `ShoppingListUpdated` event broadcasts:
```json
{
    "message": "List updated with new ingredients from [meal name]",
    "shoppingListId": 123
}
```

The message is already constructed in `UpdateShoppingListJob` with the recipe title included.

### Toast Display Requirements (AC: 2, 3)

- **Duration:** 3000ms (3 seconds minimum)
- **Variant:** `success` (green toast for positive action)
- **Position:** Standard toast position (handled by `ToastViewport` in AppShell)
- **Stacking:** Multiple toasts stack automatically via Radix Vue toast system

### Testing Strategy

**Backend Test (already exists, verify coverage):**
The existing `tests/Feature/Jobs/UpdateShoppingListJobTest.php` should include verification that `ShoppingListUpdated` event is dispatched. Check that this is covered.

**Frontend Test (optional but recommended):**
Create `resources/js/composables/__tests__/useShoppingListSync.test.ts` using Vitest to verify:
1. Listener only initializes when userId exists
2. Toast is called with correct parameters
3. Module-level flag prevents duplicate initialization

### Critical: Echo Event Naming

The event name in `broadcastAs()` uses dot notation: `shopping.list.updated`
When listening in Echo, prefix with a dot: `.shopping.list.updated`

```typescript
// ✅ Correct
window.Echo.private(`user.${userId.value}`)
    .listen('.shopping.list.updated', callback);

// ❌ Wrong (missing dot prefix for custom broadcastAs)
window.Echo.private(`user.${userId.value}`)
    .listen('shopping.list.updated', callback);
```

### File Structure Requirements

**Files to CREATE:**
- `resources/js/composables/useShoppingListSync.ts` — Echo listener composable

**Files to MODIFY:**
- `resources/js/components/AppShell.vue` — Import and initialize listener

**Files NOT to modify:**
- `app/Events/ShoppingListUpdated.php` — Complete from Story 1.4
- `app/Jobs/UpdateShoppingListJob.php` — Complete from Story 1.4
- `resources/js/components/ui/toast/*` — Existing Radix Vue toast system

### Project Structure Notes

- Composables location: `resources/js/composables/` (follows existing pattern)
- Test location: `resources/js/composables/__tests__/` (if frontend tests are added)
- Uses existing Echo initialization from `resources/js/echo.ts`
- Uses existing toast system from `resources/js/components/ui/toast/`

### Previous Story Intelligence (Story 1.5)

From Story 1.5 (Synchronization Testing):
- The sync pipeline is fully tested end-to-end
- `config(['queue.default' => 'sync'])` is used for synchronous job execution in tests
- `Event::fake([ShoppingListUpdated::class])` pattern is used to prevent actual broadcasting in tests
- The full flow (Observer → Service → Job → Event) is verified

**Key Learning:** The `ShoppingListUpdated` event is dispatched after the job completes. The event is faked in backend tests, so frontend integration is the remaining piece.

### References

- Epic 2 Story 2.1 Requirements [Source: _bmad-output/planning-artifacts/epics.md#Story 2.1 lines 319–347]
- Architecture: Toast notification flow [Source: _bmad-output/planning-artifacts/architecture.md#UI Feedback Mechanism lines 290–304]
- Existing event implementation [Source: app/Events/ShoppingListUpdated.php]
- Pattern to follow [Source: resources/js/composables/useRecipeImport.ts]
- AppShell listener initialization [Source: resources/js/components/AppShell.vue]
- Toast component [Source: resources/js/components/ui/toast/]
- Story 1.4 (event creation) [Source: _bmad-output/implementation-artifacts/1-4-background-list-update-job.md]
- Story 1.5 (test patterns) [Source: _bmad-output/implementation-artifacts/1-5-synchronization-testing.md]

## Dev Agent Record

### Agent Model Used
claude-sonnet-4-6 (code-review)

### Debug Log References
N/A

### Completion Notes List
- Created `resources/js/composables/useShoppingListSync.ts` following exact pattern from `useRecipeImport.ts`
- Integrated listener into `AppShell.vue` via `onMounted` hook alongside existing recipe import listener
- Uses existing toast component system from `@/components/ui/toast` (success variant, 3-second duration)
- User ID obtained from Inertia page props via `usePage()` with computed ref
- Module-level flag prevents duplicate listener initialization
- Echo listener on private channel `user.{userId}` for `.shopping.list.updated` event
- Payload guard added (`e?.message ?? 'Your shopping list has been updated'`) for malformed events
- Window.Echo callback type corrected to `(data: any) => void` for consistency with `useRecipeImport.ts`
- `console.log` debug statement removed from production code
- Backend event dispatch verified via existing `UpdateShoppingListJobTest.php` (no changes needed)
- Frontend tests created: `resources/js/composables/__tests__/useShoppingListSync.test.ts` (6 tests, all pass)

### File List

- `resources/js/composables/useShoppingListSync.ts` (created)
- `resources/js/components/AppShell.vue` (modified - added import and listener initialization)
- `resources/js/composables/__tests__/useShoppingListSync.test.ts` (created - 6 frontend tests)
- `tests/Feature/Jobs/UpdateShoppingListJobTest.php` (no changes - existing coverage verified)
