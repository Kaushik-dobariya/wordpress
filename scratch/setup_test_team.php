<?php
require_once __DIR__ . '/../wp-load.php';

// Check if team members exist
$existing = get_posts(array(
    'post_type' => 'spicecraft_team',
    'post_status' => 'any',
    'posts_per_page' => -1,
));

echo "Existing team members: " . count($existing) . PHP_EOL;

if (empty($existing)) {
    // Create 2 test team members with authentic roles
    $member1_id = wp_insert_post(array(
        'post_title'   => 'Rajesh Varma',
        'post_content' => 'Oversees our master blending operations and quality benchmarking with deep expertise in single-origin Indian spices and traditional aroma preservation methods.',
        'post_status'  => 'publish',
        'post_type'    => 'spicecraft_team',
    ));
    update_post_meta($member1_id, '_sc_team_role', 'Head of Sourcing & Master Blender');
    update_post_meta($member1_id, '_sc_team_linkedin', 'https://linkedin.com/in/spicecraft-sourcing');
    update_post_meta($member1_id, '_sc_team_order', 10);
    set_post_thumbnail($member1_id, 31);

    $member2_id = wp_insert_post(array(
        'post_title'   => 'Dr. Ananya Sharma',
        'post_content' => 'Leads our modern cleanroom laboratory, cryogenic milling protocols, and ISO/FSSAI analytical testing for micro-biological purity.',
        'post_status'  => 'publish',
        'post_type'    => 'spicecraft_team',
    ));
    update_post_meta($member2_id, '_sc_team_role', 'Director of Quality & Food Science');
    update_post_meta($member2_id, '_sc_team_linkedin', 'https://linkedin.com/in/spicecraft-quality');
    update_post_meta($member2_id, '_sc_team_order', 20);
    set_post_thumbnail($member2_id, 32);

    echo "Created Member 1: Rajesh Varma (ID $member1_id)" . PHP_EOL;
    echo "Created Member 2: Dr. Ananya Sharma (ID $member2_id)" . PHP_EOL;
} else {
    foreach ($existing as $m) {
        echo "Found Member: {$m->post_title} (ID: {$m->ID})" . PHP_EOL;
    }
}
