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
            <?php foreach ($posts as $index => $p) :
                $has_image = has_post_thumbnail($p);
                ?>
                <a
                    class="home-carousel__slide<?php echo $has_image ? ' has-image' : ''; ?>"
                    href="<?php echo esc_url(get_permalink($p)); ?>"
                >
                    <?php
                    // Score badge (custom-review-score mu-plugin), if it's
                    // active and this post has a score — '' otherwise.
                    if (function_exists('mlrs_render_badge')) :
                        echo mlrs_render_badge($p->ID); // phpcs:ignore WordPress.Security.EscapeOutput -- escaped inside
                    endif;
                    ?>
                    <?php if ($has_image) :
                        // Phones get the slide at 16:9 (see the theme's CSS),
                        // the same shape featured images are uploaded in, so
                        // they're served the uncropped 'large' size family
                        // instead of the 32:9 mlfc-carousel crop made for the
                        // full-bleed desktop slide.
                        $mobile_srcset = wp_get_attachment_image_srcset(get_post_thumbnail_id($p), 'large');
                        ?>
                        <picture>
                            <?php if ($mobile_srcset) : ?>
                                <source media="(max-width: 47.99rem)" srcset="<?php echo esc_attr($mobile_srcset); ?>" sizes="100vw">
                            <?php endif; ?>
                            <?php echo get_the_post_thumbnail($p, 'mlfc-carousel', array(
                                'class'   => 'home-carousel__image',
                                // Only the first slide is visible on load; the rest
                                // only ever come into view after a manual/auto
                                // advance, so they shouldn't compete for bandwidth
                                // with it on initial page load.
                                'loading' => $index === 0 ? 'eager' : 'lazy',
                            )); ?>
                        </picture>
                    <?php endif; ?>
                    <span class="home-carousel__overlay">
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
