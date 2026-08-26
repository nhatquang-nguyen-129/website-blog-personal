<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * $attributes, $content, $block are provided in scope by WordPress because
 * block.json declares "render": "file:./render.php".
 */

$post_id = get_the_ID();
if (!$post_id) {
    return;
}

$headings = mlptoc_extract_headings(get_post_field('post_content', $post_id));
if (count($headings) < 2) {
    return;
}

$title = !empty($attributes['title']) ? $attributes['title'] : __('Mục lục', 'table-of-contents');
?>
<nav <?php echo get_block_wrapper_attributes(array('class' => 'mlptoc')); ?> aria-label="<?php echo esc_attr(wp_strip_all_tags($title)); ?>">
    <p class="mlptoc__title"><?php echo wp_kses_post($title); ?></p>
    <ol class="mlptoc__list">
        <?php foreach ($headings as $heading) : ?>
            <li class="mlptoc__item mlptoc__item--level-<?php echo esc_attr($heading['level']); ?>">
                <a href="#<?php echo esc_attr($heading['id']); ?>"><?php echo esc_html($heading['text']); ?></a>
            </li>
        <?php endforeach; ?>
    </ol>
</nav>
