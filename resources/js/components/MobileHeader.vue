<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import { Menu, X } from 'lucide-vue-next';
import { computed, inject, type Ref } from 'vue';

const page = usePage();

const isAuthPage = computed(() =>
    ['Login', 'Register', 'ForgotPassword', 'ResetPassword', 'VerifyEmail', 'ConfirmPassword'].includes(page.component),
);

const isMenuOpen = inject<Ref<boolean>>('isMenuOpen');
const toggleMenu = inject<() => void>('toggleMenu');
</script>

<template>
    <button v-if="!isAuthPage" class="absolute right-4 top-4 z-50 rounded-lg bg-slate-900 p-2 text-white md:hidden" @click="toggleMenu">
        <Menu v-if="!isMenuOpen" class="h-6 w-6" />
        <X v-else class="h-6 w-6" />
    </button>
</template>
