<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { computed } from 'vue';

interface PaginationLink {
    label: string;
    url: string | null;
    active: boolean;
}

interface Pagination {
    current_page: number;
    last_page: number;
    prev_page_url: string | null;
    next_page_url: string | null;
    links: PaginationLink[];
}

interface Props {
    pagination: Pagination;
}

const props = defineProps<Props>();

const goToPage = (url: string | null) => {
    if (!url) return;

    router.visit(url, {
        preserveScroll: true,
        preserveState: true,
    });
};

const visiblePages = computed(() => {
    const allPages = props.pagination.links.filter((link) => link.label !== '&laquo; Previous' && link.label !== 'Next &raquo;');

    const currentPage = props.pagination.current_page;
    const lastPage = props.pagination.last_page;

    // If total pages <= 5, show all pages
    if (lastPage <= 5) {
        return allPages;
    }

    const pages: PaginationLink[] = [];
    const addedPages = new Set<number>();

    // Helper function to add page without duplicates
    const addPage = (pageNum: number) => {
        if (pageNum >= 1 && pageNum <= lastPage && !addedPages.has(pageNum)) {
            pages.push(allPages[pageNum - 1]);
            addedPages.add(pageNum);
        }
    };

    // Helper function to add ellipsis
    const addEllipsis = () => {
        pages.push({ label: '...', url: null, active: false });
    };

    // Always show first 2 pages
    addPage(1);
    addPage(2);

    // Determine the range around current page
    const rangeStart = Math.max(3, currentPage - 1);
    const rangeEnd = Math.min(lastPage - 2, currentPage + 1);

    // Add ellipsis if there's a gap after first 2 pages
    if (rangeStart > 3) {
        addEllipsis();
    }

    // Add pages around current page
    for (let i = rangeStart; i <= rangeEnd; i++) {
        addPage(i);
    }

    // Add ellipsis if there's a gap before last 2 pages
    if (rangeEnd < lastPage - 2) {
        addEllipsis();
    }

    // Always show last 2 pages
    addPage(lastPage - 1);
    addPage(lastPage);

    return pages;
});
</script>

<template>
    <div
        v-if="pagination.last_page > 1"
        class="mt-6 flex items-center justify-between rounded-bl-2xl rounded-br-2xl rounded-tl-none rounded-tr-none border-t border-slate-200 bg-white px-4 py-3"
    >
        <p class="text-sm text-slate-500">Page {{ pagination.current_page }} of {{ pagination.last_page }}</p>

        <div class="flex items-center gap-2">
            <button
                @click="goToPage(pagination.prev_page_url)"
                :disabled="!pagination.prev_page_url"
                class="cursor-pointer rounded-lg border px-3 py-2 text-sm"
                :class="
                    !pagination.prev_page_url
                        ? 'cursor-not-allowed border-slate-200 text-slate-300'
                        : 'border-slate-300 text-slate-600 hover:bg-slate-50'
                "
            >
                Prev
            </button>

            <button
                v-for="(link, index) in visiblePages"
                :key="`${link.label}-${index}`"
                v-html="link.label"
                @click="goToPage(link.url)"
                :disabled="!link.url"
                class="hidden rounded-lg px-3 py-2 text-sm md:inline-flex"
                :class="[
                    link.label === '...'
                        ? 'cursor-default text-slate-400'
                        : link.active
                          ? 'cursor-pointer bg-blue-600 text-white'
                          : 'cursor-pointer text-slate-600 hover:bg-slate-100',
                ]"
            />

            <button
                @click="goToPage(pagination.next_page_url)"
                :disabled="!pagination.next_page_url"
                class="cursor-pointer rounded-lg border px-3 py-2 text-sm"
                :class="
                    !pagination.next_page_url
                        ? 'cursor-not-allowed border-slate-200 text-slate-300'
                        : 'border-slate-300 text-slate-600 hover:bg-slate-50'
                "
            >
                Next
            </button>
        </div>
    </div>
</template>
