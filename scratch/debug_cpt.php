<?php
require_once __DIR__ . '/../wp-load.php';

$res = register_post_type('spicecraft_team', array('label' => 'Team'));
if (is_wp_error($res)) {
    echo "WP_Error: " . $res->get_error_message() . PHP_EOL;
} else {
    echo "Success: " . get_class($res) . " (Slug: " . $res->name . ")" . PHP_EOL;
}
