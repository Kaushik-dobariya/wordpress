<?php
require_once __DIR__ . '/../wp-load.php';

$posts = get_posts( array(
    'post_type'   => 'spicecraft_recipe',
    'post_status' => 'any',
    'numberposts' => -1,
) );

echo "Found " . count( $posts ) . " recipes in DB:\n";
foreach ( $posts as $p ) {
    echo "Deleting: {$p->ID} - {$p->post_title}\n";
    wp_delete_post( $p->ID, true );
}
echo "Cleaned.\n";
