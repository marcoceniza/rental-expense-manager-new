<script setup>
import { computed } from 'vue'
import { router } from '@inertiajs/vue3'

const props = defineProps({
    pagination: {
        type: Object,
        required: true
    }
})

const goToPage = (url) => {
    if (!url) return

    router.visit(url, {
        preserveScroll: true,
        preserveState: true,
    })
}

const visiblePages = computed(() => {
    return props.pagination.links.filter(
        (link) =>
            link.label !== '&laquo; Previous' &&
            link.label !== 'Next &raquo;'
    )
})
</script>

<template>
    <div
        v-if="pagination.last_page > 1"
        class="flex items-center justify-between mt-6 px-4 py-3 bg-white border-t border-slate-200 rounded-bl-2xl rounded-br-2xl rounded-tl-none rounded-tr-none"
    >
        <p class="text-sm text-slate-500">
            Page {{ pagination.current_page }} of {{ pagination.last_page }}
        </p>

        <div class="flex items-center gap-2">

            <button
                @click="goToPage(pagination.prev_page_url)"
                :disabled="!pagination.prev_page_url"
                class="px-3 py-2 rounded-lg border text-sm cursor-pointer"
                :class="!pagination.prev_page_url
                    ? 'text-slate-300 border-slate-200 cursor-not-allowed'
                    : 'text-slate-600 border-slate-300 hover:bg-slate-50'"
            >
                Prev
            </button>

            <button
                v-for="link in visiblePages"
                :key="link.label"
                v-html="link.label"
                @click="goToPage(link.url)"
                :disabled="!link.url"
                class="px-3 py-2 rounded-lg text-sm cursor-pointer"
                :class="link.active
                    ? 'bg-blue-600 text-white'
                    : 'text-slate-600 hover:bg-slate-100'"
            />

            <button
                @click="goToPage(pagination.next_page_url)"
                :disabled="!pagination.next_page_url"
                class="px-3 py-2 rounded-lg border text-sm cursor-pointer"
                :class="!pagination.next_page_url
                    ? 'text-slate-300 border-slate-200 cursor-not-allowed'
                    : 'text-slate-600 border-slate-300 hover:bg-slate-50'"
            >
                Next
            </button>

        </div>
    </div>
</template>