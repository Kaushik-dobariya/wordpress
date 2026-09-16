<?php
require_once __DIR__ . '/../wp-load.php';
echo "woocommerce_coming_soon: " . var_export( get_option( 'woocommerce_coming_soon' ), true ) . "\n";
echo "woocommerce_store_pages_only: " . var_export( get_option( 'woocommerce_store_pages_only' ), true ) . "\n";
