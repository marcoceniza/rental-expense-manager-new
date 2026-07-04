<script setup lang="ts">
import { format, parseISO } from 'date-fns';
import { AlertCircle, RotateCcw, Trash2, X } from 'lucide-vue-next';

import type { Transaction } from '@/types';

const props = defineProps<{
    show: boolean;
    trashed: {
        data: Transaction[];
    };
    trashedCount: number;
    formatCurrency: (amount: number) => string;
    restore: (id: number) => void;
    forceDelete: (id: number) => void;
}>();

const emit = defineEmits(['close']);

const close = () => emit('close');
</script>

<template>
    <div v-if="show" class="fixed inset-0 top-[-32px] z-50 flex items-center justify-center bg-slate-900/50 p-4 backdrop-blur-sm">
        <div class="w-full max-w-2xl overflow-hidden rounded-2xl bg-white shadow-2xl duration-200 animate-in fade-in zoom-in">
            <div class="flex items-center justify-between border-b border-slate-100 bg-red-50/50 p-6">
                <div class="flex items-center gap-3 text-red-600">
                    <Trash2 class="h-6 w-6" />
                    <h3 class="text-xl font-bold">Trash Bin</h3>
                </div>

                <button @click="close" class="cursor-pointer rounded-lg p-2 transition-colors hover:bg-white">
                    <X class="h-5 w-5 text-slate-400" />
                </button>
            </div>

            <div class="max-h-[60vh] overflow-y-auto">
                <table v-if="trashedCount > 0" class="w-full text-left">
                    <thead class="sticky top-0 z-10 bg-slate-50 text-[10px] font-bold uppercase text-slate-500">
                        <tr>
                            <th class="px-6 py-3">Details</th>
                            <th class="px-6 py-3 text-right">Amount</th>
                            <th class="px-6 py-3 text-right">Deleted Date</th>
                            <th class="px-6 py-3 text-center">Actions</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-slate-100">
                        <tr v-for="trash in trashed.data" :key="trash.id" class="transition-colors hover:bg-slate-50">
                            <td class="px-6 py-4">
                                <p class="mb-1 text-xs text-slate-500">
                                    {{ format(parseISO(trash.transaction_date), 'MMM dd, yyyy') }}
                                </p>
                                <p class="text-sm font-bold text-slate-900">
                                    {{ trash.description }}
                                </p>
                                <p class="mt-0.5 text-[10px] text-slate-600">
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
                                    {{ trash.deleted_at ? format(parseISO(trash.deleted_at), 'MMM dd, yyyy') : 'N/A' }}
                                </p>
                            </td>

                            <td class="px-6 py-4">
                                <div class="flex items-center justify-center gap-2">
                                    <button
                                        @click="restore(trash.id)"
                                        class="cursor-pointer rounded-lg p-2 text-emerald-600 transition-colors hover:bg-emerald-50"
                                        title="Restore"
                                    >
                                        <RotateCcw class="h-4 w-4" />
                                    </button>

                                    <button
                                        @click="forceDelete(trash.id)"
                                        class="cursor-pointer rounded-lg p-2 text-rose-600 transition-colors hover:bg-rose-50"
                                        title="Delete Permanently"
                                    >
                                        <Trash2 class="h-4 w-4" />
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <div v-else class="flex flex-col items-center gap-4 p-12 text-center text-slate-400">
                    <AlertCircle class="h-12 w-12 opacity-20" />
                    <p class="italic">The trash bin is currently empty.</p>
                </div>
            </div>

            <div class="border-t border-slate-100 bg-slate-50 p-6 text-right">
                <button
                    @click="close"
                    class="cursor-pointer rounded-xl border border-slate-200 bg-white px-6 py-2 font-bold text-slate-600 transition-colors hover:bg-slate-50"
                >
                    Close
                </button>
            </div>
        </div>
    </div>
</template>
