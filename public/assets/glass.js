// Small progressive enhancements; forms and charts are rendered by PHP.
document.documentElement.classList.remove('no-js');
document.documentElement.classList.add('has-js');
document.addEventListener('DOMContentLoaded', () => {
  const header=document.querySelector('#site-header');
  const toggle=document.querySelector('.mobile-menu-toggle');
  const nav=document.querySelector('#site-navigation');
  const close=()=>{toggle?.setAttribute('aria-expanded','false');nav?.removeAttribute('data-open');};
  toggle?.addEventListener('click',()=>{
    const expanded=toggle.getAttribute('aria-expanded')==='true';
    toggle.setAttribute('aria-expanded',String(!expanded));
    if(expanded) nav.removeAttribute('data-open'); else nav.dataset.open='true';
  });
  nav?.addEventListener('click',event=>{if(event.target.closest('a'))close();});
  document.addEventListener('keydown',event=>{if(event.key==='Escape'&&toggle?.getAttribute('aria-expanded')==='true'){close();toggle.focus();}});
  document.addEventListener('click',event=>{if(header&&!header.contains(event.target))close();});
  const scroll=()=>header?.classList.toggle('is-scrolled',window.scrollY>90);
  scroll();window.addEventListener('scroll',scroll,{passive:true});
  const markCurrent=(links,selected)=>links.forEach(link=>{
    const active=link===selected;
    link.classList.toggle('active',active);
    if(active) link.setAttribute('aria-current','location'); else link.removeAttribute('aria-current');
  });
  const syncLocation=()=>{
    const hash=window.location.hash;
    if(document.body.classList.contains('page-home')) {
      const links=[...document.querySelectorAll('.primary-links a')];
      markCurrent(links,links.find(link=>new URL(link.href).hash===hash&&hash));
    }
    const details=[...document.querySelectorAll('.detail-nav a')];
    if(details.length) markCurrent(details,details.find(link=>link.hash===hash)||details[0]);
    const workspace=[...document.querySelectorAll('.glass-dashboard .workspace-nav nav>a')];
    if(workspace.length) markCurrent(workspace,hash==='#my-courses-title'?workspace[1]:workspace[0]);
  };
  document.querySelectorAll('.tabs a.active').forEach(link=>link.setAttribute('aria-current','page'));
  syncLocation();window.addEventListener('hashchange',syncLocation);
  const media=window.matchMedia('(min-width: 1101px)');
  media.addEventListener('change',close);
});
