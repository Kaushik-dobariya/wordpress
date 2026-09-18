<?php
require_once __DIR__ . '/../wp-load.php';

echo "==================================================" . PHP_EOL;
echo "COMPREHENSIVE 15-SECTION AUDIT ON ABOUT FRONTEND" . PHP_EOL;
echo "==================================================" . PHP_EOL;

$context = stream_context_create(array(
    'http' => array(
        'timeout' => 10,
        'ignore_errors' => true,
    )
));

$html = file_get_contents('http://localhost/about/', false, $context);

$sections = array(
    'about-hero'                => '1. About Hero',
    'company-intro'             => '2. Company Introduction',
    'our-story'                 => '3. Our Story',
    'vision-mission'            => '4. Vision & Mission',
    'core-values'               => '5. Core Values',
    'quality-philosophy'        => '6. Quality Philosophy',
    'sourcing-philosophy'       => '7. Sourcing Philosophy',
    'manufacturing-philosophy'  => '8. Manufacturing Philosophy',
    'company-statistics'        => '9. Company Statistics',
    'journey-milestones'        => '10. Journey / Milestones',
    'leadership-team'           => '11. Leadership / People',
    'certifications-trust'      => '12. Certifications / Trust',
    'product-connection'        => '13. Product Connection',
    'b2b-export'                => '14. B2B / Export CTA',
    'final-contact'             => '15. Final Contact CTA',
);

$all_found = true;
foreach ($sections as $sec_id => $sec_label) {
    if (strpos($html, 'id="' . $sec_id . '"') !== false || strpos($html, 'class="' . $sec_id) !== false || strpos($html, 'sc-' . $sec_id) !== false) {
        echo "✅ PASS: $sec_label rendered" . PHP_EOL;
    } else {
        echo "❌ FAIL: $sec_label not found in HTML" . PHP_EOL;
        $all_found = false;
    }
}

// Check team members in HTML
if (strpos($html, 'Rajesh Varma') !== false && strpos($html, 'Dr. Ananya Sharma') !== false) {
    echo "✅ PASS: Team members (Rajesh Varma, Dr. Ananya Sharma) rendered with CPT data" . PHP_EOL;
} else {
    echo "❌ FAIL: Team members missing" . PHP_EOL;
}

// Check timeline milestones
if (strpos($html, 'Direct Sourcing Network') !== false && strpos($html, 'Cleanroom Facility') !== false) {
    echo "✅ PASS: Timeline milestones rendered" . PHP_EOL;
} else {
    echo "❌ FAIL: Timeline milestones missing" . PHP_EOL;
}

// Check single H1
preg_match_all('/<h1[^>]*>(.*?)<\/h1>/is', $html, $h1_matches);
echo "H1 count: " . count($h1_matches[0]) . PHP_EOL;
if (count($h1_matches[0]) === 1) {
    echo "✅ PASS: Exactly one <h1> on page: " . trim(strip_tags($h1_matches[0][0])) . PHP_EOL;
} else {
    echo "❌ FAIL: Expected exactly one <h1>, found " . count($h1_matches[0]) . PHP_EOL;
}

// Check H2 headings count (sections should use H2)
preg_match_all('/<h2[^>]*>(.*?)<\/h2>/is', $html, $h2_matches);
echo "H2 section count: " . count($h2_matches[0]) . PHP_EOL;
if (count($h2_matches[0]) >= 10) {
    echo "✅ PASS: Robust section H2 hierarchy (" . count($h2_matches[0]) . " H2 headings found)" . PHP_EOL;
}

// Check images have alt text
preg_match_all('/<img[^>]+>/i', $html, $img_matches);
$img_without_alt = 0;
foreach ($img_matches[0] as $img_tag) {
    if (strpos($img_tag, 'alt=') === false) {
        $img_without_alt++;
    }
}
if ($img_without_alt === 0) {
    echo "✅ PASS: All <img> tags have alt attribute" . PHP_EOL;
} else {
    echo "⚠️ WARN: $img_without_alt images without alt attribute" . PHP_EOL;
}

