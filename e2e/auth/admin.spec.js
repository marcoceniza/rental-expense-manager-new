import { test, expect } from '@playwright/test';
import { loginAsAdmin } from './auth.helper.js';

test('admin can login and access dashboard', async ({ page }) => {
    await loginAsAdmin(page);

    await expect(page.getByTestId('user-name')).toHaveText('Admin User');
});