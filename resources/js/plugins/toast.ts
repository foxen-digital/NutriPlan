import { watchEffect } from 'vue';
import { usePage } from '@inertiajs/vue3';
import { useToast } from '@/components/ui/toast';

export default {
    install() {
        const { toast } = useToast();
        const page = usePage();

        watchEffect(() => {
            const flash = page.props.flash as {
                success?: string;
                error?: string;
                info?: string;
                warning?: string;
            };

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
        });
    }
};
