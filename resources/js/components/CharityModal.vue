<script setup lang="ts">
import { X, Check, LoaderCircle } from 'lucide-vue-next'
import BaseButton from './base/BaseButton.vue'

interface Category {
    id: number
    name: string
    type: string
}

interface FormData {
    transaction_date: string
    type: string
    category_id: string | number
    description: string
    amount: number
    remarks: string
}

defineProps<{
    isOpen: boolean
    editingId: number | null
    form: FormData
    categoryTypes: string[]
    filteredCategories: Category[]
    loading: boolean
}>()

const emit = defineEmits([
    'update:isOpen',
    'submit',
])

const close = () => emit('update:isOpen', false)

const handleSubmit = () => emit('submit')
</script>

<template>
    <Transition name="fade">
        <div v-if="isOpen"
            class="fixed top-[-32px] inset-0 z-50 flex min-h-screen items-start sm:items-center justify-center px-4 py-10 sm:p-4 bg-slate-900/50 backdrop-blur-sm overflow-y-auto">
            <Transition name="scale">
                <div class="bg-white w-full max-w-lg rounded-2xl shadow-2xl overflow-y-auto max-h-[calc(100vh-4rem)]">
                    <div class="p-6 border-b border-slate-100 flex justify-between bg-slate-50">
                        <h3 class="text-xl font-bold text-slate-900">
                            {{ editingId ? 'Edit Charity Transaction' : 'New Charity Transaction' }}
                        </h3>

                        <button @click="close" class="p-2 hover:bg-white rounded-lg transition-colors cursor-pointer">
                            <X class="w-5 h-5 text-slate-400" />
                        </button>
                    </div>

                    <form @submit.prevent="handleSubmit" class="p-6 space-y-4 text-slate-900">
                        <div class="grid grid-cols-2 gap-4">
                            <div class="space-y-1.5">
                                <label class="text-xs font-bold text-slate-500 uppercase">
                                    Date
                                </label>

                                <input v-model="form.transaction_date" type="date" required
                                    class="w-full px-4 py-3 bg-slate-50 rounded-xl border border-slate-200 focus:ring-2 focus:ring-blue-500 focus:outline-none" />
                            </div>

                            <div class="space-y-1.5">
                                <label class="text-xs font-bold text-slate-500 uppercase">
                                    Type
                                </label>

                                <select v-model="form.type"
                                    class="w-full px-4 py-3 bg-slate-50 rounded-xl border border-slate-200 focus:ring-2 focus:ring-blue-500 focus:outline-none">
                                    <option v-for="type in categoryTypes" :key="type" :value="type">
                                        {{ type }}
                                    </option>
                                </select>
                            </div>
                        </div>

                        <div class="space-y-1.5">
                            <label class="text-xs font-bold text-slate-500 uppercase">
                                Category
                            </label>

                            <select v-model="form.category_id" required
                                class="w-full px-4 py-3 bg-slate-50 rounded-xl border border-slate-200 focus:ring-2 focus:ring-blue-500 focus:outline-none">
                                <option value="" disabled>
                                    Select a category
                                </option>

                                <option v-for="c in filteredCategories" :key="c.id" :value="c.id">
                                    {{ c.name }}
                                </option>
                            </select>
                        </div>

                        <div class="space-y-1.5">
                            <label class="text-xs font-bold text-slate-500 uppercase">
                                Description
                            </label>

                            <input v-model="form.description" type="text"
                                placeholder="e.g., Tuition Assistance Payment"
                                class="w-full px-4 py-3 bg-slate-50 rounded-xl border border-slate-200 focus:ring-2 focus:ring-blue-500 focus:outline-none" />
                        </div>

                        <div class="space-y-1.5">
                            <label class="text-xs font-bold text-slate-500 uppercase">
                                Amount (PHP)
                            </label>

                            <input v-model.number="form.amount" type="number" step="0.01"
                                class="w-full px-4 py-3 bg-slate-50 rounded-xl border border-slate-200 focus:ring-2 focus:ring-blue-500 focus:outline-none font-bold text-lg" />
                        </div>

                        <div class="space-y-1.5">
                            <label class="text-xs font-bold text-slate-500 uppercase">
                                Remarks
                            </label>

                            <textarea v-model="form.remarks" placeholder="Add any additional notes..." rows="2"
                                class="w-full px-4 py-3 bg-slate-50 rounded-xl border border-slate-200 focus:ring-2 focus:ring-blue-500 focus:outline-none resize-none" />
                        </div>

                        <div class="pt-4 flex gap-3">
                            <BaseButton type="button" fullWidth variant="secondary" @click="close">
                                Cancel
                            </BaseButton>

                            <BaseButton type="submit" fullWidth variant="primary" :disabled="loading" :loading="loading">

                                Update
                            </BaseButton>
                        </div>
                    </form>
                </div>
            </Transition>
        </div>
    </Transition>
</template>

<style scoped>
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.2s;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}

.scale-enter-active,
.scale-leave-active {
    transition: transform 0.2s ease, opacity 0.2s ease;
}

.scale-enter-from,
.scale-leave-to {
    transform: scale(0.9);
    opacity: 0;
}
</style>