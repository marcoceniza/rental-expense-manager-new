<script setup>
import Sidebar from '../components/Sidebar.vue'
import MobileHeader from '../components/MobileHeader.vue'
import { computed, provide, ref } from 'vue'
import { usePage, router } from '@inertiajs/vue3'
import { route } from 'ziggy-js'

const page = usePage()

// Inertia equivalent of route.name check
const isAuthPage = computed(() =>
    ['Login', 'Register', 'ForgotPassword', 'ResetPassword', 'VerifyEmail', 'ConfirmPassword'].includes(page.component)
)

// Menu state
const isMenuOpen = ref(false)
const toggleMenu = () => {
    isMenuOpen.value = !isMenuOpen.value
}

// User and logout
const user = computed(() => page.props.auth?.user)
const handleLogout = () => {
    router.post(route('logout'))
}

// Provide to child components
provide('isMenuOpen', isMenuOpen)
provide('toggleMenu', toggleMenu)
provide('user', user)
provide('handleLogout', handleLogout)
</script>

<template>
    <div class="min-h-screen bg-slate-50 flex">

        <!-- Mobile Sidebar Overlay -->
        <div v-if="!isAuthPage" class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-[60] md:hidden" />

        <!-- Sidebar (only show when NOT auth pages) -->
        <Sidebar v-if="!isAuthPage" />

        <main class="flex-1 flex flex-col min-w-0 min-h-screen">

            <!-- Mobile Header -->
            <MobileHeader v-if="!isAuthPage" />

            <div class="flex-1 p-4 md:p-8">

                <div :class="!isAuthPage
                    ? 'max-w-6xl mx-auto'
                    : 'w-full h-full'">
                    <!-- Inertia replaces RouterView -->
                    <slot />
                </div>

            </div>
        </main>
    </div>
</template>