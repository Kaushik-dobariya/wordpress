<?php
/**
 * Cleanup test fixtures after visual testing.
 */
require_once __DIR__ . '/../wp-load.php';

$existing = get_term_by( 'slug', 'iso-22000-food-safety', 'spicecraft_certification' );
if ( $existing ) {
	wp_delete_term( $existing->term_id, 'spicecraft_certification' );
	echo "FIXTURE_CLEANED\n";
} else {
	echo "NO_FIXTURE_FOUND\n";
}
