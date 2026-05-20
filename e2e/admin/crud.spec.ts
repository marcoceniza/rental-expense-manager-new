import { test, expect } from '@playwright/test'
import { loginAsAdmin } from '../auth/auth.helper.js'

const uniqueSuffix = () => `${Date.now()}-${Math.floor(Math.random() * 1000)}`

test.describe('Admin CRUD actions', () => {
    test('admin can create, update and delete categories', async ({ page }) => {
        const categoryName = `Test Category ${uniqueSuffix()}`
        const updatedName = `${categoryName} Updated`

        await loginAsAdmin(page)
        await page.goto('/admin/categories')

        await page.getByRole('button', { name: 'Add Category' }).click()
        await page.getByPlaceholder('e.g., Maintenance, Rent, etc.').fill(categoryName)
        await page.getByRole('combobox', { name: 'Type' }).selectOption('expense')
        await page.getByLabel('Tuition Related').check()
        await page.getByRole('button', { name: 'Save' }).click()

        await expect(page.getByText(categoryName)).toBeVisible()

        const categoryCard = page.locator('div.group.relative', { hasText: categoryName }).first()
        await categoryCard.locator('button').first().click()

        await page.getByPlaceholder('e.g., Maintenance, Rent, etc.').fill(updatedName)
        await page.getByRole('button', { name: 'Save' }).click()

        await expect(page.getByText(updatedName)).toBeVisible()

        const updatedCard = page.locator('div.group.relative', { hasText: updatedName }).first()
        await updatedCard.locator('button').nth(1).click()
        await page.getByRole('button', { name: 'Yes, Delete' }).click()

        await expect(page.getByText(updatedName)).not.toBeVisible()
    })

    test('admin can create, update and delete transactions', async ({ page }) => {
        const categoryName = `Transaction Category ${uniqueSuffix()}`
        const transactionDescription = `Transaction ${uniqueSuffix()}`
        const updatedDescription = `${transactionDescription} Updated`

        await loginAsAdmin(page)
        await page.goto('/admin/categories')
        await page.getByRole('button', { name: 'Add Category' }).click()
        await page.getByPlaceholder('e.g., Maintenance, Rent, etc.').fill(categoryName)
        await page.getByRole('combobox', { name: 'Type' }).selectOption('income')
        await page.getByRole('button', { name: 'Save' }).click()
        await expect(page.getByText(categoryName)).toBeVisible()

        await page.goto('/admin/transactions')
        await page.getByRole('button', { name: 'Add Transaction' }).click()
        await page.getByLabel('Date').fill(new Date().toISOString().split('T')[0])
        await page.getByRole('combobox', { name: 'Type' }).selectOption('income')
        await page.getByRole('combobox', { name: 'Category' }).selectOption({ label: categoryName })
        await page.getByPlaceholder('e.g., Rent Payment - Ground Floor').fill(transactionDescription)
        await page.getByLabel('Amount (PHP)').fill('1234.56')
        await page.getByLabel('Remarks').fill('Playwright CRUD test')
        await page.getByRole('button', { name: 'Save' }).click()

        const transactionRow = page.locator('tr', { hasText: transactionDescription }).first()
        await expect(transactionRow).toBeVisible()

        await transactionRow.locator('button').first().click()
        await page.getByPlaceholder('e.g., Rent Payment - Ground Floor').fill(updatedDescription)
        await page.getByRole('button', { name: 'Update' }).click()

        const updatedRow = page.locator('tr', { hasText: updatedDescription }).first()
        await expect(updatedRow).toBeVisible()

        await updatedRow.locator('button').nth(1).click()
        await page.getByRole('button', { name: 'Yes, Delete' }).click()
        await expect(page.getByText(updatedDescription)).not.toBeVisible()
    })

    test('admin can create, update and delete recurring entries', async ({ page }) => {
        const categoryName = `Recurring Category ${uniqueSuffix()}`
        const recurringDescription = `Recurring ${uniqueSuffix()}`
        const updatedDescription = `${recurringDescription} Updated`

        await loginAsAdmin(page)
        await page.goto('/admin/categories')
        await page.getByRole('button', { name: 'Add Category' }).click()
        await page.getByPlaceholder('e.g., Maintenance, Rent, etc.').fill(categoryName)
        await page.getByRole('combobox', { name: 'Type' }).selectOption('expense')
        await page.getByRole('button', { name: 'Save' }).click()
        await expect(page.getByText(categoryName)).toBeVisible()

        await page.goto('/admin/recurring')
        await page.getByRole('button', { name: 'Add Recurring' }).click()
        await page.getByRole('combobox', { name: 'Category' }).selectOption({ label: `${categoryName} (expense)` })
        await page.getByLabel('Description').fill(recurringDescription)
        await page.getByLabel('Amount (PHP)').fill('987.65')
        await page.getByRole('button', { name: 'Save Recurring' }).click()

        const recurringCard = page.locator('div', { hasText: recurringDescription }).first()
        await expect(recurringCard).toBeVisible()

        await recurringCard.locator('button').first().click()
        await page.getByLabel('Description').fill(updatedDescription)
        await page.getByRole('button', { name: 'Update' }).click()

        const updatedCard = page.locator('div', { hasText: updatedDescription }).first()
        await expect(updatedCard).toBeVisible()

        page.once('dialog', (dialog) => dialog.accept())
        await updatedCard.locator('button').nth(1).click()
        await expect(page.getByText(updatedDescription)).not.toBeVisible()
    })
})
