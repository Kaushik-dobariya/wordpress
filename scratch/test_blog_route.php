<?php
require_once __DIR__ . '/../wp-load.php';

$_SERVER['REQUEST_URI'] = '/blog/';
wp();
echo "is_404: " . (is_404() ? 'YES' : 'NO') . "\n";
echo "is_home: " . (is_home() ? 'YES' : 'NO') . "\n";
echo "is_page: " . (is_page() ? 'YES' : 'NO') . "\n";
echo "is_archive: " . (is_archive() ? 'YES' : 'NO') . "\n";
