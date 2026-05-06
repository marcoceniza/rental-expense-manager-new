<script setup>
import { ref, computed } from 'vue'
import { router } from '@inertiajs/vue3'
import { format } from 'date-fns'
import { LayoutGrid, ArrowUpRight, ArrowDownRight } from 'lucide-vue-next'
import ConfirmDateChangeModal from '@/components/ConfirmDateChangeModal.vue'
import AppLayout from '@/layouts/AppLayout.vue'

defineOptions({
    layout: AppLayout,
})

/**
 * PROPS FROM LARAVEL
 */
const props = defineProps({
    otherStats: Object,
    categories: Array,
    year: Number,
})

/**
 * STATE
 */
const currentYear = ref(props.year)
const pendingYear = ref(null)
const showYearModal = ref(false)

/**
 * CATEGORY MAP
 */
const categoryMap = computed(() => {
    const map = {}
    props.categories.forEach(c => {
        map[c.id] = c.name
    })
    return map
})

/**
 * YEAR CHANGE
 */
const handleYearChange = (newYear) => {
    if (newYear === currentYear.value) return

    pendingYear.value = newYear
    showYearModal.value = true
}

const prevYear = () => handleYearChange(currentYear.value - 1)
const nextYear = () => handleYearChange(currentYear.value + 1)

/**
 * CONFIRM CHANGE → INERTIA REQUEST
 */
const confirmYearChange = () => {
    if (pendingYear.value !== null) {
        router.get('/others', { year: pendingYear.value }, {
            preserveState: true,
            preserveScroll: true,
        })

        currentYear.value = pendingYear.value
    }

    showYearModal.value = false
    pendingYear.value = null
}

const cancelYearChange = () => {
    showYearModal.value = false
    pendingYear.value = null
}

/**
 * LABEL
 */
const label = computed(() => String(pendingYear.value))

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
                    <LayoutGrid class="w-8 h-8" />
                    Others
                </h2>
                <p class="text-slate-500 mt-1">
                    Monitor all transactions related to others.
                </p>
            </div>

            <!-- YEAR NAV -->
            <div class="flex items-center max-sm:justify-around gap-3 bg-white p-2 rounded-xl shadow-sm border border-slate-200">
                <button @click="prevYear" class="p-2 hover:bg-slate-50 rounded-lg transition-colors cursor-pointer">
                    <ArrowDownRight class="w-5 h-5 rotate-90 text-slate-400" />
                </button>

                <span class="text-sm font-semibold text-slate-700 min-w-35 text-center">
                    {{ currentYear }}
                </span>

                <button
                    @click="nextYear"
                    class="p-2 hover:bg-slate-50 rounded-lg transition-colors cursor-pointer"
                    :disabled="currentYear >= new Date().getFullYear()"
                    :class="{ 'cursor-not-allowed opacity-50': currentYear >= new Date().getFullYear() }"
                >
                    <ArrowUpRight class="w-5 h-5 -rotate-90 text-slate-400" />
                </button>
            </div>
        </div>

        <!-- TABLE CARD -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">

            <!-- HEADER -->
            <div class="p-6 border-b border-slate-100 flex justify-between items-center">
                <h3 class="text-lg font-bold text-slate-900">
                    Other Transactions
                </h3>

                <p class="text-red-500">
                    Total:
                    <strong>
                        {{ formatCurrency(props.otherStats?.expense || 0) }}
                    </strong>
                </p>
            </div>

            <!-- TABLE -->
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

                        <!-- EMPTY -->
                        <tr v-if="(props.otherStats?.transactions || []).length === 0">
                            <td colspan="4" class="px-6 py-12 text-center text-slate-400 italic">
                                No transactions found.
                            </td>
                        </tr>

                        <!-- LIST -->
                        <tr
                            v-for="t in (props.otherStats?.transactions || [])"
                            :key="t.id"
                            class="hover:bg-slate-50 transition-colors"
                        >
                            <td class="px-6 py-4 text-sm text-slate-600">
                                {{ format(new Date(t.transaction_date), 'MMM dd, yyyy') }}
                            </td>

                            <td class="px-6 py-4">
                                <p class="text-sm font-medium text-slate-900">
                                    {{ t.description }}
                                </p>
                                <p v-if="t.remarks" class="text-xs text-slate-400 mt-0.5">
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

    <!-- MODAL -->
    <ConfirmDateChangeModal
        :show="showYearModal"
        :label="label"
        @confirm="confirmYearChange"
        @cancel="cancelYearChange"
    />
</template>