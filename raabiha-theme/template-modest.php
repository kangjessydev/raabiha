<?php
/**
 * Template Name: Modest Canvas (Blank)
 *
 * A completely blank canvas template without the default Raabiha header and footer.
 * This is designed to be used with the Raabiha Shortcode Components for edge-to-edge
 * modern landing pages like the "Architectural Modesty" design.
 */

get_header();
?>

    <main class="w-full min-h-screen">
        <?php
        // Render Gutenberg Content (Which will contain our blocks)
        if ( have_posts() ) :
            while ( have_posts() ) : the_post();
                the_content();
            endwhile;
        endif;
        ?>
    </main>

<?php
get_footer();
?>
