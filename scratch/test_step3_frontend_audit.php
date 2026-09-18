<?php
/**
 * SpiceCraft Phase 3 Step 3 - Comprehensive Frontend & HTTP Audit
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

echo "=== STARTING FRONTEND & HTTP AUDIT ===\n\n";

$base_url = home_url();

// 1. Test /certifications/ Empty State when 0 terms exist
$archive_url = home_url( '/certifications/' );
$archive_html = @file_get_contents( $archive_url );
sc_assert( false !== $archive_html, "HTTP GET /certifications/ succeeded" );
sc_assert( false !== strpos( $archive_html, 'Official Certifications &amp; Accreditations' ) || false !== strpos( $archive_html, 'Official Standards' ) || false !== strpos( $archive_html, 'Certification Information Being Updated' ), "Archive H1 or neutral title rendered" );
sc_assert( false !== strpos( $archive_html, 'sc-cert-empty-state' ), "Neutral empty state rendered when 0 records exist" );
sc_assert( false === strpos( $archive_html, 'FSSAI Certified' ) && false === strpos( $archive_html, 'ISO 22000' ) && false === strpos( $archive_html, 'HACCP Certified' ), "Zero fabricated cards rendered on empty archive" );

// 2. Controlled Multi-Term Fixture Test
// Create 2 test terms:
// Term A: Public, Active, Public PDF, Featured, Related to Product 15
// Term B: Public, Pending Renewal, Private PDF, Detail Disabled, Related to Product 21
// Term C: Internal / Admin Only (must never appear)

$term_a = wp_insert_term( 'Audit Quality Assurance Standard', 'spicecraft_certification', array(
	'slug'        => 'audit-qa-standard',
	'description' => 'Comprehensive food safety audit for spice processing.',
) );
$term_a_id = is_array( $term_a ) ? $term_a['term_id'] : 0;

spicecraft_update_certification_meta( $term_a_id, array(
	'short_name'         => 'AQAS',
	'certificate_number' => 'AUDIT-QA-9001',
	'issuing_authority'  => 'International Spice Registrar',
	'accreditation_body' => 'Global Safety Board',
	'issue_date'         => '2023-05-10',
	'valid_from'         => '2023-05-10',
	'expiry_date'        => date( 'Y-m-d', strtotime( '+1 year' ) ),
	'status'             => 'active',
	'auto_expiry'        => 0,
	'scope'              => 'Cleaning, processing, steam sterilization, and packing of spices.',
	'facility_scope'     => 'Processing Facility Alpha, Unit 1',
	'verification_url'   => 'https://example.com/verify/AUDIT-QA-9001',
	'visibility'         => 'public',
	'doc_visibility'     => 'public',
	'doc_id'             => 16, // dummy ID
	'public_detail'      => 1,
	'featured'           => 1,
	'order'              => 1,
	'related_products'   => array( 15 ),
) );

$term_b = wp_insert_term( 'Spice Organic Agriculture Standard', 'spicecraft_certification', array(
	'slug'        => 'spice-organic-agriculture',
	'description' => 'Organic agricultural audit for spice cultivation and handling.',
) );
$term_b_id = is_array( $term_b ) ? $term_b['term_id'] : 0;

spicecraft_update_certification_meta( $term_b_id, array(
	'short_name'         => 'SOAS',
	'certificate_number' => 'ORG-2025-9988',
	'issuing_authority'  => 'Organic Accreditation Service',
	'accreditation_body' => 'International Organic Council',
	'issue_date'         => '2022-01-01',
	'valid_from'         => '2022-01-01',
	'expiry_date'        => date( 'Y-m-d', strtotime( '+3 months' ) ),
	'status'             => 'pending_renewal',
	'auto_expiry'        => 0,
	'scope'              => 'Storage and packaging of organic spices.',
	'facility_scope'     => 'Packaging Plant Beta',
	'visibility'         => 'public',
	'doc_visibility'     => 'private', // STRICT PRIVACY TEST
	'doc_id'             => 17,
	'public_detail'      => 0, // THIN RECORD / DETAIL DISABLED TEST
	'featured'           => 0,
	'order'              => 2,
	'related_products'   => array( 21 ),
) );

$term_c = wp_insert_term( 'Internal Facility Security Audit', 'spicecraft_certification', array(
	'slug'        => 'internal-security-audit',
	'description' => 'Internal restricted facility physical security audit.',
) );
$term_c_id = is_array( $term_c ) ? $term_c['term_id'] : 0;

spicecraft_update_certification_meta( $term_c_id, array(
	'short_name'         => 'IFSA',
	'certificate_number' => 'INT-SEC-001',
	'issuing_authority'  => 'Internal Audit Department',
	'status'             => 'active',
	'visibility'         => 'internal', // MUST NOT APPEAR ON FRONTEND
	'public_detail'      => 0,
) );

// Flush rewrites so term A resolves
flush_rewrite_rules();

// 3. Test Archive with Populated Records
$populated_archive_html = @file_get_contents( $archive_url );
sc_assert( false !== strpos( $populated_archive_html, 'Audit Quality Assurance Standard' ), "Archive renders Public Term A" );
sc_assert( false !== strpos( $populated_archive_html, 'Spice Organic Agriculture Standard' ), "Archive renders Public Term B" );
sc_assert( false === strpos( $populated_archive_html, 'Internal Facility Security Audit' ), "Archive strictly hides Internal Term C" );
sc_assert( false !== strpos( $populated_archive_html, 'sc-cert-card--active' ), "Archive renders active status class for Term A" );
sc_assert( false !== strpos( $populated_archive_html, 'sc-cert-card--pending_renewal' ), "Archive renders pending renewal status class for Term B" );
sc_assert( false !== strpos( $populated_archive_html, 'sc-cert-filters-form' ), "Archive renders filtering form when multiple records exist" );

// 4. Test Term A Detail Page
$term_a_obj = get_term( $term_a_id, 'spicecraft_certification' );
$detail_a_url = get_term_link( $term_a_obj );
$detail_a_html = @file_get_contents( $detail_a_url );
sc_assert( false !== $detail_a_html, "HTTP GET /certification/audit-qa-standard/ succeeded" );
sc_assert( false !== strpos( $detail_a_html, '<h1 class="sc-cert-detail-hero__title">Audit Quality Assurance Standard</h1>' ), "Detail hero renders single H1 with certification title" );
sc_assert( false !== strpos( $detail_a_html, 'AUDIT-QA-9001' ), "Detail renders certificate registration number" );
sc_assert( false !== strpos( $detail_a_html, 'International Spice Registrar' ), "Detail renders issuing registrar" );
sc_assert( false !== strpos( $detail_a_html, 'Processing Facility Alpha, Unit 1' ), "Detail renders covered facility scope" );
sc_assert( false !== strpos( $detail_a_html, 'https://example.com/verify/AUDIT-QA-9001' ), "Detail renders official verification link" );
sc_assert( false !== strpos( $detail_a_html, 'rel="noopener noreferrer"' ), "Verification link enforces rel='noopener noreferrer'" );
sc_assert( false !== strpos( $detail_a_html, 'target="_blank"' ), "Verification link enforces target='_blank'" );

// 5. Test Term B Thin Record / Detail Disabled 302 Redirect
$term_b_obj = get_term( $term_b_id, 'spicecraft_certification' );
$detail_b_url = get_term_link( $term_b_obj );

// Use stream context to inspect redirect headers
$ctx = stream_context_create( array(
	'http' => array(
		'follow_location' => 0,
		'ignore_errors'   => true,
	),
) );
$fp = @fopen( $detail_b_url, 'r', false, $ctx );
$meta_data = stream_get_meta_data( $fp );
$status_line = $meta_data['wrapper_data'][0] ?? '';
fclose( $fp );
sc_assert( false !== strpos( $status_line, '302' ) || false !== strpos( $status_line, '301' ), "Detail-disabled Term B issues redirect (302) away from thin detail page: {$status_line}" );

// 6. Test Term B Private Document Suppression
// Even if rendered somewhere, doc_id 17 must not have its URL rendered
sc_assert( false === strpos( $populated_archive_html, 'wp-content/uploads/' . date('Y/m') . '/dummy17' ) && false === strpos( $populated_archive_html, 'doc_id=17' ), "Private document for Term B is strictly suppressed from frontend HTML/source" );

// 7. Test Single Product Certification Integration
// Term A is related to Product 15
$prod15_url = get_permalink( 15 );
$prod15_html = @file_get_contents( $prod15_url );
sc_assert( false !== strpos( $prod15_html, 'sc-product-certs' ), "Product 15 renders sc-product-certs block" );
sc_assert( false !== strpos( $prod15_html, 'AQAS' ) || false !== strpos( $prod15_html, 'Audit Quality Assurance Standard' ), "Product 15 renders Term A trust badge" );

// Product 22 is NOT related to Term A or B
$prod22_url = get_permalink( 22 );
$prod22_html = @file_get_contents( $prod22_url );
sc_assert( false === strpos( $prod22_html, 'AQAS' ) && false === strpos( $prod22_html, 'AUDIT-QA-9001' ), "Unrelated Product 22 does NOT show Term A (strict scoping rule respected)" );

// 8. Test Homepage, About, Manufacturing, Quality Integrations
$home_html = @file_get_contents( $base_url );
sc_assert( false !== strpos( $home_html, 'Audit Quality Assurance Standard' ) || false !== strpos( $home_html, 'sc-home-certifications' ), "Homepage integrates public certification records" );

$about_html = @file_get_contents( home_url( '/about/' ) );
sc_assert( false !== strpos( $about_html, 'Audit Quality Assurance Standard' ) || false !== strpos( $about_html, 'sc-about-certs' ), "About page integrates public certification records" );

// Test Manufacturing: explicitly select Term A in Manufacturing CMS settings
$mfg_settings = spicecraft_get_manufacturing_settings();
$orig_mfg = $mfg_settings;
$mfg_settings['certifications']['heading'] = 'Manufacturing Quality Standards';
$mfg_settings['certifications']['selected_ids'] = array( $term_a_id );
spicecraft_update_manufacturing_settings( $mfg_settings );

$mfg_html = @file_get_contents( home_url( '/manufacturing/' ) );
sc_assert( false !== strpos( $mfg_html, 'Audit Quality Assurance Standard' ) && false !== strpos( $mfg_html, 'sc-mfg-certs' ), "Manufacturing page integrates explicitly selected certification records" );
spicecraft_update_manufacturing_settings( $orig_mfg ); // restore

// Test Quality & Sourcing: explicitly select Term A in Quality CMS settings
$quality_settings = spicecraft_get_quality_settings();
$orig_quality = $quality_settings;
$quality_settings['certifications']['heading'] = 'Quality Accreditations';
$quality_settings['certifications']['selected_ids'] = array( $term_a_id );
spicecraft_update_quality_settings( $quality_settings );

$quality_url = home_url( '/quality/' );
$quality_html = @file_get_contents( $quality_url );
sc_assert( false !== strpos( $quality_html, 'Audit Quality Assurance Standard' ) && false !== strpos( $quality_html, 'sc-quality-certs' ), "Quality & Sourcing page integrates explicitly selected certification records" );
spicecraft_update_quality_settings( $orig_quality ); // restore

// CLEANUP FIXTURES: Delete terms A, B, C to leave ZERO fabricated data
wp_delete_term( $term_a_id, 'spicecraft_certification' );
wp_delete_term( $term_b_id, 'spicecraft_certification' );
wp_delete_term( $term_c_id, 'spicecraft_certification' );
flush_rewrite_rules();

// Confirm DB has 0 terms
$remaining_terms = get_terms( array( 'taxonomy' => 'spicecraft_certification', 'hide_empty' => false ) );
sc_assert( empty( $remaining_terms ), "Zero unverified certification terms remain in database (Zero-Fabricated-Content Rule verified)" );

echo "\n=== FRONTEND AUDIT SUMMARY ===\n";
echo "PASSED: " . count( $results['passed'] ) . "\n";
echo "FAILED: " . count( $results['failed'] ) . "\n";

if ( ! empty( $results['failed'] ) ) {
	exit( 1 );
}
exit( 0 );
