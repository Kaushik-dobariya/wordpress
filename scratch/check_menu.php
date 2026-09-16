<?php
require_once __DIR__ . '/../wp-load.php';

$locs = get_nav_menu_locations();
print_r($locs);
if (!empty($locs['primary'])) {
    $menu = wp_get_nav_menu_object($locs['primary']);
    echo "Menu name: " . $menu->name . "\n";
    $items = wp_get_nav_menu_items($menu);
    foreach ($items as $it) {
        echo "- " . $it->title . " (ID: " . $it->ID . ", Parent: " . $it->menu_item_parent . ")\n";
    }
} else {
    echo "No menu assigned to primary location. Fallback menu used.\n";
}
