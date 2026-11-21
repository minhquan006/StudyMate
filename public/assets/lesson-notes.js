// Private per-account/per-lesson notes on this browser; no academic POST fields.
(() => {
  const root = document.getElementById('lesson-notes');
  if (!root) return;
  const $ = id => document.getElementById(id);
  const input = $('lesson-note-input'), details = $('lesson-notes-details');
  const key = 'studymate:notes:v1:' + root.dataset.user + ':' + root.dataset.course + ':' + root.dataset.lesson;
  const limit = 20000;
  let lastRaw = null, dirty = false, conflict = false, recovery = false, timer, approvedExitForm = null;
  const size = text => [...text].length;
  function status(text, error = false) { $('lesson-note-status').textContent = text; root.classList.toggle('has-note-error', error); }
  function count() {
    const length = size(input.value);
    $('lesson-note-count').textContent = length.toLocaleString('vi-VN') + ' / 20.000';
    $('lesson-note-count').classList.toggle('over-limit', length > limit);
    $('lesson-note-download').disabled = !input.value;
    $('lesson-note-clear').disabled = !input.value && lastRaw === null;
    $('lesson-note-summary').textContent = input.value ? length.toLocaleString('vi-VN') + ' ký tự · chỉ lưu trên trình duyệt này' : 'Ghi lại điều bạn muốn nhớ.';
    return length;
  }
  function decode(raw) {
    if (raw === null) return null;
    const note = JSON.parse(raw);
    if (!note || note.version !== 1 || typeof note.text !== 'string' || size(note.text) > limit || typeof note.updatedAt !== 'string' || !Number.isFinite(Date.parse(note.updatedAt))) throw new Error('Invalid saved note');
    return note;
  }
  function applySaved(raw, announce = false) {
    const note = decode(raw);
    lastRaw = raw; input.value = note?.text || ''; dirty = false; conflict = false; recovery = false;
    $('lesson-note-conflict').hidden = true; $('lesson-note-recovery').hidden = true; count();
    if (note) status((announce ? 'Đã đọc bản lưu mới. ' : 'Đã lưu trên trình duyệt. ') + 'Cập nhật ' + new Date(note.updatedAt).toLocaleString('vi-VN') + '.');
    else status(announce ? 'Bản lưu hiện tại đang trống.' : 'Sẵn sàng ghi chú. Tự lưu sau khi bạn dừng gõ.');
    if (note?.text) details.open = true;
  }
  function showConflict() {
    conflict = true; $('lesson-note-conflict').hidden = false; details.open = true;
    status('Có thay đổi ở tab khác. Hãy đối chiếu trước khi lưu tiếp.', true);
  }
  function showRecovery(raw) {
    lastRaw = raw; recovery = true; dirty = Boolean(input.value); $('lesson-note-recovery').hidden = false; details.open = true;
    status('Bản lưu cũ chưa đọc được. Dữ liệu gốc vẫn được giữ.', true); count();
  }
  function save() {
    clearTimeout(timer);
    if (!dirty) return true;
    if (count() > limit) { status('Ghi chú vượt 20.000 ký tự. Hãy rút gọn hoặc tải xuống trước khi rời trang.', true); return false; }
    if (recovery) { status('Đang giữ bản lưu cũ. Tải bản gốc hoặc chọn tạo ghi chú mới trước khi lưu.', true); return false; }
    if (conflict) return false;
    try {
      if (localStorage.getItem(key) !== lastRaw) { showConflict(); return false; }
      const raw = JSON.stringify({ version:1, text:input.value, updatedAt:new Date().toISOString() });
      localStorage.setItem(key, raw); lastRaw = raw; dirty = false;
      status('Đã lưu lúc ' + new Date().toLocaleTimeString('vi-VN', { hour:'2-digit', minute:'2-digit' }) + ' trên trình duyệt này.');
      return true;
    } catch { status('Chưa lưu được trên trình duyệt. Nội dung vẫn ở đây; hãy tải xuống để giữ lại.', true); return false; }
  }
  const slug = text => text.normalize('NFD').replace(/[\u0300-\u036f]/g, '').replace(/[đĐ]/g,'d').replace(/[^a-zA-Z0-9]+/g,'-').replace(/^-|-$/g,'').slice(0,55) || 'bai-hoc';
  function download(text, suffix = '') {
    const blob = new Blob(['\uFEFF' + text], { type:'text/plain;charset=utf-8' });
    const url = URL.createObjectURL(blob), link = document.createElement('a');
    link.href = url; link.download = 'StudyMate-' + slug(root.dataset.courseTitle) + '-' + slug(root.dataset.lessonTitle) + suffix + '.txt';
    document.body.append(link); link.click(); link.remove(); setTimeout(() => URL.revokeObjectURL(url),10000);
  }
  function exportNote() {
    download('StudyMate — Ghi chú cá nhân\nKhóa học: ' + root.dataset.courseTitle + '\nBài học: ' + root.dataset.lessonTitle + '\nXuất lúc: ' + new Date().toLocaleString('vi-VN') + '\n\n' + input.value);
  }
  try { applySaved(localStorage.getItem(key)); }
  catch (error) {
    let raw;
    try { raw = localStorage.getItem(key); }
    catch { status('Trình duyệt đang chặn lưu trữ. Bạn vẫn có thể ghi và tải ghi chú xuống.', true); }
    if (typeof raw === 'string') showRecovery(raw);
  }
  input.addEventListener('input', () => {
    dirty = true;
    if (count() > limit) status('Ghi chú vượt 20.000 ký tự; chưa tự lưu.', true);
    else if (!conflict && !recovery) status('Đang sửa…');
    clearTimeout(timer); timer = setTimeout(save,700);
  });
  $('lesson-note-save').addEventListener('click', save);
  $('lesson-note-download').addEventListener('click', exportNote);
  $('lesson-note-raw-download').addEventListener('click', () => download(lastRaw || '', '-ban-luu-goc'));
  $('lesson-note-start-new').addEventListener('click', () => {
    if (!window.confirm('Tạo ghi chú mới thay cho bản lưu lỗi? Hãy tải bản gốc nếu muốn giữ lại.')) return;
    recovery = false; $('lesson-note-recovery').hidden = true; dirty = true; save(); input.focus();
  });
  $('lesson-note-use-saved').addEventListener('click', () => {
    if (dirty && !window.confirm('Đọc bản lưu mới và thay nội dung đang sửa? Bạn có thể tải ghi chú hiện tại trước.')) return;
    try { const raw = localStorage.getItem(key); try { applySaved(raw,true); } catch { showRecovery(raw); } }
    catch { status('Chưa đọc được bản lưu. Hãy tải ghi chú hiện tại để giữ lại.', true); }
  });
  $('lesson-note-keep-draft').addEventListener('click', () => {
    if (!window.confirm('Ghi đè bản lưu ở tab khác bằng nội dung đang sửa? Hãy tải ghi chú nếu cần giữ cả hai bản.')) return;
    try { lastRaw = localStorage.getItem(key); conflict = false; recovery = false; $('lesson-note-conflict').hidden = true; $('lesson-note-recovery').hidden = true; dirty = true; save(); }
    catch { status('Chưa lưu được. Bạn vẫn có thể tải ghi chú xuống.', true); }
  });
  $('lesson-note-clear').addEventListener('click', () => {
    if (!window.confirm('Xóa ghi chú của riêng bài này trên trình duyệt? Tải xuống trước nếu muốn giữ một bản.')) return;
    clearTimeout(timer);
    try {
      if (localStorage.getItem(key) !== lastRaw) { showConflict(); return; }
      localStorage.removeItem(key); applySaved(null); status('Đã xóa ghi chú của bài này trên trình duyệt.');
    } catch { status('Chưa xóa được bản lưu. Nội dung hiện tại vẫn được giữ.', true); }
  });
  window.addEventListener('storage', event => {
    if (event.key !== key && event.key !== null) return;
    if (dirty || recovery) { showConflict(); return; }
    try { applySaved(event.newValue,true); } catch { showRecovery(event.newValue); }
  });
  // Confirm a failed save before forms.js locks a POST. Cancelling must leave
  // lesson/AI/logout forms available for another intentional submission.
  document.addEventListener('submit', event => {
    if (save() || !dirty || event.target.method.toLowerCase() !== 'post') return;
    if (!window.confirm('Ghi chú chưa lưu được. Bạn có thể tải xuống để giữ lại. Tiếp tục rời trang và bỏ phần chưa lưu?')) {
      event.preventDefault(); details.open = true; input.focus(); return;
    }
    approvedExitForm = event.target;
    queueMicrotask(() => { if (event.defaultPrevented) approvedExitForm = null; });
  }, true);
  document.addEventListener('visibilitychange', () => { if (document.hidden) save(); });
  window.addEventListener('beforeunload', event => { save(); if (dirty && !(approvedExitForm && approvedExitForm.getAttribute('aria-busy') === 'true')) { event.preventDefault(); event.returnValue = ''; } });
  window.addEventListener('pagehide', () => { save(); clearTimeout(timer); });
  window.addEventListener('pageshow', event => {
    approvedExitForm = null;
    if (!event.persisted || dirty) return;
    try { applySaved(localStorage.getItem(key),true); } catch { status('Chưa đọc lại được bản lưu. Nội dung trên màn hình vẫn được giữ.', true); }
  });
  count(); root.hidden = false;
})();
