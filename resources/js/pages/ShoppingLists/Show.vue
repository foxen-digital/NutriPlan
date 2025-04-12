<template>
    <AppLayout>
        <Head :title="`${shoppingList.name} | Shopping Lists`" />

        <div class="mx-auto w-full px-4 sm:px-6 lg:px-8">
            <div class="sm:flex sm:items-center">
                <div class="sm:flex-auto">
                    <h1 class="text-2xl font-semibold leading-6 text-gray-900 dark:text-white">{{ shoppingList.name }}</h1>
                    <p class="mt-2 text-sm text-gray-700 dark:text-gray-400">Created {{ formatDate(shoppingList.created_at) }}</p>
                </div>
                <div class="mt-4 sm:ml-16 sm:mt-0 sm:flex-none">
                    <div class="hidden items-center gap-2 sm:flex">
                        <Button @click="showAddItemModal = true">
                            <PlusIcon class="mr-2 h-4 w-4" />
                            Add Item
                        </Button>
                        <Button @click="showScannerModal = true" variant="outline">
                            <BarcodeIcon class="mr-2 h-4 w-4" />
                            Scan Item
                        </Button>
                    </div>
                </div>
            </div>

            <!-- Floating Action Button for mobile -->
            <div class="fixed right-4 top-4 z-10 flex flex-col-reverse gap-2 sm:hidden">
                <DropdownMenu>
                    <DropdownMenuTrigger asChild>
                        <Button size="icon" class="h-12 w-12 rounded-full">
                            <MenuIcon class="h-6 w-6" />
                        </Button>
                    </DropdownMenuTrigger>
                    <DropdownMenuContent align="end">
                        <DropdownMenuItem @click="showAddItemModal = true">
                            <PlusIcon class="mr-2 h-4 w-4" />
                            Add Item
                        </DropdownMenuItem>
                        <DropdownMenuItem @click="showScannerModal = true">
                            <BarcodeIcon class="mr-2 h-4 w-4" />
                            Scan Item
                        </DropdownMenuItem>
                        <DropdownMenuSeparator />
                        <DropdownMenuItem @click="hidePurchased = !hidePurchased">
                            <EyeOffIcon v-if="hidePurchased" class="mr-2 h-4 w-4" />
                            <EyeIcon v-else class="mr-2 h-4 w-4" />
                            {{ hidePurchased ? 'Show Purchased' : 'Hide Purchased' }}
                        </DropdownMenuItem>
                    </DropdownMenuContent>
                </DropdownMenu>
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
                    <div class="flex items-center space-x-4">
                        <div class="hidden items-center space-x-2 sm:flex">
                            <Switch v-model:checked="hidePurchased" />
                            <Label class="cursor-pointer text-sm"> Hide Purchased </Label>
                        </div>
                        <div class="text-sm text-gray-500 dark:text-gray-400">
                            {{ purchasedCount }} of {{ shoppingList.items.length }} items purchased
                        </div>
                    </div>
                </div>

                <!-- If we're not using categories (all items are uncategorized) -->
                <div v-if="!shoppingList.use_categories" class="divide-y divide-gray-200 rounded-md border dark:divide-gray-800">
                    <draggable v-model="dragItems" item-key="id" handle=".drag-handle" @end="saveItemOrder" :disabled="isDraggingDisabled">
                        <template #item="{ element: item }">
                            <div
                                v-if="shouldShowItem(item)"
                                class="flex items-center justify-between p-4"
                                :class="{ 'opacity-60': item.is_purchased, 'bg-gray-50 dark:bg-gray-900': item.is_purchased }"
                            >
                                <div class="flex items-center gap-3">
                                    <div class="drag-handle mr-1 cursor-move text-gray-400">
                                        <GripVertical class="h-5 w-5" />
                                    </div>
                                    <Checkbox :id="`item-${item.id}`" :checked="item.is_purchased" @update:checked="toggleItemPurchased(item)" />
                                    <div>
                                        <div class="font-medium text-gray-900 dark:text-white" :class="{ 'line-through': item.is_purchased }">
                                            {{ item.name }}
                                        </div>
                                        <div v-if="item.quantity || item.unit" class="text-sm text-gray-500 dark:text-gray-400">
                                            {{ formatAmount(item.quantity) }} {{ item.unit }}
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
                        </template>
                    </draggable>
                </div>

                <!-- If we're using categories -->
                <div v-else class="space-y-4">
                    <div v-for="(items, category) in itemsByCategory" :key="category">
                        <h3 class="mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">{{ category }}</h3>

                        <div class="divide-y divide-gray-200 rounded-md border dark:divide-gray-800">
                            <draggable
                                v-model="categoryDragItems[category]"
                                item-key="id"
                                handle=".drag-handle"
                                @end="saveItemOrder"
                                :disabled="isDraggingDisabled"
                            >
                                <template #item="{ element: item }">
                                    <div
                                        v-if="shouldShowItem(item)"
                                        class="flex items-center justify-between p-4"
                                        :class="{ 'opacity-60': item.is_purchased, 'bg-gray-50 dark:bg-gray-900': item.is_purchased }"
                                    >
                                        <div class="flex items-center gap-3">
                                            <div class="drag-handle mr-1 cursor-move text-gray-400">
                                                <GripVertical class="h-5 w-5" />
                                            </div>
                                            <Checkbox
                                                :id="`item-${item.id}`"
                                                :checked="item.is_purchased"
                                                @update:checked="toggleItemPurchased(item)"
                                            />
                                            <div>
                                                <div class="font-medium text-gray-900 dark:text-white" :class="{ 'line-through': item.is_purchased }">
                                                    {{ item.name }}
                                                </div>
                                                <div v-if="item.quantity || item.unit" class="text-sm text-gray-500 dark:text-gray-400">
                                                    {{ formatAmount(item.quantity) }} {{ item.unit }}
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
                                </template>
                            </draggable>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Add Item Modal -->
        <Dialog :open="showAddItemModal" @update:open="showAddItemModal = $event">
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>Add Item</DialogTitle>
                    <DialogDescription>Add a new item to your shopping list.</DialogDescription>
                </DialogHeader>
                <form @submit.prevent="addItem">
                    <div class="space-y-4 py-4">
                        <div class="relative">
                            <Label for="item-name">Item Name</Label>
                            <Input id="item-name" v-model="itemForm.name" placeholder="e.g., Milk" required @input="searchItems" autocomplete="off" />
                            <InputError :message="itemForm.errors.name" />

                            <!-- Autocomplete Suggestions -->
                            <div
                                v-if="suggestions.length > 0"
                                class="absolute z-10 mt-1 max-h-60 w-full overflow-auto rounded-md bg-white py-1 shadow-lg dark:bg-gray-800"
                            >
                                <div
                                    v-for="(suggestion, index) in suggestions"
                                    :key="index"
                                    class="cursor-pointer px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-700"
                                    @click="selectSuggestion(suggestion)"
                                >
                                    {{ suggestion }}
                                </div>
                            </div>
                        </div>
                        <div class="flex gap-4">
                            <div class="w-1/2">
                                <Label for="item-quantity">Quantity</Label>
                                <Input id="item-quantity" type="number" step="0.5" min="0" v-model="itemForm.quantity" placeholder="e.g., 2" />
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
                        <div class="relative">
                            <Label for="edit-item-name">Item Name</Label>
                            <Input
                                id="edit-item-name"
                                v-model="itemForm.name"
                                placeholder="e.g., Milk"
                                required
                                @input="searchItems"
                                autocomplete="off"
                            />
                            <InputError :message="itemForm.errors.name" />

                            <!-- Autocomplete Suggestions -->
                            <div
                                v-if="suggestions.length > 0"
                                class="absolute z-10 mt-1 max-h-60 w-full overflow-auto rounded-md bg-white py-1 shadow-lg dark:bg-gray-800"
                            >
                                <div
                                    v-for="(suggestion, index) in suggestions"
                                    :key="index"
                                    class="cursor-pointer px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-700"
                                    @click="selectSuggestion(suggestion)"
                                >
                                    {{ suggestion }}
                                </div>
                            </div>
                        </div>
                        <div class="flex gap-4">
                            <div class="w-1/2">
                                <Label for="edit-item-quantity">Quantity</Label>
                                <Input id="edit-item-quantity" type="number" step="0.5" min="0" v-model="itemForm.quantity" placeholder="e.g., 2" />
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
                    <DialogDescription>Are you sure you want to delete this item? This action cannot be undone. </DialogDescription>
                </DialogHeader>
                <DialogFooter>
                    <Button type="button" variant="outline" @click="showDeleteItemModal = false">Cancel</Button>
                    <Button type="button" variant="destructive" @click="deleteItem">Delete</Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>

        <!-- Barcode Scanner Modal -->
        <Dialog :open="showScannerModal" @update:open="showScannerModal = $event">
            <DialogContent class="max-w-md">
                <DialogHeader>
                    <DialogTitle>Scan Barcode</DialogTitle>
                    <DialogDescription>
                        {{ scannerStatusMessage }}
                    </DialogDescription>
                </DialogHeader>

                <div class="relative">
                    <!-- Camera Viewfinder -->
                    <div v-show="!isLoading && !scanError" class="relative aspect-video overflow-hidden rounded bg-black">
                        <video ref="videoElement" class="h-full w-full object-cover"></video>
                        <div class="absolute inset-0 m-8 rounded border-2 border-dashed border-white/50"></div>
                    </div>

                    <!-- Loading State -->
                    <div v-if="isLoading" class="flex flex-col items-center justify-center py-8">
                        <div class="mb-4 h-8 w-8 animate-spin rounded-full border-4 border-primary border-t-transparent"></div>
                        <p>{{ loadingMessage }}</p>
                    </div>

                    <!-- Error State -->
                    <div v-if="scanError" class="rounded-md bg-destructive/10 p-4 text-destructive">
                        <p class="font-medium">{{ scanError }}</p>
                    </div>

                    <!-- Product Not Found State -->
                    <div v-if="barcodeNotFound" class="p-4">
                        <p class="mb-2 font-medium">Product not found for barcode: {{ lastScannedBarcode }}</p>
                        <p class="mb-4 text-sm text-gray-500 dark:text-gray-400">Would you like to add it manually?</p>
                    </div>
                </div>

                <DialogFooter>
                    <div class="flex w-full flex-col justify-between gap-2 sm:flex-row sm:justify-end">
                        <!-- Standard Controls -->
                        <Button v-if="!barcodeNotFound && !scanError" variant="outline" @click="closeScannerModal"> Cancel </Button>

                        <!-- Error Controls -->
                        <Button v-if="scanError" variant="outline" @click="retryScanner"> Retry </Button>

                        <!-- Product Not Found Controls -->
                        <div v-if="barcodeNotFound" class="flex gap-2">
                            <Button variant="outline" @click="resumeScanning"> Scan Again </Button>
                            <Button @click="addManually"> Add Manually </Button>
                        </div>
                    </div>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </AppLayout>
</template>

<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuSeparator, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';
import { Input } from '@/components/ui/input';
import { InputError } from '@/components/ui/input-error';
import { Label } from '@/components/ui/label';
import { Switch } from '@/components/ui/switch';
import useBarcodeScanner from '@/composables/useBarcodeScanner';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import axios from 'axios';
import { debounce } from 'lodash';
import {
    BarcodeIcon,
    EllipsisVerticalIcon,
    EyeIcon,
    EyeOffIcon,
    GripVertical,
    MenuIcon,
    PencilIcon,
    PlusIcon,
    ShoppingCartIcon,
    TrashIcon,
} from 'lucide-vue-next';
import { computed, nextTick, onMounted, onUnmounted, ref, watch } from 'vue';
import draggable from 'vuedraggable';

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
    order: number | null;
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
    items_by_category?: Record<string, ShoppingListItem[]>;
    use_categories: boolean;
}

const props = defineProps<{
    shoppingList: ShoppingList;
}>();

const showAddItemModal = ref(false);
const showEditItemModal = ref(false);
const showDeleteItemModal = ref(false);
const showScannerModal = ref(false);
const currentItem = ref<ShoppingListItem | null>(null);

const itemForm = useForm({
    name: '',
    quantity: '' as string | number,
    unit: '',
    category: '',
});

const suggestions = ref<string[]>([]);
const isSearching = ref(false);

const formatDate = (dateString: string) => {
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
};

const sortedItems = computed(() => {
    // Sort items by purchased status (not purchased first), then by order, and finally by name
    return [...props.shoppingList.items].sort((a, b) => {
        // First, sort by purchased status
        if (a.is_purchased !== b.is_purchased) {
            return a.is_purchased ? 1 : -1;
        }

        // Then, sort by order if available
        const aOrder = typeof a.order === 'number' ? a.order : 0;
        const bOrder = typeof b.order === 'number' ? b.order : 0;
        if (aOrder !== bOrder) {
            return aOrder - bOrder;
        }

        // Finally, sort by name as a fallback
        return a.name.localeCompare(b.name);
    });
});

const sortItemsInCategory = (items: ShoppingListItem[]) => {
    // Sort items by purchased status (not purchased first), then by order, and finally by name
    return [...items].sort((a, b) => {
        // First, sort by purchased status
        if (a.is_purchased !== b.is_purchased) {
            return a.is_purchased ? 1 : -1;
        }

        // Then, sort by order if available
        const aOrder = typeof a.order === 'number' ? a.order : 0;
        const bOrder = typeof b.order === 'number' ? b.order : 0;
        if (aOrder !== bOrder) {
            return aOrder - bOrder;
        }

        // Finally, sort by name as a fallback
        return a.name.localeCompare(b.name);
    });
};

const purchasedCount = computed(() => {
    return props.shoppingList.items.filter((item) => item.is_purchased).length;
});

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

// Barcode scanner functionality
const videoElement = ref<HTMLVideoElement | null>(null);
const isLoading = ref(false);
const loadingMessage = ref('Initializing camera...');
const scanError = ref<string | null>(null);
const barcodeNotFound = ref(false);
const lastScannedBarcode = ref<string | null>(null);
const scannerStatusMessage = computed(() => {
    if (scanError.value) return 'Scanner Error';
    if (barcodeNotFound.value) return 'Product Not Found';
    if (isLoading.value) return 'Loading...';
    return 'Position the barcode in the center of the screen';
});

const { lastDetectedCode, lastError, initializeScanner, startScanning, stopScanning, cleanupResources, lookupBarcode } = useBarcodeScanner();

// Watch for detected barcodes
watch(lastDetectedCode, async (code) => {
    if (code) {
        isLoading.value = true;
        loadingMessage.value = 'Looking up product...';
        lastScannedBarcode.value = code;

        try {
            const result = await lookupBarcode(code);

            if (result.success && result.data) {
                // Product found, open add item modal with pre-filled data
                itemForm.name = result.data.name;
                itemForm.category = (result.data.category || [])[0] || '';

                // Close scanner modal and open add item modal
                showScannerModal.value = false;
                showAddItemModal.value = true;
            } else {
                // Product not found
                barcodeNotFound.value = true;
                scanError.value = null;
                isLoading.value = false;
            }
        } catch (error) {
            scanError.value = `Error: ${error instanceof Error ? error.message : 'Unknown error'}`;
            isLoading.value = false;
        }
    }
});

// Watch for scanner modal changes
watch(showScannerModal, async (isOpen) => {
    if (isOpen) {
        // Complete reset of scanner state
        resetScannerState();
        isLoading.value = true;

        // Make sure video element is ready
        await nextTick();

        // Delay slightly to ensure previous camera session is fully closed
        setTimeout(async () => {
            if (videoElement.value) {
                try {
                    // Perform a complete cleanup first
                    await cleanupResources();

                    // Ensure video srcObject is null before reinitializing
                    if (videoElement.value.srcObject) {
                        const stream = videoElement.value.srcObject as MediaStream;
                        stream.getTracks().forEach((track) => track.stop());
                        videoElement.value.srcObject = null;
                    }

                    // Initialize scanner with fresh state
                    await initializeScanner(videoElement.value);
                    isLoading.value = false;
                    startScanning();
                } catch (error) {
                    isLoading.value = false;
                    scanError.value = `Failed to initialize scanner: ${error instanceof Error ? error.message : 'Unknown error'}`;
                    console.error('Scanner initialization error:', error);
                }
            } else {
                isLoading.value = false;
                scanError.value = 'Video element not found';
            }
        }, 300); // Small delay to ensure cleanup
    } else {
        // Stop scanning when modal closes
        stopScanning();
        cleanupResources();

        // Manually stop all media tracks
        if (videoElement.value && videoElement.value.srcObject) {
            const mediaStream = videoElement.value.srcObject as MediaStream;
            const tracks = mediaStream.getTracks();

            tracks.forEach((track) => track.stop());
            videoElement.value.srcObject = null;
        }
    }
});

// Watch for scanner initialization errors
watch(lastError, (error) => {
    if (error) {
        scanError.value = error;
        isLoading.value = false;
    }
});

const resetScannerState = () => {
    scanError.value = null;
    barcodeNotFound.value = false;
    lastScannedBarcode.value = null;
    isLoading.value = false;

    // If video element still has a stream, clean it up
    if (videoElement.value && videoElement.value.srcObject) {
        const stream = videoElement.value.srcObject as MediaStream;
        stream.getTracks().forEach((track) => track.stop());
        videoElement.value.srcObject = null;
    }
};

const closeScannerModal = () => {
    // Stop scanning and release camera resources
    stopScanning();
    cleanupResources();

    // Manually stop all media tracks
    if (videoElement.value && videoElement.value.srcObject) {
        const mediaStream = videoElement.value.srcObject as MediaStream;
        const tracks = mediaStream.getTracks();

        tracks.forEach((track) => track.stop());
        videoElement.value.srcObject = null;
    }

    showScannerModal.value = false;
};

const retryScanner = async () => {
    resetScannerState();
    isLoading.value = true;

    if (videoElement.value) {
        try {
            await initializeScanner(videoElement.value);
            isLoading.value = false;
            startScanning();
        } catch (error) {
            isLoading.value = false;
            scanError.value = `Failed to initialize scanner: ${error instanceof Error ? error.message : 'Unknown error'}`;
        }
    }
};

const resumeScanning = () => {
    barcodeNotFound.value = false;
    lastScannedBarcode.value = null;
    startScanning();
};

const addManually = () => {
    // If we have a barcode but no product name, we can still pre-fill the name with the barcode
    if (lastScannedBarcode.value && !itemForm.name) {
        itemForm.name = `Unknown item (${lastScannedBarcode.value})`;
    }

    // Close scanner modal and open add item modal
    closeScannerModal();
    showAddItemModal.value = true;
};

// Debounced search function
const debouncedSearch = debounce(async (query: string) => {
    if (!query || query.length < 2) {
        suggestions.value = [];
        return;
    }

    isSearching.value = true;

    try {
        const response = await axios.get(route('api.item-search'), {
            params: { query },
        });
        suggestions.value = response.data;
    } catch (error) {
        console.error('Error searching items:', error);
        suggestions.value = [];
    } finally {
        isSearching.value = false;
    }
}, 300);

// Function to trigger the search
const searchItems = () => {
    debouncedSearch(itemForm.name as string);
};

// Function to handle selecting a suggestion
const selectSuggestion = (suggestion: string) => {
    itemForm.name = suggestion;
    suggestions.value = [];
};

// Clear suggestions when clicking outside
const handleClickOutside = () => {
    if (suggestions.value.length > 0) {
        suggestions.value = [];
    }
};

// Add event listener when component is mounted
onMounted(() => {
    document.addEventListener('click', handleClickOutside);
    initializeDragItems();
});

// Remove event listener when component is unmounted
onUnmounted(() => {
    document.removeEventListener('click', handleClickOutside);
});

// Close suggestions when add item modal is closed
watch(showAddItemModal, (isOpen) => {
    if (!isOpen) {
        suggestions.value = [];
    }
});

// Close suggestions when edit item modal is closed
watch(showEditItemModal, (isOpen) => {
    if (!isOpen) {
        suggestions.value = [];
    }
});

const hidePurchased = ref(false);

const dragItems = ref<ShoppingListItem[]>([]);
const categoryDragItems = ref<Record<string, ShoppingListItem[]>>({});
const isDraggingDisabled = ref(false);

// Initialize dragItems with sorted items when component is mounted
onMounted(() => {
    initializeDragItems();
});

// Watch for changes in props.shoppingList.items
watch(
    () => props.shoppingList.items,
    () => {
        initializeDragItems();
    },
    { deep: true },
);

const initializeDragItems = () => {
    // For non-categorized items, initialize dragItems
    if (!props.shoppingList.use_categories) {
        dragItems.value = [...sortedItems.value];
    } else {
        // For categorized items, initialize categoryDragItems
        const categories = Object.keys(props.shoppingList.items_by_category || {});
        categories.forEach((category) => {
            categoryDragItems.value[category] = sortItemsInCategory(props.shoppingList.items_by_category?.[category] || []);
        });
    }
};

// Filter items based on the hide purchased toggle
const shouldShowItem = (item: ShoppingListItem) => {
    // If hidePurchased is true, filter out purchased items
    if (hidePurchased.value && item.is_purchased) {
        return false;
    }
    // Otherwise show all items
    return true;
};

// Filter and sort items by category
const itemsByCategory = computed(() => {
    if (!props.shoppingList.items_by_category) return {};

    const result: Record<string, ShoppingListItem[]> = {};

    Object.keys(props.shoppingList.items_by_category).forEach((category) => {
        // Only include categories with visible items
        const filteredItems = (props.shoppingList.items_by_category?.[category] || []).filter((item) => shouldShowItem(item));

        if (filteredItems.length > 0) {
            result[category] = filteredItems;
        }
    });

    return result;
});

// Save the new order of items
const saveItemOrder = () => {
    const allItems = props.shoppingList.use_categories ? Object.values(categoryDragItems.value).flat() : dragItems.value;

    // Get all item IDs in their new order
    const itemIds = allItems.map((item) => item.id);

    // Send the new order to the server
    axios
        .put(route('shopping-lists.items.order', props.shoppingList.id), {
            item_ids: itemIds,
        })
        .then(() => {
            // Success notification could be added here
        })
        .catch((error) => {
            console.error('Error saving item order:', error);
        });
};
</script>
