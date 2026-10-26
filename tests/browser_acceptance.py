"""Chromium checks on a separate MySQL performance database (100/500/100)."""
import json, math, os, platform, re, statistics, subprocess, time, urllib.request
from pathlib import Path
from playwright.sync_api import sync_playwright
BASE=os.environ.get('STUDYMATE_BROWSER_URL','http://127.0.0.1:8081')
OUT=Path('docs/evidence/final');OUT.mkdir(parents=True,exist_ok=True)
results=[]
def check(name,ok):
 assert ok,name
 results.append({'name':name,'status':'passed'});print('PASS',name,flush=True)
def login(page,name):
 page.goto(BASE+'/index.php?page=login');page.locator('[name=email]').fill(name+'@studymate.test');page.locator('[name=password]').fill('StudyMate2026!');page.get_by_role('button',name='Đăng nhập',exact=True).click();page.wait_for_load_state()
def main():
 for _ in range(5):
  with urllib.request.urlopen(BASE+'/index.php?page=courses') as r:assert r.status==200
 times=[]
 for _ in range(30):
  t=time.perf_counter()
  with urllib.request.urlopen(BASE+'/index.php?page=courses') as r:
   body=r.read();assert r.status==200 and b'Performance Course' in body
  times.append((time.perf_counter()-t)*1000)
 p95=sorted(times)[math.ceil(.95*len(times))-1]
 check('catalog warm-up 5, 30 successful timed loads p95 under 1s',p95<1000)
 (OUT/'performance.json').write_text(json.dumps({'data':{'users':100,'courses':100,'lessons':500},'warmup':5,'runs_ms':times,'p95_ms_nearest_rank':p95,'machine':{'platform':platform.platform(),'logical_cpus':os.cpu_count(),'memory_kib':int(re.search(r'MemTotal:\s+(\d+)',Path('/proc/meminfo').read_text())[1]),'docker_limits':subprocess.check_output(['docker','inspect','studymate-perf','--format','{{.HostConfig.Memory}} {{.HostConfig.NanoCpus}}'],text=True).strip()},'scope':'sequential HTTP catalog responses on local Docker MySQL; not Internet latency or load test'},indent=2))
 with sync_playwright() as p:
  browser=p.chromium.launch(executable_path='/usr/bin/chromium',args=['--no-sandbox'])
  version=browser.version
  for role in ['guest','student','instructor','admin']:
   context=browser.new_context();page=context.new_page();errors=[];page.on('pageerror',lambda e:errors.append(str(e)))
   if role!='guest':login(page,{'student':'student1','instructor':'instructor1','admin':'admin'}[role])
   routes={'guest':['home','courses','detail&id=1','login','register'],'student':['student','learn&id=2','quiz&id=2','result&id=1','profile'],'instructor':['instructor','edit&id=3&tab=info','edit&id=3&tab=content','edit&id=3&tab=quiz','edit&id=2&tab=students'],'admin':['admin&tab=users','admin&tab=courses','admin&tab=categories']}[role]
   for width in [390,768,1366]:
    page.set_viewport_size({'width':width,'height':900})
    for route in routes:
     r=page.goto(BASE+'/index.php?page='+route);check(f'{role} {route} {width} HTTP 200',r.status==200)
     check(f'{role} {route} {width} no document overflow',page.evaluate('document.documentElement.scrollWidth <= innerWidth'))
     # Every visible field has a label or explicit accessible name.
     unnamed=page.locator('input:not([type=hidden]),textarea,select').evaluate_all("els=>els.filter(e=>!e.labels?.length&&!e.getAttribute('aria-label')&&!e.getAttribute('aria-labelledby')).length")
     check(f'{role} {route} {width} fields labelled',unnamed==0)
     small=page.locator('.btn:not([disabled]),.lesson-link').evaluate_all('els=>els.filter(e=>e.getBoundingClientRect().width && e.getBoundingClientRect().height<43.5).length')
     check(f'{role} {route} {width} primary controls >=44px',small==0)
     page.screenshot(path=str(OUT/(role+'-'+re.sub('[^a-zA-Z0-9]','-',route)+'-'+str(width)+'.png')),full_page=True)
   page.goto(BASE+'/index.php?page='+{'guest':'courses','student':'student','instructor':'instructor','admin':'admin&tab=users'}[role])
   if role in ['student','instructor','admin']:
    item='.course-card' if role=='student' else 'tbody tr'
    check(role+' default 12 rows',page.locator(item).count()==12)
    page.get_by_role('link',name='Sau',exact=True).click();check(role+' page 2 works','p=2' in page.url and page.locator(item).count()==12)
   if role=='admin':
    page.goto(BASE+'/index.php?page=admin&tab=users&per_page=500');check('admin page size capped at 50',page.locator('tbody tr').count()==50)
    page.goto(BASE+'/index.php?page=admin&tab=users&q=%25');check('search escapes SQL wildcard',page.locator('tbody tr').count()==0 and 'Không có kết quả' in page.content())
   if role=='student':
    page.goto(BASE+'/index.php?page=student&q=Performance');page.get_by_role('link',name='Sau',exact=True).click();check('pagination preserves filter','q=Performance' in page.url and page.locator('.course-card').count()==12)
    check('student totals independent of current page','98' in page.locator('.stats-grid').inner_text())
   page.goto(BASE+'/index.php?page=home');page.keyboard.press('Tab')
   check(role+' Tab focus visible',page.evaluate("getComputedStyle(document.activeElement).outlineStyle !== 'none'"))
   check(role+' no browser JS exceptions',not errors);context.close()
  browser.close()
 (OUT/'browser-results.json').write_text(json.dumps({'browser':'Chromium '+version,'real_chrome_edge':'NOT RUN','screenshots':'viewport/full-page only; no browser toolbar','results':results},ensure_ascii=False,indent=2))
 print(len(results),'checks passed; catalog p95',round(p95,2),'ms')
if __name__=='__main__':main()
