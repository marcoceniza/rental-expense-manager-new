<script setup lang="ts">
import RecurringModal from '@/components/RecurringModal.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { router } from '@inertiajs/vue3';
import { Pencil, Plus, Repeat, Trash2 } from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';

defineOptions({
    layout: AppLayout,
});

interface TransactionDescription {
    description: string;
    amount: number;
}

interface Category {
    id: number;
    name: string;
    type: string;
    description?: string;
    amount?: number;
    transaction_descriptions?: TransactionDescription[];
}

interface RecurringTransaction {
    id: number;
    category_id: number;
    amount: number;
    frequency: string;
    start_date: string;
    description: string;
}

interface Props {
    recurringTransactions?: RecurringTransaction[];
    categories?: Category[];
    errors?: Record<string, string[]>;
}

const props = withDefaults(defineProps<Props>(), {
    recurringTransactions: () => [],
    categories: () => [],
    errors: () => ({}),
});

const showModal = ref(false);
const isEditMode = ref(false);
const editingId = ref<number | null>(null);
const loading = ref(false);

const formData = ref({
    category_id: '' as number | string,
    amount: 0,
    frequency: 'monthly',
    start_date: new Date().toISOString().split('T')[0],
    description: '',
});

const resetForm = () => {
    formData.value = {
        category_id: '',
        amount: 0,
        frequency: 'monthly',
        start_date: new Date().toISOString().split('T')[0],
        description: '',
    };
    isEditMode.value = false;
    editingId.value = null;
};

const openModal = () => {
    resetForm();
    showModal.value = true;
};

const editRecurring = (r: RecurringTransaction) => {
    isEditMode.value = true;
    editingId.value = r.id;

    formData.value = {
        category_id: r.category_id,
        amount: r.amount,
        frequency: r.frequency,
        start_date: r.start_date,
        description: r.description,
    };

    showModal.value = true;
};

const closeModal = () => {
    showModal.value = false;
    resetForm();
};

const handleSubmit = () => {
    loading.value = true;

    const options = {
        preserveScroll: true,
        onSuccess: () => closeModal(),
        onFinish: () => {
            loading.value = false;
        },
    };

    if (isEditMode.value) {
        router.put(route('admin.recurring.update', { recurring: editingId.value }), formData.value, options);
    } else {
        router.post(route('admin.recurring.store'), formData.value, options);
    }
};

const deleteRecurring = (id: number) => {
    if (confirm('Are you sure you want to delete this recurring transaction?')) {
        router.delete(route('admin.recurring.destroy', { recurring: id }), {
            preserveScroll: true,
            only: ['recurringTransactions'],
        });
    }
};

const safeCategories = computed(() => props.categories || []);

const selectedCategory = computed(() => {
    return safeCategories.value.find((category) => category.id === formData.value.category_id);
});

const descriptionOptions = computed(() => {
    return selectedCategory.value?.transaction_descriptions ?? [];
});

const categoryMap = computed(() => {
    const map: Record<number, string> = {};
    safeCategories.value.forEach((c) => {
        map[c.id] = c.name;
    });
    return map;
});

watch(
    () => formData.value.category_id,
    (categoryId, previousCategoryId) => {
        if (!categoryId || categoryId === previousCategoryId) {
            return;
        }

        const selectedCategory = safeCategories.value.find((category) => category.id === categoryId);

        if (!selectedCategory) {
            formData.value.description = '';
            formData.value.amount = 0;
            return;
        }

        if (selectedCategory.transaction_descriptions?.length) {
            const option = selectedCategory.transaction_descriptions[0];
            formData.value.description = option.description;
            formData.value.amount = option.amount;
            return;
        }

        formData.value.description = selectedCategory.description ?? '';
        formData.value.amount = selectedCategory.amount ?? 0;
    },
);

watch(
    () => formData.value.description,
    (description) => {
        if (!description || !descriptionOptions.value.length) {
            return;
        }

        const selectedOption = descriptionOptions.value.find((option) => option.description === description);

        if (selectedOption) {
            formData.value.amount = selectedOption.amount;
        }
    },
);

const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('en-PH', {
        style: 'currency',
        currency: 'PHP',
    }).format(amount);
};
</script>

<template>
    <div class="space-y-8">
        <div class="flex flex-col justify-between gap-4 md:flex-row md:items-center">
            <div>
                <h2 class="flex items-center gap-3 text-3xl font-bold tracking-tight text-slate-900 max-sm:text-2xl">
                    <Repeat class="h-8 w-8" />
                    Recurring Transactions
                </h2>
                <p class="mt-1 text-slate-500">Automate your monthly income and expenses.</p>
            </div>

            <button
                @click="openModal"
                class="flex cursor-pointer items-center gap-2 rounded-xl bg-blue-600 px-6 py-3 font-bold text-white shadow-lg shadow-blue-200 transition-all hover:bg-blue-700 active:scale-95"
            >
                <Plus class="h-5 w-5" />
                Add Recurring
            </button>
        </div>

        <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3">
            <div
                v-for="r in props.recurringTransactions || []"
                :key="r.id"
                class="group relative rounded-2xl border border-slate-200 bg-white p-6 shadow-sm transition-all hover:border-blue-200"
            >
                <div class="mb-4 flex items-center justify-between">
                    <div class="rounded-xl bg-blue-50 p-3 text-blue-600 transition-colors group-hover:bg-blue-600 group-hover:text-white">
                        <Repeat class="h-6 w-6" />
                    </div>

                    <div class="flex items-center gap-2">
                        <button
                            @click="editRecurring(r)"
                            class="cursor-pointer rounded-lg p-2 text-slate-400 transition-colors hover:bg-blue-50 hover:text-blue-600"
                        >
                            <Pencil class="h-5 w-5" />
                        </button>

                        <button
                            @click="deleteRecurring(r.id)"
                            class="cursor-pointer rounded-lg p-2 text-slate-400 transition-colors hover:bg-red-50 hover:text-red-600"
                        >
                            <Trash2 class="h-5 w-5" />
                        </button>
                    </div>
                </div>

                <div class="space-y-4">
                    <div>
                        <h3 class="text-lg font-bold text-slate-900">
                            {{ r.description }}
                        </h3>
                        <p class="mt-1 text-sm font-semibold uppercase tracking-wider text-slate-500">
                            {{ categoryMap[r.category_id] || 'Uncategorized' }}
                        </p>
                    </div>

                    <div class="flex items-end justify-between">
                        <div>
                            <p class="text-xs font-bold uppercase tracking-widest text-slate-400">Amount</p>
                            <p class="text-xl font-bold text-slate-900">
                                {{ formatCurrency(r.amount) }}
                            </p>
                        </div>

                        <div class="text-right">
                            <p class="text-xs font-bold uppercase tracking-widest text-slate-400">Frequency</p>
                            <p class="inline-block rounded-lg bg-blue-50 px-2 py-0.5 text-sm font-semibold text-blue-600">
                                {{ r.frequency }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <div
                v-if="(props.recurringTransactions || []).length === 0"
                class="col-span-full rounded-2xl border-2 border-dashed border-slate-200 bg-slate-50 py-12 text-center"
            >
                <div class="flex flex-col items-center gap-3">
                    <Repeat class="h-12 w-12 text-slate-300" />
                    <p class="italic text-slate-400">No recurring transactions defined yet.</p>
                    <button @click="openModal" class="cursor-pointer font-bold text-blue-600 hover:underline">Add your first one</button>
                </div>
            </div>
        </div>

        <RecurringModal
            v-model:isOpen="showModal"
            :editingId="editingId"
            :formData="formData"
            :categories="safeCategories"
            :loading="loading"
            :errors="props.errors"
            @close="closeModal"
            @submit="handleSubmit"
        />
    </div>
</template>
