<?php
require_once __DIR__ . '/../wp-load.php';

echo "=== EXECUTING STEP 0 SAMPLE CONTENT PURGE ===" . PHP_EOL;

// 1. Reset spicecraft_about_settings to default empty schema
if (function_exists('spicecraft_get_about_default_settings')) {
    $clean_about = spicecraft_get_about_default_settings();
    update_option('spicecraft_about_settings', $clean_about);
    echo "✅ Purged spicecraft_about_settings -> reset to clean default schema." . PHP_EOL;
} else {
    echo "⚠️ Function spicecraft_get_about_default_settings not found." . PHP_EOL;
}

// 2. Delete test team members (Rajesh Varma, Dr. Ananya Sharma)
$test_members = get_posts(array(
    'post_type'   => 'spicecraft_team',
    'post_status' => 'any',
    'posts_per_page' => -1,
));

$deleted_team_count = 0;
foreach ($test_members as $tm) {
    wp_delete_post($tm->ID, true);
    $deleted_team_count++;
    echo "✅ Deleted demo team member post: '{$tm->post_title}' (ID: {$tm->ID})" . PHP_EOL;
}
if ($deleted_team_count === 0) {
    echo "ℹ️ No demo team members to delete." . PHP_EOL;
}

// 3. Purge placeholder FSSAI & GST numbers from spicecraft_global_settings
$global_settings = get_option('spicecraft_global_settings', array());
if (!empty($global_settings)) {
    $purged_fssai = false;
    $purged_gst   = false;
    if (isset($global_settings['fssai_license']) && '10019021004321' === $global_settings['fssai_license']) {
        $global_settings['fssai_license'] = '';
        $purged_fssai = true;
    }
    if (isset($global_settings['gst_number']) && '24AAACS1234A1Z5' === $global_settings['gst_number']) {
        $global_settings['gst_number'] = '';
        $purged_gst = true;
    }
    update_option('spicecraft_global_settings', $global_settings);
    if ($purged_fssai) echo "✅ Purged placeholder FSSAI number from spicecraft_global_settings." . PHP_EOL;
    if ($purged_gst)   echo "✅ Purged placeholder GST number from spicecraft_global_settings." . PHP_EOL;
}

echo "=== STEP 0 PURGE COMPLETE ===" . PHP_EOL;
