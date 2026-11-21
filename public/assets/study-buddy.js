// Optional companion: server-derived XP; browser-local cosmetic preferences only.
(() => {
  const root = document.getElementById('study-buddy');
  if (!root) return;
  const $ = id => document.getElementById(id);
  const xp = Number(root.dataset.xp), inactiveDays = Number(root.dataset.inactiveDays);
  const key = 'studymate:buddy:v1:' + root.dataset.user;
  const panel = $('buddy-panel'), launcher = $('buddy-launcher');
  const rewards = [...root.querySelectorAll('[data-buddy-item]')];
  const defaults = { shown:true, outfit:'classic', accessory:'none', reminders:true, lastXp:null, remindedOn:'' };
  let prefs = { ...defaults }, storageOK = true, celebration = false, bubbleTimer, celebrationTimer;
  try {
    const raw = JSON.parse(localStorage.getItem(key));
    if (raw && typeof raw === 'object') {
      prefs.shown = raw.shown !== false;
      prefs.reminders = raw.reminders !== false;
      if (['classic','crimson','student'].includes(raw.outfit)) prefs.outfit = raw.outfit;
      if (['none','glasses','crown'].includes(raw.accessory)) prefs.accessory = raw.accessory;
      if (Number.isInteger(raw.lastXp) && raw.lastXp >= 0) prefs.lastXp = Math.min(xp, raw.lastXp);
      if (typeof raw.remindedOn === 'string' && /^\d{4}-\d{2}-\d{2}$/.test(raw.remindedOn)) prefs.remindedOn = raw.remindedOn;
    }
  } catch { storageOK = false; }
  function save() {
    try { localStorage.setItem(key, JSON.stringify(prefs)); storageOK = true; }
    catch { storageOK = false; }
    $('buddy-storage').textContent = storageOK ? 'XP theo tài khoản. Trang phục và tùy chỉnh lưu trên trình duyệt này.' : 'Trình duyệt chưa lưu được tùy chỉnh. XP vẫn lấy từ tiến độ tài khoản.';
  }
  function allowed(group, item) {
    const reward = rewards.find(button => button.dataset.buddyGroup === group && button.dataset.buddyItem === item);
    return reward && Number(reward.dataset.requiredXp) <= xp;
  }
  if (!allowed('outfits', prefs.outfit)) prefs.outfit = 'classic';
  if (!allowed('accessories', prefs.accessory)) prefs.accessory = 'none';
  const gained = prefs.lastXp !== null ? Math.max(0, xp - prefs.lastXp) : 0;
  prefs.lastXp = xp;
  const labels = {
    reading:['Sukuna đang đọc sách','Cùng đọc và thực hành từng chút một nhé.'],
    resting:['Sukuna đang uống cà phê','Nghỉ một chút, thả lỏng vai rồi mình học tiếp.'],
    celebrating:['Sukuna đang ăn mừng','Làm tốt lắm! Thêm một bước tiến cho bạn.'],
    greeting:['Sukuna đang chào bạn','Một bài học nhỏ hôm nay cũng là một bước tiến.']
  };
  function renderState() {
    const isLearning = root.dataset.page === 'learn';
    const spaceOpen = document.getElementById('study-space') && !document.getElementById('study-space').hidden;
    const resting = spaceOpen && document.getElementById('space-timer-mode')?.value === 'break';
    const state = celebration ? 'celebrating' : resting ? 'resting' : isLearning ? 'reading' : 'greeting';
    root.dataset.state = state;
    root.dataset.accessory = prefs.accessory;
    const source = '/assets/buddy/' + prefs.outfit + '-' + state + '.webp';
    if ($('buddy-sprite').getAttribute('src') !== source) $('buddy-sprite').src = source;
    $('buddy-state-text').textContent = labels[state][1];
    launcher.setAttribute('aria-label', labels[state][0] + ' · ' + xp + ' XP · Mở bạn đồng hành');
    rewards.forEach(button => button.setAttribute('aria-pressed', String(button.dataset.buddyItem === prefs[button.dataset.buddyGroup === 'outfits' ? 'outfit' : 'accessory'])));
  }
  function openPanel(open, returnFocus = false) {
    panel.hidden = !open;
    launcher.setAttribute('aria-expanded', String(open));
    if (open) $('buddy-panel-close').focus({ preventScroll:true });
    else if (returnFocus) (prefs.shown ? launcher : $('buddy-restore')).focus({ preventScroll:true });
  }
  function showCharacter() {
    launcher.hidden = !prefs.shown;
    $('buddy-restore').hidden = prefs.shown;
    root.classList.toggle('is-minimized', !prefs.shown);
  }
  function bubble(text, ms = 8000) {
    if (!prefs.shown || root.dataset.page === 'quiz') return;
    clearTimeout(bubbleTimer);
    $('buddy-bubble').textContent = text;
    $('buddy-bubble').hidden = false;
    bubbleTimer = setTimeout(() => { $('buddy-bubble').hidden = true; }, ms);
  }
  const thresholds = [...new Set(rewards.map(button => Number(button.dataset.requiredXp)))].sort((a,b) => a-b);
  const next = thresholds.find(value => value > xp);
  $('buddy-xp-progress').max = next || 250;
  $('buddy-xp-progress').value = Math.min(xp, next || 250);
  $('buddy-next-reward').textContent = next ? 'Còn ' + (next - xp) + ' XP đến phần thưởng tiếp theo.' : 'Bạn đã mở tất cả trang phục và phụ kiện hiện có.';
  $('buddy-reminders').checked = prefs.reminders;
  launcher.addEventListener('click', () => openPanel(panel.hidden));
  $('buddy-panel-close').addEventListener('click', () => openPanel(false, true));
  $('buddy-hide').addEventListener('click', () => { prefs.shown = false; openPanel(false); $('buddy-bubble').hidden = true; showCharacter(); save(); $('buddy-restore').focus(); });
  $('buddy-restore').addEventListener('click', () => { prefs.shown = true; showCharacter(); save(); openPanel(true); });
  $('buddy-reminders').addEventListener('change', event => { prefs.reminders = event.target.checked; if (!prefs.reminders) $('buddy-bubble').hidden = true; save(); });
  for (const button of rewards) button.addEventListener('click', () => {
    const group = button.dataset.buddyGroup, item = button.dataset.buddyItem;
    if (!allowed(group, item)) return;
    prefs[group === 'outfits' ? 'outfit' : 'accessory'] = item;
    renderState(); save();
  });
  document.addEventListener('keydown', event => {
    if (event.key === 'Escape' && !panel.hidden) { openPanel(false, true); event.preventDefault(); }
  });
  document.addEventListener('click', event => { if (!root.contains(event.target)) openPanel(false); });
  document.addEventListener('focusin', event => root.classList.toggle('is-form-focused', !root.contains(event.target) && event.target.matches('input:not([type=checkbox]):not([type=radio]):not([type=range]),textarea')));
  document.addEventListener('studymate:focus-state', renderState);
  root.classList.toggle('is-quiz', root.dataset.page === 'quiz');
  renderState(); showCharacter(); root.hidden = false; save();
  if (gained > 0 && prefs.shown && root.dataset.page !== 'quiz') {
    celebration = true; renderState(); bubble('Thêm ' + gained + ' XP từ tiến độ đã lưu. Làm tốt lắm!', 6500);
    celebrationTimer = setTimeout(() => { celebration = false; renderState(); }, 6500);
  } else if (storageOK && prefs.reminders && inactiveDays >= 3) {
    const today = new Date().toISOString().slice(0,10);
    if (prefs.remindedOn !== today) { prefs.remindedOn = today; save(); bubble('Lâu rồi chưa có tiến độ mới. Mình thử một bài ngắn hôm nay nhé?'); }
  }
  window.addEventListener('pagehide', () => { clearTimeout(bubbleTimer); clearTimeout(celebrationTimer); });
  window.addEventListener('pageshow', event => { if (event.persisted) { celebration = false; $('buddy-bubble').hidden = true; renderState(); } });
})();
