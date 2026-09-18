<?php
require_once __DIR__ . '/../wp-load.php';

$pages = array(
	'Home'          => home_url( '/' ),
	'About'         => home_url( '/about/' ),
	'Manufacturing' => home_url( '/manufacturing/' ),
	'Quality'       => home_url( '/quality/' ),
	'Certifications'=> home_url( '/certifications/' ),
	'Shop'          => wc_get_page_permalink( 'shop' ),
	'Product 21'    => get_permalink( 21 ),
);

foreach ( $pages as $name => $url ) {
	$resp = wp_remote_get( $url );
	$code = wp_remote_retrieve_response_code( $resp );
	$body = wp_remote_retrieve_body( $resp );
	$has_fatal = false !== strpos( $body, 'Fatal error' ) || false !== strpos( $body, 'Parse error' );
	echo "Page [{$name}]: HTTP {$code}, Fatal: " . ( $has_fatal ? 'YES' : 'NO' ) . PHP_EOL;
}
