<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import type { User } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import { format } from 'date-fns';
import { ChevronDown, ChevronUp, FileText, LoaderCircle, Search, X, ChevronLeft, ChevronRight, ChevronsLeft, ChevronsRight } from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';
import { route } from 'ziggy-js';

defineOptions({
    layout: AppLayout,
});

interface Log {
    id: number;
    user_id: number | null;
    user_name: string;
    user_email: string | null;
    action: string;
    description: string | null;
    loggable_type: string | null;
    loggable_id: number | null;
    ip_address: string | null;
    user_agent: string | null;
    payload: Record<string, unknown> | null;
    created_at: string;
}

interface LogsResponse {
    data: Log[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
}

const props = defineProps<{
    logs: LogsResponse;
    filters: {
        actions: string[];
        users: User[];
    };
}>();

const loading = ref(false);
const searchQuery = ref('');
const actionFilter = ref<string>('');
const userFilter = ref<string>('');
const startDate = ref<string>('');
const endDate = ref<string>('');
const expandedLogs = ref<Set<number>>(new Set());

const toggleExpand = (logId: number) => {
    if (expandedLogs.value.has(logId)) {
        expandedLogs.value.delete(logId);
        expandedLogs.value = new Set(expandedLogs.value);
    } else {
        expandedLogs.value.add(logId);
        expandedLogs.value = new Set(expandedLogs.value);
    }
};

const filteredLogs = computed(() => {
    return props.logs.data.filter((log: Log) => {
        const matchesSearch =
            !searchQuery.value ||
            log.action.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
            log.description?.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
            log.user_name.toLowerCase().includes(searchQuery.value.toLowerCase());

        const matchesAction = !actionFilter.value || log.action === actionFilter.value;
        const matchesUser = !userFilter.value || String(log.user_id) === userFilter.value;

        return matchesSearch && matchesAction && matchesUser;
    });
});


const applyFilters = (page: number = 1) => {
    loading.value = true;

    const params: Record<string, string> = {};

    if (searchQuery.value) {
        params.search = searchQuery.value;
    }
    if (actionFilter.value) {
        params.action = actionFilter.value;
    }
    if (userFilter.value) {
        params.user_id = userFilter.value;
    }
    if (startDate.value) {
        params.start_date = startDate.value;
    }
    if (endDate.value) {
        params.end_date = endDate.value;
    }
    if (perPage.value) {
        params.per_page = String(perPage.value);
    }
    if (page > 1) {
        params.page = String(page);
    }

    router.get(route('admin.logs'), params, {
        preserveScroll: true,
        preserveState: true,
        onFinish: () => {
            loading.value = false;
        },
    });
};

const removeFilter = (type: 'action' | 'user') => {
    if (type === 'action') {
        actionFilter.value = '';
    } else {
        userFilter.value = '';
    }
};

// Pagination state
const currentPage = computed(() => props.logs.current_page || 1);
const pageInput = ref(currentPage.value);
const perPage = ref<number>(props.logs.per_page || 20);
const totalPages = computed(() => props.logs.last_page || 1);

watch(currentPage, (page) => {
    pageInput.value = page;
});

watch(
    () => props.logs.per_page,
    (value) => {
        perPage.value = value || 20;
    },
);

const goToPage = (page: number) => {
    if (!Number.isInteger(page) || page < 1 || page > totalPages.value) {
        pageInput.value = currentPage.value;
        return;
    }

    if (page === currentPage.value) {
        pageInput.value = currentPage.value;
        return;
    }

    applyFilters(page);
};

const changePerPage = () => {
    applyFilters(1);
};

const getStartRecord = () => {
    if (props.logs.data.length === 0) return 0;
    return (props.logs.current_page - 1) * props.logs.per_page + 1;
};

const getEndRecord = () => {
    if (props.logs.data.length === 0) return 0;
    return Math.min(props.logs.current_page * props.logs.per_page, props.logs.total);
};

const formatDate = (dateString: string) => {
    try {
        return format(new Date(dateString), 'MMM dd, yyyy HH:mm:ss');
    } catch {
        return dateString;
    }
};

const formatAction = (action: string) => {
    const actions = {
        login: { label: 'Login', class: 'bg-green-100 text-green-700' },
        logout: { label: 'Logout', class: 'bg-orange-100 text-orange-700' },
        create: { label: 'Created', class: 'bg-blue-100 text-blue-700' },
        update: { label: 'Updated', class: 'bg-purple-100 text-purple-700' },
        delete: { label: 'Deleted', class: 'bg-red-100 text-red-700' },
    };

    const matched = Object.keys(actions).find((key) => action.toLowerCase().includes(key));
    if (matched) {
        return actions[matched as keyof typeof actions];
    }

    return { label: action, class: 'bg-slate-100 text-slate-700' };
};

const truncate = (str: string | null, length: number = 100) => {
    if (!str) return '';
    return str.length > length ? str.substring(0, length) + '...' : str;
};
</script>

<template>
    <Head title="Activity Logs" />

        <div class="space-y-8">
            <h2 class="flex items-center gap-3 text-3xl font-bold tracking-tight text-slate-900 max-sm:text-2xl">
                <FileText class="h-8 w-8" />
                Activity Logs
            </h2>
            <p class="mt-1 text-slate-500">Track all system activities including logins, transactions, and configuration changes.</p>
        </div>

        <!-- Filters -->
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                <div class="relative">
                    <Search class="absolute left-4 top-1/2 h-5 w-5 -translate-y-1/2 text-slate-400" />
                    <input
                        v-model="searchQuery"
                        type="text"
                        placeholder="Search logs..."
                        @input="applyFilters()"
                        class="w-full rounded-xl border-none bg-slate-50 py-3 pl-12 pr-4 transition-all focus:ring-2 focus:ring-blue-500"
                    />
                </div>

                <select
                    v-model="actionFilter"
                    @change="applyFilters()"
                    class="min-w-48 cursor-pointer rounded-xl border-none bg-slate-50 px-4 py-3 font-medium text-slate-700 transition-all focus:ring-2 focus:ring-blue-500"
                >
                    <option value="">All Actions</option>
                    <option v-for="action in props.filters.actions" :key="action" :value="action">
                        {{ action }}
                    </option>
                </select>

                <select
                    v-model="userFilter"
                    @change="applyFilters()"
                    class="min-w-48 cursor-pointer rounded-xl border-none bg-slate-50 px-4 py-3 font-medium text-slate-700 transition-all focus:ring-2 focus:ring-blue-500"
                >
                    <option value="">All Users</option>
                    <option v-for="user in props.filters.users" :key="user.id" :value="String(user.id)">{{ user.name }} ({{ user.email }})</option>
                </select>

                <div class="grid grid-cols-2 gap-2">
    <div>
        <label class="block text-xs font-medium text-slate-600 mb-1">From</label>
        <input v-model="startDate" type="date" @change="applyFilters()" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-slate-700 transition-all focus:ring-2 focus:ring-blue-500" />
    </div>
    <div>
        <label class="block text-xs font-medium text-slate-600 mb-1">To</label>
        <input v-model="endDate" type="date" @change="applyFilters()" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-slate-700 transition-all focus:ring-2 focus:ring-blue-500" />
        <p class="text-xs text-slate-500 mt-1">Clear</p>
    </div>
</div>
            </div>

            <!-- Active Filters -->
                        <div v-if="actionFilter || userFilter || searchQuery" class="mt-4 flex flex-wrap gap-2">
                            <span v-if="actionFilter" class="inline-flex items-center gap-2 rounded-full bg-blue-100 px-3 py-1 text-sm font-medium text-blue-700">
                                Action: {{ actionFilter }}
                                <button @click="removeFilter('action')" class="hover:text-blue-900">
                                    <X class="h-4 w-4" />
                                </button>
                            </span>
                            <span
                                v-if="userFilter"
                                class="inline-flex items-center gap-2 rounded-full bg-purple-100 px-3 py-1 text-sm font-medium text-purple-700"
                            >
                                User: {{ props.filters.users.find((u) => String(u.id) === userFilter)?.name }}
                                <button @click="removeFilter('user')" class="hover:text-purple-900">
                                    <X class="h-4 w-4" />
                                </button>
                            </span>
                        </div>

                        <!-- Pagination Controls -->
                        <div class="mt-6 flex flex-col gap-4 rounded-2xl border border-slate-200 bg-slate-50 p-4 md:flex-row md:items-center md:justify-between">
                            <div class="flex items-center gap-4">
                                <span class="text-sm font-medium text-slate-600">Rows per page:</span>
                                <select
                                    v-model="perPage"
                                    @change="changePerPage"
                                    class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm font-medium text-slate-700 focus:ring-2 focus:ring-blue-500"
                                >
                                    <option :value="10">10</option>
                                    <option :value="20">20</option>
                                    <option :value="50">50</option>
                                    <option :value="100">100</option>
                                </select>
                            </div>
                
                            <div class="flex items-center gap-2">
                                <button
                                    @click="goToPage(1)"
                                    :disabled="currentPage === 1"
                                    class="inline-flex items-center gap-1 rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm font-medium text-slate-600 transition-colors hover:bg-slate-100 disabled:opacity-50 disabled:cursor-not-allowed"
                                >
                                    <ChevronsLeft class="h-4 w-4" />
                                    <span class="hidden sm:inline">First</span>
                                </button>
                    
                                <button
                                    @click="goToPage(currentPage - 1)"
                                    :disabled="currentPage === 1"
                                    class="inline-flex items-center gap-1 rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm font-medium text-slate-600 transition-colors hover:bg-slate-100 disabled:opacity-50 disabled:cursor-not-allowed"
                                >
                                    <ChevronLeft class="h-4 w-4" />
                                    <span class="hidden sm:inline">Previous</span>
                                </button>
                    
                                <div class="flex items-center gap-1">
                                    <span class="text-sm text-slate-600">Page</span>
                                    <input
                                        v-model.number="pageInput"
                                        type="number"
                                        min="1"
                                        :max="totalPages"
                                        @change="goToPage(pageInput)"
                                        class="w-16 rounded-lg border border-slate-200 bg-white px-3 py-2 text-center text-sm font-medium text-slate-700 focus:ring-2 focus:ring-blue-500"
                                    />
                                    <span class="text-sm text-slate-600">of {{ totalPages }}</span>
                                </div>
                    
                                <button
                                    @click="goToPage(currentPage + 1)"
                                    :disabled="currentPage === totalPages"
                                    class="inline-flex items-center gap-1 rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm font-medium text-slate-600 transition-colors hover:bg-slate-100 disabled:opacity-50 disabled:cursor-not-allowed"
                                >
                                    <span class="hidden sm:inline">Next</span>
                                    <ChevronRight class="h-4 w-4" />
                                </button>
                    
                                <button
                                    @click="goToPage(totalPages)"
                                    :disabled="currentPage === totalPages"
                                    class="inline-flex items-center gap-1 rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm font-medium text-slate-600 transition-colors hover:bg-slate-100 disabled:opacity-50 disabled:cursor-not-allowed"
                                >
                                    <span class="hidden sm:inline">Last</span>
                                    <ChevronsRight class="h-4 w-4" />
                                </button>
                            </div>
                
                            <div class="text-sm text-slate-600">
                                Showing {{ getStartRecord() }}-{{ getEndRecord() }} of {{ props.logs.total }} entries
                            </div>
                        </div>

                        <!-- Logs Table -->
        <div class="rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead class="bg-slate-50 text-xs font-bold uppercase text-slate-500">
                        <tr>
                            <th class="px-6 py-4">Timestamp</th>
                            <th class="px-6 py-4">User</th>
                            <th class="px-6 py-4">Action</th>
                            <th class="px-6 py-4">Description</th>
                            <th class="px-6 py-4">IP Address</th>
                            <th class="whitespace-nowrap px-6 py-4">Details</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-slate-100">
                        <tr v-if="loading">
                            <td colspan="6" class="px-6 py-12 text-center text-slate-500">
                                <div class="flex items-center justify-center gap-2">
                                    <LoaderCircle class="h-4 w-4 animate-spin text-blue-600" />
                                    <span>Loading logs...</span>
                                </div>
                            </td>
                        </tr>

                        <tr v-else-if="filteredLogs.length === 0">
                            <td colspan="6" class="px-6 py-12 text-center text-slate-400">
                                <p class="font-medium italic">No activity logs found.</p>
                                <p class="mt-2 text-sm">Try adjusting your filters or check back later.</p>
                            </td>
                        </tr>

                        <template v-else>
                            <template v-for="log in filteredLogs" :key="log.id">
                                <tr class="group transition-colors hover:bg-slate-50">
                                    <td class="px-6 py-4 text-sm text-slate-600">
                                        <span class="font-medium text-slate-800">{{ formatDate(log.created_at) }}</span>
                                    </td>

                                    <td class="px-6 py-4">
                                        <div>
                                            <p class="text-sm font-medium text-slate-900">{{ log.user_name }}</p>
                                            <p v-if="log.user_email" class="text-xs text-slate-400">{{ log.user_email }}</p>
                                        </div>
                                    </td>

                                    <td class="px-6 py-4">
                                        <span class="rounded-full px-2.5 py-1 text-xs font-semibold" :class="formatAction(log.action).class">
                                            {{ formatAction(log.action).label }}
                                        </span>
                                    </td>

                                    <td class="max-w-xs px-6 py-4">
                                        <p class="text-sm font-medium text-slate-900">{{ truncate(log.description, 50) }}</p>
                                    </td>

                                    <td class="px-6 py-4 text-sm text-slate-600">
                                        <code class="rounded bg-slate-100 px-1.5 py-0.5 text-xs">{{ log.ip_address || '-' }}</code>
                                    </td>

                                    <td class="whitespace-nowrap px-6 py-4">
                                        <button
                                            v-if="log.payload && Object.keys(log.payload).length > 0"
                                            @click="toggleExpand(log.id)"
                                            class="inline-flex items-center gap-1.5 text-sm font-medium text-blue-600 transition-colors hover:text-blue-700"
                                        >
                                            <span>{{ expandedLogs.has(log.id) ? 'Hide' : 'View' }} Details</span>
                                            <ChevronDown v-if="!expandedLogs.has(log.id)" class="h-4 w-4" />
                                            <ChevronUp v-else class="h-4 w-4" />
                                        </button>
                                        <span v-else class="text-sm text-slate-400">No additional details</span>
                                    </td>
                                </tr>

                                <tr v-if="expandedLogs.has(log.id)">
                                    <td :colspan="6" class="px-6 py-4">
                                        <div class="rounded-lg border border-slate-200 bg-slate-50 p-4">
                                            <h4 class="mb-2 text-sm font-bold text-slate-900">Event Details</h4>
                                            <div class="max-h-64 overflow-y-auto">
                                                <pre class="whitespace-pre-wrap break-all font-mono text-xs text-slate-600">{{
                                                    log.payload ? JSON.stringify(log.payload, null, 2) : 'No data'
                                                }}</pre>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            </template>
                        </template>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</template>
