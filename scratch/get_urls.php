<?php
require_once dirname( __DIR__ ) . '/wp-load.php';
$p = get_post( 25 );
echo "Home URL: " . home_url( '/' ) . "\n";
echo "Product 25 URL: " . get_permalink( 25 ) . "\n";
echo "Shop URL: " . wc_get_page_permalink( 'shop' ) . "\n";
