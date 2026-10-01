<?php
require_once __DIR__ . '/../wp-load.php';

$opt = get_option('spicecraft_careers_settings', array());
$opt['smtp_enabled']    = 1;
$opt['smtp_host']       = 'smtp.cubeontechs.com';
$opt['smtp_port']       = 465;
$opt['smtp_encryption'] = 'ssl';
$opt['smtp_user']       = 'career@cubeontechs.com';
$opt['smtp_from_email'] = 'career@cubeontechs.com';
$opt['smtp_from_name']  = 'SpiceCraft Recruitment';

update_option('spicecraft_careers_settings', $opt);

echo "Updated spicecraft_careers_settings successfully in DB!\n";

// Now test sending an actual test email using wp_mail() through WordPress
echo "Testing wp_mail() with active settings...\n";

$last_error = '';
add_action('wp_mail_failed', function($wp_error) use (&$last_error) {
    if (is_wp_error($wp_error)) {
        $last_error = $wp_error->get_error_message();
    }
});

$to      = 'career@cubeontechs.com';
$subject = 'SpiceCraft Live Delivery Verification — Port 465 SSL';
$message = '<h3>SpiceCraft Careers SMTP Verification</h3><p>Your Outgoing SMTP relay (smtp.cubeontechs.com:465 SSL) is configured, verified, and active in WordPress!</p>';
$headers = array('Content-Type: text/html; charset=UTF-8');

$sent = wp_mail($to, $subject, $message, $headers);

if ($sent) {
    echo "SUCCESS: Live email delivered to $to via WordPress wp_mail()!\n";
} else {
    echo "FAILED: $last_error\n";
}
