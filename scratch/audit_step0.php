<?php
require_once __DIR__ . '/../wp-load.php';

echo "=== STEP 0 AUDIT: CHECKING STORED OPTIONS & DATA ===" . PHP_EOL . PHP_EOL;

// 1. Check spicecraft_about_settings
$about_settings = get_option('spicecraft_about_settings', array());
echo "1. spicecraft_about_settings:" . PHP_EOL;
if (!empty($about_settings)) {
    echo "Found about_settings with keys: " . implode(', ', array_keys($about_settings)) . PHP_EOL;
    // Check specific demo claims
    $demo_claims = array(
        'hero_heading' => $about_settings['hero']['heading'] ?? '',
        'intro_content' => $about_settings['introduction']['content'] ?? '',
        'story_content' => $about_settings['story']['content'] ?? '',
        'quote_text' => $about_settings['story']['quote_text'] ?? '',
        'values_items' => count($about_settings['values']['items'] ?? array()),
        'quality_points' => count($about_settings['quality']['points'] ?? array()),
        'sourcing_points' => count($about_settings['sourcing']['points'] ?? array()),
        'mfg_points' => count($about_settings['manufacturing']['points'] ?? array()),
        'mfg_highlights' => count($about_settings['manufacturing']['highlights'] ?? array()),
        'stats_items' => count($about_settings['statistics']['items'] ?? array()),
        'milestone_items' => count($about_settings['milestones']['items'] ?? array()),
    );
    print_r($demo_claims);
} else {
    echo "Empty or not set." . PHP_EOL;
}

echo PHP_EOL . "2. spicecraft_global_settings:" . PHP_EOL;
$global_settings = get_option('spicecraft_global_settings', array());
if (!empty($global_settings)) {
    print_r($global_settings);
} else {
    echo "Empty or not set." . PHP_EOL;
}

echo PHP_EOL . "3. Team members (spicecraft_team):" . PHP_EOL;
$team = get_posts(array(
    'post_type' => 'spicecraft_team',
    'post_status' => 'any',
    'posts_per_page' => -1,
));
echo "Count: " . count($team) . PHP_EOL;
echo PHP_EOL . "5. spicecraft_homepage_settings:" . PHP_EOL;
$hp = get_option('spicecraft_homepage_settings', array());
if (!empty($hp)) {
    echo "HP keys: " . implode(', ', array_keys($hp)) . PHP_EOL;
    if (isset($hp['brand_story'])) {
        echo "Brand story: " . substr(strip_tags($hp['brand_story']['content'] ?? ''), 0, 100) . "..." . PHP_EOL;
    }
} else {
    echo "Empty or not set." . PHP_EOL;
}

echo PHP_EOL . "6. Certifications Taxonomy Terms:" . PHP_EOL;
$certs = get_terms(array(
    'taxonomy' => 'spicecraft_certification',
    'hide_empty' => false,
));
foreach ($certs as $c) {
    echo "- '{$c->name}' (slug: '{$c->slug}', count: {$c->count})" . PHP_EOL;
}
