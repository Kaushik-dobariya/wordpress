<?php
/**
 * Test Suite: Phase 4 Step 1 - Product Enquiry & Lead Management CMS
 */

// Load WordPress environment
require_once dirname( __DIR__ ) . '/wp-load.php';

echo "========================================================================\n";
echo "  SPICECRAFT CMS: PHASE 4 STEP 1 - ENQUIRY & LEAD MANAGEMENT TEST SUITE \n";
echo "========================================================================\n\n";

$tests_passed = 0;
$tests_total  = 0;

function sc_assert( $condition, $message ) {
	global $tests_passed, $tests_total;
	$tests_total++;
	if ( $condition ) {
		echo " [PASS] " . $message . "\n";
		$tests_passed++;
	} else {
		echo " [FAIL] " . $message . "\n";
	}
}

// -------------------------------------------------------------------------
// TEST 1: CPT Registration & Privacy
// -------------------------------------------------------------------------
echo "1. Checking spicecraft_enquiry CPT Registration & Privacy...\n";
$cpt_obj = get_post_type_object( 'spicecraft_enquiry' );
sc_assert( ! empty( $cpt_obj ), 'spicecraft_enquiry post type is properly registered' );
sc_assert( false === $cpt_obj->public, 'Enquiry CPT is strictly private (public => false)' );
sc_assert( true === $cpt_obj->exclude_from_search, 'Enquiry records are excluded from public search' );
sc_assert( false === $cpt_obj->show_in_rest, 'Enquiry CPT is not exposed in public REST API' );
sc_assert( false === $cpt_obj->has_archive, 'Enquiry CPT has no public archive' );
sc_assert( true === $cpt_obj->show_ui, 'Enquiry CPT UI is accessible in WordPress Admin' );

// -------------------------------------------------------------------------
// TEST 2: Helper Functions & Options
// -------------------------------------------------------------------------
echo "\n2. Checking Enquiry Helper Functions...\n";
sc_assert( function_exists( 'spicecraft_get_enquiry_statuses' ), 'spicecraft_get_enquiry_statuses() exists' );
$statuses = spicecraft_get_enquiry_statuses();
sc_assert( isset( $statuses['new'] ) && isset( $statuses['contacted'] ) && isset( $statuses['qualified'] ) && isset( $statuses['in_discussion'] ) && isset( $statuses['closed'] ), 'Statuses include new, contacted, qualified, in_discussion, closed' );

sc_assert( function_exists( 'spicecraft_get_customer_types' ), 'spicecraft_get_customer_types() exists' );
$cust_types = spicecraft_get_customer_types();
sc_assert( count( $cust_types ) === 8, '8 customer types defined' );
sc_assert( isset( $cust_types['retailer'] ) && isset( $cust_types['distributor'] ) && isset( $cust_types['wholesaler'] ) && isset( $cust_types['importer'] ) && isset( $cust_types['exporter'] ) && isset( $cust_types['food_manufacturer'] ) && ( isset( $cust_types['restaurant_hospitality'] ) || isset( $cust_types['restaurant'] ) ) && isset( $cust_types['other'] ), 'All 8 required customer types exist (Retailer, Distributor, Wholesaler, Importer, Exporter, Food Manufacturer, Restaurant, Other)' );

sc_assert( function_exists( 'spicecraft_get_enquiry_types' ), 'spicecraft_get_enquiry_types() exists' );
$types = spicecraft_get_enquiry_types();
sc_assert( isset( $types['product'] ) && isset( $types['bulk'] ) && isset( $types['export'] ), 'Types include product, bulk, export, etc.' );

sc_assert( function_exists( 'spicecraft_get_enquiry_receiving_email' ), 'spicecraft_get_enquiry_receiving_email() exists' );
$initial_email = spicecraft_get_enquiry_receiving_email();
sc_assert( is_email( $initial_email ), 'Enquiry receiving email resolves to a valid email: ' . $initial_email );

// -------------------------------------------------------------------------
// TEST 3: Dynamic Email Configuration (Setting change verification)
// -------------------------------------------------------------------------
echo "\n3. Checking Dynamic Backend Email Configuration...\n";
$original_settings = get_option( 'spicecraft_global_settings', array() );
$test_custom_email = 'trade-inbox-test@spicecraft.com';

// Update setting
$updated_settings = $original_settings;
$updated_settings['enquiry_receiving_email'] = $test_custom_email;
update_option( 'spicecraft_global_settings', $updated_settings );

$resolved_email = spicecraft_get_enquiry_receiving_email();
sc_assert( $resolved_email === $test_custom_email, 'Updating backend setting immediately reflects in runtime without code change (' . $resolved_email . ')' );

// Restore original setting
update_option( 'spicecraft_global_settings', $original_settings );
sc_assert( spicecraft_get_enquiry_receiving_email() === $initial_email, 'Restoring backend setting immediately returns previous recipient' );

// -------------------------------------------------------------------------
// TEST 4: Engine Validation & Server-Side Security
// -------------------------------------------------------------------------
echo "\n4. Checking Submission Engine & Validation...\n";
sc_assert( class_exists( 'SpiceCraft_Enquiry_Engine' ), 'SpiceCraft_Enquiry_Engine class exists' );
$engine = SpiceCraft_Enquiry_Engine::get_instance();

// Find an existing published product for testing
$products = get_posts( array(
	'post_type'      => 'product',
	'posts_per_page' => 1,
	'post_status'    => 'publish',
) );

$test_product_id = ! empty( $products ) ? $products[0]->ID : 0;
sc_assert( $test_product_id > 0, 'Found published test product ID: ' . $test_product_id );

// Test 4A: Product Enquiry Submission with Customer Type, Pack Size, Consent
$post_data = array(
	'full_name'         => 'Rajesh Kumar Test',
	'company'           => 'Apex Foods International',
	'email'             => 'rajesh.test@apexfoods.com',
	'phone'             => '+91 98765 43210',
	'country'           => 'India',
	'city'              => 'Mumbai',
	'customer_type'     => 'importer',
	'whatsapp_number'   => '+91 98765 43210',
	'enquiry_type'      => 'product',
	'pack_size'         => '500g',
	'quantity'          => '500 kg',
	'packaging'         => '25kg Polypropylene Gunny Bags',
	'preferred_contact' => 'whatsapp',
	'consent'           => '1',
	'message'           => 'We need pricing and lab test COA for export to UAE.',
	'product_id'        => $test_product_id,
	'lead_source'       => 'Product Detail Page',
	'page_url'          => get_permalink( $test_product_id ),
);

$new_enquiry_id = $engine->process_submission( $post_data );
sc_assert( is_int( $new_enquiry_id ) && $new_enquiry_id > 0, 'Valid product enquiry created lead post ID: ' . $new_enquiry_id );

// Verify metadata on newly created lead
$lead_name       = get_post_meta( $new_enquiry_id, '_sc_enquiry_customer_name', true );
$lead_company    = get_post_meta( $new_enquiry_id, '_sc_enquiry_company', true );
$lead_email      = get_post_meta( $new_enquiry_id, '_sc_enquiry_email', true );
$lead_phone      = get_post_meta( $new_enquiry_id, '_sc_enquiry_phone', true );
$lead_country    = get_post_meta( $new_enquiry_id, '_sc_enquiry_country', true );
$lead_cust_type  = get_post_meta( $new_enquiry_id, '_sc_enquiry_customer_type', true );
$lead_pack_size  = get_post_meta( $new_enquiry_id, '_sc_enquiry_pack_size', true );
$lead_consent    = get_post_meta( $new_enquiry_id, '_sc_enquiry_consent', true );
$lead_prod_id    = get_post_meta( $new_enquiry_id, '_sc_enquiry_product_id', true );
$lead_prod_name  = get_post_meta( $new_enquiry_id, '_sc_enquiry_product_name', true );
$lead_status     = get_post_meta( $new_enquiry_id, '_sc_enquiry_status', true );
$lead_type       = get_post_meta( $new_enquiry_id, '_sc_enquiry_type', true );
$lead_activity   = get_post_meta( $new_enquiry_id, '_sc_enquiry_activity_log', true );

sc_assert( 'Rajesh Kumar Test' === $lead_name, 'Stored customer name matches: ' . $lead_name );
sc_assert( 'Apex Foods International' === $lead_company, 'Stored company matches: ' . $lead_company );
sc_assert( 'rajesh.test@apexfoods.com' === $lead_email, 'Stored email matches: ' . $lead_email );
sc_assert( 'India' === $lead_country, 'Stored country matches: ' . $lead_country );
sc_assert( 'importer' === $lead_cust_type, 'Stored customer type matches: ' . $lead_cust_type );
sc_assert( '500g' === $lead_pack_size, 'Stored pack size matches: ' . $lead_pack_size );
sc_assert( 1 === (int) $lead_consent, 'Stored consent agreement recorded: ' . $lead_consent );
sc_assert( (int) $test_product_id === (int) $lead_prod_id, 'Stored product ID matches server-side: ' . $lead_prod_id );
sc_assert( ! empty( $lead_prod_name ), 'Server-side validated product title stored: ' . $lead_prod_name );
sc_assert( 'new' === $lead_status, 'Initial status is "new"' );
sc_assert( 'product' === $lead_type, 'Enquiry type is "product"' );
sc_assert( is_array( $lead_activity ) && ! empty( $lead_activity ), 'Initial activity log created on submission' );
sc_assert( isset( $lead_activity[0]['action'] ) && false !== stripos( $lead_activity[0]['action'], 'lead created' ), 'Activity log records "Lead created" action' );

// -------------------------------------------------------------------------
// TEST 5: General / Export Enquiry (Without Product)
// -------------------------------------------------------------------------
echo "\n5. Checking General Business / Export Enquiry...\n";
$general_data = array(
	'full_name'         => 'Elena Rostova',
	'company'           => 'EuroSpice Import GmbH',
	'email'             => 'elena@eurospice-hamburg.de',
	'phone'             => '+49 40 1234567',
	'country'           => 'Germany',
	'city'              => 'Hamburg',
	'customer_type'     => 'distributor',
	'whatsapp_number'   => '+49 40 1234567',
	'enquiry_type'      => 'export',
	'quantity'          => '2 FCL (40ft containers)',
	'packaging'         => 'Private Label Retail Jars',
	'preferred_contact' => 'email',
	'consent'           => 1,
	'message'           => 'Interested in long-term import contract for whole black pepper and turmeric.',
	'product_id'        => 0,
	'lead_source'       => 'Website Header CTA',
	'page_url'          => home_url( '/#contact' ),
);

$gen_enquiry_id = $engine->process_submission( $general_data );
sc_assert( is_int( $gen_enquiry_id ) && $gen_enquiry_id > 0, 'General enquiry created lead post ID: ' . $gen_enquiry_id );
$gen_type = get_post_meta( $gen_enquiry_id, '_sc_enquiry_type', true );
$gen_prod = get_post_meta( $gen_enquiry_id, '_sc_enquiry_product_name', true );
$gen_cust = get_post_meta( $gen_enquiry_id, '_sc_enquiry_customer_type', true );
sc_assert( 'export' === $gen_type, 'General enquiry type is "export"' );
sc_assert( 'distributor' === $gen_cust, 'General enquiry customer type is "distributor"' );
sc_assert( empty( $gen_prod ), 'General enquiry has no product name attached' );

// -------------------------------------------------------------------------
// TEST 6: Validation Errors
// -------------------------------------------------------------------------
echo "\n6. Checking Server-Side Validation Rejections...\n";
// 6A: Missing Name
$invalid_data = $post_data;
$invalid_data['full_name'] = '';
$res1 = $engine->process_submission( $invalid_data );
sc_assert( is_wp_error( $res1 ) && 'missing_name' === $res1->get_error_code(), 'Empty name rejected with missing_name error' );

// 6B: Invalid Email
$invalid_data = $post_data;
$invalid_data['email'] = 'not-an-email';
$res2 = $engine->process_submission( $invalid_data );
sc_assert( is_wp_error( $res2 ) && 'invalid_email' === $res2->get_error_code(), 'Invalid email rejected with invalid_email error' );

// 6C: Missing Phone
$invalid_data = $post_data;
$invalid_data['phone'] = '';
$res3 = $engine->process_submission( $invalid_data );
sc_assert( is_wp_error( $res3 ) && 'missing_phone' === $res3->get_error_code(), 'Missing phone rejected with missing_phone error' );

// 6D: Missing Message
$invalid_data = $post_data;
$invalid_data['message'] = '';
$res4 = $engine->process_submission( $invalid_data );
sc_assert( is_wp_error( $res4 ) && 'missing_message' === $res4->get_error_code(), 'Missing message rejected with missing_message error' );

// 6E: Non-existent product ID
$invalid_data = $post_data;
$invalid_data['product_id'] = 99999999;
$res5 = $engine->process_submission( $invalid_data );
sc_assert( is_wp_error( $res5 ) && 'invalid_product' === $res5->get_error_code(), 'Fake product ID rejected with invalid_product error' );

// -------------------------------------------------------------------------
// TEST 7: Admin Status Workflow & Private Internal Notes
// -------------------------------------------------------------------------
echo "\n7. Checking Admin Status Workflow, Follow-up Info & Internal Notes...\n";
// Update status to 'qualified'
update_post_meta( $new_enquiry_id, '_sc_enquiry_status', 'qualified' );
sc_assert( 'qualified' === get_post_meta( $new_enquiry_id, '_sc_enquiry_status', true ), 'Status updated to "qualified"' );

// Update status to 'in_discussion'
update_post_meta( $new_enquiry_id, '_sc_enquiry_status', 'in_discussion' );
sc_assert( 'in_discussion' === get_post_meta( $new_enquiry_id, '_sc_enquiry_status', true ), 'Status updated to "in_discussion"' );

// Add follow-up metadata
update_post_meta( $new_enquiry_id, '_sc_enquiry_last_contacted', '2026-09-30' );
update_post_meta( $new_enquiry_id, '_sc_enquiry_next_followup', '2026-10-05' );
$admin_user = get_users( array( 'role' => 'administrator', 'number' => 1 ) );
$admin_uid  = ! empty( $admin_user ) ? $admin_user[0]->ID : 1;
update_post_meta( $new_enquiry_id, '_sc_enquiry_assigned_user', $admin_uid );

sc_assert( '2026-09-30' === get_post_meta( $new_enquiry_id, '_sc_enquiry_last_contacted', true ), 'Last contacted date stored correctly' );
sc_assert( '2026-10-05' === get_post_meta( $new_enquiry_id, '_sc_enquiry_next_followup', true ), 'Next follow-up date stored correctly' );
sc_assert( (int) $admin_uid === (int) get_post_meta( $new_enquiry_id, '_sc_enquiry_assigned_user', true ), 'Assigned team member ID stored correctly' );

// Add private internal notes
$admin_notes = "24 Sep 2026: Called buyer Rajesh Kumar.\nDiscussed 500kg minimum order.\nSent preliminary quotation.";
update_post_meta( $new_enquiry_id, '_sc_enquiry_admin_notes', $admin_notes );
$saved_notes = get_post_meta( $new_enquiry_id, '_sc_enquiry_admin_notes', true );
sc_assert( $saved_notes === $admin_notes, 'Private internal notes saved securely' );

// -------------------------------------------------------------------------
// TEST 8: CSV Export Data Integrity & Filtering
// -------------------------------------------------------------------------
echo "\n8. Checking CSV Export Formatting, Filtering & Privacy...\n";
$export_posts = get_posts( array(
	'post_type'      => 'spicecraft_enquiry',
	'post_status'    => 'publish',
	'posts_per_page' => 10,
) );
sc_assert( count( $export_posts ) >= 2, 'Found at least 2 enquiries for CSV export verification' );

$headers = array( 'ID', 'Date', 'Full Name', 'Company', 'Email', 'Phone', 'Country', 'Customer Type', 'Enquiry Type', 'Product', 'Pack Size', 'Quantity', 'Status', 'Follow-up Date' );

foreach ( $export_posts as $ep ) {
	$lead_notes = get_post_meta( $ep->ID, '_sc_enquiry_admin_notes', true );
	// Ensure internal notes are NOT in header
	sc_assert( ! in_array( 'Internal Notes', $headers, true ), 'CSV headers strictly exclude private internal admin notes' );
}

// -------------------------------------------------------------------------
// TEST 9: Frontend Form Template & Verification
// -------------------------------------------------------------------------
echo "\n9. Checking Frontend Form Component Code & Markup...\n";
$form_file = get_template_directory() . '/template-parts/components/enquiry-form.php';
sc_assert( file_exists( $form_file ), 'enquiry-form.php template exists' );
$form_content = file_get_contents( $form_file );

sc_assert( false !== strpos( $form_content, 'name="customer_type"' ), 'Frontend form contains customer_type selector' );
sc_assert( false !== strpos( $form_content, 'name="consent"' ), 'Frontend form contains consent checkbox' );
sc_assert( false !== strpos( $form_content, 'name="consent_required"' ), 'Frontend form contains consent_required security flag' );
sc_assert( false !== strpos( $form_content, 'name="pack_size"' ), 'Frontend form contains pack_size input/selector' );
sc_assert( false !== strpos( $form_content, 'I agree to be contacted regarding this enquiry.' ), 'Frontend form contains required consent label' );
sc_assert( false !== strpos( $form_content, 'Tell us about your requirement' ), 'Frontend form contains "Tell us about your requirement" label' );

// -------------------------------------------------------------------------
// TEST 10: Admin Meta Save & Activity History Integration
// -------------------------------------------------------------------------
echo "\n10. Checking Admin Meta Box Save & Activity Logging...\n";
sc_assert( class_exists( 'SpiceCraft_Enquiry_Meta' ), 'SpiceCraft_Enquiry_Meta class exists' );
$meta_instance = SpiceCraft_Enquiry_Meta::get_instance();

// Emulate current user with capability before creating nonce
$orig_current_user = get_current_user_id();
wp_set_current_user( $admin_uid );

// Simulate an admin status change through save_enquiry_meta
$_POST['spicecraft_enquiry_meta_nonce'] = wp_create_nonce( 'spicecraft_save_enquiry_meta' );
$_POST['_sc_enquiry_status']            = 'closed';
$_POST['_sc_enquiry_admin_notes']       = $admin_notes . "\nClosed deal on 01 Oct.";
$_POST['_sc_enquiry_customer_type']     = 'wholesaler';
$_POST['_sc_enquiry_last_contacted']    = '2026-10-01';
$_POST['_sc_enquiry_next_followup']     = '';
$_POST['_sc_enquiry_assigned_user']     = $admin_uid;

$meta_instance->save_enquiry_meta( $new_enquiry_id, get_post( $new_enquiry_id ) );

// Check updated meta
$updated_status    = get_post_meta( $new_enquiry_id, '_sc_enquiry_status', true );
$updated_cust_type = get_post_meta( $new_enquiry_id, '_sc_enquiry_customer_type', true );
$updated_log       = get_post_meta( $new_enquiry_id, '_sc_enquiry_activity_log', true );

sc_assert( 'closed' === $updated_status, 'save_enquiry_meta successfully saved status: ' . $updated_status );
sc_assert( 'wholesaler' === $updated_cust_type, 'save_enquiry_meta successfully saved customer type: ' . $updated_cust_type );
sc_assert( is_array( $updated_log ) && count( $updated_log ) >= 2, 'Activity log automatically recorded status transition event' );

// Clean up globals
unset( $_POST['spicecraft_enquiry_meta_nonce'], $_POST['enquiry_status'], $_POST['enquiry_admin_notes'] );
wp_set_current_user( $orig_current_user );

echo "\n------------------------------------------------------------------------\n";
printf( "SUMMARY: %d / %d tests passed (%.1f%%)\n", $tests_passed, $tests_total, ( $tests_passed / $tests_total ) * 100 );
echo "------------------------------------------------------------------------\n";
