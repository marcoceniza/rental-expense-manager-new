<script setup lang="ts">
import BaseButton from '@/components/base/BaseButton.vue';
import { AlertTriangle } from 'lucide-vue-next';

defineProps({
    isOpen: { type: Boolean, required: true },
    title: { type: String, default: '' },
    message: { type: String, required: true },
    loading: { type: Boolean, default: false },
    actionName: { type: String, default: '' },
});

const emit = defineEmits(['confirm', 'close']);

const close = () => emit('close');
</script>

<template>
    <!-- Backdrop with smooth fade-in -->
    <Transition
        enter-active-class="transition-opacity duration-200 ease-out"
        leave-active-class="transition-opacity duration-150 ease-in"
        enter-from-class="opacity-0"
        leave-to-class="opacity-0"
    >
        <div
            v-if="isOpen"
            class="fixed inset-0 top-[-32px] z-50 flex items-center justify-center bg-black/60 p-4 backdrop-blur-sm"
            @click.self="close"
        >
            <!-- Modal with scale animation -->
            <Transition
                enter-active-class="transition-all duration-200 ease-out"
                leave-active-class="transition-all duration-150 ease-in"
                enter-from-class="opacity-0 scale-95"
                leave-to-class="opacity-0 scale-95"
            >
                <div v-if="isOpen" class="w-full max-w-md overflow-hidden rounded-2xl bg-white shadow-2xl">
                    <!-- Gradient Header -->
                    <div class="bg-gradient-to-br from-red-500 to-rose-600 px-6 pb-8 pt-6">
                        <div class="flex items-center gap-3">
                            <div class="rounded-xl bg-white/20 p-3 backdrop-blur-sm">
                                <AlertTriangle class="h-6 w-6 text-white" />
                            </div>
                            <h3 class="text-xl font-bold text-white">
                                {{ title || 'Confirm Deletion' }}
                            </h3>
                        </div>
                    </div>

                    <!-- Content -->
                    <div class="space-y-4 px-6 py-6">
                        <p class="text-base leading-relaxed text-slate-600">Are you sure you want to delete this {{ actionName }}</p>

                        <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3">
                            <p class="text-center font-semibold text-red-900">
                                {{ message }}
                            </p>
                        </div>

                        <div class="flex items-start gap-3 rounded-lg border border-amber-200 bg-amber-50 px-4 py-3">
                            <AlertTriangle class="mt-0.5 h-5 w-5 flex-shrink-0 text-amber-600" />
                            <p class="text-sm font-medium text-amber-900">This action cannot be undone.</p>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="flex justify-end gap-3 bg-slate-50 px-6 py-4">
                        <BaseButton
                            variant="secondary"
                            size="sm"
                            @click="close"
                            class="shadow-lg shadow-slate-200 transition-all hover:shadow-xl hover:shadow-slate-300"
                        >
                            Cancel
                        </BaseButton>

                        <BaseButton
                            variant="danger"
                            :loading="loading"
                            :disabled="loading"
                            size="sm"
                            @click="$emit('confirm')"
                            class="shadow-lg shadow-red-200 transition-all hover:shadow-xl hover:shadow-red-300"
                        >
                            Yes, Delete
                        </BaseButton>
                    </div>
                </div>
            </Transition>
        </div>
    </Transition>
</template>
