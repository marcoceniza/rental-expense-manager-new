import { computed } from 'vue'
import { usePage } from '@inertiajs/vue3'
import { route } from 'ziggy-js'
import{ LayoutDashboard, ReceiptText, Repeat, FileChartPie, Tag, LayoutGrid, Heart, UserPlus } from 'lucide-vue-next'
import { SharedData } from '@/types'

function safeRoute(name: string, params?: Record<string, unknown>) {
    try {
        return route(name, params)
    } catch (error) {
        console.warn(`Ziggy route not found: ${name}`, error)
        return '#'
    }
}

export function useNavItems() {
    const page = usePage<SharedData>()

    const navItems = computed(() => {
        const user = page.props.auth?.user
        const isAdmin = user?.user_type === 'admin'

        const userItems = [
            { name: 'Dashboard', path: safeRoute('dashboard'), icon: LayoutDashboard },
            { name: 'Transactions', path: safeRoute('transactions.user'), icon: ReceiptText },
            { name: 'Reports', path: safeRoute('reports'), icon: FileChartPie },
            { name: 'Charity', path: safeRoute('charity'), icon: Heart },
        ]

        const adminItems = [
            { name: 'Dashboard', path: safeRoute('admin.dashboard'), icon: LayoutDashboard },
            { name: 'Transactions', path: safeRoute('admin.transactions.index'), icon: ReceiptText },
            { name: 'Reports', path: safeRoute('admin.reports'), icon: FileChartPie },
            { name: 'Charity', path: safeRoute('admin.charity'), icon: Heart },
            { name: 'Recurring', path: safeRoute('admin.recurring.index'), icon: Repeat },
            { name: 'Categories', path: safeRoute('admin.categories.index'), icon: Tag },
            { name: 'Others', path: safeRoute('admin.others'), icon: LayoutGrid },
            { name: 'Create User', path: safeRoute('admin.register'), icon: UserPlus },
        ]

        return isAdmin ? adminItems : userItems
    })

    return { navItems }
}