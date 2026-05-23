<script setup lang="ts">
import BasePagination from '@/components/base/BasePagination.vue';
import ConfirmDelete from '@/components/ConfirmDelete.vue';
import TransactionModal from '@/components/TransactionModal.vue';
import TransactionTrashModal from '@/components/TransactionTrashModal.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import type { Auth, Category, Transaction } from '@/types';
import { useForm } from '@inertiajs/vue3';
import { format, parseISO } from 'date-fns';
import { Filter, Pencil, Plus, ReceiptText, Search, Trash2 } from 'lucide-vue-next';
import { computed, ref } from 'vue';

defineOptions({
    layout: AppLayout,
});

interface PaginatedData<T> {
    data: T[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    prev_page_url: string | null;
    next_page_url: string | null;
    links: Array<{
        url: string | null;
        label: string;
        active: boolean;
    }>;
}

const props = defineProps<{
    transactions: PaginatedData<Transaction>;
    categories: Category[];
    trashed: PaginatedData<Transaction>;
    trashedCount: number;
    auth: Auth;
    currentDate: string;
}>();

const searchQuery = ref('');
const typeFilter = ref('all');
const showModal = ref(false);
const showTrashModal = ref(false);
const editingId = ref<number | null>(null);

const form = useForm({
    transaction_date: props.currentDate,
    type: 'income',
    category_id: '',
    description: '',
    amount: 0,
    remarks: '',
});

const confirmDelete = ref<{
    show: boolean;
    id: number | null;
    name: string;
}>({
    show: false,
    id: null,
    name: '',
});

const filteredTransactions = computed(() => {
    return props.transactions.data.filter((t: Transaction) => {
        const search = searchQuery.value.toLowerCase();

        const fields = [t.description, t.category?.name, t.type, t.amount, new Date(t.transaction_date).toLocaleDateString()];

        const matchesSearch = fields.some((field) =>
            String(field ?? '')
                .toLowerCase()
                .includes(search),
        );

        const matchesType = typeFilter.value === 'all' || t.type === typeFilter.value;

        const excludeSpecial = t.category?.is_tuition !== true && t.category?.is_other !== true;

        return matchesSearch && matchesType && excludeSpecial;
    });
});

const filteredCategories = computed(() => props.categories.filter((c: Category) => c.type === form.type));

const categoryTypes = ['income', 'expense', 'liability'];

const openModal = (t: any = null) => {
    if (t) {
        editingId.value = t.id;
        form.clearErrors();
        form.defaults(t);
        form.reset();
    } else {
        editingId.value = null;
        form.clearErrors();
        form.reset();
        form.transaction_date = props.currentDate;
    }

    showModal.value = true;
};

const closeModal = () => {
    showModal.value = false;
    editingId.value = null;
    form.reset();
};

const handleSubmit = () => {
    if (editingId.value) {
        form.put(route('admin.transactions.update', { transaction: editingId.value }), {
            preserveScroll: true,
            onSuccess: () => closeModal(),
        });
    } else {
        form.post(route('admin.transactions.store'), {
            preserveScroll: true,
            onSuccess: () => closeModal(),
        });
    }
};

const confirmDeleteHandler = (id: number, name: string) => {
    confirmDelete.value = {
        show: true,
        id,
        name,
    };
};

const deleteTransaction = () => {
    if (!confirmDelete.value.id) return;

    form.delete(
        route('admin.transactions.destroy', {
            transaction: confirmDelete.value.id,
        }),
        {
            preserveScroll: true,
            only: ['transactions', 'trashed', 'trashedCount'],
            onFinish: () => {
                confirmDelete.value.show = false;
            },
        },
    );
};

const restoreTransaction = (id: number) => {
    form.patch(route('admin.transactions.restore', { id: id }), {
        preserveScroll: true,
        only: ['transactions', 'trashed', 'trashedCount'],
    });
};

const deletePermanent = (id: number) => {
    form.delete(route('admin.transactions.force-delete', { id: id }), {
        preserveScroll: true,
        only: ['transactions', 'trashed', 'trashedCount'],
    });
};

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
                    <ReceiptText class="h-8 w-8" />
                    Transactions
                </h2>
                <p class="mt-1 text-slate-500">Manage all your financial records in one place.</p>
            </div>

            <div v-if="auth.user?.user_type === 'admin'" class="flex items-center gap-3 max-sm:justify-end">
                <button
                    @click="showTrashModal = true"
                    class="group relative cursor-pointer rounded-xl border border-slate-200 bg-white p-3 text-slate-400 shadow-sm transition-all hover:border-red-200 hover:text-red-500"
                >
                    <Trash2 class="h-5 w-5 transition-transform group-hover:scale-110" />
                    <span
                        v-show="props.trashedCount > 0"
                        class="absolute -right-2 -top-2 inline-flex items-center justify-center rounded-full bg-red-600 px-2 py-1 text-xs font-bold leading-none text-red-100"
                        >{{ props.trashedCount }}</span
                    >
                </button>

                <button
                    @click="openModal()"
                    class="flex cursor-pointer items-center gap-2 rounded-xl bg-blue-600 px-6 py-3 font-bold text-white shadow-lg shadow-blue-200 transition-all hover:bg-blue-700 active:scale-95"
                >
                    <Plus class="h-5 w-5" />
                    Add Transaction
                </button>
            </div>
        </div>

        <div class="flex flex-col items-center gap-4 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm md:flex-row">
            <div class="relative w-full flex-1">
                <Search class="absolute left-4 top-1/2 h-5 w-5 -translate-y-1/2 text-slate-400" />
                <input
                    v-model="searchQuery"
                    type="text"
                    placeholder="Search transactions..."
                    class="w-full rounded-xl border-none bg-slate-50 py-3 pl-12 pr-4 transition-all focus:ring-2 focus:ring-blue-500"
                />
            </div>

            <div class="flex w-full items-center gap-2 md:w-auto">
                <Filter class="h-5 w-5 text-slate-400" />
                <select
                    v-model="typeFilter"
                    class="min-w-37.5 cursor-pointer rounded-xl border-none bg-slate-50 px-4 py-3 font-medium text-slate-700 transition-all focus:ring-2 focus:ring-blue-500 max-sm:w-full"
                >
                    <option value="all">All Types</option>
                    <option value="income">Income</option>
                    <option value="expense">Expense</option>
                    <option value="liability">Liability</option>
                </select>
            </div>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead class="bg-slate-50 text-xs font-bold uppercase text-slate-500">
                        <tr>
                            <th class="px-6 py-4">Date</th>
                            <th class="px-6 py-4">Description</th>
                            <th class="px-6 py-4">Category</th>
                            <th class="px-6 py-4">Amount</th>
                            <th v-if="auth.user?.user_type === 'admin'" class="px-6 py-4 text-center">Actions</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-slate-100">
                        <tr v-if="filteredTransactions.length === 0">
                            <td colspan="5" class="px-6 py-12 text-center italic text-slate-400">No transactions found.</td>
                        </tr>

                        <tr v-else v-for="t in filteredTransactions" :key="t.id" class="group transition-colors hover:bg-slate-50">
                            <td class="px-6 py-4 text-sm text-slate-600">
                                <span class="font-medium text-slate-800">
                                    {{ format(parseISO(t.transaction_date), 'MMM dd, yyyy') }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <p class="text-sm font-medium text-slate-900">{{ t.description }}</p>
                                <p v-if="t.remarks" class="mt-0.5 text-xs text-slate-400">{{ t.remarks }}</p>
                            </td>
                            <td class="px-6 py-4">
                                <span
                                    class="rounded-full px-2.5 py-1 text-xs font-semibold"
                                    :class="{
                                        'bg-blue-50 text-blue-600': t.type === 'income',
                                        'bg-red-50 text-red-600': t.type === 'expense',
                                        'bg-amber-50 text-amber-600': t.type === 'liability',
                                    }"
                                >
                                    {{ props.categories.find((c) => c.id === Number(t.category_id))?.name || 'Uncategorized' }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="text-sm font-bold" :class="t.type === 'income' ? 'text-emerald-600' : 'text-slate-900'">
                                    {{ t.type === 'income' ? '+' : '-' }}{{ formatCurrency(t.amount) }}
                                </span>
                            </td>
                            <td v-if="auth.user?.user_type === 'admin'" class="px-6 py-4">
                                <div class="flex items-center justify-center gap-2 transition-opacity md:opacity-0 md:group-hover:opacity-100">
                                    <button
                                        @click="openModal(t)"
                                        class="cursor-pointer rounded-lg p-2 text-slate-400 transition-colors hover:bg-blue-50 hover:text-blue-600"
                                    >
                                        <Pencil class="h-4 w-4" />
                                    </button>

                                    <button
                                        @click="confirmDeleteHandler(t.id, t.description)"
                                        class="cursor-pointer rounded-lg p-2 text-slate-400 transition-colors hover:bg-red-50 hover:text-red-600"
                                    >
                                        <Trash2 class="h-4 w-4" />
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
                <BasePagination v-if="transactions.last_page > 1" :pagination="transactions" />
            </div>
        </div>

        <TransactionModal
            v-model:isOpen="showModal"
            :editingId="editingId"
            :formData="form"
            :categoryTypes="categoryTypes"
            :filteredCategories="filteredCategories"
            :loading="form.processing"
            @submit="handleSubmit"
            @close="closeModal"
        />

        <ConfirmDelete
            v-if="confirmDelete.show"
            :isOpen="true"
            title="Delete Transaction"
            :message="confirmDelete.name"
            :loading="form.processing"
            actionName="transaction"
            @confirm="deleteTransaction"
            @close="confirmDelete.show = false"
        />

        <TransactionTrashModal
            :show="showTrashModal"
            :trashed="trashed"
            :trashedCount="trashedCount"
            :formatCurrency="formatCurrency"
            :restore="restoreTransaction"
            :forceDelete="deletePermanent"
            @close="showTrashModal = false"
        />
    </div>
</template>
