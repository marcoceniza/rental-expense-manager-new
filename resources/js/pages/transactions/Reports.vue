<script setup lang="ts">
import ConfirmDateChangeModal from '@/components/ConfirmDateChangeModal.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import type { SharedData } from '@/types';
import { router, usePage } from '@inertiajs/vue3';
import { format } from 'date-fns';
import jsPDF from 'jspdf';
import autoTable from 'jspdf-autotable';
import { BarChart3, Calendar, FileChartPie, FileDown, PieChart } from 'lucide-vue-next';
import { computed, ref } from 'vue';

defineOptions({
    layout: AppLayout,
});

interface User {
    name: string;
    email: string;
    user_type: string;
}

interface MonthlyData {
    month: string;
    income: number;
    expense: number;
    liability: number;
    net: number;
}

interface AnnualReport {
    monthly: MonthlyData[];
    totals: {
        income: number;
        expense: number;
        liability: number;
        net: number;
    };
}

interface CategorySummary {
    categoryReport: Array<{
        name: string;
        type: string;
        total: number;
    }>;
}

interface Props {
    year?: number;
    annualReport?: AnnualReport;
    categoryReport?: unknown[];
    user?: User;
    categorySummary?: CategorySummary;
}

const page = usePage<SharedData>();
const isAdmin = computed(() => page.props.auth?.user?.user_type === 'admin');

const props = withDefaults(defineProps<Props>(), {
    categoryReport: () => [],
});

const currentYear = ref<number | undefined>(props.year ? Number(props.year) : undefined);
const selectedYear = ref<number | undefined>(currentYear.value);
const pendingYear = ref<number | null>(null);
const showYearModal = ref(false);

const annualStats = computed(() => {
    const data = props.annualReport || { monthly: [], totals: { income: 0, expense: 0, liability: 0, net: 0 } };

    return {
        monthly: Array.isArray(data.monthly) ? data.monthly : [],
        totals: {
            income: Number(data?.totals?.income) || 0,
            expense: Number(data?.totals?.expense) || 0,
            liability: Number(data?.totals?.liability) || 0,
            net: Number(data?.totals?.net) || 0,
        },
    };
});

const handleYearSelect = (event: Event) => {
    const target = event.target as HTMLSelectElement;
    const selected = target.value ? Number(target.value) : undefined;

    if (selected === currentYear.value) {
        selectedYear.value = currentYear.value;
        return;
    }

    pendingYear.value = selected ?? null;
    showYearModal.value = true;
};

const confirmYearChange = () => {
    const reportPath = isAdmin.value ? '/admin/reports' : '/reports';

    router.get(reportPath, { year: pendingYear.value });
};

const cancelYearChange = () => {
    selectedYear.value = currentYear.value;
    pendingYear.value = null;
    showYearModal.value = false;
};

const label = computed(() => String(pendingYear.value ?? currentYear.value));

const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('en-PH', {
        style: 'currency',
        currency: 'PHP',
    }).format(Number(amount) || 0);
};

const pdfCurrency = (value: number) => {
    return `PHP ${Number(value || 0).toLocaleString('en-PH', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    })}`;
};

const exportPDF = () => {
    const doc = new jsPDF();
    const year = currentYear.value;

    doc.setFontSize(22);
    doc.text('Annual Financial Report', 14, 20);

    doc.setFontSize(12);
    doc.text(`Year: ${year}`, 14, 28);
    doc.text(`Generated on: ${format(new Date(), 'PPP')}`, 14, 34);

    doc.setFontSize(16);
    doc.text('Monthly Summary', 14, 48);

    const summaryData = annualStats.value.monthly.map((m) => [
        m.month,
        pdfCurrency(m.income),
        pdfCurrency(m.expense),
        pdfCurrency(m.liability),
        pdfCurrency(m.net),
    ]);

    autoTable(doc, {
        startY: 54,
        head: [['Month', 'Income', 'Expense', 'Liability', 'Net']],
        body: summaryData,
        foot: [
            [
                'TOTALS',
                pdfCurrency(annualStats.value.totals.income),
                pdfCurrency(annualStats.value.totals.expense),
                pdfCurrency(annualStats.value.totals.liability),
                pdfCurrency(annualStats.value.totals.net),
            ],
        ],

        styles: {
            fontSize: 10,
        },

        columnStyles: {
            1: { halign: 'right' },
            2: { halign: 'right' },
            3: { halign: 'right' },
            4: { halign: 'right' },
        },
    });

    doc.save(`Financial_Report_${year}.pdf`);
};
const annualBarChart = computed(() => {
    const data = annualStats.value.monthly;

    return {
        series: [
            { name: 'Income', data: data.map((m) => Number(m.income) || 0) },
            { name: 'Expense', data: data.map((m) => Number(m.expense) || 0) },
            { name: 'Liability', data: data.map((m) => Number(m.liability) || 0) },
        ],
        options: {
            chart: { type: 'bar' },
            xaxis: { categories: data.map((m) => m.month) },
            dataLabels: { enabled: false },
            colors: ['#22c55e', '#ef4444', '#f59e0b'],
        },
    };
});
</script>

<template>
    <div class="space-y-8">
        <div class="flex flex-col justify-between gap-4 md:flex-row md:items-center">
            <div>
                <h2 class="flex items-center gap-3 text-3xl font-bold tracking-tight text-slate-900 max-sm:text-2xl">
                    <FileChartPie class="h-8 w-8" />
                    Financial Reports
                </h2>
                <p class="mt-1 text-slate-500">Analyze your annual performance.</p>
            </div>

            <div class="flex items-center gap-3 max-sm:justify-end">
                <select
                    v-model="selectedYear"
                    @change="handleYearSelect"
                    class="rounded-xl border border-slate-200 bg-white px-4 py-3 font-bold text-slate-700 shadow-sm transition-all focus:ring-2 focus:ring-blue-500"
                >
                    <option v-for="y in [2024, 2025, 2026, 2027]" :key="y" :value="y">
                        {{ y }}
                    </option>
                </select>
                <button
                    v-if="props.user?.user_type === 'admin'"
                    @click="exportPDF"
                    class="flex cursor-pointer items-center gap-2 rounded-xl bg-slate-900 px-6 py-3 font-bold text-white shadow-lg transition-all hover:bg-slate-800 active:scale-95"
                >
                    <FileDown class="h-5 w-5" />
                    Export PDF
                </button>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-6 md:grid-cols-4">
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <p class="text-xs font-bold uppercase tracking-widest text-slate-400">Annual Income</p>
                <h3 class="mt-1 text-2xl font-bold text-emerald-600">
                    {{ formatCurrency(annualStats.totals.income) }}
                </h3>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <p class="text-xs font-bold uppercase tracking-widest text-slate-400">Annual Expenses</p>
                <h3 class="mt-1 text-2xl font-bold text-red-600">
                    {{ formatCurrency(annualStats.totals.expense) }}
                </h3>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <p class="text-xs font-bold uppercase tracking-widest text-slate-400">Annual Liabilities</p>
                <h3 class="mt-1 text-2xl font-bold text-amber-600">
                    {{ formatCurrency(annualStats.totals.liability) }}
                </h3>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <p class="text-xs font-bold uppercase tracking-widest text-slate-400">Net Profit/Loss</p>
                <h3 class="mt-1 text-2xl font-bold" :class="annualStats.totals.net >= 0 ? 'text-blue-600' : 'text-red-600'">
                    {{ formatCurrency(annualStats.totals.net) }}
                </h3>
            </div>
        </div>

        <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="flex items-center gap-2 border-b border-slate-100 p-6">
                <Calendar class="h-5 w-5 text-blue-600" />
                <h3 class="text-lg font-bold text-slate-900">Monthly Breakdown</h3>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead class="bg-slate-50 text-xs font-bold uppercase text-slate-500">
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
                            <td colspan="5" class="px-6 py-12 text-center italic text-slate-400">No data available for this year.</td>
                        </tr>
                        <tr v-for="m in annualStats.monthly || []" :key="m.month" class="transition-colors hover:bg-slate-50">
                            <td class="px-6 py-4 font-semibold text-slate-700">
                                {{ m.month }}
                            </td>

                            <td class="px-6 py-4 text-right font-medium text-emerald-600">
                                {{ formatCurrency(m.income) }}
                            </td>

                            <td class="px-6 py-4 text-right font-medium text-red-600">
                                {{ formatCurrency(m.expense) }}
                            </td>

                            <td class="px-6 py-4 text-right font-medium text-amber-600">
                                {{ formatCurrency(m.liability) }}
                            </td>

                            <td class="px-6 py-4 text-right font-bold" :class="m.net >= 0 ? 'text-slate-900' : 'text-red-600'">
                                {{ formatCurrency(m.net) }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-8 lg:grid-cols-2">
            <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div class="flex items-center gap-2 border-b border-slate-100 p-6">
                    <PieChart class="h-5 w-5 text-blue-600" />
                    <h3 class="text-lg font-bold text-slate-900">Category Report</h3>
                </div>

                <div class="space-y-4 p-6">
                    <div v-for="c in categorySummary?.categoryReport || []" :key="c.name" class="flex items-center justify-between">
                        <div class="flex flex-col">
                            <span class="text-sm font-bold text-slate-700">
                                {{ c.name }}
                            </span>
                            <span class="text-[10px] font-bold uppercase tracking-widest text-slate-400">
                                {{ c.type }}
                            </span>
                        </div>

                        <span class="text-sm font-bold text-slate-900">
                            {{ formatCurrency(c.total) }}
                        </span>
                    </div>

                    <div v-if="(categorySummary?.categoryReport || []).length === 0" class="py-8 text-center italic text-slate-400">
                        No data available for this year.
                    </div>
                </div>
            </div>

            <div class="flex flex-col overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div class="flex items-center gap-2 border-b border-slate-100 p-6">
                    <BarChart3 class="h-5 w-5 text-blue-600" />
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

                    <div v-else class="py-12 text-center italic text-slate-400">No data available for this year.</div>
                </div>
            </div>
        </div>
    </div>

    <ConfirmDateChangeModal :show="showYearModal" :label="label" @confirm="confirmYearChange" @cancel="cancelYearChange" />
</template>
