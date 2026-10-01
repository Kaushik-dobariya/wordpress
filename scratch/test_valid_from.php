<?php
require_once __DIR__ . '/../wp-load.php';

$error_info = null;
add_action('wp_mail_failed', function($wp_error) use (&$error_info) {
    $error_info = $wp_error;
});

// Set valid from email and name
add_filter('wp_mail_from', function($from) {
    return 'no-reply@cubeontechs.com';
});
add_filter('wp_mail_from_name', function($name) {
    return 'SpiceCraft Recruitment';
});

$result = wp_mail(
    'career@cubeontechs.com',
    'Test from SpiceCraft with valid From',
    'Testing wp_mail with valid From address.'
);

echo "wp_mail() return: " . ($result ? 'TRUE' : 'FALSE') . "\n";
if ($error_info) {
    echo "wp_mail_failed error message: " . $error_info->get_error_message() . "\n";
}
