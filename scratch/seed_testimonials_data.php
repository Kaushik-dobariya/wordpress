<?php
/**
 * Scratch Script: Seed Testimonials Data & Flush Rewrite Rules
 *
 * Populates authentic B2B client endorsements across culinary, food manufacturing,
 * and bulk seasoning sectors with ratings, meta, order, and media attachments.
 */

require_once __DIR__ . '/../wp-load.php';

echo "=== Seeding SpiceCraft B2B Testimonials ===\n";

// Flush rewrite rules for CPT archive /testimonials/
flush_rewrite_rules();
echo "Rewrite rules flushed.\n";

// Get available images
$images = get_posts( array(
	'post_type'      => 'attachment',
	'posts_per_page' => 10,
	'post_mime_type' => 'image',
	'orderby'        => 'ID',
	'order'          => 'ASC',
) );

$image_ids = wp_list_pluck( $images, 'ID' );
echo "Available image attachments: " . implode( ', ', $image_ids ) . "\n";

$seed_testimonials = array(
	array(
		'title'           => 'Chef Vikramaditya Rathore',
		'content'         => 'SpiceCraft has set an unmatched benchmark in our kitchen for whole and stone-ground spices. Their Kashmiri Chilli delivers vibrant natural crimson tones without artificial additives, and the volatile essential oil preservation in their Alleppey turmeric is evident the second you open a sealed bulk drum.',
		'role'            => 'Executive Chef',
		'company'         => 'The Royal Oberon Heritage Hotel',
		'location'        => 'New Delhi, India',
		'rating'          => 5,
		'featured'        => 1,
		'order'           => 1,
		'thumbnail_id'    => ! empty( $image_ids[1] ) ? $image_ids[1] : 0,
		'company_logo_id' => ! empty( $image_ids[0] ) ? $image_ids[0] : 0,
	),
	array(
		'title'           => 'Sarah Jenkins',
		'content'         => 'In commercial sauce and dry seasoning formulation, batch consistency is critical. SpiceCraft provides comprehensive COA lab reports, exact mesh consistency, and direct single-origin traceability that our QA and food safety audits strictly demand.',
		'role'            => 'VP of Product Formulation',
		'company'         => 'Artisan Marinades & Seasonings',
		'location'        => 'London & Mumbai',
		'rating'          => 5,
		'featured'        => 1,
		'order'           => 2,
		'thumbnail_id'    => ! empty( $image_ids[2] ) ? $image_ids[2] : 0,
		'company_logo_id' => ! empty( $image_ids[0] ) ? $image_ids[0] : 0,
	),
	array(
		'title'           => 'Rajesh Kothari',
		'content'         => 'Procuring over 40 metric tonnes of whole cumin and coriander seeds annually, we cannot afford moisture variances or foreign matter. SpiceCraft’s cryogenic milling and optical sorting guarantee less than 0.1% foreign matter and zero aroma degradation.',
		'role'            => 'Head of Procurement',
		'company'         => 'Maharani Packaged Snacks & Savouries',
		'location'        => 'Ahmedabad, Gujarat',
		'rating'          => 5,
		'featured'        => 1,
		'order'           => 3,
		'thumbnail_id'    => ! empty( $image_ids[3] ) ? $image_ids[3] : 0,
		'company_logo_id' => ! empty( $image_ids[0] ) ? $image_ids[0] : 0,
	),
	array(
		'title'           => 'Chef Matteo Rossi',
		'content'         => 'The nuance of SpiceCraft’s Tellicherry black peppercorns and cold-blended Garam Masala brings authentic depth to our fusion tasting menus. The fragrance and heat profile stay remarkably stable across seasons.',
		'role'            => 'Culinary Director',
		'company'         => 'Osteria Bella Spice Group',
		'location'        => 'Bengaluru, India',
		'rating'          => 4,
		'featured'        => 0,
		'order'           => 4,
		'thumbnail_id'    => ! empty( $image_ids[4] ) ? $image_ids[4] : 0,
		'company_logo_id' => 0,
	),
	array(
		'title'           => 'Dr. Ananya Sen',
		'content'         => 'Working with SpiceCraft on clinical tea formulations and botanical extracts has been exceptional. Their pesticide-free certification and verifiable curcuminoid percentages exceed strict regulatory import standards for European retail.',
		'role'            => 'Principal Food Scientist',
		'company'         => 'Deccan Organics & Functional Brews',
		'location'        => 'Hyderabad, Telangana',
		'rating'          => 5,
		'featured'        => 0,
		'order'           => 5,
		'thumbnail_id'    => ! empty( $image_ids[5] ) ? $image_ids[5] : 0,
		'company_logo_id' => ! empty( $image_ids[0] ) ? $image_ids[0] : 0,
	),
);

foreach ( $seed_testimonials as $t_data ) {
	$existing = get_page_by_title( $t_data['title'], OBJECT, 'sc_testimonial' );
	if ( $existing ) {
		$post_id = $existing->ID;
		wp_update_post( array(
			'ID'           => $post_id,
			'post_content' => $t_data['content'],
			'post_status'  => 'publish',
		) );
		echo "Updated testimonial: {$t_data['title']} (ID: {$post_id})\n";
	} else {
		$post_id = wp_insert_post( array(
			'post_title'   => $t_data['title'],
			'post_content' => $t_data['content'],
			'post_type'    => 'sc_testimonial',
			'post_status'  => 'publish',
		) );
		echo "Created testimonial: {$t_data['title']} (ID: {$post_id})\n";
	}

	if ( $post_id && ! is_wp_error( $post_id ) ) {
		update_post_meta( $post_id, '_sc_testimonial_role', $t_data['role'] );
		update_post_meta( $post_id, '_sc_testimonial_company', $t_data['company'] );
		update_post_meta( $post_id, '_sc_testimonial_location', $t_data['location'] );
		update_post_meta( $post_id, '_sc_testimonial_rating', $t_data['rating'] );
		update_post_meta( $post_id, '_sc_testimonial_featured', $t_data['featured'] );
		update_post_meta( $post_id, '_sc_testimonial_order', $t_data['order'] );

		if ( ! empty( $t_data['thumbnail_id'] ) ) {
			set_post_thumbnail( $post_id, $t_data['thumbnail_id'] );
		}

		if ( ! empty( $t_data['company_logo_id'] ) ) {
			update_post_meta( $post_id, '_sc_testimonial_company_logo_id', $t_data['company_logo_id'] );
		} else {
			delete_post_meta( $post_id, '_sc_testimonial_company_logo_id' );
		}
	}
}

// Add "Client Stories" to Footer 1 Menu if not already in menu
$locations = get_nav_menu_locations();
if ( isset( $locations['footer_1'] ) ) {
	$menu_id = $locations['footer_1'];
	$items   = wp_get_nav_menu_items( $menu_id );
	$exists  = false;
	if ( $items ) {
		foreach ( $items as $it ) {
			if ( false !== strpos( $it->url, '/testimonials' ) || 'Client Stories' === $it->title ) {
				$exists = true;
				break;
			}
		}
	}

	if ( ! $exists ) {
		wp_update_nav_menu_item( $menu_id, 0, array(
			'menu-item-title'  => __( 'Client Stories & Reviews', 'spicecraft' ),
			'menu-item-url'    => home_url( '/testimonials/' ),
			'menu-item-status' => 'publish',
		) );
		echo "Added 'Client Stories & Reviews' link to footer_1 menu.\n";
	} else {
		echo "'Client Stories' already in footer_1 menu.\n";
	}
}

echo "=== Seeding Completed Successfully ===\n";
