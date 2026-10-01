<?php
/**
 * Setup WordPress Menus matching the requested clean structure:
 * Top menu: Home, About us, Products, Contact us
 * Footer menus: Manufacturing, Recipes, Quality & Sourcing, Careers, Blog, etc.
 */

require_once __DIR__ . '/../wp-load.php';

// 1. Primary Menu
$primary_menu_name = 'Primary Navigation';
$primary_menu = wp_get_nav_menu_object( $primary_menu_name );

if ( ! $primary_menu ) {
	$primary_menu_id = wp_create_nav_menu( $primary_menu_name );
} else {
	$primary_menu_id = $primary_menu->term_id;
	// Clear existing items to re-populate cleanly
	$items = wp_get_nav_menu_items( $primary_menu_id );
	if ( $items ) {
		foreach ( $items as $it ) {
			wp_delete_post( $it->ID, true );
		}
	}
}

// Add 4 clean items to Primary Menu
wp_update_nav_menu_item( $primary_menu_id, 0, array(
	'menu-item-title'  => 'Home',
	'menu-item-url'    => home_url( '/' ),
	'menu-item-status' => 'publish',
) );

wp_update_nav_menu_item( $primary_menu_id, 0, array(
	'menu-item-title'  => 'About Us',
	'menu-item-url'    => home_url( '/about/' ),
	'menu-item-status' => 'publish',
) );

$shop_url = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/shop/' );
wp_update_nav_menu_item( $primary_menu_id, 0, array(
	'menu-item-title'  => 'Products',
	'menu-item-url'    => $shop_url,
	'menu-item-status' => 'publish',
) );

wp_update_nav_menu_item( $primary_menu_id, 0, array(
	'menu-item-title'  => 'Contact Us',
	'menu-item-url'    => home_url( '/#contact' ),
	'menu-item-status' => 'publish',
) );

// 2. Footer Menu 1: Company & Facilities
$footer1_name = 'Footer Company';
$footer1_menu = wp_get_nav_menu_object( $footer1_name );
if ( ! $footer1_menu ) {
	$footer1_id = wp_create_nav_menu( $footer1_name );
} else {
	$footer1_id = $footer1_menu->term_id;
	$items = wp_get_nav_menu_items( $footer1_id );
	if ( $items ) {
		foreach ( $items as $it ) {
			wp_delete_post( $it->ID, true );
		}
	}
}

wp_update_nav_menu_item( $footer1_id, 0, array(
	'menu-item-title'  => 'About Us',
	'menu-item-url'    => home_url( '/about/' ),
	'menu-item-status' => 'publish',
) );
wp_update_nav_menu_item( $footer1_id, 0, array(
	'menu-item-title'  => 'Manufacturing & Milling',
	'menu-item-url'    => home_url( '/manufacturing/' ),
	'menu-item-status' => 'publish',
) );
wp_update_nav_menu_item( $footer1_id, 0, array(
	'menu-item-title'  => 'Quality & Sourcing',
	'menu-item-url'    => home_url( '/quality/' ),
	'menu-item-status' => 'publish',
) );
wp_update_nav_menu_item( $footer1_id, 0, array(
	'menu-item-title'  => 'Certifications & Accreditations',
	'menu-item-url'    => home_url( '/certifications/' ),
	'menu-item-status' => 'publish',
) );
wp_update_nav_menu_item( $footer1_id, 0, array(
	'menu-item-title'  => 'Careers & Opportunities',
	'menu-item-url'    => home_url( '/careers/' ),
	'menu-item-status' => 'publish',
) );

// 3. Footer Menu 2: Explore & Resources
$footer2_name = 'Footer Resources';
$footer2_menu = wp_get_nav_menu_object( $footer2_name );
if ( ! $footer2_menu ) {
	$footer2_id = wp_create_nav_menu( $footer2_name );
} else {
	$footer2_id = $footer2_menu->term_id;
	$items = wp_get_nav_menu_items( $footer2_id );
	if ( $items ) {
		foreach ( $items as $it ) {
			wp_delete_post( $it->ID, true );
		}
	}
}

wp_update_nav_menu_item( $footer2_id, 0, array(
	'menu-item-title'  => 'Products Catalog',
	'menu-item-url'    => $shop_url,
	'menu-item-status' => 'publish',
) );
wp_update_nav_menu_item( $footer2_id, 0, array(
	'menu-item-title'  => 'Recipes & Inspiration',
	'menu-item-url'    => home_url( '/recipes/' ),
	'menu-item-status' => 'publish',
) );
wp_update_nav_menu_item( $footer2_id, 0, array(
	'menu-item-title'  => 'Blog & Industry Insights',
	'menu-item-url'    => home_url( '/#blog' ),
	'menu-item-status' => 'publish',
) );
wp_update_nav_menu_item( $footer2_id, 0, array(
	'menu-item-title'  => 'Wholesale & Export Supply',
	'menu-item-url'    => home_url( '/#export-bulk' ),
	'menu-item-status' => 'publish',
) );
wp_update_nav_menu_item( $footer2_id, 0, array(
	'menu-item-title'  => 'Contact Trade Desk',
	'menu-item-url'    => home_url( '/#contact' ),
	'menu-item-status' => 'publish',
) );

// Assign to theme locations
$locations = get_theme_mod( 'nav_menu_locations', array() );
$locations['primary']  = $primary_menu_id;
$locations['mobile']   = $primary_menu_id;
$locations['footer_1'] = $footer1_id;
$locations['footer_2'] = $footer2_id;
set_theme_mod( 'nav_menu_locations', $locations );

echo "MENUS CONFIGURED SUCCESSFULLY!\n";
echo "Primary Menu ID: {$primary_menu_id}\n";
echo "Footer 1 Menu ID: {$footer1_id}\n";
echo "Footer 2 Menu ID: {$footer2_id}\n";
print_r( $locations );
