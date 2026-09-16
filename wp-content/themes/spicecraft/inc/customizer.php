<?php
/**
 * SpiceCraft Global Settings & Customizer Architecture
 *
 * Implements WordPress-native Customizer settings for centralized business information.
 * Avoids hardcoded phone numbers, emails, addresses, and statutory credentials.
 *
 * @package SpiceCraft
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register customizer options for SpiceCraft.
 *
 * @param WP_Customize_Manager $wp_customize Theme Customizer object.
 */
function spicecraft_customize_register( $wp_customize ) {

	// =========================================================================
	// PANEL: SpiceCraft Business & Brand Settings
	// =========================================================================
	$wp_customize->add_panel(
		'spicecraft_business_panel',
		array(
			'title'       => esc_html__( 'SpiceCraft Global Settings', 'spicecraft' ),
			'description' => esc_html__( 'Manage contact information, WhatsApp lead routing, addresses, and statutory credentials.', 'spicecraft' ),
			'priority'    => 30,
		)
	);

	// -------------------------------------------------------------------------
	// SECTION 1: Contact & Enquiry Routing
	// -------------------------------------------------------------------------
	$wp_customize->add_section(
		'spicecraft_contact_section',
		array(
			'title'    => esc_html__( 'Contact & WhatsApp Enquiry', 'spicecraft' ),
			'panel'    => 'spicecraft_business_panel',
			'priority' => 10,
		)
	);

	// WhatsApp Number (Primary Lead Channel)
	$wp_customize->add_setting(
		'spicecraft_whatsapp_number',
		array(
			'default'           => '+91 98765 43210',
			'sanitize_callback' => 'sanitize_text_field',
			'transport'         => 'refresh',
		)
	);
	$wp_customize->add_control(
		'spicecraft_whatsapp_number',
		array(
			'label'       => esc_html__( 'WhatsApp Business Number', 'spicecraft' ),
			'description' => esc_html__( 'Include country code (e.g., +91 9876543210). Used across all catalog enquiry buttons.', 'spicecraft' ),
			'section'     => 'spicecraft_contact_section',
			'type'        => 'text',
		)
	);

	// Phone Number
	$wp_customize->add_setting(
		'spicecraft_phone_number',
		array(
			'default'           => '+91 (0) 79 1234 5678',
			'sanitize_callback' => 'sanitize_text_field',
			'transport'         => 'refresh',
		)
	);
	$wp_customize->add_control(
		'spicecraft_phone_number',
		array(
			'label'   => esc_html__( 'Primary Contact Phone', 'spicecraft' ),
			'section' => 'spicecraft_contact_section',
			'type'    => 'text',
		)
	);

	// General Inquiries Email
	$wp_customize->add_setting(
		'spicecraft_contact_email',
		array(
			'default'           => 'info@spicecraft.local',
			'sanitize_callback' => 'sanitize_email',
			'transport'         => 'postMessage',
		)
	);
	$wp_customize->add_control(
		'spicecraft_contact_email',
		array(
			'label'   => esc_html__( 'General Contact Email', 'spicecraft' ),
			'section' => 'spicecraft_contact_section',
			'type'    => 'email',
		)
	);

	// Trade / Export Enquiry Email
	$wp_customize->add_setting(
		'spicecraft_export_email',
		array(
			'default'           => 'exports@spicecraft.local',
			'sanitize_callback' => 'sanitize_email',
			'transport'         => 'postMessage',
		)
	);
	$wp_customize->add_control(
		'spicecraft_export_email',
		array(
			'label'       => esc_html__( 'Export & Bulk Trade Email', 'spicecraft' ),
			'description' => esc_html__( 'Dedicated inbox for domestic distribution and international trade enquiries.', 'spicecraft' ),
			'section'     => 'spicecraft_contact_section',
			'type'        => 'email',
		)
	);

	// Business Working Hours
	$wp_customize->add_setting(
		'spicecraft_business_hours',
		array(
			'default'           => 'Mon – Sat: 9:00 AM – 6:00 PM IST',
			'sanitize_callback' => 'sanitize_text_field',
			'transport'         => 'refresh',
		)
	);
	$wp_customize->add_control(
		'spicecraft_business_hours',
		array(
			'label'   => esc_html__( 'Business & Factory Hours', 'spicecraft' ),
			'section' => 'spicecraft_contact_section',
			'type'    => 'text',
		)
	);

	// -------------------------------------------------------------------------
	// SECTION 2: Physical & Factory Addresses
	// -------------------------------------------------------------------------
	$wp_customize->add_section(
		'spicecraft_address_section',
		array(
			'title'    => esc_html__( 'Offices & Manufacturing Units', 'spicecraft' ),
			'panel'    => 'spicecraft_business_panel',
			'priority' => 20,
		)
	);

	// Corporate / Registered Office
	$wp_customize->add_setting(
		'spicecraft_corporate_address',
		array(
			'default'           => 'SpiceCraft Agro Foods Ltd., Heritage Spice Hub, Ahmedabad, Gujarat, India.',
			'sanitize_callback' => 'sanitize_textarea_field',
			'transport'         => 'refresh',
		)
	);
	$wp_customize->add_control(
		'spicecraft_corporate_address',
		array(
			'label'   => esc_html__( 'Corporate Office Address', 'spicecraft' ),
			'section' => 'spicecraft_address_section',
			'type'    => 'textarea',
		)
	);

	// Manufacturing / Processing Plant
	$wp_customize->add_setting(
		'spicecraft_factory_address',
		array(
			'default'           => 'GIDC Agro Park Phase II, Unjha - Sanand Industrial Corridor, Gujarat, India.',
			'sanitize_callback' => 'sanitize_textarea_field',
			'transport'         => 'refresh',
		)
	);
	$wp_customize->add_control(
		'spicecraft_factory_address',
		array(
			'label'   => esc_html__( 'Manufacturing & Export Plant', 'spicecraft' ),
			'section' => 'spicecraft_address_section',
			'type'    => 'textarea',
		)
	);

	// -------------------------------------------------------------------------
	// SECTION 3: Social Channels & External Links
	// -------------------------------------------------------------------------
	$wp_customize->add_section(
		'spicecraft_social_section',
		array(
			'title'    => esc_html__( 'Social Media Links', 'spicecraft' ),
			'panel'    => 'spicecraft_business_panel',
			'priority' => 30,
		)
	);

	$socials = array(
		'spicecraft_social_facebook'  => esc_html__( 'Facebook URL', 'spicecraft' ),
		'spicecraft_social_instagram' => esc_html__( 'Instagram URL', 'spicecraft' ),
		'spicecraft_social_linkedin'  => esc_html__( 'LinkedIn URL', 'spicecraft' ),
		'spicecraft_social_youtube'   => esc_html__( 'YouTube Channel URL', 'spicecraft' ),
	);

	foreach ( $socials as $id => $label ) {
		$wp_customize->add_setting(
			$id,
			array(
				'default'           => '',
				'sanitize_callback' => 'esc_url_raw',
				'transport'         => 'refresh',
			)
		);
		$wp_customize->add_control(
			$id,
			array(
				'label'   => $label,
				'section' => 'spicecraft_social_section',
				'type'    => 'url',
			)
		);
	}

	// -------------------------------------------------------------------------
	// SECTION 4: Statutory Certifications & Footer Details
	// -------------------------------------------------------------------------
	$wp_customize->add_section(
		'spicecraft_footer_section',
		array(
			'title'    => esc_html__( 'Footer & Statutory Details', 'spicecraft' ),
			'panel'    => 'spicecraft_business_panel',
			'priority' => 40,
		)
	);

	// FSSAI License Number
	$wp_customize->add_setting(
		'spicecraft_fssai_license',
		array(
			'default'           => 'FSSAI Lic. No.: 10012021000123',
			'sanitize_callback' => 'sanitize_text_field',
			'transport'         => 'refresh',
		)
	);
	$wp_customize->add_control(
		'spicecraft_fssai_license',
		array(
			'label'       => esc_html__( 'FSSAI License Label / Number', 'spicecraft' ),
			'description' => esc_html__( 'Mandatory food safety compliance disclosure for Indian food manufacturers.', 'spicecraft' ),
			'section'     => 'spicecraft_footer_section',
			'type'        => 'text',
		)
	);

	// Certifications Label
	$wp_customize->add_setting(
		'spicecraft_certifications_note',
		array(
			'default'           => 'ISO 22000:2018 | HACCP | HALAL | US FDA Registered | Spices Board India Certified',
			'sanitize_callback' => 'sanitize_text_field',
			'transport'         => 'refresh',
		)
	);
	$wp_customize->add_control(
		'spicecraft_certifications_note',
		array(
			'label'   => esc_html__( 'Certifications Summary Note', 'spicecraft' ),
			'section' => 'spicecraft_footer_section',
			'type'    => 'text',
		)
	);

	// Custom Copyright Text
	$wp_customize->add_setting(
		'spicecraft_copyright_text',
		array(
			'default'           => '',
			'sanitize_callback' => 'sanitize_text_field',
			'transport'         => 'refresh',
		)
	);
	$wp_customize->add_control(
		'spicecraft_copyright_text',
		array(
			'label'       => esc_html__( 'Custom Copyright Notice', 'spicecraft' ),
			'description' => esc_html__( 'Leave empty to use automatic: © [Year] [Site Name]. All Rights Reserved.', 'spicecraft' ),
			'section'     => 'spicecraft_footer_section',
			'type'        => 'text',
		)
	);
}
add_action( 'customize_register', 'spicecraft_customize_register' );
