<script setup lang="ts">
import { computed, ref } from 'vue'
import { router, usePage, useForm } from '@inertiajs/vue3'
import { format, parseISO } from 'date-fns'
import {
    Heart,
    ArrowUpRight,
    ArrowDownRight,
    Pencil,
    Trash2
} from 'lucide-vue-next'

import ConfirmDateChangeModal from '@/components/ConfirmDateChangeModal.vue'
import ConfirmDelete from '@/components/ConfirmDelete.vue'
import AppLayout from '@/layouts/AppLayout.vue'
import CharityModal from '@/components/CharityModal.vue'
import type { SharedData } from '@/types'

defineOptions({
    layout: AppLayout,
})

interface Category {
    id: number
    name: string
    type: string
}

interface Transaction {
    id: number
    description: string
    remarks?: string
    amount: number
    type: 'income' | 'expense' | 'liability'
    transaction_date: string
    category_id: number
}

interface CharityStats {
    expense: number
    transactions: Transaction[]
}

const props = defineProps<{
    charityStats: CharityStats
    categories: Category[]
    year: number
}>()

const page = usePage<SharedData>()

const isAdmin = page.props.auth?.user?.user_type === 'admin'

const charityRoute = isAdmin
    ? route('admin.charity')
    : route('charity')

const currentYear = ref<number>(props.year)
const pendingYear = ref<number | null>(null)
const showYearModal = ref(false)

const showModal = ref(false)
const editingId = ref<number | null>(null)
const deleting = ref(false)

const confirmDelete = ref<{
    show: boolean
    id: number | null
    name: string
}>({
    show: false,
    id: null,
    name: '',
})

const form = useForm<{
    transaction_date: string
    type: string
    category_id: string
    description: string
    amount: number
    remarks: string
    redirect: string
}>({
    transaction_date: format(new Date(), 'yyyy-MM-dd'),
    type: 'expense',
    category_id: '',
    description: '',
    amount: 0,
    remarks: '',
    redirect: 'charity',
})

const categoryMap = computed<Record<number, string>>(() => {
    const map: Record<number, string> = {}

    props.categories.forEach((c) => {
        map[c.id] = c.name
    })

    return map
})

const filteredCategories = computed(() =>
    props.categories.filter((c) => c.type === form.type)
)

const categoryTypes = ['income', 'expense', 'liability']

const handleYearChange = (newYear: number) => {
    if (newYear === currentYear.value) return

    pendingYear.value = newYear
    showYearModal.value = true
}

const prevYear = () => handleYearChange(currentYear.value - 1)

const nextYear = () => handleYearChange(currentYear.value + 1)

const confirmYearChange = () => {
    if (pendingYear.value !== null) {
        router.get(
            charityRoute,
            { year: pendingYear.value },
            {
                preserveState: true,
                preserveScroll: true,
            }
        )

        currentYear.value = pendingYear.value
    }

    showYearModal.value = false
    pendingYear.value = null
}

const cancelYearChange = () => {
    showYearModal.value = false
    pendingYear.value = null
}

const label = computed(() =>
    pendingYear.value
        ? String(pendingYear.value)
        : String(currentYear.value)
)

const openModal = (t: Transaction | null = null) => {
    if (t) {
        editingId.value = t.id

        form.defaults({
            transaction_date: format(parseISO(t.transaction_date), 'yyyy-MM-dd'),
            type: t.type,
            category_id: String(t.category_id),
            description: t.description,
            amount: t.amount,
            remarks: t.remarks || '',
            redirect: 'admin.charity',
        })
    } else {
        editingId.value = null

        form.defaults({
            transaction_date: format(new Date(), 'yyyy-MM-dd'),
            type: 'expense',
            category_id: '',
            description: '',
            amount: 0,
            remarks: '',
            redirect: 'admin.charity',
        })
    }

    form.reset()
    showModal.value = true
}

const closeModal = () => {
    showModal.value = false
}

const handleSubmit = () => {
    if (editingId.value) {
        form.put(
            route('transactions.update', {
                id: editingId.value,
            }),
            {
                preserveScroll: true,
                onSuccess: () => closeModal(),
            }
        )
    } else {
        form.post(route('transactions.store'), {
            preserveScroll: true,
            onSuccess: () => closeModal(),
        })
    }
}

const confirmDeleteHandler = (id: number, name: string) => {
    confirmDelete.value = {
        show: true,
        id,
        name,
    }
}

const deleteTransaction = () => {
    if (!confirmDelete.value.id) return

    form.delete(route('transactions.destroy', {
        id: confirmDelete.value.id,
        redirect: 'admin.charity',
    }), {
        preserveScroll: true,
        onFinish: () => {
            confirmDelete.value.show = false
        },
    })
}

const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('en-PH', {
        style: 'currency',
        currency: 'PHP',
    }).format(amount ?? 0)
}
</script>

<template>
    <div class="space-y-8">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h2 class="text-3xl font-bold text-slate-900 tracking-tight flex items-center gap-3 max-sm:text-2xl">
                    <Heart class="w-8 h-8 text-rose-500 fill-rose-500" />
                    Charity
                </h2>

                <p class="text-slate-500 mt-1">
                    Monitor all transactions related to tuition and educational support.
                </p>
            </div>

            <div class="flex items-center gap-3">
                <div
                    class="flex items-center max-sm:justify-around gap-3 bg-white p-2 rounded-xl shadow-sm border border-slate-200"
                >
                    <button
                        @click="prevYear"
                        class="p-2 hover:bg-slate-50 rounded-lg transition-colors cursor-pointer"
                    >
                        <ArrowDownRight class="w-5 h-5 rotate-90 text-slate-400" />
                    </button>

                    <span class="text-sm font-semibold text-slate-700 min-w-35 text-center">
                        {{ currentYear }}
                    </span>

                    <button
                        @click="nextYear"
                        class="p-2 hover:bg-slate-50 rounded-lg transition-colors cursor-pointer"
                        :class="{ 'cursor-not-allowed opacity-50': currentYear >= new Date().getFullYear() }"
                        :disabled="currentYear >= new Date().getFullYear()"
                    >
                        <ArrowUpRight class="w-5 h-5 -rotate-90 text-slate-400" />
                    </button>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="p-6 border-b border-slate-100 flex justify-between items-center">
                <h3 class="text-lg font-bold text-slate-900">
                    Tuition Related Transactions
                </h3>

                <p class="text-red-500">
                    Total:
                    <strong>{{ formatCurrency(props.charityStats.expense || 0) }}</strong>
                </p>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead class="bg-slate-50 text-slate-500 text-xs uppercase font-bold">
                        <tr>
                            <th class="px-6 py-4">Date</th>
                            <th class="px-6 py-4">Description</th>
                            <th class="px-6 py-4">Category</th>
                            <th class="px-6 py-4 text-right">Amount</th>

                            <th
                                v-if="isAdmin"
                                class="px-6 py-4 text-center"
                            >
                                Actions
                            </th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-slate-100">
                        <tr v-if="(props.charityStats?.transactions ?? []).length === 0">
                            <td
                                :colspan="isAdmin ? 5 : 4"
                                class="px-6 py-12 text-center text-slate-400 italic"
                            >
                                No tuition-related transactions found for this year.
                            </td>
                        </tr>

                        <tr
                            v-else
                            v-for="t in props.charityStats.transactions"
                            :key="t.id"
                            class="hover:bg-slate-50 transition-colors group"
                        >
                            <td class="px-6 py-4 text-sm text-slate-600">
                                {{ format(new Date(t.transaction_date), 'MMM dd, yyyy') }}
                            </td>

                            <td class="px-6 py-4">
                                <p class="text-sm font-medium text-slate-900">
                                    {{ t.description }}
                                </p>

                                <p
                                    v-if="t.remarks"
                                    class="text-xs text-slate-400 mt-0.5"
                                >
                                    {{ t.remarks }}
                                </p>
                            </td>

                            <td class="px-6 py-4">
                                <span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-slate-100 text-slate-600">
                                    {{ categoryMap[t.category_id] || 'Uncategorized' }}
                                </span>
                            </td>

                            <td class="px-6 py-4 text-right">
                                <span
                                    class="text-sm font-bold"
                                    :class="t.type === 'income'
                                        ? 'text-emerald-600'
                                        : 'text-slate-900'"
                                >
                                    {{ t.type === 'income' ? '+' : '-' }}
                                    {{ formatCurrency(t.amount) }}
                                </span>
                            </td>

                            <td
                                v-if="isAdmin"
                                class="px-6 py-4"
                            >
                                <div class="flex items-center justify-center gap-2 md:opacity-0 md:group-hover:opacity-100 transition-opacity">
                                    <button
                                        @click="openModal(t)"
                                        class="p-2 text-slate-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-colors cursor-pointer"
                                    >
                                        <Pencil class="w-4 h-4" />
                                    </button>

                                    <button
                                        @click="confirmDeleteHandler(t.id, t.description)"
                                        class="p-2 text-slate-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors cursor-pointer"
                                    >
                                        <Trash2 class="w-4 h-4" />
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <CharityModal
            v-model:isOpen="showModal"
            :editingId="editingId"
            :form="form"
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

        <ConfirmDateChangeModal
            :show="showYearModal"
            :label="label"
            @confirm="confirmYearChange"
            @cancel="cancelYearChange"
        />
    </div>
</template>