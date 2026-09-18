<?php
/**
 * SpiceCraft Core - Global API & Data Access Helpers
 *
 * Provides safe, sanitized getter functions for global settings and product FMCG data.
 * Designed to be safely called from any theme template or plugin file.
 *
 * @package SpiceCraft_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Retrieve a Global Setting value with fallback support.
 * Checks the unified 'spicecraft_global_settings' option first,
 * then falls back to legacy theme_mods for backward compatibility.
 *
 * @param string $key     Settings key.
 * @param mixed  $default Default value if setting is empty.
 * @return mixed
 */
function spicecraft_get_setting( $key, $default = '' ) {
	$settings = get_option( 'spicecraft_global_settings', array() );

	if ( is_array( $settings ) && isset( $settings[ $key ] ) && '' !== $settings[ $key ] ) {
		return $settings[ $key ];
	}

	// Backward compatibility fallback to theme_mod
	$theme_mod_val = get_theme_mod( $key, null );
	if ( null !== $theme_mod_val && '' !== $theme_mod_val ) {
		return $theme_mod_val;
	}

	return $default;
}

/**
 * Update a single Global Setting key.
 *
 * @param string $key   Settings key.
 * @param mixed  $value Value to store.
 * @return bool
 */
function spicecraft_update_setting( $key, $value ) {
	$settings = get_option( 'spicecraft_global_settings', array() );
	if ( ! is_array( $settings ) ) {
		$settings = array();
	}
	$settings[ $key ] = $value;
	return update_option( 'spicecraft_global_settings', $settings );
}

/**
 * Retrieve all Global Settings as an associative array.
 *
 * @return array
 */
function spicecraft_get_all_settings() {
	$settings = get_option( 'spicecraft_global_settings', array() );
	return is_array( $settings ) ? $settings : array();
}

/**
 * Retrieve structured nutrition information for a product.
 * Returns an array with serving size and repeatable nutrition rows.
 *
 * @param int $product_id Product post ID.
 * @return array Array with 'serving_size' (string) and 'rows' (array of ['nutrient', 'value', 'unit']).
 */
function spicecraft_get_product_nutrition( $product_id ) {
	$serving_size = get_post_meta( $product_id, '_sc_serving_size', true );
	$rows         = get_post_meta( $product_id, '_sc_nutrition_data', true );

	return array(
		'serving_size' => ! empty( $serving_size ) ? sanitize_text_field( $serving_size ) : '',
		'rows'         => is_array( $rows ) ? $rows : array(),
	);
}

/**
 * Retrieve key highlights for a product.
 *
 * @param int $product_id Product post ID.
 * @return array Array of highlight string items.
 */
function spicecraft_get_product_highlights( $product_id ) {
	$highlights = get_post_meta( $product_id, '_sc_highlights', true );
	if ( is_array( $highlights ) ) {
		return array_filter( array_map( 'sanitize_text_field', $highlights ) );
	}
	return array();
}

/**
 * Retrieve product ingredients text.
 *
 * @param int $product_id Product post ID.
 * @return string
 */
function spicecraft_get_product_ingredients( $product_id ) {
	$ingredients = get_post_meta( $product_id, '_sc_ingredients', true );
	return ! empty( $ingredients ) ? wp_kses_post( $ingredients ) : '';
}

/**
 * Retrieve storage instructions for a product.
 *
 * @param int $product_id Product post ID.
 * @return string
 */
function spicecraft_get_product_storage( $product_id ) {
	$storage = get_post_meta( $product_id, '_sc_storage_instructions', true );
	return ! empty( $storage ) ? wp_kses_post( $storage ) : '';
}

/**
 * Retrieve usage and cooking suggestions for a product.
 *
 * @param int $product_id Product post ID.
 * @return string
 */
function spicecraft_get_product_usage( $product_id ) {
	$usage = get_post_meta( $product_id, '_sc_usage_instructions', true );
	return ! empty( $usage ) ? wp_kses_post( $usage ) : '';
}

/**
 * Retrieve shelf life statement for a product.
 *
 * @param int $product_id Product post ID.
 * @return string
 */
function spicecraft_get_product_shelf_life( $product_id ) {
	$shelf_life = get_post_meta( $product_id, '_sc_shelf_life', true );
	return ! empty( $shelf_life ) ? sanitize_text_field( $shelf_life ) : '';
}

/**
 * Retrieve country and region of origin for a product.
 *
 * @param int $product_id Product post ID.
 * @return array Associative array with 'country' and 'region'.
 */
function spicecraft_get_product_origin( $product_id ) {
	$country = get_post_meta( $product_id, '_sc_country_of_origin', true );
	$region  = get_post_meta( $product_id, '_sc_origin_region', true );

	return array(
		'country' => ! empty( $country ) ? sanitize_text_field( $country ) : '',
		'region'  => ! empty( $region ) ? sanitize_text_field( $region ) : '',
	);
}

/**
 * Retrieve certifications associated with a product via the spicecraft_certification taxonomy.
 *
 * @param int $product_id Product post ID.
 * @return array Array of WP_Term objects.
 */
function spicecraft_get_product_certifications( $product_id ) {
	if ( function_exists( 'spicecraft_get_product_public_certifications' ) ) {
		return spicecraft_get_product_public_certifications( $product_id );
	}
	$terms = get_the_terms( $product_id, 'spicecraft_certification' );
	if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
		return $terms;
	}
	return array();
}

/**
 * Retrieve custom repeatable specification rows for a product.
 *
 * @param int $product_id Product post ID.
 * @return array Array of ['label' => string, 'value' => string].
 */
function spicecraft_get_product_custom_specs( $product_id ) {
	$specs = get_post_meta( $product_id, '_sc_specifications', true );
	return is_array( $specs ) ? $specs : array();
}

/**
 * Retrieve product badge metadata (label and style).
 *
 * @param int $product_id Product post ID.
 * @return array Associative array with 'label' and 'style'.
 */
function spicecraft_get_product_badge_meta( $product_id ) {
	$label = get_post_meta( $product_id, '_sc_badge_label', true );
	$style = get_post_meta( $product_id, '_sc_badge_style', true );

	// Fallback to legacy theme meta if present
	if ( empty( $label ) ) {
		$label = get_post_meta( $product_id, '_spicecraft_badge', true );
	}

	return array(
		'label' => ! empty( $label ) ? sanitize_text_field( $label ) : '',
		'style' => ! empty( $style ) ? sanitize_text_field( $style ) : 'primary',
	);
}

/**
 * Normalize and sanitize a phone number for tel: links and WhatsApp API calls.
 * Removes all non-numeric characters except a leading plus sign.
 *
 * @param string $phone Raw phone string.
 * @return string Clean international phone string.
 */
function spicecraft_clean_phone_number( $phone ) {
	return preg_replace( '/[^0-9+]/', '', (string) $phone );
}

/**
 * Format a WhatsApp message template replacing placeholders.
 * Lines containing placeholders with empty variables are cleanly omitted.
 *
 * @param string $template Raw template string.
 * @param array  $vars     Associative array of placeholder values.
 * @return string Clean parsed message.
 */
function spicecraft_format_whatsapp_product_message( $template, $vars = array() ) {
	$lines        = explode( "\n", str_replace( "\r", '', (string) $template ) );
	$output_lines = array();

	foreach ( $lines as $line ) {
		$has_empty_placeholder = false;
		foreach ( array( 'product_name', 'sku', 'pack_size', 'product_url' ) as $key ) {
			$placeholder = '{' . $key . '}';
			if ( false !== strpos( $line, $placeholder ) ) {
				if ( empty( $vars[ $key ] ) ) {
					$has_empty_placeholder = true;
					break;
				} else {
					$line = str_replace( $placeholder, (string) $vars[ $key ], $line );
				}
			}
		}
		if ( ! $has_empty_placeholder ) {
			$output_lines[] = $line;
		}
	}

	$clean_text = preg_replace( "/\n{3,}/", "\n\n", trim( implode( "\n", $output_lines ) ) );
	return (string) $clean_text;
}

/**
 * Generate a dynamic, formatted WhatsApp click-to-chat URL.
 * Adheres to catalog enquiry lead generation requirements.
 * Returns an empty string if WhatsApp number is unconfigured (graceful omission).
 *
 * @param string $product_name Optional product name.
 * @param string $pack_size    Optional pack size.
 * @param string $sku          Optional product SKU.
 * @param string $product_url  Optional product permalink.
 * @return string Valid WhatsApp URL or empty string if unconfigured.
 */
function spicecraft_get_whatsapp_enquiry_url( $product_name = '', $pack_size = '', $sku = '', $product_url = '' ) {
	$raw_whatsapp = spicecraft_get_setting( 'whatsapp_number', '' );
	if ( empty( $raw_whatsapp ) ) {
		return '';
	}

	$clean_number = ltrim( spicecraft_clean_phone_number( $raw_whatsapp ), '+' );
	if ( empty( $clean_number ) ) {
		return '';
	}

	if ( ! empty( $product_name ) ) {
		$custom_tpl = spicecraft_get_setting( 'whatsapp_product_template', '' );
		if ( empty( $custom_tpl ) ) {
			$custom_tpl = "Hello, I am interested in {product_name}.\n\nProduct:\n{product_name}\n\nSKU:\n{sku}\n\nPack Size:\n{pack_size}\n\nProduct URL:\n{product_url}\n\nPlease share more information.";
		}
		$message = spicecraft_format_whatsapp_product_message(
			$custom_tpl,
			array(
				'product_name' => $product_name,
				'sku'          => $sku,
				'pack_size'    => $pack_size,
				'product_url'  => $product_url,
			)
		);
	} else {
		$default_msg = spicecraft_get_setting( 'whatsapp_default_message', '' );
		$message     = ! empty( $default_msg ) ? $default_msg : __( 'Hello, I am interested in your spice products. Please share your catalog and trade enquiry details.', 'spicecraft-core' );
	}

	return 'https://wa.me/' . rawurlencode( $clean_number ) . '?text=' . rawurlencode( $message );
}
