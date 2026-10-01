<?php
require_once __DIR__ . '/../wp-load.php';

$content = file_get_contents(get_permalink(24));
if (preg_match('/<div class="sc-enquiry-box"[^>]*>(.*?)<\/div>\s*<\/div>/s', $content, $m)) {
    echo $m[0];
} else {
    echo "Pattern not matched. Extracting around sc-enquiry-box:\n";
    $pos = strpos($content, 'sc-enquiry-box');
    echo substr($content, $pos - 50, 800);
}
