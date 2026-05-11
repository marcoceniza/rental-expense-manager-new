<script setup>
import { computed } from 'vue'
import { LoaderCircle } from 'lucide-vue-next'

const props = defineProps({
    type: { type: String, default: 'button' },
    variant: { type: String, default: 'primary' },
    disabled: { type: Boolean, default: false },
    loading: { type: Boolean, default: false },
    fullWidth: { type: Boolean, default: false },
    rounded: { type: Boolean, default: true },
    size: { type: String, default: 'md' },
    title: { type: String, default: '' },
})

const emit = defineEmits(['click'])

const buttonClasses = computed(() => {
    const isInactive = props.disabled || props.loading

    const base = [
        'font-semibold focus:outline-none focus:ring-2 focus:ring-offset-1 transition inline-flex items-center justify-center gap-2',
        isInactive ? 'cursor-not-allowed opacity-60' : 'cursor-pointer hover:opacity-90',
        props.rounded ? 'rounded-lg' : '',
        props.fullWidth ? 'w-full' : '',
    ]

    const sizes = {
        sm: 'px-3 py-2 text-sm',
        md: 'px-4 py-3 text-base',
        lg: 'px-5 py-4 text-lg',
    }

    base.push(sizes[props.size] || sizes.md)

    const variants = {
        primary: 'bg-blue-600 text-white',
        success: 'bg-green-600 text-white',
        secondary: 'bg-gray-600 text-white',
        warning: 'bg-yellow-600 text-white',
        danger: 'bg-red-600 text-white',
    }

    base.push(variants[props.variant] || variants.primary)

    return base.join(' ')
})

const handleClick = (event) => {
    if (!props.disabled && !props.loading) {
        emit('click', event)
    }
}
</script>

<template>
    <button
        :type="type"
        :disabled="disabled || loading"
        :class="buttonClasses"
        :title="title"
        @click="handleClick"
        v-bind="$attrs"
    >
        <LoaderCircle v-if="loading" class="w-4 h-4 animate-spin"/>
        <slot />
    </button>
</template>