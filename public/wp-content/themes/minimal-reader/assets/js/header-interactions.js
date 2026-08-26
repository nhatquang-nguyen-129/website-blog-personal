(function () {
  document.addEventListener('DOMContentLoaded', function () {
    var searchToggle = document.querySelector('[data-search-toggle]');
    var searchPanel = document.querySelector('[data-search-panel]');

    if (searchToggle && searchPanel) {
      searchToggle.addEventListener('click', function () {
        var isHidden = searchPanel.hasAttribute('hidden');
        if (isHidden) {
          searchPanel.removeAttribute('hidden');
          var input = searchPanel.querySelector('input[type="search"]');
          if (input) {
            input.focus();
          }
        } else {
          searchPanel.setAttribute('hidden', '');
        }
      });
    }

    var shareToggle = document.querySelector('[data-share-toggle]');
    var shareMenu = document.querySelector('[data-share-menu]');

    if (!shareToggle || !shareMenu) {
      return;
    }

    function closeShareMenu() {
      shareMenu.setAttribute('hidden', '');
      shareToggle.setAttribute('aria-expanded', 'false');
    }

    function openShareMenu() {
      shareMenu.removeAttribute('hidden');
      shareToggle.setAttribute('aria-expanded', 'true');
    }

    shareToggle.addEventListener('click', function (e) {
      e.stopPropagation();
      if (shareMenu.hasAttribute('hidden')) {
        openShareMenu();
      } else {
        closeShareMenu();
      }
    });

    document.addEventListener('click', function (e) {
      if (!shareMenu.hasAttribute('hidden') && !shareMenu.contains(e.target) && e.target !== shareToggle) {
        closeShareMenu();
      }
    });

    document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape') {
        closeShareMenu();
      }
    });

    var popupOpts = 'noopener,noreferrer,width=600,height=520';

    function openSharePopup(targetUrl) {
      window.open(targetUrl, '_blank', popupOpts);
    }

    Array.prototype.forEach.call(shareMenu.querySelectorAll('[data-share-action]'), function (item) {
      item.addEventListener('click', function () {
        var url = window.location.href;
        var title = document.title;
        var action = item.getAttribute('data-share-action');

        switch (action) {
          case 'copy':
            if (navigator.clipboard) {
              navigator.clipboard.writeText(url).then(function () {
                var label = item.querySelector('.site-header__share-label');
                if (!label) {
                  return;
                }
                var original = label.textContent;
                label.textContent = 'Copied!';
                window.setTimeout(function () {
                  label.textContent = original;
                }, 1200);
              });
            }
            return; // Keep the menu open so the "Copied!" label is visible.
          case 'email':
            window.location.href = 'mailto:?subject=' + encodeURIComponent(title) + '&body=' + encodeURIComponent(url);
            break;
          case 'facebook':
            openSharePopup('https://www.facebook.com/sharer/sharer.php?u=' + encodeURIComponent(url));
            break;
          case 'linkedin':
            openSharePopup('https://www.linkedin.com/sharing/share-offsite/?url=' + encodeURIComponent(url));
            break;
          case 'bluesky':
            openSharePopup('https://bsky.app/intent/compose?text=' + encodeURIComponent(title + ' ' + url));
            break;
          case 'x':
            openSharePopup('https://twitter.com/intent/tweet?url=' + encodeURIComponent(url) + '&text=' + encodeURIComponent(title));
            break;
        }

        closeShareMenu();
      });
    });
  });
})();
