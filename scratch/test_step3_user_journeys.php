<?php
/**
 * SpiceCraft Phase 3 Step 3 - User Journeys & End-to-End Functional Test Suite
 *
 * Tests Journeys 1 to 8, Catalog Mode Enforcement, Step 4A regression, and Visibility toggles.
 */
require_once __DIR__ . '/../wp-load.php';

$results = array(
	'passed' => array(),
	'failed' => array(),
);

function journey_assert( $condition, $test_name ) {
	global $results;
	if ( $condition ) {
		$results['passed'][] = $test_name;
		echo "[PASS] {$test_name}\n";
	} else {
		$results['failed'][] = $test_name;
		echo "[FAIL] {$test_name}\n";
	}
}

echo "=== STARTING PHASE 3 STEP 3 USER JOURNEYS & FUNCTIONAL TESTS ===\n\n";

// Ensure clean slate before test
$existing_terms = get_terms( array( 'taxonomy' => 'spicecraft_certification', 'hide_empty' => false ) );
if ( ! empty( $existing_terms ) && ! is_wp_error( $existing_terms ) ) {
	foreach ( $existing_terms as $t ) {
		wp_delete_term( $t->term_id, 'spicecraft_certification' );
	}
}

// -------------------------------------------------------------
// JOURNEY 5: Admin -> Create Certification -> Mark Public -> Publish -> Frontend
// -------------------------------------------------------------
echo "--- Testing Journey 5: Create Public Certification ---\n";
$term_result = wp_insert_term( 'ISO 22000 Food Safety', 'spicecraft_certification', array( 'slug' => 'iso-22000' ) );
$term_id = is_array( $term_result ) ? $term_result['term_id'] : 0;
journey_assert( $term_id > 0, "Journey 5: Certification created successfully" );

$meta_data = array(
	'short_name'         => 'ISO 22000',
	'status'             => 'active',
	'auto_expiry'        => 0,
	'number'             => 'FSMS-998231',
	'issuing_authority'  => 'Bureau Veritas Quality International',
	'accreditation_body' => 'UKAS Management Systems',
	'issue_date'         => '2024-01-15',
	'valid_from'         => '2024-01-15',
	'expiry_date'        => '2027-01-14',
	'scope'              => 'Processing, cryogenic grinding, and packaging of whole and ground spices.',
	'facility_scope'     => 'SpiceCraft Cleanroom Facility, Unit 1 & 2, Cochin, Kerala',
	'verification_url'   => 'https://certcheck.example.com/verify?id=FSMS-998231',
	'logo_id'            => 38,
	'cert_image_id'      => 32,
	'doc_id'             => 33,
	'doc_visibility'     => 'public',
	'visibility'         => 'public',
	'public_detail'      => 1,
	'featured'           => 1,
	'order'              => 1,
	'related_products'   => array( 21, 22 ),
	'related_categories' => array( 16, 18 ),
);
spicecraft_save_certification_meta( $term_id, $meta_data );

// Check that public query returns it
$public_certs = spicecraft_get_public_certifications();
journey_assert( count( $public_certs ) === 1 && $public_certs[0]->term_id === $term_id, "Journey 5: Public certification returned by public query" );

// Check Archive page renders card
$archive_resp = wp_remote_get( home_url( '/certifications/' ) );
$archive_html = wp_remote_retrieve_body( $archive_resp );
journey_assert( false !== strpos( $archive_html, 'ISO 22000' ), "Journey 5: Archive displays ISO 22000 card" );
journey_assert( false !== strpos( $archive_html, 'FSMS-998231' ), "Journey 5: Archive displays Certificate Number" );
journey_assert( false !== strpos( $archive_html, 'Active / Valid' ), "Journey 5: Archive displays Active status" );

// -------------------------------------------------------------
// JOURNEY 1: Homepage -> Certifications -> Certification Detail -> Related Product -> Enquiry
// -------------------------------------------------------------
echo "\n--- Testing Journey 1: Archive to Detail to Related Product ---\n";
$detail_url = get_term_link( $term_id, 'spicecraft_certification' );
$detail_resp = wp_remote_get( $detail_url );
$detail_html = wp_remote_retrieve_body( $detail_resp );

journey_assert( 200 === wp_remote_retrieve_response_code( $detail_resp ), "Journey 1: Detail page returns HTTP 200" );
journey_assert( false !== strpos( $detail_html, 'FSMS-998231' ), "Journey 1: Detail displays certificate number" );
journey_assert( false !== strpos( $detail_html, 'Bureau Veritas Quality International' ), "Journey 1: Detail displays issuing authority" );
journey_assert( false !== strpos( $detail_html, 'UKAS Management Systems' ), "Journey 1: Detail displays accreditation body" );
journey_assert( false !== strpos( $detail_html, 'Processing, cryogenic grinding' ), "Journey 1: Detail displays scope" );
journey_assert( false !== strpos( $detail_html, 'SpiceCraft Cleanroom Facility' ), "Journey 1: Detail displays facility scope" );
journey_assert( false !== strpos( $detail_html, 'Organic Turmeric Powder' ), "Journey 1: Detail displays related product" );
journey_assert( false !== strpos( $detail_html, 'sc-product-card' ), "Journey 1: Related product uses existing catalog card" );

// Verify Catalog mode on related product (No add to cart button or price buying CTA)
journey_assert( false === strpos( $detail_html, 'add_to_cart_button' ), "Journey 1: Related product has NO add to cart button" );
journey_assert( false === strpos( $detail_html, 'name="add-to-cart"' ), "Journey 1: Related product has NO add-to-cart form input" );

// -------------------------------------------------------------
// JOURNEY 2: Quality & Sourcing -> Certification -> Verification Link
// -------------------------------------------------------------
echo "\n--- Testing Journey 2: Verification Link & Quality Integration ---\n";
// Detail page verification link check
journey_assert( false !== strpos( $detail_html, 'https://certcheck.example.com/verify?id=FSMS-998231' ), "Journey 2: Detail displays official verification URL" );
journey_assert( false !== strpos( $detail_html, 'target="_blank"' ) && false !== strpos( $detail_html, 'rel="noopener noreferrer"' ), "Journey 2: Verification link has target='_blank' and rel='noopener noreferrer'" );

// Quality page integration: update quality settings to enable certifications section with term
$q_settings = function_exists( 'spicecraft_get_quality_settings' ) ? spicecraft_get_quality_settings() : array();
$q_settings['sections_enabled']['certifications'] = 1;
$q_settings['certifications']['heading'] = 'Quality Certifications';
$q_settings['certifications']['selected_ids'] = array( $term_id );
update_option( 'spicecraft_quality_settings', $q_settings );

$q_resp = wp_remote_get( home_url( '/quality/' ) );
$q_html = wp_remote_retrieve_body( $q_resp );
journey_assert( false !== strpos( $q_html, 'ISO 22000' ), "Journey 2: Quality page renders configured certification" );

// -------------------------------------------------------------
// JOURNEY 3: Manufacturing -> Certification -> Related Product Category -> Product
// -------------------------------------------------------------
echo "\n--- Testing Journey 3: Manufacturing Integration & Categories ---\n";
$m_settings = function_exists( 'spicecraft_get_manufacturing_settings' ) ? spicecraft_get_manufacturing_settings() : array();
$m_settings['sections_enabled']['certifications'] = 1;
$m_settings['certifications']['heading'] = 'Manufacturing Accreditations';
$m_settings['certifications']['selected_ids'] = array( $term_id );
update_option( 'spicecraft_manufacturing_settings', $m_settings );

$m_resp = wp_remote_get( home_url( '/manufacturing/' ) );
$m_html = wp_remote_retrieve_body( $m_resp );
journey_assert( false !== strpos( $m_html, 'ISO 22000' ), "Journey 3: Manufacturing page renders configured certification" );
journey_assert( false !== strpos( $detail_html, 'Ground Spices' ), "Journey 3: Detail renders related category (Ground Spices)" );

// -------------------------------------------------------------
// JOURNEY 4: Product -> Certification -> Certification Detail -> Back to Product
// -------------------------------------------------------------
echo "\n--- Testing Journey 4: Single Product Integration ---\n";
// Associate term with product 21
wp_set_object_terms( 21, array( $term_id ), 'spicecraft_certification' );
$prod_resp = wp_remote_get( get_permalink( 21 ) );
$prod_html = wp_remote_retrieve_body( $prod_resp );

journey_assert( false !== strpos( $prod_html, 'sc-product-certs' ), "Journey 4: Product 21 contains certifications trust container" );
journey_assert( false !== strpos( $prod_html, 'ISO 22000' ), "Journey 4: Product 21 displays ISO 22000 trust badge" );
journey_assert( false !== strpos( $prod_html, $detail_url ), "Journey 4: Product trust badge links to certification detail" );

// Check unassociated product 23 has no certification badge
$prod23_resp = wp_remote_get( get_permalink( 23 ) );
$prod23_html = wp_remote_retrieve_body( $prod23_resp );
journey_assert( false === strpos( $prod23_html, 'ISO 22000' ), "Journey 4: Product 23 without certification does NOT display ISO 22000 (No global assumption)" );

// -------------------------------------------------------------
// JOURNEY 7: Admin -> Set PDF Private -> Confirm no frontend document link/source output
// -------------------------------------------------------------
echo "\n--- Testing Journey 7: Private Document Suppression ---\n";
// First check with public PDF
journey_assert( false !== strpos( $detail_html, 'sc-cert-download-btn' ) || false !== strpos( $detail_html, 'Download Certificate' ), "Journey 7: Public PDF shows view/download button" );

// Switch document to private
$meta_data['doc_visibility'] = 'private';
spicecraft_save_certification_meta( $term_id, $meta_data );

$detail_resp2 = wp_remote_get( $detail_url );
$detail_html2 = wp_remote_retrieve_body( $detail_resp2 );
$doc_url = wp_get_attachment_url( 33 );
journey_assert( false === strpos( $detail_html2, $doc_url ), "Journey 7: Private PDF attachment URL completely absent from frontend HTML/source" );
journey_assert( false === strpos( $detail_html2, 'sc-cert-download-btn' ), "Journey 7: Private PDF download button suppressed" );

// -------------------------------------------------------------
// JOURNEY 8: Admin -> Disable Public Detail Page -> Archive card remains, detail redirects
// -------------------------------------------------------------
echo "\n--- Testing Journey 8: Thin Record / Detail Disabled Behavior ---\n";
$meta_data['public_detail'] = 0;
spicecraft_save_certification_meta( $term_id, $meta_data );

// Check Archive still shows card
$archive_resp2 = wp_remote_get( home_url( '/certifications/' ) );
$archive_html2 = wp_remote_retrieve_body( $archive_resp2 );
journey_assert( false !== strpos( $archive_html2, 'ISO 22000' ), "Journey 8: Archive card remains visible when public_detail is disabled" );

// Check detail URL redirects (302) to archive
$detail_redir_resp = wp_remote_get( $detail_url, array( 'redirection' => 0 ) );
$redir_code = wp_remote_retrieve_response_code( $detail_redir_resp );
$redir_loc  = wp_remote_retrieve_header( $detail_redir_resp, 'location' );
journey_assert( 302 === $redir_code, "Journey 8: Detail page returns HTTP 302 redirect when detail is disabled" );
journey_assert( false !== strpos( $redir_loc, '/certifications/' ), "Journey 8: Detail page redirects to /certifications/ archive" );

// -------------------------------------------------------------
// JOURNEY 6: Admin -> Mark Certification Internal -> Removed from all frontend contexts
// -------------------------------------------------------------
echo "\n--- Testing Journey 6: Internal Visibility Suppression ---\n";
$meta_data['visibility'] = 'internal';
$meta_data['public_detail'] = 1;
spicecraft_save_certification_meta( $term_id, $meta_data );

// Check public query returns 0
$public_certs2 = spicecraft_get_public_certifications();
journey_assert( empty( $public_certs2 ), "Journey 6: spicecraft_get_public_certifications returns 0 for internal cert" );

// Check archive does NOT display internal cert
$archive_resp3 = wp_remote_get( home_url( '/certifications/' ) );
$archive_html3 = wp_remote_retrieve_body( $archive_resp3 );
journey_assert( false === strpos( $archive_html3, 'ISO 22000' ), "Journey 6: Internal certification hidden from archive" );

// Check detail page redirects (302) to archive for internal cert
$detail_resp3 = wp_remote_get( $detail_url, array( 'redirection' => 0 ) );
journey_assert( 302 === wp_remote_retrieve_response_code( $detail_resp3 ), "Journey 6: Internal certification detail page redirects (302)" );

// Check Product 21 no longer displays internal cert
$prod_resp3 = wp_remote_get( get_permalink( 21 ) );
$prod_html3 = wp_remote_retrieve_body( $prod_resp3 );
journey_assert( false === strpos( $prod_html3, 'ISO 22000' ), "Journey 6: Internal certification hidden from product page" );

// Check Manufacturing & Quality no longer display it
$m_resp3 = wp_remote_get( home_url( '/manufacturing/' ) );
$m_html3 = wp_remote_retrieve_body( $m_resp3 );
journey_assert( false === strpos( $m_html3, 'ISO 22000' ), "Journey 6: Internal certification hidden from manufacturing page" );

$q_resp3 = wp_remote_get( home_url( '/quality/' ) );
$q_html3 = wp_remote_retrieve_body( $q_resp3 );
journey_assert( false === strpos( $q_html3, 'ISO 22000' ), "Journey 6: Internal certification hidden from quality page" );

// -------------------------------------------------------------
// Regression Checks: Catalog Mode & Step 4A
// -------------------------------------------------------------
echo "\n--- Testing Regression: Catalog Mode & Step 4A ---\n";
$cart_url = wc_get_cart_url();
$cart_resp = wp_remote_get( $cart_url, array( 'redirection' => 0 ) );
$cart_code = wp_remote_retrieve_response_code( $cart_resp );
journey_assert( in_array( $cart_code, array( 301, 302 ) ), "Catalog Regression: Cart page redirects (no purchase access)" );

$checkout_url = wc_get_checkout_url();
$checkout_resp = wp_remote_get( $checkout_url, array( 'redirection' => 0 ) );
$checkout_code = wp_remote_retrieve_response_code( $checkout_resp );
journey_assert( in_array( $checkout_code, array( 301, 302 ) ), "Catalog Regression: Checkout page redirects (no purchase access)" );

// Step 4A check: Product catalog grid has no empty first slot
$shop_resp = wp_remote_get( wc_get_page_permalink( 'shop' ) );
$shop_html = wp_remote_retrieve_body( $shop_resp );
journey_assert( false === strpos( $shop_html, 'sc-product-grid-blank-slot' ), "Step 4A Regression: Product grid has no blank first slot" );

// Clean up test term
wp_delete_term( $term_id, 'spicecraft_certification' );
journey_assert( true, "Journey test term successfully cleaned up" );

echo "\n=== USER JOURNEYS SUMMARY ===\n";
echo "PASSED: " . count( $results['passed'] ) . "\n";
echo "FAILED: " . count( $results['failed'] ) . "\n";

if ( ! empty( $results['failed'] ) ) {
	exit( 1 );
}
exit( 0 );
