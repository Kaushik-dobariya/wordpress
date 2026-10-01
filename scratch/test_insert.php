<?php
require_once __DIR__ . '/../wp-load.php';
$res = wp_insert_post( array(
    'post_title'   => 'Chef Test One',
    'post_content' => 'Test content goes here.',
    'post_type'    => 'sc_testimonial',
    'post_status'  => 'publish',
), true );
if ( is_wp_error( $res ) ) {
    global $wpdb;
    echo "Error: " . $res->get_error_message() . PHP_EOL;
    echo "DB Error: " . $wpdb->last_error . PHP_EOL;
    echo "Last Query: " . $wpdb->last_query . PHP_EOL;
} else {
    echo "Success: " . $res . PHP_EOL;
}
