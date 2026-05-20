import { test, expect } from '@playwright/test';
import { loginAsUser } from './auth.helper.js';

test('user cannot access admin page', async ({ page }) => {
    await loginAsUser(page);

    // now try accessing admin route manually
    const response = await page.goto('http://127.0.0.1:8000/admin/dashboard');

    // check HTTP status (THIS is the real check)
    expect(response.status()).toBe(403);
});