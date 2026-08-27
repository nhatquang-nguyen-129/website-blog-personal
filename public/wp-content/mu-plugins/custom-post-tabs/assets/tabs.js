(function () {
  function initPostTabs(root) {
    var tabs = root.querySelectorAll('[data-post-tabs-tab]');
    var panels = root.querySelectorAll('[data-post-tabs-panel]');
    var perPage = root.getAttribute('data-posts-per-tab') || '5';

    tabs.forEach(function (tab) {
      tab.addEventListener('click', function () {
        var target = tab.getAttribute('data-post-tabs-tab');

        tabs.forEach(function (t) {
          var isActive = t === tab;
          t.classList.toggle('is-active', isActive);
          t.setAttribute('aria-selected', isActive ? 'true' : 'false');
        });

        panels.forEach(function (panel) {
          var isActive = panel.getAttribute('data-post-tabs-panel') === target;
          panel.classList.toggle('is-active', isActive);
          if (isActive) {
            panel.removeAttribute('hidden');
          } else {
            panel.setAttribute('hidden', '');
          }
        });
      });
    });

    // Delegated, not bound per-button: a page fetch replaces the pagination
    // markup entirely, which would silently drop a direct listener on any
    // one button.
    root.addEventListener('click', function (e) {
      var button = e.target.closest('[data-post-tabs-page]');
      if (!button || !root.contains(button)) {
        return;
      }

      var panel = button.closest('[data-post-tabs-panel]');
      if (!panel || button.classList.contains('is-active')) {
        return;
      }

      var tab = panel.getAttribute('data-post-tabs-panel');
      var page = button.getAttribute('data-post-tabs-page');
      fetchPage(root, panel, tab, page, perPage);
    });
  }

  function fetchPage(root, panel, tab, page, perPage) {
    if (!window.mlptSettings || !window.mlptSettings.restUrl) {
      return;
    }

    panel.setAttribute('aria-busy', 'true');

    var url = window.mlptSettings.restUrl
      + '?tab=' + encodeURIComponent(tab)
      + '&page=' + encodeURIComponent(page)
      + '&per_page=' + encodeURIComponent(perPage);

    window
      .fetch(url)
      .then(function (response) {
        return response.ok ? response.json() : Promise.reject(response);
      })
      .then(function (data) {
        var listWrap = panel.querySelector('.post-tabs__list-wrap');
        var paginationWrap = panel.querySelector('.post-tabs__pagination-wrap');
        if (listWrap) {
          listWrap.innerHTML = data.listHtml;
        }
        if (paginationWrap) {
          paginationWrap.innerHTML = data.paginationHtml;
        }
        panel.setAttribute('data-post-tabs-current-page', data.page);
        panel.removeAttribute('aria-busy');
      })
      .catch(function () {
        panel.removeAttribute('aria-busy');
      });
  }

  document.addEventListener('DOMContentLoaded', function () {
    var groups = document.querySelectorAll('[data-post-tabs]');
    groups.forEach(initPostTabs);
  });
})();
