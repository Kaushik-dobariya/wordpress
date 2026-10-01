<?php
require_once __DIR__ . '/../wp-load.php';

$locations = get_nav_menu_locations();
if ( ! empty( $locations['primary'] ) ) {
	$menu_id = $locations['primary'];
	$items   = wp_get_nav_menu_items( $menu_id );
	$has_careers = false;
	foreach ( $items as $it ) {
		if ( false !== strpos( strtolower( $it->title ), 'career' ) || false !== strpos( strtolower( $it->url ), 'career' ) ) {
			$has_careers = true;
			break;
		}
	}

	if ( ! $has_careers ) {
		$item_id = wp_update_nav_menu_item( $menu_id, 0, array(
			'menu-item-title'   => 'Careers',
			'menu-item-url'     => home_url( '/careers/' ),
			'menu-item-status'  => 'publish',
			'menu-item-type'    => 'custom',
		) );
		echo "Added Careers to Primary Menu (ID: {$menu_id}, Item ID: {$item_id})\n";
	} else {
		echo "Careers already exists in Primary Menu.\n";
	}
}

// Ensure page-careers exists in DB with template page-careers.php
$careers_page = get_page_by_path( 'careers' );
if ( ! $careers_page ) {
	$p_id = wp_insert_post( array(
		'post_title'    => 'Careers',
		'post_name'     => 'careers',
		'post_status'   => 'publish',
		'post_type'     => 'page',
		'page_template' => 'page-careers.php',
	) );
	update_post_meta( $p_id, '_wp_page_template', 'page-careers.php' );
	echo "Created Careers page: ID {$p_id}\n";
} else {
	update_post_meta( $careers_page->ID, '_wp_page_template', 'page-careers.php' );
	echo "Careers page exists: ID {$careers_page->ID}\n";
}
