<?php
/**
 * index.php — Raabiha Theme fallback template.
 *
 * Required by WordPress theme standards.
 *
 * @package RaabihaTheme
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>
<main id="primary" class="site-main max-w-7xl mx-auto px-6 py-12 min-h-screen">
    <?php
    if ( have_posts() ) :
        while ( have_posts() ) :
            the_post();
            ?>
            <article id="post-<?php the_ID(); ?>" <?php post_class('mb-12'); ?>>
                <header class="entry-header mb-6">
                    <?php
                    if ( is_singular() ) :
                        the_title( '<h1 class="text-3xl font-bold font-serif text-[#064e3b] tracking-wide mb-4">', '</h1>' );
                    else :
                        the_title( '<h2 class="text-2xl font-bold font-serif text-[#064e3b] tracking-wide mb-2"><a href="' . esc_url( get_permalink() ) . '" rel="bookmark">', '</a></h2>' );
                    endif;
                    ?>
                </header>

                <div class="entry-content prose prose-stone max-w-none text-[#444748]">
                    <?php
                    the_content();
                    
                    wp_link_pages(
                        array(
                            'before' => '<div class="page-links">' . esc_html__( 'Pages:', 'raabiha' ),
                            'after'  => '</div>',
                        )
                    );
                    ?>
                </div>
            </article>
            <?php
        endwhile;

        the_posts_navigation();

    else :
        ?>
        <div style="padding: 4rem 2rem; text-align: center; font-family: 'Inter', sans-serif; color: #1c1c1a;">
            <h1 style="font-size: 1.5rem; color: #064e3b; margin-bottom: 1rem; font-family: serif;">Not Found</h1>
            <p>It seems we can&rsquo;t find what you&rsquo;re looking for.</p>
        </div>
        <?php
    endif;
    ?>
</main>
<?php
get_footer();

