<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Shared between block/render.php (first page, server-rendered) and the
 * /mlpt/v1/posts REST route (every page after that, fetched by tabs.js) so
 * both ever produce exactly the same markup from exactly the same query
 * logic — one definition of what a tab's list/pagination look like, not two
 * copies that can drift apart.
 */

function mlpt_valid_tabs() {
    return array(
        'latest'      => array(
            'label'   => __('Latest', 'post-tabs'),
            'orderby' => 'date',
            'order'   => 'DESC',
        ),
        'discussions' => array(
            'label'   => __('Discussions', 'post-tabs'),
            'orderby' => array('comment_count' => 'DESC', 'date' => 'DESC'),
        ),
    );
}

function mlpt_run_tab_query($tab, $page, $posts_per_page) {
    $tabs = mlpt_valid_tabs();
    if (!isset($tabs[$tab])) {
        return null;
    }

    $args = array(
        'post_type'           => 'post',
        'post_status'         => 'publish',
        'posts_per_page'      => $posts_per_page,
        'paged'               => $page,
        'ignore_sticky_posts' => true,
        'orderby'             => $tabs[$tab]['orderby'],
    );
    if (isset($tabs[$tab]['order'])) {
        $args['order'] = $tabs[$tab]['order'];
    }
    // Skip translation posts so a group only ever shows once — provided by
    // the custom-multilingual-post mu-plugin, if it's active. Same pattern
    // as custom-featured-carousel's render.php.
    if (function_exists('mlp_exclude_translations_meta_query')) {
        $args['meta_query'] = array(mlp_exclude_translations_meta_query());
    }

    return new WP_Query($args);
}

function mlpt_render_list_html(WP_Query $query) {
    if (!$query->have_posts()) {
        return '<p class="post-tabs__empty">' . esc_html__('Nothing here yet.', 'post-tabs') . '</p>';
    }

    ob_start();
    ?>
    <ul class="post-tabs__list">
        <?php while ($query->have_posts()) : $query->the_post(); ?>
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
    </ul>
    <?php
    wp_reset_postdata();
    return ob_get_clean();
}

/**
 * "1 2 3 … 100"-style page numbers: first page, last page, and a small
 * window around the current page, with a '...' marker over any gap. Not
 * WordPress's own paginate_links() — that renders real <a href> navigation
 * links, but these are buttons that fetch a page over REST instead of
 * navigating, so the numbers need to come back as plain data to build
 * <button>s from, not pre-built anchor markup.
 */
function mlpt_page_number_sequence($current, $total, $window = 1) {
    if ($total <= 1) {
        return array(1);
    }

    $pages = array();
    for ($i = 1; $i <= $total; $i++) {
        if ($i === 1 || $i === $total || abs($i - $current) <= $window) {
            $pages[] = $i;
        }
    }

    $sequence = array();
    $previous = null;
    foreach ($pages as $page) {
        if ($previous !== null && $page - $previous > 1) {
            $sequence[] = '...';
        }
        $sequence[] = $page;
        $previous = $page;
    }

    return $sequence;
}

function mlpt_render_pagination_html($tab, $current, $total) {
    if ($total <= 1) {
        return '';
    }

    ob_start();
    ?>
    <nav class="post-tabs__pagination" aria-label="<?php esc_attr_e('Pagination', 'post-tabs'); ?>">
        <?php foreach (mlpt_page_number_sequence($current, $total) as $item) : ?>
            <?php if ($item === '...') : ?>
                <span class="post-tabs__page-dots">&hellip;</span>
            <?php else : ?>
                <button
                    type="button"
                    class="post-tabs__page<?php echo $item === $current ? ' is-active' : ''; ?>"
                    data-post-tabs-page="<?php echo esc_attr($item); ?>"
                    aria-current="<?php echo $item === $current ? 'true' : 'false'; ?>"
                ><?php echo esc_html($item); ?></button>
            <?php endif; ?>
        <?php endforeach; ?>
    </nav>
    <?php
    return ob_get_clean();
}
