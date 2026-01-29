(function () {
    const STORAGE_KEY = 'dark_mode_enabled';
    const root = document.documentElement;

    function enableDarkMode() {
        root.classList.add('dark-mode');
        localStorage.setItem(STORAGE_KEY, '1');
    }

    function disableDarkMode() {
        root.classList.remove('dark-mode');
        localStorage.setItem(STORAGE_KEY, '0');
    }

    function isDarkModeEnabled() {
        return localStorage.getItem(STORAGE_KEY) === '1';
    }

    document.addEventListener('DOMContentLoaded', function () {
        const toggle = document.getElementById('dark-mode-toggle');
        if (!toggle) return;

        // Initial state
        if (isDarkModeEnabled()) {
            enableDarkMode();
            toggle.textContent = '☀️';
        }

        toggle.addEventListener('click', function () {
            if (root.classList.contains('dark-mode')) {
                disableDarkMode();
                toggle.textContent = '🌙';
            } else {
                enableDarkMode();
                toggle.textContent = '☀️';
            }
        });
    });
})();