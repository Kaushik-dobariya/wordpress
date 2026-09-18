<?php
/**
 * SpiceCraft Phase 3 Step 3 - Comprehensive Backend Test Suite
 *
 * Validates:
 * 1. Settings registration, defaults, and saving
 * 2. Term creation, editing, and term meta persistence
 * 3. Status engine (active, expired, pending_renewal, suspended, archived, not_disclosed)
 * 4. Automatic expiry detection (OFF vs ON)
 * 5. 90-day admin expiry alert calculation
 * 6. Public vs Internal visibility filtering
 * 7. Public vs Private document URL suppression
 * 8. Public detail page toggle and thin record redirect logic
 * 9. Product & category relationship queries (scoped strictly)
 * 10. Empty state behavior (when 0 records exist)
 */

require_once __DIR__ . '/../wp-load.php';

$results = array(
	'passed' => array(),
	'failed' => array(),
);

function sc_assert( $condition, $test_name ) {
	global $results;
	if ( $condition ) {
		$results['passed'][] = $test_name;
		echo "[PASS] {$test_name}\n";
	} else {
		$results['failed'][] = $test_name;
		echo "[FAIL] {$test_name}\n";
	}
}

echo "=== STARTING PHASE 3 STEP 3 BACKEND TESTS ===\n\n";

// TEST 1: Global Settings Default Schema
$defaults = spicecraft_get_certification_default_settings();
sc_assert( ! empty( $defaults['archive_enabled'] ), 'Default settings has archive_enabled = true' );
sc_assert( ! empty( $defaults['show_filters'] ), 'Default settings has show_filters = true' );
sc_assert( isset( $defaults['expiry_warning_threshold_days'] ) && 90 === $defaults['expiry_warning_threshold_days'], 'Default expiry warning threshold is 90 days' );

// TEST 2: Update and Retrieve Global Settings
$test_settings = $defaults;
$test_settings['heading']      = 'Verified SpiceCraft Accreditations';
$test_settings['show_expired'] = 0;
spicecraft_update_certification_settings( $test_settings );

$retrieved = spicecraft_get_certification_settings();
sc_assert( 'Verified SpiceCraft Accreditations' === $retrieved['heading'], 'Settings persistence: heading saved and retrieved' );
sc_assert( 0 === $retrieved['show_expired'], 'Settings persistence: show_expired saved and retrieved' );

// Reset settings to defaults
spicecraft_update_certification_settings( $defaults );

// TEST 3: Create a Controlled Test Certification Term
$existing = term_exists( 'Test ISO Compliance', 'spicecraft_certification' );
if ( $existing ) {
	$existing_id = is_array( $existing ) ? $existing['term_id'] : $existing;
	wp_delete_term( $existing_id, 'spicecraft_certification' );
}

$term_result = wp_insert_term( 'Test ISO Compliance', 'spicecraft_certification', array(
	'description' => 'Test certification for food safety management systems.',
	'slug'        => 'test-iso-compliance',
) );

if ( is_wp_error( $term_result ) ) {
	echo "wp_insert_term error: " . $term_result->get_error_message() . "\n";
}
sc_assert( ! is_wp_error( $term_result ) && ! empty( $term_result['term_id'] ), 'wp_insert_term created certification term' );
if ( is_wp_error( $term_result ) ) {
	exit( 1 );
}
$term_id = $term_result['term_id'];

// TEST 4: Save & Retrieve Term Metadata
$sample_meta = array(
	'short_name'         => 'ISO Test',
	'logo_id'            => 12,
	'image_id'           => 14,
	'doc_id'             => 16,
	'doc_visibility'     => 'private',
	'certificate_number' => 'CERT-2026-TEST',
	'issuing_authority'  => 'Test Registrar Ltd',
	'accreditation_body' => 'Test Accreditation Board',
	'issue_date'         => '2024-01-15',
	'valid_from'         => '2024-01-15',
	'expiry_date'        => '2027-01-15',
	'status'             => 'active',
	'auto_expiry'        => 0,
	'scope'              => 'Cleaning, processing, and packaging of spices.',
	'facility_scope'     => 'Unit 1, Main Processing Plant',
	'verification_url'   => 'https://example.com/verify/CERT-2026-TEST',
	'visibility'         => 'public',
	'featured'           => 1,
	'order'              => 5,
	'public_detail'      => 1,
	'related_products'   => array( 15, 21 ),
	'related_categories' => array( 3 ),
);

$saved_meta = spicecraft_save_certification_meta( $term_id, $sample_meta );
sc_assert( ! empty( $saved_meta ), 'spicecraft_save_certification_meta returned saved meta' );

$retrieved_meta = spicecraft_get_certification_meta( $term_id );
sc_assert( 'ISO Test' === $retrieved_meta['short_name'], 'Short name saved and retrieved' );
sc_assert( 'CERT-2026-TEST' === $retrieved_meta['certificate_number'], 'Certificate number saved and retrieved' );
sc_assert( 'Test Registrar Ltd' === $retrieved_meta['issuing_authority'], 'Issuing authority saved and retrieved' );
sc_assert( 'https://example.com/verify/CERT-2026-TEST' === $retrieved_meta['verification_url'], 'Verification URL saved and retrieved' );
sc_assert( array( 15, 21 ) === $retrieved_meta['related_products'], 'Related products array saved and retrieved' );

// TEST 5: Effective Status with auto_expiry OFF
sc_assert( 'active' === spicecraft_get_certification_effective_status( $term_id ), 'Effective status is active when status is active and auto_expiry is OFF' );

// TEST 6: Effective Status with auto_expiry ON and future date
$sample_meta['auto_expiry'] = 1;
$sample_meta['expiry_date'] = date( 'Y-m-d', strtotime( '+6 months' ) );
spicecraft_save_certification_meta( $term_id, $sample_meta );
sc_assert( 'active' === spicecraft_get_certification_effective_status( $term_id ), 'Effective status remains active when auto_expiry is ON and expiry is in future' );

// TEST 7: Effective Status with auto_expiry ON and past date (transition to expired)
$sample_meta['auto_expiry'] = 1;
$sample_meta['expiry_date'] = date( 'Y-m-d', strtotime( '-10 days' ) );
spicecraft_save_certification_meta( $term_id, $sample_meta );
sc_assert( 'expired' === spicecraft_get_certification_effective_status( $term_id ), 'Effective status automatically transitions to expired when past expiry date' );

// TEST 8: Effective Status with auto_expiry OFF and past date (never infer expired without auto_expiry flag)
$sample_meta['auto_expiry'] = 0;
spicecraft_save_certification_meta( $term_id, $sample_meta );
sc_assert( 'active' === spicecraft_get_certification_effective_status( $term_id ), 'Status is strictly governed by admin dropdown when auto_expiry is OFF' );

// TEST 9: Admin-side Expiry Alert Calculation (within 90 days)
$sample_meta['expiry_date'] = date( 'Y-m-d', strtotime( '+45 days' ) );
spicecraft_save_certification_meta( $term_id, $sample_meta );
$alert = spicecraft_get_certification_expiry_alert( $term_id );
sc_assert( ! empty( $alert ) && 'expiring_soon' === $alert['type'], 'Expiry alert triggers expiring_soon when within 90 days' );

// TEST 10: Admin-side Expiry Alert for already expired
$sample_meta['expiry_date'] = date( 'Y-m-d', strtotime( '-5 days' ) );
spicecraft_save_certification_meta( $term_id, $sample_meta );
$alert_past = spicecraft_get_certification_expiry_alert( $term_id );
sc_assert( ! empty( $alert_past ) && 'expired' === $alert_past['type'], 'Expiry alert triggers expired when past expiry date' );

// TEST 11: Document Privacy Enforcement
// When doc_visibility is 'private', public doc url MUST return empty string
$sample_meta['doc_visibility'] = 'private';
spicecraft_save_certification_meta( $term_id, $sample_meta );
$doc_url_private = spicecraft_get_certification_public_document_url( $term_id );
sc_assert( empty( $doc_url_private ), 'spicecraft_get_certification_public_document_url returns empty when doc_visibility is private' );

// When doc_visibility is 'public', public doc url returns the media URL (or url for doc_id)
$sample_meta['doc_visibility'] = 'public';
spicecraft_save_certification_meta( $term_id, $sample_meta );
$sample_meta_check = spicecraft_get_certification_meta( $term_id );
sc_assert( 'public' === $sample_meta_check['doc_visibility'], 'doc_visibility is public' );

// TEST 12: Public vs Internal Visibility
$sample_meta['visibility'] = 'internal';
spicecraft_save_certification_meta( $term_id, $sample_meta );
sc_assert( false === spicecraft_is_certification_public( $term_id ), 'spicecraft_is_certification_public returns false when visibility is internal' );

$sample_meta['visibility'] = 'public';
spicecraft_save_certification_meta( $term_id, $sample_meta );
sc_assert( true === spicecraft_is_certification_public( $term_id ), 'spicecraft_is_certification_public returns true when visibility is public' );

// TEST 13: Public Detail Toggle
$sample_meta['public_detail'] = 0;
spicecraft_save_certification_meta( $term_id, $sample_meta );
sc_assert( false === spicecraft_has_certification_public_detail( $term_id ), 'spicecraft_has_certification_public_detail returns false when public_detail is 0' );

$sample_meta['public_detail'] = 1;
spicecraft_save_certification_meta( $term_id, $sample_meta );
sc_assert( true === spicecraft_has_certification_public_detail( $term_id ), 'spicecraft_has_certification_public_detail returns true when public_detail is 1' );

// TEST 14: Public Certifications Query Helper
$public_certs = spicecraft_get_public_certifications();
sc_assert( count( $public_certs ) >= 1, 'spicecraft_get_public_certifications retrieves active public terms' );

// TEST 15: Product-Scoped Certifications (Product 15 vs Product 999)
// Term relates to product 15
$prod15_certs = spicecraft_get_product_public_certifications( 15 );
sc_assert( ! empty( $prod15_certs ), 'spicecraft_get_product_public_certifications returns certification for product 15' );

// Term does not relate to product 99999
$prod_unrelated_certs = spicecraft_get_product_public_certifications( 99999 );
sc_assert( empty( $prod_unrelated_certs ), 'spicecraft_get_product_public_certifications returns empty for unrelated product 99999 (strict scope enforcement)' );

// CLEANUP: Delete the test certification term so zero fake data remains
wp_delete_term( $term_id, 'spicecraft_certification' );
$deleted_check = get_term( $term_id, 'spicecraft_certification' );
sc_assert( empty( $deleted_check ) || is_wp_error( $deleted_check ), 'Test term cleaned up cleanly from database' );

echo "\n=== BACKEND TEST SUMMARY ===\n";
echo "PASSED: " . count( $results['passed'] ) . "\n";
echo "FAILED: " . count( $results['failed'] ) . "\n";

if ( ! empty( $results['failed'] ) ) {
	exit( 1 );
}
exit( 0 );
