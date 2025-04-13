# Notification Toasts

## Overview
Implement a toast notification system using radix-vue to display notifications to users throughout the application.

## Requirements
1. Display notification messages to users in a non-intrusive way
2. Support different notification types (success, error, info, warning)
3. Notifications can be triggered from:
   - Controller redirects with flash messages
   - JSON API responses
   - Future: Real-time events via Laravel Reverb

## Architecture

### Frontend Components
1. A global Toast Provider component using radix-vue's Toast component
2. Toast state management to handle multiple notifications
3. Consistent styling for different notification types

### Backend Integration
1. Flash message handling for Inertia-based redirects
2. Standardized JSON response format for API endpoints
3. Future integration with Laravel Reverb for real-time events

## Implementation Details

### Frontend Implementation

#### Toast UI Components
Create the following components in the UI component collection (`resources/js/components/ui/toast/`):

1. `toast.vue` - The base toast component 
2. `toast-provider.vue` - The global provider that manages toast instances
3. `toast-viewport.vue` - The container for displaying toasts
4. `toast-action.vue` - Optional component for toast actions (buttons)
5. `toast-close.vue` - Button to dismiss a toast
6. `toast-title.vue` - Title component for toasts
7. `toast-description.vue` - Description component for toasts
8. `use-toast.ts` - A composable for programmatically creating toasts

Each component will leverage radix-vue's toast primitives while following the project's existing UI component patterns.

#### Toast Integration with App Structure
The toast provider should be added at the highest level possible to ensure it's available throughout the application:

1. Add the ToastProvider to the AppShell component in `resources/js/components/AppShell.vue`:
   - This is the root layout component that wraps all pages
   - It already uses other providers like SidebarProvider
   - The ToastProvider should be added as the outer wrapper to ensure toasts are available globally

2. Add the ToastViewport to the same AppShell component:
   - Position it outside other layout elements to ensure it floats above all content
   - Configure it to appear in the top-right corner by default

#### Inertia Flash Message Handling
Implement flash message handling at the app initialization level:

1. Create a Vue plugin or composable that:
   - Watches for Inertia page changes
   - Checks for flash messages in the Inertia shared data
   - Creates appropriate toast notifications
   
2. Register this plugin in `resources/js/app.ts` during app setup

#### Composable API
Create a `useToast` composable function that provides a simple API for creating toasts:

```typescript
// Example usage
const { toast } = useToast()

// Simple toast
toast({
  title: 'Success',
  description: 'Your changes have been saved',
  variant: 'success',
  duration: 5000,
})

// Toast with actions
toast({
  title: 'Error',
  description: 'Failed to save changes',
  variant: 'destructive',
  action: {
    label: 'Try Again',
    onClick: () => saveChanges(),
  },
})
```

### Backend Implementation

#### Flash Message Structure
Standard flash message format:
- Type: success, error, info, warning
- Message: String content to display
- Duration: Optional time (ms) to display the toast before auto-dismissing

The Inertia middleware has been updated to include these flash messages in the shared data.

#### JSON Response Format
Standardized format for API endpoints:
```json
{
  "data": { ... },
  "notification": {
    "type": "success",
    "message": "Operation completed successfully",
    "duration": 5000
  }
}
```

#### Future Reverb Integration
Prepare for real-time notification events:
- Define event structure for notifications
- Configure Reverb channel for user-specific notifications

## Implementation Steps

1. **Create Toast UI Components**
   - Create the toast directory in the UI components collection
   - Implement all toast-related components following the project's UI pattern
   - Create the useToast composable

2. **Update App Shell Component**
   - Add ToastProvider to wrap the entire AppShell content
   - Add ToastViewport positioned for optimal visibility

3. **Implement Flash Message Integration**
   - Create a plugin or composable for Inertia flash message handling
   - Register it in the main app.ts file
   - Test with existing controller redirects

4. **Extend API Response Handler**
   - Update API response handling to check for notification objects
   - Display toasts based on API response notifications

5. **Documentation & Testing**
   - Document the toast component API for other developers
   - Create test cases for different notification scenarios
   - Test across various devices and screen sizes

## Code Examples

### AppShell.vue Integration

```vue
<script setup lang="ts">
import { SidebarProvider } from '@/components/ui/sidebar';
import { ToastProvider, ToastViewport } from '@/components/ui/toast';
import { onMounted, ref } from 'vue';

interface Props {
    variant?: 'header' | 'sidebar';
}

defineProps<Props>();

const isOpen = ref(true);

onMounted(() => {
    isOpen.value = localStorage.getItem('sidebar') !== 'false';
});

const handleSidebarChange = (open: boolean) => {
    isOpen.value = open;
    localStorage.setItem('sidebar', String(open));
};
</script>

<template>
    <ToastProvider>
        <div v-if="variant === 'header'" class="flex min-h-screen w-full flex-col">
            <slot />
        </div>
        <SidebarProvider v-else :default-open="isOpen" :open="isOpen" @update:open="handleSidebarChange">
            <slot />
        </SidebarProvider>
        
        <ToastViewport class="fixed top-4 right-4 flex flex-col gap-2 z-50" />
    </ToastProvider>
</template>
```

### Flash Message Handler

```typescript
// resources/js/plugins/toast.ts
import { useToast } from '@/components/ui/toast';
import { usePage } from '@inertiajs/vue3';
import { watch } from 'vue';

export default {
  install(app) {
    const { toast } = useToast();
    const page = usePage();
    
    watch(() => page.props.flash, (flash) => {
      // Check each type of flash message
      if (flash.success) {
        toast({
          title: 'Success',
          description: flash.success,
          variant: 'success',
        });
      }
      
      if (flash.error) {
        toast({
          title: 'Error',
          description: flash.error,
          variant: 'destructive',
        });
      }
      
      if (flash.info) {
        toast({
          title: 'Information',
          description: flash.info,
          variant: 'info',
        });
      }
      
      if (flash.warning) {
        toast({
          title: 'Warning',
          description: flash.warning,
          variant: 'warning',
        });
      }
    }, { deep: true });
  }
};
```

## Testing Plan
1. Unit tests for toast component rendering
2. Integration tests for flash message capture
3. API endpoint tests for notification response format
4. E2E tests for toast display after user actions

## Future Enhancements
1. Stacked toast management for multiple simultaneous notifications
2. Customizable themes and animations
3. Actionable toasts with buttons or links
4. Mobile-optimized positioning and styling 