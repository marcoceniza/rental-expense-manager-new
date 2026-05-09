<script setup lang="ts">
import { format, parseISO } from 'date-fns'
import { Trash2, X, RotateCcw, AlertCircle } from 'lucide-vue-next'

import type { Transaction } from '@/types'

const props = defineProps<{
    show: boolean
    trashed: {
        data: Transaction[]
    }
    trashedCount: number
    formatCurrency: (amount: number) => string
    restore: (id: number) => void
    forceDelete: (id: number) => void
}>()

const emit = defineEmits(['close'])

const close = () => emit('close')
</script>

<template>
    <div
        v-if="show"
        class="fixed top-[-32px] inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-sm"
    >
        <div
            class="bg-white w-full max-w-2xl rounded-2xl shadow-2xl overflow-hidden animate-in fade-in zoom-in duration-200"
        >
            <div class="p-6 border-b border-slate-100 flex items-center justify-between bg-red-50/50">
                <div class="flex items-center gap-3 text-red-600">
                    <Trash2 class="w-6 h-6" />
                    <h3 class="text-xl font-bold">Trash Bin</h3>
                </div>

                <button
                    @click="close"
                    class="p-2 hover:bg-white rounded-lg transition-colors cursor-pointer"
                >
                    <X class="w-5 h-5 text-slate-400" />
                </button>
            </div>

            <div class="max-h-[60vh] overflow-y-auto">

                <table v-if="trashedCount > 0" class="w-full text-left">
                    <thead class="bg-slate-50 text-slate-500 text-[10px] uppercase font-bold sticky top-0 z-10">
                        <tr>
                            <th class="px-6 py-3">Details</th>
                            <th class="px-6 py-3 text-right">Amount</th>
                            <th class="px-6 py-3 text-right">Deleted Date</th>
                            <th class="px-6 py-3 text-center">Actions</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-slate-100">
                        <tr
                            v-for="trash in trashed.data"
                            :key="trash.id"
                            class="hover:bg-slate-50 transition-colors"
                        >
                            <td class="px-6 py-4">
                                <p class="text-xs text-slate-500 mb-1">
                                    {{ format(parseISO(trash.transaction_date), 'MMM dd, yyyy') }}
                                </p>
                                <p class="text-sm font-bold text-slate-900">
                                    {{ trash.description }}
                                </p>
                                <p class="text-[10px] text-slate-600 mt-0.5">
                                    {{ trash.type.toUpperCase() }}
                                </p>
                            </td>

                            <td class="px-6 py-4 text-right">
                                <span class="text-sm font-bold text-slate-600">
                                    {{ formatCurrency(trash.amount) }}
                                </span>
                            </td>

                            <td class="px-6 py-4 text-right">
                                <p class="text-xs text-slate-600">
                                    {{ trash.deleted_at
                                        ? format(parseISO(trash.deleted_at), 'MMM dd, yyyy')
                                        : 'N/A' }}
                                </p>
                            </td>

                            <td class="px-6 py-4">
                                <div class="flex items-center justify-center gap-2">

                                    <button
                                        @click="restore(trash.id)"
                                        class="p-2 text-emerald-600 hover:bg-emerald-50 rounded-lg transition-colors cursor-pointer"
                                        title="Restore"
                                    >
                                        <RotateCcw class="w-4 h-4" />
                                    </button>

                                    <button
                                        @click="forceDelete(trash.id)"
                                        class="p-2 text-rose-600 hover:bg-rose-50 rounded-lg transition-colors cursor-pointer"
                                        title="Delete Permanently"
                                    >
                                        <Trash2 class="w-4 h-4" />
                                    </button>

                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <div v-else class="p-12 text-center text-slate-400 flex flex-col items-center gap-4">
                    <AlertCircle class="w-12 h-12 opacity-20" />
                    <p class="italic">The trash bin is currently empty.</p>
                </div>
            </div>

            <div class="p-6 bg-slate-50 border-t border-slate-100 text-right">
                <button
                    @click="close"
                    class="px-6 py-2 bg-white border border-slate-200 text-slate-600 font-bold rounded-xl hover:bg-slate-50 transition-colors cursor-pointer"
                >
                    Close
                </button>
            </div>
        </div>
    </div>
</template>