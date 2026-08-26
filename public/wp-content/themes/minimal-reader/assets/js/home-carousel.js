(function () {
  function initCarousel(root) {
    var track = root.querySelector('[data-carousel-track]');
    var slides = track ? Array.prototype.slice.call(track.children) : [];
    var dots = Array.prototype.slice.call(root.querySelectorAll('[data-carousel-goto]'));
    var autoplayMs = parseInt(root.getAttribute('data-autoplay') || '0', 10);
    var index = 0;
    var timer = null;
    var reduceMotion = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    if (slides.length < 2 || !track) {
      return;
    }

    function goTo(i) {
      index = (i + slides.length) % slides.length;
      track.style.transform = 'translateX(-' + index * 100 + '%)';
      dots.forEach(function (dot, dotIndex) {
        dot.classList.toggle('is-active', dotIndex === index);
      });
    }

    function next() {
      goTo(index + 1);
    }

    function start() {
      if (!autoplayMs || reduceMotion) {
        return;
      }
      stop();
      timer = window.setInterval(next, autoplayMs);
    }

    function stop() {
      if (timer) {
        window.clearInterval(timer);
        timer = null;
      }
    }

    dots.forEach(function (dot) {
      dot.addEventListener('click', function () {
        goTo(parseInt(dot.getAttribute('data-carousel-goto'), 10));
        start();
      });
    });

    root.addEventListener('mouseenter', stop);
    root.addEventListener('mouseleave', start);
    root.addEventListener('focusin', stop);
    root.addEventListener('focusout', start);

    goTo(0);
    start();
  }

  document.addEventListener('DOMContentLoaded', function () {
    var carousels = document.querySelectorAll('[data-carousel]');
    carousels.forEach(initCarousel);
  });
})();
