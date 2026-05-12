import { computed } from 'vue'
import { usePage } from '@inertiajs/vue3'
import { route } from 'ziggy-js'
import{ LayoutDashboard, ReceiptText, Repeat, FileChartPie, Tag, LayoutGrid, Heart, UserPlus } from 'lucide-vue-next'
import { SharedData } from '@/types'

export function useNavItems() {
    const page = usePage<SharedData>()

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
            { name: 'Transactions', path: route('admin.transactions.index'), icon: ReceiptText },
            { name: 'Reports', path: route('admin.reports'), icon: FileChartPie },
            { name: 'Charity', path: route('admin.charity'), icon: Heart },
            { name: 'Recurring', path: route('admin.recurring'), icon: Repeat },
            { name: 'Categories', path: route('admin.categories.index'), icon: Tag },
            { name: 'Others', path: route('admin.others'), icon: LayoutGrid },
            { name: 'Create User', path: route('admin.register'), icon: UserPlus },
        ]

        return isAdmin ? adminItems : userItems
    })

    return { navItems }
}