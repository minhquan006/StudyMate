// Actual pointer hit tests after scrolling, against a disposable loopback app.
const assert=require('node:assert/strict'),fs=require('node:fs'),path=require('node:path'),crypto=require('node:crypto');
const {chromium}=require(process.env.QA_PLAYWRIGHT_MODULE||'playwright');
const base=process.env.QA_BASE_URL,engines=JSON.parse(process.env.QA_BROWSERS||'[]');
assert(process.env.QA_ALLOW_WRITES==='1'&&engines.length&&['127.0.0.1','localhost'].includes(new URL(base).hostname),'Use a disposable loopback QA app.');
const broken=process.env.QA_EXPECT_BROKEN==='1',out=process.env.QA_OUTPUT||'storage/sticky-qa';fs.mkdirSync(out,{recursive:true});
const report={expectedBroken:broken,sourceCssSha256:crypto.createHash('sha256').update(fs.readFileSync(process.env.QA_CSS_PATH||'public/assets/study-space.css')).digest('hex'),checks:[],samples:[],errors:[],browsers:{},limits:['Windows Chrome/Edge viewports on disposable SQLite; not macOS Chrome 158, physical phones, MySQL/Compose or real Gemini.']};
const check=(ok,name,browser)=>{assert(ok,name);report.checks.push({browser,name});};
(async()=>{try{for(const engine of engines){
 const b=await chromium.launch({executablePath:engine.path,headless:true});report.browsers[engine.name]=b.version();
 try{
  const c=await b.newContext({viewport:{width:834,height:900}}),p=await c.newPage();p.on('pageerror',e=>report.errors.push(e.message));
  await p.goto(base+'/index.php?page=login&ui_language=vi&ui_theme=light');
  await p.getByLabel('Email',{exact:true}).fill('student1@studymate.test');await p.getByLabel('Mật khẩu',{exact:true}).fill(process.env.QA_DEMO_PASSWORD||'StudyMate2026!');
  await Promise.all([p.waitForNavigation(),p.locator('form[method=post]:has(input[name=action][value=login]) button').click()]);
  for(const lang of (broken?['vi']:['vi','en']))for(const theme of(broken?['light']:['light','dark']))for(const width of(broken?[834,991]:[390,767,768,834,991,992,1366])){
   await p.setViewportSize({width,height:900});await p.goto(base+`/index.php?page=learn&id=2&lesson=7&ui_language=${lang}&ui_theme=${theme}`);
   if(await p.locator('#study-space').isHidden())await p.locator('#study-space-toggle').click();
   if(width<768)await p.locator('#space-close').scrollIntoViewIfNeeded();else await p.evaluate(()=>scrollTo(0,650));
   await p.waitForFunction(()=>document.querySelector('body').classList.contains('study-space-on'));
   const sample=await p.evaluate(()=>{
    const close=document.querySelector('#space-close'),rail=document.querySelector('.learning-rail'),header=document.querySelector('#site-header');
    const r=close.getBoundingClientRect(),h=header.getBoundingClientRect(),a=rail.getBoundingClientRect(),style=getComputedStyle(rail);
    const x=r.left+r.width/2,y=r.top+r.height/2,target=document.elementFromPoint(x,y);
    return{x,y,scrollY,close:{top:r.top,bottom:r.bottom},header:{top:h.top,bottom:h.bottom},rail:{top:a.top,bottom:a.bottom,position:style.position,maxHeight:style.maxHeight},hitsClose:target===close||close.contains(target),hitTag:target?.tagName,hitClass:target?.getAttribute('class')};
   });report.samples.push({browser:engine.name,lang,theme,width,...sample});
   if(engine.name==='chrome'&&lang==='vi'&&theme==='light'&&[834,991].includes(width))await p.screenshot({path:path.join(out,`scrolled-${width}.png`)});
   if(broken){
    check(!sample.hitsClose&&sample.close.top<sample.header.bottom,`${width} reproduces header intercepting close`,engine.name);
    await p.mouse.click(sample.x,sample.y);
    check(await p.locator('#study-space').isVisible()&&await p.locator('.mobile-menu-toggle').getAttribute('aria-expanded')==='true',`${width} real close-position click opens menu instead of closing space`,engine.name);
   }else{
    check(sample.hitsClose,`${lang}/${theme}/${width} close receives pointer hit after scrolling`,engine.name);
    check(sample.close.top>=sample.header.bottom&&sample.close.bottom<=900,`${lang}/${theme}/${width} close stays below header and inside viewport`,engine.name);
    check(width<768?sample.rail.position==='static':sample.rail.position==='sticky'&&sample.rail.top>=sample.header.bottom&&sample.rail.bottom<=900,`${lang}/${theme}/${width} rail preserves mobile flow or usable sticky height`,engine.name);
    await p.mouse.click(sample.x,sample.y);
    check(await p.locator('#study-space').isHidden(),`${lang}/${theme}/${width} real click closes space`,engine.name);
    check(await p.locator('.mobile-menu-toggle').getAttribute('aria-expanded')==='false',`${lang}/${theme}/${width} close does not open navigation`,engine.name);
    check(await p.locator('#study-space-toggle').evaluate(x=>x===document.activeElement),`${lang}/${theme}/${width} close returns keyboard focus to toggle`,engine.name);
    check(await p.evaluate(()=>document.documentElement.scrollWidth<=innerWidth),`${lang}/${theme}/${width} no horizontal overflow`,engine.name);
   }
  }
  await c.close();
 }finally{await b.close();}
}check(report.errors.length===0,'no JavaScript page errors','all');console.log(JSON.stringify({checks:report.checks.length,samples:report.samples.length,expectedBroken:broken}));
}catch(e){report.failure=e.stack;console.error(e.stack);process.exitCode=1;}finally{fs.writeFileSync(path.join(out,'results.json'),JSON.stringify(report,null,2)+'\n');}})();
