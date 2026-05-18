<script setup>
import { CalendarClock } from 'lucide-vue-next';
import BaseButton from './base/BaseButton.vue';

const props = defineProps({
    show: Boolean,
    label: String,
});

const emit = defineEmits(['confirm', 'cancel']);
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
            class="fixed top-[-32px] inset-0 bg-black/60 backdrop-blur-sm z-50 flex items-center justify-center p-4"
            @click.self="$emit('cancel')"
        >
            <!-- Modal with scale animation -->
            <Transition
                enter-active-class="transition-all duration-200 ease-out"
                leave-active-class="transition-all duration-150 ease-in"
                enter-from-class="opacity-0 scale-95"
                leave-to-class="opacity-0 scale-95"
            >
                <div 
                    v-if="show"
                    class="bg-white rounded-2xl shadow-2xl max-w-md w-full overflow-hidden"
                >
                    <!-- Gradient Header -->
                    <div class="bg-gradient-to-br from-blue-500 to-indigo-600 px-6 pt-6 pb-8">
                        <div class="flex items-center gap-3">
                            <div class="bg-white/20 backdrop-blur-sm p-3 rounded-xl">
                                <CalendarClock class="w-6 h-6 text-white" />
                            </div>
                            <h3 class="text-xl font-bold text-white">
                                Change Reporting Period
                            </h3>
                        </div>
                    </div>

                    <!-- Content -->
                    <div class="px-6 py-6 space-y-4">
                        <p class="text-slate-600 text-base leading-relaxed">
                            You're about to change the reporting period to:
                        </p>
                        
                        <div class="bg-blue-50 border border-blue-200 rounded-xl px-4 py-3">
                            <p class="text-blue-900 font-semibold text-center">
                                {{ label }}
                            </p>
                        </div>

                        <p class="text-sm text-slate-500">
                            This will update all data views to reflect the selected period.
                        </p>
                    </div>

                    <!-- Actions -->
                    <div class="bg-slate-50 px-6 py-4 flex justify-end gap-3">
                        <BaseButton 
                            type="button" 
                            variant="secondary" 
                            size="sm" 
                            @click="$emit('cancel')"
                            class="shadow-lg shadow-slate-200 hover:shadow-xl hover:shadow-slate-300 transition-all"
                        >
                            Cancel
                        </BaseButton>

                        <BaseButton
                            type="submit"
                            variant="primary"
                            size="sm"
                            @click="$emit('confirm')"
                            class="shadow-lg shadow-blue-200 hover:shadow-xl hover:shadow-blue-300 transition-all"
                        >
                            Proceed
                        </BaseButton>
                    </div>
                </div>
            </Transition>
        </div>
    </Transition>
</template>