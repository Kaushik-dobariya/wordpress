<?php
/**
 * SpiceCraft Core - Global Certification Display Settings
 *
 * Provides administrative controls for configuring the public Certifications
 * archive, filtering, card display flags, automated expiry rules, and conversion CTA.
 *
 * @package SpiceCraft_Core
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class SpiceCraft_Certification_Settings {

	const OPTION_NAME = 'spicecraft_certification_settings';

	/**
	 * Singleton instance
	 *
	 * @var SpiceCraft_Certification_Settings|null
	 */
	private static $instance = null;

	/**
	 * Get singleton instance
	 *
	 * @return SpiceCraft_Certification_Settings
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
		add_action( 'admin_menu', array( $this, 'register_admin_menu' ), 26 );
		add_action( 'admin_init', array( $this, 'register_settings' ) );
	}

	/**
	 * Register submenu under SpiceCraft
	 */
	public function register_admin_menu() {
		add_submenu_page(
			'spicecraft-overview',
			__( 'Certification Display Settings', 'spicecraft-core' ),
			__( 'Certifications Display', 'spicecraft-core' ),
			'manage_options',
			'spicecraft-certification-settings',
			array( $this, 'render_settings_page' )
		);
	}

	/**
	 * Register settings in WordPress settings API
	 */
	public function register_settings() {
		register_setting(
			'spicecraft_cert_settings_group',
			self::OPTION_NAME,
			array(
				'sanitize_callback' => 'spicecraft_update_certification_settings_callback',
				'default'           => function_exists( 'spicecraft_get_certification_default_settings' ) ? spicecraft_get_certification_default_settings() : array(),
			)
		);
	}

	/**
	 * Render settings page with 4 logical tabs
	 */
	public function render_settings_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have permission to access this page.', 'spicecraft-core' ) );
		}

		$settings = function_exists( 'spicecraft_get_certification_settings' )
			? spicecraft_get_certification_settings()
			: array();

		$current_tab = isset( $_GET['tab'] ) ? sanitize_key( $_GET['tab'] ) : 'archive';
		$allowed_tabs = array( 'archive', 'toggles', 'expiry', 'cta' );
		if ( ! in_array( $current_tab, $allowed_tabs, true ) ) {
			$current_tab = 'archive';
		}
		?>
		<div class="wrap spicecraft-settings-wrap">
			<h1><?php esc_html_e( 'Certification Display & Trust Settings', 'spicecraft-core' ); ?></h1>
			<p class="description">
				<?php esc_html_e( 'Configure public archive presentation, visibility toggles, automated expiry detection, and B2B inquiry actions.', 'spicecraft-core' ); ?>
			</p>

			<div style="margin: 16px 0 20px 0; display: flex; gap: 10px;">
				<a href="<?php echo esc_url( admin_url( 'edit-tags.php?taxonomy=spicecraft_certification&post_type=product' ) ); ?>" class="button button-primary">
					<?php esc_html_e( '📋 Manage All Certifications', 'spicecraft-core' ); ?>
				</a>
				<a href="<?php echo esc_url( home_url( '/certifications/' ) ); ?>" target="_blank" class="button button-secondary">
					<?php esc_html_e( '🌐 View Public Archive (/certifications/)', 'spicecraft-core' ); ?>
				</a>
			</div>

			<h2 class="nav-tab-wrapper">
				<a href="<?php echo esc_url( add_query_arg( 'tab', 'archive' ) ); ?>" class="nav-tab <?php echo 'archive' === $current_tab ? 'nav-tab-active' : ''; ?>">
					<?php esc_html_e( '1. Archive Presentation', 'spicecraft-core' ); ?>
				</a>
				<a href="<?php echo esc_url( add_query_arg( 'tab', 'toggles' ) ); ?>" class="nav-tab <?php echo 'toggles' === $current_tab ? 'nav-tab-active' : ''; ?>">
					<?php esc_html_e( '2. Display Toggles', 'spicecraft-core' ); ?>
				</a>
				<a href="<?php echo esc_url( add_query_arg( 'tab', 'expiry' ) ); ?>" class="nav-tab <?php echo 'expiry' === $current_tab ? 'nav-tab-active' : ''; ?>">
					<?php esc_html_e( '3. Expiry & Status Rules', 'spicecraft-core' ); ?>
				</a>
				<a href="<?php echo esc_url( add_query_arg( 'tab', 'cta' ) ); ?>" class="nav-tab <?php echo 'cta' === $current_tab ? 'nav-tab-active' : ''; ?>">
					<?php esc_html_e( '4. Commercial CTA', 'spicecraft-core' ); ?>
				</a>
			</h2>

			<form method="post" action="options.php">
				<?php
				settings_fields( 'spicecraft_cert_settings_group' );

				// Preserve values across tabs via hidden inputs
				foreach ( $settings as $key => $val ) {
					if ( is_array( $val ) ) {
						continue;
					}
					// Only output hidden if not currently edited on this tab
					if ( ! $this->is_key_on_tab( $key, $current_tab ) ) {
						echo '<input type="hidden" name="' . esc_attr( self::OPTION_NAME . '[' . $key . ']' ) . '" value="' . esc_attr( $val ) . '">';
					}
				}

				switch ( $current_tab ) {
					case 'archive':
						$this->render_archive_tab( $settings );
						break;
					case 'toggles':
						$this->render_toggles_tab( $settings );
						break;
					case 'expiry':
						$this->render_expiry_tab( $settings );
						break;
					case 'cta':
						$this->render_cta_tab( $settings );
						break;
				}

				submit_button( __( 'Save Certification Settings', 'spicecraft-core' ) );
				?>
			</form>
		</div>
		<?php
	}

	/**
	 * Helper: Check if setting key belongs to given tab.
	 *
	 * @param string $key
	 * @param string $tab
	 * @return bool
	 */
	private function is_key_on_tab( $key, $tab ) {
		$tab_map = array(
			'archive' => array( 'archive_enabled', 'heading', 'eyebrow', 'intro', 'desktop_image_id', 'mobile_image_id', 'default_sort' ),
			'toggles' => array( 'show_filters', 'show_status', 'show_authority', 'show_cert_number', 'show_validity', 'show_verification_link', 'show_documents', 'show_related_products', 'show_related_categories' ),
			'expiry'  => array( 'show_expired', 'show_archived', 'auto_expiry_detection', 'expiry_warning_threshold_days' ),
			'cta'     => array( 'cta_enable', 'cta_heading', 'cta_description', 'cta_whatsapp_enable', 'cta_email_enable' ),
		);

		return isset( $tab_map[ $tab ] ) && in_array( $key, $tab_map[ $tab ], true );
	}

	/**
	 * Tab 1: Archive Presentation
	 */
	private function render_archive_tab( $settings ) {
		?>
		<table class="form-table" role="presentation">
			<tr>
				<th scope="row"><label for="archive_enabled"><?php esc_html_e( 'Public Archive Enabled', 'spicecraft-core' ); ?></label></th>
				<td>
					<label>
						<input type="checkbox" name="<?php echo esc_attr( self::OPTION_NAME . '[archive_enabled]' ); ?>" id="archive_enabled" value="1" <?php checked( ! empty( $settings['archive_enabled'] ) ); ?>>
						<?php esc_html_e( 'Enable the public /certifications/ directory', 'spicecraft-core' ); ?>
					</label>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="eyebrow"><?php esc_html_e( 'Archive Eyebrow', 'spicecraft-core' ); ?></label></th>
				<td>
					<input type="text" name="<?php echo esc_attr( self::OPTION_NAME . '[eyebrow]' ); ?>" id="eyebrow" value="<?php echo esc_attr( $settings['eyebrow'] ?? '' ); ?>" class="regular-text">
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="heading"><?php esc_html_e( 'Archive H1 Heading', 'spicecraft-core' ); ?></label></th>
				<td>
					<input type="text" name="<?php echo esc_attr( self::OPTION_NAME . '[heading]' ); ?>" id="heading" value="<?php echo esc_attr( $settings['heading'] ?? '' ); ?>" class="regular-text">
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="intro"><?php esc_html_e( 'Introduction Narrative', 'spicecraft-core' ); ?></label></th>
				<td>
					<textarea name="<?php echo esc_attr( self::OPTION_NAME . '[intro]' ); ?>" id="intro" rows="4" class="large-text"><?php echo esc_textarea( $settings['intro'] ?? '' ); ?></textarea>
				</td>
			</tr>
			<tr>
				<th scope="row"><label><?php esc_html_e( 'Hero Desktop Image', 'spicecraft-core' ); ?></label></th>
				<td>
					<?php
					if ( function_exists( 'spicecraft_render_media_uploader' ) ) {
						spicecraft_render_media_uploader(
							self::OPTION_NAME . '[desktop_image_id]',
							absint( $settings['desktop_image_id'] ?? 0 ),
							__( 'Select Hero Desktop Image', 'spicecraft-core' ),
							__( 'Optional desktop banner image for /certifications/. If absent, clean typography hero is rendered.', 'spicecraft-core' )
						);
					}
					?>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="default_sort"><?php esc_html_e( 'Default Sort Order', 'spicecraft-core' ); ?></label></th>
				<td>
					<select name="<?php echo esc_attr( self::OPTION_NAME . '[default_sort]' ); ?>" id="default_sort">
						<option value="order" <?php selected( ( $settings['default_sort'] ?? '' ), 'order' ); ?>><?php esc_html_e( 'Admin Display Order (Ascending)', 'spicecraft-core' ); ?></option>
						<option value="title_asc" <?php selected( ( $settings['default_sort'] ?? '' ), 'title_asc' ); ?>><?php esc_html_e( 'Certification Name (A to Z)', 'spicecraft-core' ); ?></option>
						<option value="issue_date_desc" <?php selected( ( $settings['default_sort'] ?? '' ), 'issue_date_desc' ); ?>><?php esc_html_e( 'Newest Issued First', 'spicecraft-core' ); ?></option>
						<option value="expiry_date_asc" <?php selected( ( $settings['default_sort'] ?? '' ), 'expiry_date_asc' ); ?>><?php esc_html_e( 'Expiry Date (Nearest First)', 'spicecraft-core' ); ?></option>
					</select>
				</td>
			</tr>
		</table>
		<?php
	}

	/**
	 * Tab 2: Display Toggles
	 */
	private function render_toggles_tab( $settings ) {
		$toggles = array(
			'show_filters'            => __( 'Show Status & Category Filter Bar (Visible when ≥ 3 public records exist)', 'spicecraft-core' ),
			'show_status'             => __( 'Display Status Badges on Cards & Detail Page', 'spicecraft-core' ),
			'show_authority'          => __( 'Display Issuing Authority & Registrar', 'spicecraft-core' ),
			'show_cert_number'        => __( 'Display Certificate / Registration Numbers', 'spicecraft-core' ),
			'show_validity'           => __( 'Display Issue, Valid From, and Expiry Dates', 'spicecraft-core' ),
			'show_verification_link'  => __( 'Display Official Verification Links (When configured)', 'spicecraft-core' ),
			'show_documents'          => __( 'Display Public PDF Certificate Download / View Buttons', 'spicecraft-core' ),
			'show_related_products'   => __( 'Display Associated WooCommerce Product Cards', 'spicecraft-core' ),
			'show_related_categories' => __( 'Display Associated Product Category Links', 'spicecraft-core' ),
		);
		?>
		<table class="form-table" role="presentation">
			<?php foreach ( $toggles as $key => $label ) : ?>
				<tr>
					<th scope="row"><label for="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $label ); ?></label></th>
					<td>
						<label>
							<input type="checkbox" name="<?php echo esc_attr( self::OPTION_NAME . '[' . $key . ']' ); ?>" id="<?php echo esc_attr( $key ); ?>" value="1" <?php checked( ! empty( $settings[ $key ] ) ); ?>>
							<?php esc_html_e( 'Enable', 'spicecraft-core' ); ?>
						</label>
					</td>
				</tr>
			<?php endforeach; ?>
		</table>
		<?php
	}

	/**
	 * Tab 3: Expiry & Status Rules
	 */
	private function render_expiry_tab( $settings ) {
		?>
		<table class="form-table" role="presentation">
			<tr>
				<th scope="row"><label for="auto_expiry_detection"><?php esc_html_e( 'Global Automatic Expiry Detection', 'spicecraft-core' ); ?></label></th>
				<td>
					<label>
						<input type="checkbox" name="<?php echo esc_attr( self::OPTION_NAME . '[auto_expiry_detection]' ); ?>" id="auto_expiry_detection" value="1" <?php checked( ! empty( $settings['auto_expiry_detection'] ) ); ?>>
						<?php esc_html_e( 'Automatically treat all certifications with passed Expiry Dates as "Expired"', 'spicecraft-core' ); ?>
					</label>
					<p class="description"><?php esc_html_e( 'Default is disabled. Active status is NEVER assumed purely because an expiry date is in the future.', 'spicecraft-core' ); ?></p>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="show_expired"><?php esc_html_e( 'Publicly Show Expired Certifications', 'spicecraft-core' ); ?></label></th>
				<td>
					<label>
						<input type="checkbox" name="<?php echo esc_attr( self::OPTION_NAME . '[show_expired]' ); ?>" id="show_expired" value="1" <?php checked( ! empty( $settings['show_expired'] ) ); ?>>
						<?php esc_html_e( 'Allow expired certifications to appear in public archive with an "Expired" status badge', 'spicecraft-core' ); ?>
					</label>
					<p class="description"><?php esc_html_e( 'If disabled, expired credentials are automatically hidden from visitors.', 'spicecraft-core' ); ?></p>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="show_archived"><?php esc_html_e( 'Publicly Show Archived Certifications', 'spicecraft-core' ); ?></label></th>
				<td>
					<label>
						<input type="checkbox" name="<?php echo esc_attr( self::OPTION_NAME . '[show_archived]' ); ?>" id="show_archived" value="1" <?php checked( ! empty( $settings['show_archived'] ) ); ?>>
						<?php esc_html_e( 'Allow archived records to be viewable on public frontend', 'spicecraft-core' ); ?>
					</label>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="expiry_warning_threshold_days"><?php esc_html_e( 'Admin Expiry Warning Threshold', 'spicecraft-core' ); ?></label></th>
				<td>
					<input type="number" name="<?php echo esc_attr( self::OPTION_NAME . '[expiry_warning_threshold_days]' ); ?>" id="expiry_warning_threshold_days" value="<?php echo esc_attr( $settings['expiry_warning_threshold_days'] ?? 90 ); ?>" min="1" step="1" style="width: 100px;">
					<span><?php esc_html_e( 'Days before expiry (Default: 90 days). Admin management notice only.', 'spicecraft-core' ); ?></span>
				</td>
			</tr>
		</table>
		<?php
	}

	/**
	 * Tab 4: Commercial CTA
	 */
	private function render_cta_tab( $settings ) {
		?>
		<table class="form-table" role="presentation">
			<tr>
				<th scope="row"><label for="cta_enable"><?php esc_html_e( 'Enable Commercial CTA', 'spicecraft-core' ); ?></label></th>
				<td>
					<label>
						<input type="checkbox" name="<?php echo esc_attr( self::OPTION_NAME . '[cta_enable]' ); ?>" id="cta_enable" value="1" <?php checked( ! empty( $settings['cta_enable'] ) ); ?>>
						<?php esc_html_e( 'Display B2B Technical / COA Inquiry block at the bottom of Certifications pages', 'spicecraft-core' ); ?>
					</label>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="cta_heading"><?php esc_html_e( 'CTA Heading', 'spicecraft-core' ); ?></label></th>
				<td>
					<input type="text" name="<?php echo esc_attr( self::OPTION_NAME . '[cta_heading]' ); ?>" id="cta_heading" value="<?php echo esc_attr( $settings['cta_heading'] ?? '' ); ?>" class="regular-text">
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="cta_description"><?php esc_html_e( 'CTA Description', 'spicecraft-core' ); ?></label></th>
				<td>
					<textarea name="<?php echo esc_attr( self::OPTION_NAME . '[cta_description]' ); ?>" id="cta_description" rows="3" class="large-text"><?php echo esc_textarea( $settings['cta_description'] ?? '' ); ?></textarea>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="cta_whatsapp_enable"><?php esc_html_e( 'WhatsApp Action', 'spicecraft-core' ); ?></label></th>
				<td>
					<label>
						<input type="checkbox" name="<?php echo esc_attr( self::OPTION_NAME . '[cta_whatsapp_enable]' ); ?>" id="cta_whatsapp_enable" value="1" <?php checked( ! empty( $settings['cta_whatsapp_enable'] ) ); ?>>
						<?php esc_html_e( 'Show WhatsApp inquiry button (Number read from Global Settings)', 'spicecraft-core' ); ?>
					</label>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="cta_email_enable"><?php esc_html_e( 'Email Action', 'spicecraft-core' ); ?></label></th>
				<td>
					<label>
						<input type="checkbox" name="<?php echo esc_attr( self::OPTION_NAME . '[cta_email_enable]' ); ?>" id="cta_email_enable" value="1" <?php checked( ! empty( $settings['cta_email_enable'] ) ); ?>>
						<?php esc_html_e( 'Show Trade Desk email enquiry button (Email read from Global Settings)', 'spicecraft-core' ); ?>
					</label>
				</td>
			</tr>
		</table>
		<?php
	}
}

/**
 * Settings API sanitization callback wrapper
 *
 * @param array $input
 * @return array
 */
function spicecraft_update_certification_settings_callback( $input ) {
	$current = function_exists( 'spicecraft_get_certification_settings' ) ? spicecraft_get_certification_settings() : array();
	$merged  = wp_parse_args( (array) $input, $current );

	// Toggles that may be omitted if unchecked on the active tab
	$current_tab = isset( $_POST['_wp_http_referer'] ) ? wp_parse_args( parse_url( $_POST['_wp_http_referer'], PHP_URL_QUERY ), array( 'tab' => 'archive' ) )['tab'] : 'archive';

	if ( 'archive' === $current_tab && ! isset( $input['archive_enabled'] ) ) {
		$merged['archive_enabled'] = 0;
	}
	if ( 'toggles' === $current_tab ) {
		$toggle_keys = array( 'show_filters', 'show_status', 'show_authority', 'show_cert_number', 'show_validity', 'show_verification_link', 'show_documents', 'show_related_products', 'show_related_categories' );
		foreach ( $toggle_keys as $tk ) {
			$merged[ $tk ] = ! empty( $input[ $tk ] ) ? 1 : 0;
		}
	}
	if ( 'expiry' === $current_tab ) {
		$merged['auto_expiry_detection'] = ! empty( $input['auto_expiry_detection'] ) ? 1 : 0;
		$merged['show_expired']          = ! empty( $input['show_expired'] ) ? 1 : 0;
		$merged['show_archived']         = ! empty( $input['show_archived'] ) ? 1 : 0;
	}
	if ( 'cta' === $current_tab ) {
		$merged['cta_enable']          = ! empty( $input['cta_enable'] ) ? 1 : 0;
		$merged['cta_whatsapp_enable'] = ! empty( $input['cta_whatsapp_enable'] ) ? 1 : 0;
		$merged['cta_email_enable']    = ! empty( $input['cta_email_enable'] ) ? 1 : 0;
	}

	spicecraft_update_certification_settings( $merged );
	return spicecraft_get_certification_settings();
}
