// Apply the saved theme before styles load; language is rendered by PHP.
(() => {
  const root = document.documentElement;
  const media = window.matchMedia('(prefers-color-scheme: dark)');
  let preference = root.dataset.themePreference || 'system';
  const apply = () => {
    const theme = preference === 'system' ? (media.matches ? 'dark' : 'light') : preference;
    root.dataset.theme = theme;
    root.dataset.bsTheme = theme;
    root.style.colorScheme = theme;
  };
  apply();
  media.addEventListener('change', () => { if (preference === 'system') apply(); });

  document.addEventListener('DOMContentLoaded', () => {
    const form = document.querySelector('[data-display-preferences]');
    if (!form) return;
    const theme = form.elements.namedItem('ui_theme');
    const language = form.elements.namedItem('ui_language');
    form.classList.add('preferences-enhanced');
    theme.addEventListener('change', () => {
      preference = theme.value;
      root.dataset.themePreference = preference;
      try { document.cookie = `studymate_theme=${encodeURIComponent(preference)}; Path=/; Max-Age=31536000; SameSite=Lax${location.protocol === 'https:' ? '; Secure' : ''}`; } catch { /* Theme still applies when browser cookies are blocked. */ }
      apply();
    });
    // Native navigation retains beforeunload protection for unsubmitted quizzes.
    language.addEventListener('change', () => {
      const allowed = document.dispatchEvent(new CustomEvent('studymate:before-language-change', {cancelable: true}));
      if (allowed) { form.action='/index.php'+location.hash; form.requestSubmit(); }
      else language.value = root.lang;
    });
    const settings=document.querySelector('#display-settings');
    settings?.addEventListener('toggle',()=>{
      if(!settings.open) return;
      document.querySelector('.mobile-menu-toggle')?.setAttribute('aria-expanded','false');
      document.querySelector('#site-navigation')?.removeAttribute('data-open');
    });
    document.querySelector('.mobile-menu-toggle')?.addEventListener('click',()=>{if(settings) settings.open=false;});
    document.addEventListener('click',event=>{if(settings?.open&&!settings.contains(event.target)) settings.open=false;});
    document.addEventListener('keydown',event=>{if(event.key==='Escape'&&settings?.open){settings.open=false;settings.querySelector('summary').focus();}});
    // Transient ui_* query parameters must not override a later local selection.
    const address = new URL(location.href);
    if (address.searchParams.has('ui_theme') || address.searchParams.has('ui_language')) {
      address.searchParams.delete('ui_theme');
      address.searchParams.delete('ui_language');
      history.replaceState(null, '', address.pathname + address.search + address.hash);
    }
    window.addEventListener('pageshow', () => {
      language.value = root.lang;
      const saved=document.cookie.split('; ').find(value=>value.startsWith('studymate_theme='))?.split('=')[1];
      if(['system','light','dark'].includes(saved)) {preference=saved;theme.value=saved;root.dataset.themePreference=saved;apply();}
    });
  });
})();
