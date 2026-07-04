import { expect, Page } from '@playwright/test'

const BASE_URL = process.env.PLAYWRIGHT_BASE_URL || 'http://127.0.0.1:8000'

export async function login(page: Page, email: string, password: string) {
    await page.goto(`${BASE_URL}/login`, { waitUntil: 'networkidle' })
    await page.waitForSelector('#email', { timeout: 20000 })
    await page.fill('#email', email)
    await page.fill('#password', password)
    await page.click('button[type="submit"]')
    // Wait for Inertia to navigate away from login page
    await page.waitForURL((url: URL) => !url.pathname.includes('/login'), { timeout: 10000 })
}

export async function loginAsAdmin(page: Page) {
    await login(page, 'admin@example.com', 'admin123')
    await expect(page).toHaveURL(/admin\/dashboard/)
}

export async function loginAsUser(page: Page) {
    await login(page, 'user@example.com', 'user1234')
    await expect(page).toHaveURL(/dashboard/)
}

export async function clickSidebarNav(page: Page, label: string) {
    await page.getByRole('link', { name: label }).click()
}
