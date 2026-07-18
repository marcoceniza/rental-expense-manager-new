<script setup lang="ts">
import { LoaderCircle, X } from 'lucide-vue-next';
import BaseButton from './base/BaseButton.vue';

interface CategoryType {
    value: string;
    label: string;
}

interface FormData {
    name: string;
    type: string;
    is_tuition: boolean;
    is_other: boolean;
}

interface Category {
    id: number;
    name: string;
    type: string;
    is_tuition: boolean;
    is_other: boolean;
    transactions_count?: number;
}

interface Props {
    isOpen: boolean;
    editingId?: number | null;
    formData: FormData;
    categoryTypes: CategoryType[];
    errors?: Record<string, string[]>;
    loading?: boolean;
    selectedCategory?: Category | null;
}

defineProps<Props>();

const emit = defineEmits<{
    'update:isOpen': [value: boolean];
    submit: [];
}>();

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
                <div v-if="isOpen" class="max-h-[calc(100vh-4rem)] w-full max-w-md overflow-y-auto rounded-2xl bg-white shadow-2xl">
                    <div class="flex justify-between border-b border-slate-100 p-6">
                        <h3 class="text-xl font-bold text-slate-900">
                            {{ editingId ? 'Edit Category' : 'New Category' }}
                        </h3>
                        <button @click="close" class="cursor-pointer rounded-lg p-2 hover:bg-white">
                            <X class="h-5 w-5 text-slate-400" />
                        </button>
                    </div>

                    <form @submit.prevent="handleSubmit" class="space-y-4 p-6">
                        <div class="space-y-1.5">
                            <label class="text-xs font-bold uppercase text-slate-500">Category Name</label>
                            <input
                                v-model="formData.name"
                                type="text"
                                placeholder="e.g., Maintenance, Rent, etc."
                                class="w-full rounded-xl border bg-slate-50 px-4 py-3 text-slate-900"
                                :class="
                                    errors?.name?.[0]
                                        ? 'border-red-500 focus:ring-2 focus:ring-red-500'
                                        : 'border-slate-200 focus:ring-2 focus:ring-blue-500'
                                "
                            />
                            <p v-if="errors?.name?.length" class="text-xs text-red-500">
                                {{ errors.name[0] }}
                            </p>
                        </div>

                        <div class="space-y-1.5">
                            <label class="text-xs font-bold uppercase text-slate-500">Type</label>
                            <select
                                v-model="formData.type"
                                class="w-full rounded-xl bg-slate-50 px-4 py-3 text-slate-900"
                                :class="{ 'pointer-none cursor-not-allowed opacity-50': (selectedCategory?.transactions_count ?? 0) > 0 }"
                                :disabled="(selectedCategory?.transactions_count ?? 0) > 0"
                                :title="(selectedCategory?.transactions_count ?? 0) > 0 ? 'Category is in use and cannot be deleted' : ''"
                            >
                                <option v-for="type in categoryTypes" :key="type.value" :value="type.value">
                                    {{ type.label }}
                                </option>
                            </select>
                        </div>

                        <div class="flex items-center gap-3 rounded-xl bg-slate-50 p-4">
                            <input type="checkbox" v-model="formData.is_tuition" class="h-6 w-6 cursor-pointer" />
                            <div>
                                <p class="text-sm font-bold text-slate-700">Tuition Related</p>
                                <p class="text-xs text-slate-500">Mark this category for Charity/Tuition tracking.</p>
                            </div>
                        </div>

                        <div class="flex items-center gap-3 rounded-xl bg-slate-50 p-4">
                            <input type="checkbox" v-model="formData.is_other" class="h-6 w-6 cursor-pointer" />
                            <div>
                                <p class="text-sm font-bold text-slate-700">Other</p>
                                <p class="text-xs text-slate-500">Mark this category as other.</p>
                            </div>
                        </div>

                        <div class="flex gap-3 pt-4">
                            <BaseButton type="button" fullWidth variant="secondary" @click="close"> Cancel </BaseButton>

                            <BaseButton type="submit" fullWidth variant="primary" :disabled="loading">
                                <LoaderCircle v-if="loading" class="h-4 w-4 animate-spin" />
                                Save
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
    transition:
        transform 0.2s ease,
        opacity 0.2s ease;
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
