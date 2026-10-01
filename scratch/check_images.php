<?php
require_once __DIR__ . '/../wp-load.php';
$atts = get_posts( array(
    'post_type'      => 'attachment',
    'posts_per_page' => 20,
    'post_mime_type' => 'image',
) );
foreach ( $atts as $a ) {
    echo $a->ID . ' | ' . $a->post_title . PHP_EOL;
}
