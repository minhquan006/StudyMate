const assert=require('node:assert/strict'),fs=require('node:fs'),path=require('node:path'),{execFileSync}=require('node:child_process');
const {chromium}=require('playwright');
const base=process.env.QA_BASE_URL,out=process.env.QA_OUTPUT,php=process.env.QA_PHP;
assert(process.env.QA_ALLOW_WRITES==='1'&&new URL(base).hostname==='127.0.0.1');
const report={checks:[],errors:[],screenshots:[]};
const check=(v,n)=>{assert(v,n);report.checks.push(n)};
function db(s){return JSON.parse(execFileSync(php,['-r',"require 'app/bootstrap.php';"+s],{encoding:'utf8'}))}
const image=Buffer.from(db('$im=imagecreatetruecolor(80,80);imagefill($im,0,0,imagecolorallocate($im,30,140,100));ob_start();imagepng($im);echo json_encode(base64_encode(ob_get_clean()));'),'base64');
function history(){return db("$s=[];foreach(['enrollments','lesson_progress','quiz_attempts','quiz_answers'] as $t)$s[$t]=all('SELECT * FROM '.$t.' ORDER BY id');echo json_encode(hash('sha256',json_encode($s)));")}
(async()=>{let browser;try{
 browser=await chromium.launch({executablePath:process.env.QA_CHROMIUM,headless:true,args:['--no-sandbox','--disable-dev-shm-usage']});
 const c=await browser.newContext({viewport:{width:1366,height:900}}),p=await c.newPage();p.on('pageerror',e=>report.errors.push(e.message));
 await p.goto(base+'/index.php?page=login');await p.getByLabel('Email',{exact:true}).fill('student1@studymate.test');await p.getByLabel('Mật khẩu',{exact:true}).fill(process.env.QA_DEMO_PASSWORD);
 await Promise.all([p.waitForNavigation(),p.locator('form[method=post]:has(input[value=login]) button').click()]);
 const learn=base+'/index.php?page=learn&id=2&lesson=7',key='studymate:notes:v1:1:2:7';
 const response=await p.goto(learn);const policy=response.headers()['content-security-policy'];
 check(policy.includes("img-src 'self' data: blob:")&&policy.includes('https://www.youtube.com/iframe_api'),'merged CSP supports image preview and focus YouTube API');
 await p.locator('#lesson-notes-details').evaluate(x=>x.open=true);await p.locator('#lesson-note-input').fill('Ghi chú giữ nguyên sau cập nhật hồ sơ và avatar.');await p.locator('#lesson-note-save').click();
 const xp=await p.locator('#study-buddy').getAttribute('data-xp');const before=history();
 await p.goto(base+'/index.php?page=profile');
 check(await p.locator('#study-buddy,#lesson-notes,#study-space').count()===0,'profile excludes learning widgets');
 check(await p.locator('script[src*=study-buddy],script[src*=lesson-notes],script[src*=study-space]').count()===0,'profile does not load learning widget scripts');
 const f=p.locator('form:has(input[value=profile_save])');await f.locator('input[name=name]').fill('Tú QA giao thoa');await f.locator('textarea[name=bio]').fill('Hồ sơ kiểm thử dùng một lần.');await f.locator('input[type=file]').setInputFiles({name:'qa.png',mimeType:'image/png',buffer:image});
 check((await p.locator('[data-image-preview]').getAttribute('src')).startsWith('blob:'),'real file selection previews with merged CSP');
 let seen=[];const cancel=async d=>{seen.push(d.type());await d.dismiss()};p.on('dialog',cancel);
 await p.locator('#display-settings').evaluate(x=>x.open=true);await p.locator('#display-language').selectOption('en');
 check(seen.length===1&&seen[0]==='confirm','dirty profile language change shows one confirmation');
 check(await p.locator('html').getAttribute('lang')==='vi'&&await f.locator('input[name=name]').inputValue()==='Tú QA giao thoa'&&await f.locator('input[type=file]').evaluate(x=>x.files.length)===1,'cancel retains profile text and selected file');p.off('dialog',cancel);
 await Promise.all([p.waitForNavigation(),f.locator('button').last().click()]);
 check(await p.locator('.profile-summary h2').innerText()==='Tú QA giao thoa'&&await p.locator('.profile-summary img').evaluate(x=>x.complete&&x.naturalWidth>0),'profile save retains working avatar');
 check(history()===before,'profile save preserves enrollment, completion and quiz history');
 await p.goto(learn);check(await p.locator('#lesson-note-input').inputValue()==='Ghi chú giữ nguyên sau cập nhật hồ sơ và avatar.','saved lesson note survives profile save');
 check(await p.locator('#study-buddy').getAttribute('data-xp')===xp,'profile save and avatar do not change XP');
 check(await p.locator('#site-header img.profile-avatar').evaluate(x=>x.complete&&x.naturalWidth>0),'student header displays uploaded avatar with utilities');
 for(const lang of ['vi','en'])for(const theme of ['light','dark'])for(const width of [390,768,834,991,1366]){
  await p.setViewportSize({width,height:600});await p.goto(learn+`&ui_language=${lang}&ui_theme=${theme}`);
  check(await p.evaluate(()=>document.documentElement.scrollWidth<=innerWidth),`${lang}/${theme}/${width} combined avatar and utility layout contains overflow`);
  check(await p.locator('#lesson-note-input').inputValue()==='Ghi chú giữ nguyên sau cập nhật hồ sơ và avatar.'&&await p.locator('#study-buddy').getAttribute('data-xp')===xp,`${lang}/${theme}/${width} notes and XP persist`);
  if(width===390||width===1366){const file=`combined-${lang}-${theme}-${width}.png`;await p.screenshot({path:path.join(out,file)});report.screenshots.push(file)}
 }
 const {verifyLessonOutline}=require(process.env.QA_SOURCE+'/tests/quan/lesson_outline.cjs');
 await verifyLessonOutline({page:p,base:base+'/index.php',check,artifacts:out});
 check(report.errors.length===0,'no JavaScript page errors');
 console.log(JSON.stringify({checks:report.checks.length,errors:report.errors.length}));
}catch(e){report.failure=e.stack;console.error(e.stack);process.exitCode=1}finally{if(browser)await browser.close();fs.writeFileSync(path.join(out,'cross-results.json'),JSON.stringify(report,null,2)+'\n')}})();
