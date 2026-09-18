<?php
/**
 * SpiceCraft Core - Manufacturing CMS Data Access Helpers
 *
 * Provides centralized helper functions and data getters for the Manufacturing CMS.
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
 * Return default, clean schema for Manufacturing CMS.
 * Strictly adheres to the Zero-Fabricated-Content Rule: zero pre-seeded claims.
 *
 * @return array
 */
function spicecraft_get_manufacturing_default_settings() {
	return array(
		'sections_order' => array(
			'hero'           => 10,
			'introduction'   => 20,
			'facility'       => 30,
			'process'        => 40,
			'capabilities'   => 50,
			'equipment'      => 60,
			'hygiene'        => 70,
			'packaging'      => 80,
			'warehousing'    => 90,
			'statistics'     => 100,
			'gallery'        => 110,
			'certifications' => 120,
			'products'       => 130,
			'b2b_cta'        => 140,
			'final_cta'      => 150,
		),
		'sections_enabled' => array(
			'hero'           => 1,
			'introduction'   => 1,
			'facility'       => 1,
			'process'        => 1,
			'capabilities'   => 1,
			'equipment'      => 1,
			'hygiene'        => 1,
			'packaging'      => 1,
			'warehousing'    => 1,
			'statistics'     => 1,
			'gallery'        => 1,
			'certifications' => 1,
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
		'facility' => array(
			'eyebrow'            => '',
			'heading'            => '',
			'description'        => '',
			'image_id'           => 0,
			'image_secondary_id' => 0,
			'highlights'         => array(),
		),
		'process' => array(
			'eyebrow'     => '',
			'heading'     => '',
			'description' => '',
			'items'       => array(),
		),
		'capabilities' => array(
			'eyebrow'     => '',
			'heading'     => '',
			'description' => '',
			'items'       => array(),
			'cta_label'   => '',
			'cta_url'     => '',
		),
		'equipment' => array(
			'eyebrow'     => '',
			'heading'     => '',
			'description' => '',
			'items'       => array(),
		),
		'hygiene' => array(
			'eyebrow'     => '',
			'heading'     => '',
			'description' => '',
			'image_id'    => 0,
			'practices'   => array(),
			'cta_label'   => '',
			'cta_url'     => '',
		),
		'packaging' => array(
			'eyebrow'            => '',
			'heading'            => '',
			'description'        => '',
			'image_id'           => 0,
			'image_secondary_id' => 0,
			'capabilities'       => array(),
			'category_ids'       => array(),
			'cta_label'          => '',
			'cta_url'            => '',
		),
		'warehousing' => array(
			'eyebrow'     => '',
			'heading'     => '',
			'description' => '',
			'image_id'    => 0,
			'highlights'  => array(),
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
		'certifications' => array(
			'eyebrow'      => '',
			'heading'      => '',
			'description'  => '',
			'selected_ids' => array(),
			'limit'        => 6,
			'cta_label'    => '',
			'cta_url'      => '',
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
 * Retrieve complete Manufacturing CMS settings with default fallback.
 *
 * @return array
 */
function spicecraft_get_manufacturing_settings() {
	$defaults = spicecraft_get_manufacturing_default_settings();
	$stored   = get_option( 'spicecraft_manufacturing_settings', array() );

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
 * Update Manufacturing CMS settings.
 *
 * @param array $settings New settings array.
 * @return bool
 */
function spicecraft_update_manufacturing_settings( $settings ) {
	return update_option( 'spicecraft_manufacturing_settings', $settings );
}

/**
 * Retrieve a specific section's data from Manufacturing settings.
 *
 * @param string $section Section key (e.g. 'hero', 'facility').
 * @return array
 */
function spicecraft_get_manufacturing_section( $section ) {
	$settings = spicecraft_get_manufacturing_settings();
	return $settings[ $section ] ?? array();
}

/**
 * Check if a Manufacturing section is enabled by admin.
 *
 * @param string $section Section key.
 * @return bool
 */
function spicecraft_is_manufacturing_section_enabled( $section ) {
	$settings = spicecraft_get_manufacturing_settings();
	return ! empty( $settings['sections_enabled'][ $section ] );
}

/**
 * Return sorted array of section keys based on configured priority.
 *
 * @return array Array of section keys ordered by priority.
 */
function spicecraft_get_manufacturing_section_order() {
	$settings  = spicecraft_get_manufacturing_settings();
	$order_map = $settings['sections_order'] ?? array();
	asort( $order_map, SORT_NUMERIC );
	return array_keys( $order_map );
}

/**
 * Check whether a Manufacturing section has genuine content to display.
 * Prevents rendering empty decorative containers when optional data is absent.
 *
 * @param string $section Section identifier.
 * @param array  $data    Section data array.
 * @return bool
 */
function spicecraft_manufacturing_section_has_data( $section, $data ) {
	if ( empty( $data ) || ! is_array( $data ) ) {
		return false;
	}

	switch ( $section ) {
		case 'hero':
			return ! empty( $data['heading'] );

		case 'introduction':
		case 'facility':
		case 'b2b_cta':
		case 'final_cta':
			return ! empty( $data['heading'] ) || ! empty( $data['description'] ) || ! empty( $data['content'] );

		case 'process':
		case 'capabilities':
		case 'equipment':
		case 'statistics':
			return ! empty( $data['items'] ) && is_array( $data['items'] );

		case 'hygiene':
			return ! empty( $data['heading'] ) || ! empty( $data['description'] ) || ! empty( $data['practices'] );

		case 'packaging':
			return ! empty( $data['heading'] ) || ! empty( $data['description'] ) || ! empty( $data['capabilities'] );

		case 'warehousing':
			return ! empty( $data['heading'] ) || ! empty( $data['description'] ) || ! empty( $data['highlights'] );

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
 * Retrieve active Manufacturing sections sorted by admin priority.
 *
 * @return array Ordered array of active section keys that have meaningful content.
 */
function spicecraft_get_manufacturing_active_sections() {
	$ordered_sections = spicecraft_get_manufacturing_section_order();
	$active           = array();

	foreach ( $ordered_sections as $sec_key ) {
		if ( ! spicecraft_is_manufacturing_section_enabled( $sec_key ) ) {
			continue;
		}

		$data = spicecraft_get_manufacturing_section( $sec_key );
		if ( ! spicecraft_manufacturing_section_has_data( $sec_key, $data ) ) {
			continue;
		}

		$active[] = $sec_key;
	}

	return $active;
}
