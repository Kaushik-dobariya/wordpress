<?php
/**
 * SpiceCraft Core - Certification Helpers & Business Logic
 *
 * Centralized API for retrieving, querying, sanitizing, and validating
 * statutory food safety and quality certifications from the spicecraft_certification taxonomy.
 *
 * Enforces:
 * - Controlled status evaluation & date-aware automatic expiry
 * - Strict public vs. internal visibility guards
 * - Secure document visibility guards (private PDFs never exposed on frontend)
 * - Public detail page toggles for thin records
 * - Product & category relationship mappings
 * - Zero-Fabricated-Content rule
 *
 * @package SpiceCraft_Core
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * 1. GLOBAL CERTIFICATION SETTINGS SCHEMA & GETTERS
 */

/**
 * Get default settings for Global Certification Display Settings.
 *
 * @return array
 */
function spicecraft_get_certification_default_settings() {
	return array(
		'archive_enabled'               => 1,
		'heading'                       => __( 'Official Standards & Accreditations', 'spicecraft-core' ),
		'eyebrow'                       => __( 'Verified Credentials', 'spicecraft-core' ),
		'intro'                         => __( 'Authentic certifications, statutory food safety licenses, and empirical quality compliance documentation.', 'spicecraft-core' ),
		'desktop_image_id'              => 0,
		'mobile_image_id'               => 0,
		'show_filters'                  => 1,
		'show_status'                   => 1,
		'show_authority'                => 1,
		'show_cert_number'              => 1,
		'show_validity'                 => 1,
		'show_verification_link'        => 1,
		'show_documents'                => 1,
		'show_related_products'         => 1,
		'show_related_categories'       => 1,
		'show_expired'                  => 0,
		'show_archived'                 => 0,
		'default_sort'                  => 'order', // order, title_asc, issue_date_desc, expiry_date_asc
		'auto_expiry_detection'         => 0,       // Default: No (Must not assume expiry behavior unless enabled)
		'expiry_warning_threshold_days' => 90,
		'cta_enable'                    => 1,
		'cta_heading'                   => __( 'Request Verified Certification Dossier', 'spicecraft-core' ),
		'cta_description'               => __( 'Need formal Certificate of Analysis (COA) batches, technical specification sheets, or audited plant compliance documentation? Contact our technical team.', 'spicecraft-core' ),
		'cta_whatsapp_enable'           => 1,
		'cta_email_enable'              => 1,
	);
}

/**
 * Retrieve Global Certification Settings from wp_options.
 *
 * @return array
 */
function spicecraft_get_certification_settings() {
	$defaults = spicecraft_get_certification_default_settings();
	$stored   = get_option( 'spicecraft_certification_settings', array() );

	if ( ! is_array( $stored ) ) {
		$stored = array();
	}

	return wp_parse_args( $stored, $defaults );
}

/**
 * Update Global Certification Settings in wp_options.
 *
 * @param array $settings
 * @return bool
 */
function spicecraft_update_certification_settings( $settings ) {
	$defaults = spicecraft_get_certification_default_settings();
	$sanitized = array();

	$sanitized['archive_enabled']               = ! empty( $settings['archive_enabled'] ) ? 1 : 0;
	$sanitized['heading']                       = sanitize_text_field( $settings['heading'] ?? $defaults['heading'] );
	$sanitized['eyebrow']                       = sanitize_text_field( $settings['eyebrow'] ?? $defaults['eyebrow'] );
	$sanitized['intro']                         = wp_kses_post( $settings['intro'] ?? $defaults['intro'] );
	$sanitized['desktop_image_id']              = absint( $settings['desktop_image_id'] ?? 0 );
	$sanitized['mobile_image_id']               = absint( $settings['mobile_image_id'] ?? 0 );
	$sanitized['show_filters']                  = ! empty( $settings['show_filters'] ) ? 1 : 0;
	$sanitized['show_status']                   = ! empty( $settings['show_status'] ) ? 1 : 0;
	$sanitized['show_authority']                = ! empty( $settings['show_authority'] ) ? 1 : 0;
	$sanitized['show_cert_number']              = ! empty( $settings['show_cert_number'] ) ? 1 : 0;
	$sanitized['show_validity']                 = ! empty( $settings['show_validity'] ) ? 1 : 0;
	$sanitized['show_verification_link']        = ! empty( $settings['show_verification_link'] ) ? 1 : 0;
	$sanitized['show_documents']                = ! empty( $settings['show_documents'] ) ? 1 : 0;
	$sanitized['show_related_products']         = ! empty( $settings['show_related_products'] ) ? 1 : 0;
	$sanitized['show_related_categories']       = ! empty( $settings['show_related_categories'] ) ? 1 : 0;
	$sanitized['show_expired']                  = ! empty( $settings['show_expired'] ) ? 1 : 0;
	$sanitized['show_archived']                 = ! empty( $settings['show_archived'] ) ? 1 : 0;

	$allowed_sorts = array( 'order', 'title_asc', 'issue_date_desc', 'expiry_date_asc' );
	$sanitized['default_sort'] = in_array( $settings['default_sort'] ?? 'order', $allowed_sorts, true )
		? $settings['default_sort']
		: 'order';

	$sanitized['auto_expiry_detection']         = ! empty( $settings['auto_expiry_detection'] ) ? 1 : 0;
	$sanitized['expiry_warning_threshold_days'] = absint( $settings['expiry_warning_threshold_days'] ?? 90 );
	if ( $sanitized['expiry_warning_threshold_days'] < 1 ) {
		$sanitized['expiry_warning_threshold_days'] = 90;
	}

	$sanitized['cta_enable']          = ! empty( $settings['cta_enable'] ) ? 1 : 0;
	$sanitized['cta_heading']         = sanitize_text_field( $settings['cta_heading'] ?? $defaults['cta_heading'] );
	$sanitized['cta_description']     = sanitize_textarea_field( $settings['cta_description'] ?? $defaults['cta_description'] );
	$sanitized['cta_whatsapp_enable'] = ! empty( $settings['cta_whatsapp_enable'] ) ? 1 : 0;
	$sanitized['cta_email_enable']    = ! empty( $settings['cta_email_enable'] ) ? 1 : 0;

	return update_option( 'spicecraft_certification_settings', $sanitized );
}

/**
 * 2. TERM METADATA RETRIEVAL & NORMALIZATION
 */

/**
 * Retrieve all structured metadata for a single certification term.
 *
 * @param int|WP_Term $term Term ID or WP_Term object.
 * @return array Normalized metadata dictionary.
 */
function spicecraft_get_certification_meta( $term ) {
	$term_id = is_object( $term ) ? $term->term_id : absint( $term );
	if ( ! $term_id ) {
		return array();
	}

	// Related products: array of product IDs
	$related_products = get_term_meta( $term_id, '_sc_cert_related_products', true );
	if ( ! is_array( $related_products ) ) {
		$related_products = ! empty( $related_products ) ? array_map( 'absint', explode( ',', $related_products ) ) : array();
	} else {
		$related_products = array_map( 'absint', $related_products );
	}

	// Related categories: array of product_cat IDs
	$related_categories = get_term_meta( $term_id, '_sc_cert_related_categories', true );
	if ( ! is_array( $related_categories ) ) {
		$related_categories = ! empty( $related_categories ) ? array_map( 'absint', explode( ',', $related_categories ) ) : array();
	} else {
		$related_categories = array_map( 'absint', $related_categories );
	}

	$public_detail_raw = get_term_meta( $term_id, '_sc_cert_public_detail', true );
	$public_detail     = ( '' === $public_detail_raw || '1' === (string) $public_detail_raw || true === $public_detail_raw ) ? 1 : 0;
	$cert_num          = (string) get_term_meta( $term_id, '_sc_cert_number', true );
	$authority         = (string) ( get_term_meta( $term_id, '_sc_cert_issuing_authority', true ) ?: get_term_meta( $term_id, '_sc_cert_authority', true ) );

	return array(
		'short_name'         => (string) get_term_meta( $term_id, '_sc_cert_short_name', true ),
		'number'             => $cert_num,
		'certificate_number' => $cert_num,
		'issuing_authority'  => $authority,
		'accreditation_body' => (string) get_term_meta( $term_id, '_sc_cert_accreditation_body', true ),
		'issue_date'         => (string) get_term_meta( $term_id, '_sc_cert_issue_date', true ),
		'valid_from'         => (string) get_term_meta( $term_id, '_sc_cert_valid_from', true ),
		'expiry_date'        => (string) get_term_meta( $term_id, '_sc_cert_expiry_date', true ),
		'status'             => (string) ( get_term_meta( $term_id, '_sc_cert_status', true ) ?: 'not_disclosed' ),
		'auto_expiry'        => ! empty( get_term_meta( $term_id, '_sc_cert_auto_expiry', true ) ) ? 1 : 0,
		'scope'              => (string) get_term_meta( $term_id, '_sc_cert_scope', true ),
		'facility_scope'     => (string) get_term_meta( $term_id, '_sc_cert_facility_scope', true ),
		'logo_id'            => absint( get_term_meta( $term_id, '_sc_cert_logo_id', true ) ),
		'image_id'           => absint( get_term_meta( $term_id, '_sc_cert_image_id', true ) ),
		'doc_id'             => absint( get_term_meta( $term_id, '_sc_cert_doc_id', true ) ),
		'verification_url'   => (string) get_term_meta( $term_id, '_sc_cert_verification_url', true ),
		'related_products'   => array_filter( $related_products ),
		'related_categories' => array_filter( $related_categories ),
		'visibility'         => (string) ( get_term_meta( $term_id, '_sc_cert_visibility', true ) ?: 'public' ),
		'doc_visibility'     => (string) ( get_term_meta( $term_id, '_sc_cert_doc_visibility', true ) ?: 'private' ),
		'public_detail'      => $public_detail,
		'featured'           => ! empty( get_term_meta( $term_id, '_sc_cert_featured', true ) ) ? 1 : 0,
		'order'              => (int) ( get_term_meta( $term_id, '_sc_cert_order', true ) ?: 10 ),
		'notes'              => (string) get_term_meta( $term_id, '_sc_cert_notes', true ),
	);
}

/**
 * Update certification term metadata programmatically.
 *
 * @param int   $term_id
 * @param array $meta
 * @return bool
 */
function spicecraft_update_certification_meta( $term_id, $meta ) {
	$term_id = absint( $term_id );
	if ( ! $term_id || ! is_array( $meta ) ) {
		return false;
	}

	if ( isset( $meta['short_name'] ) ) {
		update_term_meta( $term_id, '_sc_cert_short_name', sanitize_text_field( $meta['short_name'] ) );
	}
	if ( isset( $meta['certificate_number'] ) || isset( $meta['number'] ) ) {
		$num = $meta['certificate_number'] ?? $meta['number'];
		update_term_meta( $term_id, '_sc_cert_number', sanitize_text_field( $num ) );
	}
	if ( isset( $meta['issuing_authority'] ) || isset( $meta['authority'] ) ) {
		$auth = $meta['issuing_authority'] ?? $meta['authority'];
		update_term_meta( $term_id, '_sc_cert_issuing_authority', sanitize_text_field( $auth ) );
	}
	if ( isset( $meta['accreditation_body'] ) ) {
		update_term_meta( $term_id, '_sc_cert_accreditation_body', sanitize_text_field( $meta['accreditation_body'] ) );
	}
	if ( isset( $meta['issue_date'] ) ) {
		update_term_meta( $term_id, '_sc_cert_issue_date', sanitize_text_field( $meta['issue_date'] ) );
	}
	if ( isset( $meta['valid_from'] ) ) {
		update_term_meta( $term_id, '_sc_cert_valid_from', sanitize_text_field( $meta['valid_from'] ) );
	}
	if ( isset( $meta['expiry_date'] ) ) {
		update_term_meta( $term_id, '_sc_cert_expiry_date', sanitize_text_field( $meta['expiry_date'] ) );
	}
	if ( isset( $meta['status'] ) ) {
		update_term_meta( $term_id, '_sc_cert_status', sanitize_text_field( $meta['status'] ) );
	}
	if ( isset( $meta['auto_expiry'] ) ) {
		update_term_meta( $term_id, '_sc_cert_auto_expiry', ! empty( $meta['auto_expiry'] ) ? 1 : 0 );
	}
	if ( isset( $meta['scope'] ) ) {
		update_term_meta( $term_id, '_sc_cert_scope', sanitize_textarea_field( $meta['scope'] ) );
	}
	if ( isset( $meta['facility_scope'] ) ) {
		update_term_meta( $term_id, '_sc_cert_facility_scope', sanitize_text_field( $meta['facility_scope'] ) );
	}
	if ( isset( $meta['notes'] ) ) {
		update_term_meta( $term_id, '_sc_cert_notes', sanitize_textarea_field( $meta['notes'] ) );
	}
	if ( isset( $meta['logo_id'] ) ) {
		update_term_meta( $term_id, '_sc_cert_logo_id', absint( $meta['logo_id'] ) );
	}
	if ( isset( $meta['image_id'] ) ) {
		update_term_meta( $term_id, '_sc_cert_image_id', absint( $meta['image_id'] ) );
	}
	if ( isset( $meta['doc_id'] ) ) {
		update_term_meta( $term_id, '_sc_cert_doc_id', absint( $meta['doc_id'] ) );
	}
	if ( isset( $meta['doc_visibility'] ) ) {
		update_term_meta( $term_id, '_sc_cert_doc_visibility', sanitize_text_field( $meta['doc_visibility'] ) );
	}
	if ( isset( $meta['verification_url'] ) ) {
		update_term_meta( $term_id, '_sc_cert_verification_url', esc_url_raw( $meta['verification_url'] ) );
	}
	if ( isset( $meta['visibility'] ) ) {
		update_term_meta( $term_id, '_sc_cert_visibility', sanitize_text_field( $meta['visibility'] ) );
	}
	if ( isset( $meta['featured'] ) ) {
		update_term_meta( $term_id, '_sc_cert_featured', ! empty( $meta['featured'] ) ? 1 : 0 );
	}
	if ( isset( $meta['order'] ) ) {
		update_term_meta( $term_id, '_sc_cert_order', (int) $meta['order'] );
	}
	if ( isset( $meta['public_detail'] ) ) {
		update_term_meta( $term_id, '_sc_cert_public_detail', ! empty( $meta['public_detail'] ) ? 1 : 0 );
	}
	if ( isset( $meta['related_products'] ) ) {
		update_term_meta( $term_id, '_sc_cert_related_products', array_filter( array_map( 'absint', (array) $meta['related_products'] ) ) );
	}
	if ( isset( $meta['related_categories'] ) ) {
		update_term_meta( $term_id, '_sc_cert_related_categories', array_filter( array_map( 'absint', (array) $meta['related_categories'] ) ) );
	}

	return true;
}

/**
 * Alias for spicecraft_update_certification_meta.
 *
 * @param int   $term_id
 * @param array $meta
 * @return bool
 */
function spicecraft_save_certification_meta( $term_id, $meta ) {
	return spicecraft_update_certification_meta( $term_id, $meta );
}


/**
 * 3. STATUS & DATE-AWARE EXPIRY ENGINE
 */

/**
 * Determine the effective certification status taking into account
 * explicit status configuration, expiry date, and automatic expiry detection setting.
 *
 * Controlled statuses:
 * - 'active'
 * - 'expired'
 * - 'pending_renewal'
 * - 'suspended'
 * - 'archived'
 * - 'not_disclosed'
 *
 * CRITICAL RULE:
 * Never infer 'active' simply because an expiry date is in the future.
 * 'active' must be explicitly selected by the administrator.
 *
 * @param int $term_id
 * @return string
 */
function spicecraft_get_certification_effective_status( $term_id ) {
	$meta   = spicecraft_get_certification_meta( $term_id );
	$status = ! empty( $meta['status'] ) ? $meta['status'] : 'not_disclosed';

	$global_settings = spicecraft_get_certification_settings();
	$auto_expiry     = ! empty( $meta['auto_expiry'] ) || ! empty( $global_settings['auto_expiry_detection'] );

	if ( $auto_expiry && ! empty( $meta['expiry_date'] ) ) {
		$expiry_ts = strtotime( $meta['expiry_date'] . ' 23:59:59' );
		if ( $expiry_ts && time() > $expiry_ts ) {
			return 'expired';
		}
	}

	return $status;
}

/**
 * Get human-readable label for a certification status.
 *
 * @param string $status
 * @return string
 */
function spicecraft_get_certification_status_label( $status ) {
	$labels = array(
		'active'          => __( 'Active / Valid', 'spicecraft-core' ),
		'expired'         => __( 'Expired', 'spicecraft-core' ),
		'pending_renewal' => __( 'Pending Renewal', 'spicecraft-core' ),
		'suspended'       => __( 'Suspended', 'spicecraft-core' ),
		'archived'        => __( 'Archived', 'spicecraft-core' ),
		'not_disclosed'   => __( 'Status on Request', 'spicecraft-core' ),
	);

	return isset( $labels[ $status ] ) ? $labels[ $status ] : __( 'Status on Request', 'spicecraft-core' );
}

/**
 * 4. VISIBILITY & SECURITY GUARDS
 */

/**
 * Check if a certification record is publicly viewable.
 * Only public records with permitted statuses appear on the frontend.
 *
 * @param int $term_id
 * @return bool
 */
function spicecraft_is_certification_public( $term_id ) {
	$meta = spicecraft_get_certification_meta( $term_id );

	// 1. Explicit internal check
	if ( 'internal' === $meta['visibility'] ) {
		return false;
	}

	// 2. Status check against display settings
	$effective_status = spicecraft_get_certification_effective_status( $term_id );
	$global_settings  = spicecraft_get_certification_settings();

	if ( 'expired' === $effective_status && empty( $global_settings['show_expired'] ) ) {
		return false;
	}

	if ( 'archived' === $effective_status && empty( $global_settings['show_archived'] ) ) {
		return false;
	}

	return true;
}

/**
 * Get the public document URL for a certification.
 * If Certificate Document Visibility is Private, this returns empty string,
 * ensuring zero document URL or attachment reference is leaked to frontend HTML.
 *
 * @param int $term_id
 * @return string Safe attachment URL or empty string.
 */
function spicecraft_get_certification_public_document_url( $term_id ) {
	$meta = spicecraft_get_certification_meta( $term_id );

	// Document visibility must be explicitly 'public'
	if ( 'public' !== $meta['doc_visibility'] ) {
		return '';
	}

	if ( empty( $meta['doc_id'] ) ) {
		return '';
	}

	$url = wp_get_attachment_url( $meta['doc_id'] );
	return $url ? esc_url( $url ) : '';
}

/**
 * Check if a certification has a dedicated public detail page enabled.
 * Used for "thin" records (e.g. name + logo only) to remain archive-only.
 *
 * @param int $term_id
 * @return bool
 */
function spicecraft_has_certification_public_detail( $term_id ) {
	$meta = spicecraft_get_certification_meta( $term_id );
	return ! empty( $meta['public_detail'] );
}

/**
 * 5. ADMIN MANAGEMENT & EXPIRY ALERTS
 */

/**
 * Calculate admin-side expiry alert for a certification.
 *
 * @param int $term_id
 * @return array|null Null if no alert, or array('type' => 'expired'|'expiring_soon', 'days' => int, 'label' => string)
 */
function spicecraft_get_certification_expiry_alert( $term_id ) {
	$meta = spicecraft_get_certification_meta( $term_id );
	if ( empty( $meta['expiry_date'] ) ) {
		return null;
	}

	$expiry_ts = strtotime( $meta['expiry_date'] . ' 23:59:59' );
	if ( ! $expiry_ts ) {
		return null;
	}

	$diff_seconds = $expiry_ts - time();
	$diff_days    = (int) floor( $diff_seconds / DAY_IN_SECONDS );

	$global_settings = spicecraft_get_certification_settings();
	$threshold       = ! empty( $global_settings['expiry_warning_threshold_days'] ) ? absint( $global_settings['expiry_warning_threshold_days'] ) : 90;

	if ( $diff_days < 0 ) {
		return array(
			'type'  => 'expired',
			'days'  => abs( $diff_days ),
			'label' => sprintf( __( 'Expired %d days ago', 'spicecraft-core' ), abs( $diff_days ) ),
		);
	} elseif ( $diff_days <= $threshold ) {
		return array(
			'type'  => 'expiring_soon',
			'days'  => $diff_days,
			'label' => sprintf( __( 'Expiring in %d days', 'spicecraft-core' ), $diff_days ),
		);
	}

	return null;
}

/**
 * 6. FRONTEND QUERY HELPERS
 */

/**
 * Retrieve public certifications matching optional filters.
 *
 * @param array $args Filter arguments:
 *                    - 'status' (string): specific status filter
 *                    - 'category_id' (int): filter by related category
 *                    - 'featured' (bool): filter by featured flag
 *                    - 'orderby' (string): 'order', 'title_asc', 'issue_date_desc', 'expiry_date_asc'
 *                    - 'include' (array): specific term IDs
 *                    - 'number' (int): limit count
 * @return array Array of WP_Term objects.
 */
function spicecraft_get_public_certifications( $args = array() ) {
	$defaults = array(
		'status'      => '',
		'category_id' => 0,
		'featured'    => false,
		'orderby'     => '',
		'include'     => array(),
		'number'      => 0,
	);
	$args = wp_parse_args( $args, $defaults );

	$term_args = array(
		'taxonomy'   => 'spicecraft_certification',
		'hide_empty' => false,
	);

	if ( ! empty( $args['include'] ) && is_array( $args['include'] ) ) {
		$term_args['include'] = array_map( 'absint', $args['include'] );
	}

	$all_terms = get_terms( $term_args );
	if ( empty( $all_terms ) || is_wp_error( $all_terms ) ) {
		return array();
	}

	$public_terms = array();
	foreach ( $all_terms as $term ) {
		if ( ! spicecraft_is_certification_public( $term->term_id ) ) {
			continue;
		}

		$meta = spicecraft_get_certification_meta( $term->term_id );

		// Status filter
		if ( ! empty( $args['status'] ) ) {
			$effective_status = spicecraft_get_certification_effective_status( $term->term_id );
			if ( $effective_status !== $args['status'] ) {
				continue;
			}
		}

		// Category filter
		if ( ! empty( $args['category_id'] ) ) {
			$cat_id = absint( $args['category_id'] );
			if ( empty( $meta['related_categories'] ) || ! in_array( $cat_id, $meta['related_categories'], true ) ) {
				continue;
			}
		}

		// Featured filter
		if ( ! empty( $args['featured'] ) && empty( $meta['featured'] ) ) {
			continue;
		}

		$public_terms[] = $term;
	}

	// Sorting
	$sort = ! empty( $args['orderby'] ) ? $args['orderby'] : spicecraft_get_certification_settings()['default_sort'];

	usort( $public_terms, function( $a, $b ) use ( $sort ) {
		$meta_a = spicecraft_get_certification_meta( $a->term_id );
		$meta_b = spicecraft_get_certification_meta( $b->term_id );

		switch ( $sort ) {
			case 'title_asc':
				return strcasecmp( $a->name, $b->name );

			case 'issue_date_desc':
				$time_a = ! empty( $meta_a['issue_date'] ) ? strtotime( $meta_a['issue_date'] ) : 0;
				$time_b = ! empty( $meta_b['issue_date'] ) ? strtotime( $meta_b['issue_date'] ) : 0;
				return $time_b <=> $time_a;

			case 'expiry_date_asc':
				$time_a = ! empty( $meta_a['expiry_date'] ) ? strtotime( $meta_a['expiry_date'] ) : PHP_INT_MAX;
				$time_b = ! empty( $meta_b['expiry_date'] ) ? strtotime( $meta_b['expiry_date'] ) : PHP_INT_MAX;
				return $time_a <=> $time_b;

			case 'order':
			default:
				$order_a = isset( $meta_a['order'] ) ? (int) $meta_a['order'] : 10;
				$order_b = isset( $meta_b['order'] ) ? (int) $meta_b['order'] : 10;
				if ( $order_a === $order_b ) {
					return strcasecmp( $a->name, $b->name );
				}
				return $order_a <=> $order_b;
		}
	} );

	if ( ! empty( $args['number'] ) && count( $public_terms ) > $args['number'] ) {
		$public_terms = array_slice( $public_terms, 0, absint( $args['number'] ) );
	}

	return $public_terms;
}

/**
 * Retrieve public certifications explicitly related to a given product.
 *
 * Evaluates:
 * 1. Terms directly assigned to the product post.
 * 2. Certifications whose '_sc_cert_related_products' explicitly contains this product ID.
 *
 * CRITICAL RULE:
 * Does NOT assume a global certification applies to every product.
 * Only returns explicitly linked records that are also public.
 *
 * @param int $product_id
 * @return array Array of WP_Term objects.
 */
function spicecraft_get_product_public_certifications( $product_id ) {
	$product_id = absint( $product_id );
	if ( ! $product_id ) {
		return array();
	}

	$matched_term_ids = array();

	// 1. Directly assigned taxonomy terms
	$direct_terms = get_the_terms( $product_id, 'spicecraft_certification' );
	if ( ! empty( $direct_terms ) && ! is_wp_error( $direct_terms ) ) {
		foreach ( $direct_terms as $dt ) {
			$matched_term_ids[] = $dt->term_id;
		}
	}

	// 2. Certifications referencing this product in term meta
	$all_terms = get_terms( array(
		'taxonomy'   => 'spicecraft_certification',
		'hide_empty' => false,
	) );

	if ( ! empty( $all_terms ) && ! is_wp_error( $all_terms ) ) {
		foreach ( $all_terms as $term ) {
			$meta = spicecraft_get_certification_meta( $term->term_id );
			if ( ! empty( $meta['related_products'] ) && in_array( $product_id, $meta['related_products'], true ) ) {
				$matched_term_ids[] = $term->term_id;
			}
		}
	}

	$matched_term_ids = array_unique( $matched_term_ids );
	if ( empty( $matched_term_ids ) ) {
		return array();
	}

	return spicecraft_get_public_certifications( array(
		'include' => $matched_term_ids,
	) );
}

/**
 * Retrieve WooCommerce products related to a specific certification.
 *
 * @param int $term_id
 * @param int $limit
 * @return array Array of WC_Product or WP_Post objects.
 */
function spicecraft_get_certification_related_products( $term_id, $limit = 4 ) {
	$term_id = absint( $term_id );
	if ( ! $term_id ) {
		return array();
	}

	$meta = spicecraft_get_certification_meta( $term_id );

	// 1. Explicitly selected product IDs
	$selected_ids = ! empty( $meta['related_products'] ) ? $meta['related_products'] : array();

	// 2. Query products by taxonomy term
	$tax_query = array(
		array(
			'taxonomy' => 'spicecraft_certification',
			'field'    => 'term_id',
			'terms'    => $term_id,
		),
	);

	$query_args = array(
		'post_type'      => 'product',
		'post_status'    => 'publish',
		'posts_per_page' => $limit,
		'fields'         => 'ids',
	);

	if ( ! empty( $selected_ids ) ) {
		$query_args['post__in'] = $selected_ids;
	} else {
		$query_args['tax_query'] = $tax_query;
	}

	$product_ids = get_posts( $query_args );

	if ( empty( $product_ids ) && ! empty( $selected_ids ) ) {
		// Fallback if tax_query had zero but selected_ids were specified
		$product_ids = get_posts( array(
			'post_type'      => 'product',
			'post_status'    => 'publish',
			'posts_per_page' => $limit,
			'post__in'       => $selected_ids,
			'fields'         => 'ids',
		) );
	}

	return ! empty( $product_ids ) ? $product_ids : array();
}

/**
 * 7. ADMIN UI HELPERS
 */

/**
 * Render an administrative media uploader component supporting images and PDFs.
 *
 * @param string $input_name    Form input name attribute.
 * @param int    $attachment_id Current attachment ID.
 * @param string $button_text   Button label.
 * @param string $description   Optional descriptive explanation.
 * @param string $mime_type     Optional mime type filter.
 */
function spicecraft_render_media_uploader( $input_name, $attachment_id = 0, $button_text = '', $description = '', $mime_type = '' ) {
	$attachment_id = absint( $attachment_id );
	$button_text   = ! empty( $button_text ) ? $button_text : __( 'Select File', 'spicecraft-core' );
	$unique_id     = 'sc_media_' . md5( $input_name );
	$is_image      = $attachment_id ? wp_attachment_is_image( $attachment_id ) : false;
	$file_url      = $attachment_id ? wp_get_attachment_url( $attachment_id ) : '';
	$file_title    = $attachment_id ? get_the_title( $attachment_id ) : '';
	$file_name     = $attachment_id ? basename( get_attached_file( $attachment_id ) ) : '';
	?>
	<div class="sc-media-uploader-wrap" id="<?php echo esc_attr( $unique_id ); ?>">
		<div class="sc-media-preview-box" style="margin-bottom: 8px; max-width: 240px; min-height: 80px; background: #f0f0f1; border: 1px dashed #c3c4c7; border-radius: 4px; display: flex; align-items: center; justify-content: center; overflow: hidden; padding: 6px; text-align: center;">
			<?php if ( $attachment_id && $is_image ) : ?>
				<img src="<?php echo esc_url( wp_get_attachment_image_url( $attachment_id, 'medium' ) ?: $file_url ); ?>" alt="" style="max-width: 100%; height: auto; display: block; border-radius: 2px;" />
			<?php elseif ( $attachment_id ) : ?>
				<div style="display: flex; align-items: center; gap: 8px; text-align: left; padding: 4px;">
					<span class="dashicons dashicons-media-document" style="font-size: 28px; width: 28px; height: 28px; color: #d63638;"></span>
					<div style="overflow: hidden; text-overflow: ellipsis; max-width: 180px;">
						<strong style="display: block; font-size: 12px; color: #2c3338; word-break: break-all;"><?php echo esc_html( $file_name ?: $file_title ); ?></strong>
						<span style="font-size: 11px; color: #646970;"><?php esc_html_e( 'Document Attached', 'spicecraft-core' ); ?></span>
					</div>
				</div>
			<?php else : ?>
				<span style="color: #8c8f94; font-size: 12px;"><?php esc_html_e( 'No file selected', 'spicecraft-core' ); ?></span>
			<?php endif; ?>
		</div>

		<input type="hidden" name="<?php echo esc_attr( $input_name ); ?>" class="sc-media-id-input" value="<?php echo esc_attr( $attachment_id ?: '' ); ?>" />

		<div style="display: flex; gap: 6px; align-items: center;">
			<button type="button" class="button button-secondary sc-media-upload-btn" data-mime="<?php echo esc_attr( $mime_type ); ?>">
				<span class="dashicons dashicons-upload" style="margin-top: -2px;"></span>
				<?php echo esc_html( $button_text ); ?>
			</button>
			<button type="button" class="button sc-media-remove-btn" style="<?php echo empty( $attachment_id ) ? 'display: none;' : ''; ?>">
				<?php esc_html_e( 'Remove', 'spicecraft-core' ); ?>
			</button>
		</div>
		<?php if ( ! empty( $description ) ) : ?>
			<p class="description" style="margin-top: 4px;"><?php echo esc_html( $description ); ?></p>
		<?php endif; ?>
	</div>
	<?php
}

