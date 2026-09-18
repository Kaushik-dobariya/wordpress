<?php
require_once __DIR__ . '/../wp-load.php';
require_once ABSPATH . 'wp-admin/includes/template.php';
require_once ABSPATH . 'wp-admin/includes/options.php';

echo "==================================================" . PHP_EOL;
echo "TESTING ABOUT CMS ADMIN SCREENS & PERMISSIONS" . PHP_EOL;
echo "==================================================" . PHP_EOL;

// 1. Check capability requirements
$settings = SpiceCraft_About_Settings::get_instance();

// Test as guest
wp_set_current_user(0);
if (!current_user_can('manage_options')) {
    echo "✅ PASS: Guest cannot manage_options" . PHP_EOL;
}

// Test as subscriber if exists, or create temporary subscriber
$sub_id = wp_create_user('test_sub_' . time(), 'password123', 'sub_' . time() . '@test.com');
$user = new WP_User($sub_id);
$user->set_role('subscriber');
wp_set_current_user($sub_id);

if (!current_user_can('manage_options')) {
    echo "✅ PASS: Subscriber cannot manage_options" . PHP_EOL;
}

// Clean up test user
require_once ABSPATH . 'wp-admin/includes/user.php';
wp_delete_user($sub_id);

// 2. Test as Administrator (ID 1)
wp_set_current_user(1);
if (current_user_can('manage_options')) {
    echo "✅ PASS: Administrator has manage_options" . PHP_EOL;
}

// 3. Render all 8 admin tabs and check for required elements
$tabs = array(
    'order'      => '1. Order & Visibility',
    'hero'       => '2. Hero & Intro',
    'story'      => '3. Story & Values',
    'pillars'    => '4. Philosophy Pillars',
    'journey'    => '5. Journey & Stats',
    'leadership' => '6. Leadership & Team',
    'trust'      => '7. Trust & Products',
    'cta'        => '8. Business CTAs',
);

foreach ($tabs as $tab_key => $tab_title) {
    $_GET['tab'] = $tab_key;
    $_GET['page'] = 'spicecraft-about';
    
    ob_start();
    $settings->render_page();
    $output = ob_get_clean();
    
    $has_form = strpos($output, 'action="options.php"') !== false;
    $has_submit = strpos($output, 'name="submit"') !== false;
    $has_nonce = strpos($output, '_wpnonce') !== false;
    $has_option_group = strpos($output, 'spicecraft_about_group') !== false;
    
    if ($has_form && $has_submit && $has_nonce && $has_option_group) {
        echo "✅ PASS: Admin Tab '$tab_key' ($tab_title) renders complete form with nonce & submit" . PHP_EOL;
    } else {
        echo "❌ FAIL: Admin Tab '$tab_key' missing critical elements (Form: " . ($has_form?'Y':'N') . ", Submit: " . ($has_submit?'Y':'N') . ", Nonce: " . ($has_nonce?'Y':'N') . ")" . PHP_EOL;
    }
}

// 4. Test Section Order UI in Order tab
$_GET['tab'] = 'order';
ob_start();
$settings->render_page();
$output = ob_get_clean();

$has_section_order_table = strpos($output, 'Configure section visibility and order') !== false;
$has_all_15_sections = true;
$sections = array(
    'hero', 'introduction', 'story', 'vision_mission', 'values',
    'quality', 'sourcing', 'manufacturing', 'statistics', 'milestones',
    'leadership', 'certifications', 'products', 'b2b_cta', 'final_cta'
);

foreach ($sections as $s) {
    if (strpos($output, "[sections_order][$s]") === false) {
        $has_all_15_sections = false;
        break;
    }
}

if ($has_section_order_table && $has_all_15_sections) {
    echo "✅ PASS: Section Display Order & Visibility table has all 15 sections configured" . PHP_EOL;
} else {
    echo "❌ FAIL: Section order table missing sections (Table found: " . ($has_section_order_table?'Y':'N') . ", All sections: " . ($has_all_15_sections?'Y':'N') . ")" . PHP_EOL;
}

// 5. Check Team CPT integration in Leadership tab
$_GET['tab'] = 'leadership';
ob_start();
$settings->render_page();
$people_output = ob_get_clean();

if (strpos($people_output, 'Rajesh Varma') !== false && strpos($people_output, 'Dr. Ananya Sharma') !== false) {
    echo "✅ PASS: Leadership tab lists published team members for selective display" . PHP_EOL;
} else {
    echo "❌ FAIL: Team members not listed in Leadership tab" . PHP_EOL;
}

