<?php
require_once __DIR__ . '/../wp-load.php';

echo "=== POST TYPES ===\n";
$pts = get_post_types(array('public' => true), 'names');
print_r($pts);

echo "\n=== NATIVE POSTS (type 'post') ===\n";
$posts = get_posts(array('post_type' => 'post', 'numberposts' => -1, 'post_status' => 'any'));
echo "Total posts: " . count($posts) . "\n";
foreach ($posts as $p) {
    echo "- ID: {$p->ID} | Title: {$p->post_title} | Status: {$p->post_status} | Slug: {$p->post_name}\n";
}

echo "\n=== NATIVE CATEGORIES ===\n";
$cats = get_categories(array('hide_empty' => false));
foreach ($cats as $c) {
    echo "- ID: {$c->term_id} | Name: {$c->name} | Slug: {$c->slug} | Count: {$c->count}\n";
}

echo "\n=== PAGES ===\n";
$pages = get_posts(array('post_type' => 'page', 'numberposts' => -1, 'post_status' => 'any'));
foreach ($pages as $p) {
    echo "- ID: {$p->ID} | Title: {$p->post_title} | Slug: {$p->post_name}\n";
}

echo "\n=== READING SETTINGS ===\n";
echo "show_on_front: " . get_option('show_on_front') . "\n";
echo "page_on_front: " . get_option('page_on_front') . "\n";
echo "page_for_posts: " . get_option('page_for_posts') . "\n";
echo "permalink_structure: " . get_option('permalink_structure') . "\n";

echo "\n=== ACTIVE PLUGINS ===\n";
print_r(get_option('active_plugins'));
