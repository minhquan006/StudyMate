import { createRequire } from 'node:module';
import { mkdirSync, writeFileSync } from 'node:fs';
import { join } from 'node:path';
const require = createRequire(import.meta.url);
const { chromium } = require('playwright');
const base = process.env.STUDYMATE_TEST_BASE_URL;
const output = process.env.STUDYMATE_BROWSER_ARTIFACTS;
if (!base?.startsWith('http://127.0.0.1:') || !output) throw new Error('Disposable loopback server and output directory required');
mkdirSync(output, { recursive: true });
const browser = await chromium.launch({ executablePath: process.env.STUDYMATE_BROWSER_EXECUTABLE || undefined, headless: true });
const records = [];
try {
  const page = await browser.newPage({ viewport: { width: 1366, height: 900 } });
  await page.goto(base + '?page=login');
  await page.locator('[name=email]').fill('student1@studymate.test');
  await page.locator('[name=password]').fill('StudyMate2026!');
  await page.getByRole('button', { name: 'Đăng nhập', exact: true }).click();
  for (const mode of ['ask', 'summarize']) {
    await page.goto(base + '?page=learn&id=2&lesson=7');
    if (mode === 'ask') await page.locator('[name=question]').fill('Theo bài học, const và let khác nhau thế nào?');
    const responsePromise = page.waitForResponse(response => response.request().method() === 'POST');
    await page.locator(`button[name=mode][value=${mode}]`).click();
    const response = await responsePromise;
    const redirectStatus = response.status();
    if (redirectStatus !== 303) throw new Error(`Live ${mode} failed with app HTTP ${redirectStatus}`);
    await page.locator('.ai-message').waitFor({ timeout: 35_000 });
    const answer = await page.locator('.ai-message').innerText();
    if (!answer.trim()) throw new Error('Empty live answer');
    const screenshot = `${mode}-chrome.png`;
    await page.screenshot({ path: join(output, screenshot), fullPage: true });
    records.push({ mode, post_status: redirectStatus, rendered_answer: answer, screenshot, source_lesson_id: 7 });
    writeFileSync(join(output, 'browser-live.json'), JSON.stringify({ browser: browser.version(), records }, null, 2) + '\n');
  }
  process.stdout.write(JSON.stringify({ browser: browser.version(), records }));
} finally {
  await browser.close();
}
