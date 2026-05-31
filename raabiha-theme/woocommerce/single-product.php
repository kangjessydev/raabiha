<?php
/**
 * The Template for displaying all single products
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/single-product.php.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

get_header( 'shop' ); ?>

<main class="bg-[#fcf9f5] min-h-screen pt-24 pb-20">
    <div class="max-w-[1440px] mx-auto px-6 lg:px-12">
        <style>
        /* Override WooCommerce Default Layout for our Grid */
        .woocommerce #content div.product div.images, 
        .woocommerce div.product div.images, 
        .woocommerce-page #content div.product div.images, 
        .woocommerce-page div.product div.images,
        .woocommerce-product-gallery {
            width: 100% !important;
            float: none !important;
        }
        .woocommerce #content div.product div.summary, 
        .woocommerce div.product div.summary, 
        .woocommerce-page #content div.product div.summary, 
        .woocommerce-page div.product div.summary {
            width: 100% !important;
            float: none !important;
        }
        /* Hide default WooCommerce breadcrumb */
        .woocommerce-breadcrumb {
            display: none !important;
        }
        /* Add space after Rp currency symbol */
        .woocommerce-Price-currencySymbol {
            margin-right: 0.25rem;
        }
        /* Hide browser default number input arrows */
        input[type="number"]::-webkit-inner-spin-button,
        input[type="number"]::-webkit-outer-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }
        input[type="number"] {
            -moz-appearance: textfield;
        }
        
        /* Price Typography Override (Main & Variation) */
        .woocommerce div.product p.price,
        .woocommerce div.product span.price {
            color: #615e57 !important;
            font-family: ui-serif, Georgia, Cambria, "Times New Roman", Times, serif !important;
            font-size: 1.5rem !important;
            font-weight: normal !important;
            margin-bottom: 1.5rem !important;
            display: flex !important;
            flex-wrap: wrap;
            align-items: center;
            gap: 1rem;
        }
        .woocommerce-variation-price {
            display: none !important;
        }
        
        /* Constrain Product Gallery Image Height */
        .woocommerce-product-gallery__image img {
            max-height: 70vh !important;
            width: 100% !important;
            object-fit: cover !important;
        }

        /* Gallery Thumbnails Styling (Horizontal Scroll) */
        .woocommerce-product-gallery ol.flex-control-nav.flex-control-thumbs {
            display: flex !important;
            flex-wrap: nowrap !important;
            overflow-x: auto !important;
            gap: 1rem !important;
            margin-top: 1.5rem !important;
            padding: 0 !important; /* Removed scrollbar padding */
            scrollbar-width: none !important; /* Firefox hide scrollbar */
            -ms-overflow-style: none !important; /* IE hide scrollbar */
            scroll-behavior: smooth;
        }
        
        .woocommerce-product-gallery ol.flex-control-nav.flex-control-thumbs::-webkit-scrollbar {
            display: none !important; /* Chrome/Safari hide scrollbar */
        }

        .woocommerce-product-gallery ol.flex-control-nav.flex-control-thumbs li {
            float: none !important;
            flex: 0 0 calc(25% - 0.75rem) !important;
            min-width: 100px !important; /* Ensure they don't shrink too much */
            width: auto !important;
            margin: 0 !important;
            clear: none !important;
            position: relative !important;
        }

        .woocommerce-product-gallery ol.flex-control-nav.flex-control-thumbs li img {
            width: 100% !important;
            height: 100% !important;
            object-fit: cover !important;
            aspect-ratio: 1/1 !important; /* Square */
            cursor: pointer !important;
            opacity: 1 !important; /* Normal opacity */
            transition: all 0.3s ease !important;
            border: none !important;
            padding: 0 !important;
            background-color: transparent !important;
            display: block !important;
        }

        /* Transparent Emerald Overlay for Active/Hover State */
        .woocommerce-product-gallery ol.flex-control-nav.flex-control-thumbs li::after {
            content: "";
            position: absolute;
            inset: 0;
            background-color: rgba(9, 73, 59, 0.35); /* Emerald transparan */
            opacity: 0;
            transition: opacity 0.3s ease;
            pointer-events: none;
        }
        
        .woocommerce-product-gallery ol.flex-control-nav.flex-control-thumbs li:hover::after,
        .woocommerce-product-gallery ol.flex-control-nav.flex-control-thumbs li:has(img.flex-active)::after {
            opacity: 1;
        }

        @media (min-width: 768px) {
            .woocommerce div.product p.price,
            .woocommerce div.product span.price {
                font-size: 1.875rem !important;
            }
        }
        
        /* Discounted / Sale Price Style */
        .woocommerce div.product p.price del,
        .woocommerce div.product span.price del,
        .woocommerce-variation-price .price del {
            opacity: 0.5;
            font-size: 1.25rem !important;
            display: inline-block;
        }
        .woocommerce div.product p.price ins,
        .woocommerce div.product span.price ins,
        .woocommerce-variation-price .price ins {
            text-decoration: none !important;
            color: #09493B !important; /* Emerald green for sale price */
            font-weight: bold !important;
        }

        
        /* Custom Reviews UI Styling */
        #reviews h2.woocommerce-Reviews-title {
            display: none;
        }
        #reviews .noreviews {
            font-family: inherit;
            font-size: 14px;
            color: #1c1c1a;
            margin-bottom: 0.5rem;
            display: block;
        }
        #reviews .woocommerce-verification-required,
        #reviews .comment-reply-title {
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;
            font-size: 10px;
            color: #615e57;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            font-weight: normal;
            margin-bottom: 1.5rem;
            display: block;
        }
        #reviews .comment-reply-title a {
            display: none; /* Hide "Cancel reply" if it exists in the title */
        }
        #reviews .comment-form label {
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;
            font-size: 10px;
            color: #1c1c1a;
            text-transform: uppercase;
            letter-spacing: 0.2em;
            font-weight: bold;
            margin-bottom: 0.75rem;
            display: block;
        }
        #reviews .comment-form textarea,
        #reviews .comment-form input[type="text"],
        #reviews .comment-form input[type="email"],
        #reviews #respond input[type="text"],
        #reviews #respond input[type="email"],
        #reviews #respond textarea {
            width: 100% !important;
            border: 1px solid #e5e2de !important;
            background: transparent !important;
            padding: 1rem !important;
            font-size: 14px !important;
            color: #1c1c1a !important;
            outline: none !important;
            transition: border-color 0.2s !important;
            margin-bottom: 1.5rem !important;
            min-height: 120px !important;
            border-radius: 0 !important;
            box-shadow: none !important;
        }
        #reviews .comment-form textarea:focus,
        #reviews .comment-form input[type="text"]:focus,
        #reviews .comment-form input[type="email"]:focus,
        #reviews #respond input[type="text"]:focus,
        #reviews #respond input[type="email"]:focus,
        #reviews #respond textarea:focus {
            border-color: #1c1c1a !important;
        }
        #reviews .comment-form .submit,
        #reviews #respond input#submit,
        .woocommerce #review_form #respond .form-submit input {
            background-color: #09493B !important;
            color: white !important;
            border: none !important;
            border-radius: 0 !important;
            height: 3.5rem !important;
            line-height: 1 !important;
            padding: 0 2rem !important;
            font-size: 10px !important;
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace !important;
            font-weight: bold !important;
            text-transform: uppercase !important;
            letter-spacing: 0.2em !important;
            cursor: pointer !important;
            transition: background-color 0.2s !important;
            width: auto !important;
        }
        #reviews .comment-form .submit:hover,
        #reviews #respond input#submit:hover,
        .woocommerce #review_form #respond .form-submit input:hover {
            background-color: #07362c !important;
        }
        #reviews .stars {
            margin-bottom: 1.5rem;
        }
        #reviews .stars a {
            color: #1c1c1a;
        }
        #reviews .stars a:hover, #reviews .stars a.active {
            color: #09493B;
        }
        </style>
        <?php
        /**
         * woocommerce_before_main_content hook.
         */
        remove_action( 'woocommerce_before_main_content', 'woocommerce_breadcrumb', 20 );
        do_action( 'woocommerce_before_main_content' );
        ?>

        <?php while ( have_posts() ) : ?>
            <?php the_post(); ?>

            <?php wc_get_template_part( 'content', 'single-product' ); ?>

        <?php endwhile; // end of the loop. ?>

        <?php
        /**
         * woocommerce_after_main_content hook.
         */
        do_action( 'woocommerce_after_main_content' );
        ?>
    </div>
    
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        
        // Custom Gallery Thumbnail Navigation Arrows
        setTimeout(function() {
            const thumbNav = document.querySelector('.woocommerce-product-gallery ol.flex-control-nav.flex-control-thumbs');
            if (thumbNav) {
                // Wrap the thumbNav in a relative container
                const wrapper = document.createElement('div');
                wrapper.className = 'relative w-full group';
                wrapper.style.marginTop = '1.5rem';
                
                // Remove margin-top from original thumbNav since wrapper has it
                thumbNav.style.marginTop = '0';
                
                thumbNav.parentNode.insertBefore(wrapper, thumbNav);
                wrapper.appendChild(thumbNav);
                
                // Create Prev Button
                const prevBtn = document.createElement('div');
                prevBtn.className = 'absolute top-0 left-0 w-12 text-[#1c1c1a] flex items-center justify-center cursor-pointer z-10 transition-colors';
                prevBtn.style.backgroundColor = 'rgba(255, 255, 255, 0.7)';
                prevBtn.style.height = '100%';
                prevBtn.addEventListener('mouseenter', () => prevBtn.style.backgroundColor = 'rgba(255, 255, 255, 0.9)');
                prevBtn.addEventListener('mouseleave', () => prevBtn.style.backgroundColor = 'rgba(255, 255, 255, 0.7)');
                prevBtn.innerHTML = '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24" class="w-6 h-6"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>';
                
                // Create Next Button
                const nextBtn = document.createElement('div');
                nextBtn.className = 'absolute top-0 right-0 w-12 text-[#1c1c1a] flex items-center justify-center cursor-pointer z-10 transition-colors';
                nextBtn.style.backgroundColor = 'rgba(255, 255, 255, 0.7)';
                nextBtn.style.height = '100%';
                nextBtn.addEventListener('mouseenter', () => nextBtn.style.backgroundColor = 'rgba(255, 255, 255, 0.9)');
                nextBtn.addEventListener('mouseleave', () => nextBtn.style.backgroundColor = 'rgba(255, 255, 255, 0.7)');
                nextBtn.innerHTML = '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24" class="w-6 h-6"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>';
                
                wrapper.appendChild(prevBtn);
                wrapper.appendChild(nextBtn);
                
                // Scroll logic
                prevBtn.addEventListener('click', () => {
                    thumbNav.scrollBy({ left: -200, behavior: 'smooth' });
                });
                nextBtn.addEventListener('click', () => {
                    thumbNav.scrollBy({ left: 200, behavior: 'smooth' });
                });
                
                // Hide arrows if scroll not possible
                const updateArrows = () => {
                    prevBtn.style.display = thumbNav.scrollLeft > 0 ? 'flex' : 'none';
                    // Use a 5px buffer for rounding errors
                    nextBtn.style.display = thumbNav.scrollLeft < (thumbNav.scrollWidth - thumbNav.clientWidth - 5) ? 'flex' : 'none';
                };
                
                thumbNav.addEventListener('scroll', updateArrows);
                window.addEventListener('resize', updateArrows);
                updateArrows();
                
                setTimeout(updateArrows, 500);
            }
        }, 500);
    });
    </script>
</main>

<?php get_footer( 'shop' ); ?>
