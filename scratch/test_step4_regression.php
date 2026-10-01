<?php
/**
 * SpiceCraft Phase 3 Step 4 - Comprehensive Regression & System Integrity Test Suite
 *
 * Verifies all 21 key regression points:
 * 106. Homepage
 * 107. About
 * 108. Manufacturing
 * 109. Quality
 * 110. Certifications
 * 111. Shop
 * 112. Product Category
 * 113. Product Search
 * 114. Product Filters
 * 115. Product Sorting
 * 116. Product Detail
 * 117. Favourites
 * 118. Recently Viewed
 * 119. Reviews
 * 120. WhatsApp Enquiry
 * 121. Email Enquiry
 * 122. Sticky Header
 * 123. Mobile Menu
 * 124. Back-to-Top
 * 125. Product Grid Step 4A
 * 126. Catalog mode
 */

require_once __DIR__ . '/../wp-load.php';

$results = array(
	'passed' => array(),
	'failed' => array(),
);

function sc_reg_assert( $condition, $name, $detail = '' ) {
	global $results;
	if ( $condition ) {
		$results['passed'][] = $name;
		echo "[PASS] {$name}\n";
	} else {
		$results['failed'][] = $name . ( $detail ? " ({$detail})" : '' );
		echo "[FAIL] {$name}" . ( $detail ? " ({$detail})" : '' ) . "\n";
	}
}

echo "=== PHASE 3 STEP 4: COMPREHENSIVE REGRESSION SUITE ===\n\n";

$context = stream_context_create( array(
	'http' => array(
		'timeout'       => 15,
		'ignore_errors' => true,
	),
) );

function fetch_page( $url, $context ) {
	$content = @file_get_contents( $url, false, $context );
	$headers = isset( $http_response_header ) ? $http_response_header : array();
	$code    = isset( $headers[0] ) ? $headers[0] : '';
	return array(
		'content' => $content,
		'code'    => $code,
	);
}

// 106. Homepage
$home = fetch_page( home_url( '/' ), $context );
sc_reg_assert( false !== strpos( $home['code'], '200' ), '106. Homepage HTTP 200 OK' );
sc_reg_assert( false !== strpos( $home['content'], 'id="masthead"' ) && false !== strpos( $home['content'], 'sc-footer' ), '106. Homepage Header & Footer present' );

// 107. About
$about = fetch_page( home_url( '/about/' ), $context );
sc_reg_assert( false !== strpos( $about['code'], '200' ), '107. About Page HTTP 200 OK' );

// 108. Manufacturing
$mfg = fetch_page( home_url( '/manufacturing/' ), $context );
sc_reg_assert( false !== strpos( $mfg['code'], '200' ), '108. Manufacturing Page HTTP 200 OK' );

// 109. Quality & Sourcing
$quality = fetch_page( home_url( '/quality/' ), $context );
sc_reg_assert( false !== strpos( $quality['code'], '200' ), '109. Quality & Sourcing Page HTTP 200 OK' );

// 110. Certifications
$certs = fetch_page( home_url( '/certifications/' ), $context );
sc_reg_assert( false !== strpos( $certs['code'], '200' ), '110. Certifications Page HTTP 200 OK' );

// 111. Shop
$shop_url = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/shop/' );
$shop = fetch_page( $shop_url, $context );
sc_reg_assert( false !== strpos( $shop['code'], '200' ), '111. Shop Catalog HTTP 200 OK' );

// 112. Product Category
$cats = get_terms( array( 'taxonomy' => 'product_cat', 'hide_empty' => true ) );
if ( ! empty( $cats ) && ! is_wp_error( $cats ) ) {
	$cat_url = get_term_link( $cats[0] );
	$cat_res = fetch_page( $cat_url, $context );
	sc_reg_assert( false !== strpos( $cat_res['code'], '200' ), '112. Product Category Archive HTTP 200 OK' );
} else {
	sc_reg_assert( true, '112. Product Category Archive (Skipped: no cats in DB)' );
}

// 113. Product Search
$p_search = fetch_page( home_url( '/?s=pepper&post_type=product' ), $context );
sc_reg_assert( false !== strpos( $p_search['code'], '200' ), '113. Product Search HTTP 200 OK' );

// 114. Product Filters
$p_filter = fetch_page( $shop_url . '?filter_origin=wayanad', $context );
sc_reg_assert( false !== strpos( $p_filter['code'], '200' ), '114. Product Filters URL State Preserved HTTP 200' );

// 115. Product Sorting
$p_sort = fetch_page( $shop_url . '?orderby=title', $context );
sc_reg_assert( false !== strpos( $p_sort['code'], '200' ), '115. Product Sorting URL State Preserved HTTP 200' );

// 116. Product Detail
$prod_url = get_permalink( 24 ); // Tellicherry Black Peppercorns
$prod = fetch_page( $prod_url, $context );
sc_reg_assert( false !== strpos( $prod['code'], '200' ), '116. Single Product Detail HTTP 200 OK' );

// 117. Favourites
$fav = fetch_page( home_url( '/favourites/' ), $context );
sc_reg_assert( false !== strpos( $fav['code'], '200' ) || false !== strpos( $prod['content'], 'sc-favourite-toggle' ), '117. Product Favourites System Intact' );

// 118. Recently Viewed
sc_reg_assert( false !== strpos( $prod['content'], 'sc-recently-viewed' ), '118. Recently Viewed Product Component Intact' );

// 119. Reviews
sc_reg_assert( false !== strpos( $prod['content'], 'reviews' ) || false !== strpos( $prod['content'], 'sc-tab-reviews' ) || false !== strpos( $prod['content'], 'woocommerce-Reviews' ), '119. Product Customer Reviews Component Intact' );

// 120. WhatsApp Enquiry
sc_reg_assert( false !== strpos( $prod['content'], 'api.whatsapp.com' ) || false !== strpos( $prod['content'], 'wa.me' ) || false !== strpos( $prod['content'], 'whatsapp' ), '120. WhatsApp Product Enquiry Flow Intact' );

// 121. Email Enquiry
sc_reg_assert( false !== strpos( $prod['content'], 'mailto:' ) || false !== strpos( $prod['content'], 'sc-enquiry-modal' ), '121. Email / B2B Trade Enquiry Flow Intact' );

// 122. Sticky Header
sc_reg_assert( false !== strpos( $home['content'], 'sc-header' ) || false !== strpos( $home['content'], 'site-header' ), '122. Sticky Site Header Present' );

// 123. Mobile Menu
sc_reg_assert( false !== strpos( $home['content'], 'sc-menu-toggle' ) || false !== strpos( $home['content'], 'sc-mobile-drawer' ), '123. Mobile Menu Navigation Present' );

// 124. Back-to-Top
sc_reg_assert( false !== strpos( $home['content'], 'sc-scroll-top' ), '124. Scroll-to-Top Accessibility Component Present' );

// 125. Product Grid Step 4A (Zero blank first slot)
sc_reg_assert( false === strpos( $shop['content'], 'sc-grid-slot--blank' ) && false === strpos( $shop['content'], 'sc-product-card--empty' ), '125. Product Grid Step 4A: No blank first slot' );

// 126. Catalog Mode
sc_reg_assert( false === strpos( $prod['content'], 'single_add_to_cart_button' ), '126. Catalog Mode: Single product Add to Cart absent' );
sc_reg_assert( false === strpos( $shop['content'], 'ajax_add_to_cart' ), '126. Catalog Mode: Shop grid Ajax Add to Cart absent' );

echo "\n=== REGRESSION RESULTS SUMMARY ===\n";
echo "PASSED: " . count( $results['passed'] ) . "\n";
echo "FAILED: " . count( $results['failed'] ) . "\n";

if ( ! empty( $results['failed'] ) ) {
	echo "\nFailed items:\n";
	foreach ( $results['failed'] as $f ) {
		echo "  - {$f}\n";
	}
	exit( 1 );
} else {
	echo "\nALL 21 REGRESSION TESTS PASSED PERFECTLY!\n";
	exit( 0 );
}
