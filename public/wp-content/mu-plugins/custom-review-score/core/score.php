<?php
if (!defined('ABSPATH')) {
    exit;
}

define('MLRS_META_KEY', '_mlrs_score');
define('MLRS_LABEL_META_KEY', '_mlrs_label');
define('MLRS_BLOCK_NAME', 'minimal-reader/review-score');

/**
 * Stored as a string ('9.5'), not a number: '' unambiguously means "this
 * post has no score", whereas a registered number meta reads back as 0 when
 * unset — indistinguishable from an actual 0/10 score.
 */
add_action('init', 'mlrs_register_post_meta');
function mlrs_register_post_meta() {
    register_post_meta('post', MLRS_META_KEY, array(
        'type'              => 'string',
        'single'            => true,
        'show_in_rest'      => true,
        'sanitize_callback' => 'mlrs_sanitize_score',
        'auth_callback'     => function ($allowed, $meta_key, $post_id) {
            return current_user_can('edit_post', $post_id);
        },
    ));
}

function mlrs_sanitize_score($value) {
    if ($value === '' || $value === null || !is_numeric($value)) {
        return '';
    }
    return (string) max(0, min(10, round((float) $value, 1)));
}

/**
 * The post's score as a float, or null if it has none.
 *
 * A translation (custom-multilingual-post) is a clone of the original made
 * without its post meta, and ?lang= swaps that translation in on the
 * original's URL — so a translation without its own score falls back to
 * its group's original. A review's score is a verdict on the thing being
 * reviewed, not on the language it's written in.
 */
function mlrs_get_score($post_id) {
    $score = get_post_meta($post_id, MLRS_META_KEY, true);

    if ($score === '' && function_exists('mlp_get_original_post')) {
        $group = get_post_meta($post_id, '_ml_group', true);
        $original = $group ? mlp_get_original_post($group) : null;
        if ($original && (int) $original->ID !== (int) $post_id) {
            $score = get_post_meta($original->ID, MLRS_META_KEY, true);
        }
    }

    return is_numeric($score) ? (float) $score : null;
}

function mlrs_format_score($score) {
    $formatted = number_format((float) $score, 1, '.', '');
    return substr($formatted, -2) === '.0' ? substr($formatted, 0, -2) : $formatted;
}

/**
 * Score band slug, driving the badge/box color (.mlrs-tier--<slug>, styled
 * by the theme).
 */
function mlrs_score_tier($score) {
    if ($score >= 9) {
        return 'excellent';
    }
    if ($score >= 7) {
        return 'good';
    }
    if ($score >= 5) {
        return 'mixed';
    }
    return 'poor';
}

/**
 * The score badge shown on a post's Featured Carousel slide. Returns ''
 * for a post with no score — most posts aren't reviews — and for a
 * password-protected post the visitor hasn't unlocked, since core's
 * password gate only covers the_content(), not data read straight from
 * post meta like this.
 */
function mlrs_render_badge($post_id) {
    $post = get_post($post_id);
    if (!$post || post_password_required($post)) {
        return '';
    }

    $score = mlrs_get_score($post->ID);
    if ($score === null) {
        return '';
    }

    $formatted = mlrs_format_score($score);
    $label     = mlrs_get_label($post->ID);

    return sprintf(
        '<span class="mlrs-badge mlrs-tier--%1$s" title="%2$s">'
            . '<span class="mlrs-badge__label">%3$s</span>'
            . '<span class="mlrs-badge__score">%4$s<span class="mlrs-badge__max">/10</span></span>'
            . '</span>',
        esc_attr(mlrs_score_tier($score)),
        esc_attr($label . ': ' . $formatted . '/10'),
        esc_html($label),
        esc_html($formatted)
    );
}

/**
 * The block's label attribute ("Đánh giá game"), as copied into
 * MLRS_LABEL_META_KEY on save — the carousel can't read a block attribute without
 * parsing each post's content. A translation with no label of its own
 * falls back to its original's, same as mlrs_get_score(); a post with no
 * stored label gets the block's default.
 */
function mlrs_get_label($post_id) {
    $label = get_post_meta($post_id, MLRS_LABEL_META_KEY, true);

    if ($label === '' && function_exists('mlp_get_original_post')) {
        $group = get_post_meta($post_id, '_ml_group', true);
        $original = $group ? mlp_get_original_post($group) : null;
        if ($original && (int) $original->ID !== (int) $post_id) {
            $label = get_post_meta($original->ID, MLRS_LABEL_META_KEY, true);
        }
    }

    return $label !== '' ? $label : mlrs_default_label();
}

function mlrs_default_label() {
    $type = WP_Block_Type_Registry::get_instance()->get_registered(MLRS_BLOCK_NAME);
    return $type && isset($type->attributes['label']['default'])
        ? $type->attributes['label']['default']
        : __('Đánh giá', 'review-score');
}

/**
 * The first Review Score block in a parsed block tree (it may be nested
 * inside a Group/Columns), or null.
 */
function mlrs_find_block($blocks) {
    foreach ($blocks as $block) {
        if ($block['blockName'] === MLRS_BLOCK_NAME) {
            return $block;
        }
        if (!empty($block['innerBlocks'])) {
            $found = mlrs_find_block($block['innerBlocks']);
            if ($found) {
                return $found;
            }
        }
    }
    return null;
}

/**
 * Keeps the meta in step with the block, on every save:
 * - block removed → delete the score and label, so a badge never outlives
 *   the box that explains it;
 * - block present → copy its label attribute into meta for the badge.
 *
 * wp_after_insert_post, not save_post: on a REST save (the block editor)
 * save_post fires *before* meta is written, so a deleted score would be
 * immediately re-added; this one fires after.
 */
add_action('wp_after_insert_post', 'mlrs_sync_meta_with_block', 10, 2);
function mlrs_sync_meta_with_block($post_id, $post) {
    if ($post->post_type !== 'post' || wp_is_post_revision($post_id) || wp_is_post_autosave($post_id)) {
        return;
    }

    $block = has_block(MLRS_BLOCK_NAME, $post) ? mlrs_find_block(parse_blocks($post->post_content)) : null;

    if (!$block) {
        delete_post_meta($post_id, MLRS_META_KEY);
        delete_post_meta($post_id, MLRS_LABEL_META_KEY);
        return;
    }

    // A label left at its default isn't serialized into the block comment,
    // so it's absent from attrs — store nothing and let mlrs_get_label()
    // supply the default.
    $label = isset($block['attrs']['label']) ? trim(wp_strip_all_tags($block['attrs']['label'])) : '';
    if ($label !== '') {
        update_post_meta($post_id, MLRS_LABEL_META_KEY, $label);
    } else {
        delete_post_meta($post_id, MLRS_LABEL_META_KEY);
    }
}
