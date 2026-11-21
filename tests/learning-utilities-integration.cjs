// Run only against disposable loopback apps: #22 baseline and integrated source.
const assert=require('node:assert/strict'),fs=require('node:fs'),path=require('node:path'),crypto=require('node:crypto');
const {chromium}=require(process.env.QA_PLAYWRIGHT_MODULE||'playwright');
const base=process.env.QA_BASE_URL,baseline=process.env.QA_BASELINE_URL;
assert(process.env.QA_ALLOW_WRITES==='1'&&[base,baseline].every(x=>['127.0.0.1','localhost'].includes(new URL(x).hostname)));
const engines=JSON.parse(process.env.QA_BROWSERS||'[]');assert(engines.length);
const out=process.env.QA_OUTPUT||'storage/learning-utilities-qa';fs.mkdirSync(out,{recursive:true});
const report={checks:[],errors:[],screenshots:[],limits:['Disposable SQLite; not MySQL, Docker, Safari or physical phones.','No real Gemini calls. YouTube is covered separately by study-space.cjs.']};
const check=(ok,name,engine)=>{assert(ok,name);report.checks.push({engine,name});};
async function shot(p,name){const file=name+'.png';await p.screenshot({path:path.join(out,file),animations:'disabled'});report.screenshots.push(file);}
async function preferences(p){await p.locator('#display-settings').evaluate(x=>x.open=true);}
async function language(p,value){await preferences(p);await p.locator('#display-language').selectOption(value);}
async function openNotes(p){await p.locator('#lesson-notes-details').evaluate(x=>x.open=true);}
async function save(p,text){await p.locator('#lesson-note-input').fill(text);await p.locator('#lesson-note-save').click();}
(async()=>{try{for(const engine of engines){
 const browser=await chromium.launch({executablePath:engine.path,headless:true});
 try{
  // Public surfaces must be the exact #22 design, in both languages/themes.
  const publicContext=await browser.newContext({viewport:{width:1366,height:900}});
  const p=await publicContext.newPage(),q=await publicContext.newPage();
  p.on('pageerror',e=>report.errors.push(engine.name+': '+e.message));
  for(const lang of ['vi','en'])for(const theme of ['light','dark']){
   await p.goto(base+`/index.php?page=home&ui_language=${lang}&ui_theme=${theme}`);
   await q.goto(baseline+`/index.php?page=home&ui_language=${lang}&ui_theme=${theme}`);
   await p.evaluate(()=>document.fonts.ready);await q.evaluate(()=>document.fonts.ready);
   const a=await p.screenshot({animations:'disabled'}),b=await q.screenshot({animations:'disabled'});
   check(a.equals(b),`public home ${lang}/${theme} pixels match #22 exactly`,engine.name);
   check(await p.locator('script[src*=study-],link[href*=learning-utilities],#study-buddy').count()===0,'no utility assets or Buddy on public home '+lang+'/'+theme,engine.name);
   if(engine.name==='chrome')await shot(p,'home-'+lang+'-'+theme);
  }
  await p.goto(base+'/index.php?page=login&ui_language=vi&ui_theme=light');
  await p.getByLabel('Email',{exact:true}).fill('student1@studymate.test');await p.getByLabel('Mật khẩu',{exact:true}).fill(process.env.QA_DEMO_PASSWORD||'StudyMate2026!');
  await Promise.all([p.waitForNavigation(),p.locator('form[method=post]:has(input[value=login]) button').click()]);
  const auth=await publicContext.storageState();await publicContext.close();
  const learn=base+'/index.php?page=learn&id=2&lesson=7',key='studymate:notes:v1:1:2:7';
  const context=await browser.newContext({storageState:auth,viewport:{width:1366,height:900}});
  const r=await context.newPage();r.on('pageerror',e=>report.errors.push(engine.name+': '+e.message));
  await r.goto(learn);await openNotes(r);
  let dialogs=[];r.on('dialog',async d=>{dialogs.push(d.type());await d.dismiss();});
  await r.locator('#lesson-note-input').fill('Bản nháp đổi ngôn ngữ 🌿');
  await Promise.all([r.waitForNavigation(),language(r,'en')]);
  check(await r.locator('#lesson-note-input').inputValue()==='Bản nháp đổi ngôn ngữ 🌿'&&dialogs.length===0,'language switch flushes draft immediately without warning when saved',engine.name);
  check(await r.locator('#lesson-notes summary strong').innerText()==='Notes for this lesson','English notebook label',engine.name);
  await r.locator('#study-space-toggle').click();
  check(await r.locator('#space-title').innerText()==='Focus corner'&&await r.locator('#space-timer-start').innerText()==='Start','English focus labels and timer',engine.name);
  await r.locator('#buddy-launcher').click();
  check(await r.locator('#buddy-title').innerText()==='Your Sukuna'&&await r.locator('#study-buddy').evaluate(x=>x.parentElement.classList.contains('learning-rail')),'Buddy translated and mounted in learning rail',engine.name);
  await r.locator('.buddy-wardrobe summary').click();
  check(await r.locator('#study-buddy').getAttribute('data-xp')==='170','XP derives from six lessons plus one distinct quiz pass',engine.name);
  check(await r.locator('[data-buddy-item=student]').isDisabled()&&!await r.locator('[data-buddy-item=crimson]').isDisabled(),'outfit thresholds remain enforced',engine.name);
  await r.locator('[data-buddy-item=crimson]').click();await r.locator('[data-buddy-item=crown]').click();
  check((await r.locator('#buddy-sprite').getAttribute('src')).includes('crimson-')&&await r.locator('#study-buddy').getAttribute('data-accessory')==='crown','unlocked outfit and accessory equip',engine.name);
  for(const lang of ['vi','en'])for(const theme of ['light','dark'])for(const width of [390,768,1366]){
   await r.setViewportSize({width,height:900});
   await r.goto(learn+`&ui_language=${lang}&ui_theme=${theme}`);await openNotes(r);
   if(await r.locator('#study-space').isHidden())await r.locator('#study-space-toggle').click();
   check(await r.evaluate(()=>document.documentElement.scrollWidth<=innerWidth),`${lang}/${theme}/${width} no horizontal overflow`,engine.name);
   check(await r.locator('#lesson-note-input').evaluate(x=>getComputedStyle(x).color!==getComputedStyle(x).backgroundColor),`${lang}/${theme}/${width} editor foreground differs from background`,engine.name);
   check(await r.locator('#study-buddy').evaluate(x=>!['fixed','absolute'].includes(getComputedStyle(x).position)),`${lang}/${theme}/${width} Buddy stays outside action overlays`,engine.name);
   await r.locator('#buddy-launcher').click();await r.locator('.buddy-wardrobe summary').click();
   const missing=await r.locator('#study-buddy,#study-space,#lesson-notes').evaluateAll(roots=>{
    if(document.documentElement.lang!=='en')return[];
    const catalog=JSON.parse(document.querySelector('#learning-utilities-i18n').dataset.messages);
    const keys=Object.keys(catalog).filter(k=>catalog[k]!==k&&k.trim());
    return roots.flatMap(root=>[...root.querySelectorAll('button,label,summary,strong,legend,h2,h3,small,option,p')].map(x=>x.textContent.trim()).filter(t=>keys.includes(t)));
   });
   check(missing.length===0,`${lang}/${theme}/${width} no untranslated utility labels`,engine.name);
   if(engine.name==='chrome'&&width!==768){await r.locator('#lesson-notes').scrollIntoViewIfNeeded();await shot(r,`notes-${lang}-${theme}-${width}`);await r.locator('#study-space').scrollIntoViewIfNeeded();await shot(r,`tools-${lang}-${theme}-${width}`);}
  }
  await r.setViewportSize({width:1366,height:900});await r.goto(learn+'&ui_language=vi&ui_theme=light');await openNotes(r);
  await r.locator('#lesson-note-input').fill('Đổi sáng tối giữ nguyên bản nháp');await preferences(r);await r.locator('#display-theme').selectOption('dark');
  check(await r.locator('#lesson-note-input').inputValue()==='Đổi sáng tối giữ nguyên bản nháp'&&await r.locator('html').getAttribute('data-theme')==='dark'&&dialogs.length===0,'theme changes in place and preserves notes without dialog',engine.name);
  await r.locator('#lesson-note-save').click();
  // The original click fix is retained; a real press after typing still clicks.
  await r.locator('#lesson-note-input').fill('Kiểm lại click sau nhập');
  await Promise.all([r.waitForNavigation(),r.locator('form[method=post]:has(input[value=last_lesson])').filter({has:r.locator('input[name=lesson_id][value="8"]')}).first().locator('button').click()]);
  check(new URL(r.url()).searchParams.get('lesson')==='8','first click after typing navigates lesson',engine.name);
  check(await r.evaluate(k=>JSON.parse(localStorage.getItem(k)).text,key)==='Kiểm lại click sau nhập','lesson navigation preserves the immediate draft',engine.name);
  await context.close();
  // Block only notes storage: one combined confirmation for notes + AI draft.
  for(const accept of [false,true]){
   const c=await browser.newContext({storageState:auth,viewport:{width:1366,height:900}});
   await c.addInitScript(()=>{const set=Storage.prototype.setItem;Storage.prototype.setItem=function(k,v){if(k.startsWith('studymate:notes:'))throw new DOMException('Quota full','QuotaExceededError');return set.call(this,k,v);};});
   const t=await c.newPage();t.on('pageerror',e=>report.errors.push(engine.name+': '+e.message));
   await t.goto(learn);await openNotes(t);await t.locator('#lesson-note-input').fill('Bản chưa lưu phải được giữ');
   await t.locator('form[method=post]:has(input[value=ai]) textarea').fill('Câu hỏi chưa gửi');
   const seen=[];t.on('dialog',async d=>{seen.push({type:d.type(),message:d.message()});await (accept?d.accept():d.dismiss());});
   if(accept)await Promise.all([t.waitForNavigation(),language(t,'en')]);else await language(t,'en');
   check(seen.length===1&&seen[0].type==='confirm'&&seen[0].message.includes('ghi chú'),`failed save + AI draft language ${accept?'approve':'cancel'} shows one combined dialog`,engine.name);
   if(accept)check(await t.locator('html').getAttribute('lang')==='en'&&await t.locator('#lesson-note-input').inputValue()==='','explicit discard navigates without a second beforeunload warning',engine.name);
   else check(await t.locator('#display-language').inputValue()==='vi'&&await t.locator('#lesson-note-input').inputValue()==='Bản chưa lưu phải được giữ'&&!await t.locator('form[aria-busy=true]').count(),'cancel restores language and keeps draft/forms usable',engine.name);
   await c.close();
  }
  const plain=await browser.newContext({storageState:auth,javaScriptEnabled:false});const np=await plain.newPage();await np.goto(learn);
  check(await np.locator('#lesson-notes').isHidden()&&await np.locator('#study-buddy').isHidden()&&await np.locator('.lesson-text').isVisible(),'no JS retains canonical readable lesson and POST controls',engine.name);await plain.close();
 }finally{await browser.close();}
}check(report.errors.length===0,'no JavaScript errors','all');console.log(JSON.stringify({checks:report.checks.length,errors:report.errors.length}));
}catch(e){report.failure=e.stack;console.error(e.stack);process.exitCode=1;}finally{fs.writeFileSync(path.join(out,'results.json'),JSON.stringify(report,null,2)+'\n');}})();
