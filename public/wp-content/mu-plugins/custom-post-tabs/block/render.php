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

$tabs = mlpt_valid_tabs();
?>
<div
    <?php echo get_block_wrapper_attributes(array('class' => 'post-tabs')); ?>
    data-post-tabs
    data-posts-per-tab="<?php echo esc_attr($posts_per_tab); ?>"
>
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
    <?php foreach ($tabs as $key => $tab) :
        $query = mlpt_run_tab_query($key, 1, $posts_per_tab);
        ?>
        <div
            class="post-tabs__panel<?php echo $is_first ? ' is-active' : ''; ?>"
            role="tabpanel"
            id="post-tabs__panel-<?php echo esc_attr($key); ?>"
            aria-labelledby="post-tabs__tab-<?php echo esc_attr($key); ?>"
            data-post-tabs-panel="<?php echo esc_attr($key); ?>"
            data-post-tabs-current-page="1"
            <?php echo $is_first ? '' : 'hidden'; ?>
        >
            <div class="post-tabs__list-wrap"><?php echo mlpt_render_list_html($query); ?></div>
            <div class="post-tabs__pagination-wrap"><?php echo mlpt_render_pagination_html($key, 1, $query->max_num_pages); ?></div>
        </div>
        <?php $is_first = false; ?>
    <?php endforeach; ?>
</div>
