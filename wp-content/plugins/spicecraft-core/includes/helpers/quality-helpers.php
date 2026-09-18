<?php
/**
 * SpiceCraft Core - Quality & Sourcing CMS Data Access Helpers
 *
 * Provides centralized helper functions and data getters for the Quality & Sourcing CMS.
 * Decouples theme templates from direct get_option() calls and implements strict
 * meaningful-data checks to prevent empty decorative section rendering.
 *
 * @package SpiceCraft_Core
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Return default, clean schema for Quality & Sourcing CMS.
 * Strictly adheres to the Zero-Fabricated-Content Rule: zero pre-seeded claims.
 *
 * @return array
 */
function spicecraft_get_quality_default_settings() {
	return array(
		'sections_order' => array(
			'hero'           => 10,
			'introduction'   => 20,
			'principles'     => 30,
			'process'        => 40,
			'testing'        => 50,
			'sourcing'       => 60,
			'regions'        => 70,
			'raw_materials'  => 80,
			'traceability'   => 90,
			'food_safety'    => 100,
			'certifications' => 110,
			'statistics'     => 120,
			'gallery'        => 130,
			'products'       => 140,
			'b2b_cta'        => 150,
			'final_cta'      => 160,
		),
		'sections_enabled' => array(
			'hero'           => 1,
			'introduction'   => 1,
			'principles'     => 1,
			'process'        => 1,
			'testing'        => 1,
			'sourcing'       => 1,
			'regions'        => 1,
			'raw_materials'  => 1,
			'traceability'   => 1,
			'food_safety'    => 1,
			'certifications' => 1,
			'statistics'     => 1,
			'gallery'        => 1,
			'products'       => 1,
			'b2b_cta'        => 1,
			'final_cta'      => 1,
		),
		'hero' => array(
			'eyebrow'             => '',
			'heading'             => '',
			'heading_highlight'   => '',
			'intro'               => '',
			'desktop_image_id'    => 0,
			'mobile_image_id'     => 0,
			'image_alt'           => '',
			'cta_primary_label'   => '',
			'cta_primary_url'     => '',
			'cta_secondary_label' => '',
			'cta_secondary_url'   => '',
		),
		'introduction' => array(
			'eyebrow'            => '',
			'heading'            => '',
			'content'            => '',
			'image_primary_id'   => 0,
			'image_secondary_id' => 0,
			'cta_label'          => '',
			'cta_url'            => '',
		),
		'principles' => array(
			'eyebrow'     => '',
			'heading'     => '',
			'description' => '',
			'items'       => array(),
		),
		'process' => array(
			'eyebrow'     => '',
			'heading'     => '',
			'description' => '',
			'items'       => array(),
		),
		'testing' => array(
			'eyebrow'         => '',
			'heading'         => '',
			'description'     => '',
			'image_id'        => 0,
			'testing_context' => 'not_specified', // not_specified, in_house, external, combination
			'items'           => array(),
		),
		'sourcing' => array(
			'eyebrow'            => '',
			'heading'            => '',
			'description'        => '',
			'image_id'           => 0,
			'image_secondary_id' => 0,
			'highlights'         => array(),
			'cta_label'          => '',
			'cta_url'            => '',
		),
		'regions' => array(
			'eyebrow'     => '',
			'heading'     => '',
			'description' => '',
			'items'       => array(),
		),
		'raw_materials' => array(
			'eyebrow'     => '',
			'heading'     => '',
			'description' => '',
			'items'       => array(),
		),
		'traceability' => array(
			'eyebrow'     => '',
			'heading'     => '',
			'description' => '',
			'image_id'    => 0,
			'steps'       => array(),
		),
		'food_safety' => array(
			'eyebrow'     => '',
			'heading'     => '',
			'description' => '',
			'image_id'    => 0,
			'practices'   => array(),
		),
		'certifications' => array(
			'eyebrow'      => '',
			'heading'      => '',
			'description'  => '',
			'selected_ids' => array(),
			'limit'        => 6,
			'cta_label'    => '',
			'cta_url'      => '',
		),
		'statistics' => array(
			'eyebrow'     => '',
			'heading'     => '',
			'description' => '',
			'items'       => array(),
		),
		'gallery' => array(
			'eyebrow'        => '',
			'heading'        => '',
			'description'    => '',
			'attachment_ids' => array(),
		),
		'products' => array(
			'eyebrow'      => '',
			'heading'      => '',
			'description'  => '',
			'source'       => 'categories',
			'selected_ids' => array(),
			'limit'        => 4,
			'cta_label'    => '',
			'cta_url'      => '',
		),
		'b2b_cta' => array(
			'eyebrow'             => '',
			'heading'             => '',
			'description'         => '',
			'bg_image_id'         => 0,
			'primary_cta_label'   => '',
			'primary_cta_url'     => '',
			'secondary_cta_label' => '',
			'secondary_cta_url'   => '',
			'show_whatsapp'       => 1,
		),
		'final_cta' => array(
			'heading'       => '',
			'description'   => '',
			'cta_label'     => '',
			'cta_url'       => '',
			'show_whatsapp' => 1,
			'show_email'    => 1,
		),
	);
}

/**
 * Retrieve complete Quality & Sourcing CMS settings with default fallback.
 *
 * @return array
 */
function spicecraft_get_quality_settings() {
	$defaults = spicecraft_get_quality_default_settings();
	$stored   = get_option( 'spicecraft_quality_settings', array() );

	if ( ! is_array( $stored ) || empty( $stored ) ) {
		return $defaults;
	}

	$merged = array();
	foreach ( $defaults as $key => $default_val ) {
		if ( ! isset( $stored[ $key ] ) ) {
			$merged[ $key ] = $default_val;
		} elseif ( is_array( $default_val ) && is_array( $stored[ $key ] ) ) {
			if ( in_array( $key, array( 'sections_order', 'sections_enabled' ), true ) ) {
				$merged[ $key ] = array_merge( $default_val, $stored[ $key ] );
			} elseif ( isset( $default_val['items'] ) || isset( $default_val['attachment_ids'] ) ) {
				$merged[ $key ] = array_merge( $default_val, $stored[ $key ] );
			} else {
				$merged[ $key ] = array_merge( $default_val, $stored[ $key ] );
			}
		} else {
			$merged[ $key ] = $stored[ $key ];
		}
	}

	return $merged;
}

/**
 * Update Quality & Sourcing CMS settings.
 *
 * @param array $settings New settings array.
 * @return bool
 */
function spicecraft_update_quality_settings( $settings ) {
	return update_option( 'spicecraft_quality_settings', $settings );
}

/**
 * Retrieve a specific section's data from Quality settings.
 *
 * @param string $section Section key (e.g. 'hero', 'sourcing').
 * @return array
 */
function spicecraft_get_quality_section( $section ) {
	$settings = spicecraft_get_quality_settings();
	return $settings[ $section ] ?? array();
}

/**
 * Check if a Quality section is enabled by admin.
 *
 * @param string $section Section key.
 * @return bool
 */
function spicecraft_is_quality_section_enabled( $section ) {
	$settings = spicecraft_get_quality_settings();
	return ! empty( $settings['sections_enabled'][ $section ] );
}

/**
 * Return sorted array of section keys based on configured priority.
 *
 * @return array Array of section keys ordered by priority.
 */
function spicecraft_get_quality_section_order() {
	$settings  = spicecraft_get_quality_settings();
	$order_map = $settings['sections_order'] ?? array();
	asort( $order_map, SORT_NUMERIC );
	return array_keys( $order_map );
}

/**
 * Check whether a Quality & Sourcing section has genuine content to display.
 * Prevents rendering empty decorative containers when optional data is absent.
 *
 * @param string $section Section identifier.
 * @param array  $data    Section data array.
 * @return bool
 */
function spicecraft_quality_section_has_data( $section, $data ) {
	if ( empty( $data ) || ! is_array( $data ) ) {
		return false;
	}

	switch ( $section ) {
		case 'hero':
			return ! empty( $data['heading'] );

		case 'introduction':
		case 'sourcing':
		case 'b2b_cta':
		case 'final_cta':
			return ! empty( $data['heading'] ) || ! empty( $data['description'] ) || ! empty( $data['content'] );

		case 'principles':
		case 'process':
		case 'testing':
		case 'regions':
		case 'raw_materials':
		case 'statistics':
			return ! empty( $data['items'] ) && is_array( $data['items'] );

		case 'traceability':
			return ! empty( $data['heading'] ) || ! empty( $data['description'] ) || ( ! empty( $data['steps'] ) && is_array( $data['steps'] ) );

		case 'food_safety':
			return ! empty( $data['heading'] ) || ! empty( $data['description'] ) || ( ! empty( $data['practices'] ) && is_array( $data['practices'] ) );

		case 'gallery':
			return ! empty( $data['attachment_ids'] ) && is_array( $data['attachment_ids'] );

		case 'certifications':
			$has_terms = (bool) get_terms( array( 'taxonomy' => 'spicecraft_certification', 'number' => 1, 'hide_empty' => false ) );
			return $has_terms && ( ! empty( $data['heading'] ) || ! empty( $data['selected_ids'] ) );

		case 'products':
			return ! empty( $data['heading'] );

		default:
			return true;
	}
}

/**
 * Retrieve active Quality sections sorted by admin priority.
 *
 * @return array Ordered array of active section keys that have meaningful content.
 */
function spicecraft_get_quality_active_sections() {
	$ordered_sections = spicecraft_get_quality_section_order();
	$active           = array();

	foreach ( $ordered_sections as $sec_key ) {
		if ( ! spicecraft_is_quality_section_enabled( $sec_key ) ) {
			continue;
		}

		$data = spicecraft_get_quality_section( $sec_key );
		if ( ! spicecraft_quality_section_has_data( $sec_key, $data ) ) {
			continue;
		}

		$active[] = $sec_key;
	}

	return $active;
}

/**
 * Return human-friendly label for testing context.
 *
 * @param string $context Context key.
 * @return string
 */
function spicecraft_get_testing_context_label( $context ) {
	switch ( $context ) {
		case 'in_house':
			return __( 'In-House Facility Testing', 'spicecraft-core' );
		case 'external':
			return __( 'External NABL / Accredited Laboratory Testing', 'spicecraft-core' );
		case 'combination':
			return __( 'Hybrid Protocol: In-House & Independent External Testing', 'spicecraft-core' );
		case 'not_specified':
		default:
			return '';
	}
}
