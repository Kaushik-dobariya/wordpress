<?php
require_once __DIR__ . '/../wp-load.php';

$users = get_users(array('role' => 'administrator'));
foreach ($users as $u) {
    echo "Admin: " . $u->user_login . " (ID: " . $u->ID . ")" . PHP_EOL;
}
