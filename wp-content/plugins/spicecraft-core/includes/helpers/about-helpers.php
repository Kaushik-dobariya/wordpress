<?php
/**
 * SpiceCraft Core - About Page Data Access API & Helper Functions
 *
 * Provides centralized, sanitized getters for About Us CMS settings,
 * section ordering, visibility states, media attachments, and Team/WooCommerce integrations.
 *
 * @package SpiceCraft_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Option name for About Us CMS settings.
 */
if ( ! defined( 'SPICECRAFT_ABOUT_OPTION' ) ) {
	define( 'SPICECRAFT_ABOUT_OPTION', 'spicecraft_about_settings' );
}

/**
 * Retrieve default About Us CMS settings schema.
 * Strict adherence to NO FAKE DATA: no placeholder years, claims, or statistics.
 *
 * @return array
 */
function spicecraft_get_about_default_settings() {
	return array(
		'sections_order'   => array(
			'hero'           => 10,
			'introduction'   => 20,
			'story'          => 30,
			'vision_mission' => 40,
			'values'         => 50,
			'quality'        => 60,
			'sourcing'       => 70,
			'manufacturing'  => 80,
			'statistics'     => 90,
			'milestones'     => 100,
			'leadership'     => 110,
			'certifications' => 120,
			'products'       => 130,
			'b2b_cta'        => 140,
			'final_cta'      => 150,
		),
		'sections_enabled' => array(
			'hero'           => 1,
			'introduction'   => 1,
			'story'          => 1,
			'vision_mission' => 1,
			'values'         => 1,
			'quality'        => 1,
			'sourcing'       => 1,
			'manufacturing'  => 1,
			'statistics'     => 1,
			'milestones'     => 1,
			'leadership'     => 1,
			'certifications' => 1,
			'products'       => 1,
			'b2b_cta'        => 1,
			'final_cta'      => 1,
		),
		'hero'             => array(
			'eyebrow'             => '',
			'heading'             => '',
			'highlight_text'      => '',
			'description'         => '',
			'desktop_image_id'    => 0,
			'mobile_image_id'     => 0,
			'image_alt'           => '',
			'primary_cta_label'   => '',
			'primary_cta_url'     => '',
			'secondary_cta_label' => '',
			'secondary_cta_url'   => '',
		),
		'introduction'     => array(
			'eyebrow'            => '',
			'heading'            => '',
			'content'            => '',
			'primary_image_id'   => 0,
			'secondary_image_id' => 0,
			'cta_label'          => '',
			'cta_url'            => '',
		),
		'story'            => array(
			'eyebrow'            => '',
			'heading'            => '',
			'content'            => '',
			'story_image_id'     => 0,
			'secondary_image_id' => 0,
			'quote_text'         => '',
			'quote_attribution'  => '',
		),
		'vision_mission'   => array(
			'eyebrow'          => '',
			'heading'          => '',
			'description'      => '',
			'vision_enabled'   => 1,
			'vision_heading'   => '',
			'vision_content'   => '',
			'vision_image_id'  => 0,
			'mission_enabled'  => 1,
			'mission_heading'  => '',
			'mission_content'  => '',
			'mission_image_id' => 0,
		),
		'values'           => array(
			'eyebrow'     => '',
			'heading'     => '',
			'description' => '',
			'items'       => array(), // Array of ['icon' => '', 'title' => '', 'description' => '', 'order' => 10]
		),
		'quality'          => array(
			'eyebrow'     => '',
			'heading'     => '',
			'description' => '',
			'image_id'    => 0,
			'points'      => array(), // Array of ['title' => '', 'text' => '']
			'cta_label'   => '',
			'cta_url'     => '',
		),
		'sourcing'         => array(
			'eyebrow'          => '',
			'heading'          => '',
			'description'      => '',
			'image_id'         => 0,
			'support_image_id' => 0,
			'points'           => array(), // Array of ['title' => '', 'text' => '']
			'cta_label'        => '',
			'cta_url'          => '',
		),
		'manufacturing'    => array(
			'eyebrow'       => '',
			'heading'       => '',
			'description'   => '',
			'main_image_id' => 0,
			'video_url'     => '',
			'highlights'    => array(), // Array of ['title' => '', 'text' => '']
			'cta_label'     => '',
			'cta_url'       => '',
		),
		'statistics'       => array(
			'eyebrow'     => '',
			'heading'     => '',
			'description' => '',
			'items'       => array(), // Array of ['value' => '', 'suffix' => '', 'label' => '', 'description' => '', 'order' => 10]
		),
		'milestones'       => array(
			'eyebrow'     => '',
			'heading'     => '',
			'description' => '',
			'items'       => array(), // Array of ['date_label' => '', 'title' => '', 'description' => '', 'image_id' => 0, 'order' => 10]
		),
		'leadership'       => array(
			'eyebrow'      => '',
			'heading'      => '',
			'description'  => '',
			'selected_ids' => array(),
			'limit'        => 6,
		),
		'certifications'   => array(
			'eyebrow'      => '',
			'heading'      => '',
			'description'  => '',
			'selected_ids' => array(),
			'limit'        => 8,
			'cta_label'    => '',
			'cta_url'      => '',
		),
		'products'         => array(
			'eyebrow'      => '',
			'heading'      => '',
			'description'  => '',
			'source'       => 'categories', // 'categories' or 'products'
			'selected_ids' => array(),
			'limit'        => 4,
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
 * Retrieve all About settings merged with defaults.
 *
 * @return array
 */
function spicecraft_get_about_settings() {
	$defaults = spicecraft_get_about_default_settings();
	$saved    = get_option( SPICECRAFT_ABOUT_OPTION, array() );

	if ( ! is_array( $saved ) ) {
		return $defaults;
	}

	$merged = $defaults;
	foreach ( $defaults as $section_key => $default_val ) {
		if ( isset( $saved[ $section_key ] ) && is_array( $saved[ $section_key ] ) ) {
			if ( 'sections_order' === $section_key || 'sections_enabled' === $section_key ) {
				$merged[ $section_key ] = wp_parse_args( $saved[ $section_key ], $default_val );
			} elseif ( isset( $default_val['items'] ) || isset( $default_val['points'] ) || isset( $default_val['highlights'] ) ) {
				// Repeatable arrays should be replaced, not merged by numeric keys
				$merged[ $section_key ] = array_merge( $default_val, $saved[ $section_key ] );
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
 * Update About settings atomically.
 *
 * @param array $settings New settings array.
 * @return bool
 */
function spicecraft_update_about_settings( $settings ) {
	if ( ! is_array( $settings ) ) {
		return false;
	}
	return update_option( SPICECRAFT_ABOUT_OPTION, $settings );
}

/**
 * Retrieve a specific About section's configuration.
 *
 * @param string $section_key Section identifier.
 * @param array  $default     Fallback array.
 * @return array
 */
function spicecraft_get_about_section( $section_key, $default = array() ) {
	$settings = spicecraft_get_about_settings();
	if ( isset( $settings[ $section_key ] ) && is_array( $settings[ $section_key ] ) ) {
		return $settings[ $section_key ];
	}
	return $default;
}

/**
 * Check if an About section is enabled.
 *
 * @param string $section_key Section identifier.
 * @return bool
 */
function spicecraft_is_about_section_enabled( $section_key ) {
	$settings = spicecraft_get_about_settings();
	return ! empty( $settings['sections_enabled'][ $section_key ] );
}

/**
 * Retrieve section order map sorted by priority.
 *
 * @return array
 */
function spicecraft_get_about_section_order() {
	$settings  = spicecraft_get_about_settings();
	$order_map = $settings['sections_order'] ?? array();
	asort( $order_map, SORT_NUMERIC );
	return $order_map;
}

/**
 * Check if a section has meaningful data to render.
 * Used by orchestrator to hide empty sections cleanly.
 *
 * @param string $sec_key Section identifier.
 * @param array  $settings Full settings array.
 * @return bool
 */
function spicecraft_about_section_has_data( $sec_key, $settings ) {
	$sec = $settings[ $sec_key ] ?? array();

	switch ( $sec_key ) {
		case 'hero':
			return ! empty( $sec['heading'] ) || ! empty( $sec['description'] ) || ! empty( $sec['desktop_image_id'] );

		case 'introduction':
			return ! empty( $sec['heading'] ) || ! empty( $sec['content'] );

		case 'story':
			return ! empty( $sec['heading'] ) || ! empty( $sec['content'] ) || ! empty( $sec['quote_text'] );

		case 'vision_mission':
			$has_vision  = ! empty( $sec['vision_enabled'] ) && ( ! empty( $sec['vision_heading'] ) || ! empty( $sec['vision_content'] ) );
			$has_mission = ! empty( $sec['mission_enabled'] ) && ( ! empty( $sec['mission_heading'] ) || ! empty( $sec['mission_content'] ) );
			return $has_vision || $has_mission;

		case 'values':
			return ! empty( $sec['items'] ) && is_array( $sec['items'] );

		case 'quality':
			return ! empty( $sec['heading'] ) || ! empty( $sec['description'] ) || ( ! empty( $sec['points'] ) && is_array( $sec['points'] ) );

		case 'sourcing':
			return ! empty( $sec['heading'] ) || ! empty( $sec['description'] ) || ( ! empty( $sec['points'] ) && is_array( $sec['points'] ) );

		case 'manufacturing':
			return ! empty( $sec['heading'] ) || ! empty( $sec['description'] ) || ( ! empty( $sec['highlights'] ) && is_array( $sec['highlights'] ) );

		case 'statistics':
			return ! empty( $sec['items'] ) && is_array( $sec['items'] );

		case 'milestones':
			return ! empty( $sec['items'] ) && is_array( $sec['items'] );

		case 'leadership':
			// Verify if any team members exist in the system
			$team_members = spicecraft_get_about_team_members( 1, $sec['selected_ids'] ?? array() );
			return ! empty( $team_members );

		case 'certifications':
			$cert_terms = get_terms( array(
				'taxonomy'   => 'spicecraft_certification',
				'hide_empty' => false,
			) );
			return ! empty( $cert_terms ) && ! is_wp_error( $cert_terms );

		case 'products':
			return true; // WooCommerce catalog connection

		case 'b2b_cta':
			return ! empty( $sec['heading'] ) || ! empty( $sec['description'] );

		case 'final_cta':
			return ! empty( $sec['heading'] ) || ! empty( $sec['description'] );

		default:
			return true;
	}
}

/**
 * Retrieve sorted array of enabled About sections that have actual data.
 *
 * @return array Array of active section keys in order.
 */
function spicecraft_get_about_active_sections() {
	$settings  = spicecraft_get_about_settings();
	$order_map = $settings['sections_order'] ?? array();
	$enabled   = $settings['sections_enabled'] ?? array();

	asort( $order_map, SORT_NUMERIC );

	$active = array();
	foreach ( $order_map as $sec_key => $priority ) {
		if ( ! empty( $enabled[ $sec_key ] ) && spicecraft_about_section_has_data( $sec_key, $settings ) ) {
			$active[] = $sec_key;
		}
	}

	return $active;
}

/**
 * Retrieve sorted, valid statistics items.
 *
 * @return array
 */
function spicecraft_get_about_statistics() {
	$sec   = spicecraft_get_about_section( 'statistics' );
	$items = $sec['items'] ?? array();

	if ( ! is_array( $items ) || empty( $items ) ) {
		return array();
	}

	// Filter out empty rows
	$clean_items = array();
	foreach ( $items as $item ) {
		if ( ! empty( $item['value'] ) && ! empty( $item['label'] ) ) {
			$clean_items[] = array(
				'value'       => sanitize_text_field( $item['value'] ),
				'suffix'      => sanitize_text_field( $item['suffix'] ?? '' ),
				'label'       => sanitize_text_field( $item['label'] ),
				'description' => sanitize_text_field( $item['description'] ?? '' ),
				'order'       => isset( $item['order'] ) ? absint( $item['order'] ) : 10,
			);
		}
	}

	// Sort by order
	usort( $clean_items, function ( $a, $b ) {
		return ( $a['order'] ?? 10 ) <=> ( $b['order'] ?? 10 );
	} );

	return $clean_items;
}

/**
 * Retrieve sorted, valid milestone items.
 *
 * @return array
 */
function spicecraft_get_about_milestones() {
	$sec   = spicecraft_get_about_section( 'milestones' );
	$items = $sec['items'] ?? array();

	if ( ! is_array( $items ) || empty( $items ) ) {
		return array();
	}

	$clean_items = array();
	foreach ( $items as $item ) {
		if ( ! empty( $item['title'] ) || ! empty( $item['date_label'] ) ) {
			$clean_items[] = array(
				'date_label'  => sanitize_text_field( $item['date_label'] ?? '' ),
				'title'       => sanitize_text_field( $item['title'] ?? '' ),
				'description' => sanitize_textarea_field( $item['description'] ?? '' ),
				'image_id'    => absint( $item['image_id'] ?? 0 ),
				'order'       => isset( $item['order'] ) ? absint( $item['order'] ) : 10,
			);
		}
	}

	usort( $clean_items, function ( $a, $b ) {
		return ( $a['order'] ?? 10 ) <=> ( $b['order'] ?? 10 );
	} );

	return $clean_items;
}

/**
 * Retrieve sorted, valid Core Values.
 *
 * @return array
 */
function spicecraft_get_about_values() {
	$sec   = spicecraft_get_about_section( 'values' );
	$items = $sec['items'] ?? array();

	if ( ! is_array( $items ) || empty( $items ) ) {
		return array();
	}

	$clean_items = array();
	foreach ( $items as $item ) {
		if ( ! empty( $item['title'] ) ) {
			$clean_items[] = array(
				'icon'        => sanitize_text_field( $item['icon'] ?? '' ),
				'title'       => sanitize_text_field( $item['title'] ),
				'description' => sanitize_textarea_field( $item['description'] ?? '' ),
				'order'       => isset( $item['order'] ) ? absint( $item['order'] ) : 10,
			);
		}
	}

	usort( $clean_items, function ( $a, $b ) {
		return ( $a['order'] ?? 10 ) <=> ( $b['order'] ?? 10 );
	} );

	return $clean_items;
}

/**
 * Retrieve team members for Leadership section.
 *
 * @param int   $limit        Max number of members to retrieve.
 * @param array $selected_ids Optional array of post IDs to filter by.
 * @return array Array of WP_Post objects.
 */
function spicecraft_get_about_team_members( $limit = 6, $selected_ids = array() ) {
	$query_args = array(
		'post_type'      => 'spicecraft_team',
		'post_status'    => 'publish',
		'posts_per_page' => $limit > 0 ? $limit : 6,
		'meta_key'       => '_sc_team_order',
		'orderby'        => 'meta_value_num date',
		'order'          => 'ASC',
	);

	if ( ! empty( $selected_ids ) && is_array( $selected_ids ) ) {
		$valid_ids = array_filter( array_map( 'absint', $selected_ids ) );
		if ( ! empty( $valid_ids ) ) {
			$query_args['post__in'] = $valid_ids;
			$query_args['orderby']  = 'post__in';
		}
	}

	$posts = get_posts( $query_args );

	// Fallback without meta_key if order meta not yet initialized on some members
	if ( empty( $posts ) && empty( $query_args['post__in'] ) ) {
		unset( $query_args['meta_key'] );
		$query_args['orderby'] = 'date';
		$query_args['order']   = 'ASC';
		$posts = get_posts( $query_args );
	}

	return is_array( $posts ) ? $posts : array();
}
