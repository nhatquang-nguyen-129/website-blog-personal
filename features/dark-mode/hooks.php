<?php

use Features\DarkMode\DarkModeService;

require_once __DIR__ . '/DarkModeService.php';

add_action('wp_enqueue_scripts', function () {
    (new DarkModeService())->registerAssets();
});
