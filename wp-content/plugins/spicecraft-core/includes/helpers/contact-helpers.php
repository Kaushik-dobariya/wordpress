<?php
/**
 * SpiceCraft Core - Contact Page & Communication Helpers
 *
 * Provides data getters, department resolution, FAQ retrieval,
 * and location map data for the B2B Contact Us page.
 *
 * @package SpiceCraft_Core
 * @since 1.5.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Retrieve Contact Page settings with sensible B2B defaults.
 *
 * @return array
 */
function spicecraft_get_contact_settings() {
	$global_settings = function_exists( 'spicecraft_get_all_settings' ) ? spicecraft_get_all_settings() : array();

	$defaults = array(
		'hero_eyebrow'       => __( 'Direct Manufacturer Trade Desks', 'spicecraft-core' ),
		'hero_title'         => __( 'Connect with SpiceCraft Specialists', 'spicecraft-core' ),
		'hero_subtitle'      => __( 'Connect with our team for product enquiries, bulk requirements, export opportunities, private-label manufacturing and business partnerships.', 'spicecraft-core' ),
		'hero_badge'         => __( 'Response within 24 business hours', 'spicecraft-core' ),
		'map_latitude'       => '10.0159',
		'map_longitude'      => '76.3419',
		'map_zoom'           => '14',
		'map_title'          => __( 'SpiceCraft Corporate & Processing Campus', 'spicecraft-core' ),
		'faq_heading'        => __( 'Frequently Asked Trade Questions', 'spicecraft-core' ),
		'faq_subtitle'       => __( 'Clear answers on minimum orders, international phytosanitary certificates, private label packaging, and sample dispatches.', 'spicecraft-core' ),
		'cta_heading'        => __( 'Looking for Custom Sourcing or Institutional Supply?', 'spicecraft-core' ),
		'cta_subtitle'       => __( 'Our spice technologists and export desk can formulate custom blends, match sieve mesh specs, and provide full laboratory COA documentation.', 'spicecraft-core' ),
		'cta_button_text'    => __( 'Download Product Spec Sheet', 'spicecraft-core' ),
		'cta_button_url'     => home_url( '/shop/' ),
	);

	$contact_settings = array();
	foreach ( $defaults as $key => $default_val ) {
		$contact_settings[ $key ] = isset( $global_settings[ 'contact_' . $key ] ) && '' !== $global_settings[ 'contact_' . $key ]
			? $global_settings[ 'contact_' . $key ]
			: $default_val;
	}

	return $contact_settings;
}

/**
 * Retrieve configured Contact Departments / Categories for B2B routing.
 * Pulls live contact emails and phones directly from Global Settings.
 *
 * @return array
 */
function spicecraft_get_contact_departments() {
	$email_sales   = function_exists( 'spicecraft_get_setting' ) ? spicecraft_get_setting( 'email_sales', '' ) : '';
	$email_export  = function_exists( 'spicecraft_get_setting' ) ? spicecraft_get_setting( 'email_export', '' ) : '';
	$email_general = function_exists( 'spicecraft_get_setting' ) ? spicecraft_get_setting( 'email_general', '' ) : '';
	$email_career  = function_exists( 'spicecraft_get_setting' ) ? spicecraft_get_setting( 'email_career', '' ) : '';
	$phone_primary = function_exists( 'spicecraft_get_setting' ) ? spicecraft_get_setting( 'phone_primary', '' ) : '';
	$phone_factory = function_exists( 'spicecraft_get_setting' ) ? spicecraft_get_setting( 'phone_secondary', '' ) : '';
	$business_hrs  = function_exists( 'spicecraft_get_setting' ) ? spicecraft_get_setting( 'business_hours', '' ) : '';

	// Fallbacks if not configured
	$admin_email   = get_option( 'admin_email' );
	$primary_email = ! empty( $email_sales ) ? $email_sales : ( ! empty( $email_general ) ? $email_general : $admin_email );

	return array(
		'sales' => array(
			'slug'        => 'bulk',
			'title'       => __( 'Bulk Supply & Domestic Sales', 'spicecraft-core' ),
			'description' => __( 'Institutional supply, hotel & restaurant chains, wholesalers, and domestic distributors across India.', 'spicecraft-core' ),
			'email'       => ! empty( $email_sales ) ? $email_sales : $primary_email,
			'phone'       => $phone_primary,
			'icon'        => 'store',
			'badge'       => __( 'Domestic Trade', 'spicecraft-core' ),
		),
		'export' => array(
			'slug'        => 'export',
			'title'       => __( 'Export & International Trade', 'spicecraft-core' ),
			'description' => __( 'Container-load export consignments, international port delivery (FOB/CIF), phytosanitary documentation, and US FDA/EU compliance.', 'spicecraft-core' ),
			'email'       => ! empty( $email_export ) ? $email_export : $primary_email,
			'phone'       => ! empty( $phone_factory ) ? $phone_factory : $phone_primary,
			'icon'        => 'globe',
			'badge'       => __( 'Global Trade', 'spicecraft-core' ),
		),
		'private_label' => array(
			'slug'        => 'private',
			'title'       => __( 'Private Label & OEM Manufacturing', 'spicecraft-core' ),
			'description' => __( 'Custom recipe blending, cryogenic milling, bespoke barrier pouching, and retail jar packaging for supermarket brands.', 'spicecraft-core' ),
			'email'       => ! empty( $email_sales ) ? $email_sales : $primary_email,
			'phone'       => $phone_primary,
			'icon'        => 'awards',
			'badge'       => __( 'Custom OEM', 'spicecraft-core' ),
		),
		'general' => array(
			'slug'        => 'general',
			'title'       => __( 'General Corporate Enquiries', 'spicecraft-core' ),
			'description' => __( 'Vendor registrations, laboratory testing inquiries, statutory disclosures, and administrative matters.', 'spicecraft-core' ),
			'email'       => ! empty( $email_general ) ? $email_general : $admin_email,
			'phone'       => $phone_primary,
			'icon'        => 'email-alt',
			'badge'       => __( 'Corporate Desk', 'spicecraft-core' ),
		),
		'careers' => array(
			'slug'        => 'partnership',
			'title'       => __( 'Careers & Talent Acquisition', 'spicecraft-core' ),
			'description' => __( 'Food technology careers, quality assurance chemists, milling supervisors, and international trade executives.', 'spicecraft-core' ),
			'email'       => ! empty( $email_career ) ? $email_career : $admin_email,
			'phone'       => $phone_primary,
			'icon'        => 'groups',
			'badge'       => __( 'Join Team', 'spicecraft-core' ),
			'link'        => home_url( '/careers/' ),
		),
	);
}

/**
 * Retrieve FAQ items for Contact Us page.
 * Returns stored custom FAQs if available, otherwise high-value B2B industry FAQs.
 *
 * @return array
 */
function spicecraft_get_contact_faqs() {
	$global_settings = function_exists( 'spicecraft_get_all_settings' ) ? spicecraft_get_all_settings() : array();

	if ( ! empty( $global_settings['contact_faqs'] ) && is_array( $global_settings['contact_faqs'] ) ) {
		return $global_settings['contact_faqs'];
	}

	return array(
		array(
			'q' => __( 'What are your Minimum Order Quantities (MOQs) for bulk commercial supply?', 'spicecraft-core' ),
			'a' => __( 'For domestic bulk supply within India, our standard MOQ starts at 500 kg across single or assorted product lines in 25 kg poly-woven bulk sacks. For containerized export consignments, standard minimum volume is 1x20ft FCL (approximately 14–18 Metric Tons depending on spice bulk density). Smaller pilot consignments are evaluated on request.', 'spicecraft-core' ),
		),
		array(
			'q' => __( 'Do you provide pre-shipment sample testing and Certificates of Analysis (COA)?', 'spicecraft-core' ),
			'a' => __( 'Yes. For verified institutional and corporate buyers, our accredited quality assurance laboratory dispatches certified composite pre-shipment samples accompanied by a comprehensive Certificate of Analysis (COA) detailing moisture content, total ash, acid-insoluble ash, volatile oil percentage, and microbiological parameters.', 'spicecraft-core' ),
		),
		array(
			'q' => __( 'What statutory export licenses and food safety certifications do you hold?', 'spicecraft-core' ),
			'a' => __( 'SpiceCraft operates fully certified manufacturing facilities with ISO 22000:2018, HACCP food safety management, FSSAI central manufacturing license, US FDA Facility Registration, Spices Board of India registration, and certified Halal accreditation. Full audit documentation is made available during vendor onboarding.', 'spicecraft-core' ),
		),
		array(
			'q' => __( 'Can you manufacture custom spice formulations and bespoke private-label packaging?', 'spicecraft-core' ),
			'a' => __( 'Absolutely. We specialize in contract manufacturing and private label packaging for major retail brands and culinary franchises. Our R&D team can reverse-engineer or formulate custom blends, adjust grind mesh to exact culinary standards, and pack into zip-lock standup pouches, composite canisters, or food-grade PET jars with customized retail barcodes.', 'spicecraft-core' ),
		),
		array(
			'q' => __( 'What is the typical lead time from commercial quotation to order dispatch?', 'spicecraft-core' ),
			'a' => __( 'Standard catalog spice orders are dispatched within 5 to 7 business days from order confirmation and advance scheduling. Custom blends and customized private label packaging production cycles typically require 14 to 21 business days to accommodate custom packaging runs and laboratory clearance.', 'spicecraft-core' ),
		),
	);
}

/**
 * Retrieve location and map data for the Contact Us page.
 *
 * @return array
 */
function spicecraft_get_contact_map_data() {
	$contact_settings = spicecraft_get_contact_settings();
	$global_settings  = function_exists( 'spicecraft_get_all_settings' ) ? spicecraft_get_all_settings() : array();

	$embed_url = ! empty( $global_settings['map_embed_url'] ) ? $global_settings['map_embed_url'] : '';
	$address_primary = ! empty( $global_settings['address_primary'] ) ? $global_settings['address_primary'] : '';
	$address_factory = ! empty( $global_settings['address_factory'] ) ? $global_settings['address_factory'] : '';

	// Generate a safe directions link from primary address or coordinates
	$encoded_addr = ! empty( $address_primary ) ? rawurlencode( $address_primary ) : ( $contact_settings['map_latitude'] . ',' . $contact_settings['map_longitude'] );
	$directions_url = 'https://www.google.com/maps/search/?api=1&query=' . $encoded_addr;

	return array(
		'embed_url'       => $embed_url,
		'latitude'        => $contact_settings['map_latitude'],
		'longitude'       => $contact_settings['map_longitude'],
		'zoom'            => $contact_settings['map_zoom'],
		'title'           => $contact_settings['map_title'],
		'address_primary' => $address_primary,
		'address_factory' => $address_factory,
		'directions_url'  => $directions_url,
	);
}

/**
 * Output SEO meta tags and Schema.org JSON-LD structured data for the Contact Us page.
 */
function spicecraft_contact_page_head_seo() {
	if ( ! is_page_template( 'page-contact.php' ) && ! is_page( 'contact' ) && ! is_page( 'contact-us' ) ) {
		return;
	}

	$contact_settings = spicecraft_get_contact_settings();
	$global_settings  = function_exists( 'spicecraft_get_all_settings' ) ? spicecraft_get_all_settings() : array();
	$company_name     = ! empty( $global_settings['company_name'] ) ? $global_settings['company_name'] : get_bloginfo( 'name' );
	$title            = ! empty( $contact_settings['hero_title'] ) ? $contact_settings['hero_title'] : __( 'Contact SpiceCraft', 'spicecraft' );
	$description      = ! empty( $contact_settings['hero_subtitle'] ) ? $contact_settings['hero_subtitle'] : __( 'Connect with our team for product enquiries, bulk requirements, export opportunities, private-label manufacturing and business partnerships.', 'spicecraft' );
	$canonical_url    = get_permalink();

	echo "\n<!-- SpiceCraft Contact SEO -->\n";
	echo '<meta name="description" content="' . esc_attr( wp_strip_all_tags( $description ) ) . '">' . "\n";
	echo '<link rel="canonical" href="' . esc_url( $canonical_url ) . '">' . "\n";
	echo '<meta property="og:title" content="' . esc_attr( $title . ' | ' . $company_name ) . '">' . "\n";
	echo '<meta property="og:description" content="' . esc_attr( wp_strip_all_tags( $description ) ) . '">' . "\n";
	echo '<meta property="og:url" content="' . esc_url( $canonical_url ) . '">' . "\n";
	echo '<meta property="og:type" content="website">' . "\n";

	// Schema.org JSON-LD
	$schema = array(
		'@context' => 'https://schema.org',
		'@type'    => 'Organization',
		'name'     => $company_name,
		'url'      => home_url( '/' ),
		'contactPoint' => array(),
	);

	if ( ! empty( $global_settings['email_sales'] ) || ! empty( $global_settings['phone_primary'] ) ) {
		$sales_cp = array(
			'@type'       => 'ContactPoint',
			'contactType' => 'sales',
		);
		if ( ! empty( $global_settings['phone_primary'] ) ) {
			$sales_cp['telephone'] = $global_settings['phone_primary'];
		}
		if ( ! empty( $global_settings['email_sales'] ) ) {
			$sales_cp['email'] = $global_settings['email_sales'];
		}
		$schema['contactPoint'][] = $sales_cp;
	}

	if ( ! empty( $global_settings['email_export'] ) ) {
		$export_cp = array(
			'@type'       => 'ContactPoint',
			'contactType' => 'export desk',
			'email'       => $global_settings['email_export'],
		);
		$schema['contactPoint'][] = $export_cp;
	}

	if ( ! empty( $global_settings['address_primary'] ) ) {
		$schema['address'] = array(
			'@type'         => 'PostalAddress',
			'streetAddress' => $global_settings['address_primary'],
		);
	}

	echo '<script type="application/ld+json">' . wp_json_encode( $schema, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT ) . '</script>' . "\n";
}
add_action( 'wp_head', 'spicecraft_contact_page_head_seo', 5 );

/**
 * Retrieve unified company details from Global Settings for templates & components.
 *
 * @return array
 */
function spicecraft_get_company_info() {
	$settings = get_option( 'spicecraft_global_settings', array() );
	if ( ! is_array( $settings ) ) {
		$settings = array();
	}

	return array(
		'name'            => ! empty( $settings['company_name'] ) ? $settings['company_name'] : get_bloginfo( 'name' ),
		'registered_name' => ! empty( $settings['registered_company_name'] ) ? $settings['registered_company_name'] : '',
		'phone'           => ! empty( $settings['phone_primary'] ) ? $settings['phone_primary'] : '',
		'email'           => ! empty( $settings['email_general'] ) ? $settings['email_general'] : get_option( 'admin_email' ),
		'address'         => ! empty( $settings['address_primary'] ) ? $settings['address_primary'] : '',
		'business_hours'  => ! empty( $settings['business_hours'] ) ? $settings['business_hours'] : '',
	);
}

