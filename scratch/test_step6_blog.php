<?php
/**
 * Step 6 Blog / News & Articles CMS Verification & Test Suite
 */

require_once __DIR__ . '/../wp-load.php';

$_SERVER['SERVER_NAME'] = 'localhost';
$_SERVER['HTTP_HOST']   = 'localhost';

echo "====================================================\n";
echo "SPICECRAFT PHASE 3 - STEP 6: BLOG CMS TEST SUITE\n";
echo "====================================================\n";

$passes = 0;
$fails  = 0;

function assert_test( $condition, $message ) {
	global $passes, $fails;
	if ( $condition ) {
		echo " [PASS] " . $message . "\n";
		$passes++;
	} else {
		echo " [FAIL] " . $message . "\n";
		$fails++;
	}
}

// -----------------------------------------------------------------
// 1. DATA MODEL & HELPERS
// -----------------------------------------------------------------
echo "\n--- 1. Data Model & Helpers ---\n";

assert_test( function_exists( 'spicecraft_calculate_reading_time' ), 'spicecraft_calculate_reading_time exists' );
$sample_text = str_repeat( 'word ', 450 );
$calc_time   = spicecraft_calculate_reading_time( $sample_text );
assert_test( 3 === $calc_time, "Reading time for 450 words is 3 minutes (got: {$calc_time})" );

assert_test( function_exists( 'spicecraft_get_blog_options' ), 'spicecraft_get_blog_options exists' );
$options = spicecraft_get_blog_options();
assert_test( ! empty( $options['hero_title'] ), 'Hero title option exists' );

assert_test( function_exists( 'spicecraft_get_featured_posts' ), 'spicecraft_get_featured_posts exists' );
$featured = spicecraft_get_featured_posts( 2 );
assert_test( count( $featured ) >= 1, 'Found at least 1 featured post (count: ' . count( $featured ) . ')' );
if ( ! empty( $featured ) ) {
	$is_feat = get_post_meta( $featured[0]->ID, '_sc_post_is_featured', true );
	assert_test( '1' == $is_feat, "Post {$featured[0]->ID} has _sc_post_is_featured = 1" );
}

assert_test( function_exists( 'spicecraft_get_related_posts' ), 'spicecraft_get_related_posts exists' );
if ( ! empty( $featured ) ) {
	$related = spicecraft_get_related_posts( $featured[0]->ID, 3 );
	assert_test( count( $related ) >= 1, "Found related posts for {$featured[0]->ID} (count: " . count( $related ) . ")" );
	// Ensure current post is not among related
	$self_in_related = false;
	foreach ( $related as $r ) {
		if ( $r->ID === $featured[0]->ID ) {
			$self_in_related = true;
		}
	}
	assert_test( ! $self_in_related, "Current post is not present in its own related posts list" );
}

// -----------------------------------------------------------------
// 2. ADMIN CMS & SETTINGS
// -----------------------------------------------------------------
echo "\n--- 2. Admin CMS & Settings ---\n";

assert_test( class_exists( 'SpiceCraft_Blog_Meta' ), 'SpiceCraft_Blog_Meta class exists' );
assert_test( class_exists( 'SpiceCraft_Blog_Settings' ), 'SpiceCraft_Blog_Settings class exists' );

// Check that blog submenu is registered
wp_set_current_user( 1 );
require_once ABSPATH . 'wp-admin/includes/admin.php';
if ( class_exists( 'SpiceCraft_Global_Settings' ) ) {
	SpiceCraft_Global_Settings::get_instance()->register_admin_menu();
}
if ( class_exists( 'SpiceCraft_Blog_Settings' ) ) {
	SpiceCraft_Blog_Settings::get_instance()->register_admin_menu();
}
global $submenu;
$has_blog_menu = false;
$parent_keys = array( 'spicecraft-overview', 'spicecraft' );
foreach ( $parent_keys as $pkey ) {
	if ( isset( $submenu[ $pkey ] ) ) {
		foreach ( $submenu[ $pkey ] as $sub ) {
			if ( 'spicecraft-blog' === $sub[2] ) {
				$has_blog_menu = true;
				break 2;
			}
		}
	}
}
assert_test( $has_blog_menu, "Blog / News menu ('spicecraft-blog') registered under SpiceCraft parent menu" );

// Check categories
$terms = get_terms( array( 'taxonomy' => 'category', 'hide_empty' => false ) );
$found_slugs = wp_list_pluck( $terms, 'slug' );
assert_test( in_array( 'manufacturing-processing', $found_slugs ), 'Category manufacturing-processing exists' );
assert_test( in_array( 'quality-food-safety', $found_slugs ), 'Category quality-food-safety exists' );
assert_test( in_array( 'industry-insights', $found_slugs ), 'Category industry-insights exists' );
assert_test( in_array( 'sourcing-sustainability', $found_slugs ), 'Category sourcing-sustainability exists' );

// -----------------------------------------------------------------
// 3. FRONTEND /BLOG/ ARCHIVE RENDERING
// -----------------------------------------------------------------
echo "\n--- 3. Frontend /blog/ Archive Page ---\n";

$blog_page = get_page_by_path( 'blog' );
assert_test( ! empty( $blog_page ), "Page 'blog' exists in database" );

$theme_dir = get_template_directory();

// Simulate request to /blog/
$_SERVER['SERVER_NAME'] = 'localhost';
$_SERVER['HTTP_HOST']   = 'localhost';
$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['REQUEST_URI']    = '/blog/';
ob_start();
wp();
include $theme_dir . '/home.php';
$blog_html = ob_get_clean();

assert_test( ! empty( $blog_html ), '/blog/ template generated non-empty HTML' );
// Count <h1> tags
preg_match_all( '/<h1\b[^>]*>(.*?)<\/h1>/is', $blog_html, $h1_matches );
$h1_count = count( $h1_matches[0] );
assert_test( 1 === $h1_count, "Exactly one <h1> tag on /blog/ (found: {$h1_count})" );

assert_test( strpos( $blog_html, 'sc-blog-hero' ) !== false, '/blog/ contains hero container .sc-blog-hero' );
assert_test( strpos( $blog_html, 'sc-blog-featured-banner' ) !== false, '/blog/ contains featured banner .sc-blog-featured-banner' );
assert_test( strpos( $blog_html, 'sc-filter-pill' ) !== false, '/blog/ contains category filter pills .sc-filter-pill' );
assert_test( strpos( $blog_html, 'sc-blog-search' ) !== false, '/blog/ contains search form .sc-blog-search' );
assert_test( strpos( $blog_html, 'sc-article-card' ) !== false, '/blog/ contains article cards .sc-article-card' );
assert_test( strpos( $blog_html, 'sc-badge--category' ) !== false, '/blog/ cards contain category badges' );
assert_test( strpos( $blog_html, 'sc-article-card__reading-time' ) !== false, '/blog/ cards contain reading time' );
assert_test( strpos( $blog_html, 'sc-blog-bottom-cta' ) !== false, '/blog/ contains bottom lead CTA banner' );

// -----------------------------------------------------------------
// 4. ARTICLE DETAIL RENDERING (single-post.php)
// -----------------------------------------------------------------
echo "\n--- 4. Single Article Detail Page ---\n";

$test_post = get_posts( array( 'post_type' => 'post', 'posts_per_page' => 1 ) );
assert_test( ! empty( $test_post ), 'Found a published post to test' );

if ( ! empty( $test_post ) ) {
	$pid = $test_post[0]->ID;
	setup_postdata( $test_post[0] );
	global $post, $wp_query;
	$post = $test_post[0];
	$wp_query->is_single = true;
	$wp_query->is_singular = true;

	ob_start();
	include $theme_dir . '/single-post.php';
	$single_html = ob_get_clean();
	wp_reset_postdata();

	assert_test( ! empty( $single_html ), 'single-post.php generated non-empty HTML' );

	preg_match_all( '/<h1\b[^>]*>(.*?)<\/h1>/is', $single_html, $single_h1_matches );
	$single_h1_count = count( $single_h1_matches[0] );
	assert_test( 1 === $single_h1_count, "Exactly one <h1> tag on article detail (found: {$single_h1_count})" );

	assert_test( strpos( $single_html, 'sc-article-single__header' ) !== false, 'Single contains article header' );
	assert_test( strpos( $single_html, 'sc-article-single__deck' ) !== false || strpos( $single_html, 'sc-article-single__meta' ) !== false, 'Single contains article deck or meta' );
	assert_test( strpos( $single_html, 'sc-prose' ) !== false, 'Single contains .sc-prose entry content' );
	assert_test( strpos( $single_html, 'sc-article-share-card' ) !== false, 'Single contains social sharing card' );
	assert_test( strpos( $single_html, 'sc-share-btn--linkedin' ) !== false, 'Single contains LinkedIn share button' );
	assert_test( strpos( $single_html, 'sc-share-btn--whatsapp' ) !== false, 'Single contains WhatsApp share button' );
	assert_test( strpos( $single_html, 'sc-share-btn--copy' ) !== false, 'Single contains Copy Link button' );
	assert_test( strpos( $single_html, 'application/ld+json' ) !== false, 'Single outputs valid Schema.org application/ld+json' );
	assert_test( strpos( $single_html, 'BlogPosting' ) !== false || strpos( $single_html, 'Article' ) !== false, 'JSON-LD schema contains BlogPosting or Article type' );
	assert_test( strpos( $single_html, 'sc-article-related-section' ) !== false, 'Single contains related articles section' );
	assert_test( strpos( $single_html, 'sc-article-nav' ) !== false, 'Single contains previous/next navigation' );
}

// -----------------------------------------------------------------
// 5. CATEGORY ARCHIVE RENDERING
// -----------------------------------------------------------------
echo "\n--- 5. Category Archive Rendering ---\n";

$cat_term = get_term_by( 'slug', 'manufacturing-processing', 'category' );
assert_test( ! empty( $cat_term ), 'Found manufacturing-processing category' );

if ( ! empty( $cat_term ) ) {
	global $wp_query;
	query_posts( array( 'cat' => $cat_term->term_id, 'posts_per_page' => 5 ) );
	$wp_query->is_category = true;
	$wp_query->is_archive  = true;
	$wp_query->queried_object = $cat_term;
	$wp_query->queried_object_id = $cat_term->term_id;

	ob_start();
	include $theme_dir . '/category.php';
	$cat_html = ob_get_clean();
	wp_reset_query();

	assert_test( ! empty( $cat_html ), 'category.php generated non-empty HTML' );
	preg_match_all( '/<h1\b[^>]*>(.*?)<\/h1>/is', $cat_html, $cat_h1 );
	assert_test( 1 === count( $cat_h1[0] ), 'Exactly one <h1> on category archive' );
	assert_test( strpos( $cat_html, 'Manufacturing' ) !== false && strpos( $cat_html, 'Processing' ) !== false, 'Category archive contains category title' );
	assert_test( strpos( $cat_html, 'sc-article-card' ) !== false, 'Category archive renders article cards' );
}

// -----------------------------------------------------------------
// 6. ASSETS & ENQUEUE
// -----------------------------------------------------------------
echo "\n--- 6. Assets & Enqueue ---\n";

assert_test( file_exists( $theme_dir . '/assets/css/blog.css' ), 'blog.css file exists' );
assert_test( file_exists( $theme_dir . '/assets/js/blog.js' ), 'blog.js file exists' );

$enqueue_content = file_get_contents( $theme_dir . '/inc/enqueue.php' );
assert_test( strpos( $enqueue_content, 'spicecraft-blog' ) !== false, 'spicecraft-blog style and script are registered in inc/enqueue.php' );

// -----------------------------------------------------------------
// 7. NAVIGATION & MENUS
// -----------------------------------------------------------------
echo "\n--- 7. Navigation & Menus ---\n";

$primary = wp_get_nav_menu_object( 'primary-navigation' );
$p_items = wp_get_nav_menu_items( $primary->term_id );
$p_urls  = wp_list_pluck( $p_items, 'url' );
$has_p_blog = false;
foreach ( $p_urls as $u ) {
	if ( strpos( $u, '/blog/' ) !== false || strpos( $u, 'blog' ) !== false ) {
		$has_p_blog = true;
	}
}
assert_test( $has_p_blog, 'Primary Navigation contains link to /blog/' );

// -----------------------------------------------------------------
// SUMMARY
// -----------------------------------------------------------------
echo "\n====================================================\n";
echo "TEST RESULTS: {$passes} PASSED, {$fails} FAILED\n";
echo "====================================================\n";

if ( $fails > 0 ) {
	exit( 1 );
}
exit( 0 );
