<?php
require_once __DIR__ . '/../wp-load.php';

echo "============================================" . PHP_EOL;
echo "ABOUT PAGE FRONTEND TEST SUITE" . PHP_EOL;
echo "============================================" . PHP_EOL;

// 1. Fetch /about/
$context = stream_context_create(array(
    'http' => array(
        'timeout' => 10,
        'ignore_errors' => true,
    )
));

$response = file_get_contents('http://localhost/about/', false, $context);
$headers = $http_response_header ?? array();
$status_line = $headers[0] ?? 'NO_HEADER';

echo "Status: " . $status_line . PHP_EOL;
echo "Length: " . strlen($response) . " bytes" . PHP_EOL;

if (strpos($status_line, '200') !== false) {
    echo "✅ PASS: HTTP 200 OK" . PHP_EOL;
} else {
    echo "❌ FAIL: Expected 200 OK, got " . $status_line . PHP_EOL;
}

// 2. Check H1 tag count
preg_match_all('/<h1[^>]*>(.*?)<\/h1>/is', $response, $h1_matches);
$h1_count = count($h1_matches[0]);
echo "H1 Count: " . $h1_count . PHP_EOL;
if ($h1_count <= 1) {
    echo "✅ PASS: Single or zero H1 (Current count: $h1_count)" . PHP_EOL;
} else {
    echo "❌ FAIL: Multiple H1 tags found ($h1_count)" . PHP_EOL;
}

// 3. Check CSS enqueue
if (strpos($response, 'about.css') !== false) {
    echo "✅ PASS: about.css is enqueued" . PHP_EOL;
} else {
    echo "❌ FAIL: about.css was not found in page output" . PHP_EOL;
}

// 4. Check main structure
preg_match('/<main[^>]*id="primary"[^>]*>(.*?)<\/main>/is', $response, $main_content);
echo "Main Content snippet: " . substr(strip_tags($main_content[1] ?? 'EMPTY'), 0, 200) . PHP_EOL;

$page = get_page_by_path('about');
echo "Page ID: " . ($page ? $page->ID : 'NULL') . PHP_EOL;
echo "Page Template Meta: " . ($page ? get_post_meta($page->ID, '_wp_page_template', true) : 'NULL') . PHP_EOL;

if (strpos($response, 'sc-about-empty') !== false || strpos($response, 'sc-about-sections') !== false) {
    echo "✅ PASS: About page container rendered" . PHP_EOL;
} else {
    echo "❌ FAIL: Neither sc-about-empty nor sc-about-sections found" . PHP_EOL;
}

// 5. Check no PHP errors / notices
$error_patterns = array(
    'Fatal error',
    'Parse error',
    'Warning:',
    'Notice:',
    'Deprecated:',
);

$has_php_error = false;
foreach ($error_patterns as $err) {
    if (strpos($response, $err) !== false) {
        echo "❌ FAIL: Found PHP message: " . $err . PHP_EOL;
        $has_php_error = true;
    }
}
if (!$has_php_error) {
    echo "✅ PASS: Zero PHP errors/warnings in output" . PHP_EOL;
}

