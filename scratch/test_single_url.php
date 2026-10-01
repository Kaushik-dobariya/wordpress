<?php
require_once __DIR__ . '/../wp-load.php';
$p = get_page_by_title('Chef Vikramaditya Rathore', OBJECT, 'sc_testimonial');
echo "ID: " . $p->ID . PHP_EOL;
echo "Slug: " . $p->post_name . PHP_EOL;
echo "Permalink: " . get_permalink($p) . PHP_EOL;
