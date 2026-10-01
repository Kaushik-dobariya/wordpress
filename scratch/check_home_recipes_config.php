<?php
require_once __DIR__ . '/../wp-load.php';

$active = function_exists( 'spicecraft_get_homepage_active_sections' ) ? spicecraft_get_homepage_active_sections() : array();
echo "ACTIVE SECTIONS ON HOMEPAGE:\n";
print_r( $active );

$sec = function_exists( 'spicecraft_get_homepage_section' ) ? spicecraft_get_homepage_section( 'recipes' ) : array();
echo "RECIPES SECTION CONFIG:\n";
print_r( $sec );
