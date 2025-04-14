import { useToast } from '@/components/ui/toast';
import type { ToastProps } from '@/components/ui/toast/use-toast';
import { router, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import '../echo';

// Augment the window interface to include Echo
declare global {
    interface Window {
        Echo: {
            private: (channel: string) => {
                listen: (event: string, callback: (data: any) => void) => void;
            };
        };
        Pusher: any;
    }
}

// Extending the Inertia PageProps interface
interface AuthUser {
    id: number;
    [key: string]: any;
}

interface Auth {
    user: AuthUser | null;
    [key: string]: any;
}

interface CustomPageProps {
    auth: Auth;
    [key: string]: any;
}

export function useRecipeImport() {
    const { toast } = useToast();
    const page = usePage<CustomPageProps>();

    // User ID is available in auth.user.id from the Inertia props
    const userId = computed(() => page.props.auth?.user?.id);

    /**
     * Initialize Echo listeners for recipe import notifications
     */
    function initializeListeners() {
        if (!userId.value) {
            // If user is not logged in, don't set up the listeners
            return;
        }

        // Listen for recipe import completed events on the user's private channel
        window.Echo.private(`user.${userId.value}`).listen(
            '.recipe.import.completed',
            (e: { status: 'success' | 'error'; message: string; recipeId: number | null }) => {
                console.log('Recipe Import Event Received:', e);

                const title = e.status === 'success' ? 'Success' : 'Error';
                const variant = e.status === 'success' ? 'success' : 'destructive';

                // Show toast notification
                const toastOptions: ToastProps = {
                    title,
                    description: e.message,
                    variant,
                };

                if (e.status === 'success' && e.recipeId !== null) {
                    Object.assign(toastOptions, {
                        action: {
                            label: 'View Recipe',
                            onClick: () => {
                                // Navigate to the recipe page using Inertia router
                                router.visit(`/recipes/${e.recipeId}`);
                            },
                            altText: 'View the imported recipe',
                        },
                    });
                }

                toast(toastOptions);
            },
        );
    }

    return {
        initializeListeners,
    };
}
