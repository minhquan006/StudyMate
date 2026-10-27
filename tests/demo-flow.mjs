import { createRequire } from 'node:module';
import { mkdir, writeFile } from 'node:fs/promises';
import assert from 'node:assert/strict';
import { execFileSync } from 'node:child_process';
const {chromium}=createRequire(import.meta.url)('playwright');
const base=process.env.QA_BASE_URL || 'http://127.0.0.1:18083';
const out=process.env.QA_OUTPUT || '/tmp/studymate-demo-evidence';
const password=process.env.QA_DEMO_PASSWORD;
assert(password);
await mkdir(out,{recursive:true});
const browser=await chromium.launch({executablePath:process.env.QA_CHROME});
const ctx=await browser.newContext({viewport:{width:1920,height:1080},recordVideo:{dir:out,size:{width:1920,height:1080}}});
const page=await ctx.newPage();
const form=action=>page.locator(`form:has(input[name="action"][value="${action}"])`);
const report={recordedAt:new Date().toISOString(),sourceSha:execFileSync('git',['rev-parse','HEAD'],{encoding:'utf8'}).trim(),browser:browser.version(),checks:[],limitations:['Automated rehearsal, not joint human acceptance.','AI disabled: real ask/summarize not accepted.','Silent raw footage; three-member narration/editing still required.']};
const shot=async name=>{await page.screenshot({path:`${out}/${name}.png`});report.checks.push(name);};
async function login(role){await page.goto(`${base}/index.php?page=login`);await page.getByLabel('Email',{exact:true}).fill(`${role}@studymate.test`);await page.getByLabel('Mật khẩu',{exact:true}).fill(password);await form('login').locator('button').click();}
try {
 await login('instructor1');
 await page.goto(`${base}/index.php?page=edit`);
 const title=`AN demo ${Date.now()}`;
 await form('course_save').locator('[name=title]').fill(title);
 await form('course_save').locator('[name=description]').fill('Synthetic course for the recorded acceptance rehearsal, not a real student dataset.');
 await form('course_save').locator('button').click();
 const cid=new URL(page.url()).searchParams.get('id');assert(cid);
 await page.goto(`${base}/index.php?page=edit&id=${cid}&tab=content`);
 await form('chapter_save').locator('[name=title]').fill('Demo chapter');
 await form('chapter_save').locator('button').click();
 await form('lesson_save').locator('[name=chapter_id]').selectOption({label:'Demo chapter'});
 await form('lesson_save').locator('[name=title]').fill('Demo learning objective');
 await form('lesson_save').locator('[name=body]').fill('# Demo lesson\nA database transaction commits related changes together. This is synthetic content for the acceptance rehearsal.');
 await form('lesson_save').locator('button').click();
 await page.goto(`${base}/index.php?page=edit&id=${cid}&tab=quiz`);
 for(let i=0;i<5;i++){
  await page.locator(`[name="questions[${i}][prompt]"]`).fill(`Demo question ${i+1}: choose the described transaction outcome`);
  for(let j=0;j<4;j++) await page.locator(`[name="questions[${i}][answers][${j}]"]`).fill(j===0?'Related changes commit together':`Synthetic alternative ${j}`);
  await page.locator(`[name="questions[${i}][correct]"][value="0"]`).check();
 }
 await form('quiz_save').locator('button').click();
 const publish=page.locator('form:has(input[name="target"][value="published"])');
 await publish.locator('[name=confirm]').check();await publish.locator('button').click();
 assert.match(await page.locator('.editor-actions').innerText(),/Đã xuất bản/);
 await shot('01-instructor-published');
 await form('logout').locator('button').click();
 await page.goto(`${base}/index.php?page=register`);
 const email=`an-demo-${Date.now()}@studymate.test`;
 await form('register').locator('[name=name]').fill('An demo student');
 await form('register').locator('[name=email]').fill(email);
 await form('register').locator('[name=password]').fill(password);
 await form('register').locator('[name=password_confirmation]').fill(password);
 await form('register').locator('button').click();
 await page.goto(`${base}/index.php?page=courses&q=${encodeURIComponent(title)}`);
 assert.equal(await page.locator('.course-card').count(),1);
 await page.locator('.course-card a').click();await form('enroll').locator('button').click();
 await form('complete').locator('button').click();
 await page.reload();assert.match(await page.locator('.progress-meta').innerText(),/100/);
 await shot('02-student-progress-persisted');
 // Explicitly exercise the unavailable-provider UI; this is not a successful AI call.
 await form('ai').locator('[name=question]').fill('What does a transaction guarantee?');
 const aiResponse=page.waitForResponse(r=>r.request().method()==='POST');
 await form('ai').locator('[name=mode][value=ask]').click();assert.equal((await aiResponse).status(),503);
 assert.match(await page.getByRole('alert').innerText(),/AI chưa được cấu hình/);
 await shot('03-ai-unconfigured');
 await page.goto(`${base}/index.php?page=quiz&id=${cid}`);
 for(const field of await page.locator('fieldset.question').all()) await field.locator('input[type=radio]').first().check();
 await form('submit_quiz').locator('[name=confirm]').check();
 await form('submit_quiz').locator('button').click();
 assert.equal(new URL(page.url()).searchParams.get('page'),'result');
 assert.match(await page.locator('.score').innerText(),/100/);await shot('04-student-result');
 const forbidden=await page.goto(`${base}/index.php?page=admin`);assert.equal(forbidden.status(),403);await shot('05-wrong-role-blocked');
 const adminCtx=await browser.newContext();const admin=await adminCtx.newPage();
 await admin.goto(`${base}/index.php?page=login`);await admin.getByLabel('Email',{exact:true}).fill('admin@studymate.test');await admin.getByLabel('Mật khẩu',{exact:true}).fill(password);await admin.locator('form:has(input[value="login"]) button').click();
 await admin.goto(`${base}/index.php?page=admin&tab=users&q=${encodeURIComponent(email)}`);
 const lock=admin.locator('form:has(input[value="user_status"])');await lock.locator('[name=confirm]').check();await lock.locator('button').click();
 const revoked=await page.goto(`${base}/index.php?page=student`);assert.equal(revoked.status(),401);await shot('06-old-session-revoked');
 await adminCtx.close();
 report.courseId=cid;report.studentEmail=email;
} catch(error){report.error=error.message;await page.screenshot({path:`${out}/demo-failure.png`}).catch(()=>{});throw error;}
finally {
 await ctx.close();report.videoFile=(await page.video().path()).split('/').at(-1);await browser.close();
 await writeFile(`${out}/demo.json`,JSON.stringify(report,null,2)+'\n');
}
console.log(JSON.stringify(report,null,2));
