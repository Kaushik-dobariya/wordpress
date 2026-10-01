<?php
require_once __DIR__ . '/../wp-load.php';

$context = stream_context_create([
    'http' => [
        'timeout' => 5,
        'ignore_errors' => true,
    ]
]);

$content = file_get_contents(get_permalink(24), false, $context);
echo "Length: " . strlen($content) . "\n";
echo "Contains 'enquiry': " . (stripos($content, 'enquiry') !== false ? 'YES' : 'NO') . "\n";
echo "Contains 'mailto:': " . (stripos($content, 'mailto:') !== false ? 'YES' : 'NO') . "\n";
echo "Contains 'sc-enquiry-modal': " . (stripos($content, 'sc-enquiry-modal') !== false ? 'YES' : 'NO') . "\n";
echo "Contains 'sc-enquiry': " . (stripos($content, 'sc-enquiry') !== false ? 'YES' : 'NO') . "\n";

// Search for strings matching enquiry
preg_match_all('/class="[^"]*enquiry[^"]*"/i', $content, $matches);
print_r($matches[0]);
