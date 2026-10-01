<?php
require_once __DIR__ . '/../wp-load.php';

$cat_term = get_term_by( 'slug', 'manufacturing-processing', 'category' );
echo "Cat term ID: {$cat_term->term_id}, Name: {$cat_term->name}\n";

global $wp_query;
query_posts( array( 'cat' => $cat_term->term_id, 'posts_per_page' => 5 ) );
$wp_query->is_category = true;
$wp_query->is_archive  = true;
$wp_query->queried_object = $cat_term;
$wp_query->queried_object_id = $cat_term->term_id;

$theme_dir = get_template_directory();
ob_start();
include $theme_dir . '/category.php';
$html = ob_get_clean();

preg_match_all( '/<h1\b[^>]*>(.*?)<\/h1>/is', $html, $matches );
echo "H1 tags found: " . count( $matches[0] ) . "\n";
foreach ( $matches[1] as $m ) {
	echo " - H1: " . trim( $m ) . "\n";
}

echo "Contains 'Manufacturing & Processing' or 'Manufacturing &amp; Processing'?\n";
echo "Plain: " . ( strpos( $html, 'Manufacturing & Processing' ) !== false ? 'YES' : 'NO' ) . "\n";
echo "Escaped &amp;: " . ( strpos( $html, 'Manufacturing &amp; Processing' ) !== false ? 'YES' : 'NO' ) . "\n";
