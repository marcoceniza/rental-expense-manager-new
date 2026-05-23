<script setup lang="ts">
import { X } from 'lucide-vue-next';
import BaseButton from './base/BaseButton.vue';

interface Category {
    id: number;
    name: string;
    type: string;
}

interface FormData {
    transaction_date: string;
    type: string;
    category_id: string | number;
    description: string;
    amount: number;
    remarks: string;
}

defineProps<{
    isOpen: boolean;
    editingId: number | null;
    form: FormData;
    categoryTypes: string[];
    filteredCategories: Category[];
    loading: boolean;
}>();

const emit = defineEmits(['update:isOpen', 'submit']);

const close = () => emit('update:isOpen', false);
const handleSubmit = () => emit('submit');
</script>

<template>
    <Transition name="fade">
        <div
            v-if="isOpen"
            class="fixed inset-0 top-[-32px] z-50 flex min-h-screen items-start justify-center overflow-y-auto bg-slate-900/50 px-4 py-10 backdrop-blur-sm sm:items-center sm:p-4"
        >
            <Transition name="scale">
                <div class="max-h-[calc(100vh-4rem)] w-full max-w-lg overflow-y-auto rounded-2xl bg-white shadow-2xl">
                    <div class="flex justify-between border-b border-slate-100 bg-slate-50 p-6">
                        <h3 class="text-xl font-bold text-slate-900">
                            {{ editingId ? 'Edit Charity Transaction' : 'New Charity Transaction' }}
                        </h3>

                        <button @click="close" class="cursor-pointer rounded-lg p-2 transition-colors hover:bg-white">
                            <X class="h-5 w-5 text-slate-400" />
                        </button>
                    </div>

                    <form @submit.prevent="handleSubmit" class="space-y-4 p-6 text-slate-900">
                        <div class="grid grid-cols-2 gap-4">
                            <div class="space-y-1.5">
                                <label class="text-xs font-bold uppercase text-slate-500"> Date </label>

                                <input
                                    v-model="form.transaction_date"
                                    type="date"
                                    required
                                    class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                />
                            </div>

                            <div class="space-y-1.5">
                                <label class="text-xs font-bold uppercase text-slate-500"> Type </label>

                                <select
                                    v-model="form.type"
                                    class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                >
                                    <option v-for="type in categoryTypes" :key="type" :value="type">
                                        {{ type }}
                                    </option>
                                </select>
                            </div>
                        </div>

                        <div class="space-y-1.5">
                            <label class="text-xs font-bold uppercase text-slate-500"> Category </label>

                            <select
                                v-model="form.category_id"
                                required
                                class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-500"
                            >
                                <option value="" disabled>Select a category</option>

                                <option v-for="c in filteredCategories" :key="c.id" :value="c.id">
                                    {{ c.name }}
                                </option>
                            </select>
                        </div>

                        <div class="space-y-1.5">
                            <label class="text-xs font-bold uppercase text-slate-500"> Description </label>

                            <input
                                v-model="form.description"
                                type="text"
                                placeholder="e.g., Tuition Assistance Payment"
                                class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-500"
                            />
                        </div>

                        <div class="space-y-1.5">
                            <label class="text-xs font-bold uppercase text-slate-500"> Amount (PHP) </label>

                            <input
                                v-model.number="form.amount"
                                type="number"
                                step="0.01"
                                class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-lg font-bold focus:outline-none focus:ring-2 focus:ring-blue-500"
                            />
                        </div>

                        <div class="space-y-1.5">
                            <label class="text-xs font-bold uppercase text-slate-500"> Remarks </label>

                            <textarea
                                v-model="form.remarks"
                                placeholder="Add any additional notes..."
                                rows="2"
                                class="w-full resize-none rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-500"
                            />
                        </div>

                        <div class="flex gap-3 pt-4">
                            <BaseButton type="button" fullWidth variant="secondary" @click="close"> Cancel </BaseButton>

                            <BaseButton type="submit" fullWidth variant="primary" :disabled="loading" :loading="loading"> Update </BaseButton>
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
    transition:
        transform 0.2s ease,
        opacity 0.2s ease;
}

.scale-enter-from,
.scale-leave-to {
    transform: scale(0.9);
    opacity: 0;
}
</style>
