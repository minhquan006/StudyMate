import { createRequire } from 'node:module';
import { mkdirSync, writeFileSync } from 'node:fs';
import { join } from 'node:path';

const require = createRequire(import.meta.url);
const { chromium } = require('playwright');
const base = process.env.STUDYMATE_TEST_BASE_URL;
if (!base?.startsWith('http://127.0.0.1:')) throw new Error('A disposable loopback test server is required');
const output = process.env.STUDYMATE_BROWSER_ARTIFACTS;
if (output) mkdirSync(output, { recursive: true });
const report = { checks: [], screenshots: [], browser: null };
function check(condition, name) {
  if (!condition) throw new Error(name);
  report.checks.push(name);
}
const browser = await chromium.launch({
  executablePath: process.env.STUDYMATE_BROWSER_EXECUTABLE || undefined,
  headless: true,
});
report.browser = { version: browser.version(), executable: process.env.STUDYMATE_BROWSER_EXECUTABLE || 'Playwright Chromium' };
try {
  for (const [who, routes] of [
    ['admin', ['admin&tab=users', 'admin&tab=courses', 'admin&tab=categories']],
    ['instructor1', ['instructor', 'edit&id=2&tab=students', 'edit&id=3&tab=quiz']],
  ]) {
    const context = await browser.newContext();
    const page = await context.newPage();
    const errors = [];
    page.on('pageerror', error => errors.push(String(error)));
    await page.goto(base + '?page=login');
    await page.locator('[name=email]').fill(who + '@studymate.test');
    await page.locator('[name=password]').fill('StudyMate2026!');
    await page.getByRole('button', { name: 'Đăng nhập', exact: true }).click();
    for (const width of [390, 768, 1366]) {
      await page.setViewportSize({ width, height: 900 });
      for (const route of routes) {
        const response = await page.goto(base + '?page=' + route);
        check(response.status() === 200, `browser page ${route} ${width}`);
        check(await page.evaluate(() => document.documentElement.scrollWidth <= innerWidth), `no viewport overflow ${route} ${width}`);
        if (output) {
          const name = `${process.env.STUDYMATE_TEST_DRIVER}-${route.replaceAll('&', '-').replaceAll('=', '-')}-${width}.png`;
          await page.screenshot({ path: join(output, name), fullPage: true });
          report.screenshots.push(name);
        }
      }
    }
    if (who === 'admin') {
      await page.goto(base + '?page=admin&tab=users&q=page&per_page=5');
      await page.locator('a[rel=next]').click();
      check(page.url().includes('p=2') && page.url().includes('q=page'), 'browser pagination retains search');
      check(await page.locator('tbody tr').count() === 5, 'browser next page contains five users');
    }
    check(errors.length === 0, 'no browser script errors ' + who);
    await context.close();
  }
  if (output) writeFileSync(join(output, 'audit.json'), JSON.stringify(report, null, 2) + '\n');
  process.stdout.write(JSON.stringify(report));
} finally {
  await browser.close();
}
