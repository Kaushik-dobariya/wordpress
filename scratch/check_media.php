<?php
require_once __DIR__ . '/../wp-load.php';

$attachments = get_posts([
    'post_type' => 'attachment',
    'numberposts' => 50,
    'post_status' => 'inherit',
]);

echo "Total attachments: " . count($attachments) . PHP_EOL;
foreach ($attachments as $a) {
    echo "ID: {$a->ID} | Title: {$a->post_title} | File: " . get_post_meta($a->ID, '_wp_attached_file', true) . PHP_EOL;
}
