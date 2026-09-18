<?php
/**
 * Setup test fixture for viewport & visual testing.
 */
require_once __DIR__ . '/../wp-load.php';

// Clean existing test terms
$existing = get_term_by( 'slug', 'iso-22000-food-safety', 'spicecraft_certification' );
if ( $existing ) {
	wp_delete_term( $existing->term_id, 'spicecraft_certification' );
}

$term = wp_insert_term( 'ISO 22000 Food Safety', 'spicecraft_certification', array( 'slug' => 'iso-22000-food-safety' ) );
if ( is_wp_error( $term ) ) {
	echo "ERROR: " . $term->get_error_message() . PHP_EOL;
	exit( 1 );
}

$term_id = $term['term_id'];

wp_update_term( $term_id, 'spicecraft_certification', array(
	'description' => 'Statutory international food safety management system certification covering cryogenic processing, hygienic sorting, and automated vacuum packing.',
) );

$meta = array(
	'short_name'         => 'ISO 22000',
	'status'             => 'active',
	'auto_expiry'        => 0,
	'number'             => 'FSMS-22000-IN-2024',
	'issuing_authority'  => 'Bureau Veritas Quality International',
	'accreditation_body' => 'UKAS Accreditation Service',
	'issue_date'         => '2024-03-01',
	'valid_from'         => '2024-03-01',
	'expiry_date'        => '2027-02-28',
	'scope'              => 'Cryogenic spice grinding, automated optical seed sorting, sterile blending, and moisture-barrier vacuum packaging.',
	'facility_scope'     => 'SpiceCraft Cleanroom Processing Plant, Unit 1 & Unit 2, Cochin, Kerala',
	'verification_url'   => 'https://certcheck.example.com/verify?id=FSMS-22000-IN-2024',
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

spicecraft_save_certification_meta( $term_id, $meta );

$detail_url = get_term_link( $term_id, 'spicecraft_certification' );
echo "FIXTURE_READY: {$detail_url}\n";
