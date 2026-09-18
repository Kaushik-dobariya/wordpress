<?php
/**
 * Check and ensure /certifications/ page exists with page-certifications.php template.
 */
require_once __DIR__ . '/../wp-load.php';

$page = get_page_by_path( 'certifications' );
if ( ! $page ) {
	$page_id = wp_insert_post( array(
		'post_title'     => 'Certifications',
		'post_name'      => 'certifications',
		'post_status'    => 'publish',
		'post_type'      => 'page',
		'comment_status' => 'closed',
		'ping_status'    => 'closed',
	) );

	if ( $page_id && ! is_wp_error( $page_id ) ) {
		update_post_meta( $page_id, '_wp_page_template', 'page-certifications.php' );
		echo "PAGE_CREATED: ID {$page_id}\n";
	} else {
		echo "PAGE_CREATE_FAILED\n";
	}
} else {
	$current_template = get_post_meta( $page->ID, '_wp_page_template', true );
	if ( $current_template !== 'page-certifications.php' ) {
		update_post_meta( $page->ID, '_wp_page_template', 'page-certifications.php' );
		echo "PAGE_UPDATED_TEMPLATE: ID {$page->ID}\n";
	} else {
		echo "PAGE_EXISTS: ID {$page->ID}\n";
	}
}

// Flush rewrite rules to ensure /certifications/ and /certification/{slug}/ resolve seamlessly
flush_rewrite_rules();
echo "REWRITE_RULES_FLUSHED\n";
