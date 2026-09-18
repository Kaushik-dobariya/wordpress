<?php
/**
 * Test Step 2: Frontend Empty State Verification
 */
require_once dirname( __DIR__ ) . '/wp-load.php';

$mfg_html = file_get_contents( 'http://localhost/manufacturing/' );
$q_html   = file_get_contents( 'http://localhost/quality/' );

echo "=== 1. Manufacturing Empty State ===\n";
echo "Response Length: " . strlen( $mfg_html ) . " bytes\n";
if ( strpos( $mfg_html, 'sc-mfg-empty' ) !== false ) {
	echo " [PASS] 'sc-mfg-empty' container present\n";
} else {
	echo " [FAIL] 'sc-mfg-empty' container missing\n";
}

if ( strpos( $mfg_html, 'Manufacturing Page Setup in Progress' ) !== false ) {
	echo " [PASS] Clean fallback notice displayed\n";
} else {
	echo " [FAIL] Fallback notice missing\n";
}

// Ensure no decorative empty sections rendered
$empty_sections = array( 'sc-mfg-hero', 'sc-mfg-intro', 'sc-mfg-facility', 'sc-mfg-process', 'sc-mfg-equipment', 'sc-mfg-hygiene', 'sc-mfg-packaging', 'sc-mfg-warehousing', 'sc-mfg-stats', 'sc-mfg-gallery' );
$found_unwanted = false;
foreach ( $empty_sections as $sec_id ) {
	if ( strpos( $mfg_html, $sec_id ) !== false ) {
		echo " [FAIL] Section {$sec_id} rendered despite empty data!\n";
		$found_unwanted = true;
	}
}
if ( ! $found_unwanted ) {
	echo " [PASS] All unpopulated manufacturing sections are cleanly suppressed\n";
}

echo "\n=== 2. Quality & Sourcing Empty State ===\n";
echo "Response Length: " . strlen( $q_html ) . " bytes\n";
if ( strpos( $q_html, 'sc-quality-empty' ) !== false ) {
	echo " [PASS] 'sc-quality-empty' container present\n";
} else {
	echo " [FAIL] 'sc-quality-empty' container missing\n";
}

if ( strpos( $q_html, 'Quality &amp; Sourcing Setup in Progress' ) !== false || strpos( $q_html, 'Quality & Sourcing Setup in Progress' ) !== false ) {
	echo " [PASS] Clean fallback notice displayed\n";
} else {
	echo " [FAIL] Fallback notice missing\n";
}

// Ensure no decorative empty sections rendered
$empty_q_sections = array( 'sc-quality-hero', 'sc-quality-intro', 'sc-quality-principles', 'sc-quality-process', 'sc-quality-testing', 'sc-quality-sourcing', 'sc-quality-regions', 'sc-quality-raw-materials', 'sc-quality-traceability', 'sc-quality-food-safety', 'sc-quality-stats', 'sc-quality-gallery' );
$found_unwanted_q = false;
foreach ( $empty_q_sections as $sec_id ) {
	if ( strpos( $q_html, $sec_id ) !== false ) {
		echo " [FAIL] Section {$sec_id} rendered despite empty data!\n";
		$found_unwanted_q = true;
	}
}
if ( ! $found_unwanted_q ) {
	echo " [PASS] All unpopulated quality sections are cleanly suppressed\n";
}
