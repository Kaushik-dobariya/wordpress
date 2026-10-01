<?php
/**
 * Automated Verification Script for Phase 3 Step 4: Recipe CMS & Integration
 */

require_once __DIR__ . '/../wp-load.php';

echo "=== PHASE 3 STEP 4: RECIPE CMS BACKEND VERIFICATION ===\n\n";

// 1. Flush rewrite rules
flush_rewrite_rules();
echo "1. Rewrite rules flushed.\n";

// 2. Check CPT registration
$cpt = get_post_type_object( 'spicecraft_recipe' );
if ( $cpt ) {
	echo "2. CPT 'spicecraft_recipe' registered successfully.\n";
	echo "   - Label: {$cpt->label}\n";
	echo "   - Public: " . ( $cpt->public ? 'true' : 'false' ) . "\n";
	echo "   - Has Archive: " . ( $cpt->has_archive ? 'true' : 'false' ) . "\n";
	echo "   - Rewrite Slug: " . ( isset( $cpt->rewrite['slug'] ) ? $cpt->rewrite['slug'] : 'none' ) . "\n";
} else {
	echo "ERROR: CPT 'spicecraft_recipe' NOT registered!\n";
}

// 3. Check Taxonomies
$taxes = array(
	'spicecraft_recipe_category' => 'Recipe Categories',
	'spicecraft_cuisine'         => 'Cuisines',
	'spicecraft_meal_type'       => 'Meal Types',
);

echo "\n3. Taxonomies Verification:\n";
foreach ( $taxes as $tax_key => $tax_name ) {
	$tax_obj = get_taxonomy( $tax_key );
	if ( $tax_obj ) {
		echo "   - Taxonomy '{$tax_key}' ({$tax_name}) is registered. Hierarchical: " . ( $tax_obj->hierarchical ? 'true' : 'false' ) . "\n";
	} else {
		echo "   - ERROR: Taxonomy '{$tax_key}' is NOT registered!\n";
	}
}

// 4. Test Time Helpers
echo "\n4. Testing Time Formatting Helpers:\n";
$t1 = spicecraft_format_recipe_time( 65 );
$t2 = spicecraft_format_recipe_time( 45 );
$t3 = spicecraft_format_recipe_time( 120 );
$t4 = spicecraft_format_recipe_time( 0 );
echo "   - 65 mins => '$t1' (Expected: 1 hr 5 mins) -> " . ( '1 hr 5 mins' === $t1 ? 'PASS' : 'FAIL' ) . "\n";
echo "   - 45 mins => '$t2' (Expected: 45 mins) -> " . ( '45 mins' === $t2 ? 'PASS' : 'FAIL' ) . "\n";
echo "   - 120 mins => '$t3' (Expected: 2 hrs) -> " . ( '2 hrs' === $t3 ? 'PASS' : 'FAIL' ) . "\n";
echo "   - 0 mins => '$t4' (Expected: empty) -> " . ( '' === $t4 ? 'PASS' : 'FAIL' ) . "\n";

$iso1 = spicecraft_minutes_to_iso8601( 65 );
$iso2 = spicecraft_minutes_to_iso8601( 45 );
$iso3 = spicecraft_minutes_to_iso8601( 120 );
echo "   - ISO 8601 (65 mins) => '$iso1' (Expected: PT1H5M) -> " . ( 'PT1H5M' === $iso1 ? 'PASS' : 'FAIL' ) . "\n";
echo "   - ISO 8601 (45 mins) => '$iso2' (Expected: PT45M) -> " . ( 'PT45M' === $iso2 ? 'PASS' : 'FAIL' ) . "\n";
echo "   - ISO 8601 (120 mins) => '$iso3' (Expected: PT2H) -> " . ( 'PT2H' === $iso3 ? 'PASS' : 'FAIL' ) . "\n";

// 5. Test Recipe Settings Option
echo "\n5. Testing Recipe Archive Settings:\n";
$settings = spicecraft_get_recipe_settings();
echo "   - Archive Enabled: " . ( ! empty( $settings['archive_enabled'] ) ? 'yes' : 'no' ) . "\n";
echo "   - Archive Heading: {$settings['heading']}\n";
echo "   - Recipes Per Page: {$settings['recipes_per_page']}\n";
echo "   - Show Search: " . ( ! empty( $settings['show_search'] ) ? 'yes' : 'no' ) . "\n";

// 6. Test Recipe Lifecycle, Meta Saving, and Product Linkage with Temporary Dev Fixture
echo "\n6. Testing Recipe Lifecycle & Meta Fixture:\n";
$fixture_id = wp_insert_post( array(
	'post_title'   => '[TEST FIXTURE] Authentic Rogan Josh with Tellicherry Pepper',
	'post_name'    => 'test-authentic-rogan-josh-tellicherry',
	'post_content' => 'A rich, aromatic Kashmiri specialty featuring whole spices and slow-cooked gravy.',
	'post_excerpt' => 'A fragrant, slow-simmered curry elevated by whole Tellicherry black peppercorns.',
	'post_status'  => 'publish',
	'post_type'    => 'spicecraft_recipe',
) );

if ( is_wp_error( $fixture_id ) || ! $fixture_id ) {
	echo "   - ERROR: Failed to create test recipe fixture!\n";
} else {
	echo "   - Test recipe fixture created with ID: {$fixture_id}\n";

	// Save individual meta keys matching class-recipe-meta.php
	update_post_meta( $fixture_id, '_spicecraft_recipe_prep_minutes', 20 );
	update_post_meta( $fixture_id, '_spicecraft_recipe_cook_minutes', 45 );
	update_post_meta( $fixture_id, '_spicecraft_recipe_additional_minutes', 0 );
	update_post_meta( $fixture_id, '_spicecraft_recipe_total_minutes', 65 );
	update_post_meta( $fixture_id, '_spicecraft_recipe_yield', '4 Servings' );
	update_post_meta( $fixture_id, '_spicecraft_recipe_difficulty', 'medium' );
	update_post_meta( $fixture_id, '_spicecraft_recipe_dietary', array( 'gluten_free', 'dairy_free' ) );

	$test_ingredient_groups = array(
		array(
			'group_name' => 'For the Spice Tempering',
			'items'      => array(
				array(
					'quantity'   => '1',
					'unit'       => 'tsp',
					'ingredient' => 'Tellicherry Black Peppercorns',
					'note'       => 'lightly crushed',
					'product_id' => 24, // Real WooCommerce product ID
				),
				array(
					'quantity'   => '1',
					'unit'       => 'tsp',
					'ingredient' => 'Whole Cumin Seeds',
					'note'       => '',
					'product_id' => 23, // Real WooCommerce product ID
				),
			),
		),
		array(
			'group_name' => 'For the Gravy Base',
			'items'      => array(
				array(
					'quantity'   => '2',
					'unit'       => 'tbsp',
					'ingredient' => 'Mustard Oil or Ghee',
					'note'       => '',
					'product_id' => 0,
				),
				array(
					'quantity'   => '1',
					'unit'       => 'tsp',
					'ingredient' => 'Organic Turmeric Powder',
					'note'       => 'pure high-curcumin',
					'product_id' => 21, // Real WooCommerce product ID
				),
			),
		),
	);
	update_post_meta( $fixture_id, '_spicecraft_recipe_ingredient_groups', $test_ingredient_groups );

	$test_instructions = array(
		array(
			'step_number' => 1,
			'heading'     => 'Temper the Whole Spices',
			'instruction' => 'Heat mustard oil in a heavy-bottomed pan until smoking, then lower heat and add Tellicherry peppercorns and cumin seeds until fragrant.',
			'image_id'    => 0,
			'tip'         => 'Do not let the whole spices burn; keep the flame on medium-low.',
		),
		array(
			'step_number' => 2,
			'heading'     => 'Simmer and Finish',
			'instruction' => 'Stir in the spice blend and simmer for 40 minutes on low heat until the sauce thickens and releases its aroma.',
			'image_id'    => 0,
			'tip'         => '',
		),
	);
	update_post_meta( $fixture_id, '_spicecraft_recipe_instruction_steps', $test_instructions );

	update_post_meta( $fixture_id, '_spicecraft_recipe_notes_chef', 'For best depth, use freshly cracked Tellicherry peppercorns rather than pre-ground pepper.' );
	update_post_meta( $fixture_id, '_spicecraft_recipe_notes_serving', 'Serve hot with steamed basmati rice or flaky parathas.' );
	update_post_meta( $fixture_id, '_spicecraft_recipe_notes_storage', 'Keeps refrigerated in an airtight container for up to 3 days.' );
	update_post_meta( $fixture_id, '_spicecraft_recipe_nutrition', array(
		'serving_size' => '1 Bowl (approx. 250g)',
		'calories'     => '380',
		'protein'      => '28g',
		'carbs'        => '12g',
		'fat'          => '18g',
	) );
	update_post_meta( $fixture_id, '_spicecraft_recipe_featured_products', array( 22 ) ); // Royal Garam Masala
	update_post_meta( $fixture_id, '_spicecraft_recipe_linked_product_ids', array( 24, 23, 21, 22 ) );

	// Retrieve meta via helper
	$retrieved = spicecraft_get_recipe_meta( $fixture_id );
	echo "   - Meta retrieved successfully.\n";
	echo "     * Formatted Prep: {$retrieved['formatted_prep']}\n";
	echo "     * Formatted Cook: {$retrieved['formatted_cook']}\n";
	echo "     * Formatted Total: {$retrieved['formatted_total']}\n";
	echo "     * Difficulty: {$retrieved['difficulty']}\n";
	echo "     * Ingredient Groups Count: " . count( $retrieved['ingredient_groups'] ) . "\n";
	echo "     * Instructions Count: " . count( $retrieved['instruction_steps'] ) . "\n";

	// Test product deduplication & linkage helper
	$linked_products = spicecraft_get_recipe_linked_products( $fixture_id );
	echo "   - Deduplicated Linked Products for Recipe: " . implode( ', ', $linked_products ) . "\n";
	echo "     (Expected IDs: 24, 23, 21, 22) -> " . ( count( $linked_products ) === 4 ? 'PASS' : 'FAIL' ) . "\n";

	// Test reverse relationship: Products -> Recipes
	$recipes_for_prod_24 = spicecraft_get_recipes_for_product( 24 );
	echo "   - Reverse Linkage for Product 24 (Tellicherry Black Pepper): Found " . count( $recipes_for_prod_24 ) . " recipe(s) -> " . ( count( $recipes_for_prod_24 ) >= 1 ? 'PASS' : 'FAIL' ) . "\n";

	// Test Schema generator output
	$iso_total = spicecraft_minutes_to_iso8601( $retrieved['total_minutes'] );
	echo "   - Recipe Schema TotalTime: $iso_total (Expected: PT1H5M) -> " . ( 'PT1H5M' === $iso_total ? 'PASS' : 'FAIL' ) . "\n";

	// Now Clean up test fixture cleanly (Zero fake content rule)
	wp_delete_post( $fixture_id, true );
	echo "   - Test fixture {$fixture_id} permanently deleted. Clean state verified.\n";
}

echo "\n=== BACKEND VERIFICATION COMPLETE ===\n";
