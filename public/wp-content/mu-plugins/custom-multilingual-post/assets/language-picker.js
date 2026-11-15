(function () {
  var picker = document.querySelector('[data-mlp-lang-picker]');
  if (!picker) {
    return;
  }

  var toggle = picker.querySelector('[data-mlp-lang-toggle]');
  var list = picker.querySelector('[data-mlp-lang-list]');

  var close = function () {
    list.hidden = true;
    toggle.setAttribute('aria-expanded', 'false');
  };

  toggle.addEventListener('click', function (e) {
    e.stopPropagation();
    var opening = list.hidden;
    list.hidden = !opening;
    toggle.setAttribute('aria-expanded', opening ? 'true' : 'false');
  });

  document.addEventListener('click', function (e) {
    if (!list.hidden && !picker.contains(e.target)) {
      close();
    }
  });

  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape' && !list.hidden) {
      close();
      toggle.focus();
    }
  });
})();
