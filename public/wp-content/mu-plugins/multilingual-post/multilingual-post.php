<?php
/**
 * Plugin Name: Multilingual Post Toggle (MU)
 * Description: Toggle multilingual content per post using declared language blocks.
 */

defined('ABSPATH') || exit;

define('ML_POST_PATH', __DIR__);
define('ML_POST_URL', content_url('mu-plugins/multilingual-post'));

require_once ML_POST_PATH . '/inc/meta.php';
require_once ML_POST_PATH . '/inc/render.php';
require_once ML_POST_PATH . '/inc/enqueue.php';