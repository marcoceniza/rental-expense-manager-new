import { computed } from 'vue'
import { usePage } from '@inertiajs/vue3'
import { route } from 'ziggy-js'
import {
    LayoutDashboard,
    ReceiptText,
    Repeat,
    FileChartPie,
    Tag,
    LayoutGrid,
    Heart,
} from 'lucide-vue-next'

export function useNavItems() {
    const page = usePage()

    const navItems = computed(() => {
        const user = page.props.auth?.user
        const isAdmin = user?.user_type === 'admin'

        const userItems = [
            { name: 'Dashboard', path: route('dashboard'), icon: LayoutDashboard },
            { name: 'Transactions', path: route('transactions.user'), icon: ReceiptText },
            { name: 'Reports', path: route('reports'), icon: FileChartPie },
            { name: 'Charity', path: route('charity'), icon: Heart },
        ]

        const adminItems = [
            { name: 'Dashboard', path: route('admin.dashboard'), icon: LayoutDashboard },

            // FIX: resource route
            { name: 'Transactions', path: route('transactions.index'), icon: ReceiptText },

            { name: 'Reports', path: route('admin.reports'), icon: FileChartPie },
            { name: 'Charity', path: route('admin.charity'), icon: Heart },

            // FIX: you defined this manually as admin.recurring
            { name: 'Recurring', path: route('admin.recurring'), icon: Repeat },

            { name: 'Categories', path: route('categories.index'), icon: Tag },

            { name: 'Others', path: route('admin.others'), icon: LayoutGrid },

            { name: 'Create User', path: route('admin.register'), icon: LayoutGrid },
        ]

        return isAdmin ? adminItems : userItems
    })

    return { navItems }
}