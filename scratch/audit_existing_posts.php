<?php
require_once __DIR__ . '/../wp-load.php';

$posts = get_posts( array(
	'post_type'      => 'post',
	'post_status'    => 'any',
	'posts_per_page' => -1,
) );

echo "TOTAL POSTS: " . count( $posts ) . "\n";
foreach ( $posts as $p ) {
	$cats = wp_get_post_categories( $p->ID, array( 'fields' => 'names' ) );
	echo "ID: {$p->ID} | Status: {$p->post_status} | Title: {$p->post_title} | Categories: " . implode( ', ', $cats ) . "\n";
}

$categories = get_terms( array(
	'taxonomy'   => 'category',
	'hide_empty' => false,
) );
echo "\nCATEGORIES:\n";
foreach ( $categories as $cat ) {
	echo "Term: {$cat->name} (slug: {$cat->slug}, ID: {$cat->term_id}, count: {$cat->count})\n";
}
