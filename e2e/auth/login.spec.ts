import { test, expect } from '@playwright/test';
import { loginAsAdmin } from './auth.helper.ts';

test('admin can login successfully', async ({ page }) => {
    await loginAsAdmin(page);
});