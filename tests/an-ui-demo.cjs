// Run against a disposable, seeded loopback app. Creates one Student enrollment/attempt.
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const crypto = require('node:crypto');
const { chromium } = require(process.env.QA_PLAYWRIGHT_MODULE || 'playwright');
const base = process.env.QA_BASE_URL;
if (!base || process.env.QA_ALLOW_WRITES !== '1' || !['127.0.0.1', 'localhost'].includes(new URL(base).hostname)) {
    throw new Error('Use QA_ALLOW_WRITES=1 and a disposable loopback app in QA_BASE_URL.');
}
const output = process.env.QA_OUTPUT || path.join('storage', 'an-ui-demo');
fs.mkdirSync(output, { recursive: true });
const password = process.env.QA_DEMO_PASSWORD || 'StudyMate2026!';
const engines = JSON.parse(process.env.QA_BROWSERS || '[]');
assert(engines.length, 'Set QA_BROWSERS to real browser executables.');
const files = ['app/views.php', 'app/layout.php', 'app/ai_view.php', 'public/assets/app.css', 'public/assets/forms.js'];
const report = {
    started: new Date().toISOString(),
    sourceFilesSha256: Object.fromEntries(files.map(file => [file, crypto.createHash('sha256').update(fs.readFileSync(file)).digest('hex')])),
    browsers: {}, checks: [], screenshots: [], errors: [],
    limitations: ['SQLite disposable QA only.', 'Gemini HTTP calls are disabled. AI response layout uses an explicitly named QA fixture when provided.', 'Automated browser viewports, not physical mobile devices.'],
};
function check(condition, name, browser) {
    assert(condition, name);
    report.checks.push({ browser, name });
}
async function login(page, role) {
    await page.goto(base + '/index.php?page=login');
    await page.locator('[name=email]').fill(role + '@studymate.test');
    await page.locator('[name=password]').fill(password);
    await Promise.all([page.waitForNavigation(), page.locator('form:has(input[value=login]) button').click()]);
}
async function submit(page, form) {
    await Promise.all([page.waitForNavigation(), form.locator('button').last().click()]);
}
async function snapshot(page, name, browser) {
    if (browser !== engines[0].name) return;
    const filename = name + '.png';
    await page.screenshot({ path: path.join(output, filename), fullPage: true });
    report.screenshots.push(filename);
}
(async () => {
    try {
        for (const engine of engines) {
            const browser = await chromium.launch({ executablePath: engine.path, headless: true });
            report.browsers[engine.name] = browser.version();
            try {
                const routes = {
                    guest: ['home', 'courses', 'detail&id=1', 'login', 'register'],
                    student1: ['student', 'learn&id=2', 'quiz&id=2', 'result&id=1', 'profile'],
                    instructor1: ['instructor', 'edit&id=3&tab=info', 'edit&id=3&tab=content', 'edit&id=3&tab=quiz', 'edit&id=2&tab=students'],
                    admin: ['admin&tab=users', 'admin&tab=courses', 'admin&tab=categories'],
                };
                for (const [role, pages] of Object.entries(routes)) {
                    const context = await browser.newContext();
                    const page = await context.newPage();
                    page.on('pageerror', error => report.errors.push(engine.name + ': ' + error.message));
                    if (role !== 'guest') await login(page, role);
                    for (const width of [390, 768, 1366]) {
                        await page.setViewportSize({ width, height: 900 });
                        for (const route of pages) {
                            const response = await page.goto(base + '/index.php?page=' + route);
                            await page.evaluate(() => document.fonts.ready);
                            const label = role + ' ' + route + ' ' + width;
                            check(response.status() === 200, label + ' HTTP 200', engine.name);
                            const text = await page.locator('body').innerText();
                            check(!/Warning:|Fatal error|Stack trace:/.test(text), label + ' no PHP diagnostics', engine.name);
                            check(await page.evaluate(() => document.documentElement.scrollWidth <= innerWidth), label + ' no page overflow', engine.name);
                            const unnamed = await page.locator('input:not([type=hidden]), textarea, select').evaluateAll(elements => elements.filter(element => !element.labels?.length && !element.getAttribute('aria-label') && !element.getAttribute('aria-labelledby')).length);
                            check(unnamed === 0, label + ' fields labelled', engine.name);
                            const undersized = await page.locator('.btn:not([disabled]), .lesson-link').evaluateAll(elements => elements.filter(element => element.getBoundingClientRect().width && element.getBoundingClientRect().height < 43.5).length);
                            check(undersized === 0, label + ' controls at least 44px', engine.name);
                            if (width !== 768) {
                                const name = ({ home:'home', courses:'courses', 'detail&id=1':'detail', 'learn&id=2':'learn', 'quiz&id=2':'quiz', 'result&id=1':'result' })[route];
                                if (name) await snapshot(page, name + '-' + width, engine.name);
                            }
                        }
                    }
                    if (role === 'admin') {
                        await page.setViewportSize({ width:390, height:844 });
                        await page.goto(base + '/index.php?page=admin&tab=users');
                        const table = page.locator('.table-responsive[tabindex="0"]');
                        let reached = false;
                        for (let attempt = 0; attempt < 30 && !reached; attempt++) {
                            await page.keyboard.press('Tab');
                            reached = await table.evaluate(element => element === document.activeElement);
                        }
                        check(reached, 'Admin table receives focus with Tab', engine.name);
                        await page.keyboard.press('ArrowRight');
                        await page.waitForFunction(() => document.querySelector('.table-responsive').scrollLeft > 0);
                        check(await table.evaluate(element => element.scrollLeft > 0), 'Admin table scrolls with keyboard', engine.name);
                    }
                    await page.goto(base + '/index.php?page=home');
                    await page.keyboard.press('Tab');
                    check(await page.locator('.skip-link').evaluate(element => element === document.activeElement), role + ' first Tab reaches skip link', engine.name);
                    await page.keyboard.press('Enter');
                    check(await page.locator('#main-content').evaluate(element => element === document.activeElement), role + ' skip link enters main content', engine.name);
                    await context.close();
                }
                const context = await browser.newContext({ viewport: { width: 390, height: 844 } });
                const page = await context.newPage();
                page.on('pageerror', error => report.errors.push(engine.name + ': ' + error.message));
                await page.goto(base + '/index.php?page=courses');
                await page.getByLabel('Tìm theo tên khóa học').fill('JavaScript');
                await submit(page, page.locator('.catalog-filter'));
                check(await page.locator('.course-card').count() === 1, 'search finds JavaScript', engine.name);
                check(await page.locator('.course-card h3').innerText() === 'JavaScript cơ bản', 'search result uses actual course title', engine.name);
                await page.getByLabel('Tìm theo tên khóa học').fill('không-có-khóa-này');
                await submit(page, page.locator('.catalog-filter'));
                check(await page.locator('.course-card').count() === 0 && await page.getByRole('heading', { name:'Chưa tìm thấy khóa học phù hợp' }).isVisible(), 'empty search explains recovery', engine.name);
                await page.getByRole('link', { name:'Xem tất cả khóa học', exact:true }).click();
                check(await page.locator('.course-card').count() >= 2, 'reset recovers complete catalog', engine.name);
                const contrast = await page.evaluate(() => {
                    function luminance(hex) {
                        const channels = hex.match(/\w\w/g).map(value => parseInt(value, 16) / 255);
                        const linear = channels.map(value => value <= .04045 ? value / 12.92 : ((value + .055) / 1.055) ** 2.4);
                        return linear[0] * .2126 + linear[1] * .7152 + linear[2] * .0722;
                    }
                    function ratio(first, second) {
                        const values = [luminance(first), luminance(second)].sort((a, b) => b - a);
                        return (values[0] + .05) / (values[1] + .05);
                    }
                    const styles = getComputedStyle(document.documentElement);
                    return ['--ink', '--muted', '--primary'].map(name => ratio(styles.getPropertyValue(name).trim().replace('#', ''), 'ffffff'));
                });
                check(contrast.every(ratio => ratio >= 4.5), 'main text, secondary text and primary color meet 4.5:1 on white', engine.name);
                await page.locator('.course-card h3').first().evaluate(element => { element.textContent = 'Nội dung khóa học dài '.repeat(12); });
                check(await page.evaluate(() => document.documentElement.scrollWidth <= innerWidth), 'synthetic long course title stays inside mobile grid', engine.name);
                await login(page, 'student2');
                await page.goto(base + '/index.php?page=detail&id=1');
                if (await page.locator('form:has(input[value=enroll])').count()) {
                    await submit(page, page.locator('form:has(input[value=enroll])'));
                }
                await page.goto(base + '/index.php?page=detail&id=1');
                check(await page.getByRole('link', { name:'Tiếp tục học', exact:true }).isVisible() && await page.locator('form:has(input[value=enroll])').count() === 0, 'enrolled Student sees continue action', engine.name);
                await page.getByRole('link', { name:'Tiếp tục học', exact:true }).click();
                const lessons = await page.locator('form:has(input[value=last_lesson]) [name=lesson_id]').evaluateAll(elements => elements.map(element => element.value));
                for (const lesson of lessons) {
                    await page.goto(base + '/index.php?page=learn&id=1&lesson=' + lesson);
                    const activeLesson = page.locator('.lesson-link[aria-current=step]');
                    await activeLesson.focus();
                    await Promise.all([page.waitForNavigation(), page.keyboard.press('Enter')]);
                    check(new URL(page.url()).searchParams.get('lesson') === lesson, 'lesson ' + lesson + ' opens by keyboard', engine.name);
                    const complete = page.locator('form:has(input[value=complete]) button');
                    if (await complete.isEnabled()) await submit(page, page.locator('form:has(input[value=complete])'));
                }
                check((await page.locator('.progress-meta').innerText()).includes('100%'), 'all lesson completion POSTs update progress', engine.name);
                check(await page.locator('.lesson-link[aria-current=step]').count() === 1, 'current lesson is announced', engine.name);
                for (const mode of ['ask', 'summarize']) {
                    await page.locator('form:has(input[value=ai]) textarea').fill(mode === 'ask' ? 'Giải thích ví dụ trong bài học' : '');
                    const request = page.waitForRequest(request => request.method() === 'POST');
                    await Promise.all([page.waitForNavigation(), page.locator('button[name=mode][value=' + mode + ']').click()]);
                    const fields = new URLSearchParams((await request).postData());
                    check(fields.get('action') === 'ai' && fields.get('mode') === mode && !!fields.get('csrf') && !!fields.get('lesson_id') && fields.get('course_id') === '1', mode + ' preserves AI contract and CSRF', engine.name);
                    check(await page.getByRole('alert').isVisible() && await page.locator('.lesson-text').isVisible(), mode + ' disabled AI leaves lesson usable', engine.name);
                }
                await page.goto(base + '/index.php?page=quiz&id=1');
                for (const question of await page.locator('.question').all()) await question.locator('input[type=radio]').first().check();
                const quiz = page.locator('form:has(input[value=submit_quiz])');
                check(!!await quiz.locator('[name=request_id]').inputValue() && !!await quiz.locator('[name=csrf]').inputValue(), 'quiz preserves request ID and CSRF', engine.name);
                await quiz.locator('[name=confirm]').check();
                await submit(page, quiz);
                check(new URL(page.url()).searchParams.get('page') === 'result' && await page.locator('.review-question').count() === 5, 'quiz POST renders server result and explanations', engine.name);
                if (process.env.QA_AI_PREVIEW_PATH) {
                    await page.goto(base + process.env.QA_AI_PREVIEW_PATH);
                    check(await page.locator('.ai-message').isVisible(), 'offline AI fixture response is readable', engine.name);
                    check(await page.evaluate(() => document.documentElement.scrollWidth <= innerWidth), 'long offline AI response stays inside page', engine.name);
                    const code = page.locator('.ai-message pre');
                    check(await code.getAttribute('tabindex') === '0', 'long code block is keyboard focusable', engine.name);
                    await code.focus();
                    await page.keyboard.press('ArrowRight');
                    await page.waitForFunction(() => document.querySelector('.ai-message pre').scrollLeft > 0);
                    check(await code.evaluate(element => element.scrollLeft > 0), 'long code block scrolls with keyboard', engine.name);
                    await snapshot(page, 'ai-offline-fixture-390', engine.name);
                }
                await context.close();
            } finally {
                await browser.close();
            }
        }
        assert.equal(report.errors.length, 0);
    } catch (error) {
        report.failure = error.stack;
        process.exitCode = 1;
        console.error(error);
    } finally {
        report.finished = new Date().toISOString();
        fs.writeFileSync(path.join(output, 'results.json'), JSON.stringify(report, null, 2) + '\n');
        console.log(JSON.stringify({ checks:report.checks.length, errors:report.errors.length, failure:!!report.failure }));
    }
})();
