(function () {
  function initCarousel(root) {
    var track = root.querySelector('[data-carousel-track]');
    var slides = track ? Array.prototype.slice.call(track.children) : [];
    var dots = Array.prototype.slice.call(root.querySelectorAll('[data-carousel-goto]'));
    var prevBtn = root.querySelector('[data-carousel-prev]');
    var nextBtn = root.querySelector('[data-carousel-next]');
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

    // A manual interaction (arrow, dot, swipe) resets the autoplay clock so
    // the next slide doesn't arrive a moment after the user just navigated.
    function goToAndRestart(i) {
      goTo(i);
      start();
    }

    dots.forEach(function (dot) {
      dot.addEventListener('click', function () {
        goToAndRestart(parseInt(dot.getAttribute('data-carousel-goto'), 10));
      });
    });

    if (prevBtn) {
      prevBtn.addEventListener('click', function () {
        goToAndRestart(index - 1);
      });
    }
    if (nextBtn) {
      nextBtn.addEventListener('click', function () {
        goToAndRestart(index + 1);
      });
    }

    // Two-finger horizontal trackpad swipe (macOS and similar) fires as
    // wheel events with a dominant deltaX.
    var wheelLocked = false;
    root.addEventListener(
      'wheel',
      function (e) {
        if (Math.abs(e.deltaX) <= Math.abs(e.deltaY) || Math.abs(e.deltaX) < 12) {
          return;
        }
        e.preventDefault();
        if (wheelLocked) {
          return;
        }
        wheelLocked = true;
        goToAndRestart(e.deltaX > 0 ? index + 1 : index - 1);
        window.setTimeout(function () {
          wheelLocked = false;
        }, 400);
      },
      { passive: false }
    );

    // Touch swipe (phones/tablets).
    var touchStartX = null;
    root.addEventListener(
      'touchstart',
      function (e) {
        touchStartX = e.touches[0].clientX;
      },
      { passive: true }
    );
    root.addEventListener(
      'touchend',
      function (e) {
        if (touchStartX === null) {
          return;
        }
        var deltaX = e.changedTouches[0].clientX - touchStartX;
        touchStartX = null;
        if (Math.abs(deltaX) < 40) {
          return;
        }
        goToAndRestart(deltaX < 0 ? index + 1 : index - 1);
      },
      { passive: true }
    );

    // Hover and focus are tracked as flags rather than calling stop()/start()
    // straight off each event, for two reasons: hovering AND focusing at once
    // then only one of them ending shouldn't resume autoplay while the other
    // condition still holds, and switching browser tabs while hovering never
    // fires mouseleave on the now-hidden page — without a flag, the carousel
    // would stay "paused" indefinitely until an unrelated mouse move outside
    // then back inside it.
    var isHovering = false;
    var isFocusedWithin = false;

    function updatePlayState() {
      if (isHovering || isFocusedWithin || document.hidden) {
        stop();
      } else {
        start();
      }
    }

    root.addEventListener('mouseenter', function () {
      isHovering = true;
      updatePlayState();
    });
    root.addEventListener('mouseleave', function () {
      isHovering = false;
      updatePlayState();
    });
    root.addEventListener('focusin', function () {
      isFocusedWithin = true;
      updatePlayState();
    });
    root.addEventListener('focusout', function () {
      isFocusedWithin = false;
      updatePlayState();
    });

    // Covers the actual tab-switch case above: once the tab is visible again,
    // hover/focus flags may be stale (their "end" event never fired while
    // hidden), so reset them instead of trusting whatever they happened to
    // be left at.
    document.addEventListener('visibilitychange', function () {
      if (!document.hidden) {
        isHovering = false;
        isFocusedWithin = false;
      }
      updatePlayState();
    });

    goTo(0);
    start();
  }

  document.addEventListener('DOMContentLoaded', function () {
    var carousels = document.querySelectorAll('[data-carousel]');
    carousels.forEach(initCarousel);
  });
})();
