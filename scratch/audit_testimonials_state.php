<?php
require_once __DIR__ . '/../wp-load.php';

echo "=== AUDIT TESTIMONIALS CMS STATE ===\n";

$ts = get_posts( array(
	'post_type'      => 'spicecraft_testimonial',
	'post_status'    => 'any',
	'posts_per_page' => -1,
) );

echo "Total testimonials in DB: " . count( $ts ) . "\n";
foreach ( $ts as $t ) {
	$role    = get_post_meta( $t->ID, '_sc_testimonial_role', true );
	$company = get_post_meta( $t->ID, '_sc_testimonial_company', true );
	$rating  = get_post_meta( $t->ID, '_sc_testimonial_rating', true );
	$order   = get_post_meta( $t->ID, '_sc_testimonial_order', true );
	$feat    = get_post_meta( $t->ID, '_sc_testimonial_featured', true );
	echo " - ID {$t->ID}: '{$t->post_title}', Status: {$t->post_status}, Role: '{$role}', Company: '{$company}', Rating: {$rating}, Order: {$order}, Featured: " . ( $feat ? 'YES' : 'NO' ) . "\n";
}

$page = get_page_by_path( 'testimonials' );
echo "Page 'testimonials': " . ( $page ? "ID {$page->ID} ({$page->post_status})" : "Not Found" ) . "\n";

$home_settings = get_option( 'spicecraft_homepage_settings', array() );
echo "Homepage settings 'testimonials':\n";
print_r( isset( $home_settings['testimonials'] ) ? $home_settings['testimonials'] : 'None' );
