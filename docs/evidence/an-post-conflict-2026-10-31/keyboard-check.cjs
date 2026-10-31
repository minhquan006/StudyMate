const {chromium}=require('playwright');
const fs=require('fs');
(async()=>{
const root='/workspace/pr15-validation';
const password=fs.readFileSync(root+'/pr15-test.env.example','utf8').split('\n').find(l=>l.startsWith('DEMO_PASSWORD=')).slice('DEMO_PASSWORD='.length);
const browser=await chromium.launch({executablePath:'/usr/bin/chromium',args:['--no-sandbox']});
const page=await browser.newPage({viewport:{width:390,height:844}});const results=[];
try{
await page.goto('http://127.0.0.1:18085/index.php?page=login');
await page.getByLabel('Email',{exact:true}).fill('admin@studymate.test');await page.getByLabel('Mật khẩu',{exact:true}).fill(password);
await page.locator('form:has(input[value="login"]) button').click();
for(const tab of ['users','courses','categories']){
await page.goto('http://127.0.0.1:18085/index.php?page=admin&tab='+tab);
let reached=false;
for(let i=0;i<100;i++){await page.keyboard.press('Tab');if(await page.evaluate(()=>document.activeElement.matches('.table-responsive'))){reached=true;break;}}
if(!reached)throw Error('Cannot Tab to table: '+tab);
const before=await page.locator('.table-responsive').evaluate(e=>({left:e.scrollLeft,overflow:e.scrollWidth>e.clientWidth,role:e.getAttribute('role'),label:e.getAttribute('aria-label'),outline:getComputedStyle(e).outlineStyle}));
await page.keyboard.press('ArrowRight');await page.waitForTimeout(200);
const after=await page.locator('.table-responsive').evaluate(e=>e.scrollLeft);
if(before.role!=='region'||!before.label||before.outline==='none'||(before.overflow&&after<=before.left))throw Error('Table keyboard support failed: '+tab);
results.push({tab,tab_reachable:reached,...before,scrollAfter:after});
await page.screenshot({path:root+'/browser/keyboard-'+tab+'-390.png'});
}
fs.writeFileSync(root+'/keyboard-results.json',JSON.stringify(results,null,2));console.log('PASS: Tab, focus, accessible region labels and horizontal arrow scrolling for all 3 Admin tables');
}finally{await browser.close();}
})().catch(e=>{console.error(e);process.exit(1)});
