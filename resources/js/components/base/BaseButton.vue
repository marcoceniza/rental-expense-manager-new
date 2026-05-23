<script setup lang="ts">
import { LoaderCircle } from 'lucide-vue-next';
import { computed } from 'vue';

type ButtonType = 'button' | 'submit' | 'reset';
type ButtonVariant = 'primary' | 'success' | 'secondary' | 'warning' | 'danger';
type ButtonSize = 'sm' | 'md' | 'lg';

interface Props {
    type?: ButtonType;
    variant?: ButtonVariant;
    disabled?: boolean;
    loading?: boolean;
    fullWidth?: boolean;
    rounded?: boolean;
    size?: ButtonSize;
    title?: string;
}

const props = withDefaults(defineProps<Props>(), {
    type: 'button',
    variant: 'primary',
    disabled: false,
    loading: false,
    fullWidth: false,
    rounded: true,
    size: 'md',
    title: '',
});

const emit = defineEmits<{
    click: [event: MouseEvent];
}>();

const buttonClasses = computed(() => {
    const isInactive = props.disabled || props.loading;

    const base = [
        'font-semibold focus:outline-none focus:ring-2 focus:ring-offset-1 transition inline-flex items-center justify-center gap-2',
        isInactive ? 'cursor-not-allowed opacity-60' : 'cursor-pointer hover:opacity-90',
        props.rounded ? 'rounded-lg' : '',
        props.fullWidth ? 'w-full' : '',
    ];

    const sizes: Record<ButtonSize, string> = {
        sm: 'px-3 py-2 text-sm',
        md: 'px-4 py-3 text-base',
        lg: 'px-5 py-4 text-lg',
    };

    base.push(sizes[props.size]);

    const variants: Record<ButtonVariant, string> = {
        primary: 'bg-blue-600 text-white',
        success: 'bg-green-600 text-white',
        secondary: 'bg-gray-600 text-white',
        warning: 'bg-yellow-600 text-white',
        danger: 'bg-red-600 text-white',
    };

    base.push(variants[props.variant]);

    return base.join(' ');
});

const handleClick = (event: MouseEvent) => {
    if (!props.disabled && !props.loading) {
        emit('click', event);
    }
};
</script>

<template>
    <button :type="type" :disabled="disabled || loading" :class="buttonClasses" :title="title" @click="handleClick" v-bind="$attrs">
        <LoaderCircle v-if="loading" class="h-4 w-4 animate-spin" />
        <slot />
    </button>
</template>
