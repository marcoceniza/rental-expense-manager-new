<script setup lang="ts">
import { ref } from 'vue'
import { Head, useForm } from '@inertiajs/vue3'
import { UserPlus, Eye, EyeOff, Mail, Lock, User } from 'lucide-vue-next'
import AppLayout from '@/layouts/AppLayout.vue'

defineOptions({
    layout: AppLayout,
})

const showPassword = ref(false)
const showPasswordConfirmation = ref(false)

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
})

const submit = () => {
    form.post(route('admin.register'), {
        preserveScroll: true,
        onSuccess: () => {
            form.reset()
        },
    })
}
</script>

<template>
    <Head title="Create New User" />

    <div class="space-y-8">
        <!-- Header -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h2 class="text-3xl font-bold text-slate-900 tracking-tight flex items-center gap-3 max-sm:text-2xl">
                    <UserPlus class="w-8 h-8" />
                    Create New User
                </h2>
                <p class="text-slate-500 mt-1">
                    Add a new user to the system with their account details
                </p>
            </div>
        </div>

        <!-- Form Container -->
        <div class="max-w-2xl mx-auto">
            <div class="space-y-8">
                <!-- Form Card -->
                <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-8">
                    <form @submit.prevent="submit" class="space-y-6">
                        <!-- Name Field -->
                        <div class="space-y-2">
                            <label for="name" class="block text-sm font-semibold text-slate-700">
                                Full Name
                            </label>
                            <div class="relative">
                                <User class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-slate-400" />
                                <input
                                    id="name"
                                    type="text"
                                    required
                                    autofocus
                                    v-model="form.name"
                                    placeholder="Enter full name"
                                    class="w-full pl-12 pr-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all text-slate-900 placeholder:text-slate-400"
                                />
                            </div>
                            <p v-if="form.errors.name" class="text-sm text-red-600 mt-1">
                                {{ form.errors.name }}
                            </p>
                        </div>

                        <!-- Email Field -->
                        <div class="space-y-2">
                            <label for="email" class="block text-sm font-semibold text-slate-700">
                                Email Address
                            </label>
                            <div class="relative">
                                <Mail class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-slate-400" />
                                <input
                                    id="email"
                                    type="email"
                                    required
                                    v-model="form.email"
                                    placeholder="email@example.com"
                                    autocomplete="email"
                                    class="w-full pl-12 pr-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all text-slate-900 placeholder:text-slate-400"
                                />
                            </div>
                            <p v-if="form.errors.email" class="text-sm text-red-600 mt-1">
                                {{ form.errors.email }}
                            </p>
                        </div>

                        <!-- Password Field -->
                        <div class="space-y-2">
                            <label for="password" class="block text-sm font-semibold text-slate-700">
                                Password
                            </label>
                            <div class="relative">
                                <Lock class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-slate-400" />
                                <input
                                    id="password"
                                    :type="showPassword ? 'text' : 'password'"
                                    required
                                    v-model="form.password"
                                    placeholder="Enter password"
                                    autocomplete="new-password"
                                    class="w-full pl-12 pr-12 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all text-slate-900 placeholder:text-slate-400"
                                />
                                <button
                                    type="button"
                                    @click="showPassword = !showPassword"
                                    class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 transition-colors cursor-pointer"
                                >
                                    <Eye v-if="!showPassword" class="w-5 h-5" />
                                    <EyeOff v-else class="w-5 h-5" />
                                </button>
                            </div>
                            <p v-if="form.errors.password" class="text-sm text-red-600 mt-1">
                                {{ form.errors.password }}
                            </p>
                        </div>

                        <!-- Password Confirmation Field -->
                        <div class="space-y-2">
                            <label for="password_confirmation" class="block text-sm font-semibold text-slate-700">
                                Confirm Password
                            </label>
                            <div class="relative">
                                <Lock class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-slate-400" />
                                <input
                                    id="password_confirmation"
                                    :type="showPasswordConfirmation ? 'text' : 'password'"
                                    required
                                    v-model="form.password_confirmation"
                                    placeholder="Confirm password"
                                    autocomplete="new-password"
                                    class="w-full pl-12 pr-12 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all text-slate-900 placeholder:text-slate-400"
                                />
                                <button
                                    type="button"
                                    @click="showPasswordConfirmation = !showPasswordConfirmation"
                                    class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 transition-colors cursor-pointer"
                                >
                                    <Eye v-if="!showPasswordConfirmation" class="w-5 h-5" />
                                    <EyeOff v-else class="w-5 h-5" />
                                </button>
                            </div>
                            <p v-if="form.errors.password_confirmation" class="text-sm text-red-600 mt-1">
                                {{ form.errors.password_confirmation }}
                            </p>
                        </div>

                        <!-- Submit Button -->
                        <div class="pt-4">
                            <button
                                type="submit"
                                :disabled="form.processing"
                                class="w-full flex items-center justify-center gap-2 bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-xl font-bold transition-all shadow-lg shadow-blue-200 active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed cursor-pointer"
                            >
                                <UserPlus class="w-5 h-5" />
                                <span v-if="form.processing">Creating User...</span>
                                <span v-else>Create User Account</span>
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Info Card -->
                <div class="bg-blue-50 border border-blue-100 rounded-xl p-4">
                    <p class="text-sm text-blue-800">
                        <span class="font-semibold">Note:</span> The new user will receive their credentials and can log in immediately after creation.
                    </p>
                </div>
            </div>
        </div>
    </div>
</template>
