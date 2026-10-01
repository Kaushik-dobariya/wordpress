<?php
require_once __DIR__ . '/../wp-load.php';
require_once ABSPATH . 'wp-admin/includes/admin.php';

wp_set_current_user( 1 );

global $menu, $submenu;

if ( class_exists( 'SpiceCraft_Global_Settings' ) ) {
	SpiceCraft_Global_Settings::get_instance()->register_admin_menu();
}
if ( class_exists( 'SpiceCraft_Blog_Settings' ) ) {
	SpiceCraft_Blog_Settings::get_instance()->register_admin_menu();
}

echo "Submenus keys:\n";
print_r( array_keys( (array) $submenu ) );

if ( isset( $submenu['spicecraft-overview'] ) ) {
	echo "\nSubmenu items for spicecraft-overview:\n";
	foreach ( $submenu['spicecraft-overview'] as $item ) {
		echo " - Title: {$item[0]}, Slug: {$item[2]}\n";
	}
}
