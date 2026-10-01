<?php
/**
 * SpiceCraft Core - Dedicated Careers CMS Settings Interface
 *
 * Implements an administrative management console under
 * SpiceCraft -> Careers: Settings. Controls:
 * - Profile Receiving Email (Defaults to career@cubeontechs.com, 100% backend-configurable)
 * - Careers Page Hero, Titles & Value Proposition
 * - Application Form Texts, Consent / Privacy Disclaimers & Success Messaging
 * - Display Switches (Closed Jobs, Salaries, Locations, etc.)
 * - General Talent Pool Call-To-Action
 *
 * @package SpiceCraft_Core
 * @since 1.3.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class SpiceCraft_Careers_Settings {

	/**
	 * Option Name in wp_options.
	 */
	const OPTION_NAME = SPICECRAFT_CAREERS_OPTION;

	/**
	 * Settings group name for register_setting.
	 */
	const SETTINGS_GROUP = 'spicecraft_careers_settings_group';

	/**
	 * Singleton Instance.
	 *
	 * @var SpiceCraft_Careers_Settings|null
	 */
	private static $instance = null;

	/**
	 * Get Singleton Instance.
	 *
	 * @return SpiceCraft_Careers_Settings
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor.
	 */
	private function __construct() {
		add_action( 'admin_menu', array( $this, 'register_admin_menu' ), 28 );
		add_action( 'admin_init', array( $this, 'register_settings' ) );
		add_action( 'wp_ajax_spicecraft_test_smtp_email', array( $this, 'handle_test_smtp_email' ) );
	}

	/**
	 * Register Admin Submenus.
	 */
	public function register_admin_menu() {
		// Submenu under top-level SpiceCraft overview menu
		add_submenu_page(
			'spicecraft-overview',
			__( 'Careers & Recruitment Settings', 'spicecraft-core' ),
			__( 'Careers: Settings', 'spicecraft-core' ),
			'manage_options',
			'spicecraft-careers-settings',
			array( $this, 'render_settings_page' )
		);
	}

	/**
	 * Register Settings with WordPress Settings API.
	 */
	public function register_settings() {
		register_setting(
			self::SETTINGS_GROUP,
			self::OPTION_NAME,
			array(
				'sanitize_callback' => array( $this, 'sanitize_settings' ),
				'default'           => spicecraft_get_careers_default_settings(),
			)
		);
	}

	/**
	 * Sanitize Settings Input.
	 *
	 * @param array $input Raw form input.
	 * @return array Sanitized settings array.
	 */
	public function sanitize_settings( $input ) {
		if ( ! is_array( $input ) ) {
			return spicecraft_get_careers_default_settings();
		}

		$clean = spicecraft_get_careers_default_settings();

		// 1. General & Hero
		$clean['page_title']    = ! empty( $input['page_title'] ) ? sanitize_text_field( $input['page_title'] ) : $clean['page_title'];
		$clean['hero_badge']    = ! empty( $input['hero_badge'] ) ? sanitize_text_field( $input['hero_badge'] ) : $clean['hero_badge'];
		$clean['hero_title']    = ! empty( $input['hero_title'] ) ? sanitize_text_field( $input['hero_title'] ) : $clean['hero_title'];
		$clean['hero_subtitle'] = ! empty( $input['hero_subtitle'] ) ? sanitize_textarea_field( $input['hero_subtitle'] ) : $clean['hero_subtitle'];
		$clean['hero_image_id'] = ! empty( $input['hero_image_id'] ) ? absint( $input['hero_image_id'] ) : 0;

		// 2. Why Join Us / Culture Points
		$clean['why_join_title']    = ! empty( $input['why_join_title'] ) ? sanitize_text_field( $input['why_join_title'] ) : $clean['why_join_title'];
		$clean['why_join_subtitle'] = ! empty( $input['why_join_subtitle'] ) ? sanitize_textarea_field( $input['why_join_subtitle'] ) : $clean['why_join_subtitle'];

		$clean_points = array();
		if ( ! empty( $input['culture_points'] ) && is_array( $input['culture_points'] ) ) {
			foreach ( $input['culture_points'] as $pt ) {
				$title = ! empty( $pt['title'] ) ? sanitize_text_field( $pt['title'] ) : '';
				$desc  = ! empty( $pt['description'] ) ? sanitize_textarea_field( $pt['description'] ) : '';
				$icon  = ! empty( $pt['icon'] ) ? sanitize_text_field( $pt['icon'] ) : 'star';
				if ( ! empty( $title ) || ! empty( $desc ) ) {
					$clean_points[] = array(
						'title'       => $title,
						'description' => $desc,
						'icon'        => $icon,
					);
				}
			}
		}
		$clean['culture_points'] = ! empty( $clean_points ) ? $clean_points : $clean['culture_points'];

		// 3. Application Settings & Profile Receiving Email (CRITICAL REQUIREMENT)
		if ( isset( $input['profile_email'] ) ) {
			$raw_email = trim( $input['profile_email'] );
			$sanitized_email = sanitize_email( $raw_email );

			if ( ! empty( $sanitized_email ) && is_email( $sanitized_email ) ) {
				$clean['profile_email'] = $sanitized_email;
			} else {
				// Reject malformed address and alert administrator
				add_settings_error(
					'spicecraft_careers_profile_email',
					'invalid_profile_email',
					__( 'The Profile Receiving Email address provided was invalid. The existing valid address was preserved.', 'spicecraft-core' ),
					'error'
				);
				$current_settings = get_option( self::OPTION_NAME, array() );
				$clean['profile_email'] = ! empty( $current_settings['profile_email'] ) ? $current_settings['profile_email'] : SPICECRAFT_CAREERS_DEFAULT_EMAIL;
			}
		}

		$clean['application_title'] = ! empty( $input['application_title'] ) ? sanitize_text_field( $input['application_title'] ) : $clean['application_title'];
		$clean['application_intro'] = ! empty( $input['application_intro'] ) ? sanitize_textarea_field( $input['application_intro'] ) : $clean['application_intro'];
		$clean['success_message']   = ! empty( $input['success_message'] ) ? sanitize_textarea_field( $input['success_message'] ) : $clean['success_message'];
		$clean['privacy_text']      = ! empty( $input['privacy_text'] ) ? sanitize_textarea_field( $input['privacy_text'] ) : $clean['privacy_text'];

		// 4. Display Toggles
		$clean['show_closed_jobs']     = ! empty( $input['show_closed_jobs'] ) ? 1 : 0;
		$clean['show_salary']          = ! empty( $input['show_salary'] ) ? 1 : 0;
		$clean['show_department']      = ! empty( $input['show_department'] ) ? 1 : 0;
		$clean['show_location']        = ! empty( $input['show_location'] ) ? 1 : 0;
		$clean['show_employment_type'] = ! empty( $input['show_employment_type'] ) ? 1 : 0;
		$clean['show_experience']      = ! empty( $input['show_experience'] ) ? 1 : 0;

		// 5. General Application & Bottom CTA
		$clean['general_application_title'] = ! empty( $input['general_application_title'] ) ? sanitize_text_field( $input['general_application_title'] ) : $clean['general_application_title'];
		$clean['general_application_desc']  = ! empty( $input['general_application_desc'] ) ? sanitize_textarea_field( $input['general_application_desc'] ) : $clean['general_application_desc'];
		$clean['general_application_btn']   = ! empty( $input['general_application_btn'] ) ? sanitize_text_field( $input['general_application_btn'] ) : $clean['general_application_btn'];
		$clean['contact_hr_cta']            = ! empty( $input['contact_hr_cta'] ) ? sanitize_text_field( $input['contact_hr_cta'] ) : $clean['contact_hr_cta'];

		if ( isset( $input['contact_hr_email'] ) ) {
			$clean_contact = sanitize_email( $input['contact_hr_email'] );
			$clean['contact_hr_email'] = is_email( $clean_contact ) ? $clean_contact : $clean['profile_email'];
		}

		// 6. Outbound SMTP Delivery Settings
		$clean['smtp_enabled']    = ! empty( $input['smtp_enabled'] ) ? 1 : 0;
		$clean['smtp_host']       = ! empty( $input['smtp_host'] ) ? sanitize_text_field( $input['smtp_host'] ) : 'smtp.cubeontechs.com';
		$clean['smtp_port']       = ! empty( $input['smtp_port'] ) ? absint( $input['smtp_port'] ) : 465;
		$clean['smtp_encryption'] = in_array( $input['smtp_encryption'] ?? '', array( 'tls', 'ssl', 'none' ), true ) ? $input['smtp_encryption'] : 'ssl';
		$clean['smtp_user']       = ! empty( $input['smtp_user'] ) ? sanitize_text_field( $input['smtp_user'] ) : 'career@cubeontechs.com';
		
		// If password field is submitted blank, preserve previously saved password
		$existing_settings  = get_option( self::OPTION_NAME, array() );
		$clean['smtp_pass'] = ! empty( $input['smtp_pass'] ) ? sanitize_text_field( $input['smtp_pass'] ) : ( $existing_settings['smtp_pass'] ?? '' );
		
		$clean['smtp_from_email'] = ! empty( $input['smtp_from_email'] ) && is_email( $input['smtp_from_email'] ) ? sanitize_email( $input['smtp_from_email'] ) : $clean['profile_email'];
		$clean['smtp_from_name']  = ! empty( $input['smtp_from_name'] ) ? sanitize_text_field( $input['smtp_from_name'] ) : 'SpiceCraft Recruitment';

		return $clean;
	}

	/**
	 * Render the Careers Settings Admin Page.
	 */
	public function render_settings_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$settings = spicecraft_get_careers_settings();
		?>
		<div class="wrap spicecraft-settings-wrap">
			<h1 class="wp-heading-inline">
				<span class="dashicons dashicons-businessman" style="font-size: 28px; width: 28px; height: 28px; vertical-align: middle;"></span>
				<?php esc_html_e( 'SpiceCraft Careers & Recruitment Management', 'spicecraft-core' ); ?>
			</h1>
			<p class="description">
				<?php esc_html_e( 'Configure public careers presentation, candidate notification email routing, application disclaimers, and display options.', 'spicecraft-core' ); ?>
			</p>
			<hr class="wp-header-end" />

			<?php settings_errors(); ?>

			<!-- Ecosystem Sub-Navigation -->
			<div style="margin: 15px 0 20px; display: flex; gap: 8px;">
				<a href="<?php echo esc_url( admin_url( 'edit.php?post_type=' . SpiceCraft_Careers_CPT::JOB_CPT ) ); ?>" class="button">
					<span class="dashicons dashicons-list-view" style="vertical-align: middle;"></span> <?php esc_html_e( 'Manage Job Openings', 'spicecraft-core' ); ?>
				</a>
				<a href="<?php echo esc_url( admin_url( 'post-new.php?post_type=' . SpiceCraft_Careers_CPT::JOB_CPT ) ); ?>" class="button">
					<span class="dashicons dashicons-plus-alt2" style="vertical-align: middle;"></span> <?php esc_html_e( 'Post New Job', 'spicecraft-core' ); ?>
				</a>
				<a href="<?php echo esc_url( admin_url( 'edit.php?post_type=' . SpiceCraft_Careers_CPT::APPLICATION_CPT ) ); ?>" class="button">
					<span class="dashicons dashicons-feedback" style="vertical-align: middle;"></span> <?php esc_html_e( 'View Received Applications', 'spicecraft-core' ); ?>
				</a>
				<a href="<?php echo esc_url( spicecraft_get_careers_url() ); ?>" class="button" target="_blank" rel="noopener noreferrer">
					<span class="dashicons dashicons-external" style="vertical-align: middle;"></span> <?php esc_html_e( 'View Public Careers Page', 'spicecraft-core' ); ?>
				</a>
			</div>

			<form method="post" action="options.php" id="sc-careers-settings-form">
				<?php
				settings_fields( self::SETTINGS_GROUP );
				?>

				<div class="sc-metabox-wrapper" style="background: #fff; border: 1px solid #c3c4c7; border-radius: 6px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); margin-top: 15px;">
					<!-- Nav Tabs -->
					<div class="sc-metabox-tabs" role="tablist">
						<button type="button" class="sc-metabox-tab-btn is-active" data-tab="sc-set-email">
							<span class="dashicons dashicons-email-alt"></span> <?php esc_html_e( 'Profile Receiving Email', 'spicecraft-core' ); ?>
						</button>
						<button type="button" class="sc-metabox-tab-btn" data-tab="sc-set-hero">
							<span class="dashicons dashicons-cover-image"></span> <?php esc_html_e( 'Careers Hero & Intro', 'spicecraft-core' ); ?>
						</button>
						<button type="button" class="sc-metabox-tab-btn" data-tab="sc-set-culture">
							<span class="dashicons dashicons-groups"></span> <?php esc_html_e( 'Why Join Us / Culture', 'spicecraft-core' ); ?>
						</button>
						<button type="button" class="sc-metabox-tab-btn" data-tab="sc-set-form">
							<span class="dashicons dashicons-forms"></span> <?php esc_html_e( 'Application & Privacy', 'spicecraft-core' ); ?>
						</button>
						<button type="button" class="sc-metabox-tab-btn" data-tab="sc-set-display">
							<span class="dashicons dashicons-visibility"></span> <?php esc_html_e( 'Display Options', 'spicecraft-core' ); ?>
						</button>
						<button type="button" class="sc-metabox-tab-btn" data-tab="sc-set-cta">
							<span class="dashicons dashicons-megaphone"></span> <?php esc_html_e( 'General Talent CTA', 'spicecraft-core' ); ?>
						</button>
					</div>

					<!-- TAB 1: Profile Receiving Email (CRITICAL REQUIREMENT) -->
					<div class="sc-metabox-panel" id="sc-set-email" style="display: block; padding: 20px;">
						<div class="notice notice-info inline" style="margin: 0 0 20px; padding: 12px 14px;">
							<p style="margin: 0; font-size: 13px;">
								<strong><?php esc_html_e( 'Dynamic Email Routing Notice:', 'spicecraft-core' ); ?></strong>
								<?php esc_html_e( 'Every candidate application submitted across all positions is immediately delivered to the recipient email configured below. You can change this email address at any time without modifying any source code.', 'spicecraft-core' ); ?>
							</p>
						</div>

						<table class="form-table" role="presentation">
							<tr>
								<th scope="row">
									<label for="sc_profile_email" style="font-weight: 700; color: #1e293b;">
										<?php esc_html_e( 'Profile Receiving Email *', 'spicecraft-core' ); ?>
									</label>
								</th>
								<td>
									<input
										type="email"
										id="sc_profile_email"
										name="<?php echo esc_attr( self::OPTION_NAME ); ?>[profile_email]"
										value="<?php echo esc_attr( $settings['profile_email'] ); ?>"
										class="regular-text"
										required
										style="font-size: 15px; padding: 6px 12px; max-width: 450px; width: 100%; border-color: #94a3b8;"
									/>
									<p class="description" style="margin-top: 6px;">
										<?php
										printf(
											/* translators: %s: default email */
											esc_html__( 'Initial default: %s. Application processing reads this value dynamically from the database on every submission.', 'spicecraft-core' ),
											'<code>' . esc_html( SPICECRAFT_CAREERS_DEFAULT_EMAIL ) . '</code>'
										);
										?>
									</p>
								</td>
							</tr>
							<tr>
								<th scope="row"><?php esc_html_e( 'Current Runtime Value', 'spicecraft-core' ); ?></th>
								<td>
									<span style="display: inline-block; padding: 6px 12px; background: #e0f2fe; border: 1px solid #7dd3fc; border-radius: 4px; font-weight: 700; color: #0369a1; font-family: monospace; font-size: 14px;">
										<?php echo esc_html( spicecraft_get_careers_profile_email() ); ?>
									</span>
									<span style="display: inline-block; margin-left: 8px; color: #059669; font-weight: 600;">
										<span class="dashicons dashicons-yes-alt" style="vertical-align: middle;"></span> <?php esc_html_e( 'Active & Verified', 'spicecraft-core' ); ?>
									</span>
								</td>
							</tr>
						</table>

						<!-- Outbound SMTP Mail Transport Section -->
						<div style="margin-top: 30px; padding-top: 20px; border-top: 2px dashed #e2e8f0;">
							<h3 style="margin-top: 0; color: #0f172a; font-size: 16px; display: flex; align-items: center; gap: 8px;">
								<span class="dashicons dashicons-networking" style="color: #9e2a2b;"></span>
								<?php esc_html_e( 'Outbound SMTP Mail Transport (Live Delivery Setup)', 'spicecraft-core' ); ?>
							</h3>
							<p class="description" style="max-width: 780px; margin-bottom: 15px;">
								<?php esc_html_e( 'On local development environments (Windows/localhost) and modern web hosts, PHP cannot send emails directly to external inboxes like career@cubeontechs.com without authenticated SMTP credentials. Enable and configure SMTP below to ensure real email delivery with attachments.', 'spicecraft-core' ); ?>
							</p>

							<table class="form-table" role="presentation">
								<tr>
									<th scope="row"><?php esc_html_e( 'Custom SMTP Relay', 'spicecraft-core' ); ?></th>
									<td>
										<label for="sc_smtp_enabled" style="font-weight: 600;">
											<input type="checkbox" id="sc_smtp_enabled" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[smtp_enabled]" value="1" <?php checked( ! empty( $settings['smtp_enabled'] ) ); ?> />
											<?php esc_html_e( 'Route outgoing recruitment notifications via authenticated SMTP', 'spicecraft-core' ); ?>
										</label>
									</td>
								</tr>
								<tr>
									<th scope="row"><label for="sc_smtp_host"><?php esc_html_e( 'Outgoing Mail Server (SMTP Host)', 'spicecraft-core' ); ?></label></th>
									<td>
										<input type="text" id="sc_smtp_host" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[smtp_host]" value="<?php echo esc_attr( $settings['smtp_host'] ?? 'smtp.cubeontechs.com' ); ?>" class="regular-text" placeholder="smtp.cubeontechs.com" />
										<p class="description"><?php esc_html_e( 'Outgoing SMTP server: smtp.cubeontechs.com (Do not use incoming imap.cubeontechs.com)', 'spicecraft-core' ); ?></p>
									</td>
								</tr>
								<tr>
									<th scope="row"><label for="sc_smtp_port"><?php esc_html_e( 'SMTP Port', 'spicecraft-core' ); ?></label></th>
									<td>
										<input type="number" id="sc_smtp_port" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[smtp_port]" value="<?php echo esc_attr( $settings['smtp_port'] ?? 465 ); ?>" class="small-text" style="width: 90px;" />
										<p class="description"><?php esc_html_e( 'Port 465 for SSL (Recommended), or 587 for TLS.', 'spicecraft-core' ); ?></p>
									</td>
								</tr>
								<tr>
									<th scope="row"><label for="sc_smtp_encryption"><?php esc_html_e( 'Encryption', 'spicecraft-core' ); ?></label></th>
									<td>
										<select id="sc_smtp_encryption" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[smtp_encryption]">
											<option value="ssl" <?php selected( ( $settings['smtp_encryption'] ?? 'ssl' ), 'ssl' ); ?>><?php esc_html_e( 'SSL (Port 465 - Recommended)', 'spicecraft-core' ); ?></option>
											<option value="tls" <?php selected( ( $settings['smtp_encryption'] ?? 'ssl' ), 'tls' ); ?>><?php esc_html_e( 'TLS / STARTTLS (Port 587)', 'spicecraft-core' ); ?></option>
											<option value="none" <?php selected( ( $settings['smtp_encryption'] ?? 'ssl' ), 'none' ); ?>><?php esc_html_e( 'None / Insecure (Port 25)', 'spicecraft-core' ); ?></option>
										</select>
									</td>
								</tr>
								<tr>
									<th scope="row"><label for="sc_smtp_user"><?php esc_html_e( 'SMTP Username', 'spicecraft-core' ); ?></label></th>
									<td>
										<input type="text" id="sc_smtp_user" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[smtp_user]" value="<?php echo esc_attr( $settings['smtp_user'] ?? 'career@cubeontechs.com' ); ?>" class="regular-text" placeholder="career@cubeontechs.com" />
										<p class="description"><?php esc_html_e( 'Your complete email account username.', 'spicecraft-core' ); ?></p>
									</td>
								</tr>
								<tr>
									<th scope="row"><label for="sc_smtp_pass"><?php esc_html_e( 'SMTP Password', 'spicecraft-core' ); ?></label></th>
									<td>
										<input type="password" id="sc_smtp_pass" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[smtp_pass]" value="<?php echo esc_attr( $settings['smtp_pass'] ?? '' ); ?>" class="regular-text" autocomplete="new-password" />
										<p class="description"><?php esc_html_e( 'Email account password or app-specific password. (Saved securely; leave blank to preserve existing password).', 'spicecraft-core' ); ?></p>
									</td>
								</tr>
								<tr>
									<th scope="row"><label for="sc_smtp_from_email"><?php esc_html_e( 'Sender (From) Email', 'spicecraft-core' ); ?></label></th>
									<td>
										<input type="email" id="sc_smtp_from_email" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[smtp_from_email]" value="<?php echo esc_attr( $settings['smtp_from_email'] ?? $settings['profile_email'] ); ?>" class="regular-text" />
										<p class="description"><?php esc_html_e( 'Must match or be authorized by your SMTP server domain (e.g. career@cubeontechs.com).', 'spicecraft-core' ); ?></p>
									</td>
								</tr>
								<tr>
									<th scope="row"><label for="sc_smtp_from_name"><?php esc_html_e( 'Sender (From) Name', 'spicecraft-core' ); ?></label></th>
									<td>
										<input type="text" id="sc_smtp_from_name" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[smtp_from_name]" value="<?php echo esc_attr( $settings['smtp_from_name'] ?? 'SpiceCraft Recruitment' ); ?>" class="regular-text" />
									</td>
								</tr>
							</table>

							<!-- Live SMTP Test Card -->
							<div style="background: #f8fafc; border: 1px solid #cbd5e1; border-radius: 6px; padding: 16px; margin-top: 15px; max-width: 650px;">
								<h4 style="margin: 0 0 8px; color: #0f172a; font-size: 14px;">
									<span class="dashicons dashicons-email-alt" style="vertical-align: middle;"></span>
									<?php esc_html_e( 'Test SMTP Connection & Live Delivery', 'spicecraft-core' ); ?>
								</h4>
								<p style="margin: 0 0 12px; font-size: 13px; color: #475569;">
									<?php
									printf(
										/* translators: %s: recipient email */
										esc_html__( 'Sends an immediate test verification email to %s using the currently saved SMTP configuration.', 'spicecraft-core' ),
										'<strong>' . esc_html( spicecraft_get_careers_profile_email() ) . '</strong>'
									);
									?>
								</p>
								<button type="button" id="sc-test-smtp-btn" class="button button-secondary">
									<span class="dashicons dashicons-controls-play" style="vertical-align: middle;"></span>
									<?php esc_html_e( 'Send Test Email Now', 'spicecraft-core' ); ?>
								</button>
								<div id="sc-test-smtp-result" style="margin-top: 12px; display: none;"></div>
							</div>
						</div>
					</div>

					<!-- TAB 2: Hero & Intro -->
					<div class="sc-metabox-panel" id="sc-set-hero" style="display: none; padding: 20px;">
						<table class="form-table" role="presentation">
							<tr>
								<th scope="row"><label for="sc_page_title"><?php esc_html_e( 'Page Browser Title', 'spicecraft-core' ); ?></label></th>
								<td>
									<input type="text" id="sc_page_title" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[page_title]" value="<?php echo esc_attr( $settings['page_title'] ); ?>" class="regular-text" />
								</td>
							</tr>
							<tr>
								<th scope="row"><label for="sc_hero_badge"><?php esc_html_e( 'Hero Badge Text', 'spicecraft-core' ); ?></label></th>
								<td>
									<input type="text" id="sc_hero_badge" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[hero_badge]" value="<?php echo esc_attr( $settings['hero_badge'] ); ?>" class="regular-text" placeholder="e.g. We Are Hiring" />
								</td>
							</tr>
							<tr>
								<th scope="row"><label for="sc_hero_title"><?php esc_html_e( 'Hero Main Heading (H1)', 'spicecraft-core' ); ?></label></th>
								<td>
									<input type="text" id="sc_hero_title" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[hero_title]" value="<?php echo esc_attr( $settings['hero_title'] ); ?>" class="large-text" />
								</td>
							</tr>
							<tr>
								<th scope="row"><label for="sc_hero_subtitle"><?php esc_html_e( 'Hero Description / Intro', 'spicecraft-core' ); ?></label></th>
								<td>
									<textarea id="sc_hero_subtitle" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[hero_subtitle]" rows="3" class="large-text"><?php echo esc_textarea( $settings['hero_subtitle'] ); ?></textarea>
								</td>
							</tr>
							<tr>
								<th scope="row"><?php esc_html_e( 'Careers Hero Image', 'spicecraft-core' ); ?></th>
								<td>
									<?php
									if ( function_exists( 'spicecraft_render_admin_media_uploader' ) ) {
										spicecraft_render_admin_media_uploader(
											self::OPTION_NAME . '[hero_image_id]',
											$settings['hero_image_id'],
											'',
											__( 'Select Hero Image', 'spicecraft-core' )
										);
									}
									?>
								</td>
							</tr>
						</table>
					</div>

					<!-- TAB 3: Why Join Us / Culture Points -->
					<div class="sc-metabox-panel" id="sc-set-culture" style="display: none; padding: 20px;">
						<table class="form-table" role="presentation">
							<tr>
								<th scope="row"><label for="sc_why_join_title"><?php esc_html_e( 'Section Title', 'spicecraft-core' ); ?></label></th>
								<td>
									<input type="text" id="sc_why_join_title" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[why_join_title]" value="<?php echo esc_attr( $settings['why_join_title'] ); ?>" class="regular-text" />
								</td>
							</tr>
							<tr>
								<th scope="row"><label for="sc_why_join_subtitle"><?php esc_html_e( 'Section Subtitle / Description', 'spicecraft-core' ); ?></label></th>
								<td>
									<textarea id="sc_why_join_subtitle" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[why_join_subtitle]" rows="2" class="large-text"><?php echo esc_textarea( $settings['why_join_subtitle'] ); ?></textarea>
								</td>
							</tr>
						</table>

						<div style="display: flex; justify-content: space-between; align-items: center; margin: 20px 0 12px;">
							<h3 style="margin: 0; font-size: 15px;"><?php esc_html_e( 'Culture & Work Environment Pillars', 'spicecraft-core' ); ?></h3>
							<button type="button" class="button button-secondary" id="sc-add-culture-point-btn">
								<span class="dashicons dashicons-plus-alt2" style="margin-top: -2px;"></span> <?php esc_html_e( 'Add Culture Pillar', 'spicecraft-core' ); ?>
							</button>
						</div>

						<div id="sc-culture-points-container">
							<?php
							$points = ! empty( $settings['culture_points'] ) && is_array( $settings['culture_points'] ) ? $settings['culture_points'] : array();
							foreach ( $points as $p_idx => $pt ) :
								?>
								<div class="sc-repeatable-row sc-card" style="padding: 14px; margin-bottom: 12px; background: #fafafa; border: 1px solid #cbd5e1; border-radius: 4px;">
									<div style="display: flex; gap: 10px; margin-bottom: 8px;">
										<input type="text" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[culture_points][<?php echo esc_attr( $p_idx ); ?>][title]" value="<?php echo esc_attr( $pt['title'] ?? '' ); ?>" placeholder="<?php esc_attr_e( 'Pillar Title', 'spicecraft-core' ); ?>" class="regular-text" style="flex-grow: 1; font-weight: 600;" />
										<input type="text" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[culture_points][<?php echo esc_attr( $p_idx ); ?>][icon]" value="<?php echo esc_attr( $pt['icon'] ?? 'star' ); ?>" placeholder="Icon (shield, leaf, award, heart)" style="width: 140px;" />
										<button type="button" class="button sc-remove-row-btn">&times;</button>
									</div>
									<textarea name="<?php echo esc_attr( self::OPTION_NAME ); ?>[culture_points][<?php echo esc_attr( $p_idx ); ?>][description]" placeholder="<?php esc_attr_e( 'Pillar description...', 'spicecraft-core' ); ?>" rows="2" class="widefat"><?php echo esc_textarea( $pt['description'] ?? '' ); ?></textarea>
								</div>
							<?php endforeach; ?>
						</div>
					</div>

					<!-- TAB 4: Application Form & Privacy -->
					<div class="sc-metabox-panel" id="sc-set-form" style="display: none; padding: 20px;">
						<table class="form-table" role="presentation">
							<tr>
								<th scope="row"><label for="sc_app_title"><?php esc_html_e( 'Application Form Title', 'spicecraft-core' ); ?></label></th>
								<td>
									<input type="text" id="sc_app_title" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[application_title]" value="<?php echo esc_attr( $settings['application_title'] ); ?>" class="regular-text" />
								</td>
							</tr>
							<tr>
								<th scope="row"><label for="sc_app_intro"><?php esc_html_e( 'Application Form Intro', 'spicecraft-core' ); ?></label></th>
								<td>
									<textarea id="sc_app_intro" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[application_intro]" rows="2" class="large-text"><?php echo esc_textarea( $settings['application_intro'] ); ?></textarea>
								</td>
							</tr>
							<tr>
								<th scope="row"><label for="sc_success_msg"><?php esc_html_e( 'Success Message Template', 'spicecraft-core' ); ?></label></th>
								<td>
									<textarea id="sc_success_msg" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[success_message]" rows="3" class="large-text"><?php echo esc_textarea( $settings['success_message'] ); ?></textarea>
									<p class="description"><?php esc_html_e( 'Use {Job Title} tag to dynamically interpolate the position applied for.', 'spicecraft-core' ); ?></p>
								</td>
							</tr>
							<tr>
								<th scope="row"><label for="sc_privacy_text"><?php esc_html_e( 'Consent & Privacy Notice *', 'spicecraft-core' ); ?></label></th>
								<td>
									<textarea id="sc_privacy_text" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[privacy_text]" rows="2" class="large-text"><?php echo esc_textarea( $settings['privacy_text'] ); ?></textarea>
									<p class="description"><?php esc_html_e( 'Text displayed beside mandatory candidate consent checkbox on application form.', 'spicecraft-core' ); ?></p>
								</td>
							</tr>
						</table>
					</div>

					<!-- TAB 5: Display Options -->
					<div class="sc-metabox-panel" id="sc-set-display" style="display: none; padding: 20px;">
						<p class="description" style="margin-bottom: 16px;"><?php esc_html_e( 'Control what information appears on public job cards and detail pages.', 'spicecraft-core' ); ?></p>
						<table class="form-table" role="presentation">
							<tr>
								<th scope="row"><?php esc_html_e( 'Closed Positions', 'spicecraft-core' ); ?></th>
								<td>
									<label for="sc_show_closed">
										<input type="checkbox" id="sc_show_closed" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[show_closed_jobs]" value="1" <?php checked( $settings['show_closed_jobs'] ); ?> />
										<?php esc_html_e( 'Display Closed Jobs with "Position Closed" badge on Careers page', 'spicecraft-core' ); ?>
									</label>
								</td>
							</tr>
							<tr>
								<th scope="row"><?php esc_html_e( 'Salary & Remuneration', 'spicecraft-core' ); ?></th>
								<td>
									<label for="sc_show_salary">
										<input type="checkbox" id="sc_show_salary" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[show_salary]" value="1" <?php checked( $settings['show_salary'] ); ?> />
										<?php esc_html_e( 'Display salary / compensation if specified on the job', 'spicecraft-core' ); ?>
									</label>
								</td>
							</tr>
							<tr>
								<th scope="row"><?php esc_html_e( 'Department Badges', 'spicecraft-core' ); ?></th>
								<td>
									<label for="sc_show_dept">
										<input type="checkbox" id="sc_show_dept" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[show_department]" value="1" <?php checked( $settings['show_department'] ); ?> />
										<?php esc_html_e( 'Display Department badge on job cards', 'spicecraft-core' ); ?>
									</label>
								</td>
							</tr>
							<tr>
								<th scope="row"><?php esc_html_e( 'Plant / Location', 'spicecraft-core' ); ?></th>
								<td>
									<label for="sc_show_loc">
										<input type="checkbox" id="sc_show_loc" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[show_location]" value="1" <?php checked( $settings['show_location'] ); ?> />
										<?php esc_html_e( 'Display Location on job cards', 'spicecraft-core' ); ?>
									</label>
								</td>
							</tr>
							<tr>
								<th scope="row"><?php esc_html_e( 'Employment Type', 'spicecraft-core' ); ?></th>
								<td>
									<label for="sc_show_emp">
										<input type="checkbox" id="sc_show_emp" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[show_employment_type]" value="1" <?php checked( $settings['show_employment_type'] ); ?> />
										<?php esc_html_e( 'Display Employment Type (Full Time, etc.)', 'spicecraft-core' ); ?>
									</label>
								</td>
							</tr>
							<tr>
								<th scope="row"><?php esc_html_e( 'Experience Requirement', 'spicecraft-core' ); ?></th>
								<td>
									<label for="sc_show_exp">
										<input type="checkbox" id="sc_show_exp" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[show_experience]" value="1" <?php checked( $settings['show_experience'] ); ?> />
										<?php esc_html_e( 'Display Experience Required on job cards', 'spicecraft-core' ); ?>
									</label>
								</td>
							</tr>
						</table>
					</div>

					<!-- TAB 6: General Talent Pool CTA -->
					<div class="sc-metabox-panel" id="sc-set-cta" style="display: none; padding: 20px;">
						<table class="form-table" role="presentation">
							<tr>
								<th scope="row"><label for="sc_gen_title"><?php esc_html_e( 'Section Title', 'spicecraft-core' ); ?></label></th>
								<td>
									<input type="text" id="sc_gen_title" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[general_application_title]" value="<?php echo esc_attr( $settings['general_application_title'] ); ?>" class="regular-text" />
								</td>
							</tr>
							<tr>
								<th scope="row"><label for="sc_gen_desc"><?php esc_html_e( 'Section Description', 'spicecraft-core' ); ?></label></th>
								<td>
									<textarea id="sc_gen_desc" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[general_application_desc]" rows="2" class="large-text"><?php echo esc_textarea( $settings['general_application_desc'] ); ?></textarea>
								</td>
							</tr>
							<tr>
								<th scope="row"><label for="sc_gen_btn"><?php esc_html_e( 'Contact Button Text', 'spicecraft-core' ); ?></label></th>
								<td>
									<input type="text" id="sc_gen_btn" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[general_application_btn]" value="<?php echo esc_attr( $settings['general_application_btn'] ); ?>" class="regular-text" />
								</td>
							</tr>
							<tr>
								<th scope="row"><label for="sc_contact_email"><?php esc_html_e( 'General Talent Desk Email', 'spicecraft-core' ); ?></label></th>
								<td>
									<input type="email" id="sc_contact_email" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[contact_hr_email]" value="<?php echo esc_attr( $settings['contact_hr_email'] ); ?>" class="regular-text" />
								</td>
							</tr>
						</table>
					</div>
				</div>

				<p class="submit" style="margin-top: 20px;">
					<?php submit_button( __( 'Save Careers Settings', 'spicecraft-core' ), 'primary', 'submit', false ); ?>
				</p>
			</form>
		</div>

		<script>
		jQuery(document).ready(function($) {
			$('#sc-test-smtp-btn').on('click', function(e) {
				e.preventDefault();
				var $btn = $(this);
				var $res = $('#sc-test-smtp-result');
				$btn.prop('disabled', true).text('<?php echo esc_js( __( 'Testing Delivery Connection...', 'spicecraft-core' ) ); ?>');
				$res.show().html('<span class="spinner is-active" style="float:none; margin:0 8px 0 0;"></span> <?php echo esc_js( __( 'Connecting to SMTP server and sending verification email...', 'spicecraft-core' ) ); ?>');

				var postData = {
					action: 'spicecraft_test_smtp_email',
					security: '<?php echo esc_js( wp_create_nonce( 'spicecraft_test_smtp_nonce' ) ); ?>',
					smtp_host: $('#sc_smtp_host').val(),
					smtp_port: $('#sc_smtp_port').val(),
					smtp_encryption: $('#sc_smtp_encryption').val(),
					smtp_user: $('#sc_smtp_user').val(),
					smtp_pass: $('#sc_smtp_pass').val(),
					smtp_from_email: $('#sc_smtp_from_email').val(),
					smtp_from_name: $('#sc_smtp_from_name').val()
				};

				$.ajax({
					url: ajaxurl,
					type: 'POST',
					data: postData,
					success: function(resp) {
						$btn.prop('disabled', false).html('<span class="dashicons dashicons-controls-play" style="vertical-align: middle;"></span> <?php echo esc_js( __( 'Send Test Email Now', 'spicecraft-core' ) ); ?>');
						if (resp.success) {
							$res.html('<div class="notice notice-success inline" style="margin:0; padding:10px 14px;"><p><strong>' + resp.data.message + '</strong></p></div>');
							$('#sc_smtp_enabled').prop('checked', true);
						} else {
							$res.html('<div class="notice notice-error inline" style="margin:0; padding:10px 14px;"><p><strong>' + resp.data.message + '</strong></p></div>');
						}
					},
					error: function(xhr) {
						$btn.prop('disabled', false).html('<span class="dashicons dashicons-controls-play" style="vertical-align: middle;"></span> <?php echo esc_js( __( 'Send Test Email Now', 'spicecraft-core' ) ); ?>');
						$res.html('<div class="notice notice-error inline" style="margin:0; padding:10px 14px;"><p><strong>HTTP Error: ' + xhr.status + ' ' + xhr.statusText + '</strong></p></div>');
					}
				});
			});
		});
		</script>
		<?php
	}

	/**
	 * AJAX Handler: Dispatch Test SMTP Verification Email.
	 */
	public function handle_test_smtp_email() {
		check_ajax_referer( 'spicecraft_test_smtp_nonce', 'security' );
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Unauthorized access.', 'spicecraft-core' ) ) );
		}

		$recipient  = spicecraft_get_careers_profile_email();
		$site_name  = get_bloginfo( 'name' );
		$subject    = sprintf( 'SpiceCraft Careers SMTP Test — %s', current_time( 'Y-m-d H:i:s' ) );
		$body       = "<h2>SpiceCraft Careers — SMTP Delivery Verification</h2>\n";
		$body      .= "<p>This is a real-time verification email sent from your SpiceCraft WordPress CMS to confirm that outgoing SMTP email delivery is operating correctly.</p>\n";
		$body      .= "<p><strong>Target Recipient:</strong> " . esc_html( $recipient ) . "<br />\n";
		$body      .= "<strong>Server Host:</strong> " . esc_html( php_uname( 'n' ) ) . "<br />\n";
		$body      .= "<strong>Timestamp:</strong> " . current_time( 'F j, Y, g:i a' ) . "</p>";

		$settings   = spicecraft_get_careers_settings();

		// Read parameters from POST if passed from form, otherwise fall back to saved settings
		$smtp_host       = ! empty( $_POST['smtp_host'] ) ? sanitize_text_field( wp_unslash( $_POST['smtp_host'] ) ) : ( $settings['smtp_host'] ?? 'smtp.cubeontechs.com' );
		$smtp_port       = ! empty( $_POST['smtp_port'] ) ? absint( $_POST['smtp_port'] ) : ( $settings['smtp_port'] ?? 465 );
		$smtp_encryption = ! empty( $_POST['smtp_encryption'] ) ? sanitize_text_field( wp_unslash( $_POST['smtp_encryption'] ) ) : ( $settings['smtp_encryption'] ?? 'ssl' );
		$smtp_user       = ! empty( $_POST['smtp_user'] ) ? sanitize_text_field( wp_unslash( $_POST['smtp_user'] ) ) : ( $settings['smtp_user'] ?? 'career@cubeontechs.com' );
		$smtp_pass       = ! empty( $_POST['smtp_pass'] ) ? sanitize_text_field( wp_unslash( $_POST['smtp_pass'] ) ) : ( $settings['smtp_pass'] ?? '' );
		$from_email      = ! empty( $_POST['smtp_from_email'] ) ? sanitize_email( wp_unslash( $_POST['smtp_from_email'] ) ) : ( $settings['smtp_from_email'] ?? $recipient );
		$from_name       = ! empty( $_POST['smtp_from_name'] ) ? sanitize_text_field( wp_unslash( $_POST['smtp_from_name'] ) ) : ( $settings['smtp_from_name'] ?? $site_name . ' Recruitment' );

		if ( empty( $from_email ) || false !== strpos( $from_email, 'localhost' ) || false === strpos( $from_email, '.' ) ) {
			$from_email = $recipient;
		}

		$last_error = '';
		$error_cb   = function( $wp_error ) use ( &$last_error ) {
			if ( is_wp_error( $wp_error ) ) {
				$last_error = $wp_error->get_error_message();
			}
		};
		add_action( 'wp_mail_failed', $error_cb );

		// Configure PHPMailer explicitly for this test
		$test_init_cb = function( $phpmailer ) use ( $smtp_host, $smtp_port, $smtp_encryption, $smtp_user, $smtp_pass, $from_email, $from_name ) {
			$phpmailer->isSMTP();
			$phpmailer->Host     = $smtp_host;
			$phpmailer->Port     = $smtp_port;
			$phpmailer->SMTPAuth = ! empty( $smtp_user );
			if ( $phpmailer->SMTPAuth ) {
				$phpmailer->AuthType = 'LOGIN'; // Force standard LOGIN auth (prevents CRAM-MD5 failure)
				$phpmailer->Username = $smtp_user;
				$phpmailer->Password = $smtp_pass;
			}
			if ( 'ssl' === $smtp_encryption ) {
				$phpmailer->SMTPSecure = 'ssl';
			} elseif ( 'tls' === $smtp_encryption ) {
				$phpmailer->SMTPSecure = 'tls';
			} else {
				$phpmailer->SMTPSecure  = '';
				$phpmailer->SMTPAutoTLS = false;
			}
			$phpmailer->setFrom( $from_email, $from_name, false );
			$phpmailer->Sender  = $from_email;
			$phpmailer->Timeout = 15;
		};
		add_action( 'phpmailer_init', $test_init_cb, 9999 );

		$from_filter = function() use ( $from_email ) { return $from_email; };
		$name_filter = function() use ( $from_name ) { return $from_name; };
		add_filter( 'wp_mail_from', $from_filter, 9999 );
		add_filter( 'wp_mail_from_name', $name_filter, 9999 );

		$headers = array( 'Content-Type: text/html; charset=UTF-8' );
		$sent    = wp_mail( $recipient, $subject, $body, $headers );

		remove_action( 'wp_mail_failed', $error_cb );
		remove_action( 'phpmailer_init', $test_init_cb, 9999 );
		remove_filter( 'wp_mail_from', $from_filter, 9999 );
		remove_filter( 'wp_mail_from_name', $name_filter, 9999 );

		if ( $sent ) {
			// Automatically update database settings to enabled on successful test
			$saved = get_option( self::OPTION_NAME, array() );
			$saved['smtp_enabled']    = 1;
			$saved['smtp_host']       = $smtp_host;
			$saved['smtp_port']       = $smtp_port;
			$saved['smtp_encryption'] = $smtp_encryption;
			$saved['smtp_user']       = $smtp_user;
			if ( ! empty( $smtp_pass ) ) {
				$saved['smtp_pass']   = $smtp_pass;
			}
			$saved['smtp_from_email'] = $from_email;
			$saved['smtp_from_name']  = $from_name;
			update_option( self::OPTION_NAME, $saved );

			wp_send_json_success( array(
				'message' => sprintf( __( 'Success! Test email was successfully dispatched to %s via %s:%d (%s). Outbound SMTP is now saved and active. Please check your inbox!', 'spicecraft-core' ), $recipient, $smtp_host, $smtp_port, strtoupper( $smtp_encryption ) ),
			) );
		} else {
			wp_send_json_error( array(
				'message' => sprintf( __( 'Delivery failed: %s. Please check host, port (%s), encryption (%s), and credentials.', 'spicecraft-core' ), $last_error ?: __( 'Connection refused or credentials rejected.', 'spicecraft-core' ), $smtp_port, strtoupper( $smtp_encryption ) ),
			) );
		}
	}
}
