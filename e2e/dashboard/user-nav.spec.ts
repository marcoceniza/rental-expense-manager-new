import { test, expect } from '@playwright/test'
import { loginAsUser, clickSidebarNav } from '../auth/auth.helper.js'

const userNavItems = [
    { label: 'Dashboard', url: /\/dashboard$/ },
    { label: 'Transactions', url: /\/transactions$/ },
    { label: 'Reports', url: /\/reports$/ },
    { label: 'Charity', url: /\/charity$/ },
]

test('user can access all sidebar pages for regular users', async ({ page }) => {
    await loginAsUser(page)

    for (const item of userNavItems) {
        await clickSidebarNav(page, item.label)
        await expect(page).toHaveURL(item.url)
    }
})
