const assert=require('node:assert/strict'),fs=require('node:fs'),crypto=require('node:crypto');
const {execFileSync}=require('node:child_process');const {chromium}=require('playwright');
const base=process.env.QA_BASE_URL,out=process.env.QA_OUTPUT;
assert(process.env.QA_ALLOW_WRITES==='1'&&new URL(base).hostname==='127.0.0.1');
const report={checks:[],errors:[],screenshots:[]};
function check(ok,name){assert(ok,name);report.checks.push(name)}
function db(code){return JSON.parse(execFileSync(process.env.QA_PHP,['-r',"require 'app/bootstrap.php';require 'database/seed.php';require 'app/services.php';"+code],{encoding:'utf8'}))}
function snapshot(){return db("$s=[];foreach(['users','categories','courses','chapters','lessons','enrollments','lesson_progress','quizzes','questions','answers','quiz_attempts','quiz_answers'] as $t)$s[$t]=all('SELECT * FROM '.$t.' ORDER BY id');echo json_encode(hash('sha256',json_encode($s)));")}
function files(){return db("$s=[];foreach(glob($storage.'/uploads/*')?:[] as $f)$s[basename($f)]=hash_file('sha256',$f);echo json_encode($s);")}
function row(table,id){return db(`echo json_encode(one('SELECT * FROM ${table} WHERE id=?',[${id}]));`)}
function raster(type,width=240,height=120){const x=db(`$im=imagecreatetruecolor(${width},${height});$color=imagecolorallocate($im,20,130,90);imagefill($im,0,0,$color);ob_start();${type==='png'?'imagepng($im)':'imagejpeg($im)'};echo json_encode(base64_encode(ob_get_clean()));`);return Buffer.from(x,'base64')}
const png=raster('png'),jpg=raster('jpg');
// Minimal EXIF Orientation=6: a phone JPEG requiring a clockwise rotation.
const tiff=Buffer.from('49492a0008000000010012010300010000000600000000000000','hex');
const exifPayload=Buffer.concat([Buffer.from('Exif\0\0'),tiff]),exifHeader=Buffer.alloc(4);
exifHeader.writeUInt16BE(0xffe1,0);exifHeader.writeUInt16BE(exifPayload.length+2,2);
const phoneJpg=Buffer.concat([jpg.subarray(0,2),exifHeader,exifPayload,jpg.subarray(2)]);
const file=(buffer=png,name='avatar.png',mimeType='image/png')=>({name,mimeType,buffer});
async function login(page,user){await page.goto(base+'?page=login');await page.locator('[name=email]').fill(user+'@studymate.test');await page.locator('[name=password]').fill(process.env.QA_DEMO_PASSWORD);await submit(page,page.locator('form:has(input[value=login])'))}
async function submit(page,form){await Promise.all([page.waitForNavigation(),form.locator('button[type=submit],button:not([type])').last().press('Enter')])}
async function values(form){return form.evaluate(f=>Object.fromEntries([...new FormData(f)].filter(([,v])=>typeof v==='string')))}
async function post(context,data,image){const multipart={...data};if(image)multipart.image={name:image.name,mimeType:image.mimeType,buffer:image.buffer};return context.request.post(new URL('/index.php',base).href,{multipart,maxRedirects:0})}
async function fields(page){return values(page.locator('form:has(input[value=profile_save])'))}
async function shot(page,name){await page.screenshot({path:out+'/'+name+'.png',fullPage:true});report.screenshots.push(name+'.png')}

(async()=>{
 const browser=await chromium.launch({executablePath:process.env.QA_CHROMIUM,headless:true,args:['--no-sandbox','--disable-dev-shm-usage']});
 try{
  report.browser=browser.version();
  const context=await browser.newContext({viewport:{width:1366,height:900}}),page=await context.newPage();page.on('pageerror',e=>report.errors.push(e.message));
  for(const [user,id] of [['student1',1],['instructor1',3],['admin',5]]){
   await login(page,user);await page.goto(base+'?page=profile');
   check(await page.locator('[name=qualifications]').count()===(user==='instructor1'?1:0),'qualifications only offered to instructor '+user);
   const before=row('users',id);await page.locator('[name=name]').fill('Tú QA '+user);await page.locator('[name=birth_date]').fill('2000-02-29');await page.locator('[name=bio]').fill('Giới thiệu tiếng Việt <script>window.__profileXSS=true</script>');
   if(user==='instructor1')await page.locator('[name=qualifications]').fill('Thạc sĩ Công nghệ thông tin\nChuyên môn: lập trình web');
   await page.locator('[name=image]').setInputFiles(file());
   await page.waitForFunction(()=>document.querySelector('[data-image-preview]').naturalWidth>0&&document.querySelector('[data-image-preview]').src.startsWith('blob:'));
   check(true,'local image preview loads under CSP '+user);
   await submit(page,page.locator('form:has(input[value=profile_save])'));
   const after=row('users',id);
   check(after.name==='Tú QA '+user&&after.birth_date==='2000-02-29'&&after.bio.includes('<script>'),'profile fields persisted '+user);
   check(['email','role','password','auth_version','id'].every(k=>before[k]===after[k]),'account identity and permissions preserved '+user);
   check(/^[a-f0-9]{32}\.png$/.test(after.avatar_path),'generated avatar filename '+user);
   check(await page.locator('.account-link img').count()===1&&await page.locator('.profile-summary img').count()===1,'avatar appears in header and profile '+user);
   check(!(await page.evaluate(()=>window.__profileXSS)),'profile markup never executes '+user);
  }
  await page.goto(base+'?page=detail&id=2');
  check((await page.locator('.instructor-profile').innerText()).includes('Thạc sĩ')&&!(await page.locator('body').innerText()).includes('2000-02-29'),'public instructor qualifications shown without birth date');
  const anon=await browser.newContext();
  const instructorAvatar=await anon.request.get(new URL('/media.php?kind=avatar&id=3',base).href);
  check(instructorAvatar.status()===200&&instructorAvatar.headers()['x-content-type-options']==='nosniff','public instructor avatar serves an image with nosniff');
  check((await anon.request.get(new URL('/media.php?kind=avatar&id=1',base).href)).status()===404,'student avatar is not public');
  check((await anon.request.get(new URL('/media.php?kind=course&id=2abc',base).href)).status()===404,'media endpoint rejects suffix ID');
  check((await anon.request.get(new URL('/media.php?kind=avatar&id[]=3',base).href)).status()===404,'media endpoint rejects array ID');
  await anon.close();

  await login(page,'student1');await page.goto(base+'?page=profile');const valid=await fields(page);
  for(const [label,changes,status] of [
   ['another account',{user_id:'2'},422],['role escalation',{role:'admin'},422],['email change',{email:'other@example.test'},422],['raw image path',{avatar_path:'../../file.png'},422],
   ['forged qualifications',{qualifications:'admin'},403],['future date',{birth_date:'2999-01-01'},422],['invalid leap date',{birth_date:'2023-02-29'},422],['before minimum',{birth_date:'1899-12-31'},422],['noncanonical date',{birth_date:'2000-2-9'},422],['array name',{'name[]':'Tú',name:undefined},422],['long name',{name:'X'.repeat(81)},422],['long bio',{bio:'X'.repeat(2001)},422],['null byte',{bio:'a\0b'},422],['bad CSRF',{csrf:'wrong'},419]
  ]){
   const data={...valid,...changes};for(const k in data)if(data[k]===undefined)delete data[k];
   const before=snapshot(),beforeFiles=files(),response=await post(context,data,file());
   check(response.status()===status,label+' is rejected with '+status);
   check(snapshot()===before&&JSON.stringify(files())===JSON.stringify(beforeFiles),label+' preserves 12 tables and image files');
  }
  const invalidImages=[
   ['SVG',Buffer.from('<svg xmlns="http://www.w3.org/2000/svg" onload="alert(1)"/>'),'attack.svg','image/svg+xml'],
   ['PHP extension',jpg,'attack.php','image/jpeg'],['forged content',Buffer.from('this is not an image'),'fake.png','image/png'],
   ['MIME mismatch',png,'fake.jpg','image/jpeg'],['truncated image',png.subarray(0,40),'broken.png','image/png'],
   ['empty image',Buffer.alloc(0),'empty.png','image/png'],['over 2 MB',Buffer.concat([png,Buffer.alloc(2*1024*1024)]),'large.png','image/png'],
   ['PHP upload limit',Buffer.concat([png,Buffer.alloc(3200000)]),'large.png','image/png'],['over dimension',raster('png',4097,2),'wide.png','image/png'],['over pixel count',raster('png',4000,2001),'pixels.png','image/png'],
  ];
  for(const [label,buffer,name,mime] of invalidImages){
   const before=snapshot(),beforeFiles=files(),response=await post(context,valid,file(buffer,name,mime));
   check(response.status()===422,label+' rejected by server');check(snapshot()===before&&JSON.stringify(files())===JSON.stringify(beforeFiles),label+' preserves DB/files');
  }
  let before=snapshot(),beforeFiles=files();let response=await post(context,{...valid,remove_image:'1'},file());
  check(response.status()===422&&snapshot()===before&&JSON.stringify(files())===JSON.stringify(beforeFiles),'conflicting replace/remove rejected without changes');
  const malformed={...valid,'image[]':{name:'nested.png',mimeType:'image/png',buffer:png}};
  response=await context.request.post(new URL('/index.php',base).href,{multipart:malformed,maxRedirects:0});
  check(response.status()===422&&snapshot()===before&&JSON.stringify(files())===JSON.stringify(beforeFiles),'array upload rejected without changes');
  response=await post(context,valid,file(Buffer.concat([png,Buffer.alloc(4300000)]),'request.png','image/png'));
  check(response.status()===413&&snapshot()===before&&JSON.stringify(files())===JSON.stringify(beforeFiles),'oversized multipart request returns 413 and preserves DB/files');

  // A real DB failure after decoding must clean up newly written image bytes.
  db("if($config['db_driver']==='mysql')run(\"ALTER TABLE users ADD CONSTRAINT tu_media_reject CHECK(name <> 'Rollback QA')\");else run(\"CREATE TRIGGER tu_media_reject BEFORE UPDATE ON users WHEN NEW.name='Rollback QA' BEGIN SELECT RAISE(ABORT,'forced QA update failure'); END\");echo json_encode(true);");
  response=await post(context,{...valid,name:'Rollback QA'},file());
  check(response.status()===500&&snapshot()===before&&JSON.stringify(files())===JSON.stringify(beforeFiles),'DB rollback cleans new image while preserving old image/data');
  db("if($config['db_driver']==='mysql')run('ALTER TABLE users DROP CHECK tu_media_reject');else run('DROP TRIGGER tu_media_reject');echo json_encode(true);");
  let old=row('users',1).avatar_path;
  response=await post(context,{...valid,birth_date:'',bio:'Tên đã cập nhật'},file(Buffer.concat([phoneJpg,Buffer.from('<?php malicious(); ?>')]),'photo.jpg','image/jpeg'));
  check(response.status()===303&&row('users',1).birth_date===null,'optional birth date can be cleared with JPEG upload');
  check(!Object.keys(files()).includes(old),'avatar replacement removes old bytes');
  let imageKey=row('users',1).avatar_path;
  check(!db(`echo json_encode(str_contains(file_get_contents(mediaFile('${imageKey}')),'<?php'));`),'JPEG re-encoding strips trailing payload');
  check(JSON.stringify(db(`$s=getimagesize(mediaFile('${imageKey}'));echo json_encode([$s[0],$s[1]]);`))==='[120,240]','phone JPEG EXIF orientation is applied before metadata is stripped');
  response=await post(context,{...valid,remove_image:'1'});await page.reload();
  check(response.status()===303&&row('users',1).avatar_path===null&&!Object.keys(files()).includes(imageKey)&&await page.locator('.profile-summary .avatar-placeholder').count()===1,'avatar removal restores initial fallback and removes bytes');

  const authCases=[['student1','course','2',403],['instructor2','course','2',404],['admin','course','2',403],['instructor1','category','1',403],['student1','category','1',403]];
  for(const [user,kind,id,status] of authCases){await login(page,user);await page.goto(base+'?page=profile');const csrf=(await fields(page)).csrf;before=snapshot();beforeFiles=files();
   response=await post(context,{action:'media_save',media_kind:kind,[kind==='course'?'course_id':'category_id']:id,csrf,return_page:'profile'},file());
   check(response.status()===status&&snapshot()===before&&JSON.stringify(files())===JSON.stringify(beforeFiles),'permission '+user+'/'+kind+' preserves data/files');
  }
  await login(page,'instructor1');await page.goto(base+'?page=edit&id=2');let mediaForm=page.locator('form:has(input[value=media_save])');let mediaFields=await values(mediaForm);
  for(const id of ['2abc','0','','01','9223372036854775808','999999']){before=snapshot();beforeFiles=files();response=await post(context,{...mediaFields,course_id:id},file());check(response.status()===(id==='999999'?404:422)&&snapshot()===before&&JSON.stringify(files())===JSON.stringify(beforeFiles),'course image rejects invalid/missing object '+id)}
  await mediaForm.locator('[name=image]').setInputFiles(file(jpg,'course.jpg','image/jpeg'));await submit(page,mediaForm);
  imageKey=row('courses',2).image_path;
  check(/\.jpg$/.test(imageKey),'course JPEG upload persisted');await page.goto(base+'?page=detail&id=2');
  check((await page.locator('.photo-cover img').getAttribute('src')).includes('kind=course'),'uploaded course image takes precedence on detail');
  await page.goto(base+'?page=courses');check(await page.locator('.course-card img[src*="kind=course"]').count()>=1,'uploaded image appears on course card');
  await page.goto(base+'?page=edit&id=3');mediaForm=page.locator('form:has(input[value=media_save])');await mediaForm.locator('[name=image]').setInputFiles(file());await submit(page,mediaForm);
  const guest=await browser.newContext();check((await guest.request.get(new URL('/media.php?kind=course&id=3',base).href)).status()===404,'draft course image remains private');await guest.close();
  await page.goto(base+'?page=edit&id=2');mediaForm=page.locator('form:has(input[value=media_save])');
  await mediaForm.locator('[name=remove_image]').check();await submit(page,mediaForm);await page.goto(base+'?page=detail&id=2');
  check(row('courses',2).image_path===null&&!Object.keys(files()).includes(imageKey)&&(await page.locator('.photo-cover img').getAttribute('src')).includes('javascript-foundations.jpg'),'course removal restores Quân curated fallback');

  await login(page,'admin');await page.goto(base+'?page=admin&tab=categories');
  mediaForm=page.locator('form:has(input[value=media_save]):has(input[name=category_id][value="1"])');
  await mediaForm.locator('..').locator('summary').click();await mediaForm.locator('[name=image]').setInputFiles(file());await submit(page,mediaForm);
  imageKey=row('categories',1).image_path;await page.goto(base+'?page=home');
  check(await page.locator('.field-card img[src*="kind=category"][src*="id=1"]').count()===1,'category upload appears on public home');
  db(`unlink(mediaFile('${imageKey}'));echo json_encode(true);`);await page.reload();
  check(await page.locator('.field-card img[src*="kind=category"][src*="id=1"]').count()===0,'missing file falls back without a broken category image');
  await page.goto(base+'?page=admin&tab=categories');mediaForm=page.locator('form:has(input[value=media_save]):has(input[name=category_id][value="1"])');await mediaForm.locator('..').locator('summary').click();
  await mediaForm.locator('[name=remove_image]').check();await submit(page,mediaForm);check(row('categories',1).image_path===null,'category image removal clears missing-file reference');

  // Retain business form contracts, and exercise one new-course lifecycle.
  await login(page,'instructor1');await page.goto(base+'?page=edit');const courseForm=page.locator('form:has(input[value=course_save])');
  check((await values(courseForm)).course_id==='0','new-course form retains course_id=0');
  await courseForm.locator('[name=title]').fill('Khóa học QA hình ảnh');await courseForm.locator('[name=description]').fill('Nội dung riêng để kiểm tra ảnh khóa học và quyền giảng viên.');await submit(page,courseForm);
  const createdId=Number(new URL(page.url()).searchParams.get('id'));check(createdId>3&&row('courses',createdId).instructor_id==3,'native new course form still creates owned draft');
  mediaForm=page.locator('form:has(input[value=media_save])');await mediaForm.locator('[name=image]').setInputFiles(file());await submit(page,mediaForm);const deletedKey=row('courses',createdId).image_path;
  response=await post(context,{action:'course_delete',course_id:String(createdId),confirm:'1',csrf:(await values(page.locator('form:has(input[value=media_save])'))).csrf,return_page:'instructor'});
  check(response.status()===303&&row('courses',createdId)===null&&!Object.keys(files()).includes(deletedKey),'deleting image-bearing empty draft removes its image');

  for(const [language,theme] of [['vi','light'],['vi','dark'],['en','light'],['en','dark']]){
   for(const width of [390,768,1366]){
    await page.setViewportSize({width,height:900});await page.goto(base+'?page=profile&ui_language='+language+'&ui_theme='+theme);
    check(await page.locator('html').getAttribute('lang')===language&&await page.locator('html').getAttribute('data-theme')===theme,'profile display '+language+'/'+theme+'/'+width);
    check(await page.evaluate(()=>document.documentElement.scrollWidth<=innerWidth),'profile no overflow '+language+'/'+theme+'/'+width);
    if(language==='en')check(await page.getByRole('button',{name:'Save profile',exact:true}).count()===1&&await page.getByLabel('Qualifications and expertise').count()===1,'profile English labels '+theme+'/'+width);
    if(width!==768)await shot(page,'profile-'+language+'-'+theme+'-'+width);
    await page.goto(base+'?page=edit&id=2');check(await page.evaluate(()=>document.documentElement.scrollWidth<=innerWidth),'course editor no overflow '+language+'/'+theme+'/'+width);
   }
  }
  // Language-change cancel must retain unsaved text and selected file.
  await page.goto(base+'?page=profile&ui_language=vi');await page.locator('[name=name]').fill('Bản chưa lưu');await page.locator('[name=image]').setInputFiles(file());
  await page.locator('#display-settings summary').click();let dialogs=0;page.once('dialog',async d=>{dialogs++;await d.dismiss()});await page.locator('#display-language').selectOption('en');
  check(dialogs===1&&await page.locator('[name=name]').inputValue()==='Bản chưa lưu'&&await page.locator('[name=image]').evaluate(x=>x.files.length)===1,'cancel language change retains profile draft/file with one confirmation');
  const nojs=await browser.newContext({storageState:await context.storageState(),javaScriptEnabled:false});const plain=await nojs.newPage();await plain.goto(base+'?page=profile');
  await plain.locator('[name=bio]').fill('Lưu hồ sơ khi tắt JavaScript');await submit(plain,plain.locator('form:has(input[value=profile_save])'));
  check(row('users',3).bio==='Lưu hồ sơ khi tắt JavaScript','profile saves through native forms without JavaScript');await nojs.close();

  await login(page,'student2');await page.goto(base+'?page=detail&id=2');await submit(page,page.locator('form:has(input[value=enroll])'));
  const lessons=db("echo json_encode(array_column(courseLessons(2),'id'));");
  for(const id of lessons){await page.goto(base+'?page=learn&id=2&lesson='+id);await submit(page,page.locator('form:has(input[value=complete])'))}
  await page.goto(base+'?page=quiz&id=2');const answers=db("$out=[];foreach(questionsFor(2) as $q)foreach($q['answers'] as $a)if($a['is_correct'])$out[$q['id']]=$a['id'];echo json_encode($out);");
  for(const [qid,aid] of Object.entries(answers))await page.locator('input[name="answers['+qid+']"][value="'+aid+'"]').check();await page.locator('[name=confirm]').check();const quizFields=await values(page.locator('form:has(input[value=submit_quiz])'));
  await submit(page,page.locator('form:has(input[value=submit_quiz])'));
  check(Number(db("echo json_encode(val('SELECT score_percent FROM quiz_attempts WHERE user_id=2 ORDER BY id DESC LIMIT 1'));"))===100,'enroll, six completions and native quiz still reach 100%');
  before=snapshot();response=await post(context,quizFields);check(response.status()===303&&snapshot()===before,'quiz UUID replay still preserves all 12 tables');
  check(report.errors.length===0,'no JavaScript page errors');
  console.log(JSON.stringify({checks:report.checks.length,errors:report.errors.length,screenshots:report.screenshots.length}));
 }catch(error){report.failure=error.stack;throw error}
 finally{fs.writeFileSync(out+'/results.json',JSON.stringify(report,null,2));await browser.close()}
})().catch(e=>{console.error(e);process.exitCode=1});
