<?php
/**
 * Test HTTP Endpoints for Blog CMS
 */

require_once __DIR__ . '/../wp-load.php';

echo "=== TESTING LIVE BLOG HTTP ENDPOINTS ===\n";

$endpoints = array(
	'Blog Index' => home_url( '/blog/' ),
	'Blog Search (Canonical)' => home_url( '/?s=cryogenic&post_type=post' ),
	'Category Archive' => home_url( '/category/manufacturing-processing/' ),
);

$articles = get_posts( array( 'post_type' => 'post', 'posts_per_page' => 1 ) );
if ( ! empty( $articles ) ) {
	$endpoints['Single Article'] = get_permalink( $articles[0]->ID );
}

foreach ( $endpoints as $label => $url ) {
	echo "\nFetching: {$label} ({$url})\n";
	$response = wp_remote_get( $url, array( 'timeout' => 10, 'redirection' => 5 ) );
	if ( is_wp_error( $response ) ) {
		echo " [FAIL] WP_Error: " . $response->get_error_message() . "\n";
		continue;
	}

	$code = wp_remote_retrieve_response_code( $response );
	$body = wp_remote_retrieve_body( $response );

	preg_match_all( '/<h1\b[^>]*>(.*?)<\/h1>/is', $body, $h1_matches );
	$h1_count = count( $h1_matches[0] );
	$h1_text  = $h1_count > 0 ? trim( strip_tags( $h1_matches[1][0] ) ) : 'None';

	echo " - HTTP Status: {$code}\n";
	echo " - H1 Count: {$h1_count} ('{$h1_text}')\n";
	echo " - HTML Length: " . strlen( $body ) . " bytes\n";

	if ( 200 === $code ) {
		echo " [PASS] {$label} rendered successfully (HTTP 200)\n";
	} else {
		echo " [FAIL] {$label} returned HTTP {$code}\n";
	}
}
