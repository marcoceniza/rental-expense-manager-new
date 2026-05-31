<script setup lang="ts">
import { ref, onMounted, onUnmounted } from 'vue';
import logoDark from '../images/logo.png';
import logoLight from '../images/logo2.png';

defineOptions({
    inheritAttrs: false,
});

const currentLogo = ref<string>(logoLight);

const updateLogo = () => {
    const isDark = document.documentElement.classList.contains('dark');
    // Use the light/white logo when the theme is dark so it shows on dark backgrounds
    currentLogo.value = isDark ? logoLight : logoDark;
};

let observer: MutationObserver | null = null;

onMounted(() => {
    updateLogo();

    // Watch for class changes on <html> so we react to theme toggles
    observer = new MutationObserver(() => updateLogo());
    observer.observe(document.documentElement, { attributes: true, attributeFilter: ['class'] });
});

onUnmounted(() => {
    if (observer) observer.disconnect();
});
</script>

<template>
    <figure class="w-full"><img :src="currentLogo" alt="Logo" /></figure>
</template>
