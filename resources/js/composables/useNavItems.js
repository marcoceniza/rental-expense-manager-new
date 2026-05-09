import { computed } from 'vue'
import { usePage } from '@inertiajs/vue3'
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
        // Inertia shared auth (Laravel backend)
        const user = page.props.auth?.user
        const isAdmin = user?.user_type === 'admin'

        const userItems = [
            { name: 'Dashboard', path: '/dashboard', icon: LayoutDashboard },
            { name: 'Transactions', path: '/transactions', icon: ReceiptText },
            { name: 'Reports', path: '/reports', icon: FileChartPie },
            { name: 'Charity', path: '/charity', icon: Heart },
        ]

        const adminItems = [
            { name: 'Dashboard', path: '/admin/dashboard', icon: LayoutDashboard },
            { name: 'Transactions', path: '/admin/transactions', icon: ReceiptText },
            { name: 'Reports', path: '/admin/reports', icon: FileChartPie },
            { name: 'Charity', path: '/admin/charity', icon: Heart },
            { name: 'Recurring', path: '/admin/recurring', icon: Repeat },
            { name: 'Categories', path: '/admin/categories', icon: Tag },
            { name: 'Others', path: '/admin/others', icon: LayoutGrid },
            { name: 'Create User', path: '/admin/register', icon: LayoutGrid },
        ]

        return isAdmin ? adminItems : userItems
    })

    return { navItems }
}