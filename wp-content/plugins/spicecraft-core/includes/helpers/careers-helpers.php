<?php
/**
 * SpiceCraft Core - Careers & Job Opening Helper Functions
 *
 * Provides safe, sanitized getter functions for Careers settings,
 * Job Openings metadata, application statuses, and email configurations.
 * Designed to be safely called from theme templates and plugin handlers.
 *
 * @package SpiceCraft_Core
 * @since 1.3.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Option key for Careers settings.
 */
if ( ! defined( 'SPICECRAFT_CAREERS_OPTION' ) ) {
	define( 'SPICECRAFT_CAREERS_OPTION', 'spicecraft_careers_settings' );
}

/**
 * Initial / Default Careers profile receiving email.
 * Critical requirement: Default is career@cubeontechs.com,
 * but runtime execution must ALWAYS read the current backend setting.
 */
if ( ! defined( 'SPICECRAFT_CAREERS_DEFAULT_EMAIL' ) ) {
	define( 'SPICECRAFT_CAREERS_DEFAULT_EMAIL', 'career@cubeontechs.com' );
}

/**
 * Retrieve the default Careers settings array.
 *
 * @return array
 */
function spicecraft_get_careers_default_settings() {
	return array(
		// General / Hero
		'page_title'               => __( 'Careers & Opportunities', 'spicecraft-core' ),
		'hero_badge'               => __( 'We Are Hiring', 'spicecraft-core' ),
		'hero_title'               => __( 'Craft Your Career With Heritage & Innovation', 'spicecraft-core' ),
		'hero_subtitle'            => __( 'Join India\'s premier artisanal spice manufacturer. Explore opportunities across master blending, culinary research, milling technology, quality assurance, and international export trade.', 'spicecraft-core' ),
		'hero_image_id'            => 0,

		// Why Join Us / Culture
		'why_join_title'           => __( 'Why Build Your Career at SpiceCraft?', 'spicecraft-core' ),
		'why_join_subtitle'        => __( 'We unite four decades of traditional Indian spice craftsmanship with cutting-edge cryo-milling facilities, ethical farm partnerships, and an uncompromising commitment to purity.', 'spicecraft-core' ),
		'culture_points'           => array(
			array(
				'title'       => __( 'Heritage Meets Modern Technology', 'spicecraft-core' ),
				'description' => __( 'Work in state-of-the-art cryo-milling and cold-grinding facilities engineered to preserve volatile essential oils.', 'spicecraft-core' ),
				'icon'        => 'shield',
			),
			array(
				'title'       => __( 'Direct Farmer Partnerships', 'spicecraft-core' ),
				'description' => __( 'Collaborate with over 500 family-owned estates across Malabar, Wayanad, and Guntur practicing regenerative agro-forestry.', 'spicecraft-core' ),
				'icon'        => 'leaf',
			),
			array(
				'title'       => __( 'Global Quality Standards', 'spicecraft-core' ),
				'description' => __( 'Operate in ISO 22000, FSSC 22000, US FDA registered, and BRCGS certified clean-room processing environments.', 'spicecraft-core' ),
				'icon'        => 'award',
			),
			array(
				'title'       => __( 'Continuous Learning & Growth', 'spicecraft-core' ),
				'description' => __( 'Access culinary masterclasses, food science certifications, leadership mentoring, and comprehensive health benefits.', 'spicecraft-core' ),
				'icon'        => 'heart',
			),
		),

		// Application Settings
		'profile_email'            => SPICECRAFT_CAREERS_DEFAULT_EMAIL,
		'application_title'        => __( 'Apply for This Position', 'spicecraft-core' ),
		'application_intro'        => __( 'Please complete the form below and upload your latest resume. Our talent acquisition team reviews every profile with utmost confidentiality.', 'spicecraft-core' ),
		'success_message'          => __( 'Application Submitted Successfully. Thank you for your interest in joining our team. We have received your application for: {Job Title}. Our team will review your profile and contact you if there is a suitable opportunity.', 'spicecraft-core' ),
		'privacy_text'             => __( 'I agree that the information provided may be used for recruitment and employment-related communication.', 'spicecraft-core' ),

		// Display Toggles
		'show_closed_jobs'         => 1,
		'show_salary'              => 0,
		'show_department'          => 1,
		'show_location'            => 1,
		'show_employment_type'     => 1,
		'show_experience'          => 1,

		// General Application / Bottom CTA
		'general_application_title'=> __( 'Don\'t See the Right Role?', 'spicecraft-core' ),
		'general_application_desc' => __( 'We are always looking for passionate food technologists, quality auditors, procurement specialists, and supply chain professionals. Send us your profile for future openings.', 'spicecraft-core' ),
		'general_application_btn'  => __( 'Submit General Profile', 'spicecraft-core' ),
		'contact_hr_cta'           => __( 'Contact Talent Desk', 'spicecraft-core' ),
		'contact_hr_email'         => SPICECRAFT_CAREERS_DEFAULT_EMAIL,

		// Outbound SMTP Transport Delivery
		'smtp_enabled'             => 1,
		'smtp_host'                => 'smtp.cubeontechs.com',
		'smtp_port'                => 465,
		'smtp_encryption'          => 'ssl',
		'smtp_user'                => 'career@cubeontechs.com',
		'smtp_pass'                => '',
		'smtp_from_email'          => SPICECRAFT_CAREERS_DEFAULT_EMAIL,
		'smtp_from_name'           => 'SpiceCraft Recruitment',
	);
}

/**
 * Retrieve all Careers settings with defaults merged.
 *
 * @return array
 */
function spicecraft_get_careers_settings() {
	$defaults = spicecraft_get_careers_default_settings();
	$saved    = get_option( SPICECRAFT_CAREERS_OPTION, array() );

	if ( ! is_array( $saved ) ) {
		$saved = array();
	}

	return wp_parse_args( $saved, $defaults );
}

/**
 * Retrieve a single Careers setting value.
 *
 * @param string $key     Setting key.
 * @param mixed  $default Fallback value.
 * @return mixed
 */
function spicecraft_get_careers_setting( $key, $default = '' ) {
	$settings = spicecraft_get_careers_settings();
	if ( isset( $settings[ $key ] ) && '' !== $settings[ $key ] ) {
		return $settings[ $key ];
	}
	return $default;
}

/**
 * Retrieve the current Careers profile receiving email.
 *
 * CRITICAL REQUIREMENT:
 * 1. Must NEVER be hard-coded inside application-processing logic.
 * 2. Always reads the CURRENT backend setting from the database.
 * 3. Default initial value is career@cubeontechs.com if unset in database.
 *
 * @return string Clean, sanitized email address.
 */
function spicecraft_get_careers_profile_email() {
	$saved = get_option( SPICECRAFT_CAREERS_OPTION, array() );

	if ( is_array( $saved ) && ! empty( $saved['profile_email'] ) ) {
		$email = sanitize_email( $saved['profile_email'] );
		if ( is_email( $email ) ) {
			return $email;
		}
	}

	// Secondary fallback to Global Settings if configured
	if ( function_exists( 'spicecraft_get_setting' ) ) {
		$global_career_email = spicecraft_get_setting( 'email_career', '' );
		if ( ! empty( $global_career_email ) && is_email( $global_career_email ) ) {
			return sanitize_email( $global_career_email );
		}
	}

	// Final default fallback
	return SPICECRAFT_CAREERS_DEFAULT_EMAIL;
}

/**
 * Update the Careers profile receiving email in the database.
 *
 * @param string $email Valid email address.
 * @return bool True if updated, false on error.
 */
function spicecraft_update_careers_profile_email( $email ) {
	$clean_email = sanitize_email( $email );
	if ( ! is_email( $clean_email ) ) {
		return false;
	}

	$settings                  = get_option( SPICECRAFT_CAREERS_OPTION, array() );
	if ( ! is_array( $settings ) ) {
		$settings = array();
	}
	$settings['profile_email'] = $clean_email;

	return update_option( SPICECRAFT_CAREERS_OPTION, $settings );
}

/**
 * Retrieve standard employment type choices.
 *
 * @return array
 */
function spicecraft_get_employment_types() {
	return array(
		'full_time'  => __( 'Full Time', 'spicecraft-core' ),
		'part_time'  => __( 'Part Time', 'spicecraft-core' ),
		'contract'   => __( 'Contract', 'spicecraft-core' ),
		'internship' => __( 'Internship', 'spicecraft-core' ),
		'temporary'  => __( 'Temporary', 'spicecraft-core' ),
	);
}

/**
 * Retrieve standard application statuses.
 *
 * @return array
 */
function spicecraft_get_application_statuses() {
	return array(
		'new'          => array(
			'label' => __( 'New', 'spicecraft-core' ),
			'color' => '#1d70b8',
			'bg'    => '#e8f1f8',
		),
		'under_review' => array(
			'label' => __( 'Under Review', 'spicecraft-core' ),
			'color' => '#d97706',
			'bg'    => '#fef3c7',
		),
		'shortlisted'  => array(
			'label' => __( 'Shortlisted', 'spicecraft-core' ),
			'color' => '#285238',
			'bg'    => '#dcfce7',
		),
		'interview'    => array(
			'label' => __( 'Interview', 'spicecraft-core' ),
			'color' => '#7c3aed',
			'bg'    => '#ede9fe',
		),
		'rejected'     => array(
			'label' => __( 'Rejected', 'spicecraft-core' ),
			'color' => '#b32d2e',
			'bg'    => '#fee2e2',
		),
		'hired'        => array(
			'label' => __( 'Hired', 'spicecraft-core' ),
			'color' => '#059669',
			'bg'    => '#d1fae5',
		),
	);
}

/**
 * Retrieve all structured metadata for a Job Opening.
 *
 * @param int $post_id Job post ID.
 * @return array Sanitized job metadata.
 */
function spicecraft_get_job_meta( $post_id ) {
	$post_id = absint( $post_id );
	if ( ! $post_id ) {
		return array();
	}

	$status       = get_post_meta( $post_id, '_sc_job_status', true );
	$deadline     = get_post_meta( $post_id, '_sc_job_deadline', true );
	$department   = get_post_meta( $post_id, '_sc_job_department', true );
	$location     = get_post_meta( $post_id, '_sc_job_location', true );
	$emp_type     = get_post_meta( $post_id, '_sc_job_type', true );
	$experience   = get_post_meta( $post_id, '_sc_job_experience', true );
	$openings     = get_post_meta( $post_id, '_sc_job_openings', true );
	$salary       = get_post_meta( $post_id, '_sc_job_salary', true );
	$is_featured  = get_post_meta( $post_id, '_sc_job_featured', true );

	// Repeatables
	$responsibilities = get_post_meta( $post_id, '_sc_job_responsibilities', true );
	$qualifications   = get_post_meta( $post_id, '_sc_job_qualifications', true );
	$pref_qual        = get_post_meta( $post_id, '_sc_job_preferred_qualifications', true );
	$skills           = get_post_meta( $post_id, '_sc_job_skills', true );
	$benefits         = get_post_meta( $post_id, '_sc_job_benefits', true );

	// Resolve department from taxonomy if available
	$dept_terms = get_the_terms( $post_id, 'spicecraft_department' );
	if ( empty( $department ) && ! empty( $dept_terms ) && ! is_wp_error( $dept_terms ) ) {
		$department = $dept_terms[0]->name;
	}

	$is_closed = spicecraft_is_job_closed( $post_id );

	return array(
		'status'                   => ! empty( $status ) ? $status : 'published',
		'is_closed'                => $is_closed,
		'deadline'                 => ! empty( $deadline ) ? sanitize_text_field( $deadline ) : '',
		'deadline_formatted'       => ! empty( $deadline ) ? spicecraft_format_job_deadline( $deadline ) : '',
		'department'               => ! empty( $department ) ? sanitize_text_field( $department ) : '',
		'location'                 => ! empty( $location ) ? sanitize_text_field( $location ) : '',
		'employment_type'          => ! empty( $emp_type ) ? sanitize_text_field( $emp_type ) : 'full_time',
		'employment_type_label'    => spicecraft_get_employment_type_label( $emp_type ),
		'experience'               => ! empty( $experience ) ? sanitize_text_field( $experience ) : '',
		'openings'                 => ! empty( $openings ) ? absint( $openings ) : 1,
		'salary'                   => ! empty( $salary ) ? sanitize_text_field( $salary ) : '',
		'is_featured'              => ! empty( $is_featured ),
		'responsibilities'         => is_array( $responsibilities ) ? array_filter( array_map( 'sanitize_text_field', $responsibilities ) ) : array(),
		'qualifications'           => is_array( $qualifications ) ? array_filter( array_map( 'sanitize_text_field', $qualifications ) ) : array(),
		'preferred_qualifications' => is_array( $pref_qual ) ? array_filter( array_map( 'sanitize_text_field', $pref_qual ) ) : array(),
		'skills'                   => is_array( $skills ) ? array_filter( array_map( 'sanitize_text_field', $skills ) ) : array(),
		'benefits'                 => is_array( $benefits ) ? array_filter( array_map( 'sanitize_text_field', $benefits ) ) : array(),
	);
}

/**
 * Check if a Job is closed or expired.
 *
 * @param int $post_id Job post ID.
 * @return bool True if closed/expired, false if active.
 */
function spicecraft_is_job_closed( $post_id ) {
	$post = get_post( $post_id );
	if ( ! $post || 'publish' !== $post->post_status ) {
		return true;
	}

	$status = get_post_meta( $post_id, '_sc_job_status', true );
	if ( 'closed' === $status ) {
		return true;
	}

	$deadline = get_post_meta( $post_id, '_sc_job_deadline', true );
	if ( ! empty( $deadline ) ) {
		$deadline_ts = strtotime( $deadline . ' 23:59:59' );
		if ( $deadline_ts && time() > $deadline_ts ) {
			return true;
		}
	}

	return false;
}

/**
 * Format a deadline date for human display.
 *
 * @param string $deadline_str Date string in YYYY-MM-DD.
 * @return string Formatted date string (e.g. "October 15, 2026").
 */
function spicecraft_format_job_deadline( $deadline_str ) {
	if ( empty( $deadline_str ) ) {
		return '';
	}
	$ts = strtotime( $deadline_str );
	if ( ! $ts ) {
		return $deadline_str;
	}
	return date_i18n( get_option( 'date_format', 'F j, Y' ), $ts );
}

/**
 * Get human-readable label for an employment type key.
 *
 * @param string $type_key Key (e.g. 'full_time').
 * @return string Label.
 */
function spicecraft_get_employment_type_label( $type_key ) {
	$types = spicecraft_get_employment_types();
	return $types[ $type_key ] ?? ucfirst( str_replace( '_', ' ', $type_key ) );
}

/**
 * Count applications received for a specific job.
 *
 * @param int $job_id Job post ID.
 * @return int Total applications count.
 */
function spicecraft_get_job_applications_count( $job_id ) {
	$job_id = absint( $job_id );
	if ( ! $job_id ) {
		return 0;
	}

	$query = new WP_Query( array(
		'post_type'      => 'spicecraft_app',
		'post_status'    => array( 'publish', 'private' ),
		'posts_per_page' => -1,
		'fields'         => 'ids',
		'meta_query'     => array(
			array(
				'key'   => '_sc_app_job_id',
				'value' => $job_id,
			),
		),
	) );

	return $query->found_posts;
}

/**
 * Get the public URL of the Careers page.
 *
 * @return string URL.
 */
function spicecraft_get_careers_url() {
	$archive_url = get_post_type_archive_link( 'spicecraft_job' );
	if ( $archive_url ) {
		return esc_url( $archive_url );
	}
	return esc_url( home_url( '/careers/' ) );
}
