<script setup lang="ts">
import ConfirmDateChangeModal from '@/components/ConfirmDateChangeModal.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { Link, router } from '@inertiajs/vue3';
import { addMonths, format, parseISO, subMonths } from 'date-fns';
import { ArrowDownRight, ArrowUpRight, CreditCard, LayoutDashboard, TrendingDown, TrendingUp, Wallet } from 'lucide-vue-next';
import { computed, ref } from 'vue';
import { route } from 'ziggy-js';

defineOptions({
    layout: AppLayout,
});

interface Category {
    id: number;
    name: string;
}

interface Transaction {
    id: number;
    amount: number;
    type: string;
    transaction_date: string;
    category_id: number;
    description: string;
}

const props = defineProps<{
    transactions: Transaction[];
    monthlyReport: {
        income: number;
        expense: number;
        liability: number;
        net: number;
    };
    categories: Category[];
    month: string;
}>();

const currentMonth = ref(new Date(`${props.month}-01`));
const showDateModal = ref(false);
const pendingDate = ref<Date | null>(null);

const applyChange = (date: Date) => {
    const formatted = date.toISOString().slice(0, 7);

    router.get(
        route('admin.dashboard'),
        { month: formatted },
        {
            preserveScroll: true,
        },
    );

    currentMonth.value = date;
};

const handleChange = (newDate: Date) => {
    const currentYear = currentMonth.value.getFullYear();
    const newYear = newDate.getFullYear();

    if (currentYear !== newYear) {
        pendingDate.value = newDate;
        showDateModal.value = true;
        return;
    }

    applyChange(newDate);
};

const confirmChange = () => {
    if (pendingDate.value) {
        applyChange(pendingDate.value);
    }

    showDateModal.value = false;
    pendingDate.value = null;
};

const cancelChange = () => {
    showDateModal.value = false;
    pendingDate.value = null;
};

const prevMonth = () => {
    handleChange(subMonths(currentMonth.value, 1));
};

const nextMonth = () => {
    handleChange(addMonths(currentMonth.value, 1));
};

const label = computed(() => {
    const date = pendingDate.value || currentMonth.value;

    return date.toLocaleString('default', {
        month: 'long',
        year: 'numeric',
    });
});

const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('en-PH', {
        style: 'currency',
        currency: 'PHP',
    }).format(amount);
};

const categoryMap = computed(() => {
    const map: Record<number, string> = {};

    props.categories.forEach((category) => {
        map[category.id] = category.name;
    });

    return map;
});

const monthlyDonutSeries = computed(() => [
    Number(props.monthlyReport?.income) || 0,
    Number(props.monthlyReport?.expense) || 0,
    Number(props.monthlyReport?.liability) || 0,
]);

const monthlyDonutOptions = {
    labels: ['Income', 'Expense', 'Liability'],
    colors: ['#22c55e', '#ef4444', '#f59e0b'],
    legend: {
        position: 'bottom',
    },
};
</script>

<template>
    <div class="space-y-8">
        <div class="flex flex-col justify-between gap-4 md:flex-row md:items-center">
            <div>
                <h2 class="flex items-center gap-3 text-3xl font-bold tracking-tight text-slate-900 max-sm:text-2xl">
                    <LayoutDashboard class="h-8 w-8" />
                    Financial Overview
                </h2>
                <p class="mt-1 text-slate-500">Track your income and expenses efficiently.</p>
            </div>

            <div class="flex items-center gap-3 rounded-xl border border-slate-200 bg-white p-2 shadow-sm max-sm:justify-around">
                <button @click="prevMonth" class="cursor-pointer rounded-lg p-2 transition-colors hover:bg-slate-50">
                    <ArrowDownRight class="h-5 w-5 rotate-90 text-slate-400" />
                </button>

                <span class="min-w-35 text-center text-sm font-semibold text-slate-700">
                    {{ label }}
                </span>

                <button @click="nextMonth" class="cursor-pointer rounded-lg p-2 transition-colors hover:bg-slate-50">
                    <ArrowUpRight class="h-5 w-5 -rotate-90 text-slate-400" />
                </button>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-4">
            <div class="group rounded-2xl border border-slate-200 bg-white p-6 shadow-sm transition-colors hover:border-blue-200">
                <div class="mb-4 flex items-center justify-between">
                    <div class="rounded-xl bg-blue-50 p-3 text-blue-600 transition-colors group-hover:bg-blue-600 group-hover:text-white">
                        <TrendingUp class="h-6 w-6" />
                    </div>
                </div>
                <p class="mb-2 text-sm font-medium uppercase tracking-wider text-slate-500">Total Income</p>
                <h3 class="mt-1 text-2xl font-bold text-slate-900">
                    {{ formatCurrency(props.monthlyReport?.income || 0) }}
                </h3>
            </div>

            <div class="group rounded-2xl border border-slate-200 bg-white p-6 shadow-sm transition-colors hover:border-red-200">
                <div class="mb-4 flex items-center justify-between">
                    <div class="rounded-xl bg-red-50 p-3 text-red-600 transition-colors group-hover:bg-red-600 group-hover:text-white">
                        <TrendingDown class="h-6 w-6" />
                    </div>
                </div>
                <p class="mb-2 text-sm font-medium uppercase tracking-wider text-slate-500">Total Expenses</p>
                <h3 class="mt-1 text-2xl font-bold text-slate-900">
                    {{ formatCurrency(props.monthlyReport?.expense || 0) }}
                </h3>
            </div>

            <div class="group rounded-2xl border border-slate-200 bg-white p-6 shadow-sm transition-colors hover:border-amber-200">
                <div class="mb-4 flex items-center justify-between">
                    <div class="rounded-xl bg-amber-50 p-3 text-amber-600 transition-colors group-hover:bg-amber-600 group-hover:text-white">
                        <CreditCard class="h-6 w-6" />
                    </div>
                </div>
                <p class="mb-2 text-sm font-medium uppercase tracking-wider text-slate-500">Liabilities</p>
                <h3 class="mt-1 text-2xl font-bold text-slate-900">
                    {{ formatCurrency(props.monthlyReport?.liability || 0) }}
                </h3>
            </div>

            <div class="group rounded-2xl border border-slate-200 bg-white p-6 shadow-sm transition-colors hover:border-emerald-200">
                <div class="mb-4 flex items-center justify-between">
                    <div class="rounded-xl bg-emerald-50 p-3 text-emerald-600 transition-colors group-hover:bg-emerald-600 group-hover:text-white">
                        <Wallet class="h-6 w-6" />
                    </div>
                </div>
                <p class="mb-2 text-sm font-medium uppercase tracking-wider text-slate-500">Net Balance</p>
                <h3 class="mt-1 text-2xl font-bold text-slate-900" :class="props.monthlyReport?.net >= 0 ? 'text-emerald-600' : 'text-red-600'">
                    {{ formatCurrency(props.monthlyReport?.net || 0) }}
                </h3>
            </div>
        </div>

        <div class="flex flex-col gap-6 lg:flex-row">
            <div class="flex flex-col rounded-2xl border border-slate-200 bg-white p-4 shadow-sm lg:w-1/4">
                <h3 class="mb-4 w-full text-lg font-bold text-slate-900 lg:mt-2.5 lg:text-center xl:text-xl">Monthly Overview</h3>

                <apexchart type="donut" height="280" :options="monthlyDonutOptions" :series="monthlyDonutSeries" />
            </div>

            <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm lg:w-4/5">
                <div class="flex items-center justify-between border-b border-slate-100 p-6">
                    <h3 class="text-lg font-bold text-slate-900">Recent Activity</h3>
                    <Link
                        :href="route('admin.transactions.index')"
                        class="cursor-pointer text-sm font-medium text-blue-600 transition-colors hover:text-blue-500"
                    >
                        View All
                    </Link>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead class="bg-slate-50 text-xs font-bold uppercase text-slate-500">
                            <tr>
                                <th class="px-6 py-4">Date</th>
                                <th class="px-6 py-4">Description</th>
                                <th class="px-6 py-4">Category</th>
                                <th class="px-6 py-4 text-right">Amount</th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-slate-100">
                            <tr v-if="(props.transactions || []).length === 0">
                                <td colspan="4" class="px-6 py-12 text-center italic text-slate-400">No transactions recorded yet.</td>
                            </tr>

                            <tr v-for="t in (props.transactions || []).slice(0, 5)" :key="t.id" class="transition-colors hover:bg-slate-50">
                                <td class="px-6 py-4 text-sm text-slate-600">
                                    {{ format(parseISO(t.transaction_date), 'MMM dd, yyyy') }}
                                </td>

                                <td class="px-6 py-4">
                                    <p class="text-sm font-medium text-slate-900">{{ t.description }}</p>
                                </td>

                                <td class="px-6 py-4">
                                    <span class="rounded-full px-2.5 py-1 text-xs font-semibold text-slate-900">
                                        {{ categoryMap[t.category_id] || 'Uncategorized' }}
                                    </span>
                                </td>

                                <td class="px-6 py-4 text-right">
                                    <span class="text-sm font-bold" :class="t.type === 'income' ? 'text-emerald-600' : 'text-slate-900'">
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

    <ConfirmDateChangeModal :show="showDateModal" :label="label" @confirm="confirmChange" @cancel="cancelChange" />
</template>
