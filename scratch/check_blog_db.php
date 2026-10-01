<?php
/**
 * Diagnostic script to check current WordPress posts, categories, and settings
 */

require_once __DIR__ . '/../wp-load.php';

echo "=== WORDPRESS POSTS & CATEGORIES AUDIT ===\n";

$posts = get_posts( array(
	'post_type'      => 'post',
	'post_status'    => 'any',
	'posts_per_page' => -1,
) );

echo "Total Posts: " . count( $posts ) . "\n";
foreach ( $posts as $p ) {
	$cats = wp_get_post_categories( $p->ID, array( 'fields' => 'names' ) );
	$feat = get_post_meta( $p->ID, '_sc_post_is_featured', true );
	echo " - ID: {$p->ID}, Title: '{$p->post_title}', Status: {$p->post_status}, Categories: " . implode( ', ', $cats ) . ", Featured: " . ( $feat ? 'YES' : 'NO' ) . "\n";
}

$categories = get_categories( array( 'hide_empty' => false ) );
echo "\nTotal Categories: " . count( $categories ) . "\n";
foreach ( $categories as $c ) {
	echo " - ID: {$c->term_id}, Name: '{$c->name}', Slug: '{$c->slug}', Count: {$c->count}\n";
}

$blog_page = get_page_by_path( 'blog' );
echo "\nPage with path 'blog': " . ( $blog_page ? "Found (ID {$blog_page->ID}, Status {$blog_page->post_status})" : "Not Found" ) . "\n";

$page_for_posts = get_option( 'page_for_posts' );
echo "Option 'page_for_posts': " . ( $page_for_posts ? $page_for_posts : "0 (Default/None)" ) . "\n";
echo "Option 'show_on_front': " . get_option( 'show_on_front' ) . "\n";
echo "Permalink Structure: " . get_option( 'permalink_structure' ) . "\n";
