import { test, expect } from '@playwright/test';
import { loginAsAdmin } from './auth.helper.ts';

test('admin can login and access dashboard', async ({ page }) => {
    await loginAsAdmin(page);

    await expect(page.getByText('Admin User')).toBeVisible();
});