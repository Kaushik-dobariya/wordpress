<?php
require_once __DIR__ . '/../wp-load.php';
$shop_resp = wp_remote_get( wc_get_page_permalink( 'shop' ) );
$body = wp_remote_retrieve_body( $shop_resp );
var_dump( strpos( $body, 'sc-catalog' ) );
var_dump( strpos( $body, 'sc-catalog-experience' ) );

$sample_products = wc_get_products( array( 'limit' => 1 ) );
$detail_resp = wp_remote_get( get_permalink( $sample_products[0]->get_id() ) );
$dbody = wp_remote_retrieve_body( $detail_resp );
var_dump( strpos( $dbody, 'sc-single-product' ) );
