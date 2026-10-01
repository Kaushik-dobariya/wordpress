<?php
require_once __DIR__ . '/../wp-load.php';

$settings = spicecraft_get_careers_settings();
var_dump($settings['show_closed_jobs']);

$q = new WP_Query([
    'post_type' => 'spicecraft_job',
    'post_status' => 'publish',
    'posts_per_page' => 10
]);

echo "Found posts: " . $q->found_posts . "\n";
foreach ($q->posts as $p) {
    echo "- ID: {$p->ID}, Title: {$p->post_title}, Status: {$p->post_status}\n";
    $m = spicecraft_get_job_meta($p->ID);
    echo "  Department: {$m['department']}, Location: {$m['location']}, Status: {$m['status']}, Closed: " . ($m['is_closed'] ? 'YES' : 'NO') . "\n";
}
