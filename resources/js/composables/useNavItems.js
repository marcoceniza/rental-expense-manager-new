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

        const items = [
            { name: 'Dashboard', path: '/dashboard', icon: LayoutDashboard },
            { name: 'Transactions', path: '/transactions', icon: ReceiptText },
            { name: 'Reports', path: '/reports', icon: FileChartPie },
            { name: 'Charity', path: '/charity', icon: Heart },
        ]

        if (isAdmin) {
            items.push(
                { name: 'Recurring', path: '/recurring', icon: Repeat },
                { name: 'Categories', path: '/categories', icon: Tag },
                { name: 'Others', path: '/others', icon: LayoutGrid },
            )
        }

        return items
    })

    return { navItems }
}