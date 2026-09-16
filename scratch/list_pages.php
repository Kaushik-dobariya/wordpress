<?php
require_once __DIR__ . '/../wp-load.php';

echo "--- PAGES ---\n";
$pages = get_posts(['post_type' => 'page', 'posts_per_page' => -1]);
foreach ($pages as $p) {
    echo $p->ID . ': ' . $p->post_title . ' (' . $p->post_name . ") - template: " . get_page_template_slug($p->ID) . "\n";
}
