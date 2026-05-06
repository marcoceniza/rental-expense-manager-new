<script setup>
import { ref, computed } from 'vue'
import { router, usePage } from '@inertiajs/vue3'
import { format, parseISO } from 'date-fns'
import { Plus, Pencil, Trash2, ReceiptText } from 'lucide-vue-next'
import AppLayout from '@/layouts/AppLayout.vue'

defineOptions({
    layout: AppLayout,
})

/**
 * INERTIA PROPS
 */
const page = usePage()

const transactions = computed(() => page.props.transactions.data || [])
const pagination = computed(() => page.props.transactions)
const categories = computed(() => page.props.categories || [])
const trashed = computed(() => page.props.trashed || [])
const auth = computed(() => page.props.auth)

/**
 * STATE
 */
const searchQuery = ref('')
const typeFilter = ref('all')

const showModal = ref(false)
const showTrashModal = ref(false)
const isSubmitting = ref(false)

const editingId = ref(null)

const confirmDelete = ref({
	show: false,
	id: null,
	name: ''
})

const formData = ref({
	transaction_date: format(new Date(), 'yyyy-MM-dd'),
	type: 'income',
	category_id: '',
	description: '',
	amount: 0,
	remarks: ''
})

/**
 * FILTERS
 */
const filteredTransactions = computed(() => {
	return transactions.value.filter(t => {
		const search = searchQuery.value.toLowerCase()

		const fields = [
			t.description,
			t.category?.name,
			t.type,
			t.amount,
			new Date(t.transaction_date).toLocaleDateString()
		]

		const matchesSearch = fields.some(field =>
			String(field ?? '').toLowerCase().includes(search)
		)

		const matchesType =
			typeFilter.value === 'all' || t.type === typeFilter.value

		const excludeSpecial =
			t.category?.is_tuition !== true &&
			t.category?.is_other !== true

		return matchesSearch && matchesType && excludeSpecial
	})
})

const filteredCategories = computed(() =>
	categories.value.filter(c => c.type === formData.value.type)
)

/**
 * ACTIONS
 */
const openModal = (t = null) => {
	if (t) {
		editingId.value = t.id
		formData.value = {
			...t,
			transaction_date: format(parseISO(t.transaction_date), 'yyyy-MM-dd')
		}
	} else {
		editingId.value = null
		formData.value = {
			transaction_date: format(new Date(), 'yyyy-MM-dd'),
			type: 'income',
			category_id: '',
			description: '',
			amount: 0,
			remarks: ''
		}
	}

	showModal.value = true
}

const closeModal = () => {
	showModal.value = false
}

/**
 * CREATE / UPDATE
 */
const handleSubmit = () => {
	if (isSubmitting.value) return
	isSubmitting.value = true

	if (editingId.value) {
		router.put(`/transactions/${editingId.value}`, formData.value, {
			preserveScroll: true,
			onFinish: () => {
				isSubmitting.value = false
				closeModal()
			}
		})
	} else {
		router.post('/transactions', formData.value, {
			preserveScroll: true,
			onFinish: () => {
				isSubmitting.value = false
				closeModal()
			}
		})
	}
}

/**
 * DELETE
 */
const confirmDeleteHandler = (id, name) => {
	confirmDelete.value = { show: true, id, name }
}

const deleteTransaction = () => {
	router.delete(`/transactions/${confirmDelete.value.id}`, {
		preserveScroll: true,
		onFinish: () => {
			confirmDelete.value.show = false
		}
	})
}

/**
 * TRASH ACTIONS
 */
const restoreTransaction = (id) => {
	router.post(`/transactions/${id}/restore`, {}, { preserveScroll: true })
}

const deletePermanent = (id) => {
	router.delete(`/transactions/${id}/force-delete`, { preserveScroll: true })
}

/**
 * PAGINATION
 */
const goToPage = (url) => {
	if (!url) return
	router.visit(url, { preserveScroll: true })
}

/**
 * FORMAT
 */
const formatCurrency = (amount) => {
	return new Intl.NumberFormat('en-PH', {
		style: 'currency',
		currency: 'PHP'
	}).format(amount)
}
</script>

<template>
	<div class="space-y-8">

		<!-- HEADER -->
		<div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
			<div>
				<h2 class="text-3xl font-bold text-slate-900 tracking-tight flex items-center gap-3 max-sm:text-2xl">
					<ReceiptText class="w-8 h-8" />
					Transactions
				</h2>
				<p class="text-slate-500 mt-1">Manage all your financial records in one place.</p>
			</div>

			<div v-if="auth.user?.user_type === 'admin'" class="flex items-center gap-3 max-sm:justify-end">
				<button
					@click="showTrashModal = true"
					class="relative p-3 bg-white border border-slate-200 text-slate-400 hover:text-red-500 hover:border-red-200 rounded-xl transition-all shadow-sm group cursor-pointer"
				>
					<Trash2 class="w-5 h-5 group-hover:scale-110 transition-transform" />
				</button>

				<button
					@click="openModal()"
					class="flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-xl font-bold transition-all shadow-lg shadow-blue-200 active:scale-95 cursor-pointer"
				>
					<Plus class="w-5 h-5" />
					Add Transaction
				</button>
			</div>
		</div>

		<!-- FILTER -->
		<div class="bg-white p-4 rounded-2xl shadow-sm border border-slate-200 flex flex-col md:flex-row gap-4 items-center">
			<div class="relative flex-1 w-full">
				<Search class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-slate-400" />
				<input
					v-model="searchQuery"
					type="text"
					placeholder="Search transactions..."
					class="w-full pl-12 pr-4 py-3 bg-slate-50 border-none rounded-xl focus:ring-2 focus:ring-blue-500 transition-all"
				/>
			</div>

			<div class="flex items-center gap-2 w-full md:w-auto">
				<Filter class="w-5 h-5 text-slate-400" />
				<select
					v-model="typeFilter"
					class="bg-slate-50 border-none rounded-xl px-4 py-3 focus:ring-2 focus:ring-blue-500 transition-all font-medium text-slate-700 min-w-37.5 max-sm:w-full cursor-pointer"
				>
					<option value="all">All Types</option>
					<option value="income">Income</option>
					<option value="expense">Expense</option>
					<option value="liability">Liability</option>
				</select>
			</div>
		</div>

		<!-- TABLE -->
		<div class="bg-white rounded-2xl shadow-sm border border-slate-200">
			<div class="overflow-x-auto">
				<table class="w-full text-left">
					<thead class="bg-slate-50 text-slate-500 text-xs uppercase font-bold">
						<tr>
							<th class="px-6 py-4">Date</th>
							<th class="px-6 py-4">Description</th>
							<th class="px-6 py-4">Category</th>
							<th class="px-6 py-4 text-right">Amount</th>
							<th v-if="auth.user?.user_type === 'admin'" class="px-6 py-4 text-center">Actions</th>
						</tr>
					</thead>

					<tbody class="divide-y divide-slate-100">
						<tr v-if="filteredTransactions.length === 0">
							<td colspan="5" class="px-6 py-12 text-center text-slate-400 italic">
								No transactions found.
							</td>
						</tr>

						<tr
							v-else
							v-for="t in filteredTransactions"
							:key="t.id"
							class="hover:bg-slate-50 transition-colors group"
						>
							<td class="px-6 py-4 text-sm text-slate-600">
								<span class="font-medium text-slate-800">
									{{ format(parseISO(t.transaction_date), 'MMM dd, yyyy') }}
								</span>
							</td>

							<td class="px-6 py-4">
								<p class="text-sm font-medium text-slate-900">{{ t.description }}</p>
							</td>

							<td class="px-6 py-4">
								<span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-slate-100 text-slate-600">
									{{ categories.find(c => c.id == t.category_id)?.name || 'Uncategorized' }}
								</span>
							</td>

							<td class="px-6 py-4 text-right">
								<span class="text-sm font-bold text-slate-900">
									{{ formatCurrency(t.amount) }}
								</span>
							</td>

							<td v-if="auth.user?.user_type === 'admin'" class="px-6 py-4">
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

		<!-- PAGINATION -->
		<div class="flex gap-2">
			<button
				v-for="link in pagination.links"
				:key="link.label"
				v-html="link.label"
				@click="goToPage(link.url)"
				class="px-3 py-1 border"
				:class="{ 'bg-blue-500 text-white': link.active }"
			/>
		</div>

		<!-- MODAL -->
		<div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-sm">
			<div class="bg-white w-full max-w-lg rounded-2xl shadow-2xl p-6 space-y-4">
				<input v-model="formData.description" placeholder="Description" class="w-full px-4 py-3 bg-slate-50 rounded-xl" />
				<input v-model.number="formData.amount" type="number" class="w-full px-4 py-3 bg-slate-50 rounded-xl" />

				<select v-model="formData.category_id" class="w-full px-4 py-3 bg-slate-50 rounded-xl">
					<option v-for="c in filteredCategories" :key="c.id" :value="c.id">
						{{ c.name }}
					</option>
				</select>

				<div class="flex gap-2">
					<button @click="closeModal" class="flex-1 bg-slate-100 py-3 rounded-xl">Cancel</button>
					<button @click="handleSubmit" class="flex-1 bg-blue-600 text-white py-3 rounded-xl">Save</button>
				</div>
			</div>
		</div>

		<!-- DELETE CONFIRM -->
		<div v-if="confirmDelete.show" class="fixed inset-0 bg-slate-900/50 flex items-center justify-center">
			<div class="bg-white p-6 rounded-xl">
				<p>Delete {{ confirmDelete.name }}?</p>
				<button @click="deleteTransaction">Yes</button>
				<button @click="confirmDelete.show = false">Cancel</button>
			</div>
		</div>

		<!-- TRASH -->
		<div v-if="showTrashModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-sm">
			<div class="bg-white w-full max-w-2xl rounded-2xl shadow-2xl p-6">
				<h3 class="text-xl font-bold mb-4">Trash Bin</h3>

				<div v-for="t in trashed" :key="t.id" class="flex justify-between border-b py-2">
					<span>{{ t.description }}</span>

					<div class="flex gap-2">
						<button @click="restoreTransaction(t.id)">Restore</button>
						<button @click="deletePermanent(t.id)">Delete</button>
					</div>
				</div>

				<button @click="showTrashModal = false" class="mt-4">Close</button>
			</div>
		</div>

	</div>
</template>