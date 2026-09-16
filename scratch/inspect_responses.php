<?php
require_once __DIR__ . '/../wp-load.php';
$shop_resp = wp_remote_get( wc_get_page_permalink( 'shop' ) );
$body = wp_remote_retrieve_body( $shop_resp );
file_put_contents( __DIR__ . '/shop_response.html', $body );

$sample_products = wc_get_products( array( 'limit' => 1 ) );
$detail_resp = wp_remote_get( get_permalink( $sample_products[0]->get_id() ) );
$dbody = wp_remote_retrieve_body( $detail_resp );
file_put_contents( __DIR__ . '/detail_response.html', $dbody );

echo "Saved HTML responses. Let's see title or headings:\n";
preg_match( '/<title>(.*?)<\/title>/', $body, $m1 );
echo "Shop Title: " . ( $m1[1] ?? 'None' ) . "\n";
preg_match( '/<title>(.*?)<\/title>/', $dbody, $m2 );
echo "Detail Title: " . ( $m2[1] ?? 'None' ) . "\n";
