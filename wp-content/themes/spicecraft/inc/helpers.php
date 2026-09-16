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
	$value = get_theme_mod( $key, $default );
	return ! empty( $value ) ? $value : $default;
}

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

/**
 * Generate a dynamic, formatted WhatsApp click-to-chat URL.
 * Adheres to catalog enquiry lead generation requirements.
 *
 * @param string $product_name Optional product name.
 * @param string $pack_size    Optional pack size.
 * @return string Valid WhatsApp URL or '#' if unconfigured.
 */
function spicecraft_get_whatsapp_enquiry_url( $product_name = '', $pack_size = '' ) {
	$raw_whatsapp = spicecraft_get_theme_option( 'spicecraft_whatsapp_number', '+91 98765 43210' );
	if ( empty( $raw_whatsapp ) ) {
		return '#';
	}

	$clean_number = ltrim( spicecraft_clean_phone_number( $raw_whatsapp ), '+' );

	if ( ! empty( $product_name ) ) {
		$size_label = ! empty( $pack_size ) ? ' - ' . $pack_size : '';
		$message = sprintf(
			/* translators: 1: Product name, 2: Pack size */
			__( 'Hello, I am interested in %1$s%2$s. Please share more information regarding business/bulk supply.', 'spicecraft' ),
			$product_name,
			$size_label
		);
	} else {
		$message = __( 'Hello, I am interested in your spice products. Please share your catalog and trade enquiry details.', 'spicecraft' );
	}

	return 'https://wa.me/' . rawurlencode( $clean_number ) . '?text=' . rawurlencode( $message );
}

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
	$badge = get_post_meta( $product_id, '_spicecraft_badge', true );
	if ( ! empty( $badge ) ) {
		return sanitize_text_field( $badge );
	}

	// Check tags as fallback
	$tags = wp_get_post_terms( $product_id, 'product_tag', array( 'fields' => 'names' ) );
	if ( ! empty( $tags ) && ! is_wp_error( $tags ) ) {
		return sanitize_text_field( $tags[0] );
	}

	return '';
}

/**
 * Retrieve available pack sizes dynamically from product attributes.
 * Supports both custom 'Pack Size' attributes and global taxonomies.
 *
 * @param WC_Product $product WooCommerce Product object.
 * @return array Array of pack size label strings.
 */
function spicecraft_get_product_pack_sizes( $product ) {
	if ( ! $product instanceof WC_Product ) {
		return array();
	}

	$attributes = $product->get_attributes();
	if ( empty( $attributes ) ) {
		return array();
	}

	// Search for attributes with 'pack', 'size', or 'weight' in their names
	foreach ( $attributes as $attr_name => $attr_obj ) {
		$lower_name = strtolower( $attr_name );
		if ( false !== strpos( $lower_name, 'pack' ) || false !== strpos( $lower_name, 'size' ) || false !== strpos( $lower_name, 'weight' ) ) {
			if ( is_a( $attr_obj, 'WC_Product_Attribute' ) ) {
				$options = $attr_obj->get_options();
				if ( is_array( $options ) && ! empty( $options ) ) {
					// Check if taxonomy based or custom text options
					if ( $attr_obj->is_taxonomy() ) {
						$terms = wc_get_product_terms( $product->get_id(), $attr_obj->get_name(), array( 'fields' => 'names' ) );
						return array_map( 'sanitize_text_field', $terms );
					}
					return array_map( 'sanitize_text_field', $options );
				}
			}
		}
	}

	return array();
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
 *
 * @param WC_Product $product WooCommerce Product object.
 * @return array Associative array of [ 'Label' => 'Value' ].
 */
function spicecraft_get_product_specs( $product ) {
	if ( ! $product instanceof WC_Product ) {
		return array();
	}

	$specs = array();

	// Check SKU
	$sku = $product->get_sku();
	if ( ! empty( $sku ) ) {
		$specs[ __( 'Product SKU', 'spicecraft' ) ] = $sku;
	}

	// Check Primary Category
	$primary_cat = spicecraft_get_product_primary_category( $product->get_id() );
	if ( ! empty( $primary_cat ) ) {
		$specs[ __( 'Category', 'spicecraft' ) ] = $primary_cat;
	}

	// Attributes (Origin, Grade, Shelf Life, Form, Processing, Packaging, etc.)
	$attributes = $product->get_attributes();
	if ( ! empty( $attributes ) ) {
		foreach ( $attributes as $attr_name => $attr_obj ) {
			$lower = strtolower( $attr_name );
			// Skip pack size as it has its own prominent selector
			if ( false !== strpos( $lower, 'pack' ) || false !== strpos( $lower, 'size' ) || false !== strpos( $lower, 'weight' ) ) {
				continue;
			}
			if ( is_a( $attr_obj, 'WC_Product_Attribute' ) ) {
				$label = wc_attribute_label( $attr_obj->get_name() );
				if ( $attr_obj->is_taxonomy() ) {
					$terms = wc_get_product_terms( $product->get_id(), $attr_obj->get_name(), array( 'fields' => 'names' ) );
					if ( ! empty( $terms ) ) {
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

	// Dynamic custom meta specifications if entered
	$origin = get_post_meta( $product->get_id(), '_spicecraft_origin', true );
	if ( ! empty( $origin ) && ! isset( $specs[ __( 'Origin', 'spicecraft' ) ] ) ) {
		$specs[ __( 'Origin', 'spicecraft' ) ] = sanitize_text_field( $origin );
	}

	$shelf_life = get_post_meta( $product->get_id(), '_spicecraft_shelf_life', true );
	if ( ! empty( $shelf_life ) && ! isset( $specs[ __( 'Shelf Life', 'spicecraft' ) ] ) ) {
		$specs[ __( 'Shelf Life', 'spicecraft' ) ] = sanitize_text_field( $shelf_life );
	}

	$processing = get_post_meta( $product->get_id(), '_spicecraft_processing', true );
	if ( ! empty( $processing ) && ! isset( $specs[ __( 'Processing Method', 'spicecraft' ) ] ) ) {
		$specs[ __( 'Processing Method', 'spicecraft' ) ] = sanitize_text_field( $processing );
	}

	$packaging = get_post_meta( $product->get_id(), '_spicecraft_packaging', true );
	if ( ! empty( $packaging ) && ! isset( $specs[ __( 'Packaging Options', 'spicecraft' ) ] ) ) {
		$specs[ __( 'Packaging Options', 'spicecraft' ) ] = sanitize_text_field( $packaging );
	}

	return $specs;
}
