const {chromium}=require('playwright');
const fs=require('fs');const path=require('path');
const root=path.resolve(process.env.STUDYMATE_TEST_ARTIFACTS||path.join(__dirname,'../../storage/qa-display')),base=process.env.STUDYMATE_TEST_URL||'http://127.0.0.1:8096/index.php';
const checks=[],errors=[];
const check=(condition,name)=>{if(!condition)throw Error(name);checks.push(name);};
(async()=>{
 const browser=await chromium.launch({headless:true,executablePath:process.env.STUDYMATE_BROWSER_EXECUTABLE||undefined});
 try {
  fs.mkdirSync(path.join(root,'screenshots'),{recursive:true});
  const context=await browser.newContext({viewport:{width:1440,height:1000},colorScheme:'light'});
  const page=await context.newPage();page.on('pageerror',e=>errors.push(e.message));
  const go=route=>page.goto(base+'?page='+route);
  async function ready(){await page.evaluate(()=>document.fonts.ready);await page.locator('img').evaluateAll(xs=>Promise.all(xs.map(x=>{x.loading='eager';return x.decode().catch(()=>{});})));await page.evaluate(()=>new Promise(r=>requestAnimationFrame(()=>requestAnimationFrame(r))));}
  async function settings(){const menu=page.locator('#display-settings');if(!await menu.evaluate(el=>el.open))await menu.locator('summary').click();}
  async function close(){await page.locator('#display-settings').evaluate(el=>el.open=false);}
  async function language(value){if(await page.locator('html').getAttribute('lang')===value)return;await settings();await Promise.all([page.waitForNavigation(),page.locator('#display-language').selectOption(value)]);}
  async function theme(value){await settings();await page.locator('#display-theme').selectOption(value);await close();}
  async function login(user){await page.setViewportSize({width:1440,height:1000});await go('login');await page.locator('[name=email]').fill(user+'@studymate.test');await page.locator('[name=password]').fill('StudyMate2026!');await Promise.all([page.waitForNavigation(),page.locator('form:has(input[name=action][value=login]) button').click()]);}
  async function logout(){await page.setViewportSize({width:1440,height:1000});await Promise.all([page.waitForNavigation(),page.locator('form:has(input[name=action][value=logout]) button').click()]);}
  async function inspect(route,width,label,shot=false){
   await page.setViewportSize({width,height:width===390?844:1000});const response=await go(route);await ready();
   check(response.status()===200,label+': HTTP 200');
   check(await page.evaluate(()=>document.documentElement.scrollWidth<=innerWidth+1),label+': fits viewport');
   check(!/Warning:|Fatal error|Stack trace:/.test(await page.locator('body').innerText()),label+': no PHP diagnostics');
   check(await page.locator('img').evaluateAll(xs=>xs.every(x=>x.complete&&x.naturalWidth>0)),label+': photos loaded');
   if(shot)await page.screenshot({path:path.join(root,'screenshots','display-'+label.replaceAll(/[^\w-]/g,'-')+'.png'),animations:'disabled'});
  }
  await go('home');
  check(await page.locator('html').getAttribute('lang')==='vi'&&await page.locator('html').getAttribute('data-theme')==='light','new browser uses Vietnamese and system light');
  const authoredTitle=await page.locator('.course-card h3').first().innerText();
  for(const locale of ['vi','en']){
   await language(locale);
   for(const mode of ['light','dark']){
    await theme(mode);
    for(const width of [1440,834,390])for(const route of ['home','courses','detail&id=1','login','register'])await inspect(route,width,`${locale}-${mode}-${route}-${width}`,route==='home'&&width!==834);
    await page.setViewportSize({width:1440,height:1000});await go('home');
    check(await page.locator('.course-card h3').first().innerText()===authoredTitle,`${locale}/${mode}: authored course title retained`);
    check((await page.locator('#hero-title').innerText()).startsWith(locale==='vi'?'Học để hiểu.':'Learn to understand.'),`${locale}/${mode}: hero translated on server`);
   }
  }
  await go('courses&q=JavaScript&category=2&p=1');
  check(/^\d+ courses? for “JavaScript”$/.test(await page.locator('.results-toolbar p').innerText()),'English search summary uses translated count and query');
  const query=page.locator('.catalog-filter input[name=q]');await query.fill('unsaved search');const previous=page.url();
  await theme('light');check(page.url()===previous&&await query.inputValue()==='unsaved search','theme switch is immediate and preserves unsaved search');
  await query.fill('JavaScript');await language('vi');
  let address=new URL(page.url());check(['q','category','p'].every(k=>address.searchParams.has(k)),'language switch preserves search/category/pagination');
  check(!address.searchParams.has('ui_language')&&!address.searchParams.has('ui_theme'),'preference query parameters do not override later choices');
  await go('home#fields');await language('en');check(new URL(page.url()).hash==='#fields','language switch retains current landing section');
  await theme('dark');await page.reload();
  check(await page.locator('html').getAttribute('lang')==='en'&&await page.locator('html').getAttribute('data-theme')==='dark','language and theme survive reload');
  const cookies=await context.cookies();check(cookies.some(x=>x.name==='studymate_language'&&x.httpOnly&&x.sameSite==='Lax'),'language preference stored with validated browser cookie');
  const restored=await browser.newContext({storageState:await context.storageState(),colorScheme:'light'});const reopened=await restored.newPage();await reopened.goto(base+'?page=login');
  check(await reopened.locator('html').getAttribute('lang')==='en'&&await reopened.locator('html').getAttribute('data-theme')==='dark','saved choices survive a new browser context');await restored.close();
  await page.emulateMedia({colorScheme:'dark'});await theme('system');check(await page.locator('html').getAttribute('data-theme')==='dark','system option follows dark device preference');
  await page.emulateMedia({colorScheme:'light'});await page.waitForFunction(()=>document.documentElement.dataset.theme==='light');check(true,'system option responds to device appearance changes');
  await theme('dark');
  await page.setViewportSize({width:390,height:844});await settings();
  check(await page.locator('.preferences-panel').evaluate(el=>{const r=el.getBoundingClientRect();return r.left>=0&&r.right<=innerWidth;}),'mobile display settings stay within viewport');
  await page.keyboard.press('Escape');check(!await page.locator('#display-settings').evaluate(el=>el.open),'Escape closes settings');
  check(await page.locator('#display-settings>summary').evaluate(el=>el===document.activeElement),'Escape restores focus to settings button');
  await settings();await page.locator('#site-header .brand').click();check(!await page.locator('#display-settings').evaluate(el=>el.open),'navigation closes settings');
  for(const width of [320,375,390,834,1101,1280,1440]){await page.setViewportSize({width,height:1000});await go('home');check(await page.evaluate(()=>document.documentElement.scrollWidth<=innerWidth+1),`English header fits ${width}px`);}
  await login('student1');check(await page.locator('html').getAttribute('lang')==='en'&&await page.locator('html').getAttribute('data-theme')==='dark','login retains display choices');
  for(const locale of ['vi','en']){await language(locale);for(const mode of ['light','dark']){await theme(mode);for(const width of [1440,390])for(const route of ['student','learn&id=2&lesson=7','quiz&id=2','result&id=1','profile'])await inspect(route,width,`${locale}-${mode}-${route}-${width}`,route==='student'||(route.startsWith('learn')&&locale==='en'));}}
  await page.setViewportSize({width:1440,height:1000});await go('learn&id=2&lesson=7');
  await go('student');check((await page.locator('.pager span').innerText()).startsWith('Page ')&&!(await page.locator('.pager span').innerText()).includes('kết quả'),'English student pagination fully translated');await go('learn&id=2&lesson=7');
  check(await page.locator('.lesson-text h1').innerText()==='Biến và kiểu dữ liệu','authored lesson remains unchanged in English interface');
  check(await page.locator('button[value=summarize]').innerText()==='Summarize','learning controls translated');
  const source=await page.locator('.lesson-text').innerHTML();await theme('light');check(await page.locator('.lesson-text').innerHTML()===source,'theme switch leaves rendered lesson and code intact');await theme('dark');
  await language('vi');check(new URL(page.url()).searchParams.get('id')==='2'&&new URL(page.url()).searchParams.get('lesson')==='7','language switch preserves course and lesson');await language('en');
  await Promise.all([page.waitForNavigation(),page.locator('button[value=ask]').click()]);check((await page.locator('[role=alert]').innerText()).includes('AI is not configured'),'server error message uses selected language');
  await go('quiz&id=2');const radio=page.locator('.answer-option input').first();await radio.check();await theme('light');check(await radio.isChecked(),'theme switch preserves unsubmitted quiz answer');
  let warning='';page.once('dialog',async dialog=>{warning=dialog.message();await dialog.dismiss();});await settings();await page.locator('#display-language').selectOption('vi');
  check(warning.includes('unsubmitted quiz answers')&&await radio.isChecked()&&await page.locator('html').getAttribute('lang')==='en','cancelled language switch preserves quiz and language');
  check(await page.locator('#display-language').inputValue()==='en','cancelled language switch resets selector');
  page.once('dialog',dialog=>dialog.accept());await language('vi');check(new URL(page.url()).searchParams.get('page')==='quiz','confirmed language switch keeps quiz route');
  await logout();check(await page.locator('html').getAttribute('lang')==='vi'&&await page.locator('html').getAttribute('data-theme')==='light','logout retains display choices');
  await login('instructor1');await language('en');await theme('dark');await go('edit&id=3&tab=content&lesson=13');
  const title=page.locator('input[name=title]').last();const original=await title.inputValue();await title.fill('Unsaved lesson title');await theme('light');check(await title.inputValue()==='Unsaved lesson title','theme switch preserves unsaved instructor edit');
  page.once('dialog',dialog=>dialog.dismiss());await settings();await page.locator('#display-language').selectOption('vi');check(await title.inputValue()==='Unsaved lesson title'&&await page.locator('html').getAttribute('lang')==='en','cancelled language change preserves unsaved editor');
  page.once('dialog',dialog=>dialog.accept());await language('vi');address=new URL(page.url());check(address.searchParams.get('tab')==='content'&&address.searchParams.get('lesson')==='13','language switch preserves editor tab and lesson');
  check(await page.locator('input[name=title]').last().inputValue()===original,'switching display preferences never writes lesson content');
  for(const locale of ['vi','en']){await language(locale);for(const mode of ['light','dark']){await theme(mode);for(const width of [1440,390])for(const route of ['instructor','edit&id=3&tab=info','edit&id=3&tab=content','edit&id=3&tab=quiz','edit&id=3&tab=students','preview&id=3'])await inspect(route,width,`${locale}-${mode}-${route}-${width}`,route==='edit&id=3&tab=content'&&locale==='en');}}
  await page.setViewportSize({width:1440,height:1000});await go('edit&id=3&tab=quiz');check(await page.getByRole('textbox',{name:'Option A, question 1',exact:true}).count()===1,'quiz editor translates full accessible option label');
  await logout();await login('admin');
  for(const locale of ['vi','en']){await language(locale);for(const mode of ['light','dark']){await theme(mode);for(const width of [1440,390])for(const route of ['admin&tab=users','admin&tab=courses','admin&tab=categories'])await inspect(route,width,`${locale}-${mode}-${route}-${width}`,route==='admin&tab=categories');}}
  await page.setViewportSize({width:1440,height:1000});await go('admin&tab=categories');
  function contrast(fg,bg){const rgb=s=>s.match(/[\d.]+/g).slice(0,3).map(Number);const luminance=c=>rgb(c).map(x=>x/255).map(x=>x<=.04045?x/12.92:((x+.055)/1.055)**2.4).reduce((a,x,i)=>a+x*[.2126,.7152,.0722][i],0);const a=luminance(fg),b=luminance(bg);return(Math.max(a,b)+.05)/(Math.min(a,b)+.05);}
  // Measure the final palette after the shared 180ms button-color transition.
  for(const mode of ['light','dark']){await theme(mode);await page.waitForTimeout(240);const colors=await page.locator('main .btn-primary').first().evaluate(el=>{const s=getComputedStyle(el);return[s.color,s.backgroundColor];});check(contrast(...colors)>=4.5,mode+': primary button contrast at least 4.5:1');}
  const health=await context.request.get(base+'?page=health');check(health.status()===200&&(await health.json()).status==='ok','health API contract remains unchanged');
  const nojs=await browser.newContext({javaScriptEnabled:false,colorScheme:'dark',viewport:{width:390,height:844}});const plain=await nojs.newPage();const response=await plain.goto(base+'?page=courses&q=JavaScript&ui_language=en&ui_theme=system');
  check(response.headers()['content-language']==='en'&&await plain.locator('h1').innerText()==='Find a course for you','English fully renders without JavaScript');
  check(await plain.locator('body').evaluate(el=>getComputedStyle(el).backgroundColor==='rgb(12, 25, 20)'),'system dark appearance works without JavaScript');
  await plain.locator('#display-settings summary').click();await plain.locator('#display-language').selectOption('vi');await plain.locator('#display-theme').selectOption('light');await Promise.all([plain.waitForNavigation(),plain.locator('.preferences-apply').click()]);
  check(await plain.locator('html').getAttribute('lang')==='vi'&&await plain.locator('html').getAttribute('data-theme')==='light','native Apply works without JavaScript');
  check(new URL(plain.url()).searchParams.get('q')==='JavaScript','no-JS preference form retains search context');await nojs.close();
  const invalid=await browser.newContext();const unsafe=await invalid.newPage();await unsafe.goto(base+'?ui_language[]=en&ui_theme[]=dark');check(await unsafe.locator('html').getAttribute('lang')==='vi'&&await unsafe.locator('html').getAttribute('data-theme-preference')==='system','invalid preference arrays fall back safely');await invalid.close();
  check(errors.length===0,'no browser script errors');
  fs.writeFileSync(path.join(root,'QA_DISPLAY_PREFERENCES.json'),JSON.stringify({date_utc:new Date().toISOString(),browser:browser.version(),checks,errors,scope:'4 display combinations; desktop and mobile; disposable SQLite; no authored-content writes from browser tests; no live AI'},null,2));
  console.log(JSON.stringify({checks:checks.length,errors:errors.length}));
 }finally{await browser.close();}
})().catch(e=>{console.error(e);process.exitCode=1;});
