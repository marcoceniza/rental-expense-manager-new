<script setup lang="ts">
import AuthLayout from '@/layouts/auth/AuthSimpleLayout.vue';
import { Notyf } from 'notyf'
import 'notyf/notyf.min.css'
import { SharedData, FlashProps } from '@/types'
import { usePage } from '@inertiajs/vue3'
import { watch } from 'vue'

defineProps<{
    title?: string;
    description?: string;
}>();

const page = usePage<SharedData>()
const notyf = new Notyf({
    duration: 3000,
    position: {
        x: 'right',
        y: 'top',
    },
})

watch(
    () => page.props.flash,
    (flash: FlashProps) => {
        if (flash.success) notyf.success(flash.success)
        if (flash.error) notyf.error(flash.error)
    },
    { deep: true, immediate: true }
)
</script>

<template>
    <AuthLayout :title="title" :description="description">
        <slot />
    </AuthLayout>
</template>
