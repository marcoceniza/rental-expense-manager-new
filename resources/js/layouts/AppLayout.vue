<script setup lang="ts">
import { FlashProps, SharedData, User } from '@/types';
import { router, usePage } from '@inertiajs/vue3';
import { Notyf } from 'notyf';
import 'notyf/notyf.min.css';
import { computed, provide, ref, watch } from 'vue';
import { route } from 'ziggy-js';
import MobileHeader from '../components/MobileHeader.vue';
import Sidebar from '../components/Sidebar.vue';

const page = usePage<SharedData>();
const notyf = new Notyf({
    duration: 6000,
    position: {
        x: 'right',
        y: 'top',
    },
});

const isAuthPage = computed(() =>
    ['Login', 'Register', 'ForgotPassword', 'ResetPassword', 'VerifyEmail', 'ConfirmPassword'].includes(page.component),
);

const isMenuOpen = ref(false);
const toggleMenu = () => {
    isMenuOpen.value = !isMenuOpen.value;
};

const user = computed<User | undefined>(() => page.props.auth?.user);
const handleLogout = () => {
    router.post(route('logout'));
};

watch(
    () => page.props.flash,
    (flash: FlashProps) => {
        if (flash.success) notyf.success(flash.success);
        if (flash.error) notyf.error(flash.error);
    },
    { deep: true, immediate: true },
);

provide('isMenuOpen', isMenuOpen);
provide('toggleMenu', toggleMenu);
provide('user', user);
provide('handleLogout', handleLogout);
</script>

<template>
    <div class="flex min-h-screen bg-slate-50">

        <div v-if="!isAuthPage" class="print:hidden">
            <Sidebar />
        </div>

        <main class="flex min-h-screen min-w-0 flex-1 flex-col">
            <div v-if="!isAuthPage" class="print:hidden">
                <MobileHeader />
            </div>

            <div class="flex-1 p-4 md:p-8 print:p-0">
                <div :class="!isAuthPage ? 'mx-auto max-w-6xl print:max-w-none' : 'h-full w-full'">
                    <slot />
                </div>
            </div>
        </main>
    </div>
</template>
