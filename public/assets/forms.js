// Progressive enhancement only: PHP forms still work without JavaScript.
(() => {
  let quizChanged = false;
  const pending = new Set();
  let approvedExitForm = null;
  const quiz = document.querySelector('input[name="action"][value="submit_quiz"]')?.form;

  quiz?.addEventListener('change', (event) => {
    if (event.target.matches('input[type="radio"]')) quizChanged = true;
  });

  window.addEventListener('beforeunload', (event) => {
    if (!quizChanged || (quiz && pending.has(quiz)) ||
        (approvedExitForm && pending.has(approvedExitForm))) return;
    event.preventDefault();
    event.returnValue = '';
  });

  document.addEventListener('submit', (event) => {
    const form = event.target;
    if (form.method.toLowerCase() !== 'post' || event.defaultPrevented) return;
    if (pending.has(form)) {
      event.preventDefault();
      return;
    }
    // Confirm before locking a different form: cancelling beforeunload does
    // not trigger pageshow, so a pending logout would otherwise stay locked.
    if (quizChanged && form !== quiz) {
      if (!window.confirm('Bạn có đáp án quiz chưa nộp. Rời trang sẽ mất lựa chọn. Bạn có muốn tiếp tục?')) {
        event.preventDefault();
        return;
      }
      approvedExitForm = form;
    }
    pending.add(form);
    form.setAttribute('aria-busy', 'true');
    // Keep controls enabled so named submitters (e.g. AI mode) remain in POST.
    const status = document.createElement('p');
    status.className = 'form-pending small muted';
    status.setAttribute('role', 'status');
    status.textContent = 'Đang xử lý, vui lòng chờ…';
    form.append(status);
  });

  // A page restored by Back/Forward must allow another intentional submission.
  window.addEventListener('pageshow', () => {
    for (const form of pending) {
      form.removeAttribute('aria-busy');
      form.querySelector('.form-pending')?.remove();
    }
    pending.clear();
    approvedExitForm = null;
  });
})();
