// Run only on a disposable, seeded local database; creates QA users/progress/attempts.
const assert=require('node:assert/strict');const fs=require('node:fs');const path=require('node:path');const crypto=require('node:crypto');
const {chromium}=require(process.env.QA_PLAYWRIGHT_MODULE||'playwright');
const base=process.env.QA_BASE_URL||'http://127.0.0.1:8085';
if(process.env.QA_ALLOW_WRITES!=='1'||!['127.0.0.1','localhost'].includes(new URL(base).hostname))throw Error('Require QA_ALLOW_WRITES=1 and a disposable seeded loopback app.');
const out=process.env.QA_OUTPUT||'/tmp/studymate-form-feedback';fs.mkdirSync(out,{recursive:true});
const files=['app/layout.php','public/assets/app.css','public/assets/forms.js'];
const report={started:new Date().toISOString(),baseCommit:process.env.QA_BASE_SHA||'d21f08e95585286df1a77be4815e3d44d120ee5b',sourceFilesSha256:Object.fromEntries(files.map(f=>[f,crypto.createHash('sha256').update(fs.readFileSync(f)).digest('hex')])),checks:[],browsers:{},errors:[]};
const browsers=JSON.parse(process.env.QA_BROWSERS||'[{"name":"chromium","path":"/usr/bin/chromium"}]');
(async()=>{try{for(const engine of browsers){
 const b=await chromium.launch({executablePath:engine.path,headless:true,args:['--no-sandbox']});report.browsers[engine.name]=b.version();
 const c=await b.newContext({viewport:{width:390,height:844}});const p=await c.newPage();p.on('pageerror',e=>report.errors.push(e.message));
 const pass=name=>{report.checks.push({browser:engine.name,name,status:'passed'});console.log('PASS',engine.name,name);};
 const go=q=>p.goto(base+'/index.php?page='+q);
 const form=a=>p.locator(`form:has(input[name=action][value="${a}"])`);
 const submit=async a=>Promise.all([p.waitForNavigation(),form(a).locator('button').last().click()]);
 try{
 await go('register');await form('register').locator('button').click();assert.equal(await form('register').getAttribute('aria-busy'),null);pass('native required validation does not set pending');
 const email=`forms-${engine.name}-${Date.now()}@example.test`;const password=process.env.QA_DEMO_PASSWORD||'StudyMate2026!';
 await p.getByLabel('Họ và tên',{exact:true}).fill('An Forms QA');await p.getByLabel('Email',{exact:true}).fill(email);await p.getByLabel('Mật khẩu',{exact:true}).fill(password);await p.getByLabel('Xác nhận mật khẩu',{exact:true}).fill(password);await submit('register');assert.match(p.url(),/page=student/);pass('native registration posts and redirects');
 const observations=await p.evaluate(()=>{const f=document.querySelector('input[value=logout]').form;const events=[];const observe=e=>{if(e.target===f){events.push({blocked:e.defaultPrevented,busy:f.getAttribute('aria-busy')});e.preventDefault();}};document.addEventListener('submit',observe);f.requestSubmit();f.requestSubmit();document.removeEventListener('submit',observe);return {events,statuses:f.querySelectorAll('[role=status]').length,cursor:getComputedStyle(f.querySelector('button')).cursor,disabled:f.querySelector('button').disabled};});
 assert.deepEqual(observations.events,[{blocked:false,busy:'true'},{blocked:true,busy:'true'}]);assert.equal(observations.statuses,1);assert.equal(observations.cursor,'wait');assert.equal(observations.disabled,false);pass('pending status/style and duplicate event suppression preserve enabled controls');
 await p.evaluate(()=>window.dispatchEvent(new PageTransitionEvent('pageshow',{persisted:true})));assert.equal(await form('logout').getAttribute('aria-busy'),null);assert.equal(await form('logout').getByRole('status').count(),0);pass('pageshow handler clears pending for intentional resubmission (synthetic event)');
 await go('detail&id=1');await submit('enroll');await go('learn&id=1');const ids=await p.locator('form:has(input[value=last_lesson]) [name=lesson_id]').evaluateAll(es=>es.map(e=>e.value));assert.equal(ids.length,6);
 for(const id of ids){await go('learn&id=1&lesson='+id);await submit('complete');}pass('six lesson completion POSTs still work');
 const request=p.waitForRequest(r=>r.method()==='POST');await Promise.all([p.waitForNavigation(),p.getByRole('button',{name:'Tóm tắt',exact:true}).click()]);const fields=new URLSearchParams((await request).postData());assert.equal(fields.get('mode'),'summarize');assert.equal(fields.get('action'),'ai');assert(fields.get('csrf'));assert.match(await p.getByRole('alert').innerText(),/AI chưa được cấu hình/);pass('named AI submitter and CSRF retained; unavailable AI leaves lesson visible');assert.equal(await p.locator('.lesson-text').count(),1);
 await go('quiz&id=1');const radio=p.locator('.question input[type=radio]').first();await radio.focus();await p.keyboard.press('Space');assert(await radio.isChecked());pass('native keyboard quiz radio still works');
 const dialogEvent=p.waitForEvent('dialog',{timeout:5000});const reload=p.reload({timeout:2500}).catch(()=>null);const dialog=await dialogEvent;assert.equal(dialog.type(),'beforeunload');await dialog.dismiss();await reload;assert(await radio.isChecked());pass('refresh warns; cancelling keeps selected answer');
 let unexpected=0;const handler=async d=>{unexpected++;await d.accept();};p.on('dialog',handler);await form('submit_quiz').locator('[name=confirm]').check();await submit('submit_quiz');p.off('dialog',handler);assert.equal(unexpected,0);assert.match(p.url(),/page=result/);assert.equal(await p.locator('.review-question').count(),5);pass('intentional quiz submit has no leave warning and renders real result');
 await go('profile');await submit('logout');const r=await go('student');assert.equal(r.status(),401);pass('logout still posts and invalidates private page');
 const plain=await b.newContext({javaScriptEnabled:false});const np=await plain.newPage();await np.goto(base+'/index.php?page=login');await np.locator('[name=email]').fill(email);await np.locator('[name=password]').fill(password);await Promise.all([np.waitForNavigation(),np.locator('form:has(input[value=login]) button').click()]);assert.match(np.url(),/page=student/);await plain.close();pass('native login works with JavaScript disabled');
 }finally{await b.close();}
}assert.equal(report.errors.length,0);}catch(e){report.failure=e.stack;console.error(e);process.exitCode=1;}finally{report.finished=new Date().toISOString();fs.writeFileSync(path.join(out,'results.json'),JSON.stringify(report,null,2)+'\n');}})();
