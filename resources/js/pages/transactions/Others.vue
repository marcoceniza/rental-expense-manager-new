<script setup lang="ts">
import { ref, computed } from 'vue'
import { router, useForm } from '@inertiajs/vue3'
import { format, parseISO } from 'date-fns'
import { LayoutGrid, ArrowUpRight, ArrowDownRight, Pencil, Trash2 } from 'lucide-vue-next'
import ConfirmDateChangeModal from '@/components/ConfirmDateChangeModal.vue'
import ConfirmDelete from '@/components/ConfirmDelete.vue'
import OthersModal from '@/components/OthersModal.vue'
import BasePagination from '@/components/base/BasePagination.vue'
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
    description: string
    remarks?: string | null
    amount: number
    type: 'income' | 'expense' | 'liability'
    transaction_date: string
    category_id: number
}

interface PaginatedData<T> {
    data: T[]
    current_page: number
    last_page: number
    per_page: number
    total: number
    prev_page_url: string | null
    next_page_url: string | null
    links: Array<{
        url: string | null
        label: string
        active: boolean
    }>
}

interface OtherStats {
    income: number
    transactions: PaginatedData<Transaction>
}

const props = defineProps<{
    otherStats: OtherStats
    categories: Category[]
    year: number
}>()

const currentYear = ref<number>(props.year)
const pendingYear = ref<number | null>(null)
const showYearModal = ref(false)
const editingId = ref<number | null>(null)
const showModal = ref<boolean>(false)
const isSubmitting = ref<boolean>(false)

const categoryTypes = ['income', 'expense', 'liability']
const filteredCategories = computed(() => props.categories)

const form = useForm<{
	transaction_date: string
	type: string
	category_id: string
	description: string
	amount: number
	remarks: string
	redirect?: string
}>({
	transaction_date: format(new Date(), 'yyyy-MM-dd'),
	type: 'income',
	category_id: '',
	description: '',
	amount: 0,
	remarks: '',
	redirect: 'admin.others'
})

const confirmDelete = ref<{
	show: boolean
	id: number | null
	name: string
}>({
	show: false,
	id: null,
	name: ''
})

const categoryMap = computed<Record<number, string>>(() => {
    const map: Record<number, string> = {}

    props.categories.forEach((c: Category) => {
        map[c.id] = c.name
    })

    return map
})

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
            route('admin.others'),
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

const label = computed<string>(() =>
    String(pendingYear.value ?? currentYear.value)
)

const formatCurrency = (amount: number | null | undefined) => {
    return new Intl.NumberFormat('en-PH', {
        style: 'currency',
        currency: 'PHP',
    }).format(amount ?? 0)
}

const openModal = (t: any = null) => {
	if (t) {
		editingId.value = t.id

		form.defaults({
			transaction_date: format(parseISO(t.transaction_date), 'yyyy-MM-dd'),
			type: t.type,
			category_id: String(t.category_id),
			description: t.description,
			amount: t.amount,
			remarks: t.remarks || '',
			redirect: 'admin.others',
		})
	} else {
		editingId.value = null

		form.defaults({
			transaction_date: format(new Date(), 'yyyy-MM-dd'),
			type: 'income',
			category_id: '',
			description: '',
			amount: 0,
			remarks: '',
			redirect: 'admin.others',
		})
	}

	form.reset()
	showModal.value = true
}

const closeModal = () => {
	showModal.value = false
	editingId.value = null

	form.reset()
	form.defaults({
		transaction_date: format(new Date(), 'yyyy-MM-dd'),
		type: 'income',
		category_id: '',
		description: '',
		amount: 0,
		remarks: '',
		redirect: 'admin.others',
	})
}

const handleSubmit = () => {
	if (isSubmitting.value) return

	isSubmitting.value = true

	if (editingId.value) {
		form.put(route('admin.transactions.update', { transaction: editingId.value }), {
			preserveScroll: true,
			onFinish: () => {
				isSubmitting.value = false
				closeModal()
			},
		})
	} else {
		form.post(route('admin.transactions.store'), {
			preserveScroll: true,
			onFinish: () => {
				isSubmitting.value = false
				closeModal()
			},
		})
	}
}

const confirmDeleteHandler = (id: number, name: string) => {
	confirmDelete.value = { show: true, id, name }
}

const deleteTransaction = () => {
    if (!confirmDelete.value.id) return

    form.delete(route('admin.transactions.destroy', {
        transaction: confirmDelete.value.id,
        redirect: 'admin.others',
    }), {
        preserveScroll: true,
        onFinish: () => {
            confirmDelete.value.show = false
        },
    })
}
</script>

<template>
    <div class="space-y-8">
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

        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="p-6 border-b border-slate-100 flex justify-between items-center">
                <h3 class="text-lg font-bold text-slate-900">
                    Other Transactions
                </h3>

                <p class="text-emerald-500">
                    Total:
                    <strong>
                        {{ formatCurrency(props.otherStats?.income) }}
                    </strong>
                </p>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left">

                    <thead class="bg-slate-50 text-slate-500 text-xs uppercase font-bold">
                        <tr>
                            <th class="px-6 py-4">Date</th>
                            <th class="px-6 py-4">Description</th>
                            <th class="px-6 py-4">Category</th>
                            <th class="px-6 py-4">Amount</th>
                            <th class="px-6 py-4 text-center">Actions</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-slate-100">
                        <tr v-if="!(props.otherStats?.transactions?.data?.length)">
                            <td colspan="5" class="px-6 py-12 text-center text-slate-400 italic">
                                No transaction others found.
                            </td>
                        </tr>
                        <tr
                            v-for="t in (props.otherStats?.transactions?.data ?? [])"
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
                                <p v-if="t.remarks" class="text-xs text-slate-400 mt-0.5">
                                    {{ t.remarks }}
                                </p>
                            </td>

                            <td class="px-6 py-4">
                                <span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-slate-100 text-slate-600">
                                    {{ categoryMap[t.category_id] || 'Uncategorized' }}
                                </span>
                            </td>

                            <td class="px-6 py-4">
                                <span
                                    class="text-sm font-bold"
                                    :class="t.type === 'income' ? 'text-emerald-600' : 'text-slate-900'"
                                >
                                    {{ t.type === 'income' ? '+' : '-' }}
                                    {{ formatCurrency(t.amount) }}
                                </span>
                            </td>

                            <td class="px-6 py-4">
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
                <BasePagination
                    v-if="otherStats.transactions.last_page > 1"
                    :pagination="otherStats.transactions"
                />
            </div>
        </div>

    </div>

    <OthersModal
        v-model:isOpen="showModal"
        :editingId="editingId"
        :formData="form"
        :categoryTypes="categoryTypes"
        :filteredCategories="filteredCategories"
        :isSubmitting="isSubmitting"
        :loading="form.processing"
        @submit="handleSubmit"
        @close="closeModal"
    />

    <ConfirmDelete
        v-if="confirmDelete.show"
        :isOpen="confirmDelete.show"
        title="Delete Transaction"
        :message="confirmDelete.name"
        actionName="transaction"
        :loading="form.processing"
        @confirm="deleteTransaction"
        @close="confirmDelete.show = false"
    />

    <ConfirmDateChangeModal
        :show="showYearModal"
        :label="label"
        @confirm="confirmYearChange"
        @cancel="cancelYearChange"
    />
</template>