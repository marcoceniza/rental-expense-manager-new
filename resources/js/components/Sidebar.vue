<script setup>
import { computed, inject } from 'vue'
import { usePage, Link } from '@inertiajs/vue3'
import { LogOut, X } from "lucide-vue-next"
import { useNavItems } from '../composables/useNavItems'

const { navItems } = useNavItems()

// ✅ Inertia page object (replaces Vue Router)
const page = usePage()

const currentUrl = computed(() => page.url)

const isAuthPage = computed(() =>
	['Login', 'Register'].includes(page.component)
)

/**
 * MENU STATE (NO STORE)
 */
const isMenuOpen = inject('isMenuOpen')
const toggleMenu = inject('toggleMenu')

const user = inject('user')
const handleLogout = inject('handleLogout')
</script>

<template>
	<!-- overlay -->
	<div
		v-if="isMenuOpen"
		class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-60 md:hidden"
	/>

	<button
		class="absolute z-999 right-3.75 top-3.75"
		@click="toggleMenu"
	>
		<X v-show="isMenuOpen" class="text-white w-6 h-6" />
	</button>

	<aside
		v-if="!isAuthPage"
		class="fixed md:sticky top-0 left-0 z-70 w-64 bg-slate-900 text-white flex flex-col h-screen border-r border-slate-800 transition-transform duration-300 md:translate-x-0"
		:class="isMenuOpen ? 'translate-x-0' : '-translate-x-full'"
	>
		<!-- logo -->
		<div class="p-6 border-b border-slate-800">
			<figure>
				<img src="/logo2.png" class="mx-auto w-62.5" />
			</figure>
		</div>

		<!-- nav -->
		<nav class="flex-1 p-4 space-y-1 overflow-y-auto">
			<Link
				v-for="item in navItems"
				:key="item.path"
				:href="item.path"
				class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all group"
				:class="{ 'bg-blue-600 text-white': currentUrl === item.path }"
			>
				<component :is="item.icon" class="w-5 h-5" />
				<span>{{ item.name }}</span>
			</Link>
		</nav>

		<!-- user -->
		<div class="p-4 border-t border-slate-800 space-y-2">
			<div class="flex items-center gap-3 px-4 py-3 bg-slate-800/50 rounded-xl">
				<div class="w-8 h-8 rounded-full bg-blue-600 flex items-center justify-center text-xs font-bold">
					{{ user?.name?.charAt(0) || 'A' }}
				</div>

				<div class="flex-1 overflow-hidden">
					<p class="text-sm font-bold truncate">
						{{ user?.name || 'Admin' }}
					</p>
					<p class="text-[10px] opacity-60 truncate">
						{{ user?.email || 'admin@gmail.com' }}
					</p>
				</div>
			</div>

			<button
				@click="handleLogout"
				class="w-full flex items-center gap-3 px-4 py-3 text-slate-400 hover:text-red-400 hover:bg-red-400/10 rounded-xl"
			>
				<LogOut class="w-5 h-5" />
				<span>Logout</span>
			</button>
		</div>
	</aside>
</template>