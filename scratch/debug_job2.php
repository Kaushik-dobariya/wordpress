<?php
require_once __DIR__ . '/../wp-load.php';

$job2 = get_page_by_path('logistics-export-coordinator', OBJECT, 'spicecraft_job');
echo "Job 2 ID: " . ($job2 ? $job2->ID : 'NONE') . "\n";

query_posts(['p' => $job2->ID, 'post_type' => 'spicecraft_job']);
ob_start();
load_template(get_stylesheet_directory() . '/single-spicecraft_job.php');
$html = ob_get_clean();
wp_reset_query();

echo "HTML Length: " . strlen($html) . "\n";
echo "Contains 'Position Closed': " . (strpos($html, 'Position Closed') !== false ? 'YES' : 'NO') . "\n";
echo "Contains 'Applications Closed': " . (strpos($html, 'Applications Closed') !== false ? 'YES' : 'NO') . "\n";
echo "First 400 chars:\n" . substr($html, 0, 400) . "\n";
