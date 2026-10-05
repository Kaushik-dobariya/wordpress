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

	// 4b. Lead & Product Enquiry Stylesheet (Global Modal & Form)
	$enquiry_css_ver = file_exists( SPICECRAFT_DIR . '/assets/css/enquiry.css' ) ? (string) filemtime( SPICECRAFT_DIR . '/assets/css/enquiry.css' ) : SPICECRAFT_VERSION;
	wp_enqueue_style(
		'spicecraft-enquiry',
		SPICECRAFT_URI . '/assets/css/enquiry.css',
		array( 'spicecraft-responsive' ),
		$enquiry_css_ver
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

	// 5b. Homepage Dedicated Stylesheet (Phase 2 Step 2)
	if ( is_front_page() ) {
		$home_css_ver = file_exists( SPICECRAFT_DIR . '/assets/css/home.css' ) ? (string) filemtime( SPICECRAFT_DIR . '/assets/css/home.css' ) : SPICECRAFT_VERSION;
		wp_enqueue_style(
			'spicecraft-home',
			SPICECRAFT_URI . '/assets/css/home.css',
			array( 'spicecraft-responsive' ),
			$home_css_ver
		);
	}

	// 5d. About Us Dedicated Stylesheet (Phase 3 Step 1)
	if ( is_page_template( 'page-about.php' ) || is_page( 'about' ) || is_page( 'about-us' ) ) {
		$about_css_ver = file_exists( SPICECRAFT_DIR . '/assets/css/about.css' ) ? (string) filemtime( SPICECRAFT_DIR . '/assets/css/about.css' ) : SPICECRAFT_VERSION;
		wp_enqueue_style(
			'spicecraft-about',
			SPICECRAFT_URI . '/assets/css/about.css',
			array( 'spicecraft-responsive' ),
			$about_css_ver
		);
	}

	// 5e. Manufacturing Dedicated Stylesheet (Phase 3 Step 2)
	if ( is_page_template( 'page-manufacturing.php' ) || is_page( 'manufacturing' ) ) {
		$mfg_css_ver = file_exists( SPICECRAFT_DIR . '/assets/css/manufacturing.css' ) ? (string) filemtime( SPICECRAFT_DIR . '/assets/css/manufacturing.css' ) : SPICECRAFT_VERSION;
		wp_enqueue_style(
			'spicecraft-manufacturing',
			SPICECRAFT_URI . '/assets/css/manufacturing.css',
			array( 'spicecraft-responsive' ),
			$mfg_css_ver
		);
	}

	// 5f. Quality & Sourcing Dedicated Stylesheet (Phase 3 Step 2)
	if ( is_page_template( 'page-quality.php' ) || is_page( 'quality' ) || is_page( 'quality-sourcing' ) ) {
		$quality_css_ver = file_exists( SPICECRAFT_DIR . '/assets/css/quality.css' ) ? (string) filemtime( SPICECRAFT_DIR . '/assets/css/quality.css' ) : SPICECRAFT_VERSION;
		wp_enqueue_style(
			'spicecraft-quality',
			SPICECRAFT_URI . '/assets/css/quality.css',
			array( 'spicecraft-responsive' ),
			$quality_css_ver
		);
	}

	// 5g. Certifications Dedicated Stylesheet (Phase 3 Step 3)
	if ( is_page_template( 'page-certifications.php' ) || is_page( 'certifications' ) || is_tax( 'spicecraft_certification' ) || is_singular( 'product' ) ) {
		$cert_css_ver = file_exists( SPICECRAFT_DIR . '/assets/css/certifications.css' ) ? (string) filemtime( SPICECRAFT_DIR . '/assets/css/certifications.css' ) : SPICECRAFT_VERSION;
		wp_enqueue_style(
			'spicecraft-certifications',
			SPICECRAFT_URI . '/assets/css/certifications.css',
			array( 'spicecraft-responsive' ),
			$cert_css_ver
		);
	}

	// 5h. Recipes Dedicated Stylesheet (Phase 3 Step 4)
	if ( is_post_type_archive( 'spicecraft_recipe' ) || is_tax( array( 'spicecraft_recipe_category', 'spicecraft_cuisine', 'spicecraft_meal_type' ) ) || is_singular( 'spicecraft_recipe' ) || is_singular( 'product' ) || is_front_page() || is_search() ) {
		$recipes_css_ver = file_exists( SPICECRAFT_DIR . '/assets/css/recipes.css' ) ? (string) filemtime( SPICECRAFT_DIR . '/assets/css/recipes.css' ) : SPICECRAFT_VERSION;
		wp_enqueue_style(
			'spicecraft-recipes',
			SPICECRAFT_URI . '/assets/css/recipes.css',
			array( 'spicecraft-responsive' ),
			$recipes_css_ver
		);
	}

	// 5i. Careers Dedicated Stylesheet & Script (Phase 3 Step 5)
	if ( is_post_type_archive( 'spicecraft_job' ) || is_singular( 'spicecraft_job' ) || is_page( 'careers' ) || is_page_template( 'page-careers.php' ) ) {
		$careers_css_ver = file_exists( SPICECRAFT_DIR . '/assets/css/careers.css' ) ? (string) filemtime( SPICECRAFT_DIR . '/assets/css/careers.css' ) : SPICECRAFT_VERSION;
		wp_enqueue_style(
			'spicecraft-careers',
			SPICECRAFT_URI . '/assets/css/careers.css',
			array( 'spicecraft-responsive' ),
			$careers_css_ver
		);

		$careers_js_ver = file_exists( SPICECRAFT_DIR . '/assets/js/careers.js' ) ? (string) filemtime( SPICECRAFT_DIR . '/assets/js/careers.js' ) : SPICECRAFT_VERSION;
		wp_enqueue_script(
			'spicecraft-careers',
			SPICECRAFT_URI . '/assets/js/careers.js',
			array(),
			$careers_js_ver,
			true
		);

		wp_localize_script(
			'spicecraft-careers',
			'spicecraftCareersConfig',
			array(
				'ajaxUrl' => esc_url( admin_url( 'admin-ajax.php' ) ),
				'nonce'   => wp_create_nonce( 'spicecraft_careers_nonce' ),
			)
		);
	}

	// 5j. Blog / News Dedicated Stylesheet & Script (Phase 3 Step 6)
	if ( is_home() || is_singular( 'post' ) || is_category() || is_tag() || is_page( 'blog' ) || is_page_template( 'page-blog.php' ) || is_front_page() ) {
		$blog_css_ver = file_exists( SPICECRAFT_DIR . '/assets/css/blog.css' ) ? (string) filemtime( SPICECRAFT_DIR . '/assets/css/blog.css' ) : SPICECRAFT_VERSION;
		wp_enqueue_style(
			'spicecraft-blog',
			SPICECRAFT_URI . '/assets/css/blog.css',
			array( 'spicecraft-responsive' ),
			$blog_css_ver
		);

		$blog_js_ver = file_exists( SPICECRAFT_DIR . '/assets/js/blog.js' ) ? (string) filemtime( SPICECRAFT_DIR . '/assets/js/blog.js' ) : SPICECRAFT_VERSION;
		wp_enqueue_script(
			'spicecraft-blog',
			SPICECRAFT_URI . '/assets/js/blog.js',
			array(),
			$blog_js_ver,
			true
		);
	}

	// 5k. Testimonials Dedicated Stylesheet & Script (Phase 3 Step 7)
	if ( is_post_type_archive( 'sc_testimonial' ) || is_singular( 'sc_testimonial' ) || is_page( 'testimonials' ) || is_page_template( 'page-testimonials.php' ) || is_front_page() ) {
		$test_css_ver = file_exists( SPICECRAFT_DIR . '/assets/css/testimonials.css' ) ? (string) filemtime( SPICECRAFT_DIR . '/assets/css/testimonials.css' ) : SPICECRAFT_VERSION;
		wp_enqueue_style(
			'spicecraft-testimonials',
			SPICECRAFT_URI . '/assets/css/testimonials.css',
			array( 'spicecraft-responsive' ),
			$test_css_ver
		);

		$test_js_ver = file_exists( SPICECRAFT_DIR . '/assets/js/testimonials.js' ) ? (string) filemtime( SPICECRAFT_DIR . '/assets/js/testimonials.js' ) : SPICECRAFT_VERSION;
		wp_enqueue_script(
			'spicecraft-testimonials',
			SPICECRAFT_URI . '/assets/js/testimonials.js',
			array(),
			$test_js_ver,
			true
		);
	}

	// 5l. Contact Us Dedicated Stylesheet (Phase 5 Contact Us Page)
	if ( is_page_template( 'page-contact.php' ) || is_page( 'contact' ) || is_page( 'contact-us' ) ) {
		$contact_css_ver = file_exists( SPICECRAFT_DIR . '/assets/css/contact.css' ) ? (string) filemtime( SPICECRAFT_DIR . '/assets/css/contact.css' ) : SPICECRAFT_VERSION;
		wp_enqueue_style(
			'spicecraft-contact',
			SPICECRAFT_URI . '/assets/css/contact.css',
			array( 'spicecraft-responsive', 'spicecraft-enquiry' ),
			$contact_css_ver
		);
	}

	// 5c. Product Discovery Stylesheet (Catalog filtering, chips, favourites, search, engagement)
	$disc_css_ver = file_exists( SPICECRAFT_DIR . '/assets/css/product-discovery.css' ) ? (string) filemtime( SPICECRAFT_DIR . '/assets/css/product-discovery.css' ) : SPICECRAFT_VERSION;
	wp_enqueue_style(
		'spicecraft-product-discovery',
		SPICECRAFT_URI . '/assets/css/product-discovery.css',
		array( 'spicecraft-responsive' ),
		$disc_css_ver
	);

	// 5m. Reusable Component Library Stylesheet (Phase 5.4)
	$comp_css_ver = file_exists( SPICECRAFT_DIR . '/assets/css/components.css' ) ? (string) filemtime( SPICECRAFT_DIR . '/assets/css/components.css' ) : SPICECRAFT_VERSION;
	wp_enqueue_style(
		'spicecraft-components',
		SPICECRAFT_URI . '/assets/css/components.css',
		array( 'spicecraft-responsive' ),
		$comp_css_ver
	);

	// 6. Main Interactive JavaScript (Mobile Menu, Submenus, Search, Accessibility)
	wp_enqueue_script(
		'spicecraft-main',
		SPICECRAFT_URI . '/assets/js/main.js',
		array(),
		$main_js_ver,
		true
	);

	// 6b. Product Discovery JavaScript (Favourites, Recently Viewed, Filter Drawer, Live Search, Share)
	$disc_js_ver = file_exists( SPICECRAFT_DIR . '/assets/js/product-discovery.js' ) ? (string) filemtime( SPICECRAFT_DIR . '/assets/js/product-discovery.js' ) : SPICECRAFT_VERSION;
	wp_enqueue_script(
		'spicecraft-product-discovery',
		SPICECRAFT_URI . '/assets/js/product-discovery.js',
		array( 'spicecraft-main' ),
		$disc_js_ver,
		true
	);

	// 6c. Recipes JavaScript (Archive filters, mobile drawer, ingredient checklist, share)
	if ( is_post_type_archive( 'spicecraft_recipe' ) || is_tax( array( 'spicecraft_recipe_category', 'spicecraft_cuisine', 'spicecraft_meal_type' ) ) || is_singular( 'spicecraft_recipe' ) ) {
		$recipes_js_ver = file_exists( SPICECRAFT_DIR . '/assets/js/recipes.js' ) ? (string) filemtime( SPICECRAFT_DIR . '/assets/js/recipes.js' ) : SPICECRAFT_VERSION;
		wp_enqueue_script(
			'spicecraft-recipes',
			SPICECRAFT_URI . '/assets/js/recipes.js',
			array( 'spicecraft-main' ),
			$recipes_js_ver,
			true
		);
	}

	// 6d. Product & Trade Enquiry Controller JavaScript
	$enquiry_js_ver = file_exists( SPICECRAFT_DIR . '/assets/js/enquiry.js' ) ? (string) filemtime( SPICECRAFT_DIR . '/assets/js/enquiry.js' ) : SPICECRAFT_VERSION;
	wp_enqueue_script(
		'spicecraft-enquiry',
		SPICECRAFT_URI . '/assets/js/enquiry.js',
		array( 'spicecraft-main' ),
		$enquiry_js_ver,
		true
	);

	wp_localize_script(
		'spicecraft-enquiry',
		'spicecraftEnquiryConfig',
		array(
			'ajaxUrl' => esc_url( admin_url( 'admin-ajax.php' ) ),
			'nonce'   => wp_create_nonce( 'spicecraft_enquiry_action' ),
			'i18n'    => array(
				'submitting'     => esc_html__( 'Submitting Enquiry...', 'spicecraft' ),
				'submit'         => esc_html__( 'Submit Enquiry', 'spicecraft' ),
				'successHeading' => esc_html__( 'Thank you for your enquiry!', 'spicecraft' ),
				'successMessage' => esc_html__( 'We have received your request and our spice specialist team will get back to you shortly.', 'spicecraft' ),
				'networkError'   => esc_html__( 'A network error occurred. Please try again or reach out on WhatsApp.', 'spicecraft' ),
				'invalidEmail'   => esc_html__( 'Please enter a valid email address.', 'spicecraft' ),
				'requiredFields' => esc_html__( 'Please fill in all required fields.', 'spicecraft' ),
			),
		)
	);

	// Localize script for secure AJAX, nonces, and global client settings
	wp_localize_script(
		'spicecraft-main',
		'spicecraftConfig',
		array(
			'ajaxUrl'        => esc_url( admin_url( 'admin-ajax.php' ) ),
			'nonce'          => wp_create_nonce( 'spicecraft_frontend_nonce' ),
			'siteName'       => get_bloginfo( 'name' ),
			'isWcActive'     => class_exists( 'WooCommerce' ),
			'favouritesUrl'  => function_exists( 'spicecraft_get_favourites_url' ) ? esc_url( spicecraft_get_favourites_url() ) : home_url( '/favourites/' ),
			'shopUrl'        => function_exists( 'wc_get_page_permalink' ) ? esc_url( wc_get_page_permalink( 'shop' ) ) : home_url( '/shop/' ),
			'i18n'           => array(
				'menuOpen'         => esc_html__( 'Open Navigation Menu', 'spicecraft' ),
				'menuClose'        => esc_html__( 'Close Navigation Menu', 'spicecraft' ),
				'copiedSuccess'    => esc_html__( 'Product link copied to clipboard!', 'spicecraft' ),
				'favouriteAdded'   => esc_html__( 'Added to favourites', 'spicecraft' ),
				'favouriteRemoved' => esc_html__( 'Removed from favourites', 'spicecraft' ),
				'selectPackSize'   => esc_html__( 'Please select a pack size above before submitting an enquiry.', 'spicecraft' ),
				'noLiveResults'    => esc_html__( 'No products found', 'spicecraft' ),
			),
		)
	);

	// 7. Comment Reply Script for accessible single post comment threading
	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}
}
add_action( 'wp_enqueue_scripts', 'spicecraft_scripts' );

/**
 * Render the Global Product & Trade Enquiry Modal in Footer.
 */
function spicecraft_render_enquiry_modal() {
	get_template_part( 'template-parts/components/enquiry-modal' );
}
add_action( 'wp_footer', 'spicecraft_render_enquiry_modal', 20 );

