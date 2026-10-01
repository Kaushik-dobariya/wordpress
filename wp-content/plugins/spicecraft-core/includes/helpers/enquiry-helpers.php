<?php
/**
 * SpiceCraft Core - Enquiry & Lead Management Helper Functions
 *
 * Provides status definitions, enquiry types, dynamic recipient resolution,
 * and query helpers for the Product Enquiry & Lead Management CMS.
 *
 * @package SpiceCraft_Core
 * @since 1.4.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Retrieve all supported enquiry / lead statuses with display labels and colors.
 *
 * @return array
 */
function spicecraft_get_enquiry_statuses() {
	return array(
		'new'             => array(
			'label' => __( 'New', 'spicecraft-core' ),
			'bg'    => '#dbeafe', // Blue
			'color' => '#1e40af',
		),
		'contacted'       => array(
			'label' => __( 'Contacted', 'spicecraft-core' ),
			'bg'    => '#fef3c7', // Amber
			'color' => '#92400e',
		),
		'qualified'       => array(
			'label' => __( 'Qualified', 'spicecraft-core' ),
			'bg'    => '#fef9c3', // Light Yellow
			'color' => '#854d0e',
		),
		'in_discussion'   => array(
			'label' => __( 'In Discussion', 'spicecraft-core' ),
			'bg'    => '#ede9fe', // Purple
			'color' => '#5b21b6',
		),
		'quotation_sent'  => array(
			'label' => __( 'Quotation Sent', 'spicecraft-core' ),
			'bg'    => '#e0f2fe', // Sky
			'color' => '#0369a1',
		),
		'converted'       => array(
			'label' => __( 'Converted', 'spicecraft-core' ),
			'bg'    => '#dcfce7', // Green
			'color' => '#15803d',
		),
		'closed'          => array(
			'label' => __( 'Closed', 'spicecraft-core' ),
			'bg'    => '#f3f4f6', // Gray
			'color' => '#4b5563',
		),
	);
}

/**
 * Retrieve all supported customer / buyer types.
 *
 * @return array
 */
function spicecraft_get_customer_types() {
	return array(
		'retailer'               => __( 'Retailer', 'spicecraft-core' ),
		'distributor'            => __( 'Distributor', 'spicecraft-core' ),
		'wholesaler'             => __( 'Wholesaler', 'spicecraft-core' ),
		'importer'               => __( 'Importer', 'spicecraft-core' ),
		'exporter'               => __( 'Exporter', 'spicecraft-core' ),
		'food_manufacturer'      => __( 'Food Manufacturer', 'spicecraft-core' ),
		'restaurant_hospitality' => __( 'Restaurant / Hospitality', 'spicecraft-core' ),
		'other'                  => __( 'Other', 'spicecraft-core' ),
	);
}

/**
 * Retrieve all supported enquiry types.
 *
 * @return array
 */
function spicecraft_get_enquiry_types() {
	return array(
		'product'     => __( 'Product Enquiry', 'spicecraft-core' ),
		'bulk'        => __( 'Bulk / Wholesale Sourcing', 'spicecraft-core' ),
		'export'      => __( 'Export & International Trade', 'spicecraft-core' ),
		'private'     => __( 'Private Label & Custom Blending', 'spicecraft-core' ),
		'partnership' => __( 'Partnership / Distribution', 'spicecraft-core' ),
		'general'     => __( 'General Business Enquiry', 'spicecraft-core' ),
	);
}

/**
 * Retrieve the current configured receiving email address for enquiries.
 *
 * Dynamically resolves from Global Settings.
 * Fallback cascade:
 * 1. Global Setting: enquiry_receiving_email
 * 2. Global Setting: email_sales
 * 3. Global Setting: email_export
 * 4. Global Setting: email_general
 * 5. WordPress admin_email
 *
 * @return string
 */
function spicecraft_get_enquiry_receiving_email() {
	if ( function_exists( 'spicecraft_get_setting' ) ) {
		$custom = spicecraft_get_setting( 'enquiry_receiving_email', '' );
		if ( ! empty( $custom ) && is_email( $custom ) ) {
			return sanitize_email( $custom );
		}

		$sales = spicecraft_get_setting( 'email_sales', '' );
		if ( ! empty( $sales ) && is_email( $sales ) ) {
			return sanitize_email( $sales );
		}

		$export = spicecraft_get_setting( 'email_export', '' );
		if ( ! empty( $export ) && is_email( $export ) ) {
			return sanitize_email( $export );
		}

		$general = spicecraft_get_setting( 'email_general', '' );
		if ( ! empty( $general ) && is_email( $general ) ) {
			return sanitize_email( $general );
		}
	}

	return sanitize_email( get_option( 'admin_email' ) );
}

/**
 * Count the number of unhandled 'new' enquiries for admin menu badges.
 *
 * @return int
 */
function spicecraft_count_new_enquiries() {
	$query = new WP_Query(
		array(
			'post_type'      => 'spicecraft_enquiry',
			'post_status'    => 'publish',
			'posts_per_page' => 1,
			'fields'         => 'ids',
			'meta_query'     => array(
				array(
					'key'     => '_sc_enquiry_status',
					'value'   => 'new',
					'compare' => '=',
				),
			),
		)
	);

	return absint( $query->found_posts );
}
