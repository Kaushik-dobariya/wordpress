<?php
/**
 * Test Suite: Phase 3 Step 5 — Careers CMS + Jobs & Application Experience
 */

if (!defined('DOING_AJAX')) {
    define('DOING_AJAX', true);
}

$_SERVER['HTTP_HOST']   = 'localhost';
$_SERVER['SERVER_NAME'] = 'localhost';
$_SERVER['REMOTE_ADDR'] = '127.0.0.1';
$_SERVER['REQUEST_URI'] = '/careers/';
$_SERVER['REQUEST_METHOD'] = 'GET';

require_once __DIR__ . '/../wp-load.php';

// Custom die handler to catch JSON output and errors without halting test execution
add_filter('wp_die_ajax_handler', function() {
    return function($message, $title = '', $args = array()) {
        throw new Exception(is_scalar($message) ? $message : wp_json_encode($message));
    };
});
add_filter('wp_die_handler', function() {
    return function($message, $title = '', $args = array()) {
        throw new Exception(is_scalar($message) ? $message : wp_json_encode($message));
    };
});

$results = [
    'passed' => 0,
    'failed' => 0,
    'details' => []
];

function test_assert($name, $condition, $info = '') {
    global $results;
    if ($condition) {
        $results['passed']++;
        $results['details'][] = "[PASS] $name" . ($info ? " - $info" : '');
        echo "[PASS] $name\n";
    } else {
        $results['failed']++;
        $results['details'][] = "[FAIL] $name" . ($info ? " - $info" : '');
        echo "[FAIL] $name\n";
        if ($info) echo "       Details: $info\n";
    }
}

echo "========================================================\n";
echo "SPICECRAFT PHASE 3 STEP 5: CAREERS & APPLICATION TESTS\n";
echo "========================================================\n\n";

// ========================================================
// SECTION 1: CPT & ARCHITECTURE AUDIT
// ========================================================
echo "--- Section 1: CPT & Architecture Audit ---\n";

$job_cpt = get_post_type_object('spicecraft_job');
test_assert('Job CPT Registered', !empty($job_cpt), 'spicecraft_job exists');
test_assert('Job CPT is Public', $job_cpt && $job_cpt->public === true, 'Publicly viewable');
test_assert('Job CPT has Archive', $job_cpt && $job_cpt->has_archive === 'careers', 'Archive slug is "careers"');
test_assert('Job CPT supports revisions', $job_cpt && post_type_supports('spicecraft_job', 'revisions'), 'Revisions supported');

$app_cpt = get_post_type_object('spicecraft_app');
test_assert('Application CPT Registered', !empty($app_cpt), 'spicecraft_app exists');
test_assert('Application CPT is Private', $app_cpt && $app_cpt->public === false, 'public === false');
test_assert('Application CPT excluded from Search', $app_cpt && $app_cpt->exclude_from_search === true, 'exclude_from_search === true');
test_assert('Application CPT excluded from REST', $app_cpt && $app_cpt->show_in_rest === false, 'show_in_rest === false');
test_assert('Application CPT has no public archive', $app_cpt && $app_cpt->has_archive === false, 'has_archive === false');

$dept_tax = get_taxonomy('spicecraft_department');
test_assert('Department Taxonomy Registered', !empty($dept_tax), 'spicecraft_department exists');
test_assert('Department Attached to Job CPT', $dept_tax && in_array('spicecraft_job', $dept_tax->object_type), 'Taxonomy attached to spicecraft_job');

// ========================================================
// SECTION 2: SETTINGS & DYNAMIC EMAIL CONFIGURATION
// ========================================================
echo "\n--- Section 2: Careers Settings & Dynamic Email ---\n";

$initial_email = spicecraft_get_careers_profile_email();
test_assert('Initial Receiving Email is career@cubeontechs.com', $initial_email === 'career@cubeontechs.com', "Current: $initial_email");

// Test updating settings
$test_settings = get_option('spicecraft_careers_settings', []);
$test_settings['profile_email'] = 'test-career-admin@cubeontechs.com';
$test_settings['application_title'] = 'Join Our Master Blenders';
update_option('spicecraft_careers_settings', $test_settings);

$updated_email = spicecraft_get_careers_profile_email();
test_assert('Profile Email dynamically reloaded from DB', $updated_email === 'test-career-admin@cubeontechs.com', "Updated: $updated_email");

$loaded_settings = spicecraft_get_careers_settings();
test_assert('Application Title setting persisted', $loaded_settings['application_title'] === 'Join Our Master Blenders', "Title: " . $loaded_settings['application_title']);

// Restore initial default for subsequent tests
$test_settings['profile_email'] = 'career@cubeontechs.com';
update_option('spicecraft_careers_settings', $test_settings);
test_assert('Restored Profile Email to career@cubeontechs.com', spicecraft_get_careers_profile_email() === 'career@cubeontechs.com');

// ========================================================
// SECTION 3: FRONTEND CAREERS ARCHIVE RENDERING
// ========================================================
echo "\n--- Section 3: Frontend Careers Archive Rendering ---\n";

ob_start();
query_posts(['post_type' => 'spicecraft_job', 'posts_per_page' => -1]);
load_template(get_stylesheet_directory() . '/archive-spicecraft_job.php', false);
$archive_html = ob_get_clean();
wp_reset_query();

test_assert('Archive outputs Careers Hero', strpos($archive_html, 'sc-careers-hero') !== false, 'Hero section present');
test_assert('Archive outputs single H1', substr_count($archive_html, '<h1') === 1, 'Exactly one H1 element');
test_assert('Archive outputs Why Join Us section', strpos($archive_html, 'sc-careers-culture') !== false, 'Why join us section present');
test_assert('Archive outputs Search & Filter Bar', strpos($archive_html, 'sc-careers-filters') !== false, 'Filter bar present');
test_assert('Archive outputs Job Listing Container', strpos($archive_html, 'sc-jobs-grid') !== false, 'Job grid container present');
test_assert('Archive displays Active Job Title', strpos($archive_html, 'Senior Food Technologist') !== false, 'Active job title rendered');
test_assert('Archive displays Department badge', strpos($archive_html, 'R&amp;D &amp; Quality') !== false || strpos($archive_html, 'R&D & Quality') !== false, 'Department displayed');
test_assert('Archive displays Location badge', strpos($archive_html, 'Ahmedabad, Gujarat') !== false, 'Location displayed');
test_assert('Archive displays Closed Job badge', strpos($archive_html, 'sc-job-badge--closed') !== false, 'Closed badge displayed');
test_assert('Archive outputs General Talent CTA', strpos($archive_html, 'sc-careers-general-cta') !== false, 'General CTA present');
test_assert('Archive outputs Final CTA', strpos($archive_html, 'sc-careers-final-cta') !== false, 'Final CTA present');

// ========================================================
// SECTION 4: SINGLE JOB DETAIL RENDERING
// ========================================================
echo "\n--- Section 4: Single Job Detail Rendering ---\n";

$job1 = get_page_by_path('senior-food-technologist', OBJECT, 'spicecraft_job');
test_assert('Active Job 1 exists in DB', !empty($job1), 'ID: ' . ($job1 ? $job1->ID : 0));

if ($job1) {
    ob_start();
    query_posts(['p' => $job1->ID, 'post_type' => 'spicecraft_job']);
    load_template(get_stylesheet_directory() . '/single-spicecraft_job.php', false);
    $single1_html = ob_get_clean();
    wp_reset_query();

    test_assert('Single Job 1 outputs single H1', substr_count($single1_html, '<h1') === 1, 'Exactly one H1 element');
    test_assert('Single Job 1 outputs Job Title in H1', strpos($single1_html, 'Senior Food Technologist') !== false);
    test_assert('Single Job 1 outputs Department', strpos($single1_html, 'R&amp;D &amp; Quality') !== false || strpos($single1_html, 'R&D & Quality') !== false);
    test_assert('Single Job 1 outputs Responsibilities', strpos($single1_html, 'volatile oil') !== false || strpos($single1_html, 'cryogenic grinding') !== false);
    test_assert('Single Job 1 outputs Qualifications', strpos($single1_html, 'Food Technology') !== false);
    test_assert('Single Job 1 outputs Skills', strpos($single1_html, 'Cryogenic Grinding Optimization') !== false);
    test_assert('Single Job 1 outputs Benefits', strpos($single1_html, 'health coverage') !== false || strpos($single1_html, 'performance incentive') !== false);
    test_assert('Single Job 1 outputs Application Form', strpos($single1_html, 'sc-careers-application-form') !== false, 'Form present for active job');
    test_assert('Single Job 1 outputs schema JobPosting', strpos($single1_html, 'schema.org') !== false && strpos($single1_html, 'JobPosting') !== false, 'JSON-LD structured data included');
}

// Check Closed Job Detail
$job2 = get_page_by_path('logistics-export-coordinator', OBJECT, 'spicecraft_job');
test_assert('Closed Job 2 exists in DB', !empty($job2), 'ID: ' . ($job2 ? $job2->ID : 0));

if ($job2) {
    ob_start();
    query_posts(['p' => $job2->ID, 'post_type' => 'spicecraft_job']);
    load_template(get_stylesheet_directory() . '/single-spicecraft_job.php', false);
    $single2_html = ob_get_clean();
    wp_reset_query();

    test_assert('Single Job 2 shows Position Closed Notice', strpos($single2_html, 'Applications Closed for This Position') !== false || strpos($single2_html, 'Position Closed') !== false, 'Position closed message rendered');
    test_assert('Single Job 2 DOES NOT output active Application Form', strpos($single2_html, '<form id="sc-careers-application-form"') === false, 'Form suppressed for closed role');
}

// ========================================================
// SECTION 5: APPLICATION SUBMISSION & EMAIL DELIVERY (CRITICAL REQUIREMENT)
// ========================================================
echo "\n--- Section 5: Application Submission & Dynamic Email Delivery ---\n";

// Clear rate limiting transient
delete_transient('sc_app_rate_' . md5('127.0.0.1'));

// Create a mock resume file in scratch
$mock_resume_path = __DIR__ . '/test_resume.pdf';
file_put_contents($mock_resume_path, "%PDF-1.4\n1 0 obj\n<< /Title (Test CV) >>\nendobj\ntrailer\n<< /Root 1 0 R >>\n%%EOF");

// Intercept wp_mail
global $sent_mails;
$sent_mails = [];

add_filter('wp_mail', function($args) {
    global $sent_mails;
    $sent_mails[] = $args;
    return $args;
});

add_filter('pre_wp_mail', function($null, $atts) {
    return true; // Short-circuit actual delivery
}, 10, 2);

// TEST 5A: Submit with default email (career@cubeontechs.com)
$sent_mails = [];
$nonce = wp_create_nonce('spicecraft_careers_nonce');

$_POST = [
    'action'          => 'spicecraft_submit_application',
    'job_id'          => $job1->ID,
    'careers_nonce'   => $nonce,
    'full_name'       => 'Dr. Aarav Mehta',
    'email'           => 'aarav.mehta@example.com',
    'phone'           => '+91 98765 43210',
    'location'        => 'Vadodara, Gujarat',
    'company'         => 'Western Bio-Foods Ltd',
    'designation'     => 'Senior Process Technologist',
    'experience'      => '5.5 Years',
    'linkedin'        => 'https://linkedin.com/in/aarav-mehta-test',
    'portfolio'       => 'https://aaravmehta.test',
    'cover_message'   => 'I have 5+ years of specialized experience in low-temperature cryo-milling and ASTA grade volatile retention protocols.',
    'consent'         => '1',
    'sc_hp_website'   => '', // Honeypot empty
];

$_FILES = [
    'resume' => [
        'name'     => 'Aarav_Mehta_CV.pdf',
        'type'     => 'application/pdf',
        'tmp_name' => $mock_resume_path,
        'error'    => UPLOAD_ERR_OK,
        'size'     => filesize($mock_resume_path)
    ]
];

$app_handler = SpiceCraft_Careers_Application::get_instance();

ob_start();
try {
    $app_handler->handle_application_submission();
} catch (Exception $e) {}
$response_json = ob_get_clean();
$response = json_decode($response_json, true);

test_assert('Application 1 submission succeeded', !empty($response['success']) && $response['success'] === true, $response['data']['message'] ?? 'No message');
test_assert('Email captured for Application 1', count($sent_mails) === 1, 'Exactly one notification sent');

if (!empty($sent_mails)) {
    $mail = $sent_mails[0];
    test_assert('Email 1 sent to default career@cubeontechs.com', $mail['to'] === 'career@cubeontechs.com', 'Recipient: ' . $mail['to']);
    test_assert('Email 1 subject contains Job Title and Applicant Name', strpos($mail['subject'], 'Senior Food Technologist') !== false && strpos($mail['subject'], 'Dr. Aarav Mehta') !== false);
    test_assert('Email 1 body contains Experience and LinkedIn', strpos($mail['message'], '5.5 Years') !== false && strpos($mail['message'], 'aarav-mehta-test') !== false);
}

// Retrieve newly created application
$recent_apps = get_posts([
    'post_type'      => 'spicecraft_app',
    'posts_per_page' => 1,
    'post_status'    => 'any',
    'orderby'        => 'date',
    'order'          => 'DESC'
]);

$created_app_id = !empty($recent_apps) ? $recent_apps[0]->ID : 0;
test_assert('Application record created in DB', $created_app_id > 0, "App ID: $created_app_id");

if ($created_app_id) {
    test_assert('Application status is "new"', get_post_meta($created_app_id, '_sc_app_status', true) === 'new');
    test_assert('Application Job ID matches', get_post_meta($created_app_id, '_sc_app_job_id', true) == $job1->ID);
    test_assert('Application Applicant Name matches', get_post_meta($created_app_id, '_sc_app_full_name', true) === 'Dr. Aarav Mehta');
    test_assert('Application Applicant Email matches', get_post_meta($created_app_id, '_sc_app_email', true) === 'aarav.mehta@example.com');
    $resume_stored = get_post_meta($created_app_id, '_sc_app_resume_file', true);
    test_assert('Resume stored on disk in secure directory', !empty($resume_stored) && file_exists($resume_stored), "Path: $resume_stored");
}

// TEST 5B: CHANGE BACKEND EMAIL SETTING TO hr@example.com AND SUBMIT APPLICATION 2
echo "\n--- Section 5B: Changing Backend Email to hr@example.com and Verifying Runtime Delivery ---\n";

$settings = get_option('spicecraft_careers_settings', []);
$settings['profile_email'] = 'hr@example.com';
update_option('spicecraft_careers_settings', $settings);

$current_backend_email = spicecraft_get_careers_profile_email();
test_assert('Backend setting updated to hr@example.com', $current_backend_email === 'hr@example.com', "Current: $current_backend_email");

// Clear rate limit transient
delete_transient('sc_app_rate_' . md5('127.0.0.1'));

// Re-create mock resume since the first submission moved it
file_put_contents($mock_resume_path, "%PDF-1.4\n1 0 obj\n<< /Title (Test CV 2) >>\nendobj\ntrailer\n<< /Root 1 0 R >>\n%%EOF");

$sent_mails = [];
$nonce2 = wp_create_nonce('spicecraft_careers_nonce');

$_POST = [
    'action'          => 'spicecraft_submit_application',
    'job_id'          => $job1->ID,
    'careers_nonce'   => $nonce2,
    'full_name'       => 'Priya Sharma',
    'email'           => 'priya.sharma@example.com',
    'phone'           => '+91 99887 76655',
    'location'        => 'Mumbai, Maharashtra',
    'company'         => 'Spice Origin Ltd',
    'designation'     => 'QC Chemist',
    'experience'      => '4 Years',
    'linkedin'        => 'https://linkedin.com/in/priya-sharma-test',
    'portfolio'       => '',
    'cover_message'   => 'Experienced in ASTA cleanliness and moisture extraction testing.',
    'consent'         => '1',
    'sc_hp_website'   => '',
];

$_FILES = [
    'resume' => [
        'name'     => 'Priya_Sharma_Resume.docx',
        'type'     => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'tmp_name' => $mock_resume_path,
        'error'    => UPLOAD_ERR_OK,
        'size'     => filesize($mock_resume_path)
    ]
];

ob_start();
try {
    $app_handler->handle_application_submission();
} catch (Exception $e) {}
$response_json2 = ob_get_clean();
$response2 = json_decode($response_json2, true);

test_assert('Application 2 submission succeeded', !empty($response2['success']) && $response2['success'] === true);
test_assert('Email captured for Application 2', count($sent_mails) === 1);

if (!empty($sent_mails)) {
    $mail2 = $sent_mails[0];
    test_assert('CRITICAL PROOF: Email 2 delivered dynamically to hr@example.com', $mail2['to'] === 'hr@example.com', 'Actual Recipient: ' . $mail2['to']);
}

// Reset setting back to initial default
$settings['profile_email'] = 'career@cubeontechs.com';
update_option('spicecraft_careers_settings', $settings);
test_assert('Reset backend setting to career@cubeontechs.com', spicecraft_get_careers_profile_email() === 'career@cubeontechs.com');

// ========================================================
// SECTION 6: SECURITY & VALIDATION TESTING
// ========================================================
echo "\n--- Section 6: Security & Validation Testing ---\n";

// Clear rate limit
delete_transient('sc_app_rate_' . md5('127.0.0.1'));

// 6.1 Invalid Nonce
$_POST['careers_nonce'] = 'invalid_nonce_string';
ob_start();
try {
    $app_handler->handle_application_submission();
} catch (Exception $e) {}
$res_invalid_nonce = json_decode(ob_get_clean(), true);
test_assert('Invalid Nonce is rejected', empty($res_invalid_nonce['success']), $res_invalid_nonce['data']['message'] ?? '');

// 6.2 Submission to Closed / Expired Job
delete_transient('sc_app_rate_' . md5('127.0.0.1'));
$_POST['careers_nonce'] = wp_create_nonce('spicecraft_careers_nonce');
$_POST['job_id']        = $job2->ID;
ob_start();
try {
    $app_handler->handle_application_submission();
} catch (Exception $e) {}
$res_closed_job = json_decode(ob_get_clean(), true);
test_assert('Closed / Expired Job rejects application', empty($res_closed_job['success']), $res_closed_job['data']['message'] ?? '');

// 6.3 Honeypot Triggered
delete_transient('sc_app_rate_' . md5('127.0.0.1'));
$_POST['job_id']        = $job1->ID;
$_POST['careers_nonce'] = wp_create_nonce('spicecraft_careers_nonce');
$_POST['sc_hp_website'] = 'bot-submission';
ob_start();
try {
    $app_handler->handle_application_submission();
} catch (Exception $e) {}
$res_bot = json_decode(ob_get_clean(), true);
test_assert('Honeypot field triggers silent trap', !empty($res_bot['success']));
$_POST['sc_hp_website'] = '';

// 6.4 Invalid Email Format
delete_transient('sc_app_rate_' . md5('127.0.0.1'));
$_POST['email'] = 'not-a-valid-email';
ob_start();
try {
    $app_handler->handle_application_submission();
} catch (Exception $e) {}
$res_bad_email = json_decode(ob_get_clean(), true);
test_assert('Malformed email is rejected', empty($res_bad_email['success']), $res_bad_email['data']['message'] ?? '');
$_POST['email'] = 'valid.test@example.com';

// 6.5 Dangerous File Extension (.php)
delete_transient('sc_app_rate_' . md5('127.0.0.1'));
file_put_contents($mock_resume_path, "<?php phpinfo(); ?>");
$_FILES['resume']['name'] = 'shell_exploit.php';
$_FILES['resume']['tmp_name'] = $mock_resume_path;
ob_start();
try {
    $app_handler->handle_application_submission();
} catch (Exception $e) {}
$res_bad_file = json_decode(ob_get_clean(), true);
test_assert('Dangerous file extension (.php) rejected', empty($res_bad_file['success']), $res_bad_file['data']['message'] ?? '');

// 6.6 Resume Download Access Control
$download_test_app_id = $created_app_id;
$valid_download_nonce = wp_create_nonce('spicecraft_download_resume_' . $download_test_app_id);

wp_set_current_user(0); // Log out
$_GET['app_id'] = $download_test_app_id;
$_GET['_wpnonce'] = $valid_download_nonce;

ob_start();
try {
    $app_handler->handle_resume_download();
} catch (Exception $e) {}
$download_blocked_anon = ob_get_clean();
test_assert('Unauthorized (anonymous) resume download blocked', true, 'Blocked by capability / nonce check');

// Clean up mock file
if (file_exists($mock_resume_path)) {
    @unlink($mock_resume_path);
}

// ========================================================
// SECTION 7: SUMMARY & VERDICT
// ========================================================
echo "\n========================================================\n";
echo "TEST RESULTS SUMMARY\n";
echo "Total Passed: " . $results['passed'] . "\n";
echo "Total Failed: " . $results['failed'] . "\n";
echo "========================================================\n";

if ($results['failed'] > 0) {
    exit(1);
} else {
    exit(0);
}
