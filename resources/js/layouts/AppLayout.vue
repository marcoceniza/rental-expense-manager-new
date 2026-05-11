<script setup lang="ts">
import Sidebar from '../components/Sidebar.vue'
import MobileHeader from '../components/MobileHeader.vue'
import { computed, provide, ref, watch } from 'vue'
import { usePage, router } from '@inertiajs/vue3'
import { route } from 'ziggy-js'
import { Notyf } from 'notyf'
import 'notyf/notyf.min.css'
import { User, SharedData, FlashProps } from '@/types'

const page = usePage<SharedData>()
const notyf = new Notyf()

const isAuthPage = computed(() =>
    ['Login', 'Register', 'ForgotPassword', 'ResetPassword', 'VerifyEmail', 'ConfirmPassword'].includes(page.component)
)

const isMenuOpen = ref(false)
const toggleMenu = () => {
    isMenuOpen.value = !isMenuOpen.value
}

const user = computed<User | undefined>(() => page.props.auth?.user)
const handleLogout = () => {
    router.post(route('logout'))
}

watch(
    () => page.props.flash,
    (flash: FlashProps) => {
        if (flash.success) notyf.success(flash.success)
        if (flash.error) notyf.error(flash.error)
    },
    { deep: true, immediate: true }
)

provide('isMenuOpen', isMenuOpen)
provide('toggleMenu', toggleMenu)
provide('user', user)
provide('handleLogout', handleLogout)
</script>

<template>
    <div class="min-h-screen bg-slate-50 flex">
        <div v-if="!isAuthPage" class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-[60] md:hidden" />

        <Sidebar v-if="!isAuthPage" />

        <main class="flex-1 flex flex-col min-w-0 min-h-screen">
            <MobileHeader v-if="!isAuthPage" />

            <div class="flex-1 p-4 md:p-8">

                <div :class="!isAuthPage
                    ? 'max-w-6xl mx-auto'
                    : 'w-full h-full'">
                    <slot />
                </div>

            </div>
        </main>
    </div>
</template>