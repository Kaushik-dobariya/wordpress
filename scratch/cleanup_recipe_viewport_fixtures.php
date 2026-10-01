<?php
/**
 * Cleanup viewport test recipe fixture.
 */
require_once __DIR__ . '/../wp-load.php';

$p = get_page_by_path( 'rogan-josh-viewport-test', OBJECT, 'spicecraft_recipe' );
if ( $p ) {
	wp_delete_post( $p->ID, true );
}

$c = get_term_by( 'slug', 'curries', 'spicecraft_recipe_category' );
if ( $c ) {
	wp_delete_term( $c->term_id, 'spicecraft_recipe_category' );
}

$settings = get_option( 'spicecraft_recipe_settings', array() );
$settings['featured_recipe_id'] = 0;
update_option( 'spicecraft_recipe_settings', $settings );

echo "FIXTURE_CLEANED\n";
