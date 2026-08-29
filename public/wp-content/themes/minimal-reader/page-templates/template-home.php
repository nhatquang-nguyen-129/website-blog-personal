<?php
/**
 * Template Name: Blank Canvas
 * Description: A Page template with no automatic title, hero, or feed — just
 * this Page's own content, full width. Build the homepage entirely out of
 * blocks: core Paragraph/Heading for an intro, the core Categories block for
 * a category nav, a core Query Loop for a posts feed, and the Featured
 * Carousel block (from the custom-featured-carousel plugin) for a pinned-posts
 * spotlight — add, remove, and reorder them freely in the editor.
 */
if (!defined('ABSPATH')) {
    exit;
}

get_header();
?>

<main class="page-canvas">
    <?php
    while (have_posts()) :
        the_post();
        the_content();
    endwhile;
    ?>
</main>

<?php
get_footer();
