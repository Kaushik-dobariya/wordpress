<?php
/**
 * SpiceCraft Enqueue Architecture
 *
 * Cleanly handles styles, fonts, and scripts using official WordPress APIs.
 *
 * @package SpiceCraft
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Add preconnect resource hints for external typography assets.
 *
 * @param array  $urls          URLs to print for resource hints.
 * @param string $relation_type The relation type the URLs are printed for.
 * @return array
 */
function spicecraft_resource_hints( $urls, $relation_type ) {
	if ( wp_dependencies_unique_slug() && 'preconnect' === $relation_type ) {
		$urls[] = array(
			'href' => 'https://fonts.googleapis.com',
		);
		$urls[] = array(
			'href'        => 'https://fonts.gstatic.com',
			'crossorigin' => 'anonymous',
		);
	}
	return $urls;
}
add_filter( 'wp_resource_hints', 'spicecraft_resource_hints', 10, 2 );

/**
 * Helper to ensure helper function exists safely across all WP versions.
 */
function wp_dependencies_unique_slug() {
	return true;
}

/**
 * Enqueue scripts and styles with development file modification timestamps.
 */
function spicecraft_scripts() {
	// Versioning strategy: Use filemtime for development cache-busting
	$style_ver      = file_exists( SPICECRAFT_DIR . '/style.css' ) ? (string) filemtime( SPICECRAFT_DIR . '/style.css' ) : SPICECRAFT_VERSION;
	$main_css_ver   = file_exists( SPICECRAFT_DIR . '/assets/css/main.css' ) ? (string) filemtime( SPICECRAFT_DIR . '/assets/css/main.css' ) : SPICECRAFT_VERSION;
	$resp_css_ver   = file_exists( SPICECRAFT_DIR . '/assets/css/responsive.css' ) ? (string) filemtime( SPICECRAFT_DIR . '/assets/css/responsive.css' ) : SPICECRAFT_VERSION;
	$wc_css_ver     = file_exists( SPICECRAFT_DIR . '/assets/css/woocommerce.css' ) ? (string) filemtime( SPICECRAFT_DIR . '/assets/css/woocommerce.css' ) : SPICECRAFT_VERSION;
	$main_js_ver    = file_exists( SPICECRAFT_DIR . '/assets/js/main.js' ) ? (string) filemtime( SPICECRAFT_DIR . '/assets/js/main.js' ) : SPICECRAFT_VERSION;

	// 1. Google Fonts: Plus Jakarta Sans (Modern Clean Sans) & Cormorant Garamond (Artisanal Heritage Serif)
	wp_enqueue_style(
		'spicecraft-fonts',
		'https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,500;0,600;0,700;1,400&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap',
		array(),
		null
	);

	// 2. Base Theme Stylesheet (Metadata + Tokens + Resets)
	wp_enqueue_style(
		'spicecraft-style',
		get_stylesheet_uri(),
		array( 'spicecraft-fonts' ),
		$style_ver
	);

	// 3. Main Component & Layout Stylesheet
	wp_enqueue_style(
		'spicecraft-main',
		SPICECRAFT_URI . '/assets/css/main.css',
		array( 'spicecraft-style' ),
		$main_css_ver
	);

	// 4. Responsive Foundation Stylesheet (Mobile-first scale: 320px to 1440px+)
	wp_enqueue_style(
		'spicecraft-responsive',
		SPICECRAFT_URI . '/assets/css/responsive.css',
		array( 'spicecraft-main' ),
		$resp_css_ver
	);

	// 5. WooCommerce Catalog Stylesheet (Loaded when WooCommerce is active or on catalog pages)
	if ( class_exists( 'WooCommerce' ) || is_singular( 'product' ) || is_post_type_archive( 'product' ) || is_tax( array( 'product_cat', 'product_tag' ) ) ) {
		wp_enqueue_style(
			'spicecraft-woocommerce',
			SPICECRAFT_URI . '/assets/css/woocommerce.css',
			array( 'spicecraft-responsive' ),
			$wc_css_ver
		);
	}

	// 6. Main Interactive JavaScript (Mobile Menu, Submenus, Search, Accessibility)
	wp_enqueue_script(
		'spicecraft-main',
		SPICECRAFT_URI . '/assets/js/main.js',
		array(),
		$main_js_ver,
		true
	);

	// 6. Localize script for secure AJAX, nonces, and global client settings
	wp_localize_script(
		'spicecraft-main',
		'spicecraftConfig',
		array(
			'ajaxUrl'    => esc_url( admin_url( 'admin-ajax.php' ) ),
			'nonce'      => wp_create_nonce( 'spicecraft_frontend_nonce' ),
			'siteName'   => get_bloginfo( 'name' ),
			'isWcActive' => class_exists( 'WooCommerce' ),
			'i18n'       => array(
				'menuOpen'      => esc_html__( 'Open Navigation Menu', 'spicecraft' ),
				'menuClose'     => esc_html__( 'Close Navigation Menu', 'spicecraft' ),
				'copiedSuccess' => esc_html__( 'Link copied to clipboard!', 'spicecraft' ),
			),
		)
	);

	// 7. Comment Reply Script for accessible single post comment threading
	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}
}
add_action( 'wp_enqueue_scripts', 'spicecraft_scripts' );
