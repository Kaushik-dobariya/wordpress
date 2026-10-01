<?php
require_once __DIR__ . '/../wp-load.php';

$menus = wp_get_nav_menus();
echo "MENUS COUNT: " . count( $menus ) . "\n";
foreach ( $menus as $m ) {
    echo "Menu ID {$m->term_id}: {$m->name}\n";
    $items = wp_get_nav_menu_items( $m->term_id );
    if ( $items ) {
        foreach ( $items as $it ) {
            echo "  - {$it->title} ({$it->url})\n";
        }
    }
}
$locs = get_nav_menu_locations();
echo "\nLOCATIONS:\n";
print_r( $locs );
