import { useToast } from '@/components/ui/toast';
import { router } from '@inertiajs/vue3';
import { AlertOctagon, AlertTriangle, CheckCircle, Info } from 'lucide-vue-next';

interface Flash {
    success?: string;
    error?: string;
    info?: string;
    warning?: string;
}

export default {
    install() {
        const { toast } = useToast();

        // Process flash messages only after page navigation completes
        router.on('success', (event) => {
            const flash = event.detail.page.props.flash as Flash;

            // Only process if flash messages exist
            if (!flash) return;

            if (flash.success) {
                toast({
                    title: 'Success',
                    description: flash.success,
                    variant: 'success',
                    icon: CheckCircle,
                });
            }

            if (flash.error) {
                toast({
                    title: 'Error',
                    description: flash.error,
                    variant: 'destructive',
                    icon: AlertOctagon,
                });
            }

            if (flash.info) {
                toast({
                    title: 'Information',
                    description: flash.info,
                    variant: 'info',
                    icon: Info,
                });
            }

            if (flash.warning) {
                toast({
                    title: 'Warning',
                    description: flash.warning,
                    variant: 'warning',
                    icon: AlertTriangle,
                });
            }
        });
    },
};
