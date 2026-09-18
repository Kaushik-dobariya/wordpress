<?php
require_once dirname( __DIR__ ) . '/wp-load.php';

$terms = get_terms( array(
	'taxonomy'   => 'spicecraft_certification',
	'hide_empty' => false,
) );

echo "=== CERTIFICATION TERMS IN DB ===\n";
foreach ( $terms as $t ) {
	$meta = get_term_meta( $t->term_id );
	$objects = get_objects_in_term( $t->term_id, 'spicecraft_certification' );
	echo "ID: {$t->term_id} | Name: {$t->name} | Slug: {$t->slug} | Count: {$t->count}\n";
	echo "  Meta: " . json_encode( $meta ) . "\n";
	echo "  Linked Products: " . json_encode( $objects ) . "\n";
}
