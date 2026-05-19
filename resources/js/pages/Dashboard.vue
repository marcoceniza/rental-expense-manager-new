<script setup lang="ts">
import { ref, computed } from 'vue'
import { router, Link } from '@inertiajs/vue3'
import { route } from 'ziggy-js'
import { subMonths, addMonths, format, parseISO } from 'date-fns'
import { TrendingUp, TrendingDown, Wallet, ArrowUpRight, ArrowDownRight, CreditCard, LayoutDashboard } from 'lucide-vue-next'
import ConfirmDateChangeModal from '@/components/ConfirmDateChangeModal.vue'
import AppLayout from '@/layouts/AppLayout.vue'

defineOptions({
    layout: AppLayout,
})

interface Category {
    id: number
    name: string
}

interface Transaction {
    id: number
    amount: number
    type: string
    transaction_date: string
    category_id: number
    description: string
}

const props = defineProps<{
    transactions: Transaction[]
    monthlyReport: {
        income: number
        expense: number
        liability: number
        net: number
    },
    categories: Category[],
    month: string
}>()

const currentMonth = ref(new Date(`${props.month}-01`))
const showDateModal = ref(false)
const pendingDate = ref<Date | null>(null)

const applyChange = (date: Date) => {
    const formatted = date.toISOString().slice(0, 7)

    router.get(
        route('admin.dashboard'),
        { month: formatted },
        {
            preserveState: true,
            preserveScroll: true,
        }
    )

    currentMonth.value = date
}

const handleChange = (newDate: Date) => {
    const currentYear = currentMonth.value.getFullYear()
    const newYear = newDate.getFullYear()

    if (currentYear !== newYear) {
        pendingDate.value = newDate
        showDateModal.value = true
        return
    }

    applyChange(newDate)
}

const confirmChange = () => {
    if (pendingDate.value) {
        applyChange(pendingDate.value)
    }

    showDateModal.value = false
    pendingDate.value = null
}

const cancelChange = () => {
    showDateModal.value = false
    pendingDate.value = null
}

const prevMonth = () => {
    handleChange(subMonths(currentMonth.value, 1))
}

const nextMonth = () => {
    handleChange(addMonths(currentMonth.value, 1))
}

const label = computed(() => {
    const date = pendingDate.value || currentMonth.value

    return date.toLocaleString('default', {
        month: 'long',
        year: 'numeric',
    })
})

const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('en-PH', {
        style: 'currency',
        currency: 'PHP',
    }).format(amount)
}

const categoryMap = computed(() => {
    const map: Record<number, string> = {}

    props.categories.forEach((category) => {
        map[category.id] = category.name
    })

    return map
})

const monthlyDonutSeries = computed(() => [
    Number(props.monthlyReport?.income) || 0,
    Number(props.monthlyReport?.expense) || 0,
    Number(props.monthlyReport?.liability) || 0,
])

const monthlyDonutOptions = {
    labels: ['Income', 'Expense', 'Liability'],
    colors: ['#22c55e', '#ef4444', '#f59e0b'],
    legend: {
        position: 'bottom',
    },
}
</script>

<template>
    <div class="space-y-8">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h2 class="text-3xl font-bold text-slate-900 tracking-tight flex items-center gap-3 max-sm:text-2xl">
                    <LayoutDashboard class="w-8 h-8" />
                    Financial Overview
                </h2>
                <p class="text-slate-500 mt-1">
                    Track your income and expenses efficiently.
                </p>
            </div>

            <div class="flex items-center max-sm:justify-around gap-3 bg-white p-2 rounded-xl shadow-sm border border-slate-200">
                <button @click="prevMonth" class="p-2 hover:bg-slate-50 rounded-lg transition-colors cursor-pointer">
                    <ArrowDownRight class="w-5 h-5 rotate-90 text-slate-400" />
                </button>

                <span class="text-sm font-semibold text-slate-700 min-w-35 text-center">
                    {{ label }}
                </span>

                <button @click="nextMonth" class="p-2 hover:bg-slate-50 rounded-lg transition-colors cursor-pointer">
                    <ArrowUpRight class="w-5 h-5 -rotate-90 text-slate-400" />
                </button>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200 hover:border-blue-200 transition-colors group">
                <div class="flex items-center justify-between mb-4">
                    <div class="p-3 bg-blue-50 text-blue-600 rounded-xl group-hover:bg-blue-600 group-hover:text-white transition-colors">
                        <TrendingUp class="w-6 h-6" />
                    </div>
                </div>
                <p class="text-sm font-medium text-slate-500 uppercase tracking-wider mb-2">
                    Total Income
                </p>
                <h3 class="text-2xl font-bold text-slate-900 mt-1">
                    {{ formatCurrency(props.monthlyReport?.income || 0) }}
                </h3>
            </div>

            <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200 hover:border-red-200 transition-colors group">
                <div class="flex items-center justify-between mb-4">
                    <div class="p-3 bg-red-50 text-red-600 rounded-xl group-hover:bg-red-600 group-hover:text-white transition-colors">
                        <TrendingDown class="w-6 h-6" />
                    </div>
                </div>
                <p class="text-sm font-medium text-slate-500 uppercase tracking-wider mb-2">
                    Total Expenses
                </p>
                <h3 class="text-2xl font-bold text-slate-900 mt-1">
                    {{ formatCurrency(props.monthlyReport?.expense || 0) }}
                </h3>
            </div>

            <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200 hover:border-amber-200 transition-colors group">
                <div class="flex items-center justify-between mb-4">
                    <div class="p-3 bg-amber-50 text-amber-600 rounded-xl group-hover:bg-amber-600 group-hover:text-white transition-colors">
                        <CreditCard class="w-6 h-6" />
                    </div>
                </div>
                <p class="text-sm font-medium text-slate-500 uppercase tracking-wider mb-2">
                    Liabilities
                </p>
                <h3 class="text-2xl font-bold text-slate-900 mt-1">
                    {{ formatCurrency(props.monthlyReport?.liability || 0) }}
                </h3>
            </div>

            <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200 hover:border-emerald-200 transition-colors group">
                <div class="flex items-center justify-between mb-4">
                    <div class="p-3 bg-emerald-50 text-emerald-600 rounded-xl group-hover:bg-emerald-600 group-hover:text-white transition-colors">
                        <Wallet class="w-6 h-6" />
                    </div>
                </div>
                <p class="text-sm font-medium text-slate-500 uppercase tracking-wider mb-2">
                    Net Balance
                </p>
                <h3
                    class="text-2xl font-bold text-slate-900 mt-1"
                    :class="props.monthlyReport?.net >= 0 ? 'text-emerald-600' : 'text-red-600'"
                >
                    {{ formatCurrency(props.monthlyReport?.net || 0) }}
                </h3>
            </div>

        </div>

        <div class="flex flex-col lg:flex-row gap-6">
            <div class="lg:w-1/4 bg-white p-4 rounded-2xl shadow-sm border border-slate-200 flex flex-col">
                <h3 class="xl:text-xl lg:mt-2.5 lg:text-center text-lg font-bold text-slate-900 mb-4 w-full">
                    Monthly Overview
                </h3>

                <apexchart
                    type="donut"
                    height="280"
                    :options="monthlyDonutOptions"
                    :series="monthlyDonutSeries"
                />
            </div>

            <div class="lg:w-4/5 bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
                <div class="p-6 border-b border-slate-100 flex items-center justify-between">
                    <h3 class="text-lg font-bold text-slate-900">Recent Activity</h3>
                    <Link
                        :href="route('admin.transactions.index')"
                        class="text-sm font-medium text-blue-600 hover:text-blue-500 transition-colors cursor-pointer"
                    >
                        View All
                    </Link>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead class="bg-slate-50 text-slate-500 text-xs uppercase font-bold">
                            <tr>
                                <th class="px-6 py-4">Date</th>
                                <th class="px-6 py-4">Description</th>
                                <th class="px-6 py-4">Category</th>
                                <th class="px-6 py-4 text-right">Amount</th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-slate-100">
                            <tr v-if="(props.transactions || []).length === 0">
                                <td colspan="4" class="px-6 py-12 text-center text-slate-400 italic">
                                    No transactions recorded yet.
                                </td>
                            </tr>

                            <tr
                                v-for="t in (props.transactions || []).slice(0, 5)"
                                :key="t.id"
                                class="hover:bg-slate-50 transition-colors"
                            >
                                <td class="px-6 py-4 text-sm text-slate-600">
                                    {{ format(parseISO(t.transaction_date), 'MMM dd, yyyy') }}
                                </td>

                                <td class="px-6 py-4">
                                    <p class="text-sm font-medium text-slate-900">{{ t.description }}</p>
                                </td>

                                <td class="px-6 py-4">
                                    <span class="px-2.5 py-1 text-slate-900 rounded-full text-xs font-semibold">
                                        {{ categoryMap[t.category_id] || 'Uncategorized' }}
                                    </span>
                                </td>

                                <td class="px-6 py-4 text-right">
                                    <span
                                        class="text-sm font-bold"
                                        :class="t.type === 'income' ? 'text-emerald-600' : 'text-slate-900'"
                                    >
                                        {{ t.type === 'income' ? '+' : '-' }}
                                        {{ formatCurrency(t.amount) }}
                                    </span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <ConfirmDateChangeModal
        :show="showDateModal"
        :label="label"
        @confirm="confirmChange"
        @cancel="cancelChange"
    />
</template>