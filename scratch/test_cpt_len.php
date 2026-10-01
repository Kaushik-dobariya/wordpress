<?php
require_once __DIR__ . '/../wp-load.php';

$res = register_post_type('spicecraft_app', ['public'=>false]);
echo "Registered: " . ( is_wp_error($res) ? $res->get_error_message() : 'SUCCESS' ) . "\n";
