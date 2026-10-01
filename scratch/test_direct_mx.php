<?php
require_once __DIR__ . '/../wp-load.php';

$error_info = null;
add_action('wp_mail_failed', function($wp_error) use (&$error_info) {
    $error_info = $wp_error;
});

add_filter('wp_mail_from', function($from) {
    return 'noreply@cubeontechs.com';
});
add_filter('wp_mail_from_name', function($name) {
    return 'SpiceCraft Recruitment';
});

// Configure PHPMailer to use mx.stackmail.com:25 directly
add_action('phpmailer_init', function($phpmailer) {
    $phpmailer->isSMTP();
    $phpmailer->Host = 'mx.stackmail.com';
    $phpmailer->Port = 25;
    $phpmailer->SMTPAuth = false;
    $phpmailer->SMTPAutoTLS = false;
    $phpmailer->SMTPSecure = '';
    $phpmailer->Timeout = 15;
    $phpmailer->SMTPDebug = 2;
    $phpmailer->Debugoutput = function($str, $level) {
        echo "SMTP DEBUG: $str\n";
    };
});

$result = wp_mail(
    'career@cubeontechs.com',
    'Test Direct Delivery from SpiceCraft',
    'This is a direct test email to verify delivery to career@cubeontechs.com.'
);

echo "Direct Delivery Result: " . ($result ? 'SUCCESS' : 'FAILED') . "\n";
if ($error_info) {
    echo "Error: " . $error_info->get_error_message() . "\n";
}
