(function () {
  document.addEventListener("DOMContentLoaded", function () {
    document.querySelectorAll(".ml-post").forEach(container => {
      const buttons = container.querySelectorAll(".ml-toggle button");
      const blocks = container.querySelectorAll("[data-lang]");

      if (!buttons.length || !blocks.length) return;

      function switchLang(lang) {
        blocks.forEach(el => {
          el.style.display = el.dataset.lang === lang ? "" : "none";
        });

        buttons.forEach(btn => {
          btn.classList.toggle("active", btn.dataset.lang === lang);
        });

        localStorage.setItem("ml_lang", lang);
      }

      const saved = localStorage.getItem("ml_lang");
      const defaultLang = saved || buttons[0].dataset.lang;
      switchLang(defaultLang);

      buttons.forEach(btn => {
        btn.addEventListener("click", () => switchLang(btn.dataset.lang));
      });
    });
  });
})();