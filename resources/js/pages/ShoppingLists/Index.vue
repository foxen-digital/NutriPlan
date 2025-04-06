<template>
    <AppLayout>
        <Head title="Shopping Lists | NutriPlan" />

        <div class="mx-auto w-full px-4 sm:px-6 lg:px-8">
            <div class="sm:flex sm:items-center">
                <div class="sm:flex-auto">
                    <h1 class="text-2xl font-semibold leading-6 text-gray-900 dark:text-white">Shopping Lists</h1>
                    <p class="mt-2 text-sm text-gray-700 dark:text-gray-400">Create and manage your shopping lists for easy grocery shopping.</p>
                </div>
                <div class="mt-4 sm:ml-16 sm:mt-0 sm:flex-none">
                    <Button @click="isCreateModalOpen = true">
                        <ShoppingBasketIcon class="mr-2 h-4 w-4" />
                        New Shopping List
                    </Button>
                </div>
            </div>

            <div v-if="shoppingLists.length === 0" class="mt-8 text-center">
                <div class="mx-auto h-12 w-12 text-gray-400">
                    <ShoppingCartIcon class="h-12 w-12" />
                </div>
                <h3 class="mt-2 text-sm font-semibold text-gray-900 dark:text-white">No shopping lists</h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Get started by creating a new shopping list.</p>
                <div class="mt-6">
                    <Button @click="isCreateModalOpen = true">
                        <PlusIcon class="mr-2 h-4 w-4" />
                        New Shopping List
                    </Button>
                </div>
            </div>

            <div v-else class="mt-8 flow-root">
                <ul role="list" class="divide-y divide-gray-200 dark:divide-gray-800">
                    <li v-for="shoppingList in shoppingLists" :key="shoppingList.id" class="py-4">
                        <div class="flex items-center space-x-4">
                            <div class="flex-shrink-0">
                                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-blue-100 dark:bg-blue-900">
                                    <ShoppingBasketIcon class="h-6 w-6 text-blue-600 dark:text-blue-300" />
                                </div>
                            </div>
                            <div class="min-w-0 flex-1">
                                <Link :href="route('shopping-lists.show', shoppingList.id)" class="focus:outline-none">
                                    <p class="text-sm font-medium text-gray-900 dark:text-white">
                                        {{ shoppingList.name }}
                                    </p>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Created {{ formatDate(shoppingList.created_at) }}</p>
                                </Link>
                            </div>
                            <div class="flex-shrink-0">
                                <Badge v-if="shoppingList.items_count">{{ shoppingList.items_count }} items</Badge>
                            </div>
                            <div class="flex-shrink-0">
                                <DropdownMenu>
                                    <DropdownMenuTrigger as="div">
                                        <Button variant="ghost" size="icon">
                                            <EllipsisVerticalIcon class="h-5 w-5" />
                                        </Button>
                                    </DropdownMenuTrigger>
                                    <DropdownMenuContent align="end">
                                        <DropdownMenuItem asChild>
                                            <Link :href="route('shopping-lists.show', shoppingList.id)">
                                                <EyeIcon class="mr-2 h-4 w-4" />
                                                View
                                            </Link>
                                        </DropdownMenuItem>
                                        <DropdownMenuItem @click="confirmDeleteShoppingList(shoppingList)">
                                            <TrashIcon class="mr-2 h-4 w-4" />
                                            Delete
                                        </DropdownMenuItem>
                                    </DropdownMenuContent>
                                </DropdownMenu>
                            </div>
                        </div>
                    </li>
                </ul>
            </div>
        </div>

        <!-- Create Shopping List Modal -->
        <Dialog :open="isCreateModalOpen" @update:open="isCreateModalOpen = $event">
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>Create New Shopping List</DialogTitle>
                    <DialogDescription>Give your shopping list a name to get started.</DialogDescription>
                </DialogHeader>
                <form @submit.prevent="createShoppingList">
                    <div class="space-y-4 py-4">
                        <div>
                            <Label for="name">List Name</Label>
                            <Input id="name" v-model="createForm.name" placeholder="e.g., Weekly Groceries" required />
                            <InputError :message="createForm.errors.name" />
                        </div>
                    </div>
                    <DialogFooter>
                        <Button type="button" variant="outline" @click="isCreateModalOpen = false">Cancel</Button>
                        <Button type="submit" :disabled="createForm.processing">Create List</Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>

        <!-- Delete Confirmation Modal -->
        <Dialog :open="isDeleteModalOpen" @update:open="isDeleteModalOpen = $event">
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>Delete Shopping List</DialogTitle>
                    <DialogDescription>Are you sure you want to delete this shopping list? This action cannot be undone. </DialogDescription>
                </DialogHeader>
                <DialogFooter>
                    <Button type="button" variant="outline" @click="isDeleteModalOpen = false">Cancel</Button>
                    <Button type="button" variant="destructive" @click="deleteShoppingList">Delete</Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </AppLayout>
</template>

<script setup lang="ts">
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';
import { Input } from '@/components/ui/input';
import { InputError } from '@/components/ui/input-error';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { EllipsisVerticalIcon, EyeIcon, PlusIcon, ShoppingBasketIcon, ShoppingCartIcon, TrashIcon } from 'lucide-vue-next';
import { ref } from 'vue';

interface ShoppingList {
    id: number;
    name: string;
    created_at: string;
    updated_at: string;
    items_count?: number;
}

defineProps<{
    shoppingLists: ShoppingList;
}>();

const isCreateModalOpen = ref(false);
const isDeleteModalOpen = ref(false);
const shoppingListToDelete = ref<ShoppingList | null>(null);

const createForm = useForm({
    name: '',
});

const form = useForm({});

const formatDate = (dateString: string) => {
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
};

const createShoppingList = () => {
    createForm.post(route('shopping-lists.store'), {
        onSuccess: () => {
            isCreateModalOpen.value = false;
            createForm.reset();
        },
    });
};

const confirmDeleteShoppingList = (shoppingList: ShoppingList) => {
    shoppingListToDelete.value = shoppingList;
    isDeleteModalOpen.value = true;
};

const deleteShoppingList = () => {
    if (shoppingListToDelete.value) {
        form.delete(route('shopping-lists.destroy', shoppingListToDelete.value.id), {
            onSuccess: () => {
                isDeleteModalOpen.value = false;
                shoppingListToDelete.value = null;
            },
        });
    }
};
</script>
