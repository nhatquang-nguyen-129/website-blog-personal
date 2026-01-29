<?php

namespace Features\DarkMode;

class DarkModeService
{
    public function registerAssets(): void
    {
        $baseUrl = plugin_dir_url(__FILE__);

        wp_enqueue_style(
            'dark-mode-css',
            $baseUrl . 'assets/dark-mode.css'
        );

        wp_enqueue_script(
            'dark-mode-js',
            $baseUrl . 'assets/dark-mode.js',
            [],
            false,
            true
        );
    }
}
