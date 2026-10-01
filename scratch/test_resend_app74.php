<?php
require_once __DIR__ . '/../wp-load.php';

$app_id = 74;
$app = get_post( $app_id );
if ( ! $app ) {
	die( "App 74 not found\n" );
}

echo "Testing Application #74:\n";
echo "Title: " . $app->post_title . "\n";
echo "Resume: " . get_post_meta( $app_id, '_sc_app_resume_file', true ) . "\n";

$app_instance = SpiceCraft_Careers_Application::get_instance();

// Test resend attempt
$recipient_email = spicecraft_get_careers_profile_email();
$job_title       = get_post_meta( $app_id, '_sc_app_job_title', true );
$full_name       = get_post_meta( $app_id, '_sc_app_full_name', true ) ?: $app->post_title;
$email           = get_post_meta( $app_id, '_sc_app_email', true );
$phone           = get_post_meta( $app_id, '_sc_app_phone', true );
$location        = get_post_meta( $app_id, '_sc_app_location', true );
$company         = get_post_meta( $app_id, '_sc_app_company', true );
$designation     = get_post_meta( $app_id, '_sc_app_designation', true );
$experience      = get_post_meta( $app_id, '_sc_app_experience', true );
$linkedin        = get_post_meta( $app_id, '_sc_app_linkedin', true );
$portfolio       = get_post_meta( $app_id, '_sc_app_portfolio', true );
$cover_msg       = get_post_meta( $app_id, '_sc_app_cover_message', true ) ?: $app->post_content;
$resume_file     = get_post_meta( $app_id, '_sc_app_resume_file', true );
$orig_filename   = get_post_meta( $app_id, '_sc_app_resume_name', true );

echo "Attempting send_application_email()...\n";
$sent = $app_instance->send_application_email(
	$recipient_email,
	$app_id,
	$job_title,
	$full_name,
	$email,
	$phone,
	$location,
	$company,
	$designation,
	$experience,
	$linkedin,
	$portfolio,
	$cover_msg,
	$resume_file,
	$orig_filename
);

echo "Result: " . ( $sent ? 'SENT' : 'NOT SENT (as expected on localhost without SMTP credentials)' ) . "\n";
echo "Status recorded on post:\n";
echo "Sent flag: " . get_post_meta( $app_id, '_sc_app_email_sent', true ) . "\n";
echo "Sent to: " . get_post_meta( $app_id, '_sc_app_email_to', true ) . "\n";
echo "Logged Error: " . get_post_meta( $app_id, '_sc_app_email_error', true ) . "\n";
