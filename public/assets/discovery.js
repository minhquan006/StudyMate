// Discovery controls enhance native links/forms without changing their contracts.
(() => {
  const dismiss = document.querySelector('[data-dismiss-announcement]');
  if (dismiss) {
    dismiss.hidden = false;
    dismiss.addEventListener('click', () => dismiss.closest('.learning-announcement').remove());
  }
  const menu = document.querySelector('.explore-menu');
  if (!menu) return;
  document.addEventListener('click', event => {
    if (!menu.contains(event.target)) menu.open = false;
  });
  menu.addEventListener('keydown', event => {
    if (event.key !== 'Escape' || !menu.open) return;
    menu.open = false;
    menu.querySelector('summary').focus();
    event.preventDefault();
  });
})();
