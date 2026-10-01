<?php
require_once __DIR__ . '/../wp-load.php';

$apps = get_posts( array(
	'post_type'   => 'spicecraft_app',
	'numberposts' => 10,
	'post_status' => 'any',
) );

echo "Found " . count( $apps ) . " applications:\n";
foreach ( $apps as $a ) {
	echo "----------------------------------------\n";
	echo "ID: " . $a->ID . "\n";
	echo "Title: " . $a->post_title . "\n";
	echo "Date: " . $a->post_date . "\n";
	echo "Status: " . get_post_meta( $a->ID, '_sc_app_status', true ) . "\n";
	echo "Applicant Email: " . get_post_meta( $a->ID, '_sc_app_email', true ) . "\n";
	echo "Email Sent Flag: " . get_post_meta( $a->ID, '_sc_app_email_sent', true ) . "\n";
	echo "Email Sent To: " . get_post_meta( $a->ID, '_sc_app_email_to', true ) . "\n";
	echo "Email Error: " . get_post_meta( $a->ID, '_sc_app_email_error', true ) . "\n";
	echo "Resume File: " . get_post_meta( $a->ID, '_sc_app_resume_file', true ) . "\n";
	echo "File Exists on Disk: " . ( file_exists( get_post_meta( $a->ID, '_sc_app_resume_file', true ) ) ? 'YES' : 'NO' ) . "\n";
}
