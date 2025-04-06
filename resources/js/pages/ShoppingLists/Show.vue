<template>
    <AppLayout>

        <Head :title="`${shoppingList.name} | Shopping Lists`" />

        <div class="mx-auto w-full px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between">
                <div>
                    <div class="flex items-center gap-4">
                        <Button variant="outline" size="icon" asChild>
                            <Link :href="route('shopping-lists.index')">
                            <ArrowLeftIcon class="h-4 w-4" />
                            </Link>
                        </Button>
                        <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">{{ shoppingList.name }}</h1>
                    </div>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Created {{
                        formatDate(shoppingList.created_at) }}</p>
                </div>
                <div class="flex items-center gap-2">
                    <Button @click="showRenameModal = true" variant="outline">
                        <PencilIcon class="mr-2 h-4 w-4" />
                        Rename
                    </Button>
                    <Button @click="showAddItemModal = true">
                        <PlusIcon class="mr-2 h-4 w-4" />
                        Add Item
                    </Button>
                </div>
            </div>

            <div v-if="!shoppingList.items || shoppingList.items.length === 0" class="mt-8 text-center">
                <div class="mx-auto h-12 w-12 text-gray-400">
                    <ShoppingCartIcon class="h-12 w-12" />
                </div>
                <h3 class="mt-2 text-sm font-semibold text-gray-900 dark:text-white">No items in this list</h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Add items to start your shopping list.</p>
                <div class="mt-6">
                    <Button @click="showAddItemModal = true">
                        <PlusIcon class="mr-2 h-4 w-4" />
                        Add Item
                    </Button>
                </div>
            </div>

            <div v-else class="mt-6">
                <div class="mb-2 flex justify-between">
                    <h2 class="text-lg font-medium text-gray-900 dark:text-white">Shopping List Items</h2>
                    <div class="text-sm text-gray-500 dark:text-gray-400">
                        {{ purchasedCount }} of {{ shoppingList.items.length }} items purchased
                    </div>
                </div>

                <div class="divide-y divide-gray-200 rounded-md border dark:divide-gray-800">
                    <div v-for="item in sortedItems" :key="item.id" class="flex items-center justify-between p-4"
                        :class="{ 'opacity-60': item.is_purchased, 'bg-gray-50 dark:bg-gray-900': item.is_purchased }">
                        <div class="flex items-center gap-3">
                            <Checkbox :id="`item-${item.id}`" :checked="item.is_purchased"
                                @update:checked="toggleItemPurchased(item)" />
                            <div>
                                <div class="font-medium text-gray-900 dark:text-white"
                                    :class="{ 'line-through': item.is_purchased }">
                                    {{ item.name }}
                                </div>
                                <div v-if="item.quantity || item.unit" class="text-sm text-gray-500 dark:text-gray-400">
                                    {{ formatAmount(item.quantity) }} {{ item.unit }}
                                </div>
                                <div v-if="item.category" class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                    <Badge variant="outline" class="text-xs">{{ item.category }}</Badge>
                                </div>
                            </div>
                        </div>
                        <div>
                            <DropdownMenu>
                                <DropdownMenuTrigger as="div">
                                    <Button variant="ghost" size="icon">
                                        <EllipsisVerticalIcon class="h-5 w-5" />
                                    </Button>
                                </DropdownMenuTrigger>
                                <DropdownMenuContent align="end">
                                    <DropdownMenuItem @click="editItem(item)">
                                        <PencilIcon class="mr-2 h-4 w-4" />
                                        Edit
                                    </DropdownMenuItem>
                                    <DropdownMenuItem @click="confirmDeleteItem(item)">
                                        <TrashIcon class="mr-2 h-4 w-4" />
                                        Delete
                                    </DropdownMenuItem>
                                </DropdownMenuContent>
                            </DropdownMenu>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Rename List Modal -->
        <Dialog :open="showRenameModal" @update:open="showRenameModal = $event">
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>Rename Shopping List</DialogTitle>
                    <DialogDescription>Change the name of your shopping list.</DialogDescription>
                </DialogHeader>
                <form @submit.prevent="renameList">
                    <div class="space-y-4 py-4">
                        <div>
                            <Label for="list-name">List Name</Label>
                            <Input id="list-name" v-model="renameForm.name" placeholder="e.g., Weekly Groceries"
                                required />
                            <InputError :message="renameForm.errors.name" />
                        </div>
                    </div>
                    <DialogFooter>
                        <Button type="button" variant="outline" @click="showRenameModal = false">Cancel</Button>
                        <Button type="submit" :disabled="renameForm.processing">Save</Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>

        <!-- Add Item Modal -->
        <Dialog :open="showAddItemModal" @update:open="showAddItemModal = $event">
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>Add Item</DialogTitle>
                    <DialogDescription>Add a new item to your shopping list.</DialogDescription>
                </DialogHeader>
                <form @submit.prevent="addItem">
                    <div class="space-y-4 py-4">
                        <div>
                            <Label for="item-name">Item Name</Label>
                            <Input id="item-name" v-model="itemForm.name" placeholder="e.g., Milk" required />
                            <InputError :message="itemForm.errors.name" />
                        </div>
                        <div class="flex gap-4">
                            <div class="w-1/2">
                                <Label for="item-quantity">Quantity</Label>
                                <Input id="item-quantity" type="number" step="0.5" min="0" v-model="itemForm.quantity"
                                    placeholder="e.g., 2" />
                                <InputError :message="itemForm.errors.quantity" />
                            </div>
                            <div class="w-1/2">
                                <Label for="item-unit">Unit</Label>
                                <Input id="item-unit" v-model="itemForm.unit" placeholder="e.g., lbs" />
                                <InputError :message="itemForm.errors.unit" />
                            </div>
                        </div>
                        <div>
                            <Label for="item-category">Category (Optional)</Label>
                            <Input id="item-category" v-model="itemForm.category" placeholder="e.g., Produce" />
                            <InputError :message="itemForm.errors.category" />
                        </div>
                    </div>
                    <DialogFooter>
                        <Button type="button" variant="outline" @click="showAddItemModal = false">Cancel</Button>
                        <Button type="submit" :disabled="itemForm.processing">Add Item</Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>

        <!-- Edit Item Modal -->
        <Dialog :open="showEditItemModal" @update:open="showEditItemModal = $event">
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>Edit Item</DialogTitle>
                    <DialogDescription>Edit the details of this shopping list item.</DialogDescription>
                </DialogHeader>
                <form @submit.prevent="updateItem">
                    <div class="space-y-4 py-4">
                        <div>
                            <Label for="edit-item-name">Item Name</Label>
                            <Input id="edit-item-name" v-model="itemForm.name" placeholder="e.g., Milk" required />
                            <InputError :message="itemForm.errors.name" />
                        </div>
                        <div class="flex gap-4">
                            <div class="w-1/2">
                                <Label for="edit-item-quantity">Quantity</Label>
                                <Input id="edit-item-quantity" type="number" step="0.5" min="0"
                                    v-model="itemForm.quantity" placeholder="e.g., 2" />
                                <InputError :message="itemForm.errors.quantity" />
                            </div>
                            <div class="w-1/2">
                                <Label for="edit-item-unit">Unit</Label>
                                <Input id="edit-item-unit" v-model="itemForm.unit" placeholder="e.g., lbs" />
                                <InputError :message="itemForm.errors.unit" />
                            </div>
                        </div>
                        <div>
                            <Label for="edit-item-category">Category (Optional)</Label>
                            <Input id="edit-item-category" v-model="itemForm.category" placeholder="e.g., Produce" />
                            <InputError :message="itemForm.errors.category" />
                        </div>
                    </div>
                    <DialogFooter>
                        <Button type="button" variant="outline" @click="showEditItemModal = false">Cancel</Button>
                        <Button type="submit" :disabled="itemForm.processing">Save Changes</Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>

        <!-- Delete Item Confirmation Modal -->
        <Dialog :open="showDeleteItemModal" @update:open="showDeleteItemModal = $event">
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>Delete Item</DialogTitle>
                    <DialogDescription>Are you sure you want to delete this item? This action cannot be undone.
                    </DialogDescription>
                </DialogHeader>
                <DialogFooter>
                    <Button type="button" variant="outline" @click="showDeleteItemModal = false">Cancel</Button>
                    <Button type="button" variant="destructive" @click="deleteItem">Delete</Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </AppLayout>
</template>

<script setup lang="ts">
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';
import { Input } from '@/components/ui/input';
import { InputError } from '@/components/ui/input-error';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ArrowLeftIcon, EllipsisVerticalIcon, PencilIcon, PlusIcon, ShoppingCartIcon, TrashIcon } from 'lucide-vue-next';
import { computed, ref } from 'vue';

interface ShoppingListItem {
    id: number;
    shopping_list_id: number;
    ingredient_id: number | null;
    name: string;
    quantity: number | null;
    unit: string | null;
    category: string | null;
    is_custom: boolean;
    is_purchased: boolean;
    created_at: string;
    updated_at: string;
}

interface ShoppingList {
    id: number;
    name: string;
    user_id: number;
    created_at: string;
    updated_at: string;
    items: ShoppingListItem[];
}

const props = defineProps<{
    shoppingList: ShoppingList;
}>();

const showRenameModal = ref(false);
const showAddItemModal = ref(false);
const showEditItemModal = ref(false);
const showDeleteItemModal = ref(false);
const currentItem = ref<ShoppingListItem | null>(null);

const renameForm = useForm({
    name: props.shoppingList.name,
});

const itemForm = useForm({
    name: '',
    quantity: '' as string | number,
    unit: '',
    category: '',
});

const formatDate = (dateString: string) => {
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
};

const sortedItems = computed(() => {
    // Sort items by purchased status (not purchased first), then by name
    return [...props.shoppingList.items].sort((a, b) => {
        if (a.is_purchased !== b.is_purchased) {
            return a.is_purchased ? 1 : -1;
        }
        return a.name.localeCompare(b.name);
    });
});

const purchasedCount = computed(() => {
    return props.shoppingList.items.filter((item) => item.is_purchased).length;
});

const renameList = () => {
    renameForm.put(route('shopping-lists.update', props.shoppingList.id), {
        onSuccess: () => {
            showRenameModal.value = false;
        },
    });
};

const addItem = () => {
    itemForm.post(route('shopping-lists.items.store', props.shoppingList.id), {
        onSuccess: () => {
            showAddItemModal.value = false;
            itemForm.reset();
        },
    });
};

const editItem = (item: ShoppingListItem) => {
    currentItem.value = item;
    itemForm.name = item.name;
    itemForm.quantity = item.quantity ?? '';
    itemForm.unit = item.unit ?? '';
    itemForm.category = item.category ?? '';
    showEditItemModal.value = true;
};

const updateItem = () => {
    if (!currentItem.value) return;

    itemForm.put(route('shopping-lists.items.update', [props.shoppingList.id, currentItem.value.id]), {
        onSuccess: () => {
            showEditItemModal.value = false;
            currentItem.value = null;
        },
    });
};

const confirmDeleteItem = (item: ShoppingListItem) => {
    currentItem.value = item;
    showDeleteItemModal.value = true;
};

const deleteItem = () => {
    if (!currentItem.value) return;

    const form = useForm({});
    form.delete(route('shopping-lists.items.destroy', [props.shoppingList.id, currentItem.value.id]), {
        onSuccess: () => {
            showDeleteItemModal.value = false;
            currentItem.value = null;
        },
    });
};

const toggleItemPurchased = (item: ShoppingListItem) => {
    const form = useForm({});
    form.post(route('shopping-lists.items.toggle-purchased', [props.shoppingList.id, item.id]));
};

const formatAmount = (amount: number | string | null): string => {
    if (typeof amount === 'string') {
        amount = parseFloat(amount);
    }

    if (!amount) return '0';

    // For small values, show more decimal places
    if (amount < 0.1) {
        return amount.toFixed(2);
    }

    // For values less than 1, show one decimal place
    if (amount < 1) {
        return amount.toFixed(1);
    }

    // For values with decimal parts, show one decimal place
    if (amount % 1 !== 0) {
        return amount.toFixed(1);
    }

    // For whole numbers, show no decimal places
    return amount.toFixed(0);
};
</script>
