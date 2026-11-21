// Each browser app must be a fresh, separately seeded disposable loopback app.
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const { chromium } = require(process.env.QA_PLAYWRIGHT_MODULE || 'playwright');
const engines = JSON.parse(process.env.QA_NOTES_APPS || '[]');
assert(process.env.QA_ALLOW_WRITES === '1' && engines.length && engines.every(e => ['127.0.0.1','localhost'].includes(new URL(e.base).hostname)), 'Use disposable independent loopback apps.');
const out = process.env.QA_OUTPUT || 'storage/lesson-notes-qa';
fs.mkdirSync(out,{recursive:true});
const report = { checks:[], errors:[], browsers:{}, downloads:[], limits:['Disposable SQLite only; not MySQL/Docker or real Gemini.','Browser viewports, not physical phones or Safari.'] };
function check(ok,name,browser) { assert(ok,name); report.checks.push({browser,name}); }
async function submit(p,form) { await Promise.all([p.waitForNavigation(),form.locator('button').last().press('Enter')]); }
async function login(p,base,user='student1') {
  await p.goto(base+'/index.php?page=login');
  await p.getByLabel('Email',{exact:true}).fill(user+'@studymate.test');
  await p.getByLabel('Mật khẩu',{exact:true}).fill(process.env.QA_DEMO_PASSWORD || 'StudyMate2026!');
  await submit(p,p.locator('form[method=post]:has(input[name=action][value=login])'));
}
async function open(p) { if (!(await p.locator('#lesson-notes-details').getAttribute('open') !== null)) await p.locator('#lesson-notes-details summary').press('Enter'); }
async function saved(p,key,text) { await p.waitForFunction(({key,text}) => JSON.parse(localStorage.getItem(key) || 'null')?.text === text,{key,text}); }
async function shot(p,name,engine) { if(engine==='chrome') await p.screenshot({path:path.join(out,name+'.png')}); }
async function approve(p,button,accept) { p.once('dialog',d=>accept?d.accept():d.dismiss()); await button.click(); }

(async()=>{
  try {
    for (const engine of engines) {
      const b = await chromium.launch({executablePath:engine.path,headless:true});
      report.browsers[engine.name] = b.version();
      try {
        const c = await b.newContext({viewport:{width:1366,height:900},acceptDownloads:true});
        const p = await c.newPage(); await p.clock.install(); p.on('pageerror',e=>report.errors.push(engine.name+': '+e.message));
        let posts=0; p.on('request',r=>{if(r.method()==='POST')posts++;});
        await p.goto(engine.base+'/index.php?page=home');
        check(await p.locator('#lesson-notes').count()===0,'notes do not appear on public home',engine.name);
        await login(p,engine.base); const auth=await c.storageState();
        const url=engine.base+'/index.php?page=learn&id=2&lesson=';
        await p.goto(url+'7'); const key='studymate:notes:v1:1:2:7';
        check(await p.locator('#lesson-notes').isVisible()&&await p.locator('#lesson-note-input').inputValue()==='','fresh lesson has an empty optional notebook',engine.name);
        await open(p);
        check(await p.locator('#lesson-notes-details').getAttribute('open')!==null,'notebook opens with keyboard',engine.name);
        const text='Ý chính cần nhớ\n- const dùng cho binding không gán lại.\n- let cho phép gán lại.\n\nconst progress = 3 / 6 * 100;\nconsole.log(progress);\nCâu hỏi: Khi nào nên dùng let? 🌧️';
        const beforePosts=posts;
        await p.locator('#lesson-note-input').fill(text); await saved(p,key,text);
        check(posts===beforePosts,'typing and autosave never send an academic POST',engine.name);
        check((await p.locator('#lesson-note-status').innerText()).includes('Đã lưu'),'autosave gives saved feedback',engine.name);
        check((await p.locator('#lesson-note-count').innerText()).startsWith([...text].length.toLocaleString('vi-VN')),'counter counts Unicode code points',engine.name);
        await p.locator('#lesson-notes').scrollIntoViewIfNeeded(); await shot(p,'notes-desktop',engine.name);
        const download=p.waitForEvent('download'); await p.locator('#lesson-note-download').click(); const file=await download;
        const savedFile=path.join(out,'note-'+engine.name+'.txt'); await file.saveAs(savedFile);
        const exported=fs.readFileSync(savedFile,'utf8'); report.downloads.push({browser:engine.name,file:path.basename(savedFile),suggested:file.suggestedFilename()});
        check(exported.charCodeAt(0)===0xfeff&&exported.includes(text)&&exported.includes('JavaScript cơ bản'),'actual UTF-8 download preserves Vietnamese, code and metadata',engine.name);
        check(file.suggestedFilename().endsWith('.txt')&&!/[\\/]/.test(file.suggestedFilename()),'download filename is a safe plain-text filename',engine.name);
        await p.reload(); check(await p.locator('#lesson-note-input').inputValue()===text,'note restores after reload',engine.name);
        check(await p.locator('#lesson-notes-details').getAttribute('open')!==null,'existing note reopens its editor',engine.name);
        const hostile='<img src=x onerror="window.__notesInjected=true"><script>window.__notesInjected=true</script>';
        await p.locator('#lesson-note-input').fill(hostile); await saved(p,key,hostile); await p.reload();
        check(await p.locator('#lesson-note-input').inputValue()===hostile&&await p.locator('#lesson-notes img').count()===0&&!(await p.evaluate(()=>window.__notesInjected)),'note markup stays plain text and never executes',engine.name);
        await p.locator('#lesson-note-input').fill('X'.repeat(20001)); await p.waitForTimeout(900);
        check((await p.locator('#lesson-note-status').innerText()).includes('vượt')&&await p.evaluate(key=>JSON.parse(localStorage.getItem(key)).text,key)===hostile,'over-limit draft remains visible without replacing valid saved note',engine.name);
        const emoji='🙂'.repeat(20000); await p.locator('#lesson-note-input').fill(emoji); await saved(p,key,emoji);
        check((await p.locator('#lesson-note-count').innerText()).startsWith('20.000'),'exact Unicode limit accepts 20,000 emoji',engine.name);
        await p.locator('#lesson-note-input').fill(text); await saved(p,key,text);
        for(const width of [390,768,1366]) {
          await p.setViewportSize({width,height:900}); await p.locator('#lesson-notes').scrollIntoViewIfNeeded();
          check(await p.evaluate(()=>document.documentElement.scrollWidth<=innerWidth),'notes '+width+' no horizontal overflow',engine.name);
          check(await p.locator('#lesson-note-input').evaluate(x=>x.labels.length===1),'notes '+width+' textarea has accessible label',engine.name);
          if(width!==768)await shot(p,'notes-'+width,engine.name);
        }
        await p.setViewportSize({width:1366,height:900});
        await p.goto(url+'8'); await open(p); check(await p.locator('#lesson-note-input').inputValue()==='','different lesson does not inherit note',engine.name);
        const second='Ghi chú bài 2: kiểm tra điều kiện dừng của vòng lặp.';
        await p.locator('#lesson-note-input').fill(second); await submit(p,p.locator('.lesson-actions form[method=post]:has(input[name=action][value=last_lesson])'));
        check(new URL(p.url()).searchParams.get('lesson')==='9'&&await p.evaluate(()=>JSON.parse(localStorage.getItem('studymate:notes:v1:1:2:8')).text)===second,'next-lesson POST flushes draft before autosave delay',engine.name);
        await p.goto(url+'8'); check(await p.locator('#lesson-note-input').inputValue()===second,'previous lesson note remains available',engine.name);
        await p.locator('#lesson-note-input').fill(second+' Đã đối chiếu ví dụ.');
        const request=p.waitForRequest(r=>r.method()==='POST'); await submit(p,p.locator('form[method=post]:has(input[name=action][value=complete])'));
        const fields=new URLSearchParams((await request).postData());
        check(!fields.has('notes')&&!fields.has('note')&&fields.get('action')==='complete'&&!!fields.get('csrf'),'notes stay outside completion POST and preserve CSRF/action',engine.name);
        check(await p.locator('#study-buddy').getAttribute('data-xp')==='40','only actual completion changes Buddy XP',engine.name);
        check((await p.locator('#lesson-note-input').inputValue()).includes('Đã đối chiếu'),'completion POST preserves note',engine.name);
        await p.goto(url+'7'); const q=await c.newPage(); await q.goto(url+'7'); await open(p); await open(q);
        // Keep tab A dirty while interacting with tab B, regardless of host load.
        // Advancing A's autosave clock explicitly below still tests suspension.
        await p.clock.pauseAt(new Date());
        await p.locator('#lesson-note-input').fill('Bản đang sửa ở tab A');
        await q.locator('#lesson-note-input').fill('Bản mới từ tab B'); await q.locator('#lesson-note-save').click();
        await p.waitForFunction(()=>!document.querySelector('#lesson-note-conflict').hidden);
        check(await p.locator('#lesson-note-input').inputValue()==='Bản đang sửa ở tab A','other tab cannot silently replace an active draft',engine.name);
        await p.clock.fastForward(900);
        check(await p.evaluate(key=>JSON.parse(localStorage.getItem(key)).text,key)==='Bản mới từ tab B','conflict suspends autosave and preserves newer saved version',engine.name);
        await approve(p,p.locator('#lesson-note-use-saved'),false);
        check(await p.locator('#lesson-note-input').inputValue()==='Bản đang sửa ở tab A','cancel conflict choice keeps current draft',engine.name);
        await approve(p,p.locator('#lesson-note-use-saved'),true);
        check(await p.locator('#lesson-note-input').inputValue()==='Bản mới từ tab B','explicit read-new choice loads saved version',engine.name);
        await p.locator('#lesson-note-input').fill('Bản A được chọn giữ'); await q.locator('#lesson-note-input').fill('Bản B tiếp theo'); await q.locator('#lesson-note-save').click();
        await p.waitForFunction(()=>!document.querySelector('#lesson-note-conflict').hidden);
        await approve(p,p.locator('#lesson-note-keep-draft'),true); await saved(p,key,'Bản A được chọn giữ');
        await q.waitForFunction(()=>document.querySelector('#lesson-note-input').value==='Bản A được chọn giữ');
        check(true,'explicit keep-draft choice updates clean other tab',engine.name);
        await approve(p,p.locator('#lesson-note-clear'),false); check(await p.locator('#lesson-note-input').inputValue()==='Bản A được chọn giữ','cancel delete retains note',engine.name);
        await approve(p,p.locator('#lesson-note-clear'),true);
        check(await p.locator('#lesson-note-input').inputValue()===''&&await p.evaluate(key=>localStorage.getItem(key),key)===null,'confirmed delete removes only current lesson note',engine.name);
        check((await p.evaluate(()=>JSON.parse(localStorage.getItem('studymate:notes:v1:1:2:8')).text)).includes('Đã đối chiếu'),'delete does not remove other lesson notes',engine.name);
        await q.close(); await p.clock.resume();

        const blocked=await b.newContext({storageState:auth,acceptDownloads:true,viewport:{width:390,height:900}});
        await blocked.addInitScript(()=>{window.__notesFail=true;const set=Storage.prototype.setItem;Storage.prototype.setItem=function(k,v){if(window.__notesFail&&k.startsWith('studymate:notes:'))throw new DOMException('Full','QuotaExceededError');return set.call(this,k,v)}});
        const dp=await blocked.newPage(); await dp.goto(url+'9'); await open(dp);
        await dp.locator('#lesson-note-input').fill('Bản nháp cần giữ khi hết dung lượng.'); await dp.waitForFunction(()=>document.querySelector('#lesson-note-status').textContent.includes('Chưa lưu'));
        check(await dp.locator('#lesson-note-input').inputValue()==='Bản nháp cần giữ khi hết dung lượng.','quota failure retains visible draft',engine.name);
        const backup=dp.waitForEvent('download');await dp.locator('#lesson-note-download').click();check((await backup).suggestedFilename().endsWith('.txt'),'download remains available when storage is full',engine.name);
        const form=dp.locator('form[method=post]:has(input[name=action][value=complete])');await approve(dp,form.locator('button'),false);
        check(await form.getAttribute('aria-busy')===null&&await form.locator('button').isEnabled(),'cancel unsaved-note exit never locks native completion form',engine.name);
        await dp.evaluate(()=>{window.__notesFail=false});await dp.locator('#lesson-note-save').click();
        await submit(dp,dp.locator('form[method=post]:has(input[name=action][value=complete])'));
        check(await dp.locator('#study-buddy').getAttribute('data-xp')==='60'&&(await dp.locator('#lesson-note-input').inputValue()).includes('hết dung lượng'),'retry after recovery saves and completes lesson normally',engine.name);
        await dp.goto(url+'10');await open(dp);await dp.locator('#lesson-note-input').fill('Nội dung chưa lưu sẽ được bỏ khi đã xác nhận.');await dp.waitForFunction(()=>document.querySelector('#lesson-note-status').textContent.includes('Chưa lưu'));
        const dialogs=[];const listener=async d=>{dialogs.push(d.type());await d.accept()};dp.on('dialog',listener);
        await submit(dp,dp.locator('form[method=post]:has(input[name=action][value=complete])'));dp.off('dialog',listener);
        check(dialogs.length===1&&dialogs[0]==='confirm'&&await dp.locator('#study-buddy').getAttribute('data-xp')==='80','confirmed unsaved exit produces one dialog and completes once',engine.name);
        await blocked.close();

        const corrupt=await b.newContext({storageState:auth,acceptDownloads:true});
        await corrupt.addInitScript(()=>{const key='studymate:notes:v1:1:2:11';if(!localStorage.getItem(key))localStorage.setItem(key,'{broken-note')});
        const cp=await corrupt.newPage();await cp.goto(url+'11');
        check(await cp.locator('#lesson-note-recovery').isVisible(),'corrupt saved note offers recovery',engine.name);
        await cp.locator('#lesson-note-input').fill('Ghi chú mới sau khi đã đối chiếu bản cũ.');await cp.waitForTimeout(900);
        check(await cp.evaluate(()=>localStorage.getItem('studymate:notes:v1:1:2:11'))==='{broken-note','corrupt original is not silently overwritten',engine.name);
        const rawDownload=cp.waitForEvent('download');await cp.locator('#lesson-note-raw-download').click();const raw=await rawDownload;const rawFile=path.join(out,'raw-'+engine.name+'.txt');await raw.saveAs(rawFile);
        check(fs.readFileSync(rawFile,'utf8').includes('{broken-note'),'raw recovery download preserves original bytes as text',engine.name);
        await approve(cp,cp.locator('#lesson-note-start-new'),false);check(await cp.locator('#lesson-note-recovery').isVisible(),'cancel starting new keeps recovery state',engine.name);
        await approve(cp,cp.locator('#lesson-note-start-new'),true);check(await cp.evaluate(()=>JSON.parse(localStorage.getItem('studymate:notes:v1:1:2:11')).text)==='Ghi chú mới sau khi đã đối chiếu bản cũ.','explicit new-note choice safely replaces corrupt record',engine.name);
        await corrupt.close();
        const plain=await b.newContext({storageState:auth,javaScriptEnabled:false});const np=await plain.newPage();await np.goto(url+'7');check(await np.locator('#lesson-notes').isHidden()&&await np.locator('.lesson-text').isVisible(),'no-JavaScript lesson stays readable without inactive notes UI',engine.name);await plain.close();
        await login(p,engine.base,'student2');await p.goto(engine.base+'/index.php?page=detail&id=2');await submit(p,p.locator('form[method=post]:has(input[name=action][value=enroll])'));await p.goto(url+'8');await open(p);
        check(await p.locator('#lesson-note-input').inputValue()===''&&await p.locator('#lesson-notes').getAttribute('data-user')==='2','another account does not inherit personal notes',engine.name);
        await p.locator('#lesson-note-input').fill('Ghi chú riêng của học viên 2.');await saved(p,'studymate:notes:v1:2:2:8','Ghi chú riêng của học viên 2.');
        await login(p,engine.base,'student1');await p.goto(url+'8');check((await p.locator('#lesson-note-input').inputValue()).includes('Đã đối chiếu'),'first account restores its own note after switch',engine.name);
        await c.close();
      } finally { await b.close(); }
    }
    check(report.errors.length===0,'no JavaScript page errors','all');
    console.log(JSON.stringify({checks:report.checks.length,errors:report.errors.length}));
  } catch(error) { report.failure=error.stack;console.error(error.stack);process.exitCode=1; }
  finally { report.finished=new Date().toISOString();fs.writeFileSync(path.join(out,'results.json'),JSON.stringify(report,null,2)+'\n'); }
})();
