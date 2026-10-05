<?php
/**
 * SpiceCraft Core - Centralized Global CMS Settings
 *
 * Implements a dedicated, WordPress-native admin settings interface under
 * SpiceCraft -> Global Settings. Eliminates all hard-coded business claims,
 * phones, emails, and regulatory numbers.
 *
 * @package SpiceCraft_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class SpiceCraft_Global_Settings {

	/**
	 * Singleton Instance
	 *
	 * @var SpiceCraft_Global_Settings|null
	 */
	private static $instance = null;

	/**
	 * Option Name in wp_options
	 *
	 * @var string
	 */
	const OPTION_NAME = 'spicecraft_global_settings';

	/**
	 * Get Singleton Instance
	 *
	 * @return SpiceCraft_Global_Settings
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor
	 */
	private function __construct() {
		add_action( 'admin_menu', array( $this, 'register_admin_menu' ), 10 );
		add_action( 'admin_menu', array( $this, 'register_late_admin_menu' ), 25 );
		add_action( 'admin_init', array( $this, 'register_settings' ) );
	}

	/**
	 * Register Admin Menu
	 */
	public function register_admin_menu() {
		// Top Level Menu: SpiceCraft
		add_menu_page(
			__( 'SpiceCraft CMS', 'spicecraft-core' ),
			__( 'SpiceCraft', 'spicecraft-core' ),
			'manage_options',
			'spicecraft-overview',
			array( $this, 'render_overview_page' ),
			'dashicons-store',
			25
		);

		// Submenu 1: Overview
		add_submenu_page(
			'spicecraft-overview',
			__( 'SpiceCraft CMS Overview', 'spicecraft-core' ),
			__( 'Overview', 'spicecraft-core' ),
			'manage_options',
			'spicecraft-overview',
			array( $this, 'render_overview_page' )
		);

		// Submenu 2: Global Settings
		add_submenu_page(
			'spicecraft-overview',
			__( 'Global Settings', 'spicecraft-core' ),
			__( 'Global Settings', 'spicecraft-core' ),
			'manage_options',
			'spicecraft-settings',
			array( $this, 'render_settings_page' )
		);

		// Submenu 3: Import / Export
		add_submenu_page(
			'spicecraft-overview',
			__( 'Import / Export', 'spicecraft-core' ),
			__( 'Import / Export', 'spicecraft-core' ),
			'manage_options',
			'spicecraft-import-export',
			function () {
				if ( class_exists( 'SpiceCraft_Import_Export' ) ) {
					SpiceCraft_Import_Export::get_instance()->render_admin_page();
				}
			}
		);
	}

	/**
	 * Register Late Submenus (Certifications) to maintain exact menu order:
	 * Overview -> Global Settings -> Homepage -> Certifications
	 */
	public function register_late_admin_menu() {
		// Submenu 4: Link to Certifications Taxonomy
		add_submenu_page(
			'spicecraft-overview',
			__( 'Product Certifications', 'spicecraft-core' ),
			__( 'Certifications', 'spicecraft-core' ),
			'manage_options',
			'edit-tags.php?taxonomy=spicecraft_certification&post_type=product'
		);
	}

	/**
	 * Register Settings with Validation & Sanitization
	 */
	public function register_settings() {
		register_setting(
			'spicecraft_settings_group',
			self::OPTION_NAME,
			array(
				'sanitize_callback' => array( $this, 'sanitize_settings' ),
				'default'           => array(),
			)
		);
	}

	/**
	 * Sanitize Settings Input
	 *
	 * @param array $input Raw form data.
	 * @return array Clean sanitized data.
	 */
	public function sanitize_settings( $input ) {
		if ( ! is_array( $input ) ) {
			return array();
		}

		$existing = get_option( self::OPTION_NAME, array() );
		$clean    = is_array( $existing ) ? $existing : array();

		// Company Info (only if submitted)
		if ( isset( $input['company_name'] ) || isset( $input['address_primary'] ) ) {
			$clean['company_name']            = isset( $input['company_name'] ) ? sanitize_text_field( $input['company_name'] ) : '';
			$clean['company_description']     = isset( $input['company_description'] ) ? sanitize_textarea_field( $input['company_description'] ) : '';
			$clean['registered_company_name'] = isset( $input['registered_company_name'] ) ? sanitize_text_field( $input['registered_company_name'] ) : '';
			$clean['phone_primary']           = isset( $input['phone_primary'] ) ? sanitize_text_field( $input['phone_primary'] ) : '';
			$clean['phone_secondary']         = isset( $input['phone_secondary'] ) ? sanitize_text_field( $input['phone_secondary'] ) : '';
			$clean['business_hours']          = isset( $input['business_hours'] ) ? sanitize_text_field( $input['business_hours'] ) : '';
			$clean['address_primary']         = isset( $input['address_primary'] ) ? sanitize_textarea_field( $input['address_primary'] ) : '';
			$clean['address_factory']         = isset( $input['address_factory'] ) ? sanitize_textarea_field( $input['address_factory'] ) : '';
			$clean['map_embed_url']           = isset( $input['map_embed_url'] ) ? esc_url_raw( $input['map_embed_url'] ) : '';
			$clean['header_cta_text']         = isset( $input['header_cta_text'] ) ? sanitize_text_field( $input['header_cta_text'] ) : '';
			$clean['header_cta_url']          = isset( $input['header_cta_url'] ) ? esc_url_raw( $input['header_cta_url'] ) : '';
		}

		// WhatsApp & Enquiries (only if submitted)
		if ( isset( $input['whatsapp_number'] ) || isset( $input['email_general'] ) || isset( $input['enquiry_receiving_email'] ) ) {
			$clean['whatsapp_number']           = isset( $input['whatsapp_number'] ) ? sanitize_text_field( $input['whatsapp_number'] ) : '';
			$clean['whatsapp_default_message']  = isset( $input['whatsapp_default_message'] ) ? sanitize_textarea_field( $input['whatsapp_default_message'] ) : '';
			$clean['whatsapp_product_template'] = isset( $input['whatsapp_product_template'] ) ? sanitize_textarea_field( $input['whatsapp_product_template'] ) : '';
			$clean['email_general']             = isset( $input['email_general'] ) ? sanitize_email( $input['email_general'] ) : '';
			$clean['email_sales']               = isset( $input['email_sales'] ) ? sanitize_email( $input['email_sales'] ) : '';
			$clean['email_export']              = isset( $input['email_export'] ) ? sanitize_email( $input['email_export'] ) : '';
			$clean['email_career']              = isset( $input['email_career'] ) ? sanitize_email( $input['email_career'] ) : '';
			$clean['enquiry_receiving_email']        = isset( $input['enquiry_receiving_email'] ) ? sanitize_email( $input['enquiry_receiving_email'] ) : '';
			$clean['enquiry_customer_email_enabled'] = ! empty( $input['enquiry_customer_email_enabled'] ) ? '1' : '0';
		}

		// Branding (only if submitted)
		if ( isset( $input['footer_logo_url'] ) ) {
			$clean['footer_logo_url'] = esc_url_raw( $input['footer_logo_url'] );
		}

		// Social Links (only if submitted)
		if ( isset( $input['social_facebook'] ) || isset( $input['social_instagram'] ) ) {
			$clean['social_facebook']  = isset( $input['social_facebook'] ) ? esc_url_raw( $input['social_facebook'] ) : '';
			$clean['social_instagram'] = isset( $input['social_instagram'] ) ? esc_url_raw( $input['social_instagram'] ) : '';
			$clean['social_linkedin']  = isset( $input['social_linkedin'] ) ? esc_url_raw( $input['social_linkedin'] ) : '';
			$clean['social_youtube']   = isset( $input['social_youtube'] ) ? esc_url_raw( $input['social_youtube'] ) : '';
			$clean['social_pinterest'] = isset( $input['social_pinterest'] ) ? esc_url_raw( $input['social_pinterest'] ) : '';
			$clean['social_twitter']   = isset( $input['social_twitter'] ) ? esc_url_raw( $input['social_twitter'] ) : '';
		}

		// Regulatory & Compliance (only if submitted)
		if ( isset( $input['fssai_license'] ) || isset( $input['gst_number'] ) ) {
			$clean['fssai_license']          = isset( $input['fssai_license'] ) ? sanitize_text_field( $input['fssai_license'] ) : '';
			$clean['gst_number']             = isset( $input['gst_number'] ) ? sanitize_text_field( $input['gst_number'] ) : '';
			$clean['iec_code']               = isset( $input['iec_code'] ) ? sanitize_text_field( $input['iec_code'] ) : '';
			$clean['certifications_summary'] = isset( $input['certifications_summary'] ) ? sanitize_text_field( $input['certifications_summary'] ) : '';
		}

		// Footer & Legal (only if submitted)
		if ( isset( $input['footer_description'] ) || isset( $input['footer_copyright'] ) ) {
			$clean['footer_description'] = isset( $input['footer_description'] ) ? sanitize_textarea_field( $input['footer_description'] ) : '';
			$clean['footer_copyright']   = isset( $input['footer_copyright'] ) ? sanitize_text_field( $input['footer_copyright'] ) : '';
			$clean['footer_disclaimer']  = isset( $input['footer_disclaimer'] ) ? sanitize_textarea_field( $input['footer_disclaimer'] ) : '';
		}

		// Contact Page & Location (only if submitted)
		if ( isset( $input['contact_hero_title'] ) || isset( $input['contact_hero_eyebrow'] ) ) {
			$clean['contact_hero_eyebrow']    = isset( $input['contact_hero_eyebrow'] ) ? sanitize_text_field( $input['contact_hero_eyebrow'] ) : '';
			$clean['contact_hero_title']      = isset( $input['contact_hero_title'] ) ? sanitize_text_field( $input['contact_hero_title'] ) : '';
			$clean['contact_hero_subtitle']   = isset( $input['contact_hero_subtitle'] ) ? sanitize_textarea_field( $input['contact_hero_subtitle'] ) : '';
			$clean['contact_hero_badge']      = isset( $input['contact_hero_badge'] ) ? sanitize_text_field( $input['contact_hero_badge'] ) : '';
			$clean['contact_map_latitude']    = isset( $input['contact_map_latitude'] ) ? sanitize_text_field( $input['contact_map_latitude'] ) : '';
			$clean['contact_map_longitude']   = isset( $input['contact_map_longitude'] ) ? sanitize_text_field( $input['contact_map_longitude'] ) : '';
			$clean['contact_map_zoom']        = isset( $input['contact_map_zoom'] ) ? sanitize_text_field( $input['contact_map_zoom'] ) : '';
			$clean['contact_map_title']       = isset( $input['contact_map_title'] ) ? sanitize_text_field( $input['contact_map_title'] ) : '';
			$clean['contact_faq_heading']     = isset( $input['contact_faq_heading'] ) ? sanitize_text_field( $input['contact_faq_heading'] ) : '';
			$clean['contact_faq_subtitle']    = isset( $input['contact_faq_subtitle'] ) ? sanitize_text_field( $input['contact_faq_subtitle'] ) : '';
			$clean['contact_cta_heading']     = isset( $input['contact_cta_heading'] ) ? sanitize_text_field( $input['contact_cta_heading'] ) : '';
			$clean['contact_cta_subtitle']    = isset( $input['contact_cta_subtitle'] ) ? sanitize_textarea_field( $input['contact_cta_subtitle'] ) : '';
			$clean['contact_cta_button_text'] = isset( $input['contact_cta_button_text'] ) ? sanitize_text_field( $input['contact_cta_button_text'] ) : '';
			$clean['contact_cta_button_url']  = isset( $input['contact_cta_button_url'] ) ? esc_url_raw( $input['contact_cta_button_url'] ) : '';

			// FAQs repeater
			$clean['contact_faqs'] = array();
			if ( ! empty( $input['contact_faqs'] ) && is_array( $input['contact_faqs'] ) ) {
				foreach ( $input['contact_faqs'] as $faq ) {
					$q = isset( $faq['q'] ) ? sanitize_text_field( $faq['q'] ) : '';
					$a = isset( $faq['a'] ) ? sanitize_textarea_field( $faq['a'] ) : '';
					if ( ! empty( $q ) && ! empty( $a ) ) {
						$clean['contact_faqs'][] = array(
							'q' => $q,
							'a' => $a,
						);
					}
				}
			}
		}

		return $clean;
	}

	/**
	 * Render Global Settings Page
	 */
	public function render_settings_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$settings   = get_option( self::OPTION_NAME, array() );
		$active_tab = isset( $_GET['tab'] ) ? sanitize_key( $_GET['tab'] ) : 'company';
		?>
		<div class="wrap spicecraft-settings-wrap">
			<h1><?php esc_html_e( 'SpiceCraft Global CMS Settings', 'spicecraft-core' ); ?></h1>
			<p class="description">
				<?php esc_html_e( 'Manage global company information, contact details, WhatsApp lead routing, regulatory disclosures, and footer content.', 'spicecraft-core' ); ?>
			</p>

			<?php settings_errors(); ?>

			<!-- Navigation Tabs -->
			<h2 class="nav-tab-wrapper">
				<a href="?page=spicecraft-settings&tab=company" class="nav-tab <?php echo 'company' === $active_tab ? 'nav-tab-active' : ''; ?>">
					<?php esc_html_e( 'Company Info', 'spicecraft-core' ); ?>
				</a>
				<a href="?page=spicecraft-settings&tab=contact" class="nav-tab <?php echo 'contact' === $active_tab ? 'nav-tab-active' : ''; ?>">
					<?php esc_html_e( 'Contact Page & Map', 'spicecraft-core' ); ?>
				</a>
				<a href="?page=spicecraft-settings&tab=enquiries" class="nav-tab <?php echo 'enquiries' === $active_tab ? 'nav-tab-active' : ''; ?>">
					<?php esc_html_e( 'WhatsApp & Enquiries', 'spicecraft-core' ); ?>
				</a>
				<a href="?page=spicecraft-settings&tab=branding" class="nav-tab <?php echo 'branding' === $active_tab ? 'nav-tab-active' : ''; ?>">
					<?php esc_html_e( 'Branding & Identity', 'spicecraft-core' ); ?>
				</a>
				<a href="?page=spicecraft-settings&tab=social" class="nav-tab <?php echo 'social' === $active_tab ? 'nav-tab-active' : ''; ?>">
					<?php esc_html_e( 'Social Channels', 'spicecraft-core' ); ?>
				</a>
				<a href="?page=spicecraft-settings&tab=regulatory" class="nav-tab <?php echo 'regulatory' === $active_tab ? 'nav-tab-active' : ''; ?>">
					<?php esc_html_e( 'Regulatory & Compliance', 'spicecraft-core' ); ?>
				</a>
				<a href="?page=spicecraft-settings&tab=footer" class="nav-tab <?php echo 'footer' === $active_tab ? 'nav-tab-active' : ''; ?>">
					<?php esc_html_e( 'Footer & Legal', 'spicecraft-core' ); ?>
				</a>
			</h2>

			<form method="post" action="options.php" class="spicecraft-settings-form">
				<?php
				settings_fields( 'spicecraft_settings_group' );

				// TAB 1: Company Information
				if ( 'company' === $active_tab ) :
					?>
					<table class="form-table" role="presentation">
						<tr>
							<th scope="row"><label for="sc_company_name"><?php esc_html_e( 'Company / Brand Name', 'spicecraft-core' ); ?></label></th>
							<td>
								<input name="<?php echo esc_attr( self::OPTION_NAME ); ?>[company_name]" type="text" id="sc_company_name" value="<?php echo esc_attr( $settings['company_name'] ?? '' ); ?>" class="regular-text" />
							</td>
						</tr>
						<tr>
							<th scope="row"><label for="sc_registered_name"><?php esc_html_e( 'Registered Business Entity Name', 'spicecraft-core' ); ?></label></th>
							<td>
								<input name="<?php echo esc_attr( self::OPTION_NAME ); ?>[registered_company_name]" type="text" id="sc_registered_name" value="<?php echo esc_attr( $settings['registered_company_name'] ?? '' ); ?>" class="regular-text" />
								<p class="description"><?php esc_html_e( 'Full statutory corporate entity name (e.g., SpiceCraft Agro Foods Pvt. Ltd.).', 'spicecraft-core' ); ?></p>
							</td>
						</tr>
						<tr>
							<th scope="row"><label for="sc_company_desc"><?php esc_html_e( 'Short Company Description', 'spicecraft-core' ); ?></label></th>
							<td>
								<textarea name="<?php echo esc_attr( self::OPTION_NAME ); ?>[company_description]" id="sc_company_desc" rows="3" class="large-text"><?php echo esc_textarea( $settings['company_description'] ?? '' ); ?></textarea>
							</td>
						</tr>
						<tr>
							<th scope="row"><label for="sc_phone_primary"><?php esc_html_e( 'Primary Contact Phone', 'spicecraft-core' ); ?></label></th>
							<td>
								<input name="<?php echo esc_attr( self::OPTION_NAME ); ?>[phone_primary]" type="text" id="sc_phone_primary" value="<?php echo esc_attr( $settings['phone_primary'] ?? '' ); ?>" class="regular-text" />
							</td>
						</tr>
						<tr>
							<th scope="row"><label for="sc_phone_secondary"><?php esc_html_e( 'Secondary / Plant Phone', 'spicecraft-core' ); ?></label></th>
							<td>
								<input name="<?php echo esc_attr( self::OPTION_NAME ); ?>[phone_secondary]" type="text" id="sc_phone_secondary" value="<?php echo esc_attr( $settings['phone_secondary'] ?? '' ); ?>" class="regular-text" />
							</td>
						</tr>
						<tr>
							<th scope="row"><label for="sc_business_hours"><?php esc_html_e( 'Business & Factory Hours', 'spicecraft-core' ); ?></label></th>
							<td>
								<input name="<?php echo esc_attr( self::OPTION_NAME ); ?>[business_hours]" type="text" id="sc_business_hours" value="<?php echo esc_attr( $settings['business_hours'] ?? '' ); ?>" class="regular-text" />
								<p class="description"><?php esc_html_e( 'e.g. Mon – Sat: 9:00 AM – 6:00 PM IST', 'spicecraft-core' ); ?></p>
							</td>
						</tr>
						<tr>
							<th scope="row"><label for="sc_address_primary"><?php esc_html_e( 'Corporate Office Address', 'spicecraft-core' ); ?></label></th>
							<td>
								<textarea name="<?php echo esc_attr( self::OPTION_NAME ); ?>[address_primary]" id="sc_address_primary" rows="3" class="large-text"><?php echo esc_textarea( $settings['address_primary'] ?? '' ); ?></textarea>
							</td>
						</tr>
						<tr>
							<th scope="row"><label for="sc_address_factory"><?php esc_html_e( 'Manufacturing Plant / Export Hub', 'spicecraft-core' ); ?></label></th>
							<td>
								<textarea name="<?php echo esc_attr( self::OPTION_NAME ); ?>[address_factory]" id="sc_address_factory" rows="3" class="large-text"><?php echo esc_textarea( $settings['address_factory'] ?? '' ); ?></textarea>
							</td>
						</tr>
						<tr>
							<th scope="row"><label for="sc_map_url"><?php esc_html_e( 'Google Maps Embed URL', 'spicecraft-core' ); ?></label></th>
							<td>
								<input name="<?php echo esc_attr( self::OPTION_NAME ); ?>[map_embed_url]" type="url" id="sc_map_url" value="<?php echo esc_attr( $settings['map_embed_url'] ?? '' ); ?>" class="large-text" />
							</td>
						</tr>
						<tr>
							<th scope="row"><label for="sc_header_cta_text"><?php esc_html_e( 'Header CTA Button Text', 'spicecraft-core' ); ?></label></th>
							<td>
								<input name="<?php echo esc_attr( self::OPTION_NAME ); ?>[header_cta_text]" type="text" id="sc_header_cta_text" value="<?php echo esc_attr( $settings['header_cta_text'] ?? '' ); ?>" class="regular-text" placeholder="<?php esc_attr_e( 'Trade Enquiry', 'spicecraft-core' ); ?>" />
								<p class="description"><?php esc_html_e( 'Text label for primary header button (e.g. Trade Enquiry, Contact Us). Defaults to "Trade Enquiry".', 'spicecraft-core' ); ?></p>
							</td>
						</tr>
						<tr>
							<th scope="row"><label for="sc_header_cta_url"><?php esc_html_e( 'Header CTA Destination URL', 'spicecraft-core' ); ?></label></th>
							<td>
								<input name="<?php echo esc_attr( self::OPTION_NAME ); ?>[header_cta_url]" type="text" id="sc_header_cta_url" value="<?php echo esc_attr( $settings['header_cta_url'] ?? '' ); ?>" class="regular-text" placeholder="<?php echo esc_attr( home_url( '/#contact' ) ); ?>" />
								<p class="description"><?php esc_html_e( 'Link target for the header CTA button. Defaults to /#contact if left empty.', 'spicecraft-core' ); ?></p>
							</td>
						</tr>
					</table>

				<?php
				// TAB: Contact Page & Map
				elseif ( 'contact' === $active_tab ) :
					$contact_defaults = function_exists( 'spicecraft_get_contact_settings' ) ? spicecraft_get_contact_settings() : array();
					$faqs             = function_exists( 'spicecraft_get_contact_faqs' ) ? spicecraft_get_contact_faqs() : array();
					?>
					<h3 class="title"><?php esc_html_e( 'Contact Hero Section', 'spicecraft-core' ); ?></h3>
					<table class="form-table" role="presentation">
						<tr>
							<th scope="row"><label for="sc_contact_hero_eyebrow"><?php esc_html_e( 'Hero Eyebrow / Tag', 'spicecraft-core' ); ?></label></th>
							<td>
								<input name="<?php echo esc_attr( self::OPTION_NAME ); ?>[contact_hero_eyebrow]" type="text" id="sc_contact_hero_eyebrow" value="<?php echo esc_attr( $settings['contact_hero_eyebrow'] ?? $contact_defaults['hero_eyebrow'] ); ?>" class="regular-text" />
							</td>
						</tr>
						<tr>
							<th scope="row"><label for="sc_contact_hero_title"><?php esc_html_e( 'Hero Main Title', 'spicecraft-core' ); ?></label></th>
							<td>
								<input name="<?php echo esc_attr( self::OPTION_NAME ); ?>[contact_hero_title]" type="text" id="sc_contact_hero_title" value="<?php echo esc_attr( $settings['contact_hero_title'] ?? $contact_defaults['hero_title'] ); ?>" class="large-text" />
							</td>
						</tr>
						<tr>
							<th scope="row"><label for="sc_contact_hero_subtitle"><?php esc_html_e( 'Hero Introduction / Subtitle', 'spicecraft-core' ); ?></label></th>
							<td>
								<textarea name="<?php echo esc_attr( self::OPTION_NAME ); ?>[contact_hero_subtitle]" id="sc_contact_hero_subtitle" rows="3" class="large-text"><?php echo esc_textarea( $settings['contact_hero_subtitle'] ?? $contact_defaults['hero_subtitle'] ); ?></textarea>
							</td>
						</tr>
						<tr>
							<th scope="row"><label for="sc_contact_hero_badge"><?php esc_html_e( 'Response Time Badge', 'spicecraft-core' ); ?></label></th>
							<td>
								<input name="<?php echo esc_attr( self::OPTION_NAME ); ?>[contact_hero_badge]" type="text" id="sc_contact_hero_badge" value="<?php echo esc_attr( $settings['contact_hero_badge'] ?? $contact_defaults['hero_badge'] ); ?>" class="regular-text" />
							</td>
						</tr>
					</table>

					<h3 class="title"><?php esc_html_e( 'Location & Map Coordinates', 'spicecraft-core' ); ?></h3>
					<table class="form-table" role="presentation">
						<tr>
							<th scope="row"><label for="sc_contact_map_title"><?php esc_html_e( 'Campus / Campus Name', 'spicecraft-core' ); ?></label></th>
							<td>
								<input name="<?php echo esc_attr( self::OPTION_NAME ); ?>[contact_map_title]" type="text" id="sc_contact_map_title" value="<?php echo esc_attr( $settings['contact_map_title'] ?? $contact_defaults['map_title'] ); ?>" class="regular-text" />
							</td>
						</tr>
						<tr>
							<th scope="row"><label for="sc_contact_map_lat"><?php esc_html_e( 'Map Latitude', 'spicecraft-core' ); ?></label></th>
							<td>
								<input name="<?php echo esc_attr( self::OPTION_NAME ); ?>[contact_map_latitude]" type="text" id="sc_contact_map_lat" value="<?php echo esc_attr( $settings['contact_map_latitude'] ?? $contact_defaults['map_latitude'] ); ?>" class="regular-text" placeholder="10.0159" />
							</td>
						</tr>
						<tr>
							<th scope="row"><label for="sc_contact_map_lng"><?php esc_html_e( 'Map Longitude', 'spicecraft-core' ); ?></label></th>
							<td>
								<input name="<?php echo esc_attr( self::OPTION_NAME ); ?>[contact_map_longitude]" type="text" id="sc_contact_map_lng" value="<?php echo esc_attr( $settings['contact_map_longitude'] ?? $contact_defaults['map_longitude'] ); ?>" class="regular-text" placeholder="76.3419" />
							</td>
						</tr>
						<tr>
							<th scope="row"><label for="sc_contact_map_zoom"><?php esc_html_e( 'Default Zoom Level', 'spicecraft-core' ); ?></label></th>
							<td>
								<input name="<?php echo esc_attr( self::OPTION_NAME ); ?>[contact_map_zoom]" type="number" min="1" max="20" id="sc_contact_map_zoom" value="<?php echo esc_attr( $settings['contact_map_zoom'] ?? $contact_defaults['map_zoom'] ); ?>" class="small-text" />
								<p class="description"><?php esc_html_e( 'Recommended: 13 to 16. Google Maps Embed iframe URL can also be set in Company Info tab.', 'spicecraft-core' ); ?></p>
							</td>
						</tr>
					</table>

					<h3 class="title"><?php esc_html_e( 'Frequently Asked Trade Questions (FAQs)', 'spicecraft-core' ); ?></h3>
					<table class="form-table" role="presentation">
						<tr>
							<th scope="row"><label for="sc_contact_faq_heading"><?php esc_html_e( 'FAQ Section Heading', 'spicecraft-core' ); ?></label></th>
							<td>
								<input name="<?php echo esc_attr( self::OPTION_NAME ); ?>[contact_faq_heading]" type="text" id="sc_contact_faq_heading" value="<?php echo esc_attr( $settings['contact_faq_heading'] ?? $contact_defaults['faq_heading'] ); ?>" class="large-text" />
							</td>
						</tr>
						<tr>
							<th scope="row"><label for="sc_contact_faq_subtitle"><?php esc_html_e( 'FAQ Subtitle / Note', 'spicecraft-core' ); ?></label></th>
							<td>
								<textarea name="<?php echo esc_attr( self::OPTION_NAME ); ?>[contact_faq_subtitle]" id="sc_contact_faq_subtitle" rows="2" class="large-text"><?php echo esc_textarea( $settings['contact_faq_subtitle'] ?? $contact_defaults['faq_subtitle'] ); ?></textarea>
							</td>
						</tr>
						<?php
						$max_faqs = max( count( $faqs ), 5 );
						for ( $i = 0; $i < $max_faqs; $i++ ) :
							$faq_q = isset( $faqs[ $i ]['q'] ) ? $faqs[ $i ]['q'] : '';
							$faq_a = isset( $faqs[ $i ]['a'] ) ? $faqs[ $i ]['a'] : '';
							?>
							<tr>
								<th scope="row"><?php /* translators: %d: FAQ number */ printf( esc_html__( 'FAQ #%d', 'spicecraft-core' ), $i + 1 ); ?></th>
								<td>
									<p><strong><?php esc_html_e( 'Question:', 'spicecraft-core' ); ?></strong></p>
									<input name="<?php echo esc_attr( self::OPTION_NAME ); ?>[contact_faqs][<?php echo esc_attr( $i ); ?>][q]" type="text" value="<?php echo esc_attr( $faq_q ); ?>" class="large-text" placeholder="<?php esc_attr_e( 'Enter question...', 'spicecraft-core' ); ?>" />
									<p style="margin-top:8px;"><strong><?php esc_html_e( 'Answer:', 'spicecraft-core' ); ?></strong></p>
									<textarea name="<?php echo esc_attr( self::OPTION_NAME ); ?>[contact_faqs][<?php echo esc_attr( $i ); ?>][a]" rows="3" class="large-text" placeholder="<?php esc_attr_e( 'Enter detailed response...', 'spicecraft-core' ); ?>"><?php echo esc_textarea( $faq_a ); ?></textarea>
								</td>
							</tr>
						<?php endfor; ?>
					</table>

					<h3 class="title"><?php esc_html_e( 'Final B2B Conversion CTA Banner', 'spicecraft-core' ); ?></h3>
					<table class="form-table" role="presentation">
						<tr>
							<th scope="row"><label for="sc_contact_cta_heading"><?php esc_html_e( 'CTA Heading', 'spicecraft-core' ); ?></label></th>
							<td>
								<input name="<?php echo esc_attr( self::OPTION_NAME ); ?>[contact_cta_heading]" type="text" id="sc_contact_cta_heading" value="<?php echo esc_attr( $settings['contact_cta_heading'] ?? $contact_defaults['cta_heading'] ); ?>" class="large-text" />
							</td>
						</tr>
						<tr>
							<th scope="row"><label for="sc_contact_cta_subtitle"><?php esc_html_e( 'CTA Subtitle', 'spicecraft-core' ); ?></label></th>
							<td>
								<textarea name="<?php echo esc_attr( self::OPTION_NAME ); ?>[contact_cta_subtitle]" id="sc_contact_cta_subtitle" rows="2" class="large-text"><?php echo esc_textarea( $settings['contact_cta_subtitle'] ?? $contact_defaults['cta_subtitle'] ); ?></textarea>
							</td>
						</tr>
						<tr>
							<th scope="row"><label for="sc_contact_cta_btn_text"><?php esc_html_e( 'CTA Button Text', 'spicecraft-core' ); ?></label></th>
							<td>
								<input name="<?php echo esc_attr( self::OPTION_NAME ); ?>[contact_cta_button_text]" type="text" id="sc_contact_cta_btn_text" value="<?php echo esc_attr( $settings['contact_cta_button_text'] ?? $contact_defaults['cta_button_text'] ); ?>" class="regular-text" />
							</td>
						</tr>
						<tr>
							<th scope="row"><label for="sc_contact_cta_btn_url"><?php esc_html_e( 'CTA Button URL', 'spicecraft-core' ); ?></label></th>
							<td>
								<input name="<?php echo esc_attr( self::OPTION_NAME ); ?>[contact_cta_button_url]" type="text" id="sc_contact_cta_btn_url" value="<?php echo esc_attr( $settings['contact_cta_button_url'] ?? $contact_defaults['cta_button_url'] ); ?>" class="large-text" />
							</td>
						</tr>
					</table>

				<?php
				// TAB 2: WhatsApp & Enquiries
				elseif ( 'enquiries' === $active_tab ) :
					?>
					<table class="form-table" role="presentation">
						<tr>
							<th scope="row"><label for="sc_whatsapp"><?php esc_html_e( 'WhatsApp Business Number', 'spicecraft-core' ); ?></label></th>
							<td>
								<input name="<?php echo esc_attr( self::OPTION_NAME ); ?>[whatsapp_number]" type="text" id="sc_whatsapp" value="<?php echo esc_attr( $settings['whatsapp_number'] ?? '' ); ?>" class="regular-text" placeholder="+91 9876543210" />
								<p class="description"><?php esc_html_e( 'Include country code with leading plus sign. Used across all WhatsApp catalog enquiry buttons.', 'spicecraft-core' ); ?></p>
							</td>
						</tr>
						<tr>
							<th scope="row"><label for="sc_whatsapp_msg"><?php esc_html_e( 'Default WhatsApp General Inquiry', 'spicecraft-core' ); ?></label></th>
							<td>
								<textarea name="<?php echo esc_attr( self::OPTION_NAME ); ?>[whatsapp_default_message]" id="sc_whatsapp_msg" rows="3" class="large-text" placeholder="<?php esc_attr_e( 'Hello, I am interested in your spice products. Please share your catalog and trade enquiry details.', 'spicecraft-core' ); ?>"><?php echo esc_textarea( $settings['whatsapp_default_message'] ?? '' ); ?></textarea>
							</td>
						</tr>
						<tr>
							<th scope="row"><label for="sc_whatsapp_tpl"><?php esc_html_e( 'Product WhatsApp Inquiry Template', 'spicecraft-core' ); ?></label></th>
							<td>
								<textarea name="<?php echo esc_attr( self::OPTION_NAME ); ?>[whatsapp_product_template]" id="sc_whatsapp_tpl" rows="6" class="large-text" placeholder="Hello, I am interested in {product_name}.&#10;&#10;Product: {product_name}&#10;SKU: {sku}&#10;Pack Size: {pack_size}&#10;Product URL: {product_url}&#10;&#10;Please share more information."><?php echo esc_textarea( $settings['whatsapp_product_template'] ?? '' ); ?></textarea>
								<p class="description">
									<?php esc_html_e( 'Template used for single product WhatsApp enquiries. Supported placeholders: {product_name}, {sku}, {pack_size}, {product_url}. Lines with empty placeholder values are cleanly omitted.', 'spicecraft-core' ); ?>
								</p>
							</td>
						</tr>
						<tr>
							<th scope="row"><label for="sc_email_general"><?php esc_html_e( 'General Enquiry Email', 'spicecraft-core' ); ?></label></th>
							<td>
								<input name="<?php echo esc_attr( self::OPTION_NAME ); ?>[email_general]" type="email" id="sc_email_general" value="<?php echo esc_attr( $settings['email_general'] ?? '' ); ?>" class="regular-text" />
							</td>
						</tr>
						<tr>
							<th scope="row"><label for="sc_email_sales"><?php esc_html_e( 'Domestic Sales Email', 'spicecraft-core' ); ?></label></th>
							<td>
								<input name="<?php echo esc_attr( self::OPTION_NAME ); ?>[email_sales]" type="email" id="sc_email_sales" value="<?php echo esc_attr( $settings['email_sales'] ?? '' ); ?>" class="regular-text" />
							</td>
						</tr>
						<tr>
							<th scope="row"><label for="sc_email_export"><?php esc_html_e( 'Export & Institutional Bulk Email', 'spicecraft-core' ); ?></label></th>
							<td>
								<input name="<?php echo esc_attr( self::OPTION_NAME ); ?>[email_export]" type="email" id="sc_email_export" value="<?php echo esc_attr( $settings['email_export'] ?? '' ); ?>" class="regular-text" />
								<p class="description"><?php esc_html_e( 'Target inbox for single product trade enquiry buttons.', 'spicecraft-core' ); ?></p>
							</td>
						</tr>
						<tr>
							<th scope="row"><label for="sc_email_career"><?php esc_html_e( 'Careers & Recruitment Email', 'spicecraft-core' ); ?></label></th>
							<td>
								<input name="<?php echo esc_attr( self::OPTION_NAME ); ?>[email_career]" type="email" id="sc_email_career" value="<?php echo esc_attr( $settings['email_career'] ?? '' ); ?>" class="regular-text" />
							</td>
						</tr>
						<tr>
							<th scope="row"><label for="sc_enquiry_receiving_email"><?php esc_html_e( 'Enquiry Receiving Email', 'spicecraft-core' ); ?></label></th>
							<td>
								<input name="<?php echo esc_attr( self::OPTION_NAME ); ?>[enquiry_receiving_email]" type="email" id="sc_enquiry_receiving_email" value="<?php echo esc_attr( $settings['enquiry_receiving_email'] ?? '' ); ?>" class="regular-text" placeholder="<?php echo esc_attr( get_option( 'admin_email' ) ); ?>" />
								<p class="description"><?php esc_html_e( 'Target inbox for admin email notifications when visitors submit product and business enquiries. Changing this dynamically routes all future enquiries.', 'spicecraft-core' ); ?></p>
							</td>
						</tr>
						<tr>
							<th scope="row"><?php esc_html_e( 'Customer Confirmation Email', 'spicecraft-core' ); ?></th>
							<td>
								<label for="sc_enquiry_customer_email">
									<input name="<?php echo esc_attr( self::OPTION_NAME ); ?>[enquiry_customer_email_enabled]" type="checkbox" id="sc_enquiry_customer_email" value="1" <?php checked( ! empty( $settings['enquiry_customer_email_enabled'] ) ); ?> />
									<?php esc_html_e( 'Send automatic acknowledgement email to customers upon submission', 'spicecraft-core' ); ?>
								</label>
								<p class="description"><?php esc_html_e( 'Sends a professional branded confirmation copy to the customer\'s provided email address.', 'spicecraft-core' ); ?></p>
							</td>
						</tr>
					</table>

				<?php
				// TAB 3: Branding & Identity
				elseif ( 'branding' === $active_tab ) :
					?>
					<table class="form-table" role="presentation">
						<tr>
							<th scope="row"><?php esc_html_e( 'Main Site Logo & Icon', 'spicecraft-core' ); ?></th>
							<td>
								<p>
									<a href="<?php echo esc_url( admin_url( 'customize.php?autofocus[section]=title_tagline' ) ); ?>" class="button button-secondary">
										<?php esc_html_e( 'Configure in WordPress Customizer &rarr;', 'spicecraft-core' ); ?>
									</a>
								</p>
								<p class="description"><?php esc_html_e( 'Main header logo, site title, tagline, and browser favicon are managed natively via WordPress Site Identity.', 'spicecraft-core' ); ?></p>
							</td>
						</tr>
						<tr>
							<th scope="row"><label for="sc_footer_logo"><?php esc_html_e( 'Alternative / Footer Logo URL', 'spicecraft-core' ); ?></label></th>
							<td>
								<input name="<?php echo esc_attr( self::OPTION_NAME ); ?>[footer_logo_url]" type="url" id="sc_footer_logo" value="<?php echo esc_attr( $settings['footer_logo_url'] ?? '' ); ?>" class="large-text" />
								<p class="description"><?php esc_html_e( 'Optional inverted or monochrome logo for dark footer backgrounds.', 'spicecraft-core' ); ?></p>
							</td>
						</tr>
					</table>

				<?php
				// TAB 4: Social Channels
				elseif ( 'social' === $active_tab ) :
					?>
					<table class="form-table" role="presentation">
						<tr>
							<th scope="row"><label for="sc_social_facebook"><?php esc_html_e( 'Facebook Page URL', 'spicecraft-core' ); ?></label></th>
							<td>
								<input name="<?php echo esc_attr( self::OPTION_NAME ); ?>[social_facebook]" type="url" id="sc_social_facebook" value="<?php echo esc_attr( $settings['social_facebook'] ?? '' ); ?>" class="regular-text" />
							</td>
						</tr>
						<tr>
							<th scope="row"><label for="sc_social_instagram"><?php esc_html_e( 'Instagram URL', 'spicecraft-core' ); ?></label></th>
							<td>
								<input name="<?php echo esc_attr( self::OPTION_NAME ); ?>[social_instagram]" type="url" id="sc_social_instagram" value="<?php echo esc_attr( $settings['social_instagram'] ?? '' ); ?>" class="regular-text" />
							</td>
						</tr>
						<tr>
							<th scope="row"><label for="sc_social_linkedin"><?php esc_html_e( 'LinkedIn Company Page', 'spicecraft-core' ); ?></label></th>
							<td>
								<input name="<?php echo esc_attr( self::OPTION_NAME ); ?>[social_linkedin]" type="url" id="sc_social_linkedin" value="<?php echo esc_attr( $settings['social_linkedin'] ?? '' ); ?>" class="regular-text" />
							</td>
						</tr>
						<tr>
							<th scope="row"><label for="sc_social_youtube"><?php esc_html_e( 'YouTube Channel', 'spicecraft-core' ); ?></label></th>
							<td>
								<input name="<?php echo esc_attr( self::OPTION_NAME ); ?>[social_youtube]" type="url" id="sc_social_youtube" value="<?php echo esc_attr( $settings['social_youtube'] ?? '' ); ?>" class="regular-text" />
							</td>
						</tr>
						<tr>
							<th scope="row"><label for="sc_social_pinterest"><?php esc_html_e( 'Pinterest URL', 'spicecraft-core' ); ?></label></th>
							<td>
								<input name="<?php echo esc_attr( self::OPTION_NAME ); ?>[social_pinterest]" type="url" id="sc_social_pinterest" value="<?php echo esc_attr( $settings['social_pinterest'] ?? '' ); ?>" class="regular-text" />
							</td>
						</tr>
						<tr>
							<th scope="row"><label for="sc_social_twitter"><?php esc_html_e( 'X / Twitter Profile', 'spicecraft-core' ); ?></label></th>
							<td>
								<input name="<?php echo esc_attr( self::OPTION_NAME ); ?>[social_twitter]" type="url" id="sc_social_twitter" value="<?php echo esc_attr( $settings['social_twitter'] ?? '' ); ?>" class="regular-text" />
							</td>
						</tr>
					</table>

				<?php
				// TAB 5: Regulatory & Compliance
				elseif ( 'regulatory' === $active_tab ) :
					?>
					<div class="notice notice-info inline">
						<p><?php esc_html_e( 'Regulatory disclosure fields are optional. Leave them empty if unassigned; the website will never display unverified compliance claims.', 'spicecraft-core' ); ?></p>
					</div>

					<table class="form-table" role="presentation">
						<tr>
							<th scope="row"><label for="sc_fssai"><?php esc_html_e( 'FSSAI License Number', 'spicecraft-core' ); ?></label></th>
							<td>
								<input name="<?php echo esc_attr( self::OPTION_NAME ); ?>[fssai_license]" type="text" id="sc_fssai" value="<?php echo esc_attr( $settings['fssai_license'] ?? '' ); ?>" class="regular-text" placeholder="e.g. 10012021000123" />
								<p class="description"><?php esc_html_e( 'Mandatory food safety compliance license for Indian food processing plants.', 'spicecraft-core' ); ?></p>
							</td>
						</tr>
						<tr>
							<th scope="row"><label for="sc_gst"><?php esc_html_e( 'GST Identification Number (GSTIN)', 'spicecraft-core' ); ?></label></th>
							<td>
								<input name="<?php echo esc_attr( self::OPTION_NAME ); ?>[gst_number]" type="text" id="sc_gst" value="<?php echo esc_attr( $settings['gst_number'] ?? '' ); ?>" class="regular-text" />
							</td>
						</tr>
						<tr>
							<th scope="row"><label for="sc_iec"><?php esc_html_e( 'Importer-Exporter Code (IEC)', 'spicecraft-core' ); ?></label></th>
							<td>
								<input name="<?php echo esc_attr( self::OPTION_NAME ); ?>[iec_code]" type="text" id="sc_iec" value="<?php echo esc_attr( $settings['iec_code'] ?? '' ); ?>" class="regular-text" />
							</td>
						</tr>
						<tr>
							<th scope="row"><label for="sc_certs_summary"><?php esc_html_e( 'Certifications Summary Note', 'spicecraft-core' ); ?></label></th>
							<td>
								<input name="<?php echo esc_attr( self::OPTION_NAME ); ?>[certifications_summary]" type="text" id="sc_certs_summary" value="<?php echo esc_attr( $settings['certifications_summary'] ?? '' ); ?>" class="large-text" placeholder="e.g. ISO 22000 | HACCP | HALAL Certified" />
								<p class="description"><?php esc_html_e( 'Displayed in the site footer only when populated.', 'spicecraft-core' ); ?></p>
							</td>
						</tr>
					</table>

				<?php
				// TAB 6: Footer & Legal
				elseif ( 'footer' === $active_tab ) :
					?>
					<table class="form-table" role="presentation">
						<tr>
							<th scope="row"><label for="sc_footer_desc"><?php esc_html_e( 'Footer Short Description', 'spicecraft-core' ); ?></label></th>
							<td>
								<textarea name="<?php echo esc_attr( self::OPTION_NAME ); ?>[footer_description]" id="sc_footer_desc" rows="3" class="large-text"><?php echo esc_textarea( $settings['footer_description'] ?? '' ); ?></textarea>
							</td>
						</tr>
						<tr>
							<th scope="row"><label for="sc_copyright"><?php esc_html_e( 'Custom Copyright Text', 'spicecraft-core' ); ?></label></th>
							<td>
								<input name="<?php echo esc_attr( self::OPTION_NAME ); ?>[footer_copyright]" type="text" id="sc_copyright" value="<?php echo esc_attr( $settings['footer_copyright'] ?? '' ); ?>" class="large-text" />
								<p class="description"><?php esc_html_e( 'Leave empty to automatically display: © [Current Year] [Site Name]. All rights reserved.', 'spicecraft-core' ); ?></p>
							</td>
						</tr>
						<tr>
							<th scope="row"><label for="sc_disclaimer"><?php esc_html_e( 'Footer Disclaimer / Tagline Note', 'spicecraft-core' ); ?></label></th>
							<td>
								<textarea name="<?php echo esc_attr( self::OPTION_NAME ); ?>[footer_disclaimer]" id="sc_disclaimer" rows="2" class="large-text"><?php echo esc_textarea( $settings['footer_disclaimer'] ?? '' ); ?></textarea>
							</td>
						</tr>
					</table>

				<?php endif; ?>

				<?php submit_button( __( 'Save Global Settings', 'spicecraft-core' ) ); ?>
			</form>
		</div>
		<?php
	}

	/**
	 * Render SpiceCraft Admin Overview & Navigation Page
	 *
	 * Provides a clean, native WordPress landing page guiding administrators
	 * to all content management and catalog configuration sections without
	 * heavy JS frameworks or duplicating core screens.
	 */
	public function render_overview_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		if ( class_exists( 'SpiceCraft_Dashboard' ) ) {
			SpiceCraft_Dashboard::render_overview_screen();
			return;
		}

		$products_count = wp_count_posts( 'product' );
		$published_products = isset( $products_count->publish ) ? $products_count->publish : 0;
		$pages_count = wp_count_posts( 'page' );
		$published_pages = isset( $pages_count->publish ) ? $pages_count->publish : 0;
		$posts_count = wp_count_posts( 'post' );
		$published_posts = isset( $posts_count->publish ) ? $posts_count->publish : 0;
		$enquiries_count = wp_count_posts( 'spicecraft_enquiry' );
		$total_enquiries = isset( $enquiries_count->publish ) ? $enquiries_count->publish : 0;
		$new_enquiries   = function_exists( 'spicecraft_count_new_enquiries' ) ? spicecraft_count_new_enquiries() : 0;
		?>
		<div class="wrap spicecraft-overview-wrap">
			<h1 class="wp-heading-inline"><?php esc_html_e( 'SpiceCraft CMS Overview', 'spicecraft-core' ); ?></h1>
			<hr class="wp-header-end" />

			<div class="notice notice-info inline" style="margin-top: 15px; border-left-color: #2b7a78; background: #fff; padding: 12px 15px;">
				<p style="margin: 0; font-size: 14px; line-height: 1.5;">
					<strong style="color: #2b7a78;"><?php esc_html_e( 'FMCG Catalog Mode Active:', 'spicecraft-core' ); ?></strong>
					<?php esc_html_e( 'Online purchasing, shopping cart, and checkout are disabled. This website functions as a premium product discovery showcase with direct WhatsApp and Trade Enquiry routing.', 'spicecraft-core' ); ?>
				</p>
			</div>

			<style>
				.sc-overview-grid {
					display: grid;
					grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
					gap: 20px;
					margin-top: 20px;
				}
				.sc-overview-card {
					background: #fff;
					border: 1px solid #c3c4c7;
					border-radius: 4px;
					padding: 20px;
					box-shadow: 0 1px 1px rgba(0,0,0,.04);
					display: flex;
					flex-direction: column;
					justify-content: space-between;
				}
				.sc-overview-card h3 {
					margin-top: 0;
					margin-bottom: 8px;
					font-size: 16px;
					display: flex;
					align-items: center;
					gap: 8px;
				}
				.sc-overview-card p {
					color: #646970;
					font-size: 13px;
					line-height: 1.5;
					margin: 0 0 16px 0;
					flex-grow: 1;
				}
				.sc-overview-card .sc-card-actions {
					margin-top: auto;
					display: flex;
					gap: 8px;
					flex-wrap: wrap;
				}
				.sc-overview-section-title {
					margin: 30px 0 10px 0;
					font-size: 18px;
					font-weight: 600;
					color: #1d2327;
					border-bottom: 1px solid #dcdcde;
					padding-bottom: 8px;
				}
			</style>

			<h2 class="sc-overview-section-title"><?php esc_html_e( 'Global & Brand Configuration', 'spicecraft-core' ); ?></h2>
			<div class="sc-overview-grid">
				<div class="sc-overview-card">
					<div>
						<h3><span class="dashicons dashicons-admin-settings" style="color:#2b7a78;"></span> <?php esc_html_e( 'Global CMS Settings', 'spicecraft-core' ); ?></h3>
						<p><?php esc_html_e( 'Manage brand identity, trade desk contacts, WhatsApp lead routing templates, regulatory credentials (FSSAI, GST, IEC), and custom footer copyright.', 'spicecraft-core' ); ?></p>
					</div>
					<div class="sc-card-actions">
						<a href="<?php echo esc_url( admin_url( 'admin.php?page=spicecraft-settings' ) ); ?>" class="button button-primary"><?php esc_html_e( 'Configure Global Settings', 'spicecraft-core' ); ?> &rarr;</a>
					</div>
				</div>

				<div class="sc-overview-card">
					<div>
						<h3><span class="dashicons dashicons-format-image" style="color:#2b7a78;"></span> <?php esc_html_e( 'Site Identity & Brand Logo', 'spicecraft-core' ); ?></h3>
						<p><?php esc_html_e( 'Upload and preview the official brand logo, site favicon / app icon, site title, and marketing tagline.', 'spicecraft-core' ); ?></p>
					</div>
					<div class="sc-card-actions">
						<a href="<?php echo esc_url( admin_url( 'customize.php?autofocus[section]=title_tagline' ) ); ?>" class="button button-secondary"><?php esc_html_e( 'Customizer Site Identity', 'spicecraft-core' ); ?></a>
					</div>
				</div>

				<div class="sc-overview-card">
					<div>
						<h3><span class="dashicons dashicons-menu" style="color:#2b7a78;"></span> <?php esc_html_e( 'Header & Footer Menus', 'spicecraft-core' ); ?></h3>
						<p><?php esc_html_e( 'Control navigation links for Desktop Primary Navigation, Mobile Menu Drawer, and Footer Multi-Column Links.', 'spicecraft-core' ); ?></p>
					</div>
					<div class="sc-card-actions">
						<a href="<?php echo esc_url( admin_url( 'nav-menus.php' ) ); ?>" class="button button-secondary"><?php esc_html_e( 'Manage Menus', 'spicecraft-core' ); ?></a>
					</div>
				</div>
			</div>

			<h2 class="sc-overview-section-title"><?php esc_html_e( 'Product Catalog & FMCG Architecture', 'spicecraft-core' ); ?></h2>
			<div class="sc-overview-grid">
				<div class="sc-overview-card">
					<div>
						<h3><span class="dashicons dashicons-products" style="color:#2b7a78;"></span> <?php esc_html_e( 'Product Catalog', 'spicecraft-core' ); ?> (<?php echo esc_html( $published_products ); ?>)</h3>
						<p><?php esc_html_e( 'Manage whole spices, ground powders, and blended masalas. Edit FMCG highlights, nutritional tables, ingredients, storage directions, and packaging sizes.', 'spicecraft-core' ); ?></p>
					</div>
					<div class="sc-card-actions">
						<a href="<?php echo esc_url( admin_url( 'edit.php?post_type=product' ) ); ?>" class="button button-secondary"><?php esc_html_e( 'All Products', 'spicecraft-core' ); ?></a>
						<a href="<?php echo esc_url( admin_url( 'post-new.php?post_type=product' ) ); ?>" class="button button-secondary"><?php esc_html_e( '+ Add New Product', 'spicecraft-core' ); ?></a>
					</div>
				</div>

				<div class="sc-overview-card">
					<div>
						<h3><span class="dashicons dashicons-category" style="color:#2b7a78;"></span> <?php esc_html_e( 'Product Categories', 'spicecraft-core' ); ?></h3>
						<p><?php esc_html_e( 'Organize catalog into categories and subcategories (e.g., Whole Spices, Ground Spices, Blended Masalas, Pure Herbs).', 'spicecraft-core' ); ?></p>
					</div>
					<div class="sc-card-actions">
						<a href="<?php echo esc_url( admin_url( 'edit-tags.php?taxonomy=product_cat&post_type=product' ) ); ?>" class="button button-secondary"><?php esc_html_e( 'Manage Categories', 'spicecraft-core' ); ?></a>
					</div>
				</div>

				<div class="sc-overview-card">
					<div>
						<h3><span class="dashicons dashicons-awards" style="color:#2b7a78;"></span> <?php esc_html_e( 'Product Certifications', 'spicecraft-core' ); ?></h3>
						<p><?php esc_html_e( 'Manage quality assurance and food safety standards (ISO 22000, HACCP, Halal, FSSAI, Spices Board of India) assigned to products.', 'spicecraft-core' ); ?></p>
					</div>
					<div class="sc-card-actions">
						<a href="<?php echo esc_url( admin_url( 'edit-tags.php?taxonomy=spicecraft_certification&post_type=product' ) ); ?>" class="button button-secondary"><?php esc_html_e( 'Manage Certifications', 'spicecraft-core' ); ?></a>
					</div>
				</div>

				<div class="sc-overview-card">
					<div>
						<h3><span class="dashicons dashicons-tag" style="color:#2b7a78;"></span> <?php esc_html_e( 'Pack Sizes & Attributes', 'spicecraft-core' ); ?></h3>
						<p><?php esc_html_e( 'Configure global product attributes such as Pack Size (e.g., 50g, 100g, 250g, 500g, 1kg, 25kg Bulk Bags) used across products.', 'spicecraft-core' ); ?></p>
					</div>
					<div class="sc-card-actions">
						<a href="<?php echo esc_url( admin_url( 'edit.php?post_type=product&page=product_attributes' ) ); ?>" class="button button-secondary"><?php esc_html_e( 'Product Attributes', 'spicecraft-core' ); ?></a>
					</div>
				</div>
			</div>

			<h2 class="sc-overview-section-title"><?php esc_html_e( 'Website Content & Customer Engagement', 'spicecraft-core' ); ?></h2>
			<div class="sc-overview-grid">
				<div class="sc-overview-card">
					<div>
						<h3><span class="dashicons dashicons-admin-page" style="color:#2b7a78;"></span> <?php esc_html_e( 'Static Pages', 'spicecraft-core' ); ?> (<?php echo esc_html( $published_pages ); ?>)</h3>
						<p><?php esc_html_e( 'Edit primary brand pages such as About Us, Manufacturing Infrastructure, Quality Assurance, and Contact Desks.', 'spicecraft-core' ); ?></p>
					</div>
					<div class="sc-card-actions">
						<a href="<?php echo esc_url( admin_url( 'edit.php?post_type=page' ) ); ?>" class="button button-secondary"><?php esc_html_e( 'Manage Pages', 'spicecraft-core' ); ?></a>
					</div>
				</div>

				<div class="sc-overview-card">
					<div>
						<h3><span class="dashicons dashicons-admin-post" style="color:#2b7a78;"></span> <?php esc_html_e( 'Articles & Insights', 'spicecraft-core' ); ?> (<?php echo esc_html( $published_posts ); ?>)</h3>
						<p><?php esc_html_e( 'Publish spice knowledge articles, culinary pairing ideas, crop harvest updates, and export industry news.', 'spicecraft-core' ); ?></p>
					</div>
					<div class="sc-card-actions">
						<a href="<?php echo esc_url( admin_url( 'edit.php' ) ); ?>" class="button button-secondary"><?php esc_html_e( 'Manage Posts', 'spicecraft-core' ); ?></a>
					</div>
				</div>

				<div class="sc-overview-card">
					<div>
						<h3><span class="dashicons dashicons-admin-media" style="color:#2b7a78;"></span> <?php esc_html_e( 'Media Library', 'spicecraft-core' ); ?></h3>
						<p><?php esc_html_e( 'Upload packaging renders, laboratory certificates, facility photography, and downloadable product specification sheets.', 'spicecraft-core' ); ?></p>
					</div>
					<div class="sc-card-actions">
						<a href="<?php echo esc_url( admin_url( 'upload.php' ) ); ?>" class="button button-secondary"><?php esc_html_e( 'Media Library', 'spicecraft-core' ); ?></a>
					</div>
				</div>

				<div class="sc-overview-card">
					<div>
						<h3><span class="dashicons dashicons-admin-comments" style="color:#2b7a78;"></span> <?php esc_html_e( 'Product Reviews & Comments', 'spicecraft-core' ); ?></h3>
						<p><?php esc_html_e( 'Moderate and manage verified customer reviews and culinary feedback submitted across product detail pages.', 'spicecraft-core' ); ?></p>
					</div>
					<div class="sc-card-actions">
						<a href="<?php echo esc_url( admin_url( 'edit-comments.php' ) ); ?>" class="button button-secondary"><?php esc_html_e( 'Moderate Reviews', 'spicecraft-core' ); ?></a>
					</div>
				</div>
			</div>

			<h2 class="sc-overview-section-title"><?php esc_html_e( 'Lead Management & Customer Enquiries', 'spicecraft-core' ); ?></h2>
			<div class="sc-overview-grid">
				<div class="sc-overview-card" style="border-left: 4px solid #b83d27;">
					<div>
						<h3>
							<span class="dashicons dashicons-email-alt" style="color:#b83d27;"></span>
							<?php esc_html_e( 'Product Enquiries & Leads', 'spicecraft-core' ); ?>
							<?php if ( $new_enquiries > 0 ) : ?>
								<span class="update-plugins count-<?php echo esc_attr( $new_enquiries ); ?>" style="background:#b83d27; color:#fff; border-radius:10px; padding:2px 8px; font-size:11px; font-weight:700;">
									<?php echo esc_html( $new_enquiries ); ?> <?php esc_html_e( 'New', 'spicecraft-core' ); ?>
								</span>
							<?php endif; ?>
						</h3>
						<p><?php esc_html_e( 'Manage incoming customer leads, institutional bulk supply requests, and international export enquiries. Track lead status, record private internal notes, and export CSV reports.', 'spicecraft-core' ); ?></p>
					</div>
					<div class="sc-card-actions">
						<a href="<?php echo esc_url( admin_url( 'edit.php?post_type=spicecraft_enquiry' ) ); ?>" class="button button-primary"><?php esc_html_e( 'All Enquiries', 'spicecraft-core' ); ?> &rarr;</a>
						<?php if ( $new_enquiries > 0 ) : ?>
							<a href="<?php echo esc_url( admin_url( 'edit.php?post_type=spicecraft_enquiry&enquiry_status=new' ) ); ?>" class="button button-secondary" style="color:#b83d27; border-color:#b83d27;"><?php esc_html_e( 'Filter New Leads', 'spicecraft-core' ); ?> (<?php echo esc_html( $new_enquiries ); ?>)</a>
						<?php endif; ?>
						<a href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=spicecraft_export_enquiries_csv' ), 'spicecraft_export_enquiries_csv_nonce' ) ); ?>" class="button button-secondary"><?php esc_html_e( 'Export CSV', 'spicecraft-core' ); ?></a>
					</div>
				</div>
			</div>
		</div>
		<?php
	}
}
