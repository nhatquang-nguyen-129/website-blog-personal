<?php
if (!defined('ABSPATH')) {
    exit;
}

define('MLP_PATH', __DIR__);

/** CORE – MUST LOAD FIRST (languages before post-group: post-group calls mlp_default_lang) */
require_once MLP_PATH . '/core/languages.php';
require_once MLP_PATH . '/core/post-group.php';
require_once MLP_PATH . '/core/duplicate-post.php';
require_once MLP_PATH . '/core/language-resolver.php';

/** ADMIN */
require_once MLP_PATH . '/admin/metabox-language.php';
require_once MLP_PATH . '/admin/metabox-translations.php';
require_once MLP_PATH . '/admin/ajax-create-translation.php';

/** FRONTEND */
require_once MLP_PATH . '/frontend/language-switcher.php';