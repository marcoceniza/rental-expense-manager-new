import { test, expect } from '@playwright/test';
import { loginAsUser } from './auth.helper.js';

test('user can login and access dashboard', async ({ page }) => {
    await loginAsUser(page);

    await expect(page.locator('text=Admin')).toHaveCount(0);
});