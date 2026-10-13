(function () {
  var button = document.querySelector('[data-back-to-top]');
  if (!button) {
    return;
  }

  // One viewport scrolled down — long enough that it never pops up on a
  // normal-length page or a quick scroll, which is exactly when it'd be
  // noise rather than useful (this project's posts are long, but its
  // archive/list pages aren't).
  var SHOW_AFTER = window.innerHeight;
  var ticking = false;

  function syncVisibility() {
    button.classList.toggle('is-visible', window.scrollY > SHOW_AFTER);
    ticking = false;
  }

  // rAF-throttled: scroll fires far more often than the visibility class
  // actually needs to change, and this runs on every page.
  window.addEventListener(
    'scroll',
    function () {
      if (!ticking) {
        window.requestAnimationFrame(syncVisibility);
        ticking = true;
      }
    },
    { passive: true }
  );

  syncVisibility();

  button.addEventListener('click', function () {
    var reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    window.scrollTo({ top: 0, behavior: reduceMotion ? 'auto' : 'smooth' });
  });
})();
