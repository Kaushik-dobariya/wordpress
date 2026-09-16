<?php
require_once __DIR__ . '/../wp-load.php';

if (function_exists('spicecraft_ensure_favourites_page')) {
    spicecraft_ensure_favourites_page();
}
flush_rewrite_rules();

$fav = get_page_by_path('favourites');
if ($fav) {
    // Set template to page-favourites.php if not set
    update_post_meta($fav->ID, '_wp_page_template', 'page-favourites.php');
    echo "Favourites page exists! ID: " . $fav->ID . " status: " . $fav->post_status . " url: " . get_permalink($fav->ID) . "\n";
} else {
    echo "Favourites page missing!\n";
}
