<?php
require_once __DIR__ . '/../wp-load.php';

echo "==================================================" . PHP_EOL;
echo "TESTING DISABLED & EMPTY SECTION BEHAVIOR" . PHP_EOL;
echo "==================================================" . PHP_EOL;

$orig_settings = spicecraft_get_about_settings();

// Test 1: Disable Hero and Statistics
$test_settings = $orig_settings;
$test_settings['sections_enabled']['hero'] = 0;
$test_settings['sections_enabled']['statistics'] = 0;
update_option(SpiceCraft_About_Settings::OPTION_NAME, $test_settings);

$html = file_get_contents('http://localhost/about/');
if (strpos($html, 'id="about-hero"') === false) {
    echo "✅ PASS: Disabled Hero section is cleanly hidden" . PHP_EOL;
} else {
    echo "❌ FAIL: Disabled Hero section was rendered" . PHP_EOL;
}

if (strpos($html, 'id="company-statistics"') === false) {
    echo "✅ PASS: Disabled Statistics section is cleanly hidden" . PHP_EOL;
} else {
    echo "❌ FAIL: Disabled Statistics section was rendered" . PHP_EOL;
}

// Test 2: Enable Statistics, but empty items
$test_settings['sections_enabled']['statistics'] = 1;
$test_settings['statistics']['items'] = array();
update_option(SpiceCraft_About_Settings::OPTION_NAME, $test_settings);

$html = file_get_contents('http://localhost/about/');
if (strpos($html, 'id="company-statistics"') === false) {
    echo "✅ PASS: Empty Statistics section is cleanly hidden even when enabled" . PHP_EOL;
} else {
    echo "❌ FAIL: Empty Statistics section was rendered" . PHP_EOL;
}

// Test 3: Re-order: Put values (order 5) before intro (order 20)
$test_settings = $orig_settings;
$test_settings['sections_order']['values'] = 5;
$test_settings['sections_order']['introduction'] = 50;
update_option(SpiceCraft_About_Settings::OPTION_NAME, $test_settings);

$html = file_get_contents('http://localhost/about/');
$pos_values = strpos($html, 'id="core-values"');
$pos_intro  = strpos($html, 'id="company-intro"');

if ($pos_values !== false && $pos_intro !== false && $pos_values < $pos_intro) {
    echo "✅ PASS: Section re-ordering functions as configured (Values appears before Introduction)" . PHP_EOL;
} else {
    echo "❌ FAIL: Section re-ordering failed (pos_values: $pos_values, pos_intro: $pos_intro)" . PHP_EOL;
}

// Restore original settings
update_option(SpiceCraft_About_Settings::OPTION_NAME, $orig_settings);
echo "Restored original settings." . PHP_EOL;
