<?php
/**
 * SpiceCraft WooCommerce Integration & Theme Setup
 *
 * Configures WooCommerce theme supports, gallery features, product loops,
 * single product wrappers, ratings/reviews preservation, and breadcrumbs.
 *
 * @package SpiceCraft
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Declare Theme Support for WooCommerce & Gallery Enhancements.
 */
function spicecraft_woocommerce_setup() {
	add_theme_support(
		'woocommerce',
		array(
			'thumbnail_image_width' => 600,
			'single_image_width'    => 1000,
			'product_grid'          => array(
				'default_rows'    => 4,
				'min_rows'        => 2,
				'max_rows'        => 8,
				'default_columns' => 3,
				'min_columns'     => 1,
				'max_columns'     => 4,
			),
		)
	);

	// Gallery features: Zoom, Lightbox, and Slider
	add_theme_support( 'wc-product-gallery-zoom' );
	add_theme_support( 'wc-product-gallery-lightbox' );
	add_theme_support( 'wc-product-gallery-slider' );
}
add_action( 'after_setup_theme', 'spicecraft_woocommerce_setup' );

// Safety Guard: Stop execution of WooCommerce-specific hooks if WooCommerce is not active.
if ( ! class_exists( 'WooCommerce' ) ) {
	return;
}

/**
 * ============================================================================
 * 1. LAYOUT & TEMPLATE WRAPPERS
 * ============================================================================
 */

/**
 * Before WooCommerce Shop / Single Product Content Wrapper.
 */
function spicecraft_woocommerce_wrapper_before() {
	?>
	<div class="sc-container sc-woocommerce-container">
		<div class="sc-woocommerce-content">
	<?php
}
remove_action( 'woocommerce_before_main_content', 'woocommerce_output_content_wrapper', 10 );
add_action( 'woocommerce_before_main_content', 'spicecraft_woocommerce_wrapper_before', 10 );

/**
 * After WooCommerce Shop / Single Product Content Wrapper.
 */
function spicecraft_woocommerce_wrapper_after() {
	?>
		</div><!-- .sc-woocommerce-content -->
	</div><!-- .sc-container -->
	<?php
}
remove_action( 'woocommerce_after_main_content', 'woocommerce_output_content_wrapper_end', 10 );
add_action( 'woocommerce_after_main_content', 'spicecraft_woocommerce_wrapper_after', 10 );

// Remove default result count and catalog ordering from loop start to avoid duplication
// (they are integrated into our custom .sc-catalog-controls header in archive-product.php)
remove_action( 'woocommerce_before_shop_loop', 'woocommerce_result_count', 20 );
remove_action( 'woocommerce_before_shop_loop', 'woocommerce_catalog_ordering', 30 );

// Remove automatic breadcrumb from woocommerce_before_main_content to prevent duplicate display
// (breadcrumbs are explicitly positioned inside our custom template containers)
remove_action( 'woocommerce_before_main_content', 'woocommerce_breadcrumb', 20 );

// Remove unwanted sidebar (search, pages, archives, categories) from product pages
remove_action( 'woocommerce_sidebar', 'woocommerce_get_sidebar', 10 );

/**
 * ============================================================================
 * 2. RATINGS & REVIEWS PRESERVATION
 * ============================================================================
 * Confirm WooCommerce native reviews and star ratings remain active.
 * WooCommerce naturally attaches:
 * - woocommerce_template_single_rating (priority 10) in single_product_summary
 * - woocommerce_template_loop_rating (priority 5) in after_shop_loop_item_title
 */
function spicecraft_ensure_reviews_support() {
	// Re-verify single product ratings hook is attached
	if ( ! has_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_rating' ) ) {
		add_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_rating', 10 );
	}

	// Re-verify loop rating hook is attached
	if ( ! has_action( 'woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_rating' ) ) {
		add_action( 'woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_rating', 5 );
	}
}
add_action( 'init', 'spicecraft_ensure_reviews_support' );

/**
 * ============================================================================
 * 3. BREADCRUMBS & NAVIGATION
 * ============================================================================
 */

/**
 * Filter WooCommerce breadcrumb defaults for clean semantic markup.
 *
 * @param array $defaults Breadcrumb parameters.
 * @return array
 */
function spicecraft_woocommerce_breadcrumbs( $defaults ) {
	$defaults['delimiter']   = '<span class="sc-breadcrumb__sep" aria-hidden="true"> / </span>';
	$defaults['wrap_before'] = '<nav class="sc-breadcrumb" aria-label="' . esc_attr__( 'Breadcrumb', 'spicecraft' ) . '">';
	$defaults['wrap_after']  = '</nav>';
	$defaults['before']      = '<span class="sc-breadcrumb__item">';
	$defaults['after']       = '</span>';
	return $defaults;
}
add_filter( 'woocommerce_breadcrumb_defaults', 'spicecraft_woocommerce_breadcrumbs' );
