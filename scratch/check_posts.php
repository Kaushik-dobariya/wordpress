<?php
require_once __DIR__ . '/../wp-load.php';

$posts = get_posts(['numberposts' => -1]);
echo "Found " . count($posts) . " posts:\n";
foreach ($posts as $p) {
    $cats = wp_get_post_categories($p->ID, ['fields' => 'names']);
    echo "- #{$p->ID}: {$p->post_title} (Cats: " . implode(', ', $cats) . ")\n";
}

$testimonials = get_posts(['post_type' => 'sc_testimonial', 'numberposts' => -1]);
echo "\nFound " . count($testimonials) . " testimonials:\n";
foreach ($testimonials as $t) {
    echo "- #{$t->ID}: {$t->post_title}\n";
}
