<?php
require_once dirname( __DIR__ ) . '/wp-load.php';
$atts = get_posts( array(
	'post_type'      => 'attachment',
	'posts_per_page' => 20,
	'post_mime_type' => 'image',
) );
foreach ( $atts as $a ) {
	echo "ID: {$a->ID} | Title: {$a->post_title}\n";
}
