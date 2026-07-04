<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { LogOut, X } from 'lucide-vue-next';
import { computed, inject, type Ref } from 'vue';
import { useNavItems } from '../composables/useNavItems';
import logo2 from '../images/logo2.png';

interface User {
    name: string;
    email: string;
    user_type: string;
}

const { navItems } = useNavItems();
const page = usePage();
const currentUrl = computed(() => page.url);

const isAuthPage = computed(() => ['Login', 'Register'].includes(page.component));

const isMenuOpen = inject<Ref<boolean>>('isMenuOpen');
const toggleMenu = inject<() => void>('toggleMenu');
const user = inject<Ref<User>>('user');
const handleLogout = inject<() => void>('handleLogout');
</script>

<template>
    <div v-if="isMenuOpen" class="z-[9998] fixed inset-0 bg-slate-900/50 backdrop-blur-sm md:hidden" />

    <button class="fixed right-4 top-4 z-[10001] md:hidden" @click="toggleMenu">
        <X v-show="isMenuOpen" class="h-6 w-6 text-white" />
    </button>

    <aside
        v-if="!isAuthPage"
        class="z-[9999] fixed left-0 top-0 flex h-screen w-64 flex-col border-r border-slate-800 bg-slate-900 text-white transition-transform duration-300 md:sticky md:translate-x-0"
        :class="isMenuOpen ? 'translate-x-0' : '-translate-x-full'"
    >
        <div class="border-b border-slate-800 p-6">
            <figure>
                <img :src="logo2" class="w-62.5 mx-auto" />
            </figure>
        </div>

        <nav class="flex-1 space-y-1 overflow-y-auto p-4">
            <Link
                v-for="item in navItems"
                :key="item.path"
                :href="item.path"
                class="group flex items-center gap-3 rounded-xl px-4 py-3 transition-all"
                :class="{ 'bg-blue-600 text-white': currentUrl === item.path }"
                @click="toggleMenu"
            >
                <component :is="item.icon" class="h-5 w-5" />
                <span>{{ item.name }}</span>
            </Link>
        </nav>

        <div class="space-y-2 border-t border-slate-800 p-4">
            <div class="flex items-center gap-3 rounded-xl bg-slate-800/50 px-4 py-3">
                <div class="flex h-8 w-8 items-center justify-center rounded-full bg-blue-600 text-xs font-bold">
                    {{ user?.name?.charAt(0) || 'A' }}
                </div>

                <div class="flex-1 overflow-hidden">
                    <p class="truncate text-sm font-bold" data-testid="user-name">
                        {{ user?.name || 'Admin' }}
                    </p>
                    <p class="truncate text-[10px] opacity-60">
                        {{ user?.email || 'admin@gmail.com' }}
                    </p>
                </div>
            </div>

            <button
                @click="handleLogout"
                class="flex w-full items-center gap-3 rounded-xl px-4 py-3 text-slate-400 hover:bg-red-400/10 hover:text-red-400"
            >
                <LogOut class="h-5 w-5" />
                <span>Logout</span>
            </button>
        </div>
    </aside>
</template>
