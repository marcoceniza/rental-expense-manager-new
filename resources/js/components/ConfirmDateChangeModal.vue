<script setup>
import BaseButton from './base/BaseButton.vue';

const props = defineProps({
    show: Boolean,
    label: String,
});

const emit = defineEmits(['confirm', 'cancel']);
</script>

<template>
    <div v-if="show" class="fixed inset-0 flex items-center justify-center bg-black/50 z-50">
        <div class="bg-white p-6 rounded-xl shadow-lg w-85 max-w-full">
            <p class="text-lg text-slate-700 mb-7">
                You're about to change the reporting period to: <strong>{{ label }}</strong>
            </p>

            <div class="flex justify-end gap-2">
                <BaseButton type="button" variant="secondary" size="sm" @click="$emit('cancel')">
                    Cancel
                </BaseButton>

                <BaseButton
                    type="submit"
                    variant="primary"
                    size="sm"
                    @click="$emit('confirm')"
                >
                    Proceed
                </BaseButton>
            </div>
        </div>
    </div>
</template>