<?php
/**
 * SpiceCraft Theme Customizer Architecture
 *
 * Provides selective refresh support for core site identity (logo, title, tagline).
 * All business contacts, WhatsApp lead routing, addresses, statutory disclosures,
 * and footer content are centrally managed under SpiceCraft -> Global Settings
 * in the WordPress admin bar.
 *
 * @package SpiceCraft
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register Theme Customizer adjustments and guide notice.
 *
 * @param WP_Customize_Manager $wp_customize Theme Customizer object.
 */
function spicecraft_customize_register( $wp_customize ) {

	// Selective refresh for site title and tagline
	$wp_customize->get_setting( 'blogname' )->transport         = 'postMessage';
	$wp_customize->get_setting( 'blogdescription' )->transport  = 'postMessage';
	$wp_customize->get_setting( 'header_textcolor' )->transport = 'postMessage';

	if ( isset( $wp_customize->selective_refresh ) ) {
		$wp_customize->selective_refresh->add_partial(
			'blogname',
			array(
				'selector'        => '.site-title a',
				'render_callback' => 'spicecraft_customize_partial_blogname',
			)
		);
		$wp_customize->selective_refresh->add_partial(
			'blogdescription',
			array(
				'selector'        => '.site-description',
				'render_callback' => 'spicecraft_customize_partial_blogdescription',
			)
		);
	}

	// Guidance Section pointing to Centralized Global Settings
	$wp_customize->add_section(
		'spicecraft_admin_guidance_section',
		array(
			'title'       => esc_html__( 'SpiceCraft Global CMS Settings', 'spicecraft' ),
			'description' => sprintf(
				/* translators: %s: URL to SpiceCraft Global Settings page */
				__( 'Business contacts, WhatsApp lead routing, addresses, statutory certifications (FSSAI/GST/IEC), and footer legal text are centrally managed in the WordPress admin under <a href="%s" target="_blank" style="font-weight: 600; text-decoration: underline;">SpiceCraft &rarr; Global Settings</a>.', 'spicecraft' ),
				esc_url( admin_url( 'admin.php?page=spicecraft-settings' ) )
			),
			'priority'    => 20,
		)
	);

	// A dummy setting so section displays in Customizer
	$wp_customize->add_setting(
		'spicecraft_cms_notice',
		array(
			'default'           => '',
			'sanitize_callback' => 'sanitize_text_field',
		)
	);

	$wp_customize->add_control(
		new WP_Customize_Control(
			$wp_customize,
			'spicecraft_cms_notice',
			array(
				'label'       => esc_html__( 'Centralized Management', 'spicecraft' ),
				'description' => esc_html__( 'To prevent hard-coded data and ensure consistency across catalog enquiry CTAs, please use the dedicated SpiceCraft CMS menu.', 'spicecraft' ),
				'section'     => 'spicecraft_admin_guidance_section',
				'type'        => 'hidden',
			)
		)
	);
}
add_action( 'customize_register', 'spicecraft_customize_register' );

/**
 * Render the site title for the selective refresh partial.
 *
 * @return void
 */
function spicecraft_customize_partial_blogname() {
	bloginfo( 'name' );
}

/**
 * Render the site tagline for the selective refresh partial.
 *
 * @return void
 */
function spicecraft_customize_partial_blogdescription() {
	bloginfo( 'description' );
}
