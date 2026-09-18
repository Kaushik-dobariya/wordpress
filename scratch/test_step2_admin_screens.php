<?php
/**
 * Test Step 2: Admin Screens Rendering Across All Tabs
 */
require_once dirname( __DIR__ ) . '/wp-load.php';
require_once ABSPATH . 'wp-admin/includes/template.php';
require_once ABSPATH . 'wp-admin/includes/screen.php';
require_once ABSPATH . 'wp-admin/includes/plugin.php';

// Set current user as administrator (ID 1)
wp_set_current_user( 1 );

$mfg_settings = SpiceCraft_Manufacturing_Settings::get_instance();
$q_settings   = SpiceCraft_Quality_Settings::get_instance();

$mfg_tabs = array( 'order', 'hero', 'facility', 'equipment', 'hygiene', 'gallery', 'trust', 'cta' );
$q_tabs   = array( 'order', 'hero', 'principles', 'testing', 'sourcing', 'traceability', 'trust', 'cta' );

echo "=== Testing Manufacturing Admin Tabs ===\n";
foreach ( $mfg_tabs as $tab ) {
	$_GET['page'] = 'spicecraft-manufacturing';
	$_GET['tab']  = $tab;
	ob_start();
	$mfg_settings->render_page();
	$output = ob_get_clean();
	$has_wrap = strpos( $output, 'spicecraft-settings-wrap' ) !== false;
	$has_submit = strpos( $output, 'submit' ) !== false;
	if ( $has_wrap && $has_submit ) {
		echo " [PASS] Manufacturing Tab '{$tab}' rendered successfully (" . strlen( $output ) . " bytes)\n";
	} else {
		echo " [FAIL] Manufacturing Tab '{$tab}' render incomplete\n";
		exit( 1 );
	}
}

echo "\n=== Testing Quality & Sourcing Admin Tabs ===\n";
foreach ( $q_tabs as $tab ) {
	$_GET['page'] = 'spicecraft-quality';
	$_GET['tab']  = $tab;
	ob_start();
	$q_settings->render_page();
	$output = ob_get_clean();
	$has_wrap = strpos( $output, 'spicecraft-settings-wrap' ) !== false;
	$has_submit = strpos( $output, 'submit' ) !== false;
	if ( $has_wrap && $has_submit ) {
		echo " [PASS] Quality Tab '{$tab}' rendered successfully (" . strlen( $output ) . " bytes)\n";
	} else {
		echo " [FAIL] Quality Tab '{$tab}' render incomplete\n";
		exit( 1 );
	}
}

echo "\nAll 16 admin tabs rendered with 0 errors!\n";
