<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';

import HeadingSmall from '@/components/HeadingSmall.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle, DialogTrigger } from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import AppLayout from '@/layouts/AppLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import { type BreadcrumbItem } from '@/types';
import { usePage } from '@inertiajs/vue3';

interface Token {
    id: number;
    name: string;
    created_at: string;
    last_used_at: string | null;
}

interface Props {
    tokens: Token[];
}

const props = defineProps<Props>();
const page = usePage();

// Access the token directly from the flash
const flashToken = computed(() => {
    const flash = page.props.flash as Record<string, any> | undefined;
    return flash?.token as string | undefined;
});
const showTokenDialog = ref(false);

// Watch for token changes to show the dialog
watch(
    flashToken,
    (newToken) => {
        if (newToken) {
            showTokenDialog.value = true;
        }
    },
    { immediate: true },
);

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'API Tokens',
        href: '/settings/tokens',
    },
];

const tokenForm = useForm({
    name: '',
});

const submitTokenForm = () => {
    tokenForm.post(route('settings.tokens.store'), {
        preserveScroll: true,
        onSuccess: () => {
            tokenForm.reset();
            showCreateDialog.value = false;
        },
    });
};

const showCreateDialog = ref(false);
const showDeleteConfirmDialog = ref(false);
const tokenToDeleteId = ref<number | null>(null);

const formattedDate = (dateString: string | null) => {
    if (!dateString) return 'Never';
    return new Date(dateString).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    });
};

// Renamed original deleteToken to trigger modal
const requestDeleteToken = (id: number) => {
    tokenToDeleteId.value = id;
    showDeleteConfirmDialog.value = true;
};

// New function to handle actual deletion after confirmation
const confirmDeleteToken = () => {
    if (tokenToDeleteId.value === null) return;

    useForm({}).delete(route('settings.tokens.destroy', tokenToDeleteId.value), {
        preserveScroll: true,
        onSuccess: () => {
            showDeleteConfirmDialog.value = false; // Close dialog on success
            tokenToDeleteId.value = null;
        },
        onError: () => {
            // Handle error if needed, e.g., show a notification
            showDeleteConfirmDialog.value = false; // Close dialog even on error
            tokenToDeleteId.value = null;
        },
    });
};

const hasTokens = computed(() => props.tokens && props.tokens.length > 0);

const copyTokenToClipboard = () => {
    if (flashToken.value) {
        window.navigator.clipboard.writeText(flashToken.value);
        showTokenDialog.value = false;
    }
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="API Tokens" />

        <SettingsLayout>
            <div class="flex flex-col space-y-6">
                <HeadingSmall title="API Tokens" description="Create API tokens for third-party applications to access your account securely." />

                <!-- Token created dialog -->
                <Dialog v-model:open="showTokenDialog" v-if="flashToken">
                    <DialogContent>
                        <DialogHeader>
                            <DialogTitle>Token created successfully</DialogTitle>
                            <DialogDescription> Please copy your new token now. It will not be shown again. </DialogDescription>
                        </DialogHeader>
                        <div class="mt-2">
                            <div class="mt-1 flex rounded-md shadow-sm">
                                <pre class="rounded-md border p-2 text-xs">{{ flashToken }}</pre>
                            </div>
                        </div>
                        <DialogFooter>
                            <Button type="button" @click="copyTokenToClipboard"> Copy and Close </Button>
                        </DialogFooter>
                    </DialogContent>
                </Dialog>

                <div class="flex justify-end">
                    <Dialog v-model:open="showCreateDialog">
                        <DialogTrigger asChild>
                            <Button>Create API Token</Button>
                        </DialogTrigger>
                        <DialogContent class="sm:max-w-[425px]">
                            <DialogHeader>
                                <DialogTitle>Create API Token</DialogTitle>
                                <DialogDescription>
                                    API tokens allow third-party services to authenticate with our application on your behalf.
                                </DialogDescription>
                            </DialogHeader>
                            <form @submit.prevent="submitTokenForm">
                                <div class="grid gap-4 py-4">
                                    <div class="grid grid-cols-4 items-center gap-4">
                                        <Label for="token-name" class="col-span-4"> Token Name </Label>
                                        <Input id="token-name" v-model="tokenForm.name" placeholder="Token Name" class="col-span-4" />
                                        <InputError class="col-span-4" :message="tokenForm.errors.name" />
                                    </div>
                                </div>
                                <DialogFooter>
                                    <Button type="button" variant="outline" @click="showCreateDialog = false">Cancel</Button>
                                    <Button type="submit" :disabled="tokenForm.processing">Create</Button>
                                </DialogFooter>
                            </form>
                        </DialogContent>
                    </Dialog>
                </div>

                <div v-if="hasTokens" class="rounded-md border">
                    <Table>
                        <caption class="mb-2 mt-4 text-sm text-muted-foreground">
                            A list of your personal access tokens.
                        </caption>
                        <TableHeader>
                            <TableRow>
                                <TableHead>Name</TableHead>
                                <TableHead>Created</TableHead>
                                <TableHead>Last Used</TableHead>
                                <TableHead class="text-right">Actions</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow v-for="token in tokens" :key="token.id">
                                <TableCell>{{ token.name }}</TableCell>
                                <TableCell>{{ formattedDate(token.created_at) }}</TableCell>
                                <TableCell>{{ formattedDate(token.last_used_at) }}</TableCell>
                                <TableCell class="text-right">
                                    <Button variant="destructive" size="sm" @click="requestDeleteToken(token.id)"> Delete </Button>
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                </div>

                <div v-else class="rounded-md border p-8 text-center">
                    <p class="text-muted-foreground">You have not created any API tokens yet.</p>
                </div>
            </div>

            <!-- Delete Token Confirmation Dialog -->
            <Dialog v-model:open="showDeleteConfirmDialog">
                <DialogContent class="sm:max-w-md">
                    <DialogHeader>
                        <DialogTitle>Delete API Token</DialogTitle>
                        <DialogDescription>
                            Are you sure you want to delete this API token? This action cannot be undone.
                        </DialogDescription>
                    </DialogHeader>
                    <DialogFooter class="sm:justify-end">
                        <Button variant="outline" @click="showDeleteConfirmDialog = false">Cancel</Button>
                        <Button variant="destructive" @click="confirmDeleteToken">Delete Token</Button>
                    </DialogFooter>
                </DialogContent>
            </Dialog>
        </SettingsLayout>
    </AppLayout>
</template>
