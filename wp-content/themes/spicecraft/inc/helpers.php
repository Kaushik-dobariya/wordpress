<?php
/**
 * SpiceCraft Template Helper Functions & Sanitization Utilities
 *
 * @package SpiceCraft
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Check if WooCommerce plugin is currently active.
 *
 * @return bool
 */
function spicecraft_is_woocommerce_active() {
	return class_exists( 'WooCommerce' );
}

/**
 * Get Theme Mod / Option with safe default fallback.
 *
 * @param string $key     Option identifier.
 * @param mixed  $default Default value if option is empty.
 * @return mixed
 */
function spicecraft_get_theme_option( $key, $default = '' ) {
	if ( function_exists( 'spicecraft_get_setting' ) ) {
		$val = spicecraft_get_setting( $key, null );
		if ( null !== $val && '' !== $val ) {
			return $val;
		}
	}
	$value = get_theme_mod( $key, $default );
	return ! empty( $value ) ? $value : $default;
}

if ( ! function_exists( 'spicecraft_clean_phone_number' ) ) :
	/**
	 * Normalize and sanitize a phone number for tel: links and WhatsApp API calls.
	 * Removes non-numeric characters except leading plus sign.
	 *
	 * @param string $phone Raw phone string.
	 * @return string Clean international phone string.
	 */
	function spicecraft_clean_phone_number( $phone ) {
		return preg_replace( '/[^0-9+]/', '', (string) $phone );
	}
endif;

if ( ! function_exists( 'spicecraft_get_whatsapp_enquiry_url' ) ) :
	/**
	 * Generate a dynamic, formatted WhatsApp click-to-chat URL.
	 * Adheres to catalog enquiry lead generation requirements.
	 *
	 * @param string $product_name Optional product name.
	 * @param string $pack_size    Optional pack size.
	 * @param string $sku          Optional product SKU.
	 * @param string $product_url  Optional product permalink.
	 * @return string Valid WhatsApp URL or empty string if unconfigured.
	 */
	function spicecraft_get_whatsapp_enquiry_url( $product_name = '', $pack_size = '', $sku = '', $product_url = '' ) {
		$raw_whatsapp = spicecraft_get_theme_option( 'whatsapp_number', spicecraft_get_theme_option( 'spicecraft_whatsapp_number', '' ) );
		if ( empty( $raw_whatsapp ) ) {
			return '';
		}

		$clean_number = ltrim( spicecraft_clean_phone_number( $raw_whatsapp ), '+' );
		if ( empty( $clean_number ) ) {
			return '';
		}

		if ( ! empty( $product_name ) ) {
			$lines   = array();
			$lines[] = sprintf( __( 'Hello, I am interested in %s.', 'spicecraft' ), $product_name );
			$lines[] = '';
			$lines[] = sprintf( __( 'Product: %s', 'spicecraft' ), $product_name );
			if ( ! empty( $sku ) ) {
				$lines[] = sprintf( __( 'SKU: %s', 'spicecraft' ), $sku );
			}
			if ( ! empty( $pack_size ) ) {
				$lines[] = sprintf( __( 'Pack Size: %s', 'spicecraft' ), $pack_size );
			}
			if ( ! empty( $product_url ) ) {
				$lines[] = sprintf( __( 'Product Link: %s', 'spicecraft' ), $product_url );
			}
			$lines[] = '';
			$lines[] = __( 'Please share more information regarding business/bulk supply.', 'spicecraft' );
			$message = implode( "\r\n", $lines );
		} else {
			$default_msg = spicecraft_get_theme_option( 'whatsapp_default_message', '' );
			$message     = ! empty( $default_msg ) ? $default_msg : __( 'Hello, I am interested in your spice products. Please share your catalog and trade enquiry details.', 'spicecraft' );
		}

		return 'https://wa.me/' . rawurlencode( $clean_number ) . '?text=' . rawurlencode( $message );
	}
endif;

/**
 * Prints HTML with meta information for the current post-date/time.
 */
function spicecraft_posted_on() {
	$time_string = '<time class="entry-date published updated" datetime="%1$s">%2$s</time>';
	if ( get_the_time( 'U' ) !== get_the_modified_time( 'U' ) ) {
		$time_string = '<time class="entry-date published" datetime="%1$s">%2$s</time><time class="updated screen-reader-text" datetime="%3$s">%4$s</time>';
	}

	$time_string = sprintf(
		$time_string,
		esc_attr( get_the_date( DATE_W3C ) ),
		esc_html( get_the_date() ),
		esc_attr( get_the_modified_date( DATE_W3C ) ),
		esc_html( get_the_modified_date() )
	);

	echo '<span class="posted-on">' . $time_string . '</span>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
}

/**
 * Prints HTML with meta information about post author.
 */
function spicecraft_posted_by() {
	$byline = sprintf(
		/* translators: %s: post author. */
		esc_html_x( 'by %s', 'post author', 'spicecraft' ),
		'<span class="author vcard"><a class="url fn n" href="' . esc_url( get_author_posts_url( get_the_author_meta( 'ID' ) ) ) . '">' . esc_html( get_the_author() ) . '</a></span>'
	);

	echo '<span class="byline"> ' . $byline . '</span>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
}

/**
 * Prints HTML with categories, tags and comments.
 */
function spicecraft_entry_footer() {
	// Hide category and tag text for pages.
	if ( 'post' === get_post_type() ) {
		$categories_list = get_the_category_list( esc_html__( ', ', 'spicecraft' ) );
		if ( $categories_list ) {
			/* translators: 1: posted in label, 2: list of categories */
			printf( '<span class="cat-links">' . esc_html__( 'Posted in %1$s', 'spicecraft' ) . '</span>', $categories_list ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}

		$tags_list = get_the_tag_list( '', esc_html_x( ', ', 'list item separator', 'spicecraft' ) );
		if ( $tags_list ) {
			/* translators: 1: tagged label, 2: list of tags */
			printf( '<span class="tags-links">' . esc_html__( 'Tagged %1$s', 'spicecraft' ) . '</span>', $tags_list ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}
	}

	edit_post_link(
		sprintf(
			wp_kses(
				/* translators: %s: Name of current post. Only visible to screen readers */
				__( 'Edit <span class="screen-reader-text">%s</span>', 'spicecraft' ),
				array(
					'span' => array(
						'class' => array(),
					),
				)
			),
			wp_kses_post( get_the_title() )
		),
		'<span class="edit-link">',
		'</span>'
	);
}

/**
 * Retrieve product badge (e.g., 100% PURE, EXPORT GRADE, ORGANIC, BEST SELLER).
 * Only renders when actual product metadata or tags provide it.
 *
 * @param int $product_id Product ID.
 * @return string
 */
function spicecraft_get_product_badge( $product_id ) {
	$badge_data = spicecraft_get_product_badge_data( $product_id );
	return $badge_data['label'];
}

/**
 * Retrieve full product badge information including display style.
 *
 * @param int $product_id Product ID.
 * @return array Associative array with 'label' and 'style'.
 */
function spicecraft_get_product_badge_data( $product_id ) {
	if ( function_exists( 'spicecraft_get_product_badge_meta' ) ) {
		$badge_meta = spicecraft_get_product_badge_meta( $product_id );
		if ( ! empty( $badge_meta['label'] ) ) {
			return $badge_meta;
		}
	}

	$label = get_post_meta( $product_id, '_sc_badge_label', true );
	$style = get_post_meta( $product_id, '_sc_badge_style', true );
	if ( ! empty( $label ) ) {
		return array(
			'label' => sanitize_text_field( $label ),
			'style' => ! empty( $style ) ? sanitize_text_field( $style ) : 'primary',
		);
	}

	$legacy_badge = get_post_meta( $product_id, '_spicecraft_badge', true );
	if ( ! empty( $legacy_badge ) ) {
		return array(
			'label' => sanitize_text_field( $legacy_badge ),
			'style' => 'primary',
		);
	}

	// Check tags as fallback
	$tags = wp_get_post_terms( $product_id, 'product_tag', array( 'fields' => 'names' ) );
	if ( ! empty( $tags ) && ! is_wp_error( $tags ) ) {
		return array(
			'label' => sanitize_text_field( $tags[0] ),
			'style' => 'secondary',
		);
	}

	return array(
		'label' => '',
		'style' => 'primary',
	);
}

/**
 * Retrieve available pack sizes dynamically from product attributes & FMCG metadata.
 * Supports both custom 'Pack Size' WooCommerce attributes and '_sc_pack_sizes' meta.
 *
 * @param WC_Product $product WooCommerce Product object.
 * @return array Array of clean pack size label strings.
 */
function spicecraft_get_product_pack_sizes( $product ) {
	if ( ! $product instanceof WC_Product ) {
		return array();
	}

	$pack_sizes = array();

	// 1. Check WooCommerce Product Attributes
	$attributes = $product->get_attributes();
	if ( ! empty( $attributes ) ) {
		foreach ( $attributes as $attr_name => $attr_obj ) {
			$lower_name = strtolower( $attr_name );
			if ( false !== strpos( $lower_name, 'pack' ) || false !== strpos( $lower_name, 'size' ) || false !== strpos( $lower_name, 'weight' ) ) {
				if ( is_a( $attr_obj, 'WC_Product_Attribute' ) ) {
					$options = $attr_obj->get_options();
					if ( is_array( $options ) && ! empty( $options ) ) {
						if ( $attr_obj->is_taxonomy() ) {
							$terms = wc_get_product_terms( $product->get_id(), $attr_obj->get_name(), array( 'fields' => 'names' ) );
							if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
								$pack_sizes = array_merge( $pack_sizes, array_map( 'sanitize_text_field', $terms ) );
							}
						} else {
							$pack_sizes = array_merge( $pack_sizes, array_map( 'sanitize_text_field', $options ) );
						}
					}
				}
			}
		}
	}

	// 2. Check Custom FMCG Pack Sizes Meta Field (_sc_pack_sizes)
	$meta_packs = get_post_meta( $product->get_id(), '_sc_pack_sizes', true );
	if ( ! empty( $meta_packs ) ) {
		$parsed = array_map( 'trim', explode( ',', $meta_packs ) );
		foreach ( $parsed as $p ) {
			if ( '' !== $p && ! in_array( $p, $pack_sizes, true ) ) {
				$pack_sizes[] = sanitize_text_field( $p );
			}
		}
	}

	return array_values( array_unique( $pack_sizes ) );
}

/**
 * Retrieve the primary or first category name for a product.
 *
 * @param int $product_id Product ID.
 * @return string Category name.
 */
function spicecraft_get_product_primary_category( $product_id ) {
	$terms = get_the_terms( $product_id, 'product_cat' );
	if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
		// Filter out 'Uncategorized' if other categories exist
		foreach ( $terms as $term ) {
			if ( 'uncategorized' !== $term->slug ) {
				return $term->name;
			}
		}
		return $terms[0]->name;
	}
	return esc_html__( 'Pure Spices', 'spicecraft' );
}

/**
 * Retrieve structured product specifications dynamically from attributes & metadata.
 * Merges native WooCommerce attributes with custom FMCG spice specifications.
 *
 * @param WC_Product $product WooCommerce Product object.
 * @return array Associative array of [ 'Label' => 'Value' ].
 */
function spicecraft_get_product_specs( $product ) {
	if ( ! $product instanceof WC_Product ) {
		return array();
	}

	$product_id = $product->get_id();
	$specs      = array();

	// SKU
	$sku = $product->get_sku();
	if ( ! empty( $sku ) ) {
		$specs[ __( 'Product SKU', 'spicecraft' ) ] = $sku;
	}

	// Primary Category
	$primary_cat = spicecraft_get_product_primary_category( $product_id );
	if ( ! empty( $primary_cat ) ) {
		$specs[ __( 'Category', 'spicecraft' ) ] = $primary_cat;
	}

	// Product Form / Type (Whole, Powder, Blended, Flakes, etc.)
	$form = get_post_meta( $product_id, '_sc_form', true );
	if ( ! empty( $form ) ) {
		$specs[ __( 'Product Form', 'spicecraft' ) ] = sanitize_text_field( $form );
	}

	// Shelf Life
	$shelf_life = function_exists( 'spicecraft_get_product_shelf_life' ) ? spicecraft_get_product_shelf_life( $product_id ) : get_post_meta( $product_id, '_sc_shelf_life', true );
	if ( empty( $shelf_life ) ) {
		$shelf_life = get_post_meta( $product_id, '_spicecraft_shelf_life', true );
	}
	if ( ! empty( $shelf_life ) ) {
		$specs[ __( 'Shelf Life', 'spicecraft' ) ] = sanitize_text_field( $shelf_life );
	}

	// Origin & Region
	if ( function_exists( 'spicecraft_get_product_origin' ) ) {
		$origin_info = spicecraft_get_product_origin( $product_id );
		if ( ! empty( $origin_info['country'] ) ) {
			$specs[ __( 'Country of Origin', 'spicecraft' ) ] = $origin_info['country'];
		}
		if ( ! empty( $origin_info['region'] ) ) {
			$specs[ __( 'Sourcing Region', 'spicecraft' ) ] = $origin_info['region'];
		}
	} else {
		$country = get_post_meta( $product_id, '_sc_country_of_origin', true );
		if ( ! empty( $country ) ) {
			$specs[ __( 'Country of Origin', 'spicecraft' ) ] = sanitize_text_field( $country );
		}
	}

	// WooCommerce Native Attributes (excluding pack size / weight)
	$attributes = $product->get_attributes();
	if ( ! empty( $attributes ) ) {
		foreach ( $attributes as $attr_name => $attr_obj ) {
			$lower = strtolower( $attr_name );
			// Skip pack size as it has its own dedicated interactive selector
			if ( false !== strpos( $lower, 'pack' ) || false !== strpos( $lower, 'size' ) || false !== strpos( $lower, 'weight' ) ) {
				continue;
			}
			if ( is_a( $attr_obj, 'WC_Product_Attribute' ) ) {
				$label = wc_attribute_label( $attr_obj->get_name() );
				if ( $attr_obj->is_taxonomy() ) {
					$terms = wc_get_product_terms( $product_id, $attr_obj->get_name(), array( 'fields' => 'names' ) );
					if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
						$specs[ $label ] = implode( ', ', $terms );
					}
				} else {
					$options = $attr_obj->get_options();
					if ( ! empty( $options ) ) {
						$specs[ $label ] = implode( ', ', $options );
					}
				}
			}
		}
	}

	// Custom Repeatable Specifications Table from SpiceCraft Core
	if ( function_exists( 'spicecraft_get_product_custom_specs' ) ) {
		$custom_specs = spicecraft_get_product_custom_specs( $product_id );
		if ( ! empty( $custom_specs ) && is_array( $custom_specs ) ) {
			foreach ( $custom_specs as $row ) {
				if ( ! empty( $row['label'] ) && ! empty( $row['value'] ) ) {
					$specs[ sanitize_text_field( $row['label'] ) ] = sanitize_text_field( $row['value'] );
				}
			}
		}
	}

	return $specs;
}

if ( ! function_exists( 'spicecraft_get_homepage_settings' ) ) :
	/**
	 * Safe fallback for spicecraft_get_homepage_settings.
	 *
	 * @return array
	 */
	function spicecraft_get_homepage_settings() {
		return get_option( 'spicecraft_homepage_settings', array() );
	}
endif;

if ( ! function_exists( 'spicecraft_get_homepage_section' ) ) :
	/**
	 * Safe fallback for spicecraft_get_homepage_section.
	 *
	 * @param string $section_key Section key.
	 * @param array  $default     Default array.
	 * @return array
	 */
	function spicecraft_get_homepage_section( $section_key, $default = array() ) {
		$settings = spicecraft_get_homepage_settings();
		return isset( $settings[ $section_key ] ) && is_array( $settings[ $section_key ] ) ? $settings[ $section_key ] : $default;
	}
endif;

if ( ! function_exists( 'spicecraft_is_homepage_section_enabled' ) ) :
	/**
	 * Safe fallback for spicecraft_is_homepage_section_enabled.
	 *
	 * @param string $section_key Section key.
	 * @return bool
	 */
	function spicecraft_is_homepage_section_enabled( $section_key ) {
		$settings = spicecraft_get_homepage_settings();
		if ( isset( $settings['sections_enabled'][ $section_key ] ) ) {
			return (bool) $settings['sections_enabled'][ $section_key ];
		}
		return 'recipes' !== $section_key; // recipes defaults to false
	}
endif;

if ( ! function_exists( 'spicecraft_get_homepage_active_sections' ) ) :
	/**
	 * Safe fallback for spicecraft_get_homepage_active_sections.
	 *
	 * @return array
	 */
	function spicecraft_get_homepage_active_sections() {
		$default_order = array(
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
		);

		$settings = spicecraft_get_homepage_settings();
		$order    = isset( $settings['sections_order'] ) ? wp_parse_args( $settings['sections_order'], $default_order ) : $default_order;
		asort( $order, SORT_NUMERIC );

		$active = array();
		foreach ( array_keys( $order ) as $key ) {
			if ( spicecraft_is_homepage_section_enabled( $key ) ) {
				$active[] = $key;
			}
		}
		return $active;
	}
endif;

if ( ! function_exists( 'spicecraft_get_media_image' ) ) :
	/**
	 * Safe fallback for spicecraft_get_media_image.
	 *
	 * @param int          $attachment_id Attachment ID.
	 * @param string|array $size          Image size.
	 * @param array        $attr          Image attributes.
	 * @param int          $fallback_id   Fallback attachment ID.
	 * @return string
	 */
	function spicecraft_get_media_image( $attachment_id, $size = 'full', $attr = array(), $fallback_id = 0 ) {
		$id = absint( $attachment_id );
		if ( ! $id && $fallback_id ) {
			$id = absint( $fallback_id );
		}
		return $id ? wp_get_attachment_image( $id, $size, false, $attr ) : '';
	}
endif;

