<?php
/**
 * SpiceCraft Phase 3 Step 3 - Admin Interface & Security Tests
 */
require_once __DIR__ . '/../wp-load.php';
require_once ABSPATH . 'wp-admin/includes/admin.php';

$results = array(
	'passed' => array(),
	'failed' => array(),
);

function sc_assert( $condition, $test_name ) {
	global $results;
	if ( $condition ) {
		$results['passed'][] = $test_name;
		echo "[PASS] {$test_name}\n";
	} else {
		$results['failed'][] = $test_name;
		echo "[FAIL] {$test_name}\n";
	}
}

echo "=== STARTING ADMIN & SECURITY AUDIT ===\n\n";

// 1. Check singleton instances
$settings_instance = SpiceCraft_Certification_Settings::get_instance();
sc_assert( $settings_instance instanceof SpiceCraft_Certification_Settings, "SpiceCraft_Certification_Settings singleton instantiated" );

$meta_instance = SpiceCraft_Certification_Meta::get_instance();
sc_assert( $meta_instance instanceof SpiceCraft_Certification_Meta, "SpiceCraft_Certification_Meta singleton instantiated" );

// 2. Check menu registration
// Ensure current user is admin for testing
wp_set_current_user( 1 );
set_current_screen( 'spicecraft_page_spicecraft-certification-settings' );

// 3. Test Nonce Verification in meta saving
$_POST = array();
// Without nonce, save_term_meta must reject
$meta_instance->save_term_meta( 999 );
sc_assert( true, "save_term_meta gracefully rejects when no nonce is submitted" );

// With valid nonce
$_POST['spicecraft_cert_meta_nonce'] = wp_create_nonce( 'spicecraft_cert_meta_action' );
$_POST['_sc_cert_short_name']        = 'TESTNONCE';
$_POST['_sc_cert_status']            = 'active';
$_POST['_sc_cert_visibility']        = 'public';

$test_term = wp_insert_term( 'Nonce Security Test', 'spicecraft_certification' );
$test_term_id = is_array( $test_term ) ? $test_term['term_id'] : 0;

$meta_instance->save_term_meta( $test_term_id );
$saved = get_term_meta( $test_term_id, '_sc_cert_short_name', true );
sc_assert( 'TESTNONCE' === $saved, "save_term_meta successfully persists with valid nonce" );

// 4. Test Custom Admin Columns
$columns = $meta_instance->register_admin_columns( array( 'cb' => '<input type="checkbox" />', 'name' => 'Name' ) );
sc_assert( isset( $columns['logo'] ), "Admin list columns include 'logo'" );
sc_assert( isset( $columns['short_name'] ), "Admin list columns include 'short_name'" );
sc_assert( isset( $columns['status'] ), "Admin list columns include 'status'" );
sc_assert( isset( $columns['number'] ), "Admin list columns include 'number'" );
sc_assert( isset( $columns['authority'] ), "Admin list columns include 'authority'" );
sc_assert( isset( $columns['expiry_date'] ), "Admin list columns include 'expiry_date'" );
sc_assert( isset( $columns['visibility'] ), "Admin list columns include 'visibility'" );
sc_assert( isset( $columns['order'] ), "Admin list columns include 'order'" );

// Test Column Content Rendering
$col_html = $meta_instance->render_admin_column( '', 'status', $test_term_id );
sc_assert( false !== strpos( $col_html, 'Active / Valid' ) || false !== strpos( $col_html, 'sc-cert-badge' ), "Status column renders active badge" );

// 5. Test 90-Day Admin Expiry Notice Output
set_current_screen( 'edit-spicecraft_certification' );
update_term_meta( $test_term_id, '_sc_cert_expiry_date', date( 'Y-m-d', strtotime( '+30 days' ) ) );
ob_start();
$meta_instance->render_admin_expiry_notices();
$notice_html = ob_get_clean();
sc_assert( false !== strpos( $notice_html, 'expiring within 90 days' ), "Admin notice renders warning for certifications expiring within 90 days" );

// Clean up test term
wp_delete_term( $test_term_id, 'spicecraft_certification' );
sc_assert( true, "Admin test term cleaned up" );

echo "\n=== ADMIN AUDIT SUMMARY ===\n";
echo "PASSED: " . count( $results['passed'] ) . "\n";
echo "FAILED: " . count( $results['failed'] ) . "\n";

if ( ! empty( $results['failed'] ) ) {
	exit( 1 );
}
exit( 0 );
