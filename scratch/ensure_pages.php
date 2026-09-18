<?php
require_once dirname( __DIR__ ) . '/wp-load.php';

// 1. Manufacturing Page
$mfg_page = get_page_by_path( 'manufacturing' );
if ( ! $mfg_page ) {
	$mfg_id = wp_insert_post( array(
		'post_title'   => 'Manufacturing',
		'post_name'    => 'manufacturing',
		'post_status'  => 'publish',
		'post_type'    => 'page',
		'page_template'=> 'page-manufacturing.php',
	) );
	update_post_meta( $mfg_id, '_wp_page_template', 'page-manufacturing.php' );
	echo "Created Manufacturing page: ID {$mfg_id}\n";
} else {
	update_post_meta( $mfg_page->ID, '_wp_page_template', 'page-manufacturing.php' );
	echo "Manufacturing page exists: ID {$mfg_page->ID}\n";
}

// 2. Quality Page
$q_page = get_page_by_path( 'quality' );
if ( ! $q_page ) {
	$q_id = wp_insert_post( array(
		'post_title'   => 'Quality & Sourcing',
		'post_name'    => 'quality',
		'post_status'  => 'publish',
		'post_type'    => 'page',
		'page_template'=> 'page-quality.php',
	) );
	update_post_meta( $q_id, '_wp_page_template', 'page-quality.php' );
	echo "Created Quality & Sourcing page: ID {$q_id}\n";
} else {
	update_post_meta( $q_page->ID, '_wp_page_template', 'page-quality.php' );
	echo "Quality page exists: ID {$q_page->ID}\n";
}
