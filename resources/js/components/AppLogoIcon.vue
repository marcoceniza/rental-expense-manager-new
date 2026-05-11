<script setup lang="ts">
import type { HTMLAttributes } from 'vue';
import logo2 from '../images/logo2.png'

defineOptions({
    inheritAttrs: false,
});

interface Props {
    className?: HTMLAttributes['class'];
}

defineProps<Props>();
</script>

<template>
    <figure class="w-full"><img :src="logo2" alt="Logo"></figure>
</template>
