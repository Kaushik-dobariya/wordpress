<?php
require_once __DIR__ . '/../wp-load.php';
update_option( 'woocommerce_coming_soon', 'no' );
echo "woocommerce_coming_soon updated to: " . get_option( 'woocommerce_coming_soon' ) . "\n";
