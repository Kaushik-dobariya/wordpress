<?php
require_once __DIR__ . '/../wp-load.php';

$p = get_post(24);
if ($p) {
    echo "Found post 24: " . $p->post_title . " (Status: " . $p->post_status . ")\n";
    echo "URL: " . get_permalink(24) . "\n";
} else {
    echo "Post 24 NOT found. Listing products:\n";
    $prods = get_posts(['post_type' => 'product', 'posts_per_page' => 5]);
    foreach ($prods as $pr) {
        echo "- ID: {$pr->ID}, Title: {$pr->post_title}\n";
    }
}
