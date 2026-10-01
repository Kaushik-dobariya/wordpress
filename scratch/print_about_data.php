<?php
require_once __DIR__ . '/../wp-load.php';

$settings = get_option( 'spicecraft_about_settings', array() );
foreach ( array( 'hero', 'introduction', 'story', 'vision_mission', 'values', 'quality', 'sourcing', 'manufacturing', 'b2b_cta', 'final_cta' ) as $k ) {
    echo "=== SECTION: $k ===\n";
    print_r( isset( $settings[$k] ) ? $settings[$k] : 'NOT SET' );
}
