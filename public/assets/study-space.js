// Optional, browser-local study tools. Academic state remains owned by PHP.
(() => {
  const root = document.getElementById('study-space');
  const toggle = document.getElementById('study-space-toggle');
  if (!root || !toggle) return;
  const $ = id => document.getElementById(id);
  // Move the empty shell once, before an iframe exists. Reparenting a live iframe
  // would reload the player and interrupt music.
  document.body.append($('space-player-shell'));
  const key = 'studymate:space:v1:' + root.dataset.user;
  const modes = { focus: 25 * 60000, break: 5 * 60000, stopwatch: 0 };
  const kinds = ['rain', 'wind', 'pink', 'brown'];
  const fresh = () => ({ enabled:false, scene:'library', motion:true, tab:'scene', master:60, musicVolume:50, url:'', favorites:[], sounds:Object.fromEntries(kinds.map(kind => [kind, { selected:kind === 'rain', volume:35 }])), timer:{ mode:'focus', running:false, remaining:modes.focus, elapsed:0, anchor:0 } });
  const percent = (value, fallback) => Number.isFinite(value) ? Math.max(0, Math.min(100, value)) : fallback;
  function videoId(value) {
    try {
      const url = new URL(value);
      if (url.protocol !== 'https:' || url.username || url.password || (url.port && url.port !== '443')) return null;
      let id;
      if (url.hostname === 'youtu.be') id = url.pathname.slice(1);
      else if (['youtube.com', 'www.youtube.com', 'm.youtube.com', 'music.youtube.com'].includes(url.hostname)) {
        if (url.pathname === '/watch') id = url.searchParams.get('v');
        else id = url.pathname.match(/^\/(?:embed|shorts|live)\/([^/]+)\/?$/)?.[1];
      }
      return typeof id === 'string' && /^[\w-]{11}$/.test(id) ? id : null;
    } catch { return null; }
  }
  const canonicalUrl = id => 'https://www.youtube.com/watch?v=' + id;
  let state = fresh();
  let storageOK = true;
  try {
    const saved = JSON.parse(localStorage.getItem(key));
    if (saved && typeof saved === 'object') {
      state.enabled = saved.enabled === true;
      if (typeof saved.motion === 'boolean') state.motion = saved.motion;
      if (['library', 'forest', 'desk'].includes(saved.scene)) state.scene = saved.scene;
      if (['scene', 'sound', 'music'].includes(saved.tab)) state.tab = saved.tab;
      state.master = percent(saved.master, 60);
      state.musicVolume = percent(saved.musicVolume, 50);
      if (typeof saved.url === 'string' && videoId(saved.url)) state.url = canonicalUrl(videoId(saved.url));
      if (Array.isArray(saved.favorites)) {
        const ids = new Set();
        state.favorites = saved.favorites.filter(item => {
          if (!item || typeof item.url !== 'string' || !videoId(item.url) || ids.has(videoId(item.url))) return false;
          ids.add(videoId(item.url)); return true;
        }).slice(0, 5).map(item => ({ url:canonicalUrl(videoId(item.url)), name:typeof item.name === 'string' ? item.name.slice(0, 60) : 'Nhạc của tôi' }));
      }
      for (const kind of kinds) {
        state.sounds[kind].selected = saved.sounds?.[kind]?.selected === true;
        state.sounds[kind].volume = percent(saved.sounds?.[kind]?.volume, 35);
      }
      const timer = saved.timer;
      if (timer && Object.hasOwn(modes, timer.mode)) {
        state.timer.mode = timer.mode;
        state.timer.remaining = Number.isFinite(timer.remaining) ? Math.max(0, Math.min(modes[timer.mode], timer.remaining)) : modes[timer.mode];
        state.timer.elapsed = Number.isFinite(timer.elapsed) ? Math.max(0, Math.min(86400000, timer.elapsed)) : 0;
        state.timer.anchor = Number.isFinite(timer.anchor) ? timer.anchor : 0;
        state.timer.running = state.enabled && timer.running === true && timer.anchor > 0 && timer.anchor <= Date.now() && timer.anchor >= Date.now() - 7 * 86400000;
      }
    }
  } catch { storageOK = false; }
  function save() {
    try { localStorage.setItem(key, JSON.stringify(state)); storageOK = true; }
    catch { storageOK = false; }
    if (!storageOK) $('space-storage-status').textContent = 'Trình duyệt chưa lưu được tùy chỉnh. Bạn vẫn có thể dùng các công cụ trong phiên này.';
  }
  function message(id, text) {
    if ($(id).textContent !== text) $(id).textContent = text;
  }
  const scenery = document.createElement('div'); scenery.id = 'study-scenery'; scenery.setAttribute('aria-hidden', 'true'); scenery.hidden = true;
  const photo = document.createElement('div'); photo.className = 'scenery-photo';
  const video = document.createElement('video'); video.id = 'space-scene-video'; video.muted = true; video.defaultMuted = true; video.loop = true; video.playsInline = true; video.preload = 'none'; video.poster = '/assets/spaces/forest.jpg';
  const light = document.createElement('div'); light.className = 'scenery-light';
  const rain = document.createElement('div'); rain.className = 'scenery-rain';
  scenery.append(photo,video,light,rain); document.body.prepend(scenery);
  const reducedMotion = matchMedia('(prefers-reduced-motion: reduce)');
  let sceneryGeneration = 0;
  function applyScenery() {
    const generation = ++sceneryGeneration;
    const moving = state.enabled && state.motion && !reducedMotion.matches;
    scenery.hidden = !state.enabled;
    document.body.classList.toggle('study-space-motion', moving);
    $('space-motion').checked = state.motion && !reducedMotion.matches;
    $('space-motion').disabled = reducedMotion.matches;
    const useVideo = moving && state.scene === 'forest' && !navigator.connection?.saveData;
    if (!useVideo) {
      video.pause(); scenery.classList.remove('is-live');
      if (video.hasAttribute('src')) { video.removeAttribute('src'); video.load(); }
      message('space-motion-status', reducedMotion.matches ? 'Thiết bị đang chọn giảm chuyển động.' : !state.motion ? 'Đang dùng nền tĩnh.' : navigator.connection?.saveData && state.scene === 'forest' ? 'Đang tiết kiệm dữ liệu: dùng ảnh chuyển động nhẹ.' : 'Ánh sáng và cảnh nền chuyển động nhẹ.');
      return;
    }
    if (!video.hasAttribute('src')) video.src = '/assets/spaces/forest-motion.webm';
    if (document.hidden) { video.pause(); return; }
    message('space-motion-status', 'Đang mở video suối rừng HD…');
    video.play().then(() => {
      if (generation !== sceneryGeneration || !state.enabled) return;
      scenery.classList.add('is-live'); message('space-motion-status', 'Video suối rừng · HD 1920 × 1080.');
    }).catch(() => {
      if (generation !== sceneryGeneration) return;
      scenery.classList.remove('is-live'); message('space-motion-status', 'Chưa phát được video. Nền ảnh vẫn hiển thị; bạn có thể tắt/bật để thử lại.');
    });
  }
  $('space-motion').addEventListener('change', event => { state.motion = event.target.checked; applyScenery(); save(); });
  reducedMotion.addEventListener('change', applyScenery);
  function timerValue() {
    const elapsed = state.timer.running ? Math.max(0, Date.now() - state.timer.anchor) : 0;
    return state.timer.mode === 'stopwatch' ? Math.min(86400000, state.timer.elapsed + elapsed) : Math.max(0, state.timer.remaining - elapsed);
  }
  function pauseTimer() {
    const value = timerValue();
    if (state.timer.mode === 'stopwatch') state.timer.elapsed = value;
    else state.timer.remaining = value;
    state.timer.running = false;
    state.timer.anchor = 0;
  }
  function drawTimer() {
    const value = timerValue();
    if (state.timer.running && ((state.timer.mode !== 'stopwatch' && value === 0) || value === 86400000)) {
      pauseTimer(); save();
    }
    const seconds = state.timer.mode === 'stopwatch' ? Math.floor(value / 1000) : Math.ceil(value / 1000);
    const hours = Math.floor(seconds / 3600);
    const minutes = Math.floor(seconds / 60) % 60;
    const pad = value => String(value).padStart(2, '0');
    $('space-clock').textContent = (hours ? pad(hours) + ':' : '') + pad(minutes) + ':' + pad(seconds % 60);
    $('space-timer-start').textContent = state.timer.running ? 'Tạm dừng' : ((state.timer.mode === 'stopwatch' ? value > 0 : value > 0 && value < modes[state.timer.mode]) ? 'Tiếp tục' : 'Bắt đầu');
    message('space-timer-status', state.timer.running ? 'Đang ' + (state.timer.mode === 'stopwatch' ? 'bấm giờ.' : 'đếm ngược.') : value === 0 && state.timer.mode !== 'stopwatch' ? 'Phiên đã kết thúc. Bạn có thể bắt đầu phiên mới.' : (state.timer.mode === 'stopwatch' ? value > 0 : value < modes[state.timer.mode]) ? 'Đã tạm dừng.' : 'Sẵn sàng cho một phiên học.');
  }
  $('space-timer-start').addEventListener('click', () => {
    if (state.timer.running) pauseTimer();
    else {
      if (state.timer.mode !== 'stopwatch' && timerValue() === 0) state.timer.remaining = modes[state.timer.mode];
      if (state.timer.elapsed === 86400000) state.timer.elapsed = 0;
      state.timer.anchor = Date.now(); state.timer.running = true;
    }
    save(); drawTimer();
  });
  function resetTimer(mode = state.timer.mode) {
    state.timer = { mode, running:false, remaining:modes[mode], elapsed:0, anchor:0 };
    save(); drawTimer();
  }
  $('space-timer-reset').addEventListener('click', () => resetTimer());
  $('space-timer-mode').value = state.timer.mode;
  $('space-timer-mode').addEventListener('change', event => resetTimer(event.target.value));

  // One AudioContext mixes the local recording and procedural noise at user-set levels.
  let context;
  let ambientActive = false;
  let ambientPending = false;
  let ambientGeneration = 0;
  const buffers = new Map();
  const channels = new Map();
  async function audioContext() {
    const AudioContext = window.AudioContext || window.webkitAudioContext;
    if (!AudioContext) throw new Error('Trình duyệt chưa hỗ trợ bộ trộn âm thanh.');
    context ||= new AudioContext();
    await context.resume();
    return context;
  }
  async function soundBuffer(kind) {
    if (buffers.has(kind)) return buffers.get(kind);
    let buffer;
    if (kind === 'rain') {
      const response = await fetch('/assets/spaces/rain.ogg');
      if (!response.ok) throw new Error('Chưa tải được tiếng mưa. Thử lại hoặc chọn một âm khác.');
      try { buffer = await context.decodeAudioData(await response.arrayBuffer()); }
      catch { throw new Error('Trình duyệt chưa đọc được tiếng mưa. Bạn có thể chọn gió hoặc tiếng ồn.'); }
    } else {
      buffer = context.createBuffer(1, context.sampleRate * 3, context.sampleRate);
      const data = buffer.getChannelData(0);
      let brown = 0;
      const pink = Array(7).fill(0);
      for (let i = 0; i < data.length; i++) {
        const white = Math.random() * 2 - 1;
        if (kind === 'brown') { brown = (brown + .02 * white) / 1.02; data[i] = brown * 3.5; }
        else if (kind === 'pink') {
          pink[0] = .99886 * pink[0] + white * .0555179;
          pink[1] = .99332 * pink[1] + white * .0750759;
          pink[2] = .969 * pink[2] + white * .153852;
          pink[3] = .8665 * pink[3] + white * .3104856;
          pink[4] = .55 * pink[4] + white * .5329522;
          pink[5] = -.7616 * pink[5] - white * .016898;
          data[i] = (pink.reduce((a, b) => a + b, 0) + white * .5362) * .11;
          pink[6] = white * .115926;
        } else data[i] = white;
      }
    }
    buffers.set(kind, buffer); return buffer;
  }
  function setVolumes() {
    $('space-master-value').textContent = Math.round(state.master) + '%';
    for (const [kind, channel] of channels) channel.gain.gain.setTargetAtTime(state.master / 100 * state.sounds[kind].volume / 100 * .65, context.currentTime, .04);
    if (playerReady) player.setVolume(Math.round(state.master * state.musicVolume / 100));
    updateSoundRows();
  }
  function updateSoundRows() {
    for (const kind of kinds) {
      root.querySelector('[data-sound-row="' + kind + '"]').classList.toggle('is-playing', channels.has(kind) && state.master > 0 && state.sounds[kind].volume > 0);
      $('ambient-value-' + kind).textContent = Math.round(state.sounds[kind].volume) + '%';
    }
    document.body.classList.toggle('study-space-rain', state.enabled && channels.has('rain') && state.master > 0 && state.sounds.rain.volume > 0);
  }
  function stopChannel(kind) {
    const channel = channels.get(kind);
    if (!channel) return;
    for (const node of channel.nodes) { try { node.stop?.(); } catch {} node.disconnect(); }
    channels.delete(kind);
    updateSoundRows();
  }
  function stopAmbient() {
    ambientGeneration++;
    for (const kind of [...channels.keys()]) stopChannel(kind);
    ambientActive = false; ambientPending = false;
    $('space-ambient-play').disabled = false;
    $('space-ambient-play').textContent = 'Bật âm nền';
    message('space-audio-status', 'Âm thanh đang tắt.');
  }
  async function startChannel(kind, generation) {
    const buffer = await soundBuffer(kind);
    if (generation !== ambientGeneration || !state.enabled || !state.sounds[kind].selected || channels.has(kind)) return;
    const source = context.createBufferSource(); source.buffer = buffer; source.loop = true;
    const gain = context.createGain(); gain.gain.value = 0;
    const nodes = [source, gain];
    if (kind === 'wind') {
      const filter = context.createBiquadFilter(); filter.type = 'lowpass'; filter.frequency.value = 450;
      const lfo = context.createOscillator(); lfo.frequency.value = .12;
      const depth = context.createGain(); depth.gain.value = 180;
      lfo.connect(depth); depth.connect(filter.frequency); source.connect(filter); filter.connect(gain); lfo.start();
      nodes.push(filter, lfo, depth);
    } else source.connect(gain);
    gain.connect(context.destination); channels.set(kind, { gain, nodes }); setVolumes(); source.start();
  }
  $('space-ambient-play').addEventListener('click', async () => {
    if (ambientActive || ambientPending) { stopAmbient(); return; }
    if (!kinds.some(kind => state.sounds[kind].selected)) { message('space-audio-status', 'Chọn ít nhất một âm thanh để bắt đầu.'); return; }
    const generation = ++ambientGeneration;
    ambientPending = true; $('space-ambient-play').disabled = true;
    message('space-audio-status', 'Đang chuẩn bị âm thanh…');
    try {
      await audioContext();
      for (const kind of kinds) if (state.sounds[kind].selected) await startChannel(kind, generation);
      if (generation !== ambientGeneration) return;
      ambientActive = channels.size > 0;
      $('space-ambient-play').textContent = ambientActive ? 'Tạm dừng âm nền' : 'Bật âm nền';
      message('space-audio-status', ambientActive ? 'Đang phát âm nền theo lựa chọn của bạn.' : 'Âm thanh đang tắt.');
    } catch (error) { if (generation === ambientGeneration) { stopAmbient(); message('space-audio-status', error.message); } }
    finally { if (generation === ambientGeneration) { ambientPending = false; $('space-ambient-play').disabled = false; } }
  });
  for (const kind of kinds) {
    const selected = root.querySelector('[data-ambient="' + kind + '"]');
    const volume = root.querySelector('[data-ambient-volume="' + kind + '"]');
    selected.checked = state.sounds[kind].selected; volume.value = state.sounds[kind].volume;
    selected.addEventListener('change', async () => {
      state.sounds[kind].selected = selected.checked; save();
      if (!selected.checked) stopChannel(kind);
      else if (ambientActive) {
        try { await startChannel(kind, ambientGeneration); }
        catch (error) { message('space-audio-status', error.message); }
      }
      if (ambientActive && !kinds.some(item => state.sounds[item].selected)) stopAmbient();
    });
    volume.addEventListener('input', () => { state.sounds[kind].volume = +volume.value; setVolumes(); save(); });
  }
  $('space-master-volume').value = state.master;
  $('space-master-volume').addEventListener('input', event => { state.master = +event.target.value; setVolumes(); save(); });

  let player = null;
  let playerReady = false;
  let playerGeneration = 0;
  let youtubePromise;
  function destroyPlayer() {
    playerGeneration++;
    if (player) { try { player.pauseVideo?.(); player.destroy?.(); } catch {} }
    player = null; playerReady = false;
    const placeholder = document.createElement('div'); placeholder.id = 'space-player';
    const shell = $('space-player-shell');
    const previous = shell.querySelector('iframe, #space-player');
    if (previous) previous.replaceWith(placeholder);
    else shell.querySelector('.space-player-actions').before(placeholder);
    shell.hidden = true;
    $('space-music-play').disabled = true; $('space-music-pause').disabled = true;
  }
  function loadYoutubeAPI() {
    if (window.YT?.Player) return Promise.resolve();
    if (youtubePromise) return youtubePromise;
    youtubePromise = new Promise((resolve, reject) => {
      const previous = window.onYouTubeIframeAPIReady;
      const script = document.createElement('script'); script.src = 'https://www.youtube.com/iframe_api';
      const timeout = setTimeout(() => { script.remove(); youtubePromise = null; reject(new Error('YouTube chưa phản hồi. Kiểm tra kết nối và thử lại.')); }, 15000);
      window.onYouTubeIframeAPIReady = () => { clearTimeout(timeout); try { previous?.(); } finally { resolve(); } };
      script.onerror = () => { clearTimeout(timeout); script.remove(); youtubePromise = null; reject(new Error('Không tải được YouTube. Kiểm tra kết nối và thử lại.')); };
      document.head.append(script);
    });
    return youtubePromise;
  }
  async function openMusic(url) {
    const id = videoId(url);
    if (!id) { message('space-music-status', 'Dùng link HTTPS của một video youtube.com hoặc youtu.be hợp lệ.'); return; }
    state.url = canonicalUrl(id); $('space-youtube-url').value = state.url; save();
    destroyPlayer();
    const generation = playerGeneration;
    message('space-music-status', 'Đang mở trình phát YouTube…');
    try {
      await loadYoutubeAPI();
      if (generation !== playerGeneration || !state.enabled) return;
      $('space-player-shell').hidden = false;
      player = new YT.Player('space-player', {
        host:'https://www.youtube-nocookie.com', width:'100%', height:'200', videoId:id,
        playerVars:{ autoplay:0, playsinline:1, origin:location.origin },
        events:{
          onReady:() => {
            if (generation !== playerGeneration) return;
            playerReady = true; setVolumes();
            player.getIframe().setAttribute('title', 'Nhạc YouTube của bạn');
            player.getIframe().setAttribute('referrerpolicy', 'strict-origin-when-cross-origin');
            $('space-music-play').disabled = false; $('space-music-pause').disabled = false;
            message('space-music-status', 'Sẵn sàng. Bấm Phát hoặc dùng nút trong video.');
          },
          onStateChange:event => {
            if (generation !== playerGeneration) return;
            if (event.data === 1 && (document.hidden || !playerVisible())) { player.pauseVideo(); return; }
            if (event.data === 1) message('space-music-status', 'Đang phát nhạc YouTube.');
            if (event.data === 2) message('space-music-status', 'Nhạc đã tạm dừng.');
          },
          onError:event => {
            if (generation !== playerGeneration) return;
            const messages = { 100:'Video không còn khả dụng hoặc đang riêng tư.', 101:'Chủ video không cho phép nhúng. Hãy chọn link khác.', 150:'Chủ video không cho phép nhúng. Hãy chọn link khác.', 153:'YouTube chưa xác nhận được trang nhúng. Thử mở bằng trình duyệt thường.' };
            message('space-music-status', messages[event.data] || 'Không phát được video này. Hãy kiểm tra link hoặc chọn video khác.');
          },
          onAutoplayBlocked:() => message('space-music-status', 'Hãy bấm nút Phát trực tiếp trong video để bật nhạc.')
        }
      });
    } catch (error) { if (generation === playerGeneration) message('space-music-status', error.message); }
  }
  function playerVisible() {
    const frame = $('space-player-shell').querySelector('iframe');
    if (!frame || !state.enabled) return false;
    const rect = frame.getBoundingClientRect();
    return Math.min(rect.bottom, innerHeight) - Math.max(rect.top, 0) >= rect.height * .5 && rect.right > 0 && rect.left < innerWidth;
  }
  function pauseInvisibleMusic() {
    if (playerReady && player.getPlayerState() === 1 && (document.hidden || !playerVisible())) player.pauseVideo();
  }
  document.addEventListener('visibilitychange', () => { pauseInvisibleMusic(); if (document.hidden) video.pause(); else applyScenery(); });
  document.addEventListener('scroll', pauseInvisibleMusic, { capture:true, passive:true });
  window.addEventListener('resize', pauseInvisibleMusic);
  $('space-music-form').addEventListener('submit', event => { event.preventDefault(); openMusic($('space-youtube-url').value.trim()); });
  $('space-youtube-url').value = state.url;
  $('space-music-play').addEventListener('click', () => { if (playerReady && playerVisible()) player.playVideo(); });
  $('space-music-pause').addEventListener('click', () => { if (playerReady) player.pauseVideo(); });
  $('space-player-close').addEventListener('click', () => { destroyPlayer(); message('space-music-status', 'Đã đóng trình phát. Link yêu thích vẫn được giữ.'); toggle.focus({ preventScroll:true }); });
  $('space-music-volume').value = state.musicVolume;
  $('space-music-volume').addEventListener('input', event => { state.musicVolume = +event.target.value; setVolumes(); save(); });
  function drawFavorites() {
    const list = $('space-favorite-list'); list.replaceChildren();
    if (!state.favorites.length) {
      const empty = document.createElement('p'); empty.textContent = 'Chưa có link yêu thích.'; list.append(empty);
    }
    for (const item of state.favorites) {
      const row = document.createElement('div'); row.className = 'space-favorite-row';
      const select = document.createElement('button'); select.type = 'button'; select.className = 'favorite-select'; select.textContent = item.name;
      select.addEventListener('click', () => { $('space-music-name').value = item.name; openMusic(item.url); });
      const remove = document.createElement('button'); remove.type = 'button'; remove.className = 'favorite-remove'; remove.textContent = '×'; remove.setAttribute('aria-label', 'Xóa ' + item.name);
      remove.addEventListener('click', () => { state.favorites = state.favorites.filter(favorite => favorite.url !== item.url); save(); drawFavorites(); $('space-music-save').focus(); });
      row.append(select, remove); list.append(row);
    }
  }
  $('space-music-save').addEventListener('click', () => {
    const id = videoId($('space-youtube-url').value.trim());
    if (!id) { message('space-music-status', 'Dán link video YouTube hợp lệ trước khi lưu.'); return; }
    if (state.favorites.some(item => videoId(item.url) === id)) { message('space-music-status', 'Video này đã nằm trong danh sách yêu thích.'); return; }
    if (state.favorites.length === 5) { message('space-music-status', 'Đã có 5 video. Xóa một video để thêm lựa chọn mới.'); return; }
    state.favorites.push({ url:canonicalUrl(id), name:$('space-music-name').value.trim().slice(0, 60) || 'Nhạc yêu thích · ' + id });
    state.url = canonicalUrl(id); save(); drawFavorites();
    message('space-music-status', 'Đã lưu video vào danh sách yêu thích trên trình duyệt này.');
  });
  function stopAll() { stopAmbient(); destroyPlayer(); message('space-music-status', 'Nhạc đang tắt. Bấm Mở nhạc để chọn lại.'); }
  $('space-mute-all').addEventListener('click', stopAll);

  const tabs = [...root.querySelectorAll('[role="tab"]')];
  function setTab(name, focus = false) {
    state.tab = name;
    for (const tab of tabs) {
      const selected = tab.id === 'space-tab-' + name;
      tab.setAttribute('aria-selected', String(selected)); tab.tabIndex = selected ? 0 : -1;
      $(tab.getAttribute('aria-controls')).hidden = !selected;
      if (selected && focus) tab.focus();
    }
    save();
  }
  for (const [index, tab] of tabs.entries()) {
    tab.addEventListener('click', () => setTab(tab.id.replace('space-tab-', '')));
    tab.addEventListener('keydown', event => {
      const next = { ArrowRight:(index + 1) % 3, ArrowLeft:(index + 2) % 3, Home:0, End:2 }[event.key];
      if (next === undefined) return;
      event.preventDefault(); setTab(tabs[next].id.replace('space-tab-', ''), true);
    });
  }
  for (const input of root.querySelectorAll('[name="study_scene"]')) {
    input.checked = input.value === state.scene;
    input.addEventListener('change', () => { if (input.checked) { state.scene = input.value; document.body.dataset.studyScene = state.scene; applyScenery(); save(); } });
  }
  const outline = document.querySelector('.lesson-list-wrap');
  function setEnabled(enabled, scroll = false) {
    state.enabled = enabled;
    if (!enabled) { pauseTimer(); stopAll(); }
    root.hidden = !enabled;
    document.body.classList.toggle('study-space-on', enabled);
    document.body.dataset.studyScene = state.scene;
    applyScenery();
    toggle.setAttribute('aria-expanded', String(enabled));
    toggle.textContent = enabled ? 'Giao diện thường' : 'Không gian học tập';
    outline.open = !enabled;
    save(); drawTimer();
    if (enabled && scroll) {
      $('space-timer-mode').focus({ preventScroll:true });
      if (matchMedia('(max-width: 767px)').matches) root.scrollIntoView({ block:'start' });
    }
  }
  toggle.addEventListener('click', () => setEnabled(!state.enabled, true));
  $('space-close').addEventListener('click', () => { setEnabled(false); toggle.focus(); });
  document.querySelector('a[href="#course-outline"]').addEventListener('click', () => { outline.open = true; });
  window.addEventListener('pagehide', () => { save(); stopAmbient(); destroyPlayer(); video.pause(); });
  window.addEventListener('pageshow', () => { drawTimer(); applyScenery(); });
  drawFavorites(); setVolumes(); setTab(state.tab); setEnabled(state.enabled);
  toggle.hidden = false;
  setInterval(() => { if (state.enabled) drawTimer(); }, 500);
})();
