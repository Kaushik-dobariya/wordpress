<?php
require_once __DIR__ . '/../wp-load.php';

$about_page = get_page_by_path( 'about' );
if ( ! $about_page ) {
	$about_page = get_page_by_path( 'about-us' );
}

if ( ! $about_page ) {
	$page_id = wp_insert_post( array(
		'post_title'   => 'About Us',
		'post_name'    => 'about',
		'post_status'  => 'publish',
		'post_type'    => 'page',
		'post_content' => '',
	) );

	if ( $page_id && ! is_wp_error( $page_id ) ) {
		update_post_meta( $page_id, '_wp_page_template', 'page-about.php' );
		echo "Created About Us page (ID: {$page_id}) with template page-about.php" . PHP_EOL;
	} else {
		echo "Error creating About page: " . ( is_wp_error( $page_id ) ? $page_id->get_error_message() : 'Unknown error' ) . PHP_EOL;
	}
} else {
	update_post_meta( $about_page->ID, '_wp_page_template', 'page-about.php' );
	echo "Existing About page found (ID: {$about_page->ID}), set template to page-about.php" . PHP_EOL;
}
