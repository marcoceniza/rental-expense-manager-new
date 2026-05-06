<script setup>
import { ref, computed } from 'vue'
import { router } from '@inertiajs/vue3'
import { Plus, Trash2, Repeat, Pencil } from 'lucide-vue-next'
import AppLayout from '@/layouts/AppLayout.vue'

defineOptions({
    layout: AppLayout,
})

/**
 * PROPS FROM LARAVEL
 */
const props = defineProps({
    recurringTransactions: Array,
    categories: Array,
})

/**
 * STATE
 */
const showModal = ref(false)
const isEditMode = ref(false)
const editingId = ref(null)

/**
 * FORM
 */
const formData = ref({
    category_id: '',
    amount: 0,
    frequency: 'monthly',
    start_date: new Date().toISOString().split('T')[0],
    description: ''
})

/**
 * RESET
 */
const resetForm = () => {
    formData.value = {
        category_id: '',
        amount: 0,
        frequency: 'monthly',
        start_date: new Date().toISOString().split('T')[0],
        description: ''
    }
    isEditMode.value = false
    editingId.value = null
}

/**
 * MODAL
 */
const openModal = () => {
    resetForm()
    showModal.value = true
}

const editRecurring = (r) => {
    isEditMode.value = true
    editingId.value = r.id

    formData.value = {
        category_id: r.category_id,
        amount: r.amount,
        frequency: r.frequency,
        start_date: r.start_date,
        description: r.description
    }

    showModal.value = true
}

const closeModal = () => {
    showModal.value = false
    resetForm()
}

/**
 * SUBMIT (CREATE / UPDATE)
 */
const handleSubmit = () => {
    if (isEditMode.value) {
        router.put(`/recurrings/${editingId.value}`, formData.value, {
            preserveScroll: true,
            onSuccess: () => closeModal()
        })
    } else {
        router.post('/recurrings', formData.value, {
            preserveScroll: true,
            onSuccess: () => closeModal()
        })
    }
}

/**
 * DELETE
 */
const deleteRecurring = (id) => {
    if (confirm('Are you sure you want to delete this recurring transaction?')) {
        router.delete(`/recurrings/${id}`, {
            preserveScroll: true,
        })
    }
}

/**
 * CATEGORY MAP (FASTER THAN find())
 */
const categoryMap = computed(() => {
    const map = {}
    props.categories.forEach(c => {
        map[c.id] = c.name
    })
    return map
})

/**
 * FORMAT
 */
const formatCurrency = (amount) => {
    return new Intl.NumberFormat('en-PH', {
        style: 'currency',
        currency: 'PHP',
    }).format(amount)
}
</script>

<template>
    <div class="space-y-8">

        <!-- HEADER -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h2 class="text-3xl font-bold text-slate-900 tracking-tight flex items-center gap-3 max-sm:text-2xl">
                    <Repeat class="w-8 h-8" />
                    Recurring Transactions
                </h2>
                <p class="text-slate-500 mt-1">
                    Automate your monthly income and expenses.
                </p>
            </div>

            <button
                @click="openModal"
                class="flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-xl font-bold transition-all shadow-lg shadow-blue-200 active:scale-95 cursor-pointer"
            >
                <Plus class="w-5 h-5" />
                Add Recurring
            </button>
        </div>

        <!-- GRID -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">

            <!-- CARD -->
            <div
                v-for="r in (props.recurringTransactions || [])"
                :key="r.id"
                class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200 hover:border-blue-200 transition-all group relative"
            >
                <div class="flex items-center justify-between mb-4">

                    <!-- ICON -->
                    <div class="p-3 bg-blue-50 text-blue-600 rounded-xl group-hover:bg-blue-600 group-hover:text-white transition-colors">
                        <Repeat class="w-6 h-6" />
                    </div>

                    <!-- ACTIONS -->
                    <div class="flex items-center gap-2">
                        <button
                            @click="editRecurring(r)"
                            class="p-2 text-slate-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-colors cursor-pointer"
                        >
                            <Pencil class="w-5 h-5" />
                        </button>

                        <button
                            @click="deleteRecurring(r.id)"
                            class="p-2 text-slate-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors cursor-pointer"
                        >
                            <Trash2 class="w-5 h-5" />
                        </button>
                    </div>
                </div>

                <!-- CONTENT -->
                <div class="space-y-4">
                    <div>
                        <h3 class="text-lg font-bold text-slate-900">
                            {{ r.description }}
                        </h3>
                        <p class="text-sm font-semibold text-slate-500 uppercase tracking-wider mt-1">
                            {{ categoryMap[r.category_id] || 'Uncategorized' }}
                        </p>
                    </div>

                    <div class="flex items-end justify-between">
                        <div>
                            <p class="text-xs text-slate-400 uppercase font-bold tracking-widest">
                                Amount
                            </p>
                            <p class="text-xl font-bold text-slate-900">
                                {{ formatCurrency(r.amount) }}
                            </p>
                        </div>

                        <div class="text-right">
                            <p class="text-xs text-slate-400 uppercase font-bold tracking-widest">
                                Frequency
                            </p>
                            <p class="text-sm font-semibold text-blue-600 bg-blue-50 px-2 py-0.5 rounded-lg inline-block">
                                {{ r.frequency }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- EMPTY -->
            <div
                v-if="(props.recurringTransactions || []).length === 0"
                class="col-span-full py-12 text-center bg-slate-50 border-2 border-dashed border-slate-200 rounded-2xl"
            >
                <div class="flex flex-col items-center gap-3">
                    <Repeat class="w-12 h-12 text-slate-300" />
                    <p class="text-slate-400 italic">
                        No recurring transactions defined yet.
                    </p>
                    <button
                        @click="openModal"
                        class="text-blue-600 font-bold hover:underline cursor-pointer"
                    >
                        Add your first one
                    </button>
                </div>
            </div>

        </div>

        <!-- MODAL -->
        <div
            v-if="showModal"
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-sm"
        >
            <div class="bg-white w-full max-w-lg rounded-2xl shadow-2xl overflow-hidden animate-in fade-in zoom-in duration-200">

                <!-- HEADER -->
                <div class="p-6 border-b border-slate-100 flex items-center justify-between bg-slate-50">
                    <h3 class="text-xl font-bold text-slate-900">
                        {{ isEditMode ? 'Edit Recurring Entry' : 'New Recurring Entry' }}
                    </h3>

                    <button
                        @click="closeModal"
                        class="p-2 hover:bg-white rounded-lg transition-colors cursor-pointer"
                    >
                        <X class="w-5 h-5 text-slate-400" />
                    </button>
                </div>

                <!-- FORM -->
                <form @submit.prevent="handleSubmit" class="p-6 space-y-4">

                    <!-- CATEGORY -->
                    <div class="space-y-1.5">
                        <label class="text-xs font-bold text-slate-500 uppercase">Category</label>
                        <select
                            v-model="formData.category_id"
                            required
                            class="w-full px-4 py-3 bg-slate-50 border-none rounded-xl focus:ring-2 focus:ring-blue-500 transition-all font-medium"
                        >
                            <option value="" disabled>Select a category</option>

                            <option
                                v-for="c in props.categories"
                                :key="c.id"
                                :value="c.id"
                            >
                                {{ c.name }} ({{ c.type }})
                            </option>
                        </select>
                    </div>

                    <!-- DESCRIPTION -->
                    <div class="space-y-1.5">
                        <label class="text-xs font-bold text-slate-500 uppercase">Description</label>
                        <input
                            v-model="formData.description"
                            type="text"
                            required
                            placeholder="e.g., Monthly Rent"
                            class="w-full px-4 py-3 bg-slate-50 border-none rounded-xl focus:ring-2 focus:ring-blue-500 transition-all font-medium"
                        />
                    </div>

                    <!-- AMOUNT + FREQUENCY -->
                    <div class="grid grid-cols-2 gap-4">

                        <div class="space-y-1.5">
                            <label class="text-xs font-bold text-slate-500 uppercase">Amount (PHP)</label>
                            <input
                                v-model.number="formData.amount"
                                type="number"
                                required
                                class="w-full px-4 py-3 bg-slate-50 border-none rounded-xl focus:ring-2 focus:ring-blue-500 transition-all font-bold text-lg"
                            />
                        </div>

                        <div class="space-y-1.5">
                            <label class="text-xs font-bold text-slate-500 uppercase">Frequency</label>
                            <select
                                v-model="formData.frequency"
                                disabled
                                class="w-full px-4 py-3 bg-slate-100 border-none rounded-xl font-medium text-slate-500"
                            >
                                <option value="monthly">Monthly</option>
                            </select>
                        </div>

                    </div>

                    <!-- DATE -->
                    <div class="space-y-1.5">
                        <label class="text-xs font-bold text-slate-500 uppercase">Start Date</label>
                        <input
                            v-model="formData.start_date"
                            type="date"
                            required
                            class="w-full px-4 py-3 bg-slate-50 border-none rounded-xl focus:ring-2 focus:ring-blue-500 transition-all font-medium"
                        />
                    </div>

                    <!-- ACTIONS -->
                    <div class="pt-4 flex gap-3">
                        <button
                            type="button"
                            @click="closeModal"
                            class="flex-1 px-6 py-3 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl transition-colors cursor-pointer"
                        >
                            Cancel
                        </button>

                        <button
                            type="submit"
                            class="flex-1 px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-xl transition-all shadow-lg shadow-blue-200 active:scale-95 flex items-center justify-center gap-2 cursor-pointer"
                        >
                            <Check class="w-5 h-5" />
                            {{ isEditMode ? 'Update' : 'Save Recurring' }}
                        </button>
                    </div>

                </form>
            </div>
        </div>

    </div>
</template>