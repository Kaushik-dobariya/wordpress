<?php
require_once __DIR__ . '/../wp-load.php';

$s = get_option('spicecraft_global_settings', []);
$s['email_general'] = 'info@spicecraft.com';
$s['email_sales']   = 'sales@spicecraft.com';
$s['email_export']  = 'exports@spicecraft.com';
$s['email_career']  = 'career@cubeontechs.com';
update_option('spicecraft_global_settings', $s);
echo "Updated global settings emails successfully!\n";
