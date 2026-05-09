<script setup>
import { ref, computed } from 'vue'
import { router, usePage } from '@inertiajs/vue3'
import { format } from 'date-fns'
import { FileDown, FileChartPie, Calendar, PieChart, BarChart3 } from 'lucide-vue-next'
import jsPDF from 'jspdf'
import autoTable from 'jspdf-autotable'
import ConfirmDateChangeModal from '@/components/ConfirmDateChangeModal.vue'
import AppLayout from '@/layouts/AppLayout.vue'

defineOptions({
    layout: AppLayout,
})

const page = usePage()
const isAdmin = computed(() => page.props.auth?.user?.user_type === 'admin')

const props = defineProps({
    year: Number,
    annualReport: Object,
    categoryReport: Array,
    user: Object,
    categorySummary: Object,
});

const currentYear = ref(props.year)
const pendingYear = ref(null)
const showYearModal = ref(false)

const annualStats = computed(() => {
    const data = props.annualReport || {}

    return {
        monthly: Array.isArray(data.monthly) ? data.monthly : [],
        totals: {
            income: Number(data?.totals?.income) || 0,
            expense: Number(data?.totals?.expense) || 0,
            liability: Number(data?.totals?.liability) || 0,
            net: Number(data?.totals?.net) || 0,
        },
    }
});

const yearModel = computed({
    get: () => currentYear.value,
    set: (newYear) => {
        if (newYear === currentYear.value) return
        pendingYear.value = newYear
        showYearModal.value = true
    },
})

const confirmYearChange = () => {
    if (pendingYear.value) {
        const reportPath = isAdmin.value ? '/admin/reports' : '/reports'
        router.get(reportPath, { year: pendingYear.value }, {
            preserveState: true,
            preserveScroll: true,
        })

        currentYear.value = pendingYear.value
    }

    showYearModal.value = false
    pendingYear.value = null
}

const cancelYearChange = () => {
    pendingYear.value = null
    showYearModal.value = false
}

const label = computed(() => String(pendingYear.value))

const formatCurrency = (amount) => {
    return new Intl.NumberFormat('en-PH', {
        style: 'currency',
        currency: 'PHP',
    }).format(Number(amount) || 0)
}

const pdfCurrency = (value) => {
    const num = Number(value) || 0
    return `PHP ${num.toFixed(2)}`
}

const exportPDF = () => {
    const doc = new jsPDF()
    const year = currentYear.value

    doc.setFontSize(22)
    doc.text('Annual Financial Report', 14, 20)

    doc.setFontSize(12)
    doc.text(`Year: ${year}`, 14, 28)
    doc.text(`Generated on: ${format(new Date(), 'PPP')}`, 14, 34)

    doc.setFontSize(16)
    doc.text('Monthly Summary', 14, 48)

    const summaryData = annualStats.value.monthly.map(m => [
        m.month,
        pdfCurrency(m.income),
        pdfCurrency(m.expense),
        pdfCurrency(m.liability),
        pdfCurrency(m.net),
    ])

    autoTable(doc, {
        startY: 54,
        head: [['Month', 'Income', 'Expense', 'Liability', 'Net']],
        body: summaryData,
        foot: [[
            'TOTALS',
            pdfCurrency(annualStats.value.totals.income),
            pdfCurrency(annualStats.value.totals.expense),
            pdfCurrency(annualStats.value.totals.liability),
            pdfCurrency(annualStats.value.totals.net),
        ]],
    })

    doc.save(`Financial_Report_${year}.pdf`)
}
const annualBarChart = computed(() => {
    const data = annualStats.value.monthly

    return {
        series: [
            { name: 'Income', data: data.map(m => Number(m.income) || 0) },
            { name: 'Expense', data: data.map(m => Number(m.expense) || 0) },
            { name: 'Liability', data: data.map(m => Number(m.liability) || 0) },
        ],
        options: {
            chart: { type: 'bar' },
            xaxis: { categories: data.map(m => m.month) },
            dataLabels: { enabled: false },
            colors: ['#22c55e', '#ef4444', '#f59e0b'],
        },
    }
})
</script>

<template>
<div class="space-y-8">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h2 class="text-3xl font-bold text-slate-900 tracking-tight flex items-center gap-3 max-sm:text-2xl">
                <FileChartPie class="w-8 h-8" />
                Financial Reports
            </h2>
            <p class="text-slate-500 mt-1">
                Analyze your annual performance.
            </p>
        </div>

        <div class="flex items-center gap-3 max-sm:justify-end">
            <select
                v-model="yearModel"
                class="bg-white border border-slate-200 rounded-xl px-4 py-3 focus:ring-2 focus:ring-blue-500 transition-all font-bold text-slate-700 shadow-sm"
            >
                <option v-for="y in [2024,2025,2026,2027]" :key="y" :value="y">
                    {{ y }}
                </option>
            </select>
            <button
                v-if="props.user?.user_type === 'admin'"
                @click="exportPDF"
                class="flex items-center gap-2 bg-slate-900 hover:bg-slate-800 text-white px-6 py-3 rounded-xl font-bold transition-all shadow-lg active:scale-95 cursor-pointer"
            >
                <FileDown class="w-5 h-5" />
                Export PDF
            </button>

        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200">
            <p class="text-xs font-bold text-slate-400 uppercase tracking-widest">
                Annual Income
            </p>
            <h3 class="text-2xl font-bold text-emerald-600 mt-1">
                {{ formatCurrency(annualStats.totals.income) }}
            </h3>
        </div>

        <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200">
            <p class="text-xs font-bold text-slate-400 uppercase tracking-widest">
                Annual Expenses
            </p>
            <h3 class="text-2xl font-bold text-red-600 mt-1">
                {{ formatCurrency(annualStats.totals.expense) }}
            </h3>
        </div>

        <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200">
            <p class="text-xs font-bold text-slate-400 uppercase tracking-widest">
                Annual Liabilities
            </p>
            <h3 class="text-2xl font-bold text-amber-600 mt-1">
                {{ formatCurrency(annualStats.totals.liability) }}
            </h3>
        </div>

        <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200">
            <p class="text-xs font-bold text-slate-400 uppercase tracking-widest">
                Net Profit/Loss
            </p>
            <h3
                class="text-2xl font-bold mt-1"
                :class="annualStats.totals.net >= 0 ? 'text-blue-600' : 'text-red-600'"
            >
                {{ formatCurrency(annualStats.totals.net) }}
            </h3>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="p-6 border-b border-slate-100 flex items-center gap-2">
            <Calendar class="w-5 h-5 text-blue-600" />
            <h3 class="text-lg font-bold text-slate-900">Monthly Breakdown</h3>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead class="bg-slate-50 text-slate-500 text-xs uppercase font-bold">
                    <tr>
                        <th class="px-6 py-4">Month</th>
                        <th class="px-6 py-4 text-right">Income</th>
                        <th class="px-6 py-4 text-right">Expense</th>
                        <th class="px-6 py-4 text-right">Liability</th>
                        <th class="px-6 py-4 text-right">Net</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-slate-100">
                    <tr v-if="(annualStats.monthly || []).length === 0">
                        <td colspan="5" class="px-6 py-12 text-center text-slate-400 italic">
                            No data available for this year.
                        </td>
                    </tr>
                    <tr
                        v-for="m in (annualStats.monthly || [])"
                        :key="m.month"
                        class="hover:bg-slate-50 transition-colors"
                    >
                        <td class="px-6 py-4 font-semibold text-slate-700">
                            {{ m.month }}
                        </td>

                        <td class="px-6 py-4 text-right text-emerald-600 font-medium">
                            {{ formatCurrency(m.income) }}
                        </td>

                        <td class="px-6 py-4 text-right text-red-600 font-medium">
                            {{ formatCurrency(m.expense) }}
                        </td>

                        <td class="px-6 py-4 text-right text-amber-600 font-medium">
                            {{ formatCurrency(m.liability) }}
                        </td>

                        <td
                            class="px-6 py-4 text-right font-bold"
                            :class="m.net >= 0 ? 'text-slate-900' : 'text-red-600'"
                        >
                            {{ formatCurrency(m.net) }}
                        </td>
                    </tr>

                </tbody>
            </table>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="p-6 border-b border-slate-100 flex items-center gap-2">
                <PieChart class="w-5 h-5 text-blue-600" />
                <h3 class="text-lg font-bold text-slate-900">Category Report</h3>
            </div>

            <div class="p-6 space-y-4">
                <div
                    v-for="c in (categorySummary.categoryReport || [])"
                    :key="c.name"
                    class="flex items-center justify-between"
                >
                    <div class="flex flex-col">
                        <span class="text-sm font-bold text-slate-700">
                            {{ c.name }}
                        </span>
                        <span class="text-[10px] uppercase font-bold tracking-widest text-slate-400">
                            {{ c.type }}
                        </span>
                    </div>

                    <span class="text-sm font-bold text-slate-900">
                        {{ formatCurrency(c.total) }}
                    </span>
                </div>

                <div
                    v-if="(categorySummary.categoryReport || []).length === 0"
                    class="py-8 text-center text-slate-400 italic"
                >
                    No data available for this year.
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden flex flex-col">
            <div class="p-6 border-b border-slate-100 flex items-center gap-2">
                <BarChart3 class="w-5 h-5 text-blue-600" />
                <h3 class="text-lg font-bold text-slate-900">Annual Summary</h3>
            </div>

            <div class="p-6">
                <apexchart
                    v-if="(annualStats.monthly || []).length > 0"
                    type="bar"
                    height="300"
                    :options="annualBarChart.options"
                    :series="annualBarChart.series"
                />

                <div v-else class="text-center text-slate-400 italic py-12">
                    No data available for this year.
                </div>
            </div>
        </div>

    </div>

</div>

<ConfirmDateChangeModal
    :show="showYearModal"
    :label="label"
    @confirm="confirmYearChange"
    @cancel="cancelYearChange"
/>
</template>