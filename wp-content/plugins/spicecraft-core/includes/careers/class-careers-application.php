<?php
/**
 * SpiceCraft Core - Candidate Application Processing & Secure Resume Engine
 *
 * Handles:
 * 1. AJAX and POST submission of job applications
 * 2. Strict input validation, honeypot spam protection, and rate-limiting
 * 3. Secure resume file verification (extension, MIME, size, sanitization)
 * 4. Confidential storage in private 'spicecraft_application' CPT
 * 5. Dynamic notification email delivery to CURRENT backend setting (Default: career@cubeontechs.com)
 * 6. Nonce-protected, permission-gated administrative resume download stream
 *
 * @package SpiceCraft_Core
 * @since 1.3.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class SpiceCraft_Careers_Application {

	/**
	 * Max upload file size in bytes (10 MB).
	 */
	const MAX_FILE_SIZE = 10485760;

	/**
	 * Allowed file extensions.
	 */
	const ALLOWED_EXTENSIONS = array( 'pdf', 'doc', 'docx' );

	/**
	 * Allowed MIME types.
	 */
	const ALLOWED_MIME_TYPES = array(
		'application/pdf',
		'application/msword',
		'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
		'application/octet-stream', // Some browsers report docx as octet-stream
	);

	/**
	 * Singleton Instance.
	 *
	 * @var SpiceCraft_Careers_Application|null
	 */
	private static $instance = null;

	/**
	 * Get Singleton Instance.
	 *
	 * @return SpiceCraft_Careers_Application
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor.
	 */
	private function __construct() {
		// AJAX submission endpoints
		add_action( 'wp_ajax_spicecraft_submit_application', array( $this, 'handle_application_submission' ) );
		add_action( 'wp_ajax_nopriv_spicecraft_submit_application', array( $this, 'handle_application_submission' ) );

		// Standard POST fallback
		add_action( 'admin_post_spicecraft_submit_application', array( $this, 'handle_application_submission' ) );
		add_action( 'admin_post_nopriv_spicecraft_submit_application', array( $this, 'handle_application_submission' ) );

		// Protected Admin Resume Download
		add_action( 'admin_post_spicecraft_download_resume', array( $this, 'handle_resume_download' ) );

		// Admin Resend Application Notification
		add_action( 'admin_post_spicecraft_resend_application_email', array( $this, 'handle_resend_application_email' ) );

		// Admin Feedback Notices
		add_action( 'admin_notices', array( $this, 'display_admin_notices' ) );

		// Outbound SMTP Transport Configuration
		add_action( 'phpmailer_init', array( $this, 'configure_phpmailer' ) );
		add_filter( 'wp_mail_from', array( $this, 'filter_mail_from' ), 20 );
		add_filter( 'wp_mail_from_name', array( $this, 'filter_mail_from_name' ), 20 );
	}

	/**
	 * Handle Candidate Application Submission.
	 */
	public function handle_application_submission() {
		$is_ajax = wp_doing_ajax();

		// 1. Nonce Verification
		$nonce = isset( $_POST['careers_nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['careers_nonce'] ) ) : '';
		if ( ! wp_verify_nonce( $nonce, 'spicecraft_careers_nonce' ) && ! wp_verify_nonce( $nonce, 'spicecraft_frontend_nonce' ) ) {
			$this->send_error( __( 'Security token expired. Please refresh the page and try again.', 'spicecraft-core' ), 403, $is_ajax );
		}

		// 2. Honeypot Spam Protection
		if ( ! empty( $_POST['sc_hp_website'] ) ) {
			// Honeypot triggered; silently reject bot
			$this->send_success( __( 'Application Submitted Successfully.', 'spicecraft-core' ), '', $is_ajax );
		}

		// 3. Rate-Limiting / Submission Throttling (Prevent accidental double-clicks & flood)
		$user_ip   = $this->get_client_ip();
		$rate_key  = 'sc_app_rate_' . md5( $user_ip );
		if ( get_transient( $rate_key ) ) {
			$this->send_error( __( 'A submission was recently received from your connection. Please wait a minute before submitting again.', 'spicecraft-core' ), 429, $is_ajax );
		}

		// 4. Job Validation
		$job_id = isset( $_POST['job_id'] ) ? absint( $_POST['job_id'] ) : 0;
		if ( ! $job_id ) {
			$this->send_error( __( 'Invalid job reference. Please select a valid open position.', 'spicecraft-core' ), 400, $is_ajax );
		}

		$job_post = get_post( $job_id );
		if ( ! $job_post || 'spicecraft_job' !== $job_post->post_type || 'publish' !== $job_post->post_status ) {
			$this->send_error( __( 'The selected position is not currently available.', 'spicecraft-core' ), 400, $is_ajax );
		}

		// Check if position is closed or expired
		if ( spicecraft_is_job_closed( $job_id ) ) {
			$this->send_error( __( 'This position is currently closed and no longer accepting new applications.', 'spicecraft-core' ), 400, $is_ajax );
		}

		$job_title = $job_post->post_title;

		// 5. Sanitize & Validate Personal Details
		$full_name = isset( $_POST['full_name'] ) ? sanitize_text_field( wp_unslash( $_POST['full_name'] ) ) : '';
		if ( empty( $full_name ) || strlen( $full_name ) < 2 ) {
			$this->send_error( __( 'Please provide your full legal name.', 'spicecraft-core' ), 400, $is_ajax );
		}

		$email = isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '';
		if ( empty( $email ) || ! is_email( $email ) ) {
			$this->send_error( __( 'Please provide a valid email address.', 'spicecraft-core' ), 400, $is_ajax );
		}

		$phone = isset( $_POST['phone'] ) ? sanitize_text_field( wp_unslash( $_POST['phone'] ) ) : '';
		if ( empty( $phone ) || strlen( preg_replace( '/[^0-9]/', '', $phone ) ) < 8 ) {
			$this->send_error( __( 'Please provide a valid contact telephone number with country/area code.', 'spicecraft-core' ), 400, $is_ajax );
		}

		$location = isset( $_POST['location'] ) ? sanitize_text_field( wp_unslash( $_POST['location'] ) ) : '';

		// Professional Details
		$company     = isset( $_POST['company'] ) ? sanitize_text_field( wp_unslash( $_POST['company'] ) ) : '';
		$designation = isset( $_POST['designation'] ) ? sanitize_text_field( wp_unslash( $_POST['designation'] ) ) : '';
		$experience  = isset( $_POST['experience'] ) ? sanitize_text_field( wp_unslash( $_POST['experience'] ) ) : '';
		$linkedin    = isset( $_POST['linkedin'] ) ? esc_url_raw( wp_unslash( $_POST['linkedin'] ) ) : '';
		$portfolio   = isset( $_POST['portfolio'] ) ? esc_url_raw( wp_unslash( $_POST['portfolio'] ) ) : '';

		// Cover Message
		$cover_msg = isset( $_POST['cover_message'] ) ? sanitize_textarea_field( wp_unslash( $_POST['cover_message'] ) ) : '';
		if ( empty( $cover_msg ) || strlen( $cover_msg ) < 10 ) {
			$this->send_error( __( 'Please include a brief introductory note or cover message.', 'spicecraft-core' ), 400, $is_ajax );
		}

		// Consent Checkbox
		$consent = ! empty( $_POST['consent'] ) ? 1 : 0;
		if ( ! $consent ) {
			$this->send_error( __( 'You must acknowledge the recruitment communication and data privacy consent to proceed.', 'spicecraft-core' ), 400, $is_ajax );
		}

		// 6. Resume File Upload & Validation
		if ( empty( $_FILES['resume'] ) || ! is_array( $_FILES['resume'] ) || empty( $_FILES['resume']['name'] ) ) {
			$this->send_error( __( 'Please attach your resume or CV (PDF, DOC, or DOCX).', 'spicecraft-core' ), 400, $is_ajax );
		}

		$file = $_FILES['resume'];

		if ( UPLOAD_ERR_OK !== $file['error'] ) {
			$this->send_error( __( 'An error occurred during file upload. Please ensure your file is under 10MB and try again.', 'spicecraft-core' ), 400, $is_ajax );
		}

		if ( $file['size'] > self::MAX_FILE_SIZE ) {
			$this->send_error( __( 'Resume file size exceeds the 10MB maximum limit.', 'spicecraft-core' ), 400, $is_ajax );
		}

		// Validate extension
		$orig_filename = sanitize_file_name( $file['name'] );
		$ext           = strtolower( pathinfo( $orig_filename, PATHINFO_EXTENSION ) );

		if ( ! in_array( $ext, self::ALLOWED_EXTENSIONS, true ) ) {
			$this->send_error( __( 'Invalid file type. Only PDF, DOC, and DOCX documents are accepted.', 'spicecraft-core' ), 400, $is_ajax );
		}

		// Validate MIME type
		$finfo     = finfo_open( FILEINFO_MIME_TYPE );
		$mime_type = finfo_file( $finfo, $file['tmp_name'] );
		finfo_close( $finfo );

		if ( ! in_array( $mime_type, self::ALLOWED_MIME_TYPES, true ) ) {
			$this->send_error( __( 'The uploaded file format did not match authorized document specifications.', 'spicecraft-core' ), 400, $is_ajax );
		}

		// Reject dangerous executable extensions
		$dangerous = array( 'php', 'phtml', 'php3', 'php4', 'php5', 'phps', 'js', 'exe', 'sh', 'bat', 'cmd', 'svg', 'html', 'htm', 'cgi', 'pl' );
		if ( in_array( $ext, $dangerous, true ) ) {
			$this->send_error( __( 'Security check: unauthorized file type.', 'spicecraft-core' ), 400, $is_ajax );
		}

		// Secure Upload Directory: wp-content/uploads/spicecraft_resumes/
		$upload_dir  = wp_upload_dir();
		$resumes_dir = wp_normalize_path( $upload_dir['basedir'] . '/spicecraft_resumes' );

		if ( ! file_exists( $resumes_dir ) ) {
			wp_mkdir_p( $resumes_dir );
			// Protect directory from direct listing
			@file_put_contents( $resumes_dir . '/index.php', '<?php // Silence is golden' );
			@file_put_contents( $resumes_dir . '/.htaccess', "Options -Indexes\n<FilesMatch \"\.(php|phtml|php3|php4|php5|phps|js|sh|exe)$\">\nOrder Deny,Allow\nDeny from all\n</FilesMatch>" );
		}

		// Generate randomized unique filename
		$safe_basename = sanitize_title( pathinfo( $orig_filename, PATHINFO_FILENAME ) );
		$unique_name   = 'resume_' . gmdate( 'Ymd_His' ) . '_' . wp_generate_password( 8, false ) . '.' . $ext;
		$target_path   = wp_normalize_path( $resumes_dir . '/' . $unique_name );

		$moved = false;
		if ( is_uploaded_file( $file['tmp_name'] ) ) {
			$moved = move_uploaded_file( $file['tmp_name'], $target_path );
		} elseif ( file_exists( $file['tmp_name'] ) && ( defined( 'WP_CLI' ) || 'cli' === php_sapi_name() ) ) {
			$moved = copy( $file['tmp_name'], $target_path );
		}

		if ( ! $moved ) {
			$this->send_error( __( 'Could not store resume file securely. Please try again.', 'spicecraft-core' ), 500, $is_ajax );
		}

		$resume_rel_url = $upload_dir['baseurl'] . '/spicecraft_resumes/' . $unique_name;

		// 7. Create Private Application CPT
		$app_title = sprintf( '%s — %s', $full_name, $job_title );
		$app_id    = wp_insert_post( array(
			'post_title'   => $app_title,
			'post_content' => $cover_msg,
			'post_status'  => 'publish',
			'post_type'    => SpiceCraft_Careers_CPT::APPLICATION_CPT,
			'post_parent'  => $job_id,
		) );

		if ( ! $app_id || is_wp_error( $app_id ) ) {
			$this->send_error( __( 'Failed to record application. Please try again.', 'spicecraft-core' ), 500, $is_ajax );
		}

		// Save structured metadata
		update_post_meta( $app_id, '_sc_app_job_id', $job_id );
		update_post_meta( $app_id, '_sc_app_job_title', $job_title );
		update_post_meta( $app_id, '_sc_app_full_name', $full_name );
		update_post_meta( $app_id, '_sc_app_email', $email );
		update_post_meta( $app_id, '_sc_app_phone', $phone );
		update_post_meta( $app_id, '_sc_app_location', $location );
		update_post_meta( $app_id, '_sc_app_company', $company );
		update_post_meta( $app_id, '_sc_app_designation', $designation );
		update_post_meta( $app_id, '_sc_app_experience', $experience );
		update_post_meta( $app_id, '_sc_app_linkedin', $linkedin );
		update_post_meta( $app_id, '_sc_app_portfolio', $portfolio );
		update_post_meta( $app_id, '_sc_app_cover_message', $cover_msg );
		update_post_meta( $app_id, '_sc_app_resume_file', $target_path );
		update_post_meta( $app_id, '_sc_app_resume_url', $resume_rel_url );
		update_post_meta( $app_id, '_sc_app_resume_name', $orig_filename );
		update_post_meta( $app_id, '_sc_app_status', 'new' );
		update_post_meta( $app_id, '_sc_app_submission_ip', $user_ip );
		update_post_meta( $app_id, '_sc_app_date', current_time( 'mysql' ) );

		// Set rate-limiting transient (60s)
		set_transient( $rate_key, 1, 60 );

		// 8. CRITICAL REQUIREMENT: Dynamic Email Delivery to CURRENT Backend Setting
		// Retrieves the CURRENT recipient email directly from backend settings (never hard-coded).
		$recipient_email = spicecraft_get_careers_profile_email();

		$this->send_application_email(
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
			$target_path,
			$orig_filename
		);

		// Format dynamic confirmation message
		$raw_msg     = spicecraft_get_careers_setting( 'success_message', '' );
		$success_msg = str_replace( '{Job Title}', $job_title, $raw_msg );
		if ( empty( $success_msg ) ) {
			$success_msg = sprintf(
				/* translators: %s: Job Title */
				__( 'Application Submitted Successfully. Thank you for your interest in joining our team. We have received your application for: %s.', 'spicecraft-core' ),
				$job_title
			);
		}

		$this->send_success( $success_msg, $job_title, $is_ajax );
	}

	/**
	 * Send Notification Email to the Current Backend-Configured Address.
	 *
	 * @param string $recipient_email CURRENT profile receiving email from backend setting.
	 * @param int    $app_id          Application ID.
	 * @param string $job_title       Job title.
	 * @param string $full_name       Candidate name.
	 * @param string $email           Candidate email.
	 * @param string $phone           Candidate phone.
	 * @param string $location        Candidate location.
	 * @param string $company         Current company.
	 * @param string $designation     Current designation.
	 * @param string $experience      Experience.
	 * @param string $linkedin        LinkedIn URL.
	 * @param string $portfolio       Portfolio URL.
	 * @param string $cover_msg       Cover message.
	 * @param string $resume_file     Path to uploaded resume file.
	 * @param string $resume_name     Original resume filename.
	 */
	/**
	 * Send Notification Email to the Current Backend-Configured Address.
	 *
	 * @param string $recipient_email CURRENT profile receiving email from backend setting.
	 * @param int    $app_id          Application ID.
	 * @param string $job_title       Job title.
	 * @param string $full_name       Candidate name.
	 * @param string $email           Candidate email.
	 * @param string $phone           Candidate phone.
	 * @param string $location        Candidate location.
	 * @param string $company         Current company.
	 * @param string $designation     Current designation.
	 * @param string $experience      Experience.
	 * @param string $linkedin        LinkedIn URL.
	 * @param string $portfolio       Portfolio URL.
	 * @param string $cover_msg       Cover message.
	 * @param string $resume_file     Path to uploaded resume file.
	 * @param string $resume_name     Original resume filename.
	 * @return bool True if sent, false otherwise.
	 */
	public function send_application_email(
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
		$resume_name
	) {
		$site_name = get_bloginfo( 'name' );
		$subject   = sprintf( 'New Job Application — %s — %s', $job_title, $full_name );

		$admin_url = admin_url( 'post.php?post=' . $app_id . '&action=edit' );

		// Build clean HTML email body
		$body  = "<h2>New Job Application Received</h2>\n";
		$body .= "<p>A new candidate has submitted an application on the <strong>{$site_name}</strong> Careers portal.</p>\n";
		$body .= "<hr />\n";
		$body .= "<table cellpadding='6' cellspacing='0' style='font-family: sans-serif; font-size: 14px; width: 100%; max-width: 600px;'>\n";
		$body .= "<tr><td style='width: 160px; font-weight: bold;'>Position Applied For:</td><td><strong>{$job_title}</strong></td></tr>\n";
		$body .= "<tr><td style='font-weight: bold;'>Candidate Name:</td><td>{$full_name}</td></tr>\n";
		$body .= "<tr><td style='font-weight: bold;'>Email:</td><td><a href='mailto:{$email}'>{$email}</a></td></tr>\n";
		$body .= "<tr><td style='font-weight: bold;'>Phone:</td><td><a href='tel:{$phone}'>{$phone}</a></td></tr>\n";
		if ( ! empty( $location ) ) {
			$body .= "<tr><td style='font-weight: bold;'>Current Location:</td><td>{$location}</td></tr>\n";
		}
		if ( ! empty( $company ) ) {
			$body .= "<tr><td style='font-weight: bold;'>Current Company:</td><td>{$company}</td></tr>\n";
		}
		if ( ! empty( $designation ) ) {
			$body .= "<tr><td style='font-weight: bold;'>Current Designation:</td><td>{$designation}</td></tr>\n";
		}
		if ( ! empty( $experience ) ) {
			$body .= "<tr><td style='font-weight: bold;'>Experience:</td><td>{$experience}</td></tr>\n";
		}
		if ( ! empty( $linkedin ) ) {
			$body .= "<tr><td style='font-weight: bold;'>LinkedIn Profile:</td><td><a href='{$linkedin}'>{$linkedin}</a></td></tr>\n";
		}
		if ( ! empty( $portfolio ) ) {
			$body .= "<tr><td style='font-weight: bold;'>Portfolio:</td><td><a href='{$portfolio}'>{$portfolio}</a></td></tr>\n";
		}
		$body .= "<tr><td style='font-weight: bold;'>Submission Date:</td><td>" . current_time( 'F j, Y, g:i a' ) . "</td></tr>\n";
		$body .= "</table>\n";
		$body .= "<h3>Cover Message:</h3>\n";
		$body .= "<blockquote style='background: #f8fafc; border-left: 4px solid #9e2a2b; padding: 12px; margin: 10px 0; font-style: italic;'>" . nl2br( esc_html( $cover_msg ) ) . "</blockquote>\n";
		$body .= "<p style='margin-top: 20px;'><a href='{$admin_url}' style='background: #9e2a2b; color: #fff; padding: 8px 16px; text-decoration: none; border-radius: 4px; font-weight: bold;'>Review Candidate in WordPress Admin &rarr;</a></p>\n";
		$body .= "<hr />\n";
		$body .= "<p style='font-size: 11px; color: #64748b;'>This notification was routed dynamically to the configured Careers Profile Receiving Email: {$recipient_email}</p>\n";

		$settings   = function_exists( 'spicecraft_get_careers_settings' ) ? spicecraft_get_careers_settings() : array();
		$from_email = ! empty( $settings['smtp_from_email'] ) ? $settings['smtp_from_email'] : $recipient_email;
		$from_name  = ! empty( $settings['smtp_from_name'] ) ? $settings['smtp_from_name'] : $site_name . ' Recruitment';

		// On local development, ensure From email is valid FQDN (never @localhost)
		if ( empty( $from_email ) || false !== strpos( $from_email, 'localhost' ) || false === strpos( $from_email, '.' ) ) {
			$from_email = $recipient_email;
		}

		$headers = array(
			'Content-Type: text/html; charset=UTF-8',
			'From: ' . $from_name . ' <' . $from_email . '>',
			'Reply-To: ' . $full_name . ' <' . $email . '>',
		);

		$attachments = array();
		if ( file_exists( $resume_file ) ) {
			$attachments[] = $resume_file;
		}

		$mail_error = '';
		$error_cb   = function( $wp_error ) use ( &$mail_error ) {
			if ( is_wp_error( $wp_error ) ) {
				$mail_error = $wp_error->get_error_message();
			}
		};
		add_action( 'wp_mail_failed', $error_cb );

		$from_cb = function() use ( $from_email ) { return $from_email; };
		$name_cb = function() use ( $from_name ) { return $from_name; };
		add_filter( 'wp_mail_from', $from_cb, 999 );
		add_filter( 'wp_mail_from_name', $name_cb, 999 );

		$sent = wp_mail( $recipient_email, $subject, $body, $headers, $attachments );

		remove_action( 'wp_mail_failed', $error_cb );
		remove_filter( 'wp_mail_from', $from_cb, 999 );
		remove_filter( 'wp_mail_from_name', $name_cb, 999 );

		// Record delivery status and diagnostics on application post
		update_post_meta( $app_id, '_sc_app_email_sent', $sent ? 1 : 0 );
		update_post_meta( $app_id, '_sc_app_email_to', $recipient_email );
		update_post_meta( $app_id, '_sc_app_email_subject', $subject );
		update_post_meta( $app_id, '_sc_app_email_time', current_time( 'mysql' ) );
		if ( ! $sent ) {
			update_post_meta( $app_id, '_sc_app_email_error', $mail_error ?: __( 'Local server has no SMTP server running on localhost:25. Please configure SMTP credentials under SpiceCraft -> Careers: Settings.', 'spicecraft-core' ) );
		} else {
			delete_post_meta( $app_id, '_sc_app_email_error' );
		}

		return (bool) $sent;
	}

	/**
	 * Handle Admin Resend Application Notification Email.
	 */
	public function handle_resend_application_email() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Unauthorized access.', 'spicecraft-core' ), 403 );
		}

		$app_id = isset( $_GET['app_id'] ) ? absint( $_GET['app_id'] ) : 0;
		check_admin_referer( 'spicecraft_resend_email_' . $app_id );

		$app_post = get_post( $app_id );
		if ( ! $app_post || SpiceCraft_Careers_CPT::APPLICATION_CPT !== $app_post->post_type ) {
			wp_die( esc_html__( 'Application record not found.', 'spicecraft-core' ), 404 );
		}

		$recipient_email = spicecraft_get_careers_profile_email();
		$job_title       = get_post_meta( $app_id, '_sc_app_job_title', true );
		$full_name       = get_post_meta( $app_id, '_sc_app_full_name', true ) ?: $app_post->post_title;
		$email           = get_post_meta( $app_id, '_sc_app_email', true );
		$phone           = get_post_meta( $app_id, '_sc_app_phone', true );
		$location        = get_post_meta( $app_id, '_sc_app_location', true );
		$company         = get_post_meta( $app_id, '_sc_app_company', true );
		$designation     = get_post_meta( $app_id, '_sc_app_designation', true );
		$experience      = get_post_meta( $app_id, '_sc_app_experience', true );
		$linkedin        = get_post_meta( $app_id, '_sc_app_linkedin', true );
		$portfolio       = get_post_meta( $app_id, '_sc_app_portfolio', true );
		$cover_msg       = get_post_meta( $app_id, '_sc_app_cover_message', true ) ?: $app_post->post_content;
		$resume_file     = get_post_meta( $app_id, '_sc_app_resume_file', true );
		$orig_filename   = get_post_meta( $app_id, '_sc_app_resume_name', true );

		$sent = $this->send_application_email(
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

		$redirect_url = add_query_arg(
			array(
				'post'         => $app_id,
				'action'       => 'edit',
				'email_resent' => $sent ? '1' : '0',
			),
			admin_url( 'post.php' )
		);

		wp_safe_redirect( $redirect_url );
		exit;
	}

	/**
	 * Display Admin Notices for Application Actions.
	 */
	public function display_admin_notices() {
		$screen = get_current_screen();
		if ( ! $screen || SpiceCraft_Careers_CPT::APPLICATION_CPT !== $screen->post_type ) {
			return;
		}

		if ( isset( $_GET['email_resent'] ) ) {
			if ( '1' === $_GET['email_resent'] ) {
				$recipient = spicecraft_get_careers_profile_email();
				echo '<div class="notice notice-success is-dismissible" style="border-left-color: #059669;"><p><strong>' .
					esc_html( sprintf( __( 'Application notification email was successfully resent to %s with the resume attachment.', 'spicecraft-core' ), $recipient ) ) .
					'</strong></p></div>';
			} else {
				$post_id = isset( $_GET['post'] ) ? absint( $_GET['post'] ) : 0;
				$error   = $post_id ? get_post_meta( $post_id, '_sc_app_email_error', true ) : '';
				echo '<div class="notice notice-error is-dismissible" style="border-left-color: #dc2626;"><p><strong>' .
					esc_html__( 'Failed to send notification email. ', 'spicecraft-core' ) .
					( $error ? esc_html( $error ) : esc_html__( 'Please verify your SMTP server configuration under SpiceCraft -> Careers: Settings.', 'spicecraft-core' ) ) .
					'</strong></p></div>';
			}
		}
	}

	/**
	 * Configure PHPMailer to route emails via custom SMTP when enabled.
	 *
	 * @param PHPMailer\PHPMailer\PHPMailer $phpmailer PHPMailer instance.
	 */
	public function configure_phpmailer( $phpmailer ) {
		$settings = function_exists( 'spicecraft_get_careers_settings' ) ? spicecraft_get_careers_settings() : array();
		if ( empty( $settings['smtp_enabled'] ) ) {
			return;
		}

		$phpmailer->isSMTP();
		$phpmailer->Host = ! empty( $settings['smtp_host'] ) ? $settings['smtp_host'] : 'smtp.cubeontechs.com';
		$phpmailer->Port = ! empty( $settings['smtp_port'] ) ? absint( $settings['smtp_port'] ) : 465;
		$phpmailer->SMTPAuth = ! empty( $settings['smtp_user'] );

		if ( $phpmailer->SMTPAuth ) {
			$phpmailer->AuthType = 'LOGIN'; // Force standard LOGIN auth (prevents CRAM-MD5 failure on cPanel/StackCP)
			$phpmailer->Username = $settings['smtp_user'];
			$phpmailer->Password = ! empty( $settings['smtp_pass'] ) ? $settings['smtp_pass'] : '';
		}

		$enc = ! empty( $settings['smtp_encryption'] ) ? $settings['smtp_encryption'] : 'ssl';
		if ( 'ssl' === $enc ) {
			$phpmailer->SMTPSecure = 'ssl';
		} elseif ( 'tls' === $enc ) {
			$phpmailer->SMTPSecure = 'tls';
		} else {
			$phpmailer->SMTPSecure = '';
			$phpmailer->SMTPAutoTLS = false;
		}

		$from_email = ! empty( $settings['smtp_from_email'] ) ? $settings['smtp_from_email'] : spicecraft_get_careers_profile_email();
		$from_name  = ! empty( $settings['smtp_from_name'] ) ? $settings['smtp_from_name'] : get_bloginfo( 'name' ) . ' Recruitment';
		if ( ! empty( $from_email ) && is_email( $from_email ) ) {
			$phpmailer->setFrom( $from_email, $from_name, false );
			$phpmailer->Sender = $from_email;
		}

		$phpmailer->Timeout = 15;
	}

	/**
	 * Filter wp_mail_from to prevent invalid localhost addresses.
	 *
	 * @param string $original_email Original from email.
	 * @return string Filtered from email.
	 */
	public function filter_mail_from( $original_email ) {
		$settings = function_exists( 'spicecraft_get_careers_settings' ) ? spicecraft_get_careers_settings() : array();
		
		// If SMTP is enabled, use configured SMTP from email
		if ( ! empty( $settings['smtp_enabled'] ) && ! empty( $settings['smtp_from_email'] ) && is_email( $settings['smtp_from_email'] ) ) {
			return $settings['smtp_from_email'];
		}

		// If current from email contains localhost or is invalid, fall back to profile receiving email
		if ( empty( $original_email ) || false !== strpos( $original_email, 'localhost' ) || false === strpos( $original_email, '.' ) ) {
			$fallback = spicecraft_get_careers_profile_email();
			if ( ! empty( $fallback ) && is_email( $fallback ) ) {
				return $fallback;
			}
		}

		return $original_email;
	}

	/**
	 * Filter wp_mail_from_name.
	 *
	 * @param string $original_name Original from name.
	 * @return string Filtered from name.
	 */
	public function filter_mail_from_name( $original_name ) {
		$settings = function_exists( 'spicecraft_get_careers_settings' ) ? spicecraft_get_careers_settings() : array();
		if ( ! empty( $settings['smtp_enabled'] ) && ! empty( $settings['smtp_from_name'] ) ) {
			return $settings['smtp_from_name'];
		}
		if ( empty( $original_name ) || 'WordPress' === $original_name ) {
			return get_bloginfo( 'name' ) . ' Recruitment';
		}
		return $original_name;
	}

	/**
	 * Handle Nonce-Protected Admin Resume Download Stream.
	 */
	public function handle_resume_download() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Unauthorized access.', 'spicecraft-core' ), 403 );
		}

		$app_id = isset( $_GET['app_id'] ) ? absint( $_GET['app_id'] ) : 0;
		check_admin_referer( 'spicecraft_download_resume_' . $app_id );

		$file_path = get_post_meta( $app_id, '_sc_app_resume_file', true );
		$orig_name = get_post_meta( $app_id, '_sc_app_resume_name', true );

		if ( empty( $file_path ) || ! file_exists( $file_path ) ) {
			wp_die( esc_html__( 'Resume file not found on server.', 'spicecraft-core' ), 404 );
		}

		// Security: verify file is within uploads directory
		$upload_dir = wp_upload_dir();
		$real_path  = realpath( $file_path );
		$real_base  = realpath( $upload_dir['basedir'] );

		if ( false === $real_path || false === strpos( $real_path, $real_base ) ) {
			wp_die( esc_html__( 'Illegal file path traversal detected.', 'spicecraft-core' ), 403 );
		}

		$ext       = strtolower( pathinfo( $file_path, PATHINFO_EXTENSION ) );
		$finfo     = finfo_open( FILEINFO_MIME_TYPE );
		$mime_type = finfo_file( $finfo, $file_path );
		finfo_close( $finfo );

		$download_name = ! empty( $orig_name ) ? $orig_name : basename( $file_path );

		// Clear output buffer
		if ( ob_get_level() ) {
			ob_end_clean();
		}

		header( 'Content-Description: File Transfer' );
		header( 'Content-Type: ' . $mime_type );
		header( 'Content-Disposition: attachment; filename="' . esc_attr( $download_name ) . '"' );
		header( 'Content-Transfer-Encoding: binary' );
		header( 'Expires: 0' );
		header( 'Cache-Control: must-revalidate' );
		header( 'Pragma: public' );
		header( 'Content-Length: ' . filesize( $file_path ) );

		readfile( $file_path );
		exit;
	}

	/**
	 * Send Success Response.
	 *
	 * @param string $message Confirmation message.
	 * @param string $job_title Job title.
	 * @param bool   $is_ajax Is AJAX request.
	 */
	private function send_success( $message, $job_title, $is_ajax ) {
		if ( $is_ajax ) {
			wp_send_json_success( array(
				'message'   => $message,
				'job_title' => $job_title,
			) );
		} else {
			wp_safe_redirect( add_query_arg( 'application_submitted', '1', wp_get_referer() ?: home_url( '/careers/' ) ) );
			exit;
		}
	}

	/**
	 * Send Error Response.
	 *
	 * @param string $message Error message.
	 * @param int    $code HTTP status code.
	 * @param bool   $is_ajax Is AJAX request.
	 */
	private function send_error( $message, $code, $is_ajax ) {
		if ( $is_ajax ) {
			wp_send_json_error( array(
				'message' => $message,
			), $code );
		} else {
			wp_die( esc_html( $message ), esc_html__( 'Application Submission Error', 'spicecraft-core' ), array( 'response' => $code ) );
		}
	}

	/**
	 * Helper to get client IP safely.
	 *
	 * @return string IP address.
	 */
	private function get_client_ip() {
		if ( ! empty( $_SERVER['HTTP_CLIENT_IP'] ) ) {
			return sanitize_text_field( wp_unslash( $_SERVER['HTTP_CLIENT_IP'] ) );
		} elseif ( ! empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
			$ips = explode( ',', sanitize_text_field( wp_unslash( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) );
			return trim( $ips[0] );
		} elseif ( ! empty( $_SERVER['REMOTE_ADDR'] ) ) {
			return sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) );
		}
		return '127.0.0.1';
	}
}
