<script setup lang="ts">
import { CalendarClock } from 'lucide-vue-next';
import BaseButton from './base/BaseButton.vue';

defineProps({
    show: Boolean,
    label: String,
});

defineEmits(['confirm', 'cancel']);
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
            v-if="show"
            class="fixed inset-0 top-[-32px] z-50 flex items-center justify-center bg-black/60 p-4 backdrop-blur-sm"
            @click.self="$emit('cancel')"
        >
            <!-- Modal with scale animation -->
            <Transition
                enter-active-class="transition-all duration-200 ease-out"
                leave-active-class="transition-all duration-150 ease-in"
                enter-from-class="opacity-0 scale-95"
                leave-to-class="opacity-0 scale-95"
            >
                <div v-if="show" class="w-full max-w-md overflow-hidden rounded-2xl bg-white shadow-2xl">
                    <!-- Gradient Header -->
                    <div class="bg-gradient-to-br from-blue-500 to-indigo-600 px-6 pb-8 pt-6">
                        <div class="flex items-center gap-3">
                            <div class="rounded-xl bg-white/20 p-3 backdrop-blur-sm">
                                <CalendarClock class="h-6 w-6 text-white" />
                            </div>
                            <h3 class="text-xl font-bold text-white">Change Reporting Period</h3>
                        </div>
                    </div>

                    <!-- Content -->
                    <div class="space-y-4 px-6 py-6">
                        <p class="text-base leading-relaxed text-slate-600">You're about to change the reporting period to:</p>

                        <div class="rounded-xl border border-blue-200 bg-blue-50 px-4 py-3">
                            <p class="text-center font-semibold text-blue-900">
                                {{ label }}
                            </p>
                        </div>

                        <p class="text-sm text-slate-500">This will update all data views to reflect the selected period.</p>
                    </div>

                    <!-- Actions -->
                    <div class="flex justify-end gap-3 bg-slate-50 px-6 py-4">
                        <BaseButton
                            type="button"
                            variant="secondary"
                            size="sm"
                            @click="$emit('cancel')"
                            class="shadow-lg shadow-slate-200 transition-all hover:shadow-xl hover:shadow-slate-300"
                        >
                            Cancel
                        </BaseButton>

                        <BaseButton
                            type="submit"
                            variant="primary"
                            size="sm"
                            @click="$emit('confirm')"
                            class="shadow-lg shadow-blue-200 transition-all hover:shadow-xl hover:shadow-blue-300"
                        >
                            Proceed
                        </BaseButton>
                    </div>
                </div>
            </Transition>
        </div>
    </Transition>
</template>
