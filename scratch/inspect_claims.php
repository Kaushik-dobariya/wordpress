<?php
require_once __DIR__ . '/../wp-load.php';

$s = get_option('spicecraft_homepage_settings', array());
echo "--- HERO ---\n";
print_r($s['hero'] ?? []);

echo "\n--- BRAND STORY ---\n";
print_r($s['brand_story'] ?? []);

echo "\n--- WHY CHOOSE US ---\n";
print_r($s['why_choose_us'] ?? []);

echo "\n--- QUALITY SOURCING ---\n";
print_r($s['quality_sourcing'] ?? []);

echo "\n--- MANUFACTURING ---\n";
print_r($s['manufacturing'] ?? []);

echo "\n--- CERTIFICATIONS ---\n";
print_r($s['certifications'] ?? []);
