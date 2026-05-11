<script setup>
import { computed, inject } from 'vue'
import { usePage } from '@inertiajs/vue3'
import { Menu, X } from "lucide-vue-next"

const page = usePage()

const isAuthPage = computed(() =>
	['Login', 'Register', 'ForgotPassword', 'ResetPassword', 'VerifyEmail', 'ConfirmPassword'].includes(page.component)
)

const isMenuOpen = inject('isMenuOpen')
const toggleMenu = inject('toggleMenu')
</script>

<template>
	<button
		v-if="!isAuthPage"
		class="md:hidden absolute z-50 right-4 top-4 p-2 bg-slate-900 text-white rounded-lg"
		@click="toggleMenu"
	>
		<Menu v-if="!isMenuOpen" class="w-6 h-6" />
		<X v-else class="w-6 h-6" />
	</button>
</template>