import { createRequire } from 'node:module';
import { mkdir, writeFile } from 'node:fs/promises';
import assert from 'node:assert/strict';
import { execFileSync } from 'node:child_process';
const { chromium } = createRequire(import.meta.url)('playwright');
const base = process.env.QA_BASE_URL || 'http://127.0.0.1:18083';
const output = process.env.QA_OUTPUT || '/tmp/studymate-ui-evidence';
const password = process.env.QA_DEMO_PASSWORD;
assert(password, 'QA_DEMO_PASSWORD must match the disposable seed');
await mkdir(output, { recursive: true });
export async function login(page, role) {
  await page.goto(`${base}/index.php?page=login`);
  await page.getByLabel('Email', { exact: true }).fill(`${role === 'student' ? 'student1' : role === 'instructor' ? 'instructor1' : 'admin'}@studymate.test`);
  await page.getByLabel('Mật khẩu', { exact: true }).fill(password);
  await page.locator('form:has(input[value="login"]) button').click();
  assert.equal(new URL(page.url()).searchParams.get('page'), role);
}
const browsers = [ ['Chrome', process.env.QA_CHROME], ['Edge', process.env.QA_EDGE] ];
const result = { recordedAt: new Date().toISOString(), sourceSha: execFileSync('git', ['rev-parse','HEAD'],{encoding:'utf8'}).trim(), base, checks: [], functionalChecks: [], browsers: [], failures: [] };
for (const [name, executablePath] of browsers) {
  assert(executablePath, `Set the actual ${name} executable`);
  const browser = await chromium.launch({ executablePath });
  result.browsers.push({ name, version: browser.version(), executablePath });
  try {
    for (const role of ['public','student','instructor','admin']) {
      const context = await browser.newContext();
      const page = await context.newPage();
      if (role !== 'public') await login(page, role);
      const routes = role === 'public' ? ['home','courses','detail&id=1','login','register'] : role === 'student' ? ['student','profile','learn&id=2','quiz&id=2','result&id=1'] : role === 'instructor' ? ['instructor','edit&id=3&tab=info','edit&id=3&tab=content','edit&id=3&tab=quiz','edit&id=2&tab=students'] : ['admin&tab=users','admin&tab=courses','admin&tab=categories'];
      for (const width of [390,768,1366]) {
        await page.setViewportSize({ width, height: 900 });
        for (const route of routes) {
          const response = await page.goto(`${base}/index.php?page=${route}`);
          const metrics = await page.evaluate(() => ({
            width: innerWidth, documentWidth: document.documentElement.scrollWidth,
            unlabeled: [...document.querySelectorAll('input:not([type=hidden]),textarea,select')].filter(e=>e.getClientRects().length && !e.labels?.length && !e.getAttribute('aria-label')).map(e=>e.name),
            smallButtons: [...document.querySelectorAll('button,.btn[href]')].filter(e=>e.getClientRects().length && e.getBoundingClientRect().height < 43.5).map(e=>e.textContent.trim()),
            scrollRegions: [...document.querySelectorAll('.table-responsive')].map(e=>({scroll:e.scrollWidth>e.clientWidth,keyboard:e.tabIndex>=0}))
          }));
          const check = { browser:name,role,width,route,status:response.status(),...metrics };
          result.checks.push(check);
          if (response.status() !== 200 || metrics.documentWidth>width+1 || metrics.unlabeled.length || metrics.smallButtons.length || metrics.scrollRegions.some(r=>r.scroll && !r.keyboard)) result.failures.push(check);
          await page.keyboard.press('Tab');
          const focus = await page.evaluate(()=>({tag:document.activeElement.tagName,outline:getComputedStyle(document.activeElement).outlineStyle}));
          if(focus.tag==='BODY' || focus.outline==='none') result.failures.push({browser:name,role,width,route,focus});
          if(name==='Chrome') await page.screenshot({ path:`${output}/${role}-${width}-${route.replaceAll(/[^a-z0-9]/gi,'-')}.png` });
        }
      }
      if(role==='student' || role==='instructor') {
        await page.goto(`${base}/index.php?page=${role}&p=2`);
        assert.equal(await page.locator('.course-card,tbody tr').count(), role==='student'?2:Number(process.env.QA_INSTRUCTOR_TOTAL || 16)-12);
        assert.match(await page.locator('.pager').innerText(),/Trang 2 \/ 2/);
        const total=await page.locator('.stat').first().innerText();
        assert.equal(await page.locator('.stat strong').first().innerText(),String(role==='student'?14:Number(process.env.QA_INSTRUCTOR_TOTAL || 16)));
        await page.goto(`${base}/index.php?page=${role}&p=999`);
        assert.match(await page.locator('.pager').innerText(),/Trang 2 \/ 2/);
      }
      if(role==='student') {
        await page.goto(`${base}/index.php?page=student&q=AN%20QA&p=2`);
        assert.equal(await page.locator('.course-card').count(),1);
        assert.equal(await page.locator('.stat strong').first().innerText(),'14');
        assert.match(await page.locator('.pager a').first().getAttribute('href'),/q=AN/);
        await page.goto(`${base}/index.php?page=student&q=absent-${Date.now()}`);
        assert.match(await page.locator('main').innerText(),/Không có khóa học phù hợp/);
      }
      result.functionalChecks.push({browser:name,role,status:'passed',scope: role==='public'?'responsive/labels/focus/buttons':'pagination/aggregate/filter/empty plus responsive audit'});
      if(role==='admin') {
        await page.goto(`${base}/index.php?page=admin&tab=users&q=AN%20QA&p=2`);
        assert.equal(await page.locator('tbody tr').count(),1);
        assert.match(await page.locator('.pager').innerText(),/Trang 2 \/ 2/);
        assert.match(await page.locator('.pager a').first().getAttribute('href'),/q=AN/);
        await page.goto(`${base}/index.php?page=admin&tab=users&q=none-${Date.now()}`);
        assert.match(await page.locator('main').innerText(),/Không có kết quả phù hợp/);
      }
      await context.close();
    }
  } finally { await browser.close(); }
}
await writeFile(`${output}/audit.json`,JSON.stringify(result,null,2)+'\n');
console.log(JSON.stringify({ browsers: result.browsers.map(({name,version})=>({name,version})),screens:result.checks.length,failures:result.failures },null,2));
assert.equal(result.failures.length,0,'UI audit failed; inspect audit.json');
