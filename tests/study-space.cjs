// Browser integration tests. Run only against a disposable seeded loopback app.
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const crypto = require('node:crypto');
const { chromium } = require(process.env.QA_PLAYWRIGHT_MODULE || 'playwright');
const base = process.env.QA_BASE_URL;
assert(base && process.env.QA_ALLOW_WRITES === '1' && ['localhost','127.0.0.1'].includes(new URL(base).hostname), 'Use a disposable loopback QA app.');
const engines = JSON.parse(process.env.QA_BROWSERS || '[]'); assert(engines.length);
const out = process.env.QA_OUTPUT || 'storage/study-space-qa'; fs.mkdirSync(out, { recursive:true });
const files = ['app/bootstrap.php','app/layout.php','app/views.php','app/study_space_view.php','public/assets/study-space.js','public/assets/study-space.css','public/assets/spaces/forest-motion.webm'];
const report = { started:new Date().toISOString(), sourceFilesSha256:Object.fromEntries(files.map(f => [f,crypto.createHash('sha256').update(fs.readFileSync(f)).digest('hex')])), checks:[], errors:[], screenshots:[], browsers:{}, liveYoutube:[], limitations:['Disposable SQLite on Windows; not MySQL/Docker or real Gemini.', 'Browser viewports, not physical phones.', 'Web Audio source/decode/play/volume inspected automatically; not a listening-quality review.'] };
const password = process.env.QA_DEMO_PASSWORD || 'StudyMate2026!';
function check(ok, name, browser) { assert(ok, name); report.checks.push({ browser, name }); }
async function login(p, user = 'student1') {
  await p.goto(base + '/index.php?page=login');
  await p.getByLabel('Email', { exact:true }).fill(user + '@studymate.test');
  await p.getByLabel('Mật khẩu', { exact:true }).fill(password);
  await Promise.all([p.waitForNavigation(),p.locator('form:has(input[value=login]) button').click()]);
}
async function submit(p, form) { await Promise.all([p.waitForNavigation(),form.locator('button').last().press('Enter')]); }
async function openSpace(p) { if (await p.locator('#study-space').isHidden()) await p.locator('#study-space-toggle').click(); }
async function shot(p, name, engine) {
  if (engine !== engines[0].name) return;
  await p.screenshot({ path:path.join(out,name + '.png'), fullPage:true }); report.screenshots.push(name + '.png');
}
async function audioObserver(context) {
  await context.addInitScript(() => {
    window.__spaceQA = { starts:0, stops:0, pcm:[], targets:[] };
    const C = window.AudioContext || window.webkitAudioContext;
    if (!C) return;
    const create = C.prototype.createBufferSource;
    C.prototype.createBufferSource = function(...args) {
      const source = create.apply(this,args), start = source.start.bind(source), stop = source.stop.bind(source);
      source.start = (...args) => {
        window.__spaceQA.starts++;
        const data = source.buffer.getChannelData(0);
        window.__spaceQA.pcm.push({ seconds:source.buffer.duration, nonzero:data.slice(0,5000).some(x => Math.abs(x) > .00001) });
        return start(...args);
      };
      source.stop = (...args) => { window.__spaceQA.stops++; return stop(...args); };
      return source;
    };
    const createGain = C.prototype.createGain;
    C.prototype.createGain = function(...args) {
      const gain = createGain.apply(this,args), set = gain.gain.setTargetAtTime.bind(gain.gain);
      gain.gain.setTargetAtTime = (...args) => { window.__spaceQA.targets.push(args[0]); return set(...args); };
      return gain;
    };
  });
}
(async () => {
  try {
    for (const engine of engines) {
      const b = await chromium.launch({ executablePath:engine.path, headless:true }); report.browsers[engine.name] = b.version();
      try {
        const c = await b.newContext({ viewport:{ width:1366,height:900 } }); await audioObserver(c);
        const p = await c.newPage(); p.on('pageerror',e => report.errors.push(engine.name + ': ' + e.message));
        let youtubeRequests = 0; p.on('request',r => { if (/https:\/\/(www\.youtube\.com|www\.youtube-nocookie\.com|s\.ytimg\.com)/.test(r.url())) youtubeRequests++; });
        const home = await p.goto(base + '/index.php?page=home');
        check(home.headers()['referrer-policy'] === 'same-origin' && !home.headers()['content-security-policy'].includes('youtube.com/iframe_api'), 'public pages retain restrictive script/referrer policy', engine.name);
        await login(p); const auth = await c.storageState();
        const learn = await p.goto(base + '/index.php?page=learn&id=2&lesson=7');
        check(learn.headers()['referrer-policy'] === 'strict-origin-when-cross-origin' && learn.headers()['content-security-policy'].includes('https://www.youtube.com/iframe_api') && !learn.headers()['content-security-policy'].includes("'unsafe-eval'"), 'study page allows only explicit official player script paths and origin referrer', engine.name);
        check(await p.locator('#study-space').isHidden() && await p.locator('.lesson-text').isVisible(), 'space starts optional and lesson remains usable', engine.name);
        await openSpace(p);
        check(await p.locator('#study-space-toggle').getAttribute('aria-expanded') === 'true', 'space toggle announces expanded state', engine.name);
        await p.getByLabel('Trong rừng', { exact:true }).check();
        check(await p.locator('body').getAttribute('data-study-scene') === 'forest', 'scene radio changes actual page background', engine.name);
        await p.waitForFunction(() => document.querySelector('#space-scene-video').currentTime > .3);
        check(await p.locator('#space-scene-video').evaluate(v => v.videoWidth === 1920 && v.videoHeight === 1080 && v.muted && v.loop && v.playsInline), 'local HD scenery video actually decodes and plays muted', engine.name);
        const motionTime = await p.locator('#space-scene-video').evaluate(v => v.currentTime);
        await p.waitForFunction(t => document.querySelector('#space-scene-video').currentTime > t + .4, motionTime);
        check(true, 'scenery video advances rather than displaying a still', engine.name);
        await p.locator('#space-motion').uncheck();
        check(await p.locator('#space-scene-video').evaluate(v => v.paused && !v.hasAttribute('src')), 'turning motion off releases video source', engine.name);
        await p.locator('#space-motion').check();
        await p.emulateMedia({ reducedMotion:'reduce' });
        await p.waitForFunction(() => document.querySelector('#space-motion').disabled);
        check(await p.locator('#space-motion').isDisabled() && await p.locator('#space-scene-video').evaluate(v => v.paused && !v.hasAttribute('src')), 'system reduced motion stops video and disables effects', engine.name);
        await p.emulateMedia({ reducedMotion:'no-preference' });
        await p.waitForFunction(() => document.querySelector('#space-scene-video').currentTime > .2);
        check(await p.locator('body').getAttribute('class').then(c => c.includes('study-space-motion')), 'motion preference resumes when reduction is lifted', engine.name);

        for (const width of [390,768,1366]) {
          await p.setViewportSize({ width,height:900 });
          for (const tab of ['Không gian','Âm thanh','Nhạc']) {
            await p.getByRole('tab', { name:tab, exact:true }).click();
            check(await p.evaluate(() => document.documentElement.scrollWidth <= innerWidth), tab + ' ' + width + ' no page overflow', engine.name);
            check(await p.locator('[role=tabpanel]:visible').count() === 1, tab + ' ' + width + ' exactly one tool panel visible', engine.name);
            if (width !== 768 || tab === 'Không gian') await shot(p, ({'Không gian':'scene','Âm thanh':'sound','Nhạc':'music'})[tab] + '-' + width, engine.name);
          }
        }
        await p.getByRole('tab', { name:'Không gian', exact:true }).focus(); await p.keyboard.press('ArrowRight');
        check(await p.getByRole('tab', { name:'Âm thanh', exact:true }).getAttribute('aria-selected') === 'true', 'tool tabs work with arrow keys', engine.name);
        check(youtubeRequests === 0, 'no YouTube request before explicit valid music load', engine.name);
        check(await p.locator('.ambient-icon svg[aria-hidden=true]').count() === 4, 'four decorative sound icons have no extra screen reader noise', engine.name);
        await p.getByRole('button', { name:'Bật âm nền', exact:true }).click();
        await p.waitForFunction(() => window.__spaceQA.starts === 1);
        check(await p.evaluate(() => window.__spaceQA.pcm[0].seconds > 40 && window.__spaceQA.pcm[0].nonzero), 'real local rain recording decodes and starts after user gesture', engine.name);
        for (const kind of ['wind','pink','brown']) await p.locator('[data-ambient=' + kind + ']').check();
        await p.waitForFunction(() => window.__spaceQA.starts === 4);
        check(await p.evaluate(() => window.__spaceQA.pcm.every(p => p.nonzero)), 'four selected ambient channels contain actual signal', engine.name);
        check(await p.locator('.ambient-row.is-playing').count() === 4, 'active sound indicators reflect actual live channels', engine.name);
        await p.locator('#space-master-volume').press('Home');
        check(await p.evaluate(() => window.__spaceQA.targets.slice(-4).every(v => v === 0)), 'master volume mutes all ambient output gains', engine.name);
        check(await p.locator('.ambient-row.is-playing').count() === 0, 'muted channels stop showing active animation', engine.name);
        await p.locator('#space-master-volume').press('End'); await p.locator('[data-ambient-volume=wind]').press('Home'); await p.locator('[data-ambient-volume=wind]').press('ArrowRight');
        check(await p.evaluate(() => JSON.parse(localStorage.getItem('studymate:space:v1:1')).sounds.wind.volume === 1), 'per-channel and master levels persist', engine.name);
        check(await p.locator('#ambient-value-wind').innerText() === '1%', 'sound volume percentage follows keyboard adjustment', engine.name);
        await p.getByRole('button', { name:'Tạm dừng âm nền', exact:true }).click();
        check(await p.evaluate(() => window.__spaceQA.starts === window.__spaceQA.stops), 'pause stops every live ambient source', engine.name);
        await p.reload();
        check(await p.evaluate(() => window.__spaceQA.starts === 0) && await p.locator('body').getAttribute('data-study-scene') === 'forest', 'reload restores scene/settings without autoplaying ambience', engine.name);
        await p.getByRole('tab', { name:'Nhạc', exact:true }).click();
        for (const url of ['javascript:alert(1)','https://youtube.com.evil.test/watch?v=M7lc1UVf-VE','https://youtube.com@evil.test/watch?v=M7lc1UVf-VE','https://example.com/M7lc1UVf-VE','http://youtu.be/M7lc1UVf-VE','https://www.youtube.com/watch?v=short','https://www.youtube.com/playlist?list=abc','https://youtu.be/M7lc1UVf-VE/extra']) {
          await p.locator('#space-youtube-url').fill(url); await p.locator('#space-music-save').click();
          check(await p.locator('.space-favorite-row').count() === 0 && await p.locator('#space-player-shell iframe').count() === 0, 'rejects unsafe/non-video link: ' + url, engine.name);
        }
        check(youtubeRequests === 0, 'invalid links never contact YouTube', engine.name);
        await p.locator('#space-youtube-url').fill('https://youtu.be/M7lc1UVf-VE'); await p.locator('#space-music-name').fill('<img src=x onerror=alert(1)>'); await p.locator('#space-music-save').click();
        check(await p.locator('.favorite-select').innerText() === '<img src=x onerror=alert(1)>' && await p.locator('#space-favorite-list img').count() === 0, 'favorite name renders as text, not markup', engine.name);
        await p.locator('#space-music-save').click(); check(await p.locator('.space-favorite-row').count() === 1, 'favorite deduplicates equivalent video links', engine.name);
        for (const id of ['aaaaaaaaaaa','bbbbbbbbbbb','ccccccccccc','ddddddddddd','eeeeeeeeeee']) { await p.locator('#space-youtube-url').fill('https://youtu.be/' + id); await p.locator('#space-music-save').click(); }
        check(await p.locator('.space-favorite-row').count() === 5 && (await p.locator('#space-music-status').innerText()).includes('5 video'), 'favorites cap at five without silent replacement', engine.name);
        while (await p.locator('.favorite-remove').count()) await p.locator('.favorite-remove').first().click();
        await p.locator('#space-youtube-url').fill('https://youtu.be/M7lc1UVf-VE'); await p.locator('#space-music-name').fill('Video mẫu YouTube'); await p.locator('#space-music-save').click();
        await p.reload(); check(await p.locator('.favorite-select').innerText() === 'Video mẫu YouTube' && await p.locator('#space-player-shell iframe').count() === 0, 'favorites survive reload without loading a hidden player', engine.name);
        await p.locator('.favorite-select').click();
        await p.waitForFunction(() => !document.querySelector('#space-music-play').disabled || /không|chưa|Không|Chưa/.test(document.querySelector('#space-music-status').textContent), {}, { timeout:25000 });
        if (await p.locator('#space-music-play').isEnabled()) {
          const frame = p.locator('#space-player-shell iframe');
          check(new URL(await frame.getAttribute('src')).hostname === 'www.youtube-nocookie.com' && await frame.evaluate(e => e.getBoundingClientRect().width >= 200 && e.getBoundingClientRect().height >= 200), 'live official YouTube player is visible at minimum dimensions', engine.name);
          await p.locator('#space-music-play').click();
          await p.waitForFunction(() => /Đang phát nhạc|không|Không|Hãy bấm/.test(document.querySelector('#space-music-status').textContent), {}, { timeout:25000 }).catch(() => {});
          const status = await p.locator('#space-music-status').innerText(); report.liveYoutube.push({ browser:engine.name, status });
          if (status.includes('Đang phát nhạc')) {
            check(true, 'official sample video actually enters playing state', engine.name);
            const originalFrame = await frame.elementHandle();
            await p.setViewportSize({ width:390,height:900 }); await p.locator('.lesson-panel').scrollIntoViewIfNeeded();
            check(await p.locator('#space-music-status').innerText() === 'Đang phát nhạc YouTube.' && await originalFrame.evaluate(e => e.isConnected && e === document.querySelector('#space-player-shell iframe')), 'mobile scroll keeps the same playing YouTube iframe', engine.name);
            check(await p.locator('#space-player-shell').evaluate(e => { const r=e.getBoundingClientRect(); return getComputedStyle(e).position==='fixed' && r.left>=0 && r.right<=innerWidth && r.top>=0 && r.bottom<=innerHeight; }), 'floating player stays inside mobile viewport', engine.name);
            await p.getByRole('tab',{name:'Âm thanh',exact:true}).click();
            check(await p.locator('#space-music-status').innerText() === 'Đang phát nhạc YouTube.' && await originalFrame.evaluate(e => e.isConnected), 'switching to sound mixer keeps music playing in visible dock', engine.name);
            await shot(p,'floating-player-mobile',engine.name);
          } else report.limitations.push(engine.name + ': live video did not enter playing state: ' + status);
          await p.setViewportSize({ width:1366,height:900 }); await shot(p,'youtube-live',engine.name);
        } else report.liveYoutube.push({ browser:engine.name, status:await p.locator('#space-music-status').innerText() });
        await p.getByRole('tab', { name:'Không gian', exact:true }).click();
        if (await p.locator('#space-player-shell iframe').count()) {
          check(await p.locator('#space-player-shell').isVisible(), 'player remains visible across tool tabs', engine.name);
          await p.locator('#space-player-close').click();
          check(await p.locator('#space-player-shell iframe').count() === 0 && await p.locator('#space-player-shell').isHidden(), 'explicit close destroys YouTube player and stops media', engine.name);
        }
        await p.getByRole('link', { name:'Danh sách bài học', exact:true }).click(); check(await p.locator('.lesson-list-wrap').getAttribute('open') !== null, 'course outline remains reachable in space mode', engine.name);
        const next = p.locator('.lesson-actions form:has(input[value=last_lesson])'); check(await next.count() === 1, 'non-final fixture lesson offers next action', engine.name); await submit(p,next);
        check(new URL(p.url()).searchParams.get('lesson') !== null && await p.locator('#study-space').isVisible(), 'real next-lesson POST preserves enabled space', engine.name);
        check(await p.evaluate(() => window.__spaceQA.starts === 0) && await p.locator('#space-player-shell iframe').count() === 0, 'navigation never auto-resumes media', engine.name);
        await p.locator('#space-close').click();
        check(await p.locator('#study-space').isHidden() && await p.locator('#study-space-toggle').evaluate(e => e === document.activeElement) && await p.locator('.lesson-link[aria-current=step]').isVisible(), 'closing space restores ordinary outline and keyboard focus', engine.name);
        await p.goto(base + '/index.php?page=quiz&id=2'); check(await p.locator('#study-space-toggle').count() === 0 && await p.locator('.question').count() === 5, 'quiz remains a separate native workflow', engine.name);
        const savedAccountState = await c.storageState(); await c.close();

        const timed = await b.newContext({ storageState:auth, viewport:{ width:1366,height:900 } }); const timer = await timed.newPage(); await timer.clock.install();
        await timer.goto(base + '/index.php?page=learn&id=2&lesson=7'); await openSpace(timer); await timer.locator('#space-timer-start').click(); await timer.clock.fastForward(61000);
        check(/^23:5[5-9]$/.test(await timer.locator('#space-clock').innerText()), 'countdown uses elapsed time, not interval tick count', engine.name);
        await submit(timer,timer.locator('.lesson-actions form:has(input[value=last_lesson])'));
        check((await timer.locator('#space-clock').innerText()).startsWith('23:') && await timer.locator('#space-timer-start').innerText() === 'Tạm dừng', 'running countdown survives full PHP lesson navigation', engine.name);
        await timer.locator('#space-timer-start').click(); const paused = await timer.locator('#space-clock').innerText(); await timer.clock.fastForward(10000);
        check(await timer.locator('#space-clock').innerText() === paused, 'paused countdown does not advance', engine.name);
        await timer.locator('#space-timer-mode').selectOption('stopwatch'); await timer.locator('#space-timer-start').click(); await timer.clock.fastForward(12000);
        check(/^00:1[2-5]$/.test(await timer.locator('#space-clock').innerText()), 'stopwatch counts elapsed seconds', engine.name);
        await timer.locator('#space-timer-reset').click(); check(await timer.locator('#space-clock').innerText() === '00:00', 'stopwatch reset clears time and stops', engine.name);
        await timer.locator('#space-timer-mode').selectOption('break'); check(await timer.locator('#space-clock').innerText() === '05:00', 'short break preset is five minutes', engine.name);
        await timer.locator('#space-timer-mode').selectOption('focus'); await timer.locator('#space-timer-start').click(); await timer.clock.fastForward(25 * 60000 + 1000);
        check(await timer.locator('#space-clock').innerText() === '00:00' && (await timer.locator('#space-timer-status').innerText()).includes('đã kết thúc'), 'countdown finishes once at zero and announces completion', engine.name);
        await timed.close();

        const denied = await b.newContext({ storageState:auth }); await denied.addInitScript(() => { Storage.prototype.setItem = () => { throw new DOMException('Denied','SecurityError'); }; });
        const dp = await denied.newPage(); await dp.goto(base + '/index.php?page=learn&id=2&lesson=7'); await openSpace(dp);
        check((await dp.locator('#space-storage-status').innerText()).includes('chưa lưu được') && await dp.locator('#space-clock').innerText() === '25:00', 'blocked localStorage leaves tools usable with clear feedback', engine.name); await denied.close();
        const plain = await b.newContext({ storageState:auth, javaScriptEnabled:false }); const np = await plain.newPage(); await np.goto(base + '/index.php?page=learn&id=2&lesson=7');
        check(await np.locator('#study-space-toggle').isHidden() && await np.locator('.lesson-text').isVisible() && await np.locator('.lesson-link[aria-current=step]').isVisible(), 'without JavaScript ordinary lesson and native outline remain usable', engine.name); await plain.close();
        const corrupt = await b.newContext({ storageState:auth });
        await corrupt.addInitScript(() => localStorage.setItem('studymate:space:v1:1','{broken-json'));
        const cp = await corrupt.newPage(); await cp.goto(base + '/index.php?page=learn&id=2&lesson=7'); await openSpace(cp);
        check(await cp.locator('#space-clock').innerText() === '25:00' && await cp.locator('[name=study_scene]:checked').inputValue() === 'library', 'corrupt saved settings recover to usable defaults', engine.name); await corrupt.close();
        const account = await b.newContext({ storageState:savedAccountState }); const ap = await account.newPage();
        await login(ap,'student2'); await ap.goto(base + '/index.php?page=detail&id=1');
        if (await ap.locator('form:has(input[value=enroll])').count()) await submit(ap,ap.locator('form:has(input[value=enroll])'));
        await ap.goto(base + '/index.php?page=learn&id=1');
        check(await ap.locator('#study-space').isHidden(), 'another Student does not inherit enabled scene preference', engine.name);
        await openSpace(ap); await ap.getByRole('tab',{name:'Nhạc',exact:true}).click();
        check(await ap.locator('.space-favorite-row').count() === 0, 'another Student does not inherit favorite links', engine.name); await account.close();
      } finally { await b.close(); }
    }
    assert.equal(report.errors.length,0);
  } catch (error) { report.failure = error.stack; console.error(error); process.exitCode = 1; }
  finally { report.finished = new Date().toISOString(); fs.writeFileSync(path.join(out,'results.json'),JSON.stringify(report,null,2)+'\n'); console.log(JSON.stringify({ checks:report.checks.length, errors:report.errors.length, failure:!!report.failure, liveYoutube:report.liveYoutube })); }
})();
