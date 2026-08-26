<?php
/**
 * Template Name: Substack Home
 * Description: A Substack-style homepage — a short intro (edit this Page's
 * content in wp-admin to change it), an auto-rotating hero carousel built
 * from your Sticky Posts (Posts > Edit > "Stick to the top of the blog" —
 * a native WordPress feature, no extra plugin), a category quick-nav, and a
 * feed of the latest posts. Assign this template to a Page, then set it as
 * the site's static homepage under Settings > Reading.
 */
if (!defined('ABSPATH')) {
    exit;
}

get_header();

while (have_posts()) :
    the_post();
    ?>

    <section class="home-hero">
        <div class="container container--narrow">
            <h1 class="home-hero__title"><?php bloginfo('name'); ?></h1>
            <?php if (get_bloginfo('description')) : ?>
                <p class="home-hero__tagline"><?php bloginfo('description'); ?></p>
            <?php endif; ?>

            <?php if (get_the_content()) : ?>
                <div class="home-hero__intro">
                    <?php the_content(); ?>
                </div>
            <?php endif; ?>
        </div>
    </section>

<?php endwhile; ?>

<?php
// Pinned = WordPress's native Sticky Posts feature (Posts > Edit > "Stick to
// the top of the blog"), so "which posts to pin" is managed entirely from
// the normal post-edit screen, no custom field/plugin needed.
$pinned_ids = get_option('sticky_posts');
$pinned_ids = is_array($pinned_ids) ? array_slice($pinned_ids, 0, 6) : array();

$pinned_posts = array();
if ($pinned_ids) {
    $pinned_query_args = array(
        'post_type'      => 'post',
        'post_status'    => 'publish',
        'post__in'       => $pinned_ids,
        'orderby'        => 'post__in',
        'posts_per_page' => count($pinned_ids),
    );
    if (function_exists('mlp_exclude_translations_meta_query')) {
        $pinned_query_args['meta_query'] = array(mlp_exclude_translations_meta_query());
    }
    $pinned_query = new WP_Query($pinned_query_args);
    $pinned_posts = $pinned_query->posts;
}
?>

<?php if ($pinned_posts) : ?>
    <section class="home-carousel" data-carousel<?php echo count($pinned_posts) > 1 ? ' data-autoplay="5000"' : ''; ?>>
        <div class="home-carousel__viewport">
            <div class="home-carousel__track" data-carousel-track>
                <?php foreach ($pinned_posts as $p) :
                    $has_image = has_post_thumbnail($p);
                    ?>
                    <a class="home-carousel__slide<?php echo $has_image ? ' has-image' : ''; ?>"
                       href="<?php echo esc_url(get_permalink($p)); ?>"
                       <?php if ($has_image) : ?>
                           style="background-image:url('<?php echo esc_url(get_the_post_thumbnail_url($p, 'large')); ?>')"
                       <?php endif; ?>
                    >
                        <span class="home-carousel__overlay">
                            <span class="home-carousel__label"><?php esc_html_e('Pinned', 'minimal-reader'); ?></span>
                            <span class="home-carousel__title"><?php echo esc_html(get_the_title($p)); ?></span>
                        </span>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>

        <?php if (count($pinned_posts) > 1) : ?>
            <div class="home-carousel__dots" data-carousel-dots>
                <?php foreach ($pinned_posts as $index => $p) : ?>
                    <button type="button" class="home-carousel__dot<?php echo $index === 0 ? ' is-active' : ''; ?>" data-carousel-goto="<?php echo esc_attr($index); ?>" aria-label="<?php echo esc_attr(sprintf(__('Go to slide %d', 'minimal-reader'), $index + 1)); ?>"></button>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>
<?php endif; ?>

<main class="container container--narrow">
    <?php
    $categories = get_categories(array('hide_empty' => true));
    if ($categories) :
        ?>
        <nav class="home-cat-nav" aria-label="<?php esc_attr_e('Browse by category', 'minimal-reader'); ?>">
            <a class="home-cat-nav__item is-active" href="<?php echo esc_url(home_url('/')); ?>"><?php esc_html_e('Latest', 'minimal-reader'); ?></a>
            <?php foreach ($categories as $cat) : ?>
                <a class="home-cat-nav__item" href="<?php echo esc_url(get_category_link($cat)); ?>"><?php echo esc_html($cat->name); ?></a>
            <?php endforeach; ?>
        </nav>
    <?php endif; ?>

    <?php
    $blog_page_id = (int) get_option('page_for_posts');
    $query_args   = array(
        'post_type'      => 'post',
        'post_status'    => 'publish',
        'posts_per_page' => 6,
        'orderby'        => 'date',
        'order'          => 'DESC',
    );
    // Pinned posts already have their spotlight in the carousel above.
    if ($pinned_ids) {
        $query_args['post__not_in'] = $pinned_ids;
    }
    // Skip translation posts so a group only ever shows once in the feed —
    // provided by the custom-multilingual-post mu-plugin (core/language-resolver.php).
    if (function_exists('mlp_exclude_translations_meta_query')) {
        $query_args['meta_query'] = array(mlp_exclude_translations_meta_query());
    }
    $latest = new WP_Query($query_args);
    ?>

    <?php if ($latest->have_posts()) : ?>
        <div class="home-feed">
            <?php while ($latest->have_posts()) : $latest->the_post(); ?>
                <article class="home-feed__item">
                    <h2 class="home-feed__title">
                        <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                    </h2>
                    <div class="home-feed__meta"><?php echo esc_html(get_the_date()); ?></div>
                    <div class="home-feed__excerpt"><?php the_excerpt(); ?></div>
                </article>
            <?php endwhile; ?>
        </div>

        <?php if ($blog_page_id) : ?>
            <p class="home-feed__more">
                <a href="<?php echo esc_url(get_permalink($blog_page_id)); ?>">
                    <?php esc_html_e('View all posts →', 'minimal-reader'); ?>
                </a>
            </p>
        <?php endif; ?>

        <?php wp_reset_postdata(); ?>
    <?php elseif (!$pinned_posts) : ?>
        <p><?php esc_html_e('Nothing published yet.', 'minimal-reader'); ?></p>
    <?php endif; ?>
</main>

<?php
get_footer();
