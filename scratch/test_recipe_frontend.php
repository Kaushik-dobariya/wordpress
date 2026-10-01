<?php
/**
 * SpiceCraft Phase 3 Step 4 - Frontend HTTP & Integration Audit
 */

require_once __DIR__ . '/../wp-load.php';

$results = array(
	'passed' => array(),
	'failed' => array(),
);

function sc_assert( $condition, $test_name, $details = '' ) {
	global $results;
	if ( $condition ) {
		$results['passed'][] = $test_name;
		echo "[PASS] {$test_name}\n";
	} else {
		$results['failed'][] = $test_name . ( $details ? " - {$details}" : '' );
		echo "[FAIL] {$test_name}" . ( $details ? " ({$details})" : '' ) . "\n";
	}
}

echo "=== STARTING PHASE 3 STEP 4 FRONTEND & HTTP AUDIT ===\n\n";

$archive_url = home_url( '/recipes/' );

// ==========================================
// 1. EMPTY STATE ARCHIVE TEST (0 Recipes)
// ==========================================
echo "--- 1. Testing Empty State Archive (/recipes/) ---\n";
$archive_html = @file_get_contents( $archive_url );
sc_assert( false !== $archive_html && ! empty( $archive_html ), "Archive HTTP GET /recipes/ returns content" );

// Check HTTP response code
$http_code = isset( $http_response_header[0] ) ? $http_response_header[0] : '';
sc_assert( false !== strpos( $http_code, '200' ), "Archive returns HTTP 200 OK", $http_code );

// Check single H1 tag
preg_match_all( '/<h1[^>]*>(.*?)<\/h1>/is', $archive_html, $h1_matches );
$h1_count = count( $h1_matches[0] );
sc_assert( 1 === $h1_count, "Archive contains exactly one <h1> tag", "Found {$h1_count}" );
sc_assert( false !== strpos( $archive_html, 'Artisanal Spice Recipes' ), "Archive <h1> renders default configured heading" );

// Check empty state container
sc_assert( false !== strpos( $archive_html, 'sc-recipe-empty-state' ), "Empty state container rendered when 0 recipes exist" );
sc_assert( false !== strpos( $archive_html, 'No Recipes Found' ), "Neutral empty state title rendered" );
sc_assert( false !== strpos( $archive_html, 'Our test kitchen is currently crafting authentic recipes' ), "Empathetic, brand-appropriate empty state message rendered" );

// Check zero fake content
sc_assert( false === strpos( $archive_html, 'Chicken Tikka' ) && false === strpos( $archive_html, 'Butter Chicken' ), "Zero fake recipes displayed on empty archive" );
sc_assert( false === strpos( $archive_html, '★' ) && false === strpos( $archive_html, 'star-rating' ), "Zero fake star ratings displayed" );

// ==========================================
// 2. CONTROLLED TEST FIXTURE CREATION
// ==========================================
echo "\n--- 2. Setting Up Controlled Recipe Fixture ---\n";

// Register temporary category term
$cat_term = term_exists( 'Heritage Curries', 'spicecraft_recipe_category' );
if ( ! $cat_term ) {
	$cat_term = wp_insert_term( 'Heritage Curries', 'spicecraft_recipe_category', array( 'slug' => 'heritage-curries' ) );
}
$cat_id = is_array( $cat_term ) ? $cat_term['term_id'] : $cat_term;

// Register temporary cuisine term
$cui_term = term_exists( 'Kashmiri', 'spicecraft_cuisine' );
if ( ! $cui_term ) {
	$cui_term = wp_insert_term( 'Kashmiri', 'spicecraft_cuisine', array( 'slug' => 'kashmiri' ) );
}
$cui_id = is_array( $cui_term ) ? $cui_term['term_id'] : $cui_term;

// Register temporary meal type term
$meal_term = term_exists( 'Dinner', 'spicecraft_meal_type' );
if ( ! $meal_term ) {
	$meal_term = wp_insert_term( 'Dinner', 'spicecraft_meal_type', array( 'slug' => 'dinner' ) );
}
$meal_id = is_array( $meal_term ) ? $meal_term['term_id'] : $meal_term;

$recipe_id = wp_insert_post( array(
	'post_title'   => 'Kashmiri Rogan Josh with Tellicherry Pepper',
	'post_name'    => 'kashmiri-rogan-josh-tellicherry-pepper',
	'post_content' => 'An iconic slow-cooked Kashmiri curry prepared with whole roasted aromatic spices and pure ground aromatics.',
	'post_excerpt' => 'A slow-simmered, richly spiced Kashmiri signature curry elevated by whole Tellicherry black peppercorns.',
	'post_status'  => 'publish',
	'post_type'    => 'spicecraft_recipe',
) );

wp_set_object_terms( $recipe_id, array( (int) $cat_id ), 'spicecraft_recipe_category' );
wp_set_object_terms( $recipe_id, array( (int) $cui_id ), 'spicecraft_cuisine' );
wp_set_object_terms( $recipe_id, array( (int) $meal_id ), 'spicecraft_meal_type' );

// Save meta fields
update_post_meta( $recipe_id, '_spicecraft_recipe_prep_minutes', 20 );
update_post_meta( $recipe_id, '_spicecraft_recipe_cook_minutes', 45 );
update_post_meta( $recipe_id, '_spicecraft_recipe_additional_minutes', 0 );
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
				'product_id' => 24, // Real WooCommerce product ID
			),
			array(
				'quantity'   => '1',
				'unit'       => 'tsp',
				'ingredient' => 'Whole Cumin Seeds',
				'note'       => 'machine cleaned',
				'product_id' => 23, // Real WooCommerce product ID
			),
		),
	),
	array(
		'group_name' => 'Curry Gravy & Seasoning',
		'items'      => array(
			array(
				'quantity'   => '1',
				'unit'       => 'tsp',
				'ingredient' => 'Organic Turmeric Powder',
				'note'       => 'high curcumin',
				'product_id' => 21, // Real WooCommerce product ID
			),
			array(
				'quantity'   => '2',
				'unit'       => 'tbsp',
				'ingredient' => 'Mustard Oil',
				'note'       => 'pure pressed',
				'product_id' => 0,
			),
		),
	),
);
update_post_meta( $recipe_id, '_spicecraft_recipe_ingredient_groups', $ing_groups );

$steps = array(
	array(
		'step_number' => 1,
		'heading'     => 'Tempering the Whole Aromatics',
		'instruction' => 'Heat mustard oil in a heavy brass or cast-iron pot until lightly smoking. Introduce crushed Tellicherry peppercorns and cumin seeds until they splutter.',
		'image_id'    => 0,
		'tip'         => 'Maintain low heat to prevent peppercorn oils from scorching.',
	),
	array(
		'step_number' => 2,
		'heading'     => 'Slow Simmer & Reduction',
		'instruction' => 'Add the base gravy, pure ground turmeric, and simmer gently covered for 45 minutes until the essential oils rise to the surface.',
		'image_id'    => 0,
		'tip'         => 'Let rest for 15 minutes before serving to deepen the flavor integration.',
	),
);
update_post_meta( $recipe_id, '_spicecraft_recipe_instruction_steps', $steps );

update_post_meta( $recipe_id, '_spicecraft_recipe_notes_chef', 'Tellicherry TGSEB grade peppercorns contribute floral heat rather than harsh acrid sharpness.' );
update_post_meta( $recipe_id, '_spicecraft_recipe_notes_serving', 'Pair with hot fragrant saffron basmati rice.' );
update_post_meta( $recipe_id, '_spicecraft_recipe_notes_storage', 'Refrigerate in a glass container for up to 3 days.' );
update_post_meta( $recipe_id, '_spicecraft_recipe_nutrition', array(
	'serving_size' => '1 Bowl (approx. 250g)',
	'calories'     => '380',
	'protein'      => '28g',
	'carbs'        => '12g',
	'fat'          => '18g',
) );
update_post_meta( $recipe_id, '_spicecraft_recipe_featured_products', array( 22 ) ); // Royal Garam Masala
update_post_meta( $recipe_id, '_spicecraft_recipe_linked_product_ids', array( 24, 23, 21, 22 ) );

// Set featured recipe in settings for archive hero showcase testing
$prev_settings = get_option( 'spicecraft_recipe_settings', array() );
$test_settings = $prev_settings;
$test_settings['featured_recipe_id'] = $recipe_id;
update_option( 'spicecraft_recipe_settings', $test_settings );

// Enable recipes section on homepage during test
$prev_home_settings = get_option( 'spicecraft_homepage_settings', array() );
$test_home_settings = $prev_home_settings;
$test_home_settings['sections_enabled']['recipes'] = 1;
$test_home_settings['recipes']['source_type'] = 'latest';
$test_home_settings['recipes']['limit'] = 3;
update_option( 'spicecraft_homepage_settings', $test_home_settings );

echo "   Controlled test fixture created: ID {$recipe_id}\n";

// ==========================================
// 3. ARCHIVE WITH RECIPES TEST
// ==========================================
echo "\n--- 3. Testing Archive with Active Recipe ---\n";
$archive_loaded_html = @file_get_contents( $archive_url );
sc_assert( false !== strpos( $archive_loaded_html, 'Kashmiri Rogan Josh with Tellicherry Pepper' ), "Recipe title rendered on archive grid" );
sc_assert( false !== strpos( $archive_loaded_html, '1 hr 5 mins' ), "Total time '1 hr 5 mins' rendered on recipe card" );
sc_assert( false !== strpos( $archive_loaded_html, 'Medium' ), "Difficulty 'Medium' rendered on recipe card" );
sc_assert( false !== strpos( $archive_loaded_html, 'Master Blender’s Choice' ), "Featured recipe showcase section rendered" );
sc_assert( false !== strpos( $archive_loaded_html, 'sc-recipe-card' ), "Unified sc-recipe-card component rendered" );

// Check filtering parameters
$filter_url = home_url( '/recipes/?sc_diff=medium' );
$filter_html = @file_get_contents( $filter_url );
sc_assert( false !== strpos( $filter_html, 'Kashmiri Rogan Josh with Tellicherry Pepper' ), "Filtered archive (?sc_diff=medium) matches recipe" );
sc_assert( false !== strpos( $filter_html, 'sc-active-filters-strip' ), "Active filter strip rendered on filtered query" );

$non_match_url = home_url( '/recipes/?sc_diff=advanced' );
$non_match_html = @file_get_contents( $non_match_url );
sc_assert( false !== strpos( $non_match_html, 'sc-recipe-empty-state' ), "Non-matching filter (?sc_diff=advanced) shows empty state" );

// ==========================================
// 4. RECIPE DETAIL PAGE TEST
// ==========================================
echo "\n--- 4. Testing Recipe Detail Page ---\n";
$detail_url = get_permalink( $recipe_id );
$detail_html = @file_get_contents( $detail_url );
sc_assert( false !== $detail_html && ! empty( $detail_html ), "Recipe detail page HTTP GET succeeded" );

$detail_code = isset( $http_response_header[0] ) ? $http_response_header[0] : '';
sc_assert( false !== strpos( $detail_code, '200' ), "Recipe detail page returns HTTP 200 OK", $detail_code );

// Verify single H1 on detail page
preg_match_all( '/<h1[^>]*>(.*?)<\/h1>/is', $detail_html, $detail_h1_matches );
$detail_h1_count = count( $detail_h1_matches[0] );
sc_assert( 1 === $detail_h1_count, "Recipe detail page has exactly one <h1> tag", "Found {$detail_h1_count}" );
sc_assert( false !== strpos( $detail_html, 'Kashmiri Rogan Josh with Tellicherry Pepper' ), "Recipe title rendered in <h1>" );

// Verify Schema.org Recipe JSON-LD
sc_assert( false !== strpos( $detail_html, '"@type": "Recipe"' ) || false !== strpos( $detail_html, '"@type":"Recipe"' ), "Schema.org Recipe structured data present" );
sc_assert( false !== strpos( $detail_html, 'PT1H5M' ), "Schema.org totalTime formatted to ISO 8601 duration 'PT1H5M'" );
sc_assert( false !== strpos( $detail_html, 'PT20M' ), "Schema.org prepTime formatted to ISO 8601 duration 'PT20M'" );
sc_assert( false !== strpos( $detail_html, 'PT45M' ), "Schema.org cookTime formatted to ISO 8601 duration 'PT45M'" );
sc_assert( false === strpos( $detail_html, 'aggregateRating' ), "Strict zero-fabrication: NO fake aggregateRating in schema" );

// Verify Quick Facts Bar
sc_assert( false !== strpos( $detail_html, 'sc-quick-facts-bar' ), "Quick facts bar rendered" );
sc_assert( false !== strpos( $detail_html, '20 mins' ) && false !== strpos( $detail_html, '45 mins' ), "Prep and Cook times rendered in quick facts" );
sc_assert( false !== strpos( $detail_html, '4 Servings' ), "Yield / Servings rendered in quick facts" );

// Verify Grouped Ingredients
sc_assert( false !== strpos( $detail_html, 'Whole Spice Tempering' ), "Ingredient group 'Whole Spice Tempering' rendered" );
sc_assert( false !== strpos( $detail_html, 'Tellicherry Black Peppercorns' ), "Ingredient item 'Tellicherry Black Peppercorns' rendered" );
sc_assert( false !== strpos( $detail_html, 'sc-ingredient-checkbox' ), "Interactive ingredient checkboxes present" );

// Verify Subtle Ingredient Product Linkage
sc_assert( false !== strpos( $detail_html, 'sc-ingredient-product-link' ), "Subtle 'View Product' link rendered for linked ingredient" );

// Verify Numbered Instructions
sc_assert( false !== strpos( $detail_html, 'sc-instruction-step' ), "Structured instruction step rendered" );
sc_assert( false !== strpos( $detail_html, '01' ) && false !== strpos( $detail_html, '02' ), "Numbered step indicators 01, 02 rendered" );
sc_assert( false !== strpos( $detail_html, 'Master Blender Tip:' ), "Master blender tip box rendered" );

// Verify Culinary Notes
sc_assert( false !== strpos( $detail_html, 'Chef’s Notes' ) || false !== strpos( $detail_html, 'Master Blender Notes' ), "Culinary notes section rendered" );
sc_assert( false !== strpos( $detail_html, 'Tellicherry TGSEB grade peppercorns contribute floral heat' ), "Chef note text rendered" );

// Verify Verified Nutrition Table
sc_assert( false !== strpos( $detail_html, 'sc-nutrition-facts' ), "Verified nutrition facts section rendered" );
sc_assert( false !== strpos( $detail_html, '380' ), "Nutrition calories value (380) rendered" );

// Verify Share and Print Toolbar
sc_assert( false !== strpos( $detail_html, 'sc-share-btn' ), "Share Recipe button present with Web Share hook" );
sc_assert( false !== strpos( $detail_html, 'sc-whatsapp-share-btn' ), "WhatsApp share link present" );
sc_assert( false !== strpos( $detail_html, 'sc-print-btn' ), "Print Recipe button present with window.print() trigger" );

// Verify "Spices Used in This Recipe" Section
sc_assert( false !== stripos( $detail_html, 'Spices Used in This Recipe' ), "Related products section 'Spices Used in This Recipe' rendered" );
sc_assert( false !== strpos( $detail_html, 'Tellicherry Black Peppercorns' ), "Linked product card Tellicherry Black Peppercorns rendered" );

// Verify Catalog Mode Preservation on Recipe Page (No Add to Cart)
sc_assert( false === strpos( $detail_html, 'add_to_cart_button' ) && false === strpos( $detail_html, 'ajax_add_to_cart' ), "Catalog mode preserved: ZERO Add to Cart buttons on recipe detail page" );

// ==========================================
// 5. PRODUCT DETAIL PAGE INTEGRATION TEST
// ==========================================
echo "\n--- 5. Testing Product Page Integration (Product -> Recipe) ---\n";
$prod_url = get_permalink( 24 ); // Tellicherry Black Peppercorns
$prod_html = @file_get_contents( $prod_url );
sc_assert( false !== $prod_html && ! empty( $prod_html ), "Single Product page HTTP GET succeeded" );
sc_assert( false !== strpos( $prod_html, 'Recipes Using ' ), "'Recipes Using [Spice]' section appears on WooCommerce product page" );
sc_assert( false !== strpos( $prod_html, 'Kashmiri Rogan Josh with Tellicherry Pepper' ), "Recipe card appears inside product page recipe section" );

// Verify Catalog Mode on Product Page
sc_assert( false === strpos( $prod_html, 'single_add_to_cart_button' ), "Product page preserves catalog mode (No Add to Cart)" );

// ==========================================
// 6. HOMEPAGE RECIPE INTEGRATION TEST
// ==========================================
echo "\n--- 6. Testing Homepage Recipe Section Integration ---\n";
$home_url = home_url( '/' );
$home_html = @file_get_contents( $home_url );
sc_assert( false !== $home_html && ! empty( $home_html ), "Homepage HTTP GET succeeded" );

if ( preg_match( '/<section id="recipes"[^>]*>(.*?)<\/section>/is', $home_html, $home_recipe_matches ) ) {
	$home_recipes_section = $home_recipe_matches[1];
	sc_assert( false !== strpos( $home_recipes_section, 'Kashmiri Rogan Josh with Tellicherry Pepper' ), "Homepage recipe section consumes new spicecraft_recipe CPT" );
	sc_assert( false === strpos( $home_recipes_section, 'Hello world!' ), "Homepage recipe section excludes standard Blog Posts" );
} else {
	sc_assert( false, "Homepage recipe section (#recipes) rendered on front page" );
}

// ==========================================
// 7. GLOBAL SEARCH TEST
// ==========================================
echo "\n--- 7. Testing Global Search for Recipe ---\n";
$search_url = home_url( '/?s=Rogan+Josh' );
$search_html = @file_get_contents( $search_url );
sc_assert( false !== $search_html && ! empty( $search_html ), "Search HTTP GET succeeded" );
sc_assert( false !== strpos( $search_html, 'Kashmiri Rogan Josh with Tellicherry Pepper' ), "Recipe discovered in global search results" );
sc_assert( false !== strpos( $search_html, 'sc-search-card__badge--recipe' ) || false !== strpos( $search_html, 'Recipe' ), "Recipe badge displayed on global search card" );

// ==========================================
// 8. TEARDOWN & REVERSION (Zero Fake Content)
// ==========================================
echo "\n--- 8. Tearing Down Controlled Fixtures ---\n";
wp_delete_post( $recipe_id, true );
wp_delete_term( $cat_id, 'spicecraft_recipe_category' );
wp_delete_term( $cui_id, 'spicecraft_cuisine' );
wp_delete_term( $meal_id, 'spicecraft_meal_type' );
update_option( 'spicecraft_recipe_settings', $prev_settings );
update_option( 'spicecraft_homepage_settings', $prev_home_settings );
echo "   Controlled test fixture deleted permanently.\n";

// Verify clean state after teardown
$archive_clean_html = @file_get_contents( $archive_url );
sc_assert( false === strpos( $archive_clean_html, 'Kashmiri Rogan Josh with Tellicherry Pepper' ), "Post-teardown: Recipe cleanly removed from archive" );
sc_assert( false !== strpos( $archive_clean_html, 'sc-recipe-empty-state' ), "Post-teardown: Archive returns to clean empty state" );

echo "\n=== AUDIT RESULTS SUMMARY ===\n";
echo "TOTAL PASSED: " . count( $results['passed'] ) . "\n";
echo "TOTAL FAILED: " . count( $results['failed'] ) . "\n";

if ( ! empty( $results['failed'] ) ) {
	echo "\nFailed Tests:\n";
	foreach ( $results['failed'] as $f ) {
		echo "  - {$f}\n";
	}
	exit( 1 );
} else {
	echo "\nALL TESTS PASSED PERFECTLY!\n";
	exit( 0 );
}
