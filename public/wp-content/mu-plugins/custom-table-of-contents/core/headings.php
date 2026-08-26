<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Scan raw post HTML for h1–h4 tags and return an ordered list of
 * [id, text, level], with deterministic, de-duplicated slug ids.
 *
 * Used both to build the Table of Contents block's links (block/render.php,
 * run against the raw post_content) and, via the the_content filter below,
 * to inject matching id="" attributes into the real heading tags in the
 * rendered output — so the block's #anchor links actually land somewhere.
 * Both call sites see the same headings in the same order, so the ids
 * generated in each pass line up without the two ever talking to each other.
 */
function mlptoc_extract_headings($content) {
    if (!$content || !preg_match_all('/<h([1-4])[^>]*>(.*?)<\/h\1>/is', $content, $matches, PREG_SET_ORDER)) {
        return array();
    }

    $seen     = array();
    $headings = array();

    foreach ($matches as $match) {
        $text = trim(wp_strip_all_tags($match[2]));
        if ($text === '') {
            continue;
        }

        $slug = sanitize_title($text);
        $id   = $slug;
        $n    = 2;
        while (isset($seen[$id])) {
            $id = $slug . '-' . $n;
            $n++;
        }
        $seen[$id] = true;

        $headings[] = array(
            'id'    => $id,
            'text'  => $text,
            'level' => (int) $match[1],
        );
    }

    return $headings;
}

add_filter('the_content', 'mlptoc_add_heading_ids', 20);
function mlptoc_add_heading_ids($content) {
    if (!is_singular() || !$content) {
        return $content;
    }

    $headings = mlptoc_extract_headings($content);
    if (!$headings) {
        return $content;
    }

    $i = 0;
    return preg_replace_callback('/<h([1-4])([^>]*)>/i', function ($m) use ($headings, &$i) {
        if (!isset($headings[$i])) {
            return $m[0];
        }
        $result = (stripos($m[2], 'id=') !== false)
            ? $m[0]
            : '<h' . $m[1] . $m[2] . ' id="' . esc_attr($headings[$i]['id']) . '">';
        $i++;
        return $result;
    }, $content);
}
