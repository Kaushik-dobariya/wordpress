<?php
/**
 * Test Step 2: Backend Architecture, Helpers, Sanitization & Data Guards
 */
require_once dirname( __DIR__ ) . '/wp-load.php';

$results = array(
	'passed' => 0,
	'failed' => 0,
	'errors' => array(),
);

function sc_assert( $condition, $message, &$results ) {
	if ( $condition ) {
		$results['passed']++;
		echo " [PASS] {$message}\n";
	} else {
		$results['failed']++;
		$results['errors'][] = $message;
		echo " [FAIL] {$message}\n";
	}
}

echo "=== 1. Checking Helper Functions Existence ===\n";
$mfg_functions = array(
	'spicecraft_get_manufacturing_default_settings',
	'spicecraft_get_manufacturing_settings',
	'spicecraft_update_manufacturing_settings',
	'spicecraft_get_manufacturing_section',
	'spicecraft_is_manufacturing_section_enabled',
	'spicecraft_get_manufacturing_section_order',
	'spicecraft_manufacturing_section_has_data',
	'spicecraft_get_manufacturing_active_sections',
);
foreach ( $mfg_functions as $fn ) {
	sc_assert( function_exists( $fn ), "Function {$fn}() exists", $results );
}

$quality_functions = array(
	'spicecraft_get_quality_default_settings',
	'spicecraft_get_quality_settings',
	'spicecraft_update_quality_settings',
	'spicecraft_get_quality_section',
	'spicecraft_is_quality_section_enabled',
	'spicecraft_get_quality_section_order',
	'spicecraft_quality_section_has_data',
	'spicecraft_get_quality_active_sections',
	'spicecraft_get_testing_context_label',
);
foreach ( $quality_functions as $fn ) {
	sc_assert( function_exists( $fn ), "Function {$fn}() exists", $results );
}

echo "\n=== 2. Checking Settings Classes & Instances ===\n";
sc_assert( class_exists( 'SpiceCraft_Manufacturing_Settings' ), "Class SpiceCraft_Manufacturing_Settings exists", $results );
$mfg_instance = SpiceCraft_Manufacturing_Settings::get_instance();
sc_assert( $mfg_instance instanceof SpiceCraft_Manufacturing_Settings, "SpiceCraft_Manufacturing_Settings singleton instantiated", $results );

sc_assert( class_exists( 'SpiceCraft_Quality_Settings' ), "Class SpiceCraft_Quality_Settings exists", $results );
$q_instance = SpiceCraft_Quality_Settings::get_instance();
sc_assert( $q_instance instanceof SpiceCraft_Quality_Settings, "SpiceCraft_Quality_Settings singleton instantiated", $results );

echo "\n=== 3. Testing Zero-Fabricated-Content Defaults ===\n";
$mfg_defs = spicecraft_get_manufacturing_default_settings();
sc_assert( empty( $mfg_defs['hero']['heading'] ), "Mfg default hero heading is empty", $results );
sc_assert( empty( $mfg_defs['statistics']['items'] ), "Mfg default statistics array is empty", $results );
sc_assert( empty( $mfg_defs['process']['items'] ), "Mfg default process items array is empty", $results );
sc_assert( empty( $mfg_defs['equipment']['items'] ), "Mfg default equipment items array is empty", $results );
sc_assert( empty( $mfg_defs['gallery']['attachment_ids'] ), "Mfg default gallery attachment IDs array is empty", $results );

$q_defs = spicecraft_get_quality_default_settings();
sc_assert( empty( $q_defs['hero']['heading'] ), "Quality default hero heading is empty", $results );
sc_assert( empty( $q_defs['statistics']['items'] ), "Quality default statistics array is empty", $results );
sc_assert( empty( $q_defs['principles']['items'] ), "Quality default principles array is empty", $results );
sc_assert( empty( $q_defs['regions']['items'] ), "Quality default regions array is empty", $results );
sc_assert( 'not_specified' === $q_defs['testing']['testing_context'], "Quality default testing context is 'not_specified'", $results );

echo "\n=== 4. Testing Meaningful Data Guards (Empty Section Suppression) ===\n";
sc_assert( false === spicecraft_manufacturing_section_has_data( 'hero', array() ), "Empty mfg hero has no data", $results );
sc_assert( false === spicecraft_manufacturing_section_has_data( 'statistics', array( 'items' => array() ) ), "Empty mfg statistics has no data", $results );
sc_assert( false === spicecraft_manufacturing_section_has_data( 'gallery', array( 'attachment_ids' => array() ) ), "Empty mfg gallery has no data", $results );
sc_assert( true === spicecraft_manufacturing_section_has_data( 'hero', array( 'heading' => 'Spice Processing' ) ), "Populated mfg hero has data", $results );

sc_assert( false === spicecraft_quality_section_has_data( 'hero', array() ), "Empty quality hero has no data", $results );
sc_assert( false === spicecraft_quality_section_has_data( 'testing', array( 'items' => array() ) ), "Empty quality testing has no data", $results );
sc_assert( false === spicecraft_quality_section_has_data( 'regions', array( 'items' => array() ) ), "Empty quality regions has no data", $results );
sc_assert( true === spicecraft_quality_section_has_data( 'hero', array( 'heading' => 'Certified Quality' ) ), "Populated quality hero has data", $results );

echo "\n=== 5. Testing Testing Context Integrity ===\n";
sc_assert( '' === spicecraft_get_testing_context_label( 'not_specified' ), "'not_specified' testing context outputs empty string", $results );
sc_assert( ! empty( spicecraft_get_testing_context_label( 'in_house' ) ), "'in_house' testing context has label", $results );
sc_assert( ! empty( spicecraft_get_testing_context_label( 'external' ) ), "'external' testing context has label", $results );
sc_assert( ! empty( spicecraft_get_testing_context_label( 'combination' ) ), "'combination' testing context has label", $results );

echo "\n=== 6. Testing Sanitization Primitives ===\n";
$dummy_stats = array(
	array( 'value' => '10', 'suffix' => 'MT', 'label' => 'Milling Capacity', 'order' => 20 ),
	array( 'value' => '', 'suffix' => '', 'label' => '', 'order' => 5 ), // empty item, must be stripped
	array( 'value' => '99', 'suffix' => '%', 'label' => 'Purity Target', 'order' => 10 ),
);
$clean_stats = spicecraft_sanitize_stat_items( $dummy_stats );
sc_assert( count( $clean_stats ) === 2, "Sanitize stats stripped empty item (count 2)", $results );
sc_assert( $clean_stats[0]['value'] === '99' && $clean_stats[1]['value'] === '10', "Sanitize stats sorted by order ascending", $results );

$dummy_specs = array(
	array(
		'name'        => 'Stainless Steel Grinder',
		'description' => 'Precision grinding',
		'order'       => 10,
		'specs'       => array(
			array( 'label' => 'Material', 'value' => 'SS 316' ),
			array( 'label' => '', 'value' => '' ), // empty spec row, must be stripped
		),
	),
);
$clean_eq = spicecraft_sanitize_equipment_items( $dummy_specs );
sc_assert( count( $clean_eq ) === 1, "Sanitize equipment retained valid item", $results );
sc_assert( count( $clean_eq[0]['specs'] ) === 1, "Sanitize equipment stripped empty spec row", $results );

$clean_gal = spicecraft_sanitize_gallery_ids( array( '12', 0, 'abc', 45, '12' ) );
sc_assert( in_array( 12, $clean_gal, true ) && in_array( 45, $clean_gal, true ) && ! in_array( 0, $clean_gal, true ), "Sanitize gallery filtered to positive integer IDs", $results );

echo "\n============================================\n";
echo "Total Passed: {$results['passed']}, Total Failed: {$results['failed']}\n";
echo "============================================\n";
if ( $results['failed'] > 0 ) {
	exit( 1 );
}
