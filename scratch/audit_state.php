<?php
require_once __DIR__ . '/../wp-load.php';

echo "=== PAGES ===" . PHP_EOL;
$pages = get_posts([
    'post_type' => 'page',
    'numberposts' => -1,
    'post_status' => 'any',
]);
foreach ($pages as $p) {
    echo "ID: {$p->ID} | Slug: {$p->post_name} | Title: {$p->post_title} | Status: {$p->post_status} | Template: " . get_page_template_slug($p->ID) . PHP_EOL;
}

echo PHP_EOL . "=== THEME TEMPLATES ===" . PHP_EOL;
$theme = wp_get_theme();
print_r($theme->get_page_templates());

echo PHP_EOL . "=== CERTIFICATIONS ===" . PHP_EOL;
$certs = get_terms([
    'taxonomy' => 'spicecraft_certification',
    'hide_empty' => false,
]);
if (is_wp_error($certs)) {
    echo "Error fetching certs: " . $certs->get_error_message() . PHP_EOL;
} else {
    foreach ($certs as $c) {
        $logo_id = get_term_meta($c->term_id, '_sc_cert_logo_id', true);
        echo "Term: {$c->name} (ID: {$c->term_id}, Slug: {$c->slug}) | Logo ID: {$logo_id}" . PHP_EOL;
    }
}

echo PHP_EOL . "=== GLOBAL SETTINGS KEYS ===" . PHP_EOL;
$global = get_option('spicecraft_global_settings', []);
print_r(array_keys($global));

echo PHP_EOL . "=== HOMEPAGE SETTINGS SECTIONS ===" . PHP_EOL;
$homepage = get_option('spicecraft_homepage_settings', []);
if (!empty($homepage['sections'])) {
    foreach ($homepage['sections'] as $sec_id => $sec_data) {
        $enabled = !empty($sec_data['enabled']) ? 'ENABLED' : 'disabled';
        $order = isset($sec_data['order']) ? $sec_data['order'] : 'none';
        echo "Section: {$sec_id} | {$enabled} | Order: {$order}" . PHP_EOL;
    }
} else {
    echo "No sections array found. Keys: " . implode(', ', array_keys($homepage)) . PHP_EOL;
}

echo PHP_EOL . "=== ACTIVE PLUGINS ===" . PHP_EOL;
print_r(get_option('active_plugins'));
