<script setup lang="ts">
import { ref, computed } from 'vue'
import { router } from '@inertiajs/vue3'
import { Plus, Search, Pencil, Trash2, Tag, Filter } from 'lucide-vue-next'
import ConfirmDelete from '@/components/ConfirmDelete.vue'
import CategoryModal from '@/components/CategoryModal.vue'
import AppLayout from '@/layouts/AppLayout.vue'
import type { Category } from '@/types'

defineOptions({
    layout: AppLayout,
})

interface FormData {
    name: string;
    type: string;
    is_tuition: boolean;
    is_other: boolean;
}

interface CategoryType {
    value: string;
    label: string;
}

const props = defineProps<{
    categories: Category[];
}>()

const categoryTypes: CategoryType[] = [
    { value: 'income', label: 'Income' },
    { value: 'expense', label: 'Expense' },
    { value: 'liability', label: 'Liability' },
]

const showCategoryModal = ref<boolean>(false)
const editingId = ref<number | null>(null)
const searchQuery = ref<string>('')
const typeFilter = ref<string>('all')
const isSubmitting = ref<boolean>(false)

const isShowingDeleteConfirm = ref<boolean>(false)
const getConfirmDeleteData = ref<{ id: number | null; name: string }>({
    id: null,
    name: '',
})

const selectedCategory = ref<Category | undefined>(undefined)

const formData = ref<FormData>({
    name: '',
    type: 'expense',
    is_tuition: false,
    is_other: false,
})

const filteredCategories = computed(() => {
    return props.categories
        .filter(c => {
            const matchesSearch = c.name
                .toLowerCase()
                .includes(searchQuery.value.toLowerCase())

            const matchesType =
                typeFilter.value === 'all' ||
                c.type === typeFilter.value

            return matchesSearch && matchesType
        })
        .sort((a, b) => a.name.localeCompare(b.name))
})

const openModal = (c?: Category) => {
    selectedCategory.value = c

    if (c) {
        editingId.value = c.id
        formData.value = { ...c }
    } else {
        editingId.value = null
        formData.value = {
            name: '',
            type: 'expense',
            is_tuition: false,
            is_other: false,
        }
    }

    showCategoryModal.value = true
}

const closeModal = () => {
    showCategoryModal.value = false
    editingId.value = null

    formData.value = {
        name: '',
        type: 'income',
        is_tuition: false,
        is_other: false,
    }
}

const handleSubmit = async () => {
    if (isSubmitting.value) return

    isSubmitting.value = true

    try {
        if (editingId.value) {
            router.put(route('categories.update', { id: editingId.value }), formData.value, {
                preserveScroll: true,
                onSuccess: () => closeModal(),
            })
        } else {
            router.post(route('categories.store'), formData.value, {
                preserveScroll: true,
                onSuccess: () => closeModal(),
            })
        }
    } finally {
        isSubmitting.value = false
    }
}

const confirmDeleteHandler = (id: number, name: string) => {
    isShowingDeleteConfirm.value = true
    getConfirmDeleteData.value = { id, name }
}

const deleteCategory = () => {
    router.delete(route('categories.destroy', { id: getConfirmDeleteData.value.id }), {
        onSuccess: () => {
            isShowingDeleteConfirm.value = false
        },
    })
}
</script>

<template>
    <div class="space-y-8">

        <!-- HEADER -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h2 class="text-3xl font-bold text-slate-900 tracking-tight flex items-center gap-3 max-sm:text-2xl">
                    <Tag class="w-8 h-8" />
                    Categories
                </h2>
                <p class="text-slate-500 mt-1">
                    Manage income and expense categories.
                </p>
            </div>

            <button
                @click="openModal()"
                class="flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-xl font-bold transition-all shadow-lg shadow-blue-200 active:scale-95 cursor-pointer"
            >
                <Plus class="w-5 h-5" />
                Add Category
            </button>
        </div>

        <!-- FILTER -->
        <div class="bg-white p-4 rounded-2xl shadow-sm border border-slate-200 flex flex-col md:flex-row gap-4 items-center">
            
            <!-- SEARCH -->
            <div class="relative flex-1 w-full">
                <Search class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-slate-400" />
                <input
                    v-model="searchQuery"
                    type="text"
                    placeholder="Search categories..."
                    class="w-full pl-12 pr-4 py-3 bg-slate-50 border-none rounded-xl focus:ring-2 focus:ring-blue-500 transition-all"
                />
            </div>

            <!-- FILTER -->
            <div class="flex items-center gap-2 w-full md:w-auto">
                <Filter class="w-5 h-5 text-slate-400" />
                <select
                    v-model="typeFilter"
                    class="bg-slate-50 border-none rounded-xl px-4 py-3 focus:ring-2 focus:ring-blue-500 transition-all font-medium text-slate-700 min-w-37.5 cursor-pointer max-sm:w-full"
                >
                    <option value="all">All Types</option>
                    <option value="income">Income</option>
                    <option value="expense">Expense</option>
                    <option value="liability">Liability</option>
                </select>
            </div>
        </div>

        <!-- LIST -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">

            <!-- EMPTY -->
            <div
                v-if="filteredCategories.length === 0"
                class="col-span-full py-12 text-center text-slate-400 italic"
            >
                No categories found matching your criteria.
            </div>

            <!-- CARD -->
            <div
                v-for="c in filteredCategories"
                :key="c.id"
                class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200 hover:border-blue-200 transition-all group relative overflow-hidden"
            >
                <div class="flex items-start justify-between mb-4">

                    <!-- ICON -->
                    <div
                        class="p-3 rounded-xl"
                        :class="{
                            'bg-blue-50 text-blue-600': c.type === 'income',
                            'bg-red-50 text-red-600': c.type === 'expense',
                            'bg-amber-50 text-amber-600': c.type === 'liability'
                        }"
                    >
                        <Tag class="w-6 h-6" />
                    </div>

                    <!-- ACTIONS -->
                    <div class="flex gap-1 opacity-100 md:opacity-0 md:group-hover:opacity-100 transition-opacity">
                        <button
                            @click="openModal(c)"
                            class="p-2 text-slate-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-colors cursor-pointer"
                        >
                            <Pencil class="w-4 h-4" />
                        </button>

                        <button
                            @click="confirmDeleteHandler(c.id, c.name)"
                            :disabled="(c.transactions_count ?? 0) > 0"
                            :title="(c.transactions_count ?? 0) > 0 ? 'Category is in use and cannot be deleted' : 'Delete category'"
                            class="p-2 text-slate-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors"
                            :class="(c.transactions_count ?? 0) > 0 
                                ? 'cursor-not-allowed opacity-50 pointer-events-none' 
                                : 'cursor-pointer'"
                        >
                            <Trash2 class="w-4 h-4" />
                        </button>
                    </div>
                </div>

                <!-- NAME -->
                <h3 class="text-lg font-bold text-slate-900 mb-1">
                    {{ c.name }}
                </h3>

                <!-- TYPE -->
                <p
                    class="text-xs font-bold uppercase tracking-wider"
                    :class="{
                        'text-blue-500': c.type === 'income',
                        'text-red-500': c.type === 'expense',
                        'text-amber-500': c.type === 'liability'
                    }"
                >
                    {{ c.type }}
                </p>

                <!-- BACKGROUND ICON -->
                <div class="absolute -right-4 -bottom-4 opacity-[0.03] group-hover:opacity-[0.08] transition-opacity">
                    <Tag class="w-24 h-24 rotate-12" />
                </div>
            </div>

        </div>

        <!-- MODAL -->
        <CategoryModal
            v-model:isOpen="showCategoryModal"
            :formData="formData"
            :categoryTypes="categoryTypes"
            :isSubmitting="isSubmitting"
            @submit="handleSubmit"
            @close="closeModal"
            :selectedCategory="selectedCategory"
        />

        <!-- DELETE -->
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