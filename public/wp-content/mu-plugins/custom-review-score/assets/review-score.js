(function () {
  var boxes = document.querySelectorAll('[data-mlrs-box]');
  if (!boxes.length) {
    return;
  }

  var reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  // Without this script (or with reduced motion) the box is already
  // complete as server-rendered, showing the final number. The animation is
  // opt-in from here: count the number up from 0 once the box scrolls into
  // view.
  if (reduceMotion || !('IntersectionObserver' in window)) {
    return;
  }

  var DURATION = 1200;

  function countUp(numberEl) {
    var target = numberEl.getAttribute('data-mlrs-count');
    var end = parseFloat(target);
    var decimals = target.indexOf('.') === -1 ? 0 : target.split('.')[1].length;
    var start = null;

    function frame(now) {
      if (start === null) {
        start = now;
      }
      var progress = Math.min((now - start) / DURATION, 1);
      var eased = 1 - Math.pow(1 - progress, 3);
      numberEl.textContent = (end * eased).toFixed(decimals);
      if (progress < 1) {
        window.requestAnimationFrame(frame);
      } else {
        numberEl.textContent = target;
      }
    }

    window.requestAnimationFrame(frame);
  }

  var observer = new IntersectionObserver(function (entries) {
    entries.forEach(function (entry) {
      if (!entry.isIntersecting) {
        return;
      }
      var box = entry.target;
      observer.unobserve(box);
      var numberEl = box.querySelector('[data-mlrs-count]');
      if (numberEl) {
        countUp(numberEl);
      }
    });
  }, { threshold: 0.4 });

  Array.prototype.forEach.call(boxes, function (box) {
    var numberEl = box.querySelector('[data-mlrs-count]');
    if (numberEl) {
      numberEl.textContent = '0';
    }
    observer.observe(box);
  });
})();
