<?php
/**
 * Setup controlled test recipe fixture for responsive viewport testing.
 */
require_once __DIR__ . '/../wp-load.php';

// Check if already exists
$existing = get_page_by_path( 'rogan-josh-viewport-test', OBJECT, 'spicecraft_recipe' );
if ( $existing ) {
	wp_delete_post( $existing->ID, true );
}

$cat_term = term_exists( 'Curries', 'spicecraft_recipe_category' );
if ( ! $cat_term ) {
	$cat_term = wp_insert_term( 'Curries', 'spicecraft_recipe_category', array( 'slug' => 'curries' ) );
}
$cat_id = is_array( $cat_term ) ? $cat_term['term_id'] : $cat_term;

$recipe_id = wp_insert_post( array(
	'post_title'   => 'Kashmiri Rogan Josh with Tellicherry Pepper',
	'post_name'    => 'rogan-josh-viewport-test',
	'post_content' => 'An iconic slow-cooked Kashmiri curry prepared with whole roasted aromatic spices and pure ground aromatics.',
	'post_excerpt' => 'A slow-simmered, richly spiced Kashmiri signature curry elevated by whole Tellicherry black peppercorns.',
	'post_status'  => 'publish',
	'post_type'    => 'spicecraft_recipe',
) );

wp_set_object_terms( $recipe_id, array( (int) $cat_id ), 'spicecraft_recipe_category' );

update_post_meta( $recipe_id, '_spicecraft_recipe_prep_minutes', 20 );
update_post_meta( $recipe_id, '_spicecraft_recipe_cook_minutes', 45 );
update_post_meta( $recipe_id, '_spicecraft_recipe_total_minutes', 65 );
update_post_meta( $recipe_id, '_spicecraft_recipe_yield', '4 Servings' );
update_post_meta( $recipe_id, '_spicecraft_recipe_difficulty', 'medium' );
update_post_meta( $recipe_id, '_spicecraft_recipe_dietary', array( 'gluten_free', 'dairy_free' ) );

$ing_groups = array(
	array(
		'group_name' => 'Whole Spice Tempering',
		'items'      => array(
			array(
				'quantity'   => '1',
				'unit'       => 'tsp',
				'ingredient' => 'Tellicherry Black Peppercorns',
				'note'       => 'lightly crushed',
				'product_id' => 24,
			),
			array(
				'quantity'   => '1',
				'unit'       => 'tsp',
				'ingredient' => 'Whole Cumin Seeds',
				'note'       => '',
				'product_id' => 23,
			),
		),
	),
);
update_post_meta( $recipe_id, '_spicecraft_recipe_ingredient_groups', $ing_groups );

$steps = array(
	array(
		'step_number' => 1,
		'heading'     => 'Tempering the Whole Aromatics',
		'instruction' => 'Heat mustard oil in a heavy brass pot until lightly smoking. Introduce crushed Tellicherry peppercorns and cumin seeds until they crackle.',
		'image_id'    => 0,
		'tip'         => 'Maintain low heat to prevent peppercorn oils from scorching.',
	),
	array(
		'step_number' => 2,
		'heading'     => 'Slow Simmer & Reduction',
		'instruction' => 'Add the base gravy and simmer gently for 45 minutes.',
		'image_id'    => 0,
		'tip'         => '',
	),
);
update_post_meta( $recipe_id, '_spicecraft_recipe_instruction_steps', $steps );
update_post_meta( $recipe_id, '_spicecraft_recipe_notes_chef', 'Tellicherry TGSEB grade peppercorns contribute floral heat.' );
update_post_meta( $recipe_id, '_spicecraft_recipe_featured_products', array( 24, 21 ) );
update_post_meta( $recipe_id, '_spicecraft_recipe_linked_product_ids', array( 24, 23, 21 ) );

// Set featured recipe in settings
$settings = get_option( 'spicecraft_recipe_settings', array() );
$settings['featured_recipe_id'] = $recipe_id;
update_option( 'spicecraft_recipe_settings', $settings );

$permalink = get_permalink( $recipe_id );
echo "FIXTURE_READY: {$permalink}\n";
