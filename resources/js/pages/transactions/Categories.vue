<script setup lang="ts">
import CategoryModal from '@/components/CategoryModal.vue';
import ConfirmDelete from '@/components/ConfirmDelete.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import type { Category } from '@/types';
import { Head, router, useForm } from '@inertiajs/vue3';
import { Filter, Pencil, Plus, Search, Tag, Trash2 } from 'lucide-vue-next';
import { computed, ref } from 'vue';

defineOptions({
    layout: AppLayout,
});

interface CategoryType {
    value: string;
    label: string;
}

type CategoryForm = {
    name: string;
    type: string;
    is_tuition: boolean;
    is_other: boolean;
};

const props = defineProps<{
    categories: Category[];
}>();

const categoryTypes: CategoryType[] = [
    { value: 'income', label: 'Income' },
    { value: 'expense', label: 'Expense' },
    { value: 'liability', label: 'Liability' },
];

const showCategoryModal = ref<boolean>(false);
const editingId = ref<number | null>(null);
const searchQuery = ref<string>('');
const typeFilter = ref<string>('all');

const isShowingDeleteConfirm = ref<boolean>(false);
const getConfirmDeleteData = ref<{ id: number | null; name: string }>({
    id: null,
    name: '',
});

const selectedCategory = ref<Category | undefined>(undefined);

const form = useForm<CategoryForm>({
    name: '',
    type: 'expense',
    is_tuition: false,
    is_other: false,
});

const filteredCategories = computed(() => {
    return props.categories
        .filter((c) => {
            const matchesSearch = c.name.toLowerCase().includes(searchQuery.value.toLowerCase());

            const matchesType = typeFilter.value === 'all' || c.type === typeFilter.value;

            return matchesSearch && matchesType;
        })
        .sort((a, b) => a.name.localeCompare(b.name));
});

const openModal = (c?: Category) => {
    selectedCategory.value = c;

    if (c) {
        editingId.value = c.id;
        form.clearErrors();
        form.defaults(c);
        form.reset();
    } else {
        editingId.value = null;
        form.clearErrors();
        form.reset();
    }

    showCategoryModal.value = true;
};

const closeModal = () => {
    showCategoryModal.value = false;
    editingId.value = null;
    form.reset();
};

const handleSubmit = async () => {
    if (form.processing) return;

    try {
        if (editingId.value) {
            form.put(route('admin.categories.update', { category: editingId.value }), {
                preserveScroll: true,
                onSuccess: () => closeModal(),
            });
        } else {
            form.post(route('admin.categories.store'), {
                preserveScroll: true,
                onSuccess: () => closeModal(),
            });
        }
    } catch (error) {
        console.error('An error occurred:', error);
    }
};

const confirmDeleteHandler = (id: number, name: string) => {
    isShowingDeleteConfirm.value = true;
    getConfirmDeleteData.value = { id, name };
};

const deleteCategory = () => {
    router.delete(route('admin.categories.destroy', { category: getConfirmDeleteData.value.id }), {
        preserveScroll: true,
        only: ['categories'],
        onSuccess: () => {
            isShowingDeleteConfirm.value = false;
            getConfirmDeleteData.value = { id: null, name: '' };
        },
    });
};
</script>

<template>
    <Head title="Categories" />
    <div class="space-y-8">
        <div class="flex flex-col justify-between gap-4 md:flex-row md:items-center">
            <div>
                <h2 class="flex items-center gap-3 text-3xl font-bold tracking-tight text-slate-900 max-sm:text-2xl">
                    <Tag class="h-8 w-8" />
                    Categories
                </h2>
                <p class="mt-1 text-slate-500">Manage income and expense categories.</p>
            </div>

            <button
                @click="openModal()"
                class="flex cursor-pointer items-center gap-2 rounded-xl bg-blue-600 px-6 py-3 font-bold text-white shadow-lg shadow-blue-200 transition-all hover:bg-blue-700 active:scale-95"
            >
                <Plus class="h-5 w-5" />
                Add Category
            </button>
        </div>

        <div class="flex flex-col items-center gap-4 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm md:flex-row">
            <div class="relative w-full flex-1">
                <Search class="absolute left-4 top-1/2 h-5 w-5 -translate-y-1/2 text-slate-400" />
                <input
                    v-model="searchQuery"
                    type="text"
                    placeholder="Search categories..."
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

        <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3">
            <div v-if="filteredCategories.length === 0" class="col-span-full py-12 text-center italic text-slate-400">
                No categories found matching your criteria.
            </div>

            <div
                v-for="c in filteredCategories"
                :key="c.id"
                class="group relative overflow-hidden rounded-2xl border border-slate-200 bg-white p-6 shadow-sm transition-all hover:border-blue-200"
            >
                <div class="mb-4 flex items-start justify-between">
                    <div
                        class="rounded-xl p-3"
                        :class="{
                            'bg-blue-50 text-blue-600': c.type === 'income',
                            'bg-red-50 text-red-600': c.type === 'expense',
                            'bg-amber-50 text-amber-600': c.type === 'liability',
                        }"
                    >
                        <Tag class="h-6 w-6" />
                    </div>

                    <!-- ACTIONS -->
                    <div class="flex gap-1 opacity-100 transition-opacity md:opacity-0 md:group-hover:opacity-100">
                        <button
                            @click="openModal(c)"
                            class="cursor-pointer rounded-lg p-2 text-slate-400 transition-colors hover:bg-blue-50 hover:text-blue-600"
                        >
                            <Pencil class="h-4 w-4" />
                        </button>

                        <button
                            @click="confirmDeleteHandler(c.id, c.name)"
                            :disabled="(c.transactions_count ?? 0) > 0"
                            :title="(c.transactions_count ?? 0) > 0 ? 'Category is in use and cannot be deleted' : 'Delete category'"
                            :class="(
                                c.transactions_count ?? 0
                            ) > 0 ? 'rounded-lg p-2 text-slate-400 transition-colors cursor-not-allowed opacity-50' : 'rounded-lg p-2 text-slate-400 transition-colors cursor-pointer hover:bg-red-50 hover:text-red-600'"
                        >
                            <Trash2 class="h-4 w-4" />
                        </button>
                    </div>
                </div>

                <h3 class="mb-1 text-lg font-bold text-slate-900">
                    {{ c.name }}
                </h3>

                <p
                    class="text-xs font-bold uppercase tracking-wider"
                    :class="{
                        'text-blue-500': c.type === 'income',
                        'text-red-500': c.type === 'expense',
                        'text-amber-500': c.type === 'liability',
                    }"
                >
                    {{ c.type }}
                </p>

                <div class="absolute -bottom-4 -right-4 opacity-[0.03] transition-opacity group-hover:opacity-[0.08]">
                    <Tag class="h-24 w-24 rotate-12" />
                </div>
            </div>
        </div>

        <CategoryModal
            v-model:isOpen="showCategoryModal"
            :formData="form"
            :categoryTypes="categoryTypes"
            :loading="form.processing"
            @submit="handleSubmit"
            @close="closeModal"
            :selectedCategory="selectedCategory"
        />

        <ConfirmDelete
            v-if="isShowingDeleteConfirm"
            :isOpen="true"
            title="Delete Category"
            :message="getConfirmDeleteData.name"
            @confirm="deleteCategory"
            @close="isShowingDeleteConfirm = false"
        />
    </div>
</template>
