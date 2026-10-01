<?php
require_once __DIR__ . '/../wp-load.php';

echo "Testing wp_mail()...\n";

$error_info = null;
add_action('wp_mail_failed', function($wp_error) use (&$error_info) {
    $error_info = $wp_error;
});

$result = wp_mail(
    'career@cubeontechs.com',
    'Test from SpiceCraft',
    'This is a test email to verify mail sending from local server.',
    ['Content-Type: text/html; charset=UTF-8']
);

echo "wp_mail() return: " . ($result ? 'TRUE' : 'FALSE') . "\n";
if ($error_info) {
    echo "wp_mail_failed error message: " . $error_info->get_error_message() . "\n";
    echo "wp_mail_failed error data: \n";
    print_r($error_info->get_error_data());
} else {
    echo "No wp_mail_failed triggered.\n";
}

// Check php.ini mail settings
echo "\nPHP Mail Settings:\n";
echo "SMTP: " . ini_get('SMTP') . "\n";
echo "smtp_port: " . ini_get('smtp_port') . "\n";
echo "sendmail_from: " . ini_get('sendmail_from') . "\n";
echo "sendmail_path: " . ini_get('sendmail_path') . "\n";

// Check installed plugins
echo "\nActive Plugins:\n";
$plugins = get_option('active_plugins');
print_r($plugins);
