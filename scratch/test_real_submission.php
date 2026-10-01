<?php
require_once __DIR__ . '/../wp-load.php';

// Monitor mail attempt
add_action('wp_mail_failed', function($wp_error) {
    file_put_contents(__DIR__ . '/mail_debug.log', "FAILED: " . $wp_error->get_error_message() . "\n" . print_r($wp_error->get_error_data(), true) . "\n", FILE_APPEND);
});

add_filter('wp_mail', function($args) {
    file_put_contents(__DIR__ . '/mail_debug.log', "ATTEMPTING TO SEND:\n" . print_r($args, true) . "\n", FILE_APPEND);
    return $args;
});

// Clear previous log
if (file_exists(__DIR__ . '/mail_debug.log')) {
    unlink(__DIR__ . '/mail_debug.log');
}

// Find active job
$job = get_page_by_path('senior-food-technologist', OBJECT, 'spicecraft_job');
$job_id = $job->ID;

// Mock resume
$mock_file = __DIR__ . '/temp_resume.pdf';
file_put_contents($mock_file, "%PDF-1.4\n1 0 obj\n<< /Title (Test Resume) >>\nendobj\ntrailer\n<< /Root 1 0 R >>\n%%EOF");

$_POST = [
    'action'          => 'spicecraft_submit_application',
    'job_id'          => $job_id,
    'careers_nonce'   => wp_create_nonce('spicecraft_careers_nonce'),
    'full_name'       => 'Test Applicant',
    'email'           => 'applicant@example.com',
    'phone'           => '+91 9876543210',
    'location'        => 'Ahmedabad',
    'company'         => 'Test Co',
    'designation'     => 'Food Technologist',
    'experience'      => '3 Years',
    'linkedin'        => 'https://linkedin.com',
    'portfolio'       => '',
    'cover_message'   => 'This is a test application to verify mail sending.',
    'consent'         => '1',
    'sc_hp_website'   => '',
];

$_FILES = [
    'resume' => [
        'name'     => 'Resume.pdf',
        'type'     => 'application/pdf',
        'tmp_name' => $mock_file,
        'error'    => UPLOAD_ERR_OK,
        'size'     => filesize($mock_file)
    ]
];

// Run application handler
$handler = SpiceCraft_Careers_Application::get_instance();
delete_transient('sc_app_rate_' . md5('127.0.0.1'));

ob_start();
try {
    $handler->handle_application_submission();
} catch (Exception $e) {}
ob_end_clean();

if (file_exists(__DIR__ . '/mail_debug.log')) {
    echo file_get_contents(__DIR__ . '/mail_debug.log');
} else {
    echo "No mail log created.\n";
}

@unlink($mock_file);
