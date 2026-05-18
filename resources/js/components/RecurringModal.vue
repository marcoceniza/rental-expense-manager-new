<script setup>
import { computed } from 'vue'
import { X } from 'lucide-vue-next'
import BaseButton from '@/components/base/BaseButton.vue'

const props = defineProps({
    isOpen: { type: Boolean, required: true },
    editingId: { type: [Number, null], default: null },
    formData: { type: Object, required: true },
    categories: { type: Array, required: true },
    loading: { type: Boolean, default: false },
    errors: { type: Object, default: () => ({}) },
})

const selectedCategory = computed(() => {
    return props.categories.find((category) => category.id === props.formData.category_id)
})

const descriptionOptions = computed(() => {
    return selectedCategory.value?.transaction_descriptions ?? []
})

const emit = defineEmits(['update:isOpen', 'submit', 'close'])

const close = () => {
    emit('update:isOpen', false)
    emit('close')
}

const handleSubmit = () => emit('submit')
</script>

<template>
    <Transition name="fade">
        <div
            v-if="isOpen"
            class="fixed top-[-32px] inset-0 z-50 flex min-h-screen items-start sm:items-center justify-center px-4 py-10 sm:p-4 bg-slate-900/50 backdrop-blur-sm overflow-y-auto"
        >
            <Transition name="scale">
                <div class="bg-white w-full max-w-lg rounded-2xl shadow-2xl overflow-hidden animate-in fade-in zoom-in duration-200">

                    <div class="p-6 border-b border-slate-100 flex items-center justify-between bg-slate-50">
                        <h3 class="text-xl font-bold text-slate-900">
                            {{ editingId ? 'Edit Recurring Entry' : 'New Recurring Entry' }}
                        </h3>

                        <button @click="close" class="p-2 hover:bg-white rounded-lg transition-colors cursor-pointer">
                            <X class="w-5 h-5 text-slate-400" />
                        </button>
                    </div>

                    <form @submit.prevent="handleSubmit" class="p-6 space-y-4">
                        <div class="space-y-1.5">
                            <label class="text-xs font-bold text-slate-500 uppercase">Category</label>
                            <select
                                v-model="formData.category_id"
                                required
                                :class="['w-full px-4 py-3 bg-slate-50 text-slate-900 border border-slate-200 rounded-xl transition-all font-medium', errors?.category_id?.length ? 'ring-2 ring-red-500' : 'focus:ring-blue-500']"
                            >
                                <option value="" disabled>Select a category</option>
                                <option v-for="c in categories" :key="c.id" :value="c.id">
                                    {{ c.name }} ({{ c.type }})
                                </option>
                            </select>
                            <p v-if="errors?.category_id?.length" class="text-xs text-red-500">
                                {{ errors.category_id[0] }}
                            </p>
                        </div>

                        <div class="space-y-1.5">
                            <label class="text-xs font-bold text-slate-500 uppercase">Description</label>

                            <template v-if="descriptionOptions.length">
                                <select
                                    v-model="formData.description"
                                    required
                                    :class="['w-full px-4 py-3 bg-slate-50 text-slate-900 border border-slate-200 rounded-xl transition-all font-medium', errors?.description?.length ? 'ring-2 ring-red-500' : 'focus:ring-blue-500']"
                                >
                                    <option value="" disabled>Select a saved description</option>
                                    <option
                                        v-for="option in descriptionOptions"
                                        :key="option.description"
                                        :value="option.description"
                                    >
                                        {{ option.description }}
                                    </option>
                                </select>
                            </template>

                            <template v-else>
                                <input
                                    v-model="formData.description"
                                    type="text"
                                    required
                                    placeholder="e.g., Monthly Rent"
                                    :class="['w-full px-4 py-3 bg-slate-50 text-slate-900 border border-slate-200 rounded-xl focus:ring-2 transition-all font-medium', errors?.description?.length ? 'ring-2 ring-red-500' : 'focus:ring-blue-500']"
                                />
                            </template>

                            <p v-if="errors?.description?.length" class="text-xs text-red-500">
                                {{ errors.description[0] }}
                            </p>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div class="space-y-1.5">
                                <label class="text-xs font-bold text-slate-500 uppercase">Amount (PHP)</label>
                                <input
                                    v-model.number="formData.amount"
                                    type="number"
                                    required
                                    :class="['w-full px-4 py-3 bg-slate-50 text-slate-900 border border-slate-200 rounded-xl transition-all font-bold text-lg', errors?.amount?.length ? 'ring-2 ring-red-500' : 'focus:ring-blue-500']"
                                />
                                <p v-if="errors?.amount?.length" class="text-xs text-red-500">
                                    {{ errors.amount[0] }}
                                </p>
                            </div>

                            <div class="space-y-1.5">
                                <label class="text-xs font-bold text-slate-500 uppercase">Frequency</label>
                                <select
                                    v-model="formData.frequency"
                                    disabled
                                    class="w-full px-4 py-3 bg-slate-100 text-slate-900 border border-slate-200 rounded-xl font-medium"
                                >
                                    <option value="monthly">Monthly</option>
                                </select>
                            </div>
                        </div>

                        <div class="space-y-1.5">
                            <label class="text-xs font-bold text-slate-500 uppercase">Start Date</label>
                            <input
                                v-model="formData.start_date"
                                type="date"
                                required
                                :class="['w-full px-4 py-3 bg-slate-50 text-slate-900 border border-slate-200 rounded-xl transition-all font-medium', errors?.start_date?.length ? 'ring-2 ring-red-500' : 'focus:ring-blue-500']"
                            />
                            <p v-if="errors?.start_date?.length" class="text-xs text-red-500">
                                {{ errors.start_date[0] }}
                            </p>
                        </div>

                        <div class="pt-4 flex gap-3">
                            <BaseButton type="button" fullWidth variant="secondary" @click="close">
                                Cancel
                            </BaseButton>

                            <BaseButton type="submit" fullWidth variant="primary" :disabled="loading">
                                {{ loading ? (editingId ? 'Updating...' : 'Saving...') : editingId ? 'Update' : 'Save Recurring' }}
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

.scale-enter-active {
    transition: transform 0.2s ease, opacity 0.2s ease;
}

.scale-enter-from {
    transform: scale(0.9);
    opacity: 0;
}

.scale-leave-to {
    transform: scale(0.9);
    opacity: 0;
}
</style>
