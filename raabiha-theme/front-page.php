<?php
/**
 * Front Page Template
 *
 * This file serves as the default homepage for the Raabiha theme.
 * It currently hardcodes the homepage layout using the theme's shortcodes
 * for development and testing purposes.
 *
 * @package RaabihaTheme
 */

get_header();
?>

<main class="w-full min-h-screen">
    <?php
    // Render the exact layout from the Figma design using shortcodes
    echo do_shortcode('[raabiha_hero title="Architectural<br>Modesty" drop="DROP 02 // 2026"]');
    echo do_shortcode('[raabiha_marquee texts="New Collection, Modest Fashion, Free Shipping, Reseller Open"]');
    echo do_shortcode('[raabiha_categories title="CATEGORIES"]');
    echo do_shortcode('[raabiha_products title="NEW ARRIVALS" limit="4"]');
    echo do_shortcode('[raabiha_lookbook title="Urban<br>Sanctuary" id="04"]');
    ?>
</main>

<?php
get_footer();
?>
