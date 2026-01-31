<?php
/*
Plugin Name: Multilingual Post (MU)
*/

if (!defined('ABSPATH')) exit;

define('MLP_PATH', __DIR__);

require MLP_PATH . '/admin/metabox-language.php';
require MLP_PATH . '/admin/metabox-translations.php';
require MLP_PATH . '/admin/create-translation.php';

require MLP_PATH . '/core/post-linking.php';
require MLP_PATH . '/core/duplicate-post.php';

require MLP_PATH . '/frontend/language-switcher.php';