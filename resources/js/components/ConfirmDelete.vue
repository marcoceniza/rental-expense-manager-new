<script setup lang="ts">
import { AlertTriangle } from 'lucide-vue-next'
import BaseButton from '@/components/base/BaseButton.vue'

const props = defineProps({
    isOpen: { type: Boolean, required: true },
    title: { type: String, default: '' },
    message: { type: String, required: true },
    loading: { type: Boolean, default: false },
    actionName: { type: String, default: '' },
})

const emit = defineEmits(['confirm', 'close'])

const close = () => emit('close')
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
            class="fixed top-[-32px] inset-0 bg-black/60 backdrop-blur-sm z-50 flex items-center justify-center p-4"
            @click.self="close"
        >
            <!-- Modal with scale animation -->
            <Transition
                enter-active-class="transition-all duration-200 ease-out"
                leave-active-class="transition-all duration-150 ease-in"
                enter-from-class="opacity-0 scale-95"
                leave-to-class="opacity-0 scale-95"
            >
                <div 
                    v-if="isOpen"
                    class="bg-white rounded-2xl shadow-2xl max-w-md w-full overflow-hidden"
                >
                    <!-- Gradient Header -->
                    <div class="bg-gradient-to-br from-red-500 to-rose-600 px-6 pt-6 pb-8">
                        <div class="flex items-center gap-3">
                            <div class="bg-white/20 backdrop-blur-sm p-3 rounded-xl">
                                <AlertTriangle class="w-6 h-6 text-white" />
                            </div>
                            <h3 class="text-xl font-bold text-white">
                                {{ title || 'Confirm Deletion' }}
                            </h3>
                        </div>
                    </div>

                    <!-- Content -->
                    <div class="px-6 py-6 space-y-4">
                        <p class="text-slate-600 text-base leading-relaxed">
                            Are you sure you want to delete this {{ actionName }}
                        </p>
                        
                        <div class="bg-red-50 border border-red-200 rounded-xl px-4 py-3">
                            <p class="text-red-900 font-semibold text-center">
                                {{ message }}
                            </p>
                        </div>

                        <div class="bg-amber-50 border border-amber-200 rounded-lg px-4 py-3 flex items-start gap-3">
                            <AlertTriangle class="w-5 h-5 text-amber-600 flex-shrink-0 mt-0.5" />
                            <p class="text-sm text-amber-900 font-medium">
                                This action cannot be undone.
                            </p>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="bg-slate-50 px-6 py-4 flex justify-end gap-3">
                        <BaseButton 
                            variant="secondary" 
                            size="sm" 
                            @click="close"
                            class="shadow-lg shadow-slate-200 hover:shadow-xl hover:shadow-slate-300 transition-all"
                        >
                            Cancel
                        </BaseButton>

                        <BaseButton
                            variant="danger"
                            :loading="loading"
                            :disabled="loading"
                            size="sm"
                            @click="$emit('confirm')"
                            class="shadow-lg shadow-red-200 hover:shadow-xl hover:shadow-red-300 transition-all"
                        >
                            Yes, Delete
                        </BaseButton>
                    </div>
                </div>
            </Transition>
        </div>
    </Transition>
</template>