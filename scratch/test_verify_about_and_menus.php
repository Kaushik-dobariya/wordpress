<?php
require_once __DIR__ . '/../wp-load.php';

echo "=== VERIFYING /about/ ===" . PHP_EOL;
$about_url = home_url('/about/');
$html = file_get_contents($about_url);
echo "HTML Length: " . strlen($html) . PHP_EOL;
preg_match_all('/<section[^>]*class=["\']([^"\']*)["\']/', $html, $matches);
echo "Sections found (" . count($matches[1]) . "):" . PHP_EOL;
foreach ($matches[1] as $idx => $cls) {
    echo "  [" . ($idx + 1) . "] " . $cls . PHP_EOL;
}

echo PHP_EOL . "=== VERIFYING PRIMARY MENU IN / ===" . PHP_EOL;
$home_html = file_get_contents(home_url('/'));
preg_match('/<ul[^>]*id=["\']primary-menu["\'][^>]*>(.*?)<\/ul>/s', $home_html, $menu_match);
if (!empty($menu_match[1])) {
    preg_match_all('/<li[^>]*><a[^>]*href=["\']([^"\']*)["\'][^>]*>(.*?)<\/a>/s', $menu_match[1], $items);
    echo "Primary Menu items (" . count($items[2]) . "):" . PHP_EOL;
    for ($i = 0; $i < count($items[2]); $i++) {
        echo "  - " . trim(strip_tags($items[2][$i])) . " (" . $items[1][$i] . ")" . PHP_EOL;
    }
} else {
    echo "Primary menu not found in home HTML!" . PHP_EOL;
}

echo PHP_EOL . "=== VERIFYING FOOTER MENUS IN / ===" . PHP_EOL;
preg_match_all('/<ul[^>]*class=["\']sc-footer-menu["\'][^>]*>(.*?)<\/ul>/s', $home_html, $footer_matches);
echo "Footer menu lists found: " . count($footer_matches[1]) . PHP_EOL;
foreach ($footer_matches[1] as $f_idx => $f_list) {
    echo " Footer Menu " . ($f_idx + 1) . ":" . PHP_EOL;
    preg_match_all('/<li[^>]*><a[^>]*href=["\']([^"\']*)["\'][^>]*>(.*?)<\/a>/s', $f_list, $f_items);
    for ($j = 0; $j < count($f_items[2]); $j++) {
        echo "    * " . trim(strip_tags($f_items[2][$j])) . " (" . $f_items[1][$j] . ")" . PHP_EOL;
    }
}
