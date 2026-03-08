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

interface Auth {
    user: {
        id: number;
    } | null;
    [key: string]: unknown;
}

interface CustomPageProps {
    auth: Auth;
    [key: string]: unknown;
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

    /**
     * Initialize Echo listeners for shopping list update notifications
     */
    function initializeListeners() {
        if (!userId.value) {
            return;
        }

        if (shoppingListListenersInitialized) {
            return;
        }
        shoppingListListenersInitialized = true;

        window.Echo.private(`user.${userId.value}`).listen('.shopping.list.updated', (e: ShoppingListUpdatedEvent) => {
            const toastOptions: ToastProps = {
                title: 'Shopping List Updated',
                description: e?.message ?? 'Your shopping list has been updated',
                variant: 'success',
                duration: 3000,
            };

            toast(toastOptions);
        });
    }

    return {
        initializeListeners,
    };
}
