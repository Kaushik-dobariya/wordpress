<?php
/**
 * Step 0A: Purge unverified seeded certification terms from DB
 */
require_once dirname( __DIR__ ) . '/wp-load.php';

$terms = get_terms( array(
	'taxonomy'   => 'spicecraft_certification',
	'hide_empty' => false,
) );

echo "Purging " . count( $terms ) . " seeded certification terms...\n";
foreach ( $terms as $t ) {
	$res = wp_delete_term( $t->term_id, 'spicecraft_certification' );
	echo " - Deleted term ID {$t->term_id} ({$t->name})\n";
}

// Clear any product-term relationships
$products = get_posts( array(
	'post_type'   => 'product',
	'numberposts' => -1,
	'post_status' => 'any',
) );
foreach ( $products as $p ) {
	wp_set_object_terms( $p->ID, array(), 'spicecraft_certification' );
}

$remaining = get_terms( array(
	'taxonomy'   => 'spicecraft_certification',
	'hide_empty' => false,
) );

echo "Remaining certification terms in DB: " . count( $remaining ) . "\n";
