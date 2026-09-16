<?php
/**
 * SpiceCraft Core - Homepage Data Access API & Helper Functions
 *
 * Provides centralized, sanitized getters for homepage CMS settings,
 * section ordering, visibility states, media attachments, and WooCommerce/Post integrations.
 *
 * @package SpiceCraft_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Option name for homepage CMS settings.
 */
if ( ! defined( 'SPICECRAFT_HOMEPAGE_OPTION' ) ) {
	define( 'SPICECRAFT_HOMEPAGE_OPTION', 'spicecraft_homepage_settings' );
}

/**
 * Retrieve the default homepage schema and initial configuration.
 * Adheres strictly to the NO FAKE DATA policy: no fabricated certifications,
 * fake reviews, or arbitrary production metrics.
 *
 * @return array
 */
function spicecraft_get_homepage_default_settings() {
	return array(
		'sections_order'   => array(
			'hero'              => 10,
			'categories'        => 20,
			'featured_products' => 30,
			'brand_story'       => 40,
			'why_choose_us'     => 50,
			'quality_sourcing'  => 60,
			'manufacturing'     => 70,
			'certifications'    => 80,
			'product_discovery' => 90,
			'recipes'           => 100,
			'testimonials'      => 110,
			'blog'              => 120,
			'b2b_cta'           => 130,
			'final_cta'         => 140,
		),
		'sections_enabled' => array(
			'hero'              => 1,
			'categories'        => 1,
			'featured_products' => 1,
			'brand_story'       => 1,
			'why_choose_us'     => 1,
			'quality_sourcing'  => 1,
			'manufacturing'     => 1,
			'certifications'    => 1,
			'product_discovery' => 1,
			'recipes'           => 0, // Disabled by default until recipes/inspiration configured
			'testimonials'      => 1,
			'blog'              => 1,
			'b2b_cta'           => 1,
			'final_cta'         => 1,
		),
		'hero'             => array(
			'eyebrow'             => '',
			'heading'             => '',
			'highlight_text'      => '',
			'description'         => '',
			'primary_cta_label'   => '',
			'primary_cta_url'     => '',
			'secondary_cta_label' => '',
			'secondary_cta_url'   => '',
			'desktop_image_id'    => 0,
			'mobile_image_id'     => 0,
			'image_alt'           => '',
			'badge_text'          => '',
			'bg_treatment'        => 'gradient', // gradient, dark, subtle
		),
		'categories'       => array(
			'eyebrow'      => '',
			'heading'      => '',
			'description'  => '',
			'display_mode' => 'all', // all, top_level, manual
			'limit'        => 6,
			'selected_ids' => array(),
			'cta_label'    => '',
			'cta_url'      => '',
		),
		'featured_products'=> array(
			'eyebrow'      => '',
			'heading'      => '',
			'description'  => '',
			'source'       => 'featured', // featured, manual, latest
			'limit'        => 8,
			'selected_ids' => array(),
			'cta_label'    => '',
			'cta_url'      => '',
		),
		'brand_story'      => array(
			'eyebrow'            => '',
			'heading'            => '',
			'description'        => '',
			'primary_image_id'   => 0,
			'secondary_image_id' => 0,
			'stat_label'         => '',
			'stat_value'         => '',
			'cta_label'          => '',
			'cta_url'            => '',
		),
		'why_choose_us'    => array(
			'eyebrow'     => '',
			'heading'     => '',
			'description' => '',
			'items'       => array(), // Array of ['icon' => '', 'title' => '', 'description' => '', 'order' => 10]
		),
		'quality_sourcing' => array(
			'eyebrow'          => '',
			'heading'          => '',
			'description'      => '',
			'main_image_id'    => 0,
			'support_image_id' => 0,
			'points'           => array(), // Array of ['title' => '', 'text' => '']
			'cta_label'        => '',
			'cta_url'          => '',
		),
		'manufacturing'    => array(
			'eyebrow'          => '',
			'heading'          => '',
			'description'      => '',
			'main_image_id'    => 0,
			'support_image_id' => 0,
			'video_url'        => '',
			'stats'            => array(), // Array of ['label' => '', 'value' => '']
			'cta_label'        => '',
			'cta_url'          => '',
		),
		'certifications'   => array(
			'eyebrow'      => '',
			'heading'      => '',
			'description'  => '',
			'limit'        => 6,
			'selected_ids' => array(),
		),
		'product_discovery'=> array(
			'eyebrow'      => '',
			'heading'      => '',
			'description'  => '',
			'category_ids' => array(),
			'cta_label'    => '',
			'cta_url'      => '',
		),
		'recipes'          => array(
			'eyebrow'     => '',
			'heading'     => '',
			'description' => '',
			'source_type' => 'post_category', // post_category, manual
			'category_id' => 0,
			'limit'       => 3,
			'cta_label'   => '',
			'cta_url'     => '',
		),
		'testimonials'     => array(
			'eyebrow'      => '',
			'heading'      => '',
			'description'  => '',
			'limit'        => 6,
			'selected_ids' => array(),
		),
		'blog'             => array(
			'eyebrow'      => '',
			'heading'      => '',
			'description'  => '',
			'source'       => 'latest', // latest, category, manual
			'category_id'  => 0,
			'selected_ids' => array(),
			'limit'        => 3,
			'cta_label'    => '',
			'cta_url'      => '',
		),
		'b2b_cta'          => array(
			'eyebrow'             => '',
			'heading'             => '',
			'description'         => '',
			'bg_image_id'         => 0,
			'primary_cta_label'   => '',
			'primary_cta_url'     => '',
			'secondary_cta_label' => '',
			'secondary_cta_url'   => '',
			'enable_whatsapp'     => 1,
		),
		'final_cta'        => array(
			'heading'           => '',
			'description'       => '',
			'primary_cta_label' => '',
			'primary_cta_url'   => '',
			'enable_whatsapp'   => 1,
			'enable_email'      => 1,
		),
	);
}

/**
 * Retrieve the entire homepage settings array with defaults merged.
 *
 * @return array
 */
function spicecraft_get_homepage_settings() {
	$defaults = spicecraft_get_homepage_default_settings();
	$saved    = get_option( SPICECRAFT_HOMEPAGE_OPTION, array() );

	if ( ! is_array( $saved ) ) {
		return $defaults;
	}

	// Deep merge with defaults to ensure all keys exist
	$merged = $defaults;
	foreach ( $defaults as $section_key => $default_val ) {
		if ( isset( $saved[ $section_key ] ) && is_array( $saved[ $section_key ] ) ) {
			if ( 'sections_order' === $section_key || 'sections_enabled' === $section_key ) {
				$merged[ $section_key ] = wp_parse_args( $saved[ $section_key ], $default_val );
			} else {
				$merged[ $section_key ] = array_merge( $default_val, $saved[ $section_key ] );
			}
		} elseif ( isset( $saved[ $section_key ] ) ) {
			$merged[ $section_key ] = $saved[ $section_key ];
		}
	}

	return $merged;
}

/**
 * Update homepage settings atomically.
 *
 * @param array $settings Complete or partial settings array.
 * @return bool
 */
function spicecraft_update_homepage_settings( $settings ) {
	if ( ! is_array( $settings ) ) {
		return false;
	}
	return update_option( SPICECRAFT_HOMEPAGE_OPTION, $settings );
}

/**
 * Retrieve configuration data for a specific homepage section.
 *
 * @param string $section_key Section identifier (e.g. 'hero', 'brand_story').
 * @param array  $default     Default value if not found.
 * @return array
 */
function spicecraft_get_homepage_section( $section_key, $default = array() ) {
	$all_settings = spicecraft_get_homepage_settings();
	if ( isset( $all_settings[ $section_key ] ) && is_array( $all_settings[ $section_key ] ) ) {
		return $all_settings[ $section_key ];
	}
	return $default;
}

/**
 * Check if a specific homepage section is enabled.
 *
 * @param string $section_key Section identifier.
 * @return bool
 */
function spicecraft_is_homepage_section_enabled( $section_key ) {
	$all_settings = spicecraft_get_homepage_settings();
	$enabled_map  = isset( $all_settings['sections_enabled'] ) ? $all_settings['sections_enabled'] : array();

	if ( isset( $enabled_map[ $section_key ] ) ) {
		return (bool) $enabled_map[ $section_key ];
	}

	// Fallback to default
	$defaults = spicecraft_get_homepage_default_settings();
	return ! empty( $defaults['sections_enabled'][ $section_key ] );
}

/**
 * Retrieve all registered homepage section keys sorted by numeric priority.
 *
 * @return array Array of section keys sorted in ascending order of priority.
 */
function spicecraft_get_homepage_section_order() {
	$all_settings = spicecraft_get_homepage_settings();
	$order_map    = isset( $all_settings['sections_order'] ) ? $all_settings['sections_order'] : array();

	// Ensure all standard sections are in the map
	$defaults = spicecraft_get_homepage_default_settings();
	$order_map = wp_parse_args( $order_map, $defaults['sections_order'] );

	asort( $order_map, SORT_NUMERIC );
	return array_keys( $order_map );
}

/**
 * Retrieve active (enabled) homepage section keys sorted by numeric priority.
 *
 * @return array Array of active section keys in display order.
 */
function spicecraft_get_homepage_active_sections() {
	$ordered_sections = spicecraft_get_homepage_section_order();
	$active_sections  = array();

	foreach ( $ordered_sections as $section_key ) {
		if ( spicecraft_is_homepage_section_enabled( $section_key ) ) {
			$active_sections[] = $section_key;
		}
	}

	return $active_sections;
}

/**
 * Safely render a responsive image from an attachment ID using WordPress Core image APIs.
 * Preserves srcset, sizes, alt attributes, and lazy loading.
 *
 * @param int          $attachment_id Attachment ID.
 * @param string|array $size          Image size name or [width, height].
 * @param array        $attr          Optional extra HTML attributes for the img tag.
 * @param int          $fallback_id   Optional fallback attachment ID if primary is empty.
 * @return string HTML img tag or empty string.
 */
function spicecraft_get_media_image( $attachment_id, $size = 'full', $attr = array(), $fallback_id = 0 ) {
	$id = absint( $attachment_id );
	if ( ! $id && $fallback_id ) {
		$id = absint( $fallback_id );
	}

	if ( ! $id ) {
		return '';
	}

	return wp_get_attachment_image( $id, $size, false, $attr );
}

/**
 * Retrieve the URL of an attachment image.
 *
 * @param int    $attachment_id Attachment ID.
 * @param string $size          Image size.
 * @param int    $fallback_id   Optional fallback attachment ID.
 * @return string Image URL or empty string.
 */
function spicecraft_get_media_image_url( $attachment_id, $size = 'full', $fallback_id = 0 ) {
	$id = absint( $attachment_id );
	if ( ! $id && $fallback_id ) {
		$id = absint( $fallback_id );
	}

	if ( ! $id ) {
		return '';
	}

	$src = wp_get_attachment_image_src( $id, $size );
	return is_array( $src ) ? $src[0] : '';
}
