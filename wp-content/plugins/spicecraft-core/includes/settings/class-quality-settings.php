<?php
/**
 * SpiceCraft Core - Quality & Sourcing CMS Settings Page
 *
 * Provides a structured, WordPress-native administrative settings interface
 * for the Quality Assurance & Sourcing Standards page under SpiceCraft -> Quality & Sourcing.
 *
 * @package SpiceCraft_Core
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class SpiceCraft_Quality_Settings {

	const OPTION_NAME = 'spicecraft_quality_settings';

	/**
	 * Singleton Instance.
	 *
	 * @var SpiceCraft_Quality_Settings|null
	 */
	private static $instance = null;

	/**
	 * Get Singleton Instance.
	 *
	 * @return SpiceCraft_Quality_Settings
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
		add_action( 'admin_menu', array( $this, 'register_admin_menu' ), 24 );
		add_action( 'admin_init', array( $this, 'register_settings' ) );
	}

	/**
	 * Register Admin Submenu.
	 */
	public function register_admin_menu() {
		add_submenu_page(
			'spicecraft-overview',
			__( 'Quality & Sourcing CMS Management', 'spicecraft-core' ),
			__( 'Quality & Sourcing', 'spicecraft-core' ),
			'manage_options',
			'spicecraft-quality',
			array( $this, 'render_page' )
		);
	}

	/**
	 * Register Settings with Validation & Sanitization.
	 */
	public function register_settings() {
		register_setting(
			'spicecraft_quality_group',
			self::OPTION_NAME,
			array(
				'sanitize_callback' => array( $this, 'sanitize_settings' ),
				'default'           => spicecraft_get_quality_default_settings(),
			)
		);
	}

	/**
	 * Render Administrative Settings Page.
	 */
	public function render_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$settings   = spicecraft_get_quality_settings();
		$active_tab = isset( $_GET['tab'] ) ? sanitize_key( $_GET['tab'] ) : 'order';

		$tabs = array(
			'order'        => __( '1. Order & Visibility', 'spicecraft-core' ),
			'hero'         => __( '2. Hero & Introduction', 'spicecraft-core' ),
			'principles'   => __( '3. Principles & Process', 'spicecraft-core' ),
			'testing'      => __( '4. Testing & Laboratory', 'spicecraft-core' ),
			'sourcing'     => __( '5. Sourcing & Regions', 'spicecraft-core' ),
			'traceability' => __( '6. Standards & Traceability', 'spicecraft-core' ),
			'trust'        => __( '7. Stats, Gallery & Trust', 'spicecraft-core' ),
			'cta'          => __( '8. Business CTAs', 'spicecraft-core' ),
		);

		$quality_page = get_page_by_path( 'quality' );
		$quality_url  = $quality_page ? get_permalink( $quality_page->ID ) : home_url( '/quality/' );
		?>
		<div class="wrap spicecraft-settings-wrap">
			<div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px; margin-bottom: 12px;">
				<h1 style="margin: 0;"><?php esc_html_e( 'SpiceCraft Quality & Sourcing CMS Management', 'spicecraft-core' ); ?></h1>
				<a href="<?php echo esc_url( $quality_url ); ?>" target="_blank" rel="noopener noreferrer" class="button button-secondary" style="display: inline-flex; align-items: center; gap: 4px;">
					<span class="dashicons dashicons-external" style="margin-top: -2px;"></span>
					<?php esc_html_e( 'View Quality & Sourcing Page', 'spicecraft-core' ); ?>
				</a>
			</div>

			<p class="description" style="margin-bottom: 16px;">
				<?php esc_html_e( 'Manage quality assurance philosophy, testing protocols, ethical sourcing regions, traceability frameworks, and laboratory standards.', 'spicecraft-core' ); ?>
			</p>

			<?php settings_errors(); ?>

			<!-- Navigation Tabs -->
			<h2 class="nav-tab-wrapper" style="margin-bottom: 20px;">
				<?php foreach ( $tabs as $tab_key => $tab_title ) : ?>
					<a href="?page=spicecraft-quality&tab=<?php echo esc_attr( $tab_key ); ?>" class="nav-tab <?php echo $tab_key === $active_tab ? 'nav-tab-active' : ''; ?>">
						<?php echo esc_html( $tab_title ); ?>
					</a>
				<?php endforeach; ?>
			</h2>

			<form method="post" action="options.php">
				<?php
				settings_fields( 'spicecraft_quality_group' );

				// Persist unedited tabs
				$this->render_hidden_tabs( $settings, $active_tab );

				switch ( $active_tab ) {
					case 'order':
						$this->render_tab_order( $settings );
						break;
					case 'hero':
						$this->render_tab_hero( $settings );
						break;
					case 'principles':
						$this->render_tab_principles( $settings );
						break;
					case 'testing':
						$this->render_tab_testing( $settings );
						break;
					case 'sourcing':
						$this->render_tab_sourcing( $settings );
						break;
					case 'traceability':
						$this->render_tab_traceability( $settings );
						break;
					case 'trust':
						$this->render_tab_trust( $settings );
						break;
					case 'cta':
						$this->render_tab_cta( $settings );
						break;
				}

				submit_button();
				?>
			</form>
		</div>
		<?php
	}

	/**
	 * Tab 1: Order & Visibility
	 */
	private function render_tab_order( $settings ) {
		$section_defs = array(
			'hero'           => array( 'name' => __( 'Hero Banner', 'spicecraft-core' ), 'id' => '#sc-quality-hero', 'desc' => __( 'Page title, primary trust statement, hero image, and main actions.', 'spicecraft-core' ) ),
			'introduction'   => array( 'name' => __( 'Quality Introduction', 'spicecraft-core' ), 'id' => '#sc-quality-intro', 'desc' => __( 'High-level quality philosophy and operational mindset.', 'spicecraft-core' ) ),
			'principles'     => array( 'name' => __( 'Quality Principles', 'spicecraft-core' ), 'id' => '#sc-quality-principles', 'desc' => __( 'Core pillars guiding spice testing and consistency.', 'spicecraft-core' ) ),
			'process'        => array( 'name' => __( 'Quality Control Process', 'spicecraft-core' ), 'id' => '#sc-quality-process', 'desc' => __( 'Multi-stage quality checks from raw intake to final dispatch.', 'spicecraft-core' ) ),
			'testing'        => array( 'name' => __( 'Testing & Laboratory', 'spicecraft-core' ), 'id' => '#sc-quality-testing', 'desc' => __( 'Specific analytical tests with clear testing context (in-house vs external).', 'spicecraft-core' ) ),
			'sourcing'       => array( 'name' => __( 'Sourcing Philosophy', 'spicecraft-core' ), 'id' => '#sc-quality-sourcing', 'desc' => __( 'Direct origin relationships and ethical procurement principles.', 'spicecraft-core' ) ),
			'regions'        => array( 'name' => __( 'Sourcing Regions', 'spicecraft-core' ), 'id' => '#sc-quality-regions', 'desc' => __( 'Geographic origins, agro-climatic conditions, and spice varieties.', 'spicecraft-core' ) ),
			'raw_materials'  => array( 'name' => __( 'Raw Material Standards', 'spicecraft-core' ), 'id' => '#sc-quality-raw-materials', 'desc' => __( 'Criteria and specifications required from spice cultivators.', 'spicecraft-core' ) ),
			'traceability'   => array( 'name' => __( 'Traceability Framework', 'spicecraft-core' ), 'id' => '#sc-quality-traceability', 'desc' => __( 'Batch identification and origin documentation steps.', 'spicecraft-core' ) ),
			'food_safety'    => array( 'name' => __( 'Food Safety Governance', 'spicecraft-core' ), 'id' => '#sc-quality-food-safety', 'desc' => __( 'Hygiene, sanitation, and regulatory adherence protocols.', 'spicecraft-core' ) ),
			'certifications' => array( 'name' => __( 'Accreditations & Certifications', 'spicecraft-core' ), 'id' => '#sc-quality-certifications', 'desc' => __( 'Food safety and trade certifications.', 'spicecraft-core' ) ),
			'statistics'     => array( 'name' => __( 'Quality Statistics', 'spicecraft-core' ), 'id' => '#sc-quality-stats', 'desc' => __( 'Documented metrics (e.g. testing turnaround, batch checks).', 'spicecraft-core' ) ),
			'gallery'        => array( 'name' => __( 'Quality & Origin Gallery', 'spicecraft-core' ), 'id' => '#sc-quality-gallery', 'desc' => __( 'Visual showcase of testing procedures, harvest origins, and inspection.', 'spicecraft-core' ) ),
			'products'       => array( 'name' => __( 'Tested Product Range', 'spicecraft-core' ), 'id' => '#sc-quality-products', 'desc' => __( 'Featured product categories subject to these quality standards.', 'spicecraft-core' ) ),
			'b2b_cta'        => array( 'name' => __( 'Quality Inquiry CTA', 'spicecraft-core' ), 'id' => '#sc-quality-b2b-cta', 'desc' => __( 'Technical specification sheets and COA request prompt.', 'spicecraft-core' ) ),
			'final_cta'      => array( 'name' => __( 'Direct Contact CTA', 'spicecraft-core' ), 'id' => '#sc-quality-final-cta', 'desc' => __( 'Direct phone, email, and WhatsApp connection channels.', 'spicecraft-core' ) ),
		);

		spicecraft_render_admin_section_order_table(
			self::OPTION_NAME,
			$settings['sections_order'],
			$settings['sections_enabled'],
			$section_defs
		);
	}

	/**
	 * Tab 2: Hero & Introduction
	 */
	private function render_tab_hero( $settings ) {
		$hero  = $settings['hero'];
		$intro = $settings['introduction'];
		?>
		<div class="sc-admin-card" style="background: #fff; border: 1px solid #ccd0d4; padding: 20px; border-radius: 4px; margin-bottom: 24px;">
			<h2 style="margin-top: 0; border-bottom: 1px solid #eee; padding-bottom: 8px;"><?php esc_html_e( 'Quality Hero Section', 'spicecraft-core' ); ?></h2>
			<table class="form-table" role="presentation">
				<tr>
					<th scope="row"><label for="hero_eyebrow"><?php esc_html_e( 'Eyebrow', 'spicecraft-core' ); ?></label></th>
					<td><input type="text" id="hero_eyebrow" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[hero][eyebrow]" value="<?php echo esc_attr( $hero['eyebrow'] ); ?>" class="regular-text" /></td>
				</tr>
				<tr>
					<th scope="row"><label for="hero_heading"><?php esc_html_e( 'Main Heading (H1)', 'spicecraft-core' ); ?></label></th>
					<td><input type="text" id="hero_heading" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[hero][heading]" value="<?php echo esc_attr( $hero['heading'] ); ?>" class="large-text" /></td>
				</tr>
				<tr>
					<th scope="row"><label for="hero_heading_highlight"><?php esc_html_e( 'Highlighted Phrase', 'spicecraft-core' ); ?></label></th>
					<td>
						<input type="text" id="hero_heading_highlight" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[hero][heading_highlight]" value="<?php echo esc_attr( $hero['heading_highlight'] ); ?>" class="regular-text" />
						<p class="description"><?php esc_html_e( 'Optional phrase inside H1 to render with brand accent styling.', 'spicecraft-core' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="hero_intro"><?php esc_html_e( 'Introduction Paragraph', 'spicecraft-core' ); ?></label></th>
					<td><textarea id="hero_intro" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[hero][intro]" rows="3" class="large-text"><?php echo esc_textarea( $hero['intro'] ); ?></textarea></td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Desktop Hero Image', 'spicecraft-core' ); ?></th>
					<td><?php spicecraft_render_admin_media_uploader( self::OPTION_NAME . '[hero][desktop_image_id]', $hero['desktop_image_id'], '', __( 'Select Desktop Hero', 'spicecraft-core' ) ); ?></td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Mobile Hero Image (Optional)', 'spicecraft-core' ); ?></th>
					<td><?php spicecraft_render_admin_media_uploader( self::OPTION_NAME . '[hero][mobile_image_id]', $hero['mobile_image_id'], '', __( 'Select Mobile Hero', 'spicecraft-core' ) ); ?></td>
				</tr>
				<tr>
					<th scope="row"><label for="hero_image_alt"><?php esc_html_e( 'Image Alt Text', 'spicecraft-core' ); ?></label></th>
					<td><input type="text" id="hero_image_alt" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[hero][image_alt]" value="<?php echo esc_attr( $hero['image_alt'] ); ?>" class="regular-text" /></td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Primary CTA', 'spicecraft-core' ); ?></th>
					<td>
						<input type="text" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[hero][cta_primary_label]" value="<?php echo esc_attr( $hero['cta_primary_label'] ); ?>" placeholder="Button Label" class="regular-text" style="margin-right: 8px;" />
						<input type="text" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[hero][cta_primary_url]" value="<?php echo esc_attr( $hero['cta_primary_url'] ); ?>" placeholder="URL (#sc-quality-testing, /contact/)" class="regular-text" />
					</td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Secondary CTA', 'spicecraft-core' ); ?></th>
					<td>
						<input type="text" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[hero][cta_secondary_label]" value="<?php echo esc_attr( $hero['cta_secondary_label'] ); ?>" placeholder="Button Label" class="regular-text" style="margin-right: 8px;" />
						<input type="text" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[hero][cta_secondary_url]" value="<?php echo esc_attr( $hero['cta_secondary_url'] ); ?>" placeholder="URL (#sc-quality-regions)" class="regular-text" />
					</td>
				</tr>
			</table>
		</div>

		<div class="sc-admin-card" style="background: #fff; border: 1px solid #ccd0d4; padding: 20px; border-radius: 4px;">
			<h2 style="margin-top: 0; border-bottom: 1px solid #eee; padding-bottom: 8px;"><?php esc_html_e( 'Quality Introduction', 'spicecraft-core' ); ?></h2>
			<table class="form-table" role="presentation">
				<tr>
					<th scope="row"><label for="intro_eyebrow"><?php esc_html_e( 'Eyebrow', 'spicecraft-core' ); ?></label></th>
					<td><input type="text" id="intro_eyebrow" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[introduction][eyebrow]" value="<?php echo esc_attr( $intro['eyebrow'] ); ?>" class="regular-text" /></td>
				</tr>
				<tr>
					<th scope="row"><label for="intro_heading"><?php esc_html_e( 'Heading', 'spicecraft-core' ); ?></label></th>
					<td><input type="text" id="intro_heading" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[introduction][heading]" value="<?php echo esc_attr( $intro['heading'] ); ?>" class="large-text" /></td>
				</tr>
				<tr>
					<th scope="row"><label for="intro_content"><?php esc_html_e( 'Detailed Philosophy', 'spicecraft-core' ); ?></label></th>
					<td><textarea id="intro_content" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[introduction][content]" rows="5" class="large-text"><?php echo esc_textarea( $intro['content'] ); ?></textarea></td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Primary Image', 'spicecraft-core' ); ?></th>
					<td><?php spicecraft_render_admin_media_uploader( self::OPTION_NAME . '[introduction][image_primary_id]', $intro['image_primary_id'], '', __( 'Select Primary Image', 'spicecraft-core' ) ); ?></td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Secondary Image (Supporting)', 'spicecraft-core' ); ?></th>
					<td><?php spicecraft_render_admin_media_uploader( self::OPTION_NAME . '[introduction][image_secondary_id]', $intro['image_secondary_id'], '', __( 'Select Secondary Image', 'spicecraft-core' ) ); ?></td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Optional Link / CTA', 'spicecraft-core' ); ?></th>
					<td>
						<input type="text" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[introduction][cta_label]" value="<?php echo esc_attr( $intro['cta_label'] ); ?>" placeholder="Link Text" class="regular-text" style="margin-right: 8px;" />
						<input type="text" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[introduction][cta_url]" value="<?php echo esc_attr( $intro['cta_url'] ); ?>" placeholder="URL (/manufacturing/, #sc-quality-process)" class="regular-text" />
					</td>
				</tr>
			</table>
		</div>
		<?php
	}

	/**
	 * Tab 3: Principles & Process
	 */
	private function render_tab_principles( $settings ) {
		$principles = $settings['principles'];
		$process    = $settings['process'];
		?>
		<div class="sc-admin-card" style="background: #fff; border: 1px solid #ccd0d4; padding: 20px; border-radius: 4px; margin-bottom: 24px;">
			<h2 style="margin-top: 0; border-bottom: 1px solid #eee; padding-bottom: 8px;"><?php esc_html_e( 'Quality Principles', 'spicecraft-core' ); ?></h2>
			<table class="form-table" role="presentation">
				<tr>
					<th scope="row"><label for="pr_eyebrow"><?php esc_html_e( 'Eyebrow', 'spicecraft-core' ); ?></label></th>
					<td><input type="text" id="pr_eyebrow" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[principles][eyebrow]" value="<?php echo esc_attr( $principles['eyebrow'] ); ?>" class="regular-text" /></td>
				</tr>
				<tr>
					<th scope="row"><label for="pr_heading"><?php esc_html_e( 'Heading', 'spicecraft-core' ); ?></label></th>
					<td><input type="text" id="pr_heading" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[principles][heading]" value="<?php echo esc_attr( $principles['heading'] ); ?>" class="large-text" /></td>
				</tr>
				<tr>
					<th scope="row"><label for="pr_description"><?php esc_html_e( 'Description', 'spicecraft-core' ); ?></label></th>
					<td><textarea id="pr_description" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[principles][description]" rows="3" class="large-text"><?php echo esc_textarea( $principles['description'] ); ?></textarea></td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Principles Repeatable Items', 'spicecraft-core' ); ?></th>
					<td>
						<div id="sc-principles-rows">
							<?php
							if ( ! empty( $principles['items'] ) && is_array( $principles['items'] ) ) :
								foreach ( $principles['items'] as $idx => $p_item ) :
									?>
									<div class="sc-repeatable-row" style="display: flex; gap: 8px; align-items: center; margin-bottom: 8px; background: #f9f9f9; padding: 8px; border: 1px solid #ccd0d4; border-radius: 4px;">
										<input type="text" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[principles][items][<?php echo esc_attr( $idx ); ?>][title]" value="<?php echo esc_attr( $p_item['title'] ?? '' ); ?>" placeholder="Principle Title (e.g. Total Purity)" class="regular-text" style="flex: 2;" />
										<input type="text" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[principles][items][<?php echo esc_attr( $idx ); ?>][description]" value="<?php echo esc_attr( $p_item['description'] ?? '' ); ?>" placeholder="Explanation of standard..." style="flex: 3;" />
										<input type="text" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[principles][items][<?php echo esc_attr( $idx ); ?>][icon]" value="<?php echo esc_attr( $p_item['icon'] ?? '' ); ?>" placeholder="Icon / Glyph" style="width: 80px;" />
										<input type="number" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[principles][items][<?php echo esc_attr( $idx ); ?>][order]" value="<?php echo esc_attr( $p_item['order'] ?? 10 ); ?>" placeholder="Order" style="width: 60px;" />
										<button type="button" class="button sc-remove-row-btn">&times;</button>
									</div>
									<?php
								endforeach;
							endif;
							?>
						</div>
						<button type="button" class="button button-secondary" id="sc-add-principle-btn" data-option-name="<?php echo esc_attr( self::OPTION_NAME ); ?>">
							<span class="dashicons dashicons-plus-alt2" style="margin-top: -2px;"></span>
							<?php esc_html_e( 'Add Principle', 'spicecraft-core' ); ?>
						</button>
					</td>
				</tr>
			</table>
		</div>

		<div class="sc-admin-card" style="background: #fff; border: 1px solid #ccd0d4; padding: 20px; border-radius: 4px;">
			<h2 style="margin-top: 0; border-bottom: 1px solid #eee; padding-bottom: 8px;"><?php esc_html_e( 'Quality Control Process', 'spicecraft-core' ); ?></h2>
			<table class="form-table" role="presentation">
				<tr>
					<th scope="row"><label for="qc_eyebrow"><?php esc_html_e( 'Eyebrow', 'spicecraft-core' ); ?></label></th>
					<td><input type="text" id="qc_eyebrow" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[process][eyebrow]" value="<?php echo esc_attr( $process['eyebrow'] ); ?>" class="regular-text" /></td>
				</tr>
				<tr>
					<th scope="row"><label for="qc_heading"><?php esc_html_e( 'Heading', 'spicecraft-core' ); ?></label></th>
					<td><input type="text" id="qc_heading" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[process][heading]" value="<?php echo esc_attr( $process['heading'] ); ?>" class="large-text" /></td>
				</tr>
				<tr>
					<th scope="row"><label for="qc_description"><?php esc_html_e( 'Description', 'spicecraft-core' ); ?></label></th>
					<td><textarea id="qc_description" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[process][description]" rows="3" class="large-text"><?php echo esc_textarea( $process['description'] ); ?></textarea></td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Quality Control Stages', 'spicecraft-core' ); ?></th>
					<td>
						<?php
						spicecraft_render_admin_process_rows(
							self::OPTION_NAME,
							$process['items'],
							'sc-qc-process-rows',
							'sc-add-qc-process-btn'
						);
						?>
					</td>
				</tr>
			</table>
		</div>
		<?php
	}

	/**
	 * Tab 4: Testing & Laboratory
	 */
	private function render_tab_testing( $settings ) {
		$testing = $settings['testing'];
		$context = $testing['testing_context'] ?? 'not_specified';
		?>
		<div class="sc-admin-card" style="background: #fff; border: 1px solid #ccd0d4; padding: 20px; border-radius: 4px;">
			<h2 style="margin-top: 0; border-bottom: 1px solid #eee; padding-bottom: 8px;"><?php esc_html_e( 'Testing Protocols & Analytical Laboratory Standards', 'spicecraft-core' ); ?></h2>
			<table class="form-table" role="presentation">
				<tr>
					<th scope="row"><label for="test_eyebrow"><?php esc_html_e( 'Eyebrow', 'spicecraft-core' ); ?></label></th>
					<td><input type="text" id="test_eyebrow" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[testing][eyebrow]" value="<?php echo esc_attr( $testing['eyebrow'] ); ?>" class="regular-text" /></td>
				</tr>
				<tr>
					<th scope="row"><label for="test_heading"><?php esc_html_e( 'Heading', 'spicecraft-core' ); ?></label></th>
					<td><input type="text" id="test_heading" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[testing][heading]" value="<?php echo esc_attr( $testing['heading'] ); ?>" class="large-text" /></td>
				</tr>
				<tr>
					<th scope="row"><label for="test_description"><?php esc_html_e( 'Description', 'spicecraft-core' ); ?></label></th>
					<td><textarea id="test_description" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[testing][description]" rows="3" class="large-text"><?php echo esc_textarea( $testing['description'] ); ?></textarea></td>
				</tr>
				<tr>
					<th scope="row"><label for="testing_context"><strong><?php esc_html_e( 'Testing Context (Required Rule)', 'spicecraft-core' ); ?></strong></label></th>
					<td>
						<select id="testing_context" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[testing][testing_context]" style="min-width: 280px;">
							<option value="not_specified" <?php selected( $context, 'not_specified' ); ?>><?php esc_html_e( 'Not Specified (Do not display laboratory claim)', 'spicecraft-core' ); ?></option>
							<option value="in_house" <?php selected( $context, 'in_house' ); ?>><?php esc_html_e( 'In-House Facility Testing', 'spicecraft-core' ); ?></option>
							<option value="external" <?php selected( $context, 'external' ); ?>><?php esc_html_e( 'External / Third-Party Accredited Testing', 'spicecraft-core' ); ?></option>
							<option value="combination" <?php selected( $context, 'combination' ); ?>><?php esc_html_e( 'Combination (In-House & Independent External)', 'spicecraft-core' ); ?></option>
						</select>
						<p class="description"><?php esc_html_e( 'Crucial integrity control: prevents falsely claiming an in-house laboratory when tests are conducted externally.', 'spicecraft-core' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Inspection Image', 'spicecraft-core' ); ?></th>
					<td><?php spicecraft_render_admin_media_uploader( self::OPTION_NAME . '[testing][image_id]', $testing['image_id'], '', __( 'Select Testing Image', 'spicecraft-core' ) ); ?></td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Analytical Test Items', 'spicecraft-core' ); ?></th>
					<td>
						<div id="sc-testing-rows">
							<?php
							if ( ! empty( $testing['items'] ) && is_array( $testing['items'] ) ) :
								foreach ( $testing['items'] as $idx => $t_item ) :
									?>
									<div class="sc-repeatable-row sc-card" style="padding: 12px; margin-bottom: 8px; background: #f9f9f9; border: 1px solid #ccd0d4; border-radius: 4px;">
										<div style="display: flex; gap: 8px; margin-bottom: 6px;">
											<input type="text" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[testing][items][<?php echo esc_attr( $idx ); ?>][name]" value="<?php echo esc_attr( $t_item['name'] ?? '' ); ?>" placeholder="Test Name (e.g. Moisture Analysis)" class="regular-text" style="flex: 2;" />
											<input type="text" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[testing][items][<?php echo esc_attr( $idx ); ?>][method]" value="<?php echo esc_attr( $t_item['method'] ?? '' ); ?>" placeholder="Method (e.g. Karl Fischer / ASTA)" style="flex: 1.5;" />
											<input type="text" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[testing][items][<?php echo esc_attr( $idx ); ?>][standard]" value="<?php echo esc_attr( $t_item['standard'] ?? '' ); ?>" placeholder="Standard (e.g. < 10% max)" style="flex: 1.5;" />
											<input type="number" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[testing][items][<?php echo esc_attr( $idx ); ?>][order]" value="<?php echo esc_attr( $t_item['order'] ?? 10 ); ?>" placeholder="Order" style="width: 60px;" />
											<button type="button" class="button sc-remove-row-btn">&times;</button>
										</div>
										<textarea name="<?php echo esc_attr( self::OPTION_NAME ); ?>[testing][items][<?php echo esc_attr( $idx ); ?>][description]" placeholder="Test explanation and significance for customer safety..." rows="2" style="width: 100%;"><?php echo esc_textarea( $t_item['description'] ?? '' ); ?></textarea>
									</div>
									<?php
								endforeach;
							endif;
							?>
						</div>
						<button type="button" class="button button-secondary" id="sc-add-testing-btn" data-option-name="<?php echo esc_attr( self::OPTION_NAME ); ?>">
							<span class="dashicons dashicons-plus-alt2" style="margin-top: -2px;"></span>
							<?php esc_html_e( 'Add Test / Control Item', 'spicecraft-core' ); ?>
						</button>
					</td>
				</tr>
			</table>
		</div>
		<?php
	}

	/**
	 * Tab 5: Sourcing & Regions
	 */
	private function render_tab_sourcing( $settings ) {
		$sourcing = $settings['sourcing'];
		$regions  = $settings['regions'];
		?>
		<div class="sc-admin-card" style="background: #fff; border: 1px solid #ccd0d4; padding: 20px; border-radius: 4px; margin-bottom: 24px;">
			<h2 style="margin-top: 0; border-bottom: 1px solid #eee; padding-bottom: 8px;"><?php esc_html_e( 'Sourcing Philosophy', 'spicecraft-core' ); ?></h2>
			<table class="form-table" role="presentation">
				<tr>
					<th scope="row"><label for="src_eyebrow"><?php esc_html_e( 'Eyebrow', 'spicecraft-core' ); ?></label></th>
					<td><input type="text" id="src_eyebrow" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[sourcing][eyebrow]" value="<?php echo esc_attr( $sourcing['eyebrow'] ); ?>" class="regular-text" /></td>
				</tr>
				<tr>
					<th scope="row"><label for="src_heading"><?php esc_html_e( 'Heading', 'spicecraft-core' ); ?></label></th>
					<td><input type="text" id="src_heading" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[sourcing][heading]" value="<?php echo esc_attr( $sourcing['heading'] ); ?>" class="large-text" /></td>
				</tr>
				<tr>
					<th scope="row"><label for="src_description"><?php esc_html_e( 'Description', 'spicecraft-core' ); ?></label></th>
					<td><textarea id="src_description" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[sourcing][description]" rows="3" class="large-text"><?php echo esc_textarea( $sourcing['description'] ); ?></textarea></td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Primary Photo', 'spicecraft-core' ); ?></th>
					<td><?php spicecraft_render_admin_media_uploader( self::OPTION_NAME . '[sourcing][image_id]', $sourcing['image_id'], '', __( 'Select Primary Image', 'spicecraft-core' ) ); ?></td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Secondary Photo', 'spicecraft-core' ); ?></th>
					<td><?php spicecraft_render_admin_media_uploader( self::OPTION_NAME . '[sourcing][image_secondary_id]', $sourcing['image_secondary_id'], '', __( 'Select Secondary Image', 'spicecraft-core' ) ); ?></td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Key Pillars / Highlights', 'spicecraft-core' ); ?></th>
					<td>
						<div id="sc-sourcing-highlights">
							<?php
							if ( ! empty( $sourcing['highlights'] ) && is_array( $sourcing['highlights'] ) ) :
								foreach ( $sourcing['highlights'] as $idx => $hl ) :
									?>
									<div class="sc-repeatable-row" style="display: flex; gap: 8px; margin-bottom: 6px;">
										<input type="text" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[sourcing][highlights][<?php echo esc_attr( $idx ); ?>][title]" value="<?php echo esc_attr( $hl['title'] ?? '' ); ?>" placeholder="Pillar Title (e.g. Direct Farm Ties)" style="flex: 1;" />
										<input type="text" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[sourcing][highlights][<?php echo esc_attr( $idx ); ?>][description]" value="<?php echo esc_attr( $hl['description'] ?? '' ); ?>" placeholder="Short detail..." style="flex: 2;" />
										<button type="button" class="button sc-remove-row-btn">&times;</button>
									</div>
									<?php
								endforeach;
							endif;
							?>
						</div>
						<button type="button" class="button button-secondary" id="sc-add-sourcing-hl-btn" data-option-name="<?php echo esc_attr( self::OPTION_NAME ); ?>">
							<span class="dashicons dashicons-plus-alt2" style="margin-top: -2px;"></span>
							<?php esc_html_e( 'Add Sourcing Highlight', 'spicecraft-core' ); ?>
						</button>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Optional CTA', 'spicecraft-core' ); ?></th>
					<td>
						<input type="text" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[sourcing][cta_label]" value="<?php echo esc_attr( $sourcing['cta_label'] ); ?>" placeholder="Button Label" class="regular-text" style="margin-right: 8px;" />
						<input type="text" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[sourcing][cta_url]" value="<?php echo esc_attr( $sourcing['cta_url'] ); ?>" placeholder="URL (#sc-quality-regions)" class="regular-text" />
					</td>
				</tr>
			</table>
		</div>

		<div class="sc-admin-card" style="background: #fff; border: 1px solid #ccd0d4; padding: 20px; border-radius: 4px;">
			<h2 style="margin-top: 0; border-bottom: 1px solid #eee; padding-bottom: 8px;"><?php esc_html_e( 'Structured Sourcing Regions', 'spicecraft-core' ); ?></h2>
			<table class="form-table" role="presentation">
				<tr>
					<th scope="row"><label for="reg_eyebrow"><?php esc_html_e( 'Eyebrow', 'spicecraft-core' ); ?></label></th>
					<td><input type="text" id="reg_eyebrow" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[regions][eyebrow]" value="<?php echo esc_attr( $regions['eyebrow'] ); ?>" class="regular-text" /></td>
				</tr>
				<tr>
					<th scope="row"><label for="reg_heading"><?php esc_html_e( 'Heading', 'spicecraft-core' ); ?></label></th>
					<td><input type="text" id="reg_heading" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[regions][heading]" value="<?php echo esc_attr( $regions['heading'] ); ?>" class="large-text" /></td>
				</tr>
				<tr>
					<th scope="row"><label for="reg_description"><?php esc_html_e( 'Description', 'spicecraft-core' ); ?></label></th>
					<td><textarea id="reg_description" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[regions][description]" rows="3" class="large-text"><?php echo esc_textarea( $regions['description'] ); ?></textarea></td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Geographic Regions', 'spicecraft-core' ); ?></th>
					<td><?php spicecraft_render_admin_region_rows( self::OPTION_NAME, $regions['items'] ); ?></td>
				</tr>
			</table>
		</div>
		<?php
	}

	/**
	 * Tab 6: Standards, Traceability & Food Safety
	 */
	private function render_tab_traceability( $settings ) {
		$raw_m   = $settings['raw_materials'];
		$trace   = $settings['traceability'];
		$safety  = $settings['food_safety'];
		?>
		<div class="sc-admin-card" style="background: #fff; border: 1px solid #ccd0d4; padding: 20px; border-radius: 4px; margin-bottom: 24px;">
			<h2 style="margin-top: 0; border-bottom: 1px solid #eee; padding-bottom: 8px;"><?php esc_html_e( 'Raw Material & Supplier Standards', 'spicecraft-core' ); ?></h2>
			<table class="form-table" role="presentation">
				<tr>
					<th scope="row"><label for="rm_heading"><?php esc_html_e( 'Heading', 'spicecraft-core' ); ?></label></th>
					<td><input type="text" id="rm_heading" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[raw_materials][heading]" value="<?php echo esc_attr( $raw_m['heading'] ); ?>" class="large-text" /></td>
				</tr>
				<tr>
					<th scope="row"><label for="rm_description"><?php esc_html_e( 'Description', 'spicecraft-core' ); ?></label></th>
					<td><textarea id="rm_description" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[raw_materials][description]" rows="3" class="large-text"><?php echo esc_textarea( $raw_m['description'] ); ?></textarea></td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Supplier Standards List', 'spicecraft-core' ); ?></th>
					<td>
						<div id="sc-raw-materials-rows">
							<?php
							if ( ! empty( $raw_m['items'] ) && is_array( $raw_m['items'] ) ) :
								foreach ( $raw_m['items'] as $idx => $it ) :
									?>
									<div class="sc-repeatable-row" style="display: flex; gap: 8px; align-items: center; margin-bottom: 6px; background: #f9f9f9; padding: 8px; border: 1px solid #ccd0d4; border-radius: 4px;">
										<input type="text" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[raw_materials][items][<?php echo esc_attr( $idx ); ?>][title]" value="<?php echo esc_attr( $it['title'] ?? '' ); ?>" placeholder="Standard Title" style="flex: 2;" />
										<input type="text" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[raw_materials][items][<?php echo esc_attr( $idx ); ?>][description]" value="<?php echo esc_attr( $it['description'] ?? '' ); ?>" placeholder="Evaluation criteria..." style="flex: 3;" />
										<input type="number" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[raw_materials][items][<?php echo esc_attr( $idx ); ?>][order]" value="<?php echo esc_attr( $it['order'] ?? 10 ); ?>" placeholder="Order" style="width: 60px;" />
										<button type="button" class="button sc-remove-row-btn">&times;</button>
									</div>
									<?php
								endforeach;
							endif;
							?>
						</div>
						<button type="button" class="button button-secondary" id="sc-add-raw-m-btn" data-option-name="<?php echo esc_attr( self::OPTION_NAME ); ?>">
							<span class="dashicons dashicons-plus-alt2" style="margin-top: -2px;"></span>
							<?php esc_html_e( 'Add Supplier Standard', 'spicecraft-core' ); ?>
						</button>
					</td>
				</tr>
			</table>
		</div>

		<div class="sc-admin-card" style="background: #fff; border: 1px solid #ccd0d4; padding: 20px; border-radius: 4px; margin-bottom: 24px;">
			<h2 style="margin-top: 0; border-bottom: 1px solid #eee; padding-bottom: 8px;"><?php esc_html_e( 'Traceability Framework', 'spicecraft-core' ); ?></h2>
			<table class="form-table" role="presentation">
				<tr>
					<th scope="row"><label for="tr_heading"><?php esc_html_e( 'Heading', 'spicecraft-core' ); ?></label></th>
					<td><input type="text" id="tr_heading" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[traceability][heading]" value="<?php echo esc_attr( $trace['heading'] ); ?>" class="large-text" /></td>
				</tr>
				<tr>
					<th scope="row"><label for="tr_description"><?php esc_html_e( 'Description', 'spicecraft-core' ); ?></label></th>
					<td><textarea id="tr_description" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[traceability][description]" rows="3" class="large-text"><?php echo esc_textarea( $trace['description'] ); ?></textarea></td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Traceability Diagram / Photo', 'spicecraft-core' ); ?></th>
					<td><?php spicecraft_render_admin_media_uploader( self::OPTION_NAME . '[traceability][image_id]', $trace['image_id'], '', __( 'Select Traceability Image', 'spicecraft-core' ) ); ?></td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Traceability Chain Steps', 'spicecraft-core' ); ?></th>
					<td>
						<div id="sc-traceability-steps">
							<?php
							if ( ! empty( $trace['steps'] ) && is_array( $trace['steps'] ) ) :
								foreach ( $trace['steps'] as $idx => $st ) :
									?>
									<div class="sc-repeatable-row" style="display: flex; gap: 8px; align-items: center; margin-bottom: 6px; background: #f9f9f9; padding: 8px; border: 1px solid #ccd0d4; border-radius: 4px;">
										<input type="text" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[traceability][steps][<?php echo esc_attr( $idx ); ?>][step_number]" value="<?php echo esc_attr( $st['step_number'] ?? ( $idx + 1 ) ); ?>" placeholder="01" style="width: 50px;" />
										<input type="text" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[traceability][steps][<?php echo esc_attr( $idx ); ?>][title]" value="<?php echo esc_attr( $st['title'] ?? '' ); ?>" placeholder="Checkpoint (e.g. Origin Batch Tagging)" style="flex: 2;" />
										<input type="text" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[traceability][steps][<?php echo esc_attr( $idx ); ?>][description]" value="<?php echo esc_attr( $st['description'] ?? '' ); ?>" placeholder="Record keeping details..." style="flex: 3;" />
										<button type="button" class="button sc-remove-row-btn">&times;</button>
									</div>
									<?php
								endforeach;
							endif;
							?>
						</div>
						<button type="button" class="button button-secondary" id="sc-add-trace-step-btn" data-option-name="<?php echo esc_attr( self::OPTION_NAME ); ?>">
							<span class="dashicons dashicons-plus-alt2" style="margin-top: -2px;"></span>
							<?php esc_html_e( 'Add Traceability Step', 'spicecraft-core' ); ?>
						</button>
					</td>
				</tr>
			</table>
		</div>

		<div class="sc-admin-card" style="background: #fff; border: 1px solid #ccd0d4; padding: 20px; border-radius: 4px;">
			<h2 style="margin-top: 0; border-bottom: 1px solid #eee; padding-bottom: 8px;"><?php esc_html_e( 'Food Safety Practices', 'spicecraft-core' ); ?></h2>
			<table class="form-table" role="presentation">
				<tr>
					<th scope="row"><label for="fs_heading"><?php esc_html_e( 'Heading', 'spicecraft-core' ); ?></label></th>
					<td><input type="text" id="fs_heading" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[food_safety][heading]" value="<?php echo esc_attr( $safety['heading'] ); ?>" class="large-text" /></td>
				</tr>
				<tr>
					<th scope="row"><label for="fs_description"><?php esc_html_e( 'Description', 'spicecraft-core' ); ?></label></th>
					<td><textarea id="fs_description" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[food_safety][description]" rows="3" class="large-text"><?php echo esc_textarea( $safety['description'] ); ?></textarea></td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Food Safety Supporting Image', 'spicecraft-core' ); ?></th>
					<td><?php spicecraft_render_admin_media_uploader( self::OPTION_NAME . '[food_safety][image_id]', $safety['image_id'], '', __( 'Select Image', 'spicecraft-core' ) ); ?></td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Safety Practices List', 'spicecraft-core' ); ?></th>
					<td>
						<div id="sc-safety-practices">
							<?php
							if ( ! empty( $safety['practices'] ) && is_array( $safety['practices'] ) ) :
								foreach ( $safety['practices'] as $idx => $pr ) :
									?>
									<div class="sc-repeatable-row" style="display: flex; gap: 8px; margin-bottom: 6px;">
										<input type="text" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[food_safety][practices][<?php echo esc_attr( $idx ); ?>][title]" value="<?php echo esc_attr( $pr['title'] ?? '' ); ?>" placeholder="Practice Title" style="flex: 1;" />
										<input type="text" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[food_safety][practices][<?php echo esc_attr( $idx ); ?>][description]" value="<?php echo esc_attr( $pr['description'] ?? '' ); ?>" placeholder="Protocol description..." style="flex: 2;" />
										<button type="button" class="button sc-remove-row-btn">&times;</button>
									</div>
									<?php
								endforeach;
							endif;
							?>
						</div>
						<button type="button" class="button button-secondary" id="sc-add-safety-practice-btn" data-option-name="<?php echo esc_attr( self::OPTION_NAME ); ?>">
							<span class="dashicons dashicons-plus-alt2" style="margin-top: -2px;"></span>
							<?php esc_html_e( 'Add Safety Practice', 'spicecraft-core' ); ?>
						</button>
					</td>
				</tr>
			</table>
		</div>
		<?php
	}

	/**
	 * Tab 7: Trust, Stats & Products
	 */
	private function render_tab_trust( $settings ) {
		$stats   = $settings['statistics'];
		$gallery = $settings['gallery'];
		$certs   = $settings['certifications'];
		$prods   = $settings['products'];
		?>
		<div class="sc-admin-card" style="background: #fff; border: 1px solid #ccd0d4; padding: 20px; border-radius: 4px; margin-bottom: 24px;">
			<h2 style="margin-top: 0; border-bottom: 1px solid #eee; padding-bottom: 8px;"><?php esc_html_e( 'Quality Statistics (Metrics & Figures)', 'spicecraft-core' ); ?></h2>
			<table class="form-table" role="presentation">
				<tr>
					<th scope="row"><label for="stat_heading"><?php esc_html_e( 'Heading', 'spicecraft-core' ); ?></label></th>
					<td><input type="text" id="stat_heading" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[statistics][heading]" value="<?php echo esc_attr( $stats['heading'] ); ?>" class="large-text" /></td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Configured Metrics', 'spicecraft-core' ); ?></th>
					<td>
						<?php
						spicecraft_render_admin_stat_rows(
							self::OPTION_NAME,
							$stats['items'],
							'sc-quality-stat-rows',
							'sc-add-quality-stat-btn'
						);
						?>
					</td>
				</tr>
			</table>
		</div>

		<div class="sc-admin-card" style="background: #fff; border: 1px solid #ccd0d4; padding: 20px; border-radius: 4px; margin-bottom: 24px;">
			<h2 style="margin-top: 0; border-bottom: 1px solid #eee; padding-bottom: 8px;"><?php esc_html_e( 'Quality & Origin Gallery', 'spicecraft-core' ); ?></h2>
			<table class="form-table" role="presentation">
				<tr>
					<th scope="row"><label for="gal_heading"><?php esc_html_e( 'Heading', 'spicecraft-core' ); ?></label></th>
					<td><input type="text" id="gal_heading" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[gallery][heading]" value="<?php echo esc_attr( $gallery['heading'] ); ?>" class="large-text" /></td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Gallery Photos', 'spicecraft-core' ); ?></th>
					<td><?php spicecraft_render_admin_gallery_uploader( self::OPTION_NAME . '[gallery][attachment_ids]', $gallery['attachment_ids'] ); ?></td>
				</tr>
			</table>
		</div>

		<div class="sc-admin-card" style="background: #fff; border: 1px solid #ccd0d4; padding: 20px; border-radius: 4px; margin-bottom: 24px;">
			<h2 style="margin-top: 0; border-bottom: 1px solid #eee; padding-bottom: 8px;"><?php esc_html_e( 'Accreditations & Certifications Integration', 'spicecraft-core' ); ?></h2>
			<table class="form-table" role="presentation">
				<tr>
					<th scope="row"><label for="cert_heading"><?php esc_html_e( 'Heading', 'spicecraft-core' ); ?></label></th>
					<td><input type="text" id="cert_heading" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[certifications][heading]" value="<?php echo esc_attr( $certs['heading'] ); ?>" class="large-text" /></td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Select Certifications', 'spicecraft-core' ); ?></th>
					<td>
						<?php
						spicecraft_render_admin_taxonomy_multiselect(
							'spicecraft_certification',
							self::OPTION_NAME . '[certifications][selected_ids]',
							$certs['selected_ids']
						);
						?>
						<p class="description"><?php esc_html_e( 'Select the published certifications relevant to quality standards. Reuses existing certification architecture.', 'spicecraft-core' ); ?></p>
					</td>
				</tr>
			</table>
		</div>

		<div class="sc-admin-card" style="background: #fff; border: 1px solid #ccd0d4; padding: 20px; border-radius: 4px;">
			<h2 style="margin-top: 0; border-bottom: 1px solid #eee; padding-bottom: 8px;"><?php esc_html_e( 'Tested Products Connection', 'spicecraft-core' ); ?></h2>
			<table class="form-table" role="presentation">
				<tr>
					<th scope="row"><label for="prod_heading"><?php esc_html_e( 'Heading', 'spicecraft-core' ); ?></label></th>
					<td><input type="text" id="prod_heading" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[products][heading]" value="<?php echo esc_attr( $prods['heading'] ); ?>" class="large-text" /></td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Display Mode', 'spicecraft-core' ); ?></th>
					<td>
						<label style="margin-right: 16px;">
							<input type="radio" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[products][source]" value="categories" <?php checked( $prods['source'], 'categories' ); ?> />
							<?php esc_html_e( 'Product Categories', 'spicecraft-core' ); ?>
						</label>
						<label>
							<input type="radio" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[products][source]" value="products" <?php checked( $prods['source'], 'products' ); ?> />
							<?php esc_html_e( 'Selected Products', 'spicecraft-core' ); ?>
						</label>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Selected Items', 'spicecraft-core' ); ?></th>
					<td>
						<p class="description" style="margin-bottom: 4px;"><?php esc_html_e( 'If Categories selected:', 'spicecraft-core' ); ?></p>
						<?php
						spicecraft_render_admin_taxonomy_multiselect(
							'product_cat',
							self::OPTION_NAME . '[products][selected_ids]',
							$prods['selected_ids']
						);
						?>
					</td>
				</tr>
			</table>
		</div>
		<?php
	}

	/**
	 * Tab 8: Business CTAs
	 */
	private function render_tab_cta( $settings ) {
		$b2b   = $settings['b2b_cta'];
		$final = $settings['final_cta'];
		?>
		<div class="sc-admin-card" style="background: #fff; border: 1px solid #ccd0d4; padding: 20px; border-radius: 4px; margin-bottom: 24px;">
			<h2 style="margin-top: 0; border-bottom: 1px solid #eee; padding-bottom: 8px;"><?php esc_html_e( 'Quality & Sourcing Technical Enquiry CTA', 'spicecraft-core' ); ?></h2>
			<table class="form-table" role="presentation">
				<tr>
					<th scope="row"><label for="b2b_heading"><?php esc_html_e( 'Heading', 'spicecraft-core' ); ?></label></th>
					<td><input type="text" id="b2b_heading" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[b2b_cta][heading]" value="<?php echo esc_attr( $b2b['heading'] ); ?>" class="large-text" /></td>
				</tr>
				<tr>
					<th scope="row"><label for="b2b_desc"><?php esc_html_e( 'Description', 'spicecraft-core' ); ?></label></th>
					<td><textarea id="b2b_desc" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[b2b_cta][description]" rows="3" class="large-text"><?php echo esc_textarea( $b2b['description'] ); ?></textarea></td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Background Image', 'spicecraft-core' ); ?></th>
					<td><?php spicecraft_render_admin_media_uploader( self::OPTION_NAME . '[b2b_cta][bg_image_id]', $b2b['bg_image_id'], '', __( 'Select Background', 'spicecraft-core' ) ); ?></td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Primary CTA', 'spicecraft-core' ); ?></th>
					<td>
						<input type="text" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[b2b_cta][primary_cta_label]" value="<?php echo esc_attr( $b2b['primary_cta_label'] ); ?>" placeholder="Label (e.g. Request Batch COA)" class="regular-text" style="margin-right: 8px;" />
						<input type="text" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[b2b_cta][primary_cta_url]" value="<?php echo esc_attr( $b2b['primary_cta_url'] ); ?>" placeholder="URL (/contact/)" class="regular-text" />
					</td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Secondary CTA', 'spicecraft-core' ); ?></th>
					<td>
						<input type="text" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[b2b_cta][secondary_cta_label]" value="<?php echo esc_attr( $b2b['secondary_cta_label'] ); ?>" placeholder="Label" class="regular-text" style="margin-right: 8px;" />
						<input type="text" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[b2b_cta][secondary_cta_url]" value="<?php echo esc_attr( $b2b['secondary_cta_url'] ); ?>" placeholder="URL" class="regular-text" />
					</td>
				</tr>
			</table>
		</div>

		<div class="sc-admin-card" style="background: #fff; border: 1px solid #ccd0d4; padding: 20px; border-radius: 4px;">
			<h2 style="margin-top: 0; border-bottom: 1px solid #eee; padding-bottom: 8px;"><?php esc_html_e( 'Direct Contact CTA Section', 'spicecraft-core' ); ?></h2>
			<table class="form-table" role="presentation">
				<tr>
					<th scope="row"><label for="fin_heading"><?php esc_html_e( 'Heading', 'spicecraft-core' ); ?></label></th>
					<td><input type="text" id="fin_heading" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[final_cta][heading]" value="<?php echo esc_attr( $final['heading'] ); ?>" class="large-text" /></td>
				</tr>
				<tr>
					<th scope="row"><label for="fin_desc"><?php esc_html_e( 'Description', 'spicecraft-core' ); ?></label></th>
					<td><textarea id="fin_desc" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[final_cta][description]" rows="3" class="large-text"><?php echo esc_textarea( $final['description'] ); ?></textarea></td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Connect Channels', 'spicecraft-core' ); ?></th>
					<td>
						<label style="margin-right: 16px;">
							<input type="checkbox" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[final_cta][show_whatsapp]" value="1" <?php checked( ! empty( $final['show_whatsapp'] ) ); ?> />
							<?php esc_html_e( 'Show WhatsApp Button (Global Phone)', 'spicecraft-core' ); ?>
						</label>
						<label>
							<input type="checkbox" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[final_cta][show_email]" value="1" <?php checked( ! empty( $final['show_email'] ) ); ?> />
							<?php esc_html_e( 'Show Email Button (Global Email)', 'spicecraft-core' ); ?>
						</label>
					</td>
				</tr>
			</table>
		</div>
		<?php
	}

	/**
	 * Hidden inputs to preserve unedited tabs.
	 */
	private function render_hidden_tabs( $settings, $active_tab ) {
		$tab_sections = array(
			'order'        => array( 'sections_order', 'sections_enabled' ),
			'hero'         => array( 'hero', 'introduction' ),
			'principles'   => array( 'principles', 'process' ),
			'testing'      => array( 'testing' ),
			'sourcing'     => array( 'sourcing', 'regions' ),
			'traceability' => array( 'raw_materials', 'traceability', 'food_safety' ),
			'trust'        => array( 'statistics', 'gallery', 'certifications', 'products' ),
			'cta'          => array( 'b2b_cta', 'final_cta' ),
		);

		foreach ( $tab_sections as $t_key => $sec_keys ) {
			if ( $t_key === $active_tab ) {
				continue;
			}
			foreach ( $sec_keys as $s_key ) {
				if ( isset( $settings[ $s_key ] ) ) {
					$val = $settings[ $s_key ];
					$this->render_hidden_recursive( self::OPTION_NAME . '[' . $s_key . ']', $val );
				}
			}
		}
	}

	/**
	 * Recursively render hidden inputs for arbitrary nested arrays.
	 */
	private function render_hidden_recursive( $name_prefix, $data ) {
		if ( is_array( $data ) ) {
			foreach ( $data as $k => $v ) {
				$this->render_hidden_recursive( $name_prefix . '[' . $k . ']', $v );
			}
		} else {
			echo '<input type="hidden" name="' . esc_attr( $name_prefix ) . '" value="' . esc_attr( (string) $data ) . '" />' . "\n";
		}
	}

	/**
	 * Sanitize Settings upon save.
	 */
	public function sanitize_settings( $input ) {
		$valid_keys = array_keys( spicecraft_get_quality_default_settings()['sections_order'] );
		$output     = array();

		// Sections order & enabled
		$output['sections_order']   = spicecraft_sanitize_order_array( $input['sections_order'] ?? array(), $valid_keys );
		$output['sections_enabled'] = spicecraft_sanitize_enabled_array( $input['sections_enabled'] ?? array(), $valid_keys );

		// Hero
		$hero = $input['hero'] ?? array();
		$output['hero'] = array(
			'eyebrow'             => sanitize_text_field( $hero['eyebrow'] ?? '' ),
			'heading'             => sanitize_text_field( $hero['heading'] ?? '' ),
			'heading_highlight'   => sanitize_text_field( $hero['heading_highlight'] ?? '' ),
			'intro'               => sanitize_textarea_field( $hero['intro'] ?? '' ),
			'desktop_image_id'    => absint( $hero['desktop_image_id'] ?? 0 ),
			'mobile_image_id'     => absint( $hero['mobile_image_id'] ?? 0 ),
			'image_alt'           => sanitize_text_field( $hero['image_alt'] ?? '' ),
			'cta_primary_label'   => sanitize_text_field( $hero['cta_primary_label'] ?? '' ),
			'cta_primary_url'     => sanitize_text_field( $hero['cta_primary_url'] ?? '' ),
			'cta_secondary_label' => sanitize_text_field( $hero['cta_secondary_label'] ?? '' ),
			'cta_secondary_url'   => sanitize_text_field( $hero['cta_secondary_url'] ?? '' ),
		);

		// Introduction
		$intro = $input['introduction'] ?? array();
		$output['introduction'] = array(
			'eyebrow'            => sanitize_text_field( $intro['eyebrow'] ?? '' ),
			'heading'            => sanitize_text_field( $intro['heading'] ?? '' ),
			'content'            => wp_kses_post( $intro['content'] ?? '' ),
			'image_primary_id'   => absint( $intro['image_primary_id'] ?? 0 ),
			'image_secondary_id' => absint( $intro['image_secondary_id'] ?? 0 ),
			'cta_label'          => sanitize_text_field( $intro['cta_label'] ?? '' ),
			'cta_url'            => sanitize_text_field( $intro['cta_url'] ?? '' ),
		);

		// Principles
		$pr = $input['principles'] ?? array();
		$pr_items = array();
		if ( ! empty( $pr['items'] ) && is_array( $pr['items'] ) ) {
			foreach ( $pr['items'] as $item ) {
				$t = sanitize_text_field( $item['title'] ?? '' );
				$d = sanitize_text_field( $item['description'] ?? '' );
				if ( empty( $t ) && empty( $d ) ) continue;
				$pr_items[] = array(
					'title'       => $t,
					'description' => $d,
					'icon'        => sanitize_text_field( $item['icon'] ?? '' ),
					'order'       => absint( $item['order'] ?? 10 ),
				);
			}
			usort( $pr_items, fn( $a, $b ) => $a['order'] <=> $b['order'] );
		}
		$output['principles'] = array(
			'eyebrow'     => sanitize_text_field( $pr['eyebrow'] ?? '' ),
			'heading'     => sanitize_text_field( $pr['heading'] ?? '' ),
			'description' => sanitize_textarea_field( $pr['description'] ?? '' ),
			'items'       => $pr_items,
		);

		// Quality Process
		$qc = $input['process'] ?? array();
		$output['process'] = array(
			'eyebrow'     => sanitize_text_field( $qc['eyebrow'] ?? '' ),
			'heading'     => sanitize_text_field( $qc['heading'] ?? '' ),
			'description' => sanitize_textarea_field( $qc['description'] ?? '' ),
			'items'       => spicecraft_sanitize_process_items( $qc['items'] ?? array() ),
		);

		// Testing
		$test = $input['testing'] ?? array();
		$valid_contexts = array( 'not_specified', 'in_house', 'external', 'combination' );
		$context = in_array( $test['testing_context'] ?? '', $valid_contexts, true ) ? $test['testing_context'] : 'not_specified';

		$test_items = array();
		if ( ! empty( $test['items'] ) && is_array( $test['items'] ) ) {
			foreach ( $test['items'] as $it ) {
				$name = sanitize_text_field( $it['name'] ?? '' );
				$desc = sanitize_textarea_field( $it['description'] ?? '' );
				if ( empty( $name ) && empty( $desc ) ) continue;
				$test_items[] = array(
					'name'        => $name,
					'description' => $desc,
					'method'      => sanitize_text_field( $it['method'] ?? '' ),
					'standard'    => sanitize_text_field( $it['standard'] ?? '' ),
					'order'       => absint( $it['order'] ?? 10 ),
				);
			}
			usort( $test_items, fn( $a, $b ) => $a['order'] <=> $b['order'] );
		}

		$output['testing'] = array(
			'eyebrow'         => sanitize_text_field( $test['eyebrow'] ?? '' ),
			'heading'         => sanitize_text_field( $test['heading'] ?? '' ),
			'description'     => sanitize_textarea_field( $test['description'] ?? '' ),
			'image_id'        => absint( $test['image_id'] ?? 0 ),
			'testing_context' => $context,
			'items'           => $test_items,
		);

		// Sourcing
		$src = $input['sourcing'] ?? array();
		$src_highlights = array();
		if ( ! empty( $src['highlights'] ) && is_array( $src['highlights'] ) ) {
			foreach ( $src['highlights'] as $hl ) {
				$ht = sanitize_text_field( $hl['title'] ?? '' );
				$hd = sanitize_text_field( $hl['description'] ?? '' );
				if ( empty( $ht ) && empty( $hd ) ) continue;
				$src_highlights[] = array( 'title' => $ht, 'description' => $hd );
			}
		}
		$output['sourcing'] = array(
			'eyebrow'            => sanitize_text_field( $src['eyebrow'] ?? '' ),
			'heading'            => sanitize_text_field( $src['heading'] ?? '' ),
			'description'        => sanitize_textarea_field( $src['description'] ?? '' ),
			'image_id'           => absint( $src['image_id'] ?? 0 ),
			'image_secondary_id' => absint( $src['image_secondary_id'] ?? 0 ),
			'highlights'         => $src_highlights,
			'cta_label'          => sanitize_text_field( $src['cta_label'] ?? '' ),
			'cta_url'            => sanitize_text_field( $src['cta_url'] ?? '' ),
		);

		// Regions
		$reg = $input['regions'] ?? array();
		$output['regions'] = array(
			'eyebrow'     => sanitize_text_field( $reg['eyebrow'] ?? '' ),
			'heading'     => sanitize_text_field( $reg['heading'] ?? '' ),
			'description' => sanitize_textarea_field( $reg['description'] ?? '' ),
			'items'       => spicecraft_sanitize_region_items( $reg['items'] ?? array() ),
		);

		// Raw Materials
		$rm = $input['raw_materials'] ?? array();
		$rm_items = array();
		if ( ! empty( $rm['items'] ) && is_array( $rm['items'] ) ) {
			foreach ( $rm['items'] as $it ) {
				$rt = sanitize_text_field( $it['title'] ?? '' );
				$rd = sanitize_text_field( $it['description'] ?? '' );
				if ( empty( $rt ) && empty( $rd ) ) continue;
				$rm_items[] = array(
					'title'       => $rt,
					'description' => $rd,
					'icon'        => sanitize_text_field( $it['icon'] ?? '' ),
					'order'       => absint( $it['order'] ?? 10 ),
				);
			}
			usort( $rm_items, fn( $a, $b ) => $a['order'] <=> $b['order'] );
		}
		$output['raw_materials'] = array(
			'eyebrow'     => sanitize_text_field( $rm['eyebrow'] ?? '' ),
			'heading'     => sanitize_text_field( $rm['heading'] ?? '' ),
			'description' => sanitize_textarea_field( $rm['description'] ?? '' ),
			'items'       => $rm_items,
		);

		// Traceability
		$tr = $input['traceability'] ?? array();
		$tr_steps = array();
		if ( ! empty( $tr['steps'] ) && is_array( $tr['steps'] ) ) {
			foreach ( $tr['steps'] as $st ) {
				$st_title = sanitize_text_field( $st['title'] ?? '' );
				$st_desc  = sanitize_text_field( $st['description'] ?? '' );
				if ( empty( $st_title ) && empty( $st_desc ) ) continue;
				$tr_steps[] = array(
					'step_number' => sanitize_text_field( $st['step_number'] ?? '' ),
					'title'       => $st_title,
					'description' => $st_desc,
					'order'       => absint( $st['order'] ?? 10 ),
				);
			}
		}
		$output['traceability'] = array(
			'eyebrow'     => sanitize_text_field( $tr['eyebrow'] ?? '' ),
			'heading'     => sanitize_text_field( $tr['heading'] ?? '' ),
			'description' => sanitize_textarea_field( $tr['description'] ?? '' ),
			'image_id'    => absint( $tr['image_id'] ?? 0 ),
			'steps'       => $tr_steps,
		);

		// Food Safety
		$fs = $input['food_safety'] ?? array();
		$fs_practices = array();
		if ( ! empty( $fs['practices'] ) && is_array( $fs['practices'] ) ) {
			foreach ( $fs['practices'] as $pr ) {
				$pt = sanitize_text_field( $pr['title'] ?? '' );
				$pd = sanitize_text_field( $pr['description'] ?? '' );
				if ( empty( $pt ) && empty( $pd ) ) continue;
				$fs_practices[] = array( 'title' => $pt, 'description' => $pd );
			}
		}
		$output['food_safety'] = array(
			'eyebrow'     => sanitize_text_field( $fs['eyebrow'] ?? '' ),
			'heading'     => sanitize_text_field( $fs['heading'] ?? '' ),
			'description' => sanitize_textarea_field( $fs['description'] ?? '' ),
			'image_id'    => absint( $fs['image_id'] ?? 0 ),
			'practices'   => $fs_practices,
		);

		// Certifications
		$cert = $input['certifications'] ?? array();
		$selected_certs = ! empty( $cert['selected_ids'] ) && is_array( $cert['selected_ids'] )
			? array_values( array_filter( array_map( 'absint', $cert['selected_ids'] ) ) )
			: array();
		$output['certifications'] = array(
			'eyebrow'      => sanitize_text_field( $cert['eyebrow'] ?? '' ),
			'heading'      => sanitize_text_field( $cert['heading'] ?? '' ),
			'description'  => sanitize_textarea_field( $cert['description'] ?? '' ),
			'selected_ids' => $selected_certs,
			'limit'        => absint( $cert['limit'] ?? 6 ),
			'cta_label'    => sanitize_text_field( $cert['cta_label'] ?? '' ),
			'cta_url'      => sanitize_text_field( $cert['cta_url'] ?? '' ),
		);

		// Statistics
		$st = $input['statistics'] ?? array();
		$output['statistics'] = array(
			'eyebrow'     => sanitize_text_field( $st['eyebrow'] ?? '' ),
			'heading'     => sanitize_text_field( $st['heading'] ?? '' ),
			'description' => sanitize_textarea_field( $st['description'] ?? '' ),
			'items'       => spicecraft_sanitize_stat_items( $st['items'] ?? array() ),
		);

		// Gallery
		$gal = $input['gallery'] ?? array();
		$output['gallery'] = array(
			'eyebrow'        => sanitize_text_field( $gal['eyebrow'] ?? '' ),
			'heading'        => sanitize_text_field( $gal['heading'] ?? '' ),
			'description'    => sanitize_textarea_field( $gal['description'] ?? '' ),
			'attachment_ids' => spicecraft_sanitize_gallery_ids( $gal['attachment_ids'] ?? array() ),
		);

		// Products
		$prod = $input['products'] ?? array();
		$prod_source = in_array( $prod['source'] ?? '', array( 'categories', 'products' ), true ) ? $prod['source'] : 'categories';
		$selected_prods = ! empty( $prod['selected_ids'] ) && is_array( $prod['selected_ids'] )
			? array_values( array_filter( array_map( 'absint', $prod['selected_ids'] ) ) )
			: array();
		$output['products'] = array(
			'eyebrow'      => sanitize_text_field( $prod['eyebrow'] ?? '' ),
			'heading'      => sanitize_text_field( $prod['heading'] ?? '' ),
			'description'  => sanitize_textarea_field( $prod['description'] ?? '' ),
			'source'       => $prod_source,
			'selected_ids' => $selected_prods,
			'limit'        => absint( $prod['limit'] ?? 4 ),
			'cta_label'    => sanitize_text_field( $prod['cta_label'] ?? '' ),
			'cta_url'      => sanitize_text_field( $prod['cta_url'] ?? '' ),
		);

		// B2B CTA
		$b2b = $input['b2b_cta'] ?? array();
		$output['b2b_cta'] = array(
			'eyebrow'             => sanitize_text_field( $b2b['eyebrow'] ?? '' ),
			'heading'             => sanitize_text_field( $b2b['heading'] ?? '' ),
			'description'         => sanitize_textarea_field( $b2b['description'] ?? '' ),
			'bg_image_id'         => absint( $b2b['bg_image_id'] ?? 0 ),
			'primary_cta_label'   => sanitize_text_field( $b2b['primary_cta_label'] ?? '' ),
			'primary_cta_url'     => sanitize_text_field( $b2b['primary_cta_url'] ?? '' ),
			'secondary_cta_label' => sanitize_text_field( $b2b['secondary_cta_label'] ?? '' ),
			'secondary_cta_url'   => sanitize_text_field( $b2b['secondary_cta_url'] ?? '' ),
			'show_whatsapp'       => ! empty( $b2b['show_whatsapp'] ) ? 1 : 0,
		);

		// Final CTA
		$fin = $input['final_cta'] ?? array();
		$output['final_cta'] = array(
			'heading'       => sanitize_text_field( $fin['heading'] ?? '' ),
			'description'   => sanitize_textarea_field( $fin['description'] ?? '' ),
			'cta_label'     => sanitize_text_field( $fin['cta_label'] ?? '' ),
			'cta_url'       => sanitize_text_field( $fin['cta_url'] ?? '' ),
			'show_whatsapp' => ! empty( $fin['show_whatsapp'] ) ? 1 : 0,
			'show_email'    => ! empty( $fin['show_email'] ) ? 1 : 0,
		);

		return $output;
	}
}
