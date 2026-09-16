<?php
/**
 * SpiceCraft Theme Setup & Core Configuration
 *
 * @package SpiceCraft
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'spicecraft_setup' ) ) :
	/**
	 * Sets up theme defaults and registers support for various WordPress features.
	 */
	function spicecraft_setup() {
		/*
		 * Make theme available for translation.
		 * Translations can be filed in the /languages/ directory.
		 */
		load_theme_textdomain( 'spicecraft', SPICECRAFT_DIR . '/languages' );

		// Add default posts and comments RSS feed links to head.
		add_theme_support( 'automatic-feed-links' );

		/*
		 * Let WordPress manage the document title.
		 * By adding theme support, we declare that this theme does not use a
		 * hard-coded <title> tag in the document head, and expect WordPress to
		 * provide it.
		 */
		add_theme_support( 'title-tag' );

		/*
		 * Enable support for Post Thumbnails on posts and pages.
		 */
		add_theme_support( 'post-thumbnails' );

		// Custom Image Sizes for FMCG Catalog & Responsive Presentation
		add_image_size( 'spicecraft-catalog-card', 600, 600, true );    // 1:1 crisp square product catalog image
		add_image_size( 'spicecraft-catalog-zoom', 1200, 1200, false );  // High-res detail zoom
		add_image_size( 'spicecraft-hero-banner', 1920, 800, true );     // Hero slider / banner presentation
		add_image_size( 'spicecraft-blog-thumb', 800, 500, true );      // Recipe / blog post presentation

		/*
		 * Register Navigation Menus.
		 * Supports structured navigation without hardcoding menu items.
		 */
		register_nav_menus(
			array(
				'primary'  => esc_html__( 'Primary Menu', 'spicecraft' ),
				'mobile'   => esc_html__( 'Mobile Menu', 'spicecraft' ),
				'footer_1' => esc_html__( 'Footer Menu 1', 'spicecraft' ),
				'footer_2' => esc_html__( 'Footer Menu 2', 'spicecraft' ),
			)
		);

		/*
		 * Switch default core markup for search form, comment form, and comments
		 * to output valid HTML5.
		 */
		add_theme_support(
			'html5',
			array(
				'search-form',
				'comment-form',
				'comment-list',
				'gallery',
				'caption',
				'style',
				'script',
				'navigation-widgets',
			)
		);

		/*
		 * Enable support for Custom Logo.
		 */
		add_theme_support(
			'custom-logo',
			array(
				'height'      => 90,
				'width'       => 280,
				'flex-width'  => true,
				'flex-height' => true,
				'unlink-homepage-logo' => false,
			)
		);

		// Add theme support for selective refresh for widgets.
		add_theme_support( 'customize-selective-refresh-widgets' );

		// Add support for responsive embedded content.
		add_theme_support( 'responsive-embeds' );

		// Support Gutenberg wide and full alignment.
		add_theme_support( 'align-wide' );

		// Add editor styles support.
		add_theme_support( 'editor-styles' );
	}
endif;
add_action( 'after_setup_theme', 'spicecraft_setup' );

/**
 * Set the content width in pixels, based on the theme's design and stylesheet.
 * Priority 0 to make it available to lower priority callbacks.
 *
 * @global int $content_width
 */
function spicecraft_content_width() {
	$GLOBALS['content_width'] = apply_filters( 'spicecraft_content_width', 1280 );
}
add_action( 'after_setup_theme', 'spicecraft_content_width', 0 );
