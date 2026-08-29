(function () {
  // Below this, a comment is short enough to just show in full — no point
  // collapsing something that wouldn't have needed a toggle anyway.
  var COLLAPSED_HEIGHT = 96;

  function initCommentToggle(content) {
    if (content.scrollHeight <= COLLAPSED_HEIGHT + 16) {
      return;
    }

    content.classList.add('is-collapsed');

    var toggle = document.createElement('button');
    toggle.type = 'button';
    toggle.className = 'comment-content__toggle';
    toggle.textContent = 'See more';

    toggle.addEventListener('click', function () {
      var collapsed = content.classList.toggle('is-collapsed');
      toggle.textContent = collapsed ? 'See more' : 'See less';
    });

    content.insertAdjacentElement('afterend', toggle);
  }

  document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.comment-content').forEach(initCommentToggle);
  });
})();
