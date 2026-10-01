<?php
require_once __DIR__ . '/../wp-load.php';

// Check post types
$cpts = get_post_types( array( '_builtin' => false ), 'names' );
echo "REGISTERED CPTS: " . implode( ', ', $cpts ) . "\n\n";

// Check products
$products = get_posts( array(
	'post_type'      => 'product',
	'post_status'    => 'publish',
	'posts_per_page' => 10,
) );
echo "SAMPLE PRODUCTS (" . count( $products ) . "):\n";
foreach ( $products as $prod ) {
	echo "ID: {$prod->ID} | {$prod->post_title} | Slug: {$prod->post_name}\n";
}

// Check product categories
$prod_cats = get_terms( array(
	'taxonomy'   => 'product_cat',
	'hide_empty' => false,
) );
echo "\nPRODUCT CATEGORIES (" . count( $prod_cats ) . "):\n";
foreach ( $prod_cats as $cat ) {
	echo "ID: {$cat->term_id} | Name: {$cat->name} | Slug: {$cat->slug}\n";
}

// Check existing homepage options for recipes
$home_options = get_option( 'spicecraft_home_options', array() );
echo "\nHOMEPAGE RECIPES CONFIG:\n";
print_r( isset( $home_options['recipes'] ) ? $home_options['recipes'] : 'None' );
echo "\nSECTIONS ENABLED (recipes): " . ( isset( $home_options['sections_enabled']['recipes'] ) ? $home_options['sections_enabled']['recipes'] : 'not set' ) . "\n";
