import { useToast } from '@/components/ui/toast';
import type { ToastProps } from '@/components/ui/toast/use-toast';
import { router, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import '../echo';

// Extending the Inertia PageProps interface
interface Auth {
    user: { id: number } | null;
    [key: string]: unknown;
}

interface CustomPageProps {
    auth: Auth;
    [key: string]: unknown;
}

// Narrow shape of the `recipe` prop as passed by RecipeController::show()
interface RecipePageProps extends CustomPageProps {
    recipe?: { id: number };
}

interface InstructionNormalizationEvent {
    status: 'success' | 'warning' | 'error';
    message: string;
    recipeId?: number;
    recipeUrl?: string;
}

let instructionListenersInitialized = false;

export function useInstructionNormalization() {
    const { toast } = useToast();
    const page = usePage<CustomPageProps>();

    // User ID is available in auth.user.id from the Inertia props
    const userId = computed(() => page.props.auth?.user?.id);

    /**
     * Whether the user is currently viewing the given recipe's show page.
     * `usePage()` returns Inertia's single reactive page object, which is
     * updated in place on every navigation, so reading it inside the Echo
     * callback always reflects the current page.
     */
    function isViewingRecipe(recipeId: number): boolean {
        return (
            page.component === 'Recipes/Show' &&
            (page.props as RecipePageProps).recipe?.id === recipeId
        );
    }

    /**
     * Initialize Echo listeners for instruction normalization notifications
     */
    function initializeListeners() {
        if (!userId.value) {
            // If user is not logged in, don't set up the listeners
            return;
        }

        if (instructionListenersInitialized) {
            return;
        }
        instructionListenersInitialized = true;

        // Listen for instruction normalization events on the user's private channel
        window.Echo.private(`user.${userId.value}`).listen(
            '.recipe.instructions.normalized',
            (e: InstructionNormalizationEvent) => {
                const variant: ToastProps['variant'] =
                    e.status === 'success' ? 'success' : e.status === 'warning' ? 'warning' : 'destructive';
                const title = e.status === 'error' ? 'Error' : e.status === 'warning' ? 'Notice' : 'Success';

                const toastOptions: ToastProps = {
                    title,
                    description: e.message,
                    variant,
                    duration: 3000,
                };

                if (e.recipeId != null && e.recipeUrl) {
                    Object.assign(toastOptions, {
                        action: {
                            label: 'View Recipe',
                            onClick: () => {
                                // Navigate to the recipe page using Inertia router
                                router.visit(e.recipeUrl as string);
                            },
                            altText: 'View the recipe',
                        },
                    });
                }

                // If the user is already viewing this recipe, refresh the
                // instructions in place. Only meaningful on 'success' — on
                // 'warning'/'error' nothing was written to the database.
                if (e.status === 'success' && e.recipeId != null && isViewingRecipe(e.recipeId)) {
                    router.reload({ only: ['recipe'] });
                }

                toast(toastOptions);
            }
        );
    }

    return {
        initializeListeners,
    };
}
