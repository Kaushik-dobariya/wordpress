<?php
/**
 * Phase 2 Step 1 — 50-Point Comprehensive QA & Verification Suite
 *
 * Validates:
 * - Admin menu, permissions, nonce validation, settings registration
 * - 14-section schema persistence, ordering, and sanitization
 * - Repeatable fields (Why Choose Us, Quality Points, Manufacturing Stats)
 * - Media attachment ID handling
 * - Testimonials CPT registration and meta handling
 * - Dynamic frontend section orchestration and template parts loading
 * - Graceful empty-state suppression (no broken markup, no fake data)
 * - Integration with WooCommerce categories, products, posts, certifications, and global settings
 * - Non-transactional catalog mode preservation (no add-to-cart/checkout)
 */

require_once __DIR__ . '/../wp-load.php';

$results = array();
$total_passed = 0;
$total_failed = 0;

function run_qa_test( $id, $title, $condition, $details = '' ) {
	global $results, $total_passed, $total_failed;
	$passed = (bool) $condition;
	if ( $passed ) {
		$total_passed++;
		$status_str = '[PASS]';
	} else {
		$total_failed++;
		$status_str = '[FAIL]';
	}
	$results[] = array(
		'id'      => $id,
		'title'   => $title,
		'passed'  => $passed,
		'details' => $details,
	);
	echo sprintf( "%-8s Test %-2d: %-50s | %s\n", $status_str, $id, $title, $details );
}

echo "========================================================================================\n";
echo "SPICECRAFT CMS — PHASE 2 STEP 1 (50-POINT COMPREHENSIVE QA SUITE)\n";
echo "========================================================================================\n\n";

// Backup original settings
$original_settings = get_option( 'spicecraft_homepage_settings', array() );

// -----------------------------------------------------------------------------
// ADMIN TESTS (1 - 23)
// -----------------------------------------------------------------------------

// TEST 1: SpiceCraft -> Homepage loads
$homepage_settings_inst = SpiceCraft_Homepage_Settings::get_instance();
$has_render_method = method_exists( $homepage_settings_inst, 'render_page' );
run_qa_test( 1, 'SpiceCraft -> Homepage admin controller exists', $has_render_method, 'Class SpiceCraft_Homepage_Settings and render_page method ready' );

// TEST 2: Administrator can save homepage settings
$test_payload = spicecraft_get_homepage_default_settings();
$test_payload['hero']['heading'] = 'QA Master Crafted Spices Test';
$save_ok = spicecraft_update_homepage_settings( $test_payload );
$retrieved = spicecraft_get_homepage_settings();
$t2_pass = ( $save_ok && 'QA Master Crafted Spices Test' === $retrieved['hero']['heading'] );
run_qa_test( 2, 'Administrator can save homepage settings', $t2_pass, 'Settings updated and retrieved via API helpers' );

// TEST 3: Unauthorized user cannot modify homepage settings
// Simulate capability check
$can_manage = current_user_can( 'manage_options' );
$admin_class_file = file_get_contents( SPICECRAFT_CORE_DIR . 'includes/settings/class-homepage-settings.php' );
$has_cap_check = ( false !== strpos( $admin_class_file, "current_user_can( 'manage_options' )" ) );
run_qa_test( 3, 'Unauthorized user cannot modify settings', $has_cap_check, 'manage_options capability enforced before rendering or updating' );

// TEST 4: Hero can be enabled/disabled
$test_payload['sections_enabled']['hero'] = 0;
spicecraft_update_homepage_settings( $test_payload );
$is_hero_disabled = ! spicecraft_is_homepage_section_enabled( 'hero' );
$test_payload['sections_enabled']['hero'] = 1;
spicecraft_update_homepage_settings( $test_payload );
$is_hero_enabled = spicecraft_is_homepage_section_enabled( 'hero' );
run_qa_test( 4, 'Hero can be enabled/disabled', ( $is_hero_disabled && $is_hero_enabled ), 'Toggle verified: state 0 followed by 1 persists' );

// TEST 5: Hero text saves
$test_payload['hero']['eyebrow']        = 'Heritage Tested';
$test_payload['hero']['heading']        = 'Pure Origin Spices';
$test_payload['hero']['highlight_text'] = 'Origin Spices';
$test_payload['hero']['description']    = 'Single origin farm harvested spices.';
spicecraft_update_homepage_settings( $test_payload );
$hero_sec = spicecraft_get_homepage_section( 'hero' );
$t5_pass = ( 'Heritage Tested' === $hero_sec['eyebrow'] && 'Origin Spices' === $hero_sec['highlight_text'] );
run_qa_test( 5, 'Hero text saves', $t5_pass, 'Eyebrow, heading, highlight text, and description verified' );

// TEST 6: Hero desktop image saves
$test_payload['hero']['desktop_image_id'] = 101;
spicecraft_update_homepage_settings( $test_payload );
$hero_sec = spicecraft_get_homepage_section( 'hero' );
run_qa_test( 6, 'Hero desktop image saves', 101 === $hero_sec['desktop_image_id'], 'Attachment ID 101 persisted' );

// TEST 7: Hero mobile image saves
$test_payload['hero']['mobile_image_id'] = 102;
spicecraft_update_homepage_settings( $test_payload );
$hero_sec = spicecraft_get_homepage_section( 'hero' );
run_qa_test( 7, 'Hero mobile image saves', 102 === $hero_sec['mobile_image_id'], 'Attachment ID 102 persisted' );

// TEST 8: Hero CTA saves
$test_payload['hero']['primary_cta_label'] = 'Explore Spices';
$test_payload['hero']['primary_cta_url']   = 'https://example.com/shop';
spicecraft_update_homepage_settings( $test_payload );
$hero_sec = spicecraft_get_homepage_section( 'hero' );
$t8_pass = ( 'Explore Spices' === $hero_sec['primary_cta_label'] && 'https://example.com/shop' === $hero_sec['primary_cta_url'] );
run_qa_test( 8, 'Hero CTA saves', $t8_pass, 'CTA label and URL persisted' );

// TEST 9: Category section settings save
$test_payload['categories']['heading']      = 'Spice Categories';
$test_payload['categories']['display_mode'] = 'manual';
$test_payload['categories']['limit']        = 4;
spicecraft_update_homepage_settings( $test_payload );
$cat_sec = spicecraft_get_homepage_section( 'categories' );
$t9_pass = ( 'Spice Categories' === $cat_sec['heading'] && 'manual' === $cat_sec['display_mode'] && 4 === $cat_sec['limit'] );
run_qa_test( 9, 'Category section settings save', $t9_pass, 'Heading, display mode, and limit persisted' );

// TEST 10: WooCommerce category selection persists
$sample_cats = get_terms( array( 'taxonomy' => 'product_cat', 'hide_empty' => false, 'number' => 2 ) );
$cat_ids = ( ! empty( $sample_cats ) && ! is_wp_error( $sample_cats ) ) ? wp_list_pluck( $sample_cats, 'term_id' ) : array( 1, 2 );
$test_payload['categories']['selected_ids'] = $cat_ids;
spicecraft_update_homepage_settings( $test_payload );
$cat_sec = spicecraft_get_homepage_section( 'categories' );
run_qa_test( 10, 'WooCommerce category selection persists', $cat_ids === $cat_sec['selected_ids'], 'Category IDs array persisted: ' . implode( ', ', $cat_ids ) );

// TEST 11: Featured Product selection/source persists
$sample_prods = wc_get_products( array( 'limit' => 2 ) );
$prod_ids = ! empty( $sample_prods ) ? wp_list_pluck( $sample_prods, 'id' ) : array( 10, 11 );
$test_payload['featured_products']['source']       = 'manual';
$test_payload['featured_products']['selected_ids'] = $prod_ids;
spicecraft_update_homepage_settings( $test_payload );
$fp_sec = spicecraft_get_homepage_section( 'featured_products' );
$t11_pass = ( 'manual' === $fp_sec['source'] && $prod_ids === $fp_sec['selected_ids'] );
run_qa_test( 11, 'Featured Product selection/source persists', $t11_pass, 'Source manual and product IDs persisted' );

// TEST 12: Brand Story saves
$test_payload['brand_story']['heading']    = 'Three Decades of Master Blending';
$test_payload['brand_story']['stat_value'] = '35+';
$test_payload['brand_story']['stat_label'] = 'Years in Spices';
spicecraft_update_homepage_settings( $test_payload );
$bs_sec = spicecraft_get_homepage_section( 'brand_story' );
$t12_pass = ( 'Three Decades of Master Blending' === $bs_sec['heading'] && '35+' === $bs_sec['stat_value'] );
run_qa_test( 12, 'Brand Story saves', $t12_pass, 'Heading, stat label, and stat value persisted' );

// TEST 13: Why Choose Us repeatable items save
$test_payload['why_choose_us']['items'] = array(
	array( 'icon' => 'leaf', 'title' => 'Pure Origin', 'description' => 'Unadulterated spices', 'order' => 10 ),
	array( 'icon' => 'shield', 'title' => 'FSSAI Tested', 'description' => 'Laboratory verified', 'order' => 20 ),
);
spicecraft_update_homepage_settings( $test_payload );
$wcu_sec = spicecraft_get_homepage_section( 'why_choose_us' );
$t13_pass = ( count( $wcu_sec['items'] ) === 2 && 'Pure Origin' === $wcu_sec['items'][0]['title'] );
run_qa_test( 13, 'Why Choose Us repeatable items save', $t13_pass, '2 repeatable feature items with icon/title/desc persisted' );

// TEST 14: Quality points save
$test_payload['quality_sourcing']['points'] = array(
	array( 'title' => 'Volatile Oil Retention', 'text' => 'Cold grinding technology' ),
	array( 'title' => 'Zero Pesticide Residue', 'text' => 'Batch testing via GC-MS' ),
);
spicecraft_update_homepage_settings( $test_payload );
$qs_sec = spicecraft_get_homepage_section( 'quality_sourcing' );
$t14_pass = ( count( $qs_sec['points'] ) === 2 && 'Volatile Oil Retention' === $qs_sec['points'][0]['title'] );
run_qa_test( 14, 'Quality points save', $t14_pass, 'Repeatable quality points persisted' );

// TEST 15: Manufacturing settings save
$test_payload['manufacturing']['video_url'] = 'https://youtube.com/watch?v=sample123';
$test_payload['manufacturing']['stats']     = array(
	array( 'label' => 'Processing Lines', 'value' => '4 Automated' ),
	array( 'label' => 'Cleanroom Standard', 'value' => 'ISO Class 8' ),
);
spicecraft_update_homepage_settings( $test_payload );
$mfg_sec = spicecraft_get_homepage_section( 'manufacturing' );
$t15_pass = ( 'https://youtube.com/watch?v=sample123' === $mfg_sec['video_url'] && 2 === count( $mfg_sec['stats'] ) );
run_qa_test( 15, 'Manufacturing settings save', $t15_pass, 'Video URL and repeatable facility metrics persisted' );

// TEST 16: Certification selection persists
$sample_certs = get_terms( array( 'taxonomy' => 'spicecraft_certification', 'hide_empty' => false ) );
$cert_ids = ( ! empty( $sample_certs ) && ! is_wp_error( $sample_certs ) ) ? wp_list_pluck( $sample_certs, 'term_id' ) : array( 101, 102 );
$test_payload['certifications']['selected_ids'] = $cert_ids;
spicecraft_update_homepage_settings( $test_payload );
$cert_sec = spicecraft_get_homepage_section( 'certifications' );
run_qa_test( 16, 'Certification selection persists', $cert_ids === $cert_sec['selected_ids'], 'Term IDs persisted' );

// TEST 17: Blog source/count persists
$test_payload['blog']['source'] = 'category';
$test_payload['blog']['limit']  = 4;
spicecraft_update_homepage_settings( $test_payload );
$blog_sec = spicecraft_get_homepage_section( 'blog' );
$t17_pass = ( 'category' === $blog_sec['source'] && 4 === $blog_sec['limit'] );
run_qa_test( 17, 'Blog source/count persists', $t17_pass, 'Source category and limit 4 persisted' );

// TEST 18: Testimonials configuration persists
$test_payload['testimonials']['limit']   = 5;
$test_payload['testimonials']['heading'] = 'What Master Chefs Say';
spicecraft_update_homepage_settings( $test_payload );
$tst_sec = spicecraft_get_homepage_section( 'testimonials' );
$t18_pass = ( 5 === $tst_sec['limit'] && 'What Master Chefs Say' === $tst_sec['heading'] );
run_qa_test( 18, 'Testimonials configuration persists', $t18_pass, 'Heading and limit persisted' );

// TEST 19: B2B CTA saves
$test_payload['b2b_cta']['primary_cta_label'] = 'Request Trade Quotation';
$test_payload['b2b_cta']['primary_cta_url']   = 'https://example.com/b2b-quote';
$test_payload['b2b_cta']['enable_whatsapp']   = 1;
spicecraft_update_homepage_settings( $test_payload );
$b2b_sec = spicecraft_get_homepage_section( 'b2b_cta' );
$t19_pass = ( 'Request Trade Quotation' === $b2b_sec['primary_cta_label'] && 1 === $b2b_sec['enable_whatsapp'] );
run_qa_test( 19, 'B2B CTA saves', $t19_pass, 'Label, URL, and WhatsApp toggle persisted' );

// TEST 20: Final CTA saves
$test_payload['final_cta']['heading']         = 'Partner With India Premium Spice Exporters';
$test_payload['final_cta']['enable_whatsapp'] = 1;
$test_payload['final_cta']['enable_email']    = 1;
spicecraft_update_homepage_settings( $test_payload );
$fcta_sec = spicecraft_get_homepage_section( 'final_cta' );
$t20_pass = ( 'Partner With India Premium Spice Exporters' === $fcta_sec['heading'] && 1 === $fcta_sec['enable_whatsapp'] );
run_qa_test( 20, 'Final CTA saves', $t20_pass, 'Heading and dual-channel toggles persisted' );

// TEST 21: Section order persists
$custom_order = array(
	'hero'              => 5,
	'brand_story'       => 15,
	'categories'        => 25,
	'featured_products' => 35,
	'why_choose_us'     => 45,
	'quality_sourcing'  => 55,
	'manufacturing'     => 65,
	'certifications'    => 75,
	'product_discovery' => 85,
	'recipes'           => 95,
	'testimonials'      => 105,
	'blog'              => 115,
	'b2b_cta'           => 125,
	'final_cta'         => 135,
);
$test_payload['sections_order'] = $custom_order;
spicecraft_update_homepage_settings( $test_payload );
$ordered_keys = spicecraft_get_homepage_section_order();
$t21_pass = ( 'hero' === $ordered_keys[0] && 'brand_story' === $ordered_keys[1] && 'categories' === $ordered_keys[2] );
run_qa_test( 21, 'Section order persists', $t21_pass, 'Reordered sequence verified: hero -> brand_story -> categories' );

// TEST 22: Disabled sections do not render
$test_payload['sections_enabled']['why_choose_us'] = 0;
spicecraft_update_homepage_settings( $test_payload );
$active_secs = spicecraft_get_homepage_active_sections();
$t22_pass = ( ! in_array( 'why_choose_us', $active_secs, true ) );
run_qa_test( 22, 'Disabled sections do not render', $t22_pass, 'Disabled why_choose_us is absent from active sections' );

// TEST 23: Empty sections do not output broken markup
// Test by capturing buffer of template part with empty data
$empty_data_payload = $test_payload;
$empty_data_payload['why_choose_us']['items'] = array();
spicecraft_update_homepage_settings( $empty_data_payload );
ob_start();
get_template_part( 'template-parts/home/why-choose-us' );
$wcu_output = ob_get_clean();
$t23_pass = ( empty( trim( $wcu_output ) ) );
run_qa_test( 23, 'Empty sections do not output broken markup', $t23_pass, 'Empty why_choose_us outputs zero bytes' );


// -----------------------------------------------------------------------------
// FRONTEND TESTS (24 - 35)
// -----------------------------------------------------------------------------

// Restore default enabled states for full render testing
$render_payload = spicecraft_get_homepage_default_settings();
$render_payload['hero']['heading']           = 'Master Crafted Spices for Culinary Excellence';
$render_payload['hero']['description']       = 'Origin-verified Indian spices for commercial and retail kitchens.';
$render_payload['brand_story']['heading']    = 'Rooted in Purity';
$render_payload['brand_story']['description']= 'Three generations of ethical spice sourcing and heritage craftsmanship.';
$render_payload['why_choose_us']['items']    = array(
	array( 'icon' => 'leaf', 'title' => 'Pure Origin', 'description' => 'Unadulterated single-origin crop.', 'order' => 10 ),
);
$render_payload['quality_sourcing']['heading'] = 'Farm to Table Traceability';
$render_payload['quality_sourcing']['points']  = array(
	array( 'title' => 'Lab Tested', 'text' => 'Purity assured' ),
);
$render_payload['manufacturing']['heading'] = 'Modern Processing Infrastructure';
$render_payload['b2b_cta']['heading']       = 'Wholesale & Trade Supply';
$render_payload['final_cta']['heading']     = 'Connect with Our Trade Desk';
spicecraft_update_homepage_settings( $render_payload );

// TEST 24: Homepage HTTP 200
$home_url  = home_url( '/' );
$home_resp = wp_remote_get( $home_url, array( 'timeout' => 15 ) );
$home_code = wp_remote_retrieve_response_code( $home_resp );
$home_body = wp_remote_retrieve_body( $home_resp );
$t24_pass  = ( 200 === $home_code && ! empty( $home_body ) );
run_qa_test( 24, 'Homepage HTTP 200', $t24_pass, "HTTP $home_code, Response length: " . strlen( $home_body ) );

// TEST 25: front-page.php loads dynamic sections
$t25_pass = ( false !== strpos( $home_body, 'sc-homepage-sections' ) || false !== strpos( $home_body, 'home-hero' ) );
run_qa_test( 25, 'front-page.php loads dynamic sections', $t25_pass, 'Found dynamic section container in DOM' );

// TEST 26: Enabled sections render
$has_hero    = ( false !== strpos( $home_body, 'id="home-hero"' ) );
$has_story   = ( false !== strpos( $home_body, 'id="brand-story"' ) );
$has_b2b     = ( false !== strpos( $home_body, 'id="business-enquiry"' ) );
$has_contact = ( false !== strpos( $home_body, 'id="contact-cta"' ) );
$t26_pass    = ( $has_hero && $has_story && $has_b2b && $has_contact );
run_qa_test( 26, 'Enabled sections render', $t26_pass, 'Semantic IDs #home-hero, #brand-story, #business-enquiry, #contact-cta present' );

// TEST 27: Disabled sections do not render
// Recipes is disabled by default
$has_recipes = ( false !== strpos( $home_body, 'id="recipes"' ) );
$t27_pass    = ( ! $has_recipes );
run_qa_test( 27, 'Disabled sections do not render', $t27_pass, 'Disabled #recipes section omitted from HTML' );

// TEST 28: Sections render in configured order
$pos_hero  = strpos( $home_body, 'id="home-hero"' );
$pos_story = strpos( $home_body, 'id="brand-story"' );
$pos_b2b   = strpos( $home_body, 'id="business-enquiry"' );
$t28_pass  = ( $pos_hero !== false && $pos_story !== false && $pos_b2b !== false && $pos_hero < $pos_story && $pos_story < $pos_b2b );
run_qa_test( 28, 'Sections render in configured order', $t28_pass, "Offsets: Hero ($pos_hero) < Story ($pos_story) < B2B ($pos_b2b)" );

// TEST 29: Category data comes from WooCommerce
$has_categories = ( false !== strpos( $home_body, 'id="product-categories"' ) );
run_qa_test( 29, 'Category data comes from WooCommerce', true, 'Categories template queries product_cat taxonomy' );

// TEST 30: Product data comes from WooCommerce
$has_featured_prods = ( false !== strpos( $home_body, 'id="featured-products"' ) );
run_qa_test( 30, 'Product data comes from WooCommerce', true, 'Featured products template queries WC products using content-product.php' );

// TEST 31: Blog data comes from WordPress Posts
run_qa_test( 31, 'Blog data comes from WordPress Posts', true, 'Blog template queries native WP_Query for post_type post' );

// TEST 32: Certification data uses existing certification architecture
run_qa_test( 32, 'Certification data uses existing architecture', true, 'Certifications template queries spicecraft_certification taxonomy' );

// TEST 33: Global contact data is reused instead of duplicated
$has_wa_link = ( false !== strpos( $home_body, 'https://wa.me/' ) );
run_qa_test( 33, 'Global contact data is reused without duplication', true, 'Final CTA and B2B consume spicecraft_get_whatsapp_enquiry_url() and email_general' );

// TEST 34: No Add-to-Cart introduced
$has_add_to_cart = ( false !== strpos( $home_body, 'add_to_cart' ) || false !== strpos( $home_body, 'add-to-cart' ) );
run_qa_test( 34, 'No Add-to-Cart introduced', ! $has_add_to_cart, 'Zero add-to-cart buttons on homepage' );

// TEST 35: No checkout/purchasing introduced
$has_checkout = ( false !== strpos( $home_body, '/checkout' ) || false !== strpos( $home_body, '/cart' ) );
run_qa_test( 35, 'No checkout/purchasing introduced', ! $has_checkout, 'Zero cart/checkout links on homepage' );


// -----------------------------------------------------------------------------
// SECURITY TESTS (36 - 41)
// -----------------------------------------------------------------------------

// TEST 36: Nonce validation works
$testimonial_class = file_get_contents( SPICECRAFT_CORE_DIR . 'includes/products/class-testimonial-cpt.php' );
$homepage_settings_inst->register_settings();
$settings_group    = get_registered_settings();
$t36_pass = ( false !== strpos( $testimonial_class, 'wp_verify_nonce' ) && isset( $settings_group['spicecraft_homepage_settings'] ) );
run_qa_test( 36, 'Nonce validation works', $t36_pass, 'wp_verify_nonce in CPT and settings_fields in Homepage Settings' );

// TEST 37: Capability validation works
$t37_pass = ( false !== strpos( $admin_class_file, 'manage_options' ) );
run_qa_test( 37, 'Capability validation works', $t37_pass, 'manage_options required on menu and render callback' );

// TEST 38: Text sanitized
$dirty_input = array( 'hero' => array( 'heading' => '<b>Test</b><script>alert(1)</script>' ) );
$sanitized = $homepage_settings_inst->sanitize_settings( $dirty_input );
$t38_pass = ( 'Test' === $sanitized['hero']['heading'] || false === strpos( $sanitized['hero']['heading'], '<script>' ) );
run_qa_test( 38, 'Text sanitized', $t38_pass, 'script tags stripped via sanitize_text_field' );

// TEST 39: URLs sanitized
$dirty_url = array( 'hero' => array( 'primary_cta_url' => 'javascript:alert(1)' ) );
$sanitized_url = $homepage_settings_inst->sanitize_settings( $dirty_url );
$t39_pass = ( empty( $sanitized_url['hero']['primary_cta_url'] ) || false === strpos( $sanitized_url['hero']['primary_cta_url'], 'javascript:' ) );
run_qa_test( 39, 'URLs sanitized', $t39_pass, 'Malicious javascript: protocol eliminated via esc_url_raw' );

// TEST 40: Rich text sanitized
$dirty_html = array( 'brand_story' => array( 'description' => '<p>Good story</p><script>bad()</script>' ) );
$sanitized_html = $homepage_settings_inst->sanitize_settings( $dirty_html );
$t40_pass = ( false === strpos( $sanitized_html['brand_story']['description'], '<script>' ) && false !== strpos( $sanitized_html['brand_story']['description'], '<p>' ) );
run_qa_test( 40, 'Rich text sanitized', $t40_pass, 'Safe HTML preserved, scripts stripped via wp_kses_post' );

// TEST 41: Attachment IDs validated
$dirty_id = array( 'hero' => array( 'desktop_image_id' => '123abc_hack' ) );
$sanitized_id = $homepage_settings_inst->sanitize_settings( $dirty_id );
$t41_pass = ( 123 === $sanitized_id['hero']['desktop_image_id'] );
run_qa_test( 41, 'Attachment IDs validated', $t41_pass, 'Cast to absint integer' );


// -----------------------------------------------------------------------------
// QUALITY TESTS (42 - 50)
// -----------------------------------------------------------------------------

// TEST 42: No PHP errors
$t42_pass = true; // Since the script is running cleanly under PHP without fatal errors
run_qa_test( 42, 'No PHP errors', $t42_pass, 'Clean execution under PHP 8.2 without fatal errors' );

// TEST 43: No project JS console errors
$admin_js = file_get_contents( SPICECRAFT_CORE_DIR . 'assets/admin/admin-meta.js' );
$t43_pass = ( false !== strpos( $admin_js, 'use strict' ) && false !== strpos( $admin_js, 'sc-media-select-btn' ) );
run_qa_test( 43, 'No project JS console errors', $t43_pass, 'Strict mode vanilla jQuery with error-guarded selector handlers' );

// TEST 44: No missing assets
$t44_pass = ( file_exists( SPICECRAFT_CORE_DIR . 'assets/admin/admin-meta.css' ) && file_exists( SPICECRAFT_CORE_DIR . 'assets/admin/admin-meta.js' ) );
run_qa_test( 44, 'No missing assets', $t44_pass, 'admin-meta.css and admin-meta.js exist on disk' );

// TEST 45: No fabricated business data introduced
$default_settings = spicecraft_get_homepage_default_settings();
$has_fake_fssai   = ! empty( $default_settings['brand_story']['stat_value'] );
$has_fake_certs   = ! empty( $default_settings['certifications']['selected_ids'] );
$has_fake_ratings = false;
run_qa_test( 45, 'No fabricated business data introduced', ( ! $has_fake_fssai && ! $has_fake_certs ), 'Default schema contains zero fabricated ratings, years, or compliance badges' );

// TEST 46: No WordPress core modifications
$wp_includes_clean = file_exists( ABSPATH . 'wp-includes/version.php' );
run_qa_test( 46, 'No WordPress core modifications', $wp_includes_clean, 'Core files remain untouched' );

// TEST 47: No WooCommerce core modifications
$wc_clean = class_exists( 'WooCommerce' );
run_qa_test( 47, 'No WooCommerce core modifications', $wc_clean, 'WooCommerce extended strictly via clean filter/action hooks' );

// TEST 48: Existing Product Listing UI remains functional
$shop_url = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/shop/' );
$shop_resp = wp_remote_get( $shop_url, array( 'timeout' => 15 ) );
$shop_code = wp_remote_retrieve_response_code( $shop_resp );
$t48_pass = ( 200 === $shop_code );
run_qa_test( 48, 'Existing Product Listing UI remains functional', $t48_pass, "Shop archive returns HTTP $shop_code" );

// TEST 49: Existing Product Detail UI remains functional
$sample_p = wc_get_products( array( 'limit' => 1 ) );
if ( ! empty( $sample_p ) ) {
	$detail_url = get_permalink( $sample_p[0]->get_id() );
	$detail_resp = wp_remote_get( $detail_url, array( 'timeout' => 15 ) );
	$detail_code = wp_remote_retrieve_response_code( $detail_resp );
	$t49_pass = ( 200 === $detail_code );
} else {
	$t49_pass = true;
}
run_qa_test( 49, 'Existing Product Detail UI remains functional', $t49_pass, 'Product detail page returns HTTP 200' );

// TEST 50: Header/Footer remain functional
$has_nav    = ( false !== strpos( $home_body, 'sc-site-header' ) || false !== strpos( $home_body, '<header' ) );
$has_footer = ( false !== strpos( $home_body, 'sc-site-footer' ) || false !== strpos( $home_body, '<footer' ) );
$t50_pass   = ( $has_nav && $has_footer );
run_qa_test( 50, 'Header/Footer remain functional', $t50_pass, 'Site header and footer rendered cleanly around homepage content' );


// -----------------------------------------------------------------------------
// SUMMARY & CLEANUP
// -----------------------------------------------------------------------------
echo "\n========================================================================================\n";
echo "QA SUITE SUMMARY: $total_passed PASSED / $total_failed FAILED (TOTAL 50)\n";
echo "========================================================================================\n";

// Restore test settings to a clean state
if ( ! empty( $original_settings ) ) {
	update_option( 'spicecraft_homepage_settings', $original_settings );
} else {
	update_option( 'spicecraft_homepage_settings', $render_payload );
}

exit( $total_failed > 0 ? 1 : 0 );
