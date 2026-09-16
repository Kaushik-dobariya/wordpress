<?php
require_once __DIR__ . '/../wp-load.php';

$shop_url = wc_get_page_permalink( 'shop' );
echo "Shop URL: $shop_url\n";
$shop_resp = wp_remote_get( $shop_url );
$shop_code = wp_remote_retrieve_response_code( $shop_resp );
echo "Shop Code: $shop_code\n";
$shop_body = wp_remote_retrieve_body( $shop_resp );
echo "Shop Body Length: " . strlen( $shop_body ) . "\n";
echo "First 300 chars of Shop Body:\n" . substr( $shop_body, 0, 300 ) . "\n";

$sample_products = wc_get_products( array( 'limit' => 1 ) );
if ( ! empty( $sample_products ) ) {
    $detail_url = get_permalink( $sample_products[0]->get_id() );
    echo "Detail URL: $detail_url\n";
    $detail_resp = wp_remote_get( $detail_url );
    $detail_code = wp_remote_retrieve_response_code( $detail_resp );
    echo "Detail Code: $detail_code\n";
    $detail_body = wp_remote_retrieve_body( $detail_resp );
    echo "Detail Body Length: " . strlen( $detail_body ) . "\n";
    echo "First 300 chars of Detail Body:\n" . substr( $detail_body, 0, 300 ) . "\n";
}
