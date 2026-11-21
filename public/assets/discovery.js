// Discovery controls enhance native links/forms without changing their contracts.
(() => {
  const dismiss = document.querySelector('[data-dismiss-announcement]');
  if (dismiss) {
    dismiss.hidden = false;
    dismiss.addEventListener('click', () => dismiss.closest('.learning-announcement').remove());
  }
  const gallery = document.querySelector('[data-hero-gallery]');
  if (gallery) {
    const slides = [...gallery.querySelectorAll('.hero-slide')];
    const dots = [...gallery.querySelectorAll('[data-hero-index]')];
    const track = gallery.querySelector('.hero-track');
    const motion = gallery.querySelector('[data-hero-motion]');
    const reduced = window.matchMedia('(prefers-reduced-motion: reduce)');
    let index = 0, direction = 1, paused = reduced.matches, hovered = false, focused = false, visible = true, timer;
    function schedule() {
      clearTimeout(timer);
      gallery.classList.toggle('is-paused', paused || reduced.matches || hovered || focused || !visible || document.hidden);
      if (paused || reduced.matches || hovered || focused || !visible || document.hidden) return;
      timer = setTimeout(() => {
        if (index === slides.length - 1) direction = -1;
        if (index === 0) direction = 1;
        show(index + direction);
      }, 6500);
    }
    function updateMotion() {
      const stopped = paused || reduced.matches;
      motion.setAttribute('aria-label', stopped ? 'Bật chuyển ảnh tự động' : 'Tạm dừng chuyển ảnh');
      motion.querySelector('[data-hero-motion-icon]').textContent = stopped ? '▷' : 'Ⅱ';
      // Reduced motion always keeps transitions manual, even if the button is activated.
      motion.disabled = reduced.matches;
      if (reduced.matches) motion.setAttribute('aria-label', 'Chuyển ảnh tự động tắt theo cài đặt giảm chuyển động');
      schedule();
    }
    function show(next, announce = false) {
      index = (next + slides.length) % slides.length;
      track.style.transform = `translateX(-${index * 100}%)`;
      slides.forEach((slide, i) => {
        slide.setAttribute('aria-hidden', i === index ? 'false' : 'true');
        slide.inert = i !== index;
        dots[i].setAttribute('aria-pressed', String(i === index));
      });
      if (announce) gallery.querySelector('[data-hero-status]').textContent = slides[index].getAttribute('aria-label');
      schedule();
    }
    gallery.classList.add('is-enhanced');
    gallery.querySelector('.hero-controls').hidden = false;
    gallery.querySelector('[data-hero-prev]').addEventListener('click', () => show(index - 1, true));
    gallery.querySelector('[data-hero-next]').addEventListener('click', () => show(index + 1, true));
    dots.forEach((dot, i) => dot.addEventListener('click', () => show(i, true)));
    motion.addEventListener('click', () => { paused = !paused; updateMotion(); });
    gallery.addEventListener('mouseenter', () => { hovered = true; schedule(); });
    gallery.addEventListener('mouseleave', () => { hovered = false; schedule(); });
    gallery.addEventListener('focusin', () => { focused = true; schedule(); });
    gallery.addEventListener('focusout', event => { focused = gallery.contains(event.relatedTarget); schedule(); });
    reduced.addEventListener('change', updateMotion);
    document.addEventListener('visibilitychange', schedule);
    window.addEventListener('pagehide', () => clearTimeout(timer));
    window.addEventListener('pageshow', schedule);
    if ('IntersectionObserver' in window) new IntersectionObserver(entries => { visible = entries[0].isIntersecting; schedule(); }).observe(gallery);
    show(0);
    updateMotion();
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
