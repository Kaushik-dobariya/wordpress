<?php
require_once __DIR__ . '/../wp-load.php';
$res = wp_remote_get( home_url( '/testimonials/' ) );
$body = wp_remote_retrieve_body( $res );
preg_match_all( '/<h1[^>]*>(.*?)<\/h1>/is', $body, $m );
print_r( $m[1] );
