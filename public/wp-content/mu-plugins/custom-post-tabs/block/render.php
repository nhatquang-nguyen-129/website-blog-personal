<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * $attributes, $content, $block are provided in scope by WordPress because
 * block.json declares "render": "file:./render.php".
 */

$posts_per_tab = !empty($attributes['postsPerTab'])
    ? max(1, min(10, (int) $attributes['postsPerTab']))
    : 5;

$base_query_args = array(
    'post_type'           => 'post',
    'post_status'         => 'publish',
    'posts_per_page'      => $posts_per_tab,
    'ignore_sticky_posts' => true,
);
// Skip translation posts so a group only ever shows once — provided by the
// custom-multilingual-post mu-plugin, if it's active. Same pattern as
// custom-featured-carousel's render.php.
if (function_exists('mlp_exclude_translations_meta_query')) {
    $base_query_args['meta_query'] = array(mlp_exclude_translations_meta_query());
}

// "Top" (by view count) isn't here yet — WordPress has no native view/
// popularity metric, so it needs its own decision later rather than being
// bolted on. Comment count, on the other hand, is data core already tracks
// per post, so "Discussions" costs nothing extra to add now.
$tabs = array(
    'latest' => array(
        'label' => __('Latest', 'post-tabs'),
        'query' => new WP_Query(array_merge($base_query_args, array(
            'orderby' => 'date',
            'order'   => 'DESC',
        ))),
    ),
    'discussions' => array(
        'label' => __('Discussions', 'post-tabs'),
        'query' => new WP_Query(array_merge($base_query_args, array(
            'orderby' => array('comment_count' => 'DESC', 'date' => 'DESC'),
        ))),
    ),
);
?>
<div <?php echo get_block_wrapper_attributes(array('class' => 'post-tabs')); ?> data-post-tabs>
    <div class="post-tabs__nav" role="tablist">
        <?php $is_first = true; ?>
        <?php foreach ($tabs as $key => $tab) : ?>
            <button
                type="button"
                class="post-tabs__tab<?php echo $is_first ? ' is-active' : ''; ?>"
                role="tab"
                id="post-tabs__tab-<?php echo esc_attr($key); ?>"
                aria-selected="<?php echo $is_first ? 'true' : 'false'; ?>"
                aria-controls="post-tabs__panel-<?php echo esc_attr($key); ?>"
                data-post-tabs-tab="<?php echo esc_attr($key); ?>"
            ><?php echo esc_html($tab['label']); ?></button>
            <?php $is_first = false; ?>
        <?php endforeach; ?>
    </div>

    <?php $is_first = true; ?>
    <?php foreach ($tabs as $key => $tab) : ?>
        <div
            class="post-tabs__panel<?php echo $is_first ? ' is-active' : ''; ?>"
            role="tabpanel"
            id="post-tabs__panel-<?php echo esc_attr($key); ?>"
            aria-labelledby="post-tabs__tab-<?php echo esc_attr($key); ?>"
            data-post-tabs-panel="<?php echo esc_attr($key); ?>"
            <?php echo $is_first ? '' : 'hidden'; ?>
        >
            <?php if ($tab['query']->have_posts()) : ?>
                <ul class="post-tabs__list">
                    <?php while ($tab['query']->have_posts()) : $tab['query']->the_post(); ?>
                        <li class="post-tabs__item">
                            <a class="post-tabs__item-link" href="<?php echo esc_url(get_permalink()); ?>">
                                <div class="post-tabs__item-body">
                                    <h3 class="post-tabs__item-title"><?php echo esc_html(get_the_title()); ?></h3>
                                    <p class="post-tabs__item-excerpt"><?php echo esc_html(wp_trim_words(get_the_excerpt(), 24)); ?></p>
                                    <p class="post-tabs__item-meta">
                                        <?php echo esc_html(get_the_date()); ?> · <?php echo esc_html(get_the_author()); ?>
                                    </p>
                                </div>
                                <?php if (has_post_thumbnail()) : ?>
                                    <div class="post-tabs__item-thumb">
                                        <?php the_post_thumbnail('mlpt-thumb', array('class' => 'post-tabs__item-image')); ?>
                                    </div>
                                <?php endif; ?>
                            </a>
                        </li>
                    <?php endwhile; ?>
                    <?php wp_reset_postdata(); ?>
                </ul>
            <?php else : ?>
                <p class="post-tabs__empty"><?php esc_html_e('Nothing here yet.', 'post-tabs'); ?></p>
            <?php endif; ?>
        </div>
        <?php $is_first = false; ?>
    <?php endforeach; ?>
</div>
