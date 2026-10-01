<?php
require_once __DIR__ . '/../wp-load.php';

$active = function_exists( 'spicecraft_get_about_active_sections' ) ? spicecraft_get_about_active_sections() : array();
echo "ACTIVE ABOUT SECTIONS:\n";
print_r( $active );

$about_settings = get_option( 'spicecraft_about_settings', array() );
echo "\nABOUT SETTINGS in DB:\n";
echo "Keys: " . implode( ', ', array_keys( $about_settings ) ) . "\n";
if ( isset( $about_settings['sections_enabled'] ) ) {
    echo "sections_enabled:\n";
    print_r( $about_settings['sections_enabled'] );
}

$about_html = file_get_contents( home_url( '/about/' ) );
echo "\nHTML LENGTH of /about/: " . strlen( $about_html ) . "\n";

// Find all section elements rendered
preg_match_all( '/<section[^>]*id="([^"]*)"/is', $about_html, $matches );
echo "SECTIONS RENDERED IN HTML:\n";
print_r( $matches[1] );
