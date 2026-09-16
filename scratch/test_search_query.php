<?php
require_once __DIR__ . '/../wp-load.php';

$term = 'SC-TUR-500';
$q = new WP_Query([
    'post_type' => 'product',
    's'         => $term,
    'posts_per_page' => 5,
]);

echo "Found posts: " . $q->found_posts . "\n";
foreach ($q->posts as $p) {
    echo " - " . $p->ID . ": " . $p->post_title . "\n";
}
