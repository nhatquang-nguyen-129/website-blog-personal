<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * $attributes, $content, $block are provided in scope by WordPress because
 * block.json declares "render": "file:./render.php". $content is the
 * summary's rendered inner blocks.
 *
 * get_the_ID(), not $block->context['postId']: on a translation viewed via
 * ?lang= (custom-multilingual-post), the global post is the swapped-in
 * translation, and mlrs_get_score() handles falling back to its original.
 */

$post_id = get_the_ID() ?: (isset($block->context['postId']) ? (int) $block->context['postId'] : 0);

$score = mlrs_get_score($post_id);
$label = isset($attributes['label']) ? trim(wp_strip_all_tags($attributes['label'])) : '';
if ($label === '') {
    $label = mlrs_default_label();
}

// A box with no score has nothing to highlight — just keep the summary
// text rather than render an empty score panel.
if ($score === null) {
    echo $content; // phpcs:ignore WordPress.Security.EscapeOutput -- already-rendered inner blocks
    return;
}

$formatted = mlrs_format_score($score);
?>
<section
    <?php echo get_block_wrapper_attributes(array(
        'class' => 'mlrs-box mlrs-tier--' . mlrs_score_tier($score),
    )); ?>
    data-mlrs-box
>
    <div class="mlrs-box__score">
        <span class="mlrs-box__label"><?php echo esc_html($label); ?></span>
        <span class="mlrs-box__value">
            <span class="mlrs-box__number" data-mlrs-count="<?php echo esc_attr($formatted); ?>"><?php echo esc_html($formatted); ?></span><span class="mlrs-box__max">/10</span>
        </span>
    </div>

    <div class="mlrs-box__summary">
        <div class="mlrs-box__summary-body">
            <?php echo $content; // phpcs:ignore WordPress.Security.EscapeOutput -- already-rendered inner blocks ?>
        </div>
    </div>
</section>
