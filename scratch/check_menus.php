<?php
require_once __DIR__ . '/../wp-load.php';
$locations = get_nav_menu_locations();
foreach ($locations as $loc => $menu_id) {
    $menu = wp_get_nav_menu_object($menu_id);
    echo $loc . ': ' . ($menu ? $menu->name : 'none') . PHP_EOL;
    if ($menu) {
        $items = wp_get_nav_menu_items($menu_id);
        if ($items) {
            foreach ($items as $item) {
                echo '  - ' . $item->title . ' (' . $item->url . ')' . PHP_EOL;
            }
        }
    }
}
