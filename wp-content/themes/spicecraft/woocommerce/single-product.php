<?php
/**
 * SpiceCraft - Custom Single Product Shell Template
 *
 * Overrides WooCommerce's default single-product.php to cleanly house
 * content-single-product.php without unwanted legacy blog sidebars (search,
 * pages, archives, categories) and without duplicate breadcrumbs.
 *
 * @package SpiceCraft
 * @version 1.6.4
 */

defined( 'ABSPATH' ) || exit;

get_header( 'shop' );

/**
 * Hook: woocommerce_before_main_content.
 *
 * @hooked spicecraft_woocommerce_wrapper_before - 10
 */
do_action( 'woocommerce_before_main_content' );

while ( have_posts() ) :
	the_post();
	wc_get_template_part( 'content', 'single-product' );
endwhile;

/**
 * Hook: woocommerce_after_main_content.
 *
 * @hooked spicecraft_woocommerce_wrapper_after - 10
 */
do_action( 'woocommerce_after_main_content' );

// Note: woocommerce_sidebar hook is intentionally omitted here to prevent
// unwanted legacy widgets (search bar, pages, archives, categories) below related products.

get_footer( 'shop' );
