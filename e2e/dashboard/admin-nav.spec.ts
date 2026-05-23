import { test, expect } from '@playwright/test'
import { loginAsAdmin, clickSidebarNav } from '../auth/auth.helper.ts'

const adminNavItems = [
    { label: 'Dashboard', url: /\/admin\/dashboard$/ },
    { label: 'Transactions', url: /\/admin\/transactions$/ },
    { label: 'Reports', url: /\/admin\/reports$/ },
    { label: 'Charity', url: /\/admin\/charity$/ },
    { label: 'Recurring', url: /\/admin\/recurring$/ },
    { label: 'Categories', url: /\/admin\/categories$/ },
    { label: 'Others', url: /\/admin\/others$/ },
    { label: 'Create User', url: /\/admin\/register$/ },
]

test('admin can access all sidebar pages for admin users', async ({ page }) => {
    await loginAsAdmin(page)

    for (const item of adminNavItems) {
        await clickSidebarNav(page, item.label)
        await expect(page).toHaveURL(item.url)
    }
})
