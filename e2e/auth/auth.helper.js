import { expect } from '@playwright/test'

export async function login(page, email, password) {
    await page.goto('/login')
    await page.waitForSelector('#email', { timeout: 20000 })
    await page.fill('#email', email)
    await page.fill('#password', password)
    await page.click('button[type="submit"]')
}

export async function loginAsAdmin(page) {
    await login(page, 'admin@example.com', 'admin123')
    await expect(page).toHaveURL(/admin\/dashboard/)
}

export async function loginAsUser(page) {
    await login(page, 'user@example.com', 'user1234')
    await expect(page).toHaveURL(/dashboard/)
}

export async function clickSidebarNav(page, label) {
    await page.getByRole('link', { name: label }).click()
}
