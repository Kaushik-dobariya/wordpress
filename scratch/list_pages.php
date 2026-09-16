<?php
require_once __DIR__ . '/../wp-load.php';
$pages = get_pages();
foreach ( $pages as $p ) {
    echo "ID: {$p->ID} | Title: {$p->post_title} | Slug: {$p->post_name}\n";
}
