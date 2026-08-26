<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * $attributes, $content, $block are provided in scope by WordPress because
 * block.json declares "render": "file:./render.php".
 */

$delay    = !empty($attributes['delay']) ? (int) $attributes['delay'] : 5000;
$post_ids = !empty($attributes['postIds']) && is_array($attributes['postIds'])
    ? array_slice(array_map('intval', $attributes['postIds']), 0, 10)
    : array();

if (!$post_ids) {
    return;
}

$query_args = array(
    'post_type'           => 'post',
    'post_status'         => 'publish',
    'post__in'            => $post_ids,
    'orderby'             => 'post__in',
    'posts_per_page'      => count($post_ids),
    // Without this, WP_Query prepends any Sticky Posts ahead of the
    // explicit post__in list, even though this block doesn't use stickiness
    // at all any more.
    'ignore_sticky_posts' => true,
);
// Skip translation posts so a group only ever shows once — provided by the
// custom-multilingual-post mu-plugin, if it's active.
if (function_exists('mlp_exclude_translations_meta_query')) {
    $query_args['meta_query'] = array(mlp_exclude_translations_meta_query());
}

$posts = (new WP_Query($query_args))->posts;

if (!$posts) {
    return;
}
?>
<section
    <?php echo get_block_wrapper_attributes(array('class' => 'home-carousel')); ?>
    data-carousel
    <?php echo count($posts) > 1 ? ' data-autoplay="' . esc_attr($delay) . '"' : ''; ?>
>
    <div class="home-carousel__viewport">
        <div class="home-carousel__track" data-carousel-track>
            <?php foreach ($posts as $p) :
                $has_image = has_post_thumbnail($p);
                ?>
                <a
                    class="home-carousel__slide<?php echo $has_image ? ' has-image' : ''; ?>"
                    href="<?php echo esc_url(get_permalink($p)); ?>"
                    <?php if ($has_image) : ?>
                        style="background-image:url('<?php echo esc_url(get_the_post_thumbnail_url($p, 'large')); ?>')"
                    <?php endif; ?>
                >
                    <span class="home-carousel__overlay">
                        <span class="home-carousel__label"><?php esc_html_e('Featured', 'featured-carousel'); ?></span>
                        <span class="home-carousel__title"><?php echo esc_html(get_the_title($p)); ?></span>
                    </span>
                </a>
            <?php endforeach; ?>
        </div>

        <?php if (count($posts) > 1) : ?>
            <button type="button" class="home-carousel__arrow home-carousel__arrow--prev" data-carousel-prev aria-label="<?php esc_attr_e('Previous slide', 'featured-carousel'); ?>">&#8249;</button>
            <button type="button" class="home-carousel__arrow home-carousel__arrow--next" data-carousel-next aria-label="<?php esc_attr_e('Next slide', 'featured-carousel'); ?>">&#8250;</button>
        <?php endif; ?>
    </div>

    <?php if (count($posts) > 1) : ?>
        <div class="home-carousel__dots" data-carousel-dots>
            <?php foreach ($posts as $index => $p) : ?>
                <button
                    type="button"
                    class="home-carousel__dot<?php echo $index === 0 ? ' is-active' : ''; ?>"
                    data-carousel-goto="<?php echo esc_attr($index); ?>"
                    aria-label="<?php echo esc_attr(sprintf(__('Go to slide %d', 'featured-carousel'), $index + 1)); ?>"
                ></button>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>
