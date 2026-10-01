<?php
require_once __DIR__ . '/../wp-load.php';

$attachments = get_posts( array(
	'post_type'      => 'attachment',
	'post_mime_type' => 'image',
	'posts_per_page' => 20,
) );

echo "Found " . count( $attachments ) . " image attachments:\n";
foreach ( $attachments as $a ) {
	echo " - ID {$a->ID}: {$a->post_title} (" . wp_get_attachment_url( $a->ID ) . ")\n";
}
