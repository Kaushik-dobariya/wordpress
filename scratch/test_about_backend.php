<?php
require_once __DIR__ . '/../wp-load.php';

echo "============================================" . PHP_EOL;
echo "ABOUT CMS & BACKEND UNIT TEST SUITE" . PHP_EOL;
echo "============================================" . PHP_EOL;

$all_passed = true;

function test_assert($condition, $message) {
    global $all_passed;
    if ($condition) {
        echo "✅ PASS: {$message}" . PHP_EOL;
    } else {
        echo "❌ FAIL: {$message}" . PHP_EOL;
        $all_passed = false;
    }
}

// 1. Check helper functions exist
test_assert(function_exists('spicecraft_get_about_default_settings'), 'spicecraft_get_about_default_settings exists');
test_assert(function_exists('spicecraft_get_about_settings'), 'spicecraft_get_about_settings exists');
test_assert(function_exists('spicecraft_update_about_settings'), 'spicecraft_update_about_settings exists');
test_assert(function_exists('spicecraft_get_about_section'), 'spicecraft_get_about_section exists');
test_assert(function_exists('spicecraft_is_about_section_enabled'), 'spicecraft_is_about_section_enabled exists');
test_assert(function_exists('spicecraft_get_about_section_order'), 'spicecraft_get_about_section_order exists');
test_assert(function_exists('spicecraft_get_about_active_sections'), 'spicecraft_get_about_active_sections exists');
test_assert(function_exists('spicecraft_get_about_statistics'), 'spicecraft_get_about_statistics exists');
test_assert(function_exists('spicecraft_get_about_milestones'), 'spicecraft_get_about_milestones exists');
test_assert(function_exists('spicecraft_get_about_values'), 'spicecraft_get_about_values exists');
test_assert(function_exists('spicecraft_get_about_team_members'), 'spicecraft_get_about_team_members exists');

// 2. Check defaults schema
$defaults = spicecraft_get_about_default_settings();
test_assert(isset($defaults['sections_order']) && count($defaults['sections_order']) === 15, '15 sections defined in default order');
test_assert(isset($defaults['sections_enabled']) && count($defaults['sections_enabled']) === 15, '15 sections defined in default enabled map');
test_assert(empty($defaults['hero']['heading']), 'Hero heading is empty by default (no fabricated claims)');
test_assert(empty($defaults['statistics']['items']), 'Statistics items empty by default (no fabricated metrics)');
test_assert(empty($defaults['milestones']['items']), 'Milestones empty by default');

// 3. Check Team CPT
$cpt = get_post_type_object('spicecraft_team');
test_assert(!empty($cpt), 'Custom post type spicecraft_team is registered');
if ($cpt) {
    test_assert($cpt->show_ui === true, 'Team CPT has show_ui enabled');
    test_assert($cpt->show_in_menu === 'spicecraft-overview', 'Team CPT is nested under spicecraft-overview');
}

// 4. Test sanitization logic in SpiceCraft_About_Settings
$settings_obj = SpiceCraft_About_Settings::get_instance();
test_assert(!empty($settings_obj), 'SpiceCraft_About_Settings instance created');

$mock_input = array(
    'sections_order' => array('hero' => 15, 'introduction' => 25),
    'sections_enabled' => array('hero' => 1, 'statistics' => 0),
    'hero' => array(
        'eyebrow' => '<script>alert("xss")</script>Authentic Purity',
        'heading' => 'SpiceCraft Heritage & Blends',
        'highlight_text' => 'Since Inception',
        'description' => '<b>Leading</b> manufacturer of pure spice blends.',
        'desktop_image_id' => '31',
        'primary_cta_label' => 'Explore Range',
        'primary_cta_url' => 'https://example.com/shop',
    ),
    'values' => array(
        'items' => array(
            array('icon' => 'shield', 'title' => 'Pure Purity', 'description' => 'Zero adulterants.', 'order' => 10),
            array('icon' => '', 'title' => '', 'description' => '', 'order' => 20), // Should be filtered out
        ),
    ),
    'statistics' => array(
        'items' => array(
            array('value' => '25', 'suffix' => '+', 'label' => 'Years Heritage', 'description' => 'Generational mastery', 'order' => 10),
        ),
    ),
    'milestones' => array(
        'items' => array(
            array('date_label' => '1998', 'title' => 'The Foundation', 'description' => 'First processing unit.', 'order' => 10),
        ),
    ),
);

$sanitized = $settings_obj->sanitize_settings($mock_input);
test_assert($sanitized['sections_order']['hero'] === 15, 'Section order sanitized and updated');
test_assert($sanitized['sections_enabled']['hero'] === 1, 'Hero enabled set to 1');
test_assert($sanitized['sections_enabled']['statistics'] === 0, 'Statistics enabled set to 0');
test_assert(strpos($sanitized['hero']['eyebrow'], '<script>') === false, 'XSS stripped from hero eyebrow');
test_assert($sanitized['hero']['eyebrow'] === 'Authentic Purity', 'Sanitized eyebrow content matches');
test_assert($sanitized['hero']['desktop_image_id'] === 31, 'Desktop image ID sanitized as int 31');
test_assert(count($sanitized['values']['items']) === 1, 'Empty value items filtered out');
test_assert(count($sanitized['statistics']['items']) === 1, 'Valid statistic row retained');
test_assert(count($sanitized['milestones']['items']) === 1, 'Valid milestone row retained');

// 5. Test About page template assignment
$about_page = get_page_by_path('about');
test_assert(!empty($about_page), 'About page exists in WordPress');
if ($about_page) {
    $template = get_page_template_slug($about_page->ID);
    test_assert($template === 'page-about.php', 'About page has template set to page-about.php');
}

echo PHP_EOL . ($all_passed ? ">>> ALL BACKEND TESTS PASSED <<<" : ">>> SOME BACKEND TESTS FAILED <<<") . PHP_EOL;
