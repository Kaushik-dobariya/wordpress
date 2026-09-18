<?php
/**
 * Test Step 2: Full Frontend Verification & Test Matrix Audit
 *
 * Verifies:
 * 1. HTTP 200 responses on /manufacturing/ and /quality/
 * 2. Correct templates assigned
 * 3. Exactly one <h1> per page
 * 4. Empty-state suppression of unconfigured sections
 * 5. Dynamic rendering of all 15 Manufacturing sections and 16 Quality sections when data is entered
 * 6. Dynamic hiding when a section is disabled or emptied (Journey 7)
 * 7. Catalog mode verification (no add-to-cart, no cart/checkout forms)
 * 8. Cleanup back to clean state (Zero-Fabricated-Content Rule)
 */
require_once dirname( __DIR__ ) . '/wp-load.php';

$results = array(
	'passed' => 0,
	'failed' => 0,
	'errors' => array(),
);

function sc_test( $condition, $message, &$results ) {
	if ( $condition ) {
		$results['passed']++;
		echo " [PASS] {$message}\n";
	} else {
		$results['failed']++;
		$results['errors'][] = $message;
		echo " [FAIL] {$message}\n";
	}
}

echo "=== 1. HTTP Status and Template Verification ===\n";
$mfg_html = file_get_contents( 'http://localhost/manufacturing/' );
$q_html   = file_get_contents( 'http://localhost/quality/' );

sc_test( ! empty( $mfg_html ), "Manufacturing page returned HTTP 200", $results );
sc_test( ! empty( $q_html ), "Quality & Sourcing page returned HTTP 200", $results );

// Template verification
$mfg_post = get_page_by_path( 'manufacturing' );
$q_post   = get_page_by_path( 'quality' );

sc_test( get_post_meta( $mfg_post->ID, '_wp_page_template', true ) === 'page-manufacturing.php', "Manufacturing page uses page-manufacturing.php template", $results );
sc_test( get_post_meta( $q_post->ID, '_wp_page_template', true ) === 'page-quality.php', "Quality page uses page-quality.php template", $results );

echo "\n=== 2. Heading Hierarchy in Empty State (Single H1) ===\n";
preg_match_all( '/<h1[^>]*>(.*?)<\/h1>/is', $mfg_html, $mfg_h1s );
preg_match_all( '/<h1[^>]*>(.*?)<\/h1>/is', $q_html, $q_h1s );

sc_test( count( $mfg_h1s[0] ) === 1, "Manufacturing page has exactly one <h1> tag in empty state", $results );
sc_test( count( $q_h1s[0] ) === 1, "Quality page has exactly one <h1> tag in empty state", $results );

echo "\n=== 3. Populating Comprehensive Test Configuration for Section Auditing ===\n";

$test_mfg = array(
	'sections_order' => array(
		'hero'           => 10,
		'introduction'   => 20,
		'facility'       => 30,
		'process'        => 40,
		'capabilities'   => 50,
		'equipment'      => 60,
		'hygiene'        => 70,
		'packaging'      => 80,
		'warehousing'    => 90,
		'statistics'     => 100,
		'gallery'        => 110,
		'certifications' => 120,
		'products'       => 130,
		'b2b_cta'        => 140,
		'final_cta'      => 150,
	),
	'sections_enabled' => array(
		'hero'           => 1,
		'introduction'   => 1,
		'facility'       => 1,
		'process'        => 1,
		'capabilities'   => 1,
		'equipment'      => 1,
		'hygiene'        => 1,
		'packaging'      => 1,
		'warehousing'    => 1,
		'statistics'     => 1,
		'gallery'        => 1,
		'certifications' => 1,
		'products'       => 1,
		'b2b_cta'        => 1,
		'final_cta'      => 1,
	),
	'hero' => array(
		'eyebrow'             => 'Processing Standards',
		'heading'             => 'Precision Spice Processing Facility',
		'heading_highlight'   => 'Processing Facility',
		'intro'               => 'Dedicated technical facility for consistent milling, grading, and packaging.',
		'desktop_image_id'    => 32,
		'cta_primary_label'   => 'Explore Process',
		'cta_primary_url'     => '#sc-mfg-process',
		'cta_secondary_label' => 'Technical Specs',
		'cta_secondary_url'   => '#sc-mfg-equipment',
	),
	'introduction' => array(
		'eyebrow'          => 'Plant Philosophy',
		'heading'          => 'Engineered for Purity and Consistency',
		'content'          => 'Our operational facility maintains systematic separation across raw receiving, processing, and finished packaging.',
		'image_primary_id' => 31,
		'cta_label'        => 'Facility Standards',
		'cta_url'          => '#sc-mfg-facility',
	),
	'facility' => array(
		'eyebrow'     => 'Infrastructure',
		'heading'     => 'Purpose-Built Spice Plant Layout',
		'description' => 'Zoned infrastructure preventing cross-contamination and ensuring food-grade hygiene.',
		'image_id'    => 32,
		'highlights'  => array(
			array( 'title' => 'Segregated Zones', 'description' => 'Dedicated bays for raw cleaning and fine grinding.' ),
			array( 'title' => 'Dust Control', 'description' => 'Integrated aspiration and pneumatic dust handling.' ),
		),
	),
	'process' => array(
		'eyebrow'     => 'Step by Step',
		'heading'     => 'Processing Stages',
		'description' => 'Systematic transformation from raw agricultural lot to packaged spice.',
		'items'       => array(
			array( 'step_number' => '01', 'title' => 'Intake & Inspection', 'description' => 'Sampling and moisture assessment.', 'order' => 10 ),
			array( 'step_number' => '02', 'title' => 'Air Classification', 'description' => 'Removal of stones, chaff, and light impurities.', 'order' => 20 ),
			array( 'step_number' => '03', 'title' => 'Grading & Sizing', 'description' => 'Sieve sizing according to mesh specifications.', 'order' => 30 ),
		),
	),
	'capabilities' => array(
		'eyebrow'     => 'Capabilities',
		'heading'     => 'Technical Capabilities',
		'description' => 'Core operational capacities configured in facility.',
		'items'       => array(
			array( 'title' => 'Fine Pulverizing', 'description' => 'Standard mesh sizing from 30 to 100 mesh.' ),
			array( 'title' => 'Whole Seed Grading', 'description' => 'Gravity separation and color grading.' ),
		),
	),
	'equipment' => array(
		'eyebrow'     => 'Machinery',
		'heading'     => 'Processing Machinery',
		'description' => 'Technical machinery configured on the production line.',
		'items'       => array(
			array(
				'name'        => 'Rotary Air Separator',
				'description' => 'De-stoning and heavy impurity separation.',
				'order'       => 10,
				'specs'       => array(
					array( 'label' => 'Contact Parts', 'value' => 'Stainless Steel' ),
				),
			),
		),
	),
	'hygiene' => array(
		'eyebrow'     => 'Hygiene',
		'heading'     => 'Sanitation Controls',
		'description' => 'Standard operating procedures for plant sanitation.',
		'practices'   => array(
			array( 'title' => 'Daily Washdowns', 'description' => 'Systematic CIP and dry-cleaning cycles.' ),
		),
	),
	'packaging' => array(
		'eyebrow'      => 'Packaging',
		'heading'      => 'Packaging Options',
		'description'  => 'Flexible packing formats for food service and commercial trade.',
		'capabilities' => array(
			array( 'title' => 'Nitrogen Flushed Pouches', 'description' => 'Protection against aroma loss and oxidation.' ),
		),
	),
	'warehousing' => array(
		'eyebrow'     => 'Warehousing',
		'heading'     => 'Finished Goods Storage',
		'description' => 'Temperature and humidity controlled storage bays.',
		'highlights'  => array(
			array( 'title' => 'Palletized Storage', 'description' => 'Racked inventory ensuring FIFO dispatch.' ),
		),
	),
	'statistics' => array(
		'eyebrow'     => 'Verified Scale',
		'heading'     => 'Plant Metrics',
		'description' => 'Operational figures recorded by site administration.',
		'items'       => array(
			array( 'value' => '100', 'suffix' => '%', 'label' => 'Stainless Steel Contact', 'order' => 10 ),
			array( 'value' => '24', 'suffix' => 'h', 'label' => 'Batch Quarantine', 'order' => 20 ),
		),
	),
	'gallery' => array(
		'eyebrow'        => 'Visual Tour',
		'heading'        => 'Facility Photos',
		'description'    => 'Real-world visual inspection of operational areas.',
		'attachment_ids' => array( 31, 32, 33 ),
	),
	'certifications' => array(
		'eyebrow'      => 'Standards',
		'heading'      => 'Plant Accreditations',
		'description'  => 'Food safety management systems in place.',
		'selected_ids' => array(),
	),
	'products' => array(
		'eyebrow'     => 'Output',
		'heading'     => 'Processed Spice Categories',
		'description' => 'Browse our finished catalog items.',
		'source'      => 'categories',
	),
	'b2b_cta' => array(
		'eyebrow'           => 'Commercial Inquiries',
		'heading'           => 'Inquire for Contract Milling',
		'description'       => 'Discuss specific packaging sizes and custom grind specifications.',
		'primary_cta_label' => 'Contact Plant Manager',
		'primary_cta_url'   => '#sc-mfg-final-cta',
	),
	'final_cta' => array(
		'heading'       => 'Get in Touch with our Technical Team',
		'description'   => 'Direct coordination for samples and technical facility documentation.',
		'cta_label'     => 'Send Enquiry',
		'cta_url'       => '/contact/',
		'show_whatsapp' => 1,
		'show_email'    => 1,
	),
);

// Save test mfg settings
spicecraft_update_manufacturing_settings( $test_mfg );

// Test Quality settings
$test_q = array(
	'sections_order' => array(
		'hero'           => 10,
		'introduction'   => 20,
		'principles'     => 30,
		'process'        => 40,
		'testing'        => 50,
		'sourcing'       => 60,
		'regions'        => 70,
		'raw_materials'  => 80,
		'traceability'   => 90,
		'food_safety'    => 100,
		'certifications' => 110,
		'statistics'     => 120,
		'gallery'        => 130,
		'products'       => 140,
		'b2b_cta'        => 150,
		'final_cta'      => 160,
	),
	'sections_enabled' => array(
		'hero'           => 1,
		'introduction'   => 1,
		'principles'     => 1,
		'process'        => 1,
		'testing'        => 1,
		'sourcing'       => 1,
		'regions'        => 1,
		'raw_materials'  => 1,
		'traceability'   => 1,
		'food_safety'    => 1,
		'certifications' => 1,
		'statistics'     => 1,
		'gallery'        => 1,
		'products'       => 1,
		'b2b_cta'        => 1,
		'final_cta'      => 1,
	),
	'hero' => array(
		'eyebrow'             => 'Quality Assurance',
		'heading'             => 'Rigorous Testing & Origin Standards',
		'heading_highlight'   => 'Origin Standards',
		'intro'               => 'Detailed technical protocols ensuring spice safety, moisture control, and authentic origin verification.',
		'desktop_image_id'    => 33,
		'cta_primary_label'   => 'View Testing Items',
		'cta_primary_url'     => '#sc-quality-testing',
		'cta_secondary_label' => 'Sourcing Regions',
		'cta_secondary_url'   => '#sc-quality-regions',
	),
	'introduction' => array(
		'eyebrow'          => 'Quality Mindset',
		'heading'          => 'Systematic Quality Governance',
		'content'          => 'Every batch is evaluated through standard testing parameters before and after processing.',
		'image_primary_id' => 31,
	),
	'principles' => array(
		'eyebrow'     => 'Core Pillars',
		'heading'     => 'Guiding Quality Principles',
		'description' => 'Uncompromising testing protocols governing our spice procurement.',
		'items'       => array(
			array( 'title' => 'Aroma Retention', 'description' => 'Testing essential volatile oils.', 'order' => 10 ),
			array( 'title' => 'Zero Contamination', 'description' => 'Physical and biological screening.', 'order' => 20 ),
		),
	),
	'process' => array(
		'eyebrow'     => 'QC Flow',
		'heading'     => 'Quality Control Checkpoints',
		'description' => 'Multi-tier verification from raw arrival to dispatch.',
		'items'       => array(
			array( 'step_number' => '01', 'title' => 'Lot Sampling', 'description' => 'Representative composite sampling.', 'order' => 10 ),
			array( 'step_number' => '02', 'title' => 'Laboratory Analysis', 'description' => 'Moisture and ash testing.', 'order' => 20 ),
		),
	),
	'testing' => array(
		'eyebrow'         => 'Analytics',
		'heading'         => 'Analytical Testing Parameters',
		'description'     => 'Parameters tested on incoming and finished batches.',
		'testing_context' => 'combination',
		'items'           => array(
			array( 'name' => 'Moisture Content', 'method' => 'Dean & Stark', 'standard' => '< 10%', 'description' => 'Ensures shelf stability and prevents mold growth.' ),
		),
	),
	'sourcing' => array(
		'eyebrow'     => 'Origins',
		'heading'     => 'Ethical Procurement Philosophy',
		'description' => 'Transparent grower relationships in prime agricultural zones.',
		'highlights'  => array(
			array( 'title' => 'Harvest Season Alignment', 'description' => 'Procurement strictly during peak crop harvest.' ),
		),
	),
	'regions' => array(
		'eyebrow'     => 'Geographic Origins',
		'heading'     => 'Sourcing Regions',
		'description' => 'Agro-climatic locations where select crops are grown.',
		'items'       => array(
			array( 'name' => 'Wayanad High Ranges', 'state' => 'Kerala', 'country' => 'India', 'ingredient' => 'Black Pepper', 'description' => 'High altitude volcanic soil with high piperine content.' ),
		),
	),
	'raw_materials' => array(
		'heading'     => 'Raw Material Acceptance Criteria',
		'description' => 'Specifications for fresh harvest lots.',
		'items'       => array(
			array( 'title' => 'Visual Cleanliness', 'description' => 'Free from discoloration and extraneous material.' ),
		),
	),
	'traceability' => array(
		'heading'     => 'Lot Traceability Framework',
		'description' => 'End-to-end batch tagging.',
		'steps'       => array(
			array( 'step_number' => '01', 'title' => 'Farmer Inward Tag', 'description' => 'Unique lot identification upon gate entry.' ),
		),
	),
	'food_safety' => array(
		'heading'     => 'Food Safety Practices',
		'description' => 'Preventive controls and sanitation standards.',
		'practices'   => array(
			array( 'title' => 'Glass & Plastic Policy', 'description' => 'Strict physical hazard controls across all lines.' ),
		),
	),
	'certifications' => array(
		'eyebrow'     => 'Certifications',
		'heading'     => 'Verified Accreditations',
		'description' => 'National and international food compliance certifications.',
	),
	'statistics' => array(
		'eyebrow'     => 'Quality Metrics',
		'heading'     => 'Testing Turnaround & Standards',
		'description' => 'Documented verification metrics.',
		'items'       => array(
			array( 'value' => '100', 'suffix' => '%', 'label' => 'Batch Tested', 'order' => 10 ),
		),
	),
	'gallery' => array(
		'eyebrow'        => 'Quality Gallery',
		'heading'        => 'Inspection in Action',
		'description'    => 'Visual record of grading and quality testing.',
		'attachment_ids' => array( 31, 33 ),
	),
	'products' => array(
		'eyebrow'     => 'Tested Range',
		'heading'     => 'Verified Spice Categories',
		'description' => 'Explore the spices processed under these standards.',
	),
	'b2b_cta' => array(
		'heading'           => 'Request Certificate of Analysis (COA)',
		'description'       => 'We provide lot-specific COAs and technical specification sheets.',
		'primary_cta_label' => 'Request COA',
		'primary_cta_url'   => '#sc-quality-final-cta',
	),
	'final_cta' => array(
		'heading'       => 'Connect with Quality Assurance Team',
		'description'   => 'Direct technical assistance regarding microbial standards and physical specifications.',
		'cta_label'     => 'Inquire Now',
		'cta_url'       => '/contact/',
		'show_whatsapp' => 1,
		'show_email'    => 1,
	),
);

// Save test quality settings
spicecraft_update_quality_settings( $test_q );

echo "\n=== 4. Auditing Manufacturing Page with All 15 Sections Enabled ===\n";
$mfg_full_html = file_get_contents( 'http://localhost/manufacturing/' );

$mfg_expected_anchors = array(
	'sc-mfg-hero',
	'sc-mfg-intro',
	'sc-mfg-facility',
	'sc-mfg-process',
	'sc-mfg-capabilities',
	'sc-mfg-equipment',
	'sc-mfg-hygiene',
	'sc-mfg-packaging',
	'sc-mfg-warehousing',
	'sc-mfg-stats',
	'sc-mfg-gallery',
	'sc-mfg-certifications',
	'sc-mfg-products',
	'sc-mfg-b2b-cta',
	'sc-mfg-final-cta',
);

foreach ( $mfg_expected_anchors as $anchor ) {
	sc_test( strpos( $mfg_full_html, 'id="' . $anchor . '"' ) !== false, "Manufacturing section #{$anchor} rendered", $results );
}

preg_match_all( '/<h1[^>]*>(.*?)<\/h1>/is', $mfg_full_html, $mfg_full_h1s );
sc_test( count( $mfg_full_h1s[0] ) === 1, "Manufacturing full page has exactly one <h1> tag", $results );

echo "\n=== 5. Auditing Quality & Sourcing Page with All 16 Sections Enabled ===\n";
$q_full_html = file_get_contents( 'http://localhost/quality/' );

$q_expected_anchors = array(
	'sc-quality-hero',
	'sc-quality-intro',
	'sc-quality-principles',
	'sc-quality-process',
	'sc-quality-testing',
	'sc-quality-sourcing',
	'sc-quality-regions',
	'sc-quality-raw-materials',
	'sc-quality-traceability',
	'sc-quality-food-safety',
	'sc-quality-certifications',
	'sc-quality-stats',
	'sc-quality-gallery',
	'sc-quality-products',
	'sc-quality-b2b-cta',
	'sc-quality-final-cta',
);

foreach ( $q_expected_anchors as $anchor ) {
	sc_test( strpos( $q_full_html, 'id="' . $anchor . '"' ) !== false, "Quality section #{$anchor} rendered", $results );
}

preg_match_all( '/<h1[^>]*>(.*?)<\/h1>/is', $q_full_html, $q_full_h1s );
sc_test( count( $q_full_h1s[0] ) === 1, "Quality full page has exactly one <h1> tag", $results );

// Check testing context badge
sc_test( strpos( $q_full_html, 'Hybrid Protocol: In-House &amp; Independent External Testing' ) !== false || strpos( $q_full_html, 'Hybrid Protocol' ) !== false, "Testing context badge rendered correctly", $results );

echo "\n=== 6. User Journey 7: Disabling a Section and Verifying Disappearance ===\n";
// Disable warehousing section in Manufacturing
$test_mfg['sections_enabled']['warehousing'] = 0;
spicecraft_update_manufacturing_settings( $test_mfg );

$mfg_disabled_html = file_get_contents( 'http://localhost/manufacturing/' );
sc_test( strpos( $mfg_disabled_html, 'id="sc-mfg-warehousing"' ) === false, "Disabled section #sc-mfg-warehousing cleanly disappeared without gaps", $results );

// Re-enable warehousing
$test_mfg['sections_enabled']['warehousing'] = 1;
spicecraft_update_manufacturing_settings( $test_mfg );

echo "\n=== 7. Catalog Mode Regression Check on Manufacturing & Quality Pages ===\n";
$forbidden_terms = array( 'add_to_cart', 'add-to-cart', 'wc-forward', 'woocommerce-cart-form' );
foreach ( $forbidden_terms as $ft ) {
	sc_test( stripos( $mfg_full_html, $ft ) === false, "Manufacturing page has NO '{$ft}' (catalog mode safe)", $results );
	sc_test( stripos( $q_full_html, $ft ) === false, "Quality page has NO '{$ft}' (catalog mode safe)", $results );
}

echo "\n=== 8. Restoring Zero-Fabricated-Content Clean Schema (Critical Content Rule) ===\n";
spicecraft_update_manufacturing_settings( spicecraft_get_manufacturing_default_settings() );
spicecraft_update_quality_settings( spicecraft_get_quality_default_settings() );

$mfg_clean_html = file_get_contents( 'http://localhost/manufacturing/' );
$q_clean_html   = file_get_contents( 'http://localhost/quality/' );

sc_test( strpos( $mfg_clean_html, 'sc-mfg-empty' ) !== false, "Manufacturing restored to clean default empty state", $results );
sc_test( strpos( $q_clean_html, 'sc-quality-empty' ) !== false, "Quality restored to clean default empty state", $results );

echo "\n============================================\n";
echo "Total Passed: {$results['passed']}, Total Failed: {$results['failed']}\n";
echo "============================================\n";
if ( $results['failed'] > 0 ) {
	exit( 1 );
}
