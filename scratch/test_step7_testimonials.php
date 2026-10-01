<?php
/**
 * Test Step 7: Testimonials / Customer Reviews CMS Verification Script
 */

require_once __DIR__ . '/../wp-load.php';

echo "====================================================\n";
echo "  SPICECRAFT PHASE 3 - STEP 7 TESTIMONIALS VERIFICATION\n";
echo "====================================================\n\n";

$passes = 0;
$fails  = 0;

function sc_assert( $desc, $condition ) {
	global $passes, $fails;
	if ( $condition ) {
		echo " [PASS] $desc\n";
		$passes++;
	} else {
		echo " [FAIL] $desc\n";
		$fails++;
	}
}

// 1. CPT Registration
$post_type_obj = get_post_type_object( 'sc_testimonial' );
sc_assert( "CPT 'sc_testimonial' is registered", ! empty( $post_type_obj ) );
sc_assert( "CPT has public archive", ! empty( $post_type_obj->has_archive ) );
sc_assert( "CPT archive slug is 'testimonials'", 'testimonials' === $post_type_obj->rewrite['slug'] );

// 2. Query helper functions
$all_testimonials = spicecraft_get_testimonials();
sc_assert( "spicecraft_get_testimonials() returns 5 posts", count( $all_testimonials ) >= 5 );

$featured = spicecraft_get_featured_testimonials( 3 );
sc_assert( "spicecraft_get_featured_testimonials() returns 3 featured posts", count( $featured ) === 3 );

// 3. Helper URL
$url = spicecraft_get_testimonials_url();
sc_assert( "spicecraft_get_testimonials_url() returns valid URL", false !== strpos( $url, '/testimonials' ) );

// 4. Star Rating Output & Accessibility
$stars_html = spicecraft_render_testimonial_stars( 5, false );
sc_assert( "Stars HTML contains 5 out of 5 stars accessible screen-reader text", false !== strpos( $stars_html, '5 out of 5 stars' ) );
sc_assert( "Stars HTML contains 5 filled star SVGs", substr_count( $stars_html, 'sc-star-icon--filled' ) === 5 );

$stars_html_4 = spicecraft_render_testimonial_stars( 4, false );
sc_assert( "Stars HTML (4 stars) contains 4 filled and 1 empty star", substr_count( $stars_html_4, 'sc-star-icon--filled' ) === 4 && substr_count( $stars_html_4, 'sc-star-icon--empty' ) === 1 );

// 5. Check metadata on seeded testimonials
$first_t = $all_testimonials[0];
$role = get_post_meta( $first_t->ID, '_sc_testimonial_role', true );
$company = get_post_meta( $first_t->ID, '_sc_testimonial_company', true );
$rating = get_post_meta( $first_t->ID, '_sc_testimonial_rating', true );
$order = get_post_meta( $first_t->ID, '_sc_testimonial_order', true );

sc_assert( "Testimonial #1 has role", ! empty( $role ) );
sc_assert( "Testimonial #1 has company", ! empty( $company ) );
sc_assert( "Testimonial #1 has rating (1-5)", (int) $rating >= 1 && (int) $rating <= 5 );
sc_assert( "Testimonial #1 has display order", '' !== $order );

// 6. Test Frontend Archive Page via wp_remote_get
$archive_res = wp_remote_get( home_url( '/testimonials/' ) );
$archive_code = wp_remote_retrieve_response_code( $archive_res );
$archive_body = wp_remote_retrieve_body( $archive_res );

sc_assert( "GET /testimonials/ returns HTTP 200", 200 === (int) $archive_code );

// Verify Single <h1> on archive
preg_match_all( '/<h1[^>]*>(.*?)<\/h1>/is', $archive_body, $h1_matches );
sc_assert( "Archive page has exactly 1 <h1> tag", count( $h1_matches[0] ) === 1 );
sc_assert( "Archive <h1> contains 'Customer Endorsements & Partner Stories'", false !== strpos( html_entity_decode( $h1_matches[1][0] ), 'Customer Endorsements & Partner Stories' ) );

// Verify Trust Metrics Bar
sc_assert( "Archive contains trust metrics bar", false !== strpos( $archive_body, 'sc-testimonials-trust-bar' ) );
sc_assert( "Archive displays 4.9 Commercial Client Rating", false !== strpos( $archive_body, '4.9' ) );

// Verify Testimonial Cards & Authors
sc_assert( "Archive displays Chef Vikramaditya Rathore", false !== strpos( $archive_body, 'Chef Vikramaditya Rathore' ) );
sc_assert( "Archive displays Sarah Jenkins", false !== strpos( $archive_body, 'Sarah Jenkins' ) );
sc_assert( "Archive displays Rajesh Kothari", false !== strpos( $archive_body, 'Rajesh Kothari' ) );
sc_assert( "Archive displays B2B CTA section", false !== strpos( $archive_body, 'sc-testimonials-cta-card' ) );

// 7. Test Frontend Single Testimonial Page
$single_permalink = get_permalink( $first_t->ID );
$single_res = wp_remote_get( $single_permalink );
$single_code = wp_remote_retrieve_response_code( $single_res );
$single_body = wp_remote_retrieve_body( $single_res );

sc_assert( "GET single testimonial returns HTTP 200", 200 === (int) $single_code );
preg_match_all( '/<h1[^>]*>(.*?)<\/h1>/is', $single_body, $single_h1_matches );
sc_assert( "Single testimonial page has exactly 1 <h1> tag", count( $single_h1_matches[0] ) === 1 );
sc_assert( "Single testimonial contains Back to All Client Stories link", false !== strpos( $single_body, 'Back to All Client Stories' ) );

// 8. Test Homepage Integration
$home_res = wp_remote_get( home_url( '/' ) );
$home_code = wp_remote_retrieve_response_code( $home_res );
$home_body = wp_remote_retrieve_body( $home_res );

sc_assert( "GET / (Homepage) returns HTTP 200", 200 === (int) $home_code );
sc_assert( "Homepage contains #testimonials section", false !== strpos( $home_body, 'id="testimonials"' ) );
sc_assert( "Homepage contains testimonial cards", false !== strpos( $home_body, 'sc-testimonial-card' ) );
sc_assert( "Homepage contains link to /testimonials/", false !== strpos( $home_body, '/testimonials/' ) );

// 9. Admin Meta Box Simulation
wp_set_current_user( 1 ); // Admin user
$test_post_id = wp_insert_post( array(
	'post_title'   => 'Admin QA Testimonial',
	'post_content' => 'Exceptional aroma and purity testing consistency across all spice batches.',
	'post_type'    => 'sc_testimonial',
	'post_status'  => 'draft',
) );

sc_assert( "Admin can create draft testimonial", $test_post_id > 0 );

// Simulate meta save
update_post_meta( $test_post_id, '_sc_testimonial_role', 'Head Blender' );
update_post_meta( $test_post_id, '_sc_testimonial_company', 'Spice Frontier Labs' );
update_post_meta( $test_post_id, '_sc_testimonial_rating', 5 );
update_post_meta( $test_post_id, '_sc_testimonial_featured', 1 );
update_post_meta( $test_post_id, '_sc_testimonial_order', 99 );

sc_assert( "Meta fields successfully updated for draft", 
	'Head Blender' === get_post_meta( $test_post_id, '_sc_testimonial_role', true ) &&
	'Spice Frontier Labs' === get_post_meta( $test_post_id, '_sc_testimonial_company', true ) &&
	5 === (int) get_post_meta( $test_post_id, '_sc_testimonial_rating', true )
);

// Publish and unpublish (Draft) test
wp_update_post( array(
	'ID'          => $test_post_id,
	'post_status' => 'publish',
) );
sc_assert( "Testimonial can be published", 'publish' === get_post_status( $test_post_id ) );

wp_update_post( array(
	'ID'          => $test_post_id,
	'post_status' => 'draft',
) );
sc_assert( "Testimonial can be unpublished (set to draft)", 'draft' === get_post_status( $test_post_id ) );

// Verify draft does NOT appear publicly on frontend archive
$draft_check_res = wp_remote_get( home_url( '/testimonials/' ) );
$draft_check_body = wp_remote_retrieve_body( $draft_check_res );
sc_assert( "Draft testimonial does NOT appear on public archive", false === strpos( $draft_check_body, 'Admin QA Testimonial' ) );

// Clean up test post
wp_delete_post( $test_post_id, true );
sc_assert( "Testimonial can be deleted", empty( get_post( $test_post_id ) ) );

echo "\n====================================================\n";
echo "  SUMMARY: Passes: $passes | Fails: $fails\n";
echo "====================================================\n";
