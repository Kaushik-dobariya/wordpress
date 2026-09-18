<?php
/**
 * SpiceCraft Core - Manufacturing CMS Settings Page
 *
 * Provides a structured, WordPress-native administrative settings interface
 * for the Manufacturing Facility & Capabilities page under SpiceCraft -> Manufacturing.
 *
 * @package SpiceCraft_Core
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class SpiceCraft_Manufacturing_Settings {

	const OPTION_NAME = 'spicecraft_manufacturing_settings';

	/**
	 * Singleton Instance.
	 *
	 * @var SpiceCraft_Manufacturing_Settings|null
	 */
	private static $instance = null;

	/**
	 * Get Singleton Instance.
	 *
	 * @return SpiceCraft_Manufacturing_Settings
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
		add_action( 'admin_menu', array( $this, 'register_admin_menu' ), 23 );
		add_action( 'admin_init', array( $this, 'register_settings' ) );
	}

	/**
	 * Register Admin Submenu.
	 */
	public function register_admin_menu() {
		add_submenu_page(
			'spicecraft-overview',
			__( 'Manufacturing CMS Management', 'spicecraft-core' ),
			__( 'Manufacturing', 'spicecraft-core' ),
			'manage_options',
			'spicecraft-manufacturing',
			array( $this, 'render_page' )
		);
	}

	/**
	 * Register Settings with Validation & Sanitization.
	 */
	public function register_settings() {
		register_setting(
			'spicecraft_manufacturing_group',
			self::OPTION_NAME,
			array(
				'sanitize_callback' => array( $this, 'sanitize_settings' ),
				'default'           => spicecraft_get_manufacturing_default_settings(),
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

		$settings   = spicecraft_get_manufacturing_settings();
		$active_tab = isset( $_GET['tab'] ) ? sanitize_key( $_GET['tab'] ) : 'order';

		$tabs = array(
			'order'     => __( '1. Order & Visibility', 'spicecraft-core' ),
			'hero'      => __( '2. Hero & Introduction', 'spicecraft-core' ),
			'facility'  => __( '3. Facility & Process', 'spicecraft-core' ),
			'equipment' => __( '4. Capabilities & Equipment', 'spicecraft-core' ),
			'hygiene'   => __( '5. Hygiene & Packaging', 'spicecraft-core' ),
			'gallery'   => __( '6. Stats & Gallery', 'spicecraft-core' ),
			'trust'     => __( '7. Trust & Products', 'spicecraft-core' ),
			'cta'       => __( '8. Business CTAs', 'spicecraft-core' ),
		);

		$mfg_page = get_page_by_path( 'manufacturing' );
		$mfg_url  = $mfg_page ? get_permalink( $mfg_page->ID ) : home_url( '/manufacturing/' );
		?>
		<div class="wrap spicecraft-settings-wrap">
			<div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px; margin-bottom: 12px;">
				<h1 style="margin: 0;"><?php esc_html_e( 'SpiceCraft Manufacturing CMS Management', 'spicecraft-core' ); ?></h1>
				<a href="<?php echo esc_url( $mfg_url ); ?>" target="_blank" rel="noopener noreferrer" class="button button-secondary" style="display: inline-flex; align-items: center; gap: 4px;">
					<span class="dashicons dashicons-external" style="margin-top: -2px;"></span>
					<?php esc_html_e( 'View Manufacturing Page', 'spicecraft-core' ); ?>
				</a>
			</div>

			<p class="description" style="margin-bottom: 16px;">
				<?php esc_html_e( 'Manage technical processing standards, facility overview, equipment specs, packaging capabilities, and industrial gallery without editing code.', 'spicecraft-core' ); ?>
			</p>

			<?php settings_errors(); ?>

			<!-- Navigation Tabs -->
			<h2 class="nav-tab-wrapper" style="margin-bottom: 20px;">
				<?php foreach ( $tabs as $tab_key => $tab_title ) : ?>
					<a href="?page=spicecraft-manufacturing&tab=<?php echo esc_attr( $tab_key ); ?>" class="nav-tab <?php echo $tab_key === $active_tab ? 'nav-tab-active' : ''; ?>">
						<?php echo esc_html( $tab_title ); ?>
					</a>
				<?php endforeach; ?>
			</h2>

			<form method="post" action="options.php" class="spicecraft-mfg-form">
				<?php
				settings_fields( 'spicecraft_manufacturing_group' );
				?>
				<input type="hidden" name="_wp_http_referer" value="<?php echo esc_attr( wp_unslash( $_SERVER['REQUEST_URI'] ?? '' ) ); ?>" />
				<input type="hidden" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[_active_tab]" value="<?php echo esc_attr( $active_tab ); ?>" />

				<?php
				// TAB 1: ORDER & VISIBILITY
				if ( 'order' === $active_tab ) :
					$section_defs = array(
						'hero'           => array( 'name' => __( 'Hero Banner', 'spicecraft-core' ), 'id' => '#mfg-hero', 'desc' => __( 'Opening technical headline, facility imagery, and primary conversion links.', 'spicecraft-core' ) ),
						'introduction'   => array( 'name' => __( 'Manufacturing Introduction', 'spicecraft-core' ), 'id' => '#mfg-intro', 'desc' => __( 'High-level philosophy and scope of industrial operations.', 'spicecraft-core' ) ),
						'facility'       => array( 'name' => __( 'Facility Overview', 'spicecraft-core' ), 'id' => '#mfg-facility', 'desc' => __( 'Infrastructure scale, cleanroom layout, and operational areas.', 'spicecraft-core' ) ),
						'process'        => array( 'name' => __( 'Manufacturing Process Flow', 'spicecraft-core' ), 'id' => '#mfg-process', 'desc' => __( 'Step-by-step production stages (cleaning, grading, pulverization, packaging).', 'spicecraft-core' ) ),
						'capabilities'   => array( 'name' => __( 'Plant Capabilities', 'spicecraft-core' ), 'id' => '#mfg-capabilities', 'desc' => __( 'Repeatable operational competencies and custom milling services.', 'spicecraft-core' ) ),
						'equipment'      => array( 'name' => __( 'Technology & Equipment', 'spicecraft-core' ), 'id' => '#mfg-equipment', 'desc' => __( 'Machinery profiles with technical spec rows (material, capacity, speed).', 'spicecraft-core' ) ),
						'hygiene'        => array( 'name' => __( 'Hygiene & Food Safety', 'spicecraft-core' ), 'id' => '#mfg-hygiene', 'desc' => __( 'Contamination controls, air filtration, and hygiene protocols.', 'spicecraft-core' ) ),
						'packaging'      => array( 'name' => __( 'Packaging Solutions', 'spicecraft-core' ), 'id' => '#mfg-packaging', 'desc' => __( 'Pouch packing, nitrogen flushing, bulk containerization capabilities.', 'spicecraft-core' ) ),
						'warehousing'    => array( 'name' => __( 'Warehousing & Handling', 'spicecraft-core' ), 'id' => '#mfg-warehousing', 'desc' => __( 'Climate control, pallets, storage protocols, and inventory care.', 'spicecraft-core' ) ),
						'statistics'     => array( 'name' => __( 'Manufacturing Statistics', 'spicecraft-core' ), 'id' => '#mfg-statistics', 'desc' => __( 'Verified plant throughput metrics (hidden cleanly if unconfigured).', 'spicecraft-core' ) ),
						'gallery'        => array( 'name' => __( 'Facility Media Gallery', 'spicecraft-core' ), 'id' => '#mfg-gallery', 'desc' => __( 'High-resolution photography of factory infrastructure and production lines.', 'spicecraft-core' ) ),
						'certifications' => array( 'name' => __( 'Certifications & Standards', 'spicecraft-core' ), 'id' => '#mfg-certifications', 'desc' => __( 'Statutory compliance accreditations and manufacturing audits.', 'spicecraft-core' ) ),
						'products'       => array( 'name' => __( 'Related Products Connection', 'spicecraft-core' ), 'id' => '#mfg-products', 'desc' => __( 'Catalog bridge connecting factory output to manufactured goods.', 'spicecraft-core' ) ),
						'b2b_cta'        => array( 'name' => __( 'B2B Manufacturing Enquiry', 'spicecraft-core' ), 'id' => '#mfg-b2b-cta', 'desc' => __( 'Wholesale bulk procurement, custom blending, and private label desk.', 'spicecraft-core' ) ),
						'final_cta'      => array( 'name' => __( 'Final Contact Action', 'spicecraft-core' ), 'id' => '#mfg-final-cta', 'desc' => __( 'Direct communication channels routing to Global Settings.', 'spicecraft-core' ) ),
					);
					spicecraft_render_admin_section_order_table( self::OPTION_NAME, $settings['sections_order'], $settings['sections_enabled'], $section_defs );

				// TAB 2: HERO & INTRODUCTION
				elseif ( 'hero' === $active_tab ) :
					$hero  = $settings['hero'];
					$intro = $settings['introduction'];
					?>
					<!-- Section 1: Hero -->
					<div class="postbox" style="margin-bottom: 20px;">
						<div class="postbox-header"><h2 class="hndle"><?php esc_html_e( '1. Hero Banner', 'spicecraft-core' ); ?></h2></div>
						<div class="inside">
							<table class="form-table" role="presentation">
								<tr>
									<th scope="row"><label for="mfg_hero_eyebrow"><?php esc_html_e( 'Eyebrow', 'spicecraft-core' ); ?></label></th>
									<td>
										<input type="text" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[hero][eyebrow]" id="mfg_hero_eyebrow" value="<?php echo esc_attr( $hero['eyebrow'] ); ?>" class="regular-text" placeholder="e.g. Processing Infrastructure" />
									</td>
								</tr>
								<tr>
									<th scope="row"><label for="mfg_hero_h1"><?php esc_html_e( 'Main Heading (H1)', 'spicecraft-core' ); ?></label></th>
									<td>
										<input type="text" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[hero][heading]" id="mfg_hero_h1" value="<?php echo esc_attr( $hero['heading'] ); ?>" class="large-text" placeholder="e.g. Precision Spice Milling & Processing Standards" />
										<p class="description"><?php esc_html_e( 'This renders as the single H1 for this page. Leaving this empty suppresses the hero section cleanly.', 'spicecraft-core' ); ?></p>
									</td>
								</tr>
								<tr>
									<th scope="row"><label for="mfg_hero_highlight"><?php esc_html_e( 'Highlighted Phrase', 'spicecraft-core' ); ?></label></th>
									<td>
										<input type="text" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[hero][heading_highlight]" id="mfg_hero_highlight" value="<?php echo esc_attr( $hero['heading_highlight'] ); ?>" class="regular-text" placeholder="e.g. Processing Standards" />
									</td>
								</tr>
								<tr>
									<th scope="row"><label for="mfg_hero_intro"><?php esc_html_e( 'Short Introduction', 'spicecraft-core' ); ?></label></th>
									<td>
										<textarea name="<?php echo esc_attr( self::OPTION_NAME ); ?>[hero][intro]" id="mfg_hero_intro" rows="3" class="large-text"><?php echo esc_textarea( $hero['intro'] ); ?></textarea>
									</td>
								</tr>
								<tr>
									<th scope="row"><?php esc_html_e( 'Desktop Hero Image', 'spicecraft-core' ); ?></th>
									<td>
										<?php spicecraft_render_admin_media_uploader( self::OPTION_NAME . '[hero][desktop_image_id]', $hero['desktop_image_id'], __( 'Desktop Image (1920x800 recommended)', 'spicecraft-core' ) ); ?>
									</td>
								</tr>
								<tr>
									<th scope="row"><?php esc_html_e( 'Mobile Hero Image (Optional)', 'spicecraft-core' ); ?></th>
									<td>
										<?php spicecraft_render_admin_media_uploader( self::OPTION_NAME . '[hero][mobile_image_id]', $hero['mobile_image_id'], __( 'Mobile crop (800x600 recommended)', 'spicecraft-core' ) ); ?>
									</td>
								</tr>
								<tr>
									<th scope="row"><label for="mfg_hero_alt"><?php esc_html_e( 'Image Alt Text', 'spicecraft-core' ); ?></label></th>
									<td>
										<input type="text" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[hero][image_alt]" id="mfg_hero_alt" value="<?php echo esc_attr( $hero['image_alt'] ); ?>" class="regular-text" />
									</td>
								</tr>
								<tr>
									<th scope="row"><?php esc_html_e( 'Primary CTA', 'spicecraft-core' ); ?></th>
									<td>
										<input type="text" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[hero][cta_primary_label]" value="<?php echo esc_attr( $hero['cta_primary_label'] ); ?>" placeholder="Label (e.g. Explore Capabilities)" class="regular-text" style="width: 200px;" />
										<input type="text" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[hero][cta_primary_url]" value="<?php echo esc_attr( $hero['cta_primary_url'] ); ?>" placeholder="URL (e.g. #mfg-capabilities)" class="regular-text" style="width: 250px;" />
									</td>
								</tr>
								<tr>
									<th scope="row"><?php esc_html_e( 'Secondary CTA', 'spicecraft-core' ); ?></th>
									<td>
										<input type="text" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[hero][cta_secondary_label]" value="<?php echo esc_attr( $hero['cta_secondary_label'] ); ?>" placeholder="Label (e.g. Facility Tour)" class="regular-text" style="width: 200px;" />
										<input type="text" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[hero][cta_secondary_url]" value="<?php echo esc_attr( $hero['cta_secondary_url'] ); ?>" placeholder="URL (e.g. #mfg-facility)" class="regular-text" style="width: 250px;" />
									</td>
								</tr>
							</table>
						</div>
					</div>

					<!-- Section 2: Introduction -->
					<div class="postbox">
						<div class="postbox-header"><h2 class="hndle"><?php esc_html_e( '2. Manufacturing Introduction', 'spicecraft-core' ); ?></h2></div>
						<div class="inside">
							<table class="form-table" role="presentation">
								<tr>
									<th scope="row"><label for="mfg_intro_eyebrow"><?php esc_html_e( 'Eyebrow', 'spicecraft-core' ); ?></label></th>
									<td>
										<input type="text" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[introduction][eyebrow]" id="mfg_intro_eyebrow" value="<?php echo esc_attr( $intro['eyebrow'] ); ?>" class="regular-text" placeholder="e.g. Industrial Scale & Integrity" />
									</td>
								</tr>
								<tr>
									<th scope="row"><label for="mfg_intro_heading"><?php esc_html_e( 'Heading', 'spicecraft-core' ); ?></label></th>
									<td>
										<input type="text" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[introduction][heading]" id="mfg_intro_heading" value="<?php echo esc_attr( $intro['heading'] ); ?>" class="large-text" placeholder="e.g. Engineered for Consistency, Purity, and Batch Traceability" />
									</td>
								</tr>
								<tr>
									<th scope="row"><label for="mfg_intro_content"><?php esc_html_e( 'Description Narrative', 'spicecraft-core' ); ?></label></th>
									<td>
										<?php
										wp_editor(
											$intro['content'],
											'mfg_intro_content_editor',
											array(
												'textarea_name' => self::OPTION_NAME . '[introduction][content]',
												'textarea_rows' => 6,
												'media_buttons' => false,
											)
										);
										?>
									</td>
								</tr>
								<tr>
									<th scope="row"><?php esc_html_e( 'Primary Photo', 'spicecraft-core' ); ?></th>
									<td>
										<?php spicecraft_render_admin_media_uploader( self::OPTION_NAME . '[introduction][image_primary_id]', $intro['image_primary_id'] ); ?>
									</td>
								</tr>
								<tr>
									<th scope="row"><?php esc_html_e( 'Secondary Photo (Optional)', 'spicecraft-core' ); ?></th>
									<td>
										<?php spicecraft_render_admin_media_uploader( self::OPTION_NAME . '[introduction][image_secondary_id]', $intro['image_secondary_id'] ); ?>
									</td>
								</tr>
								<tr>
									<th scope="row"><?php esc_html_e( 'Action Link (Optional)', 'spicecraft-core' ); ?></th>
									<td>
										<input type="text" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[introduction][cta_label]" value="<?php echo esc_attr( $intro['cta_label'] ); ?>" placeholder="Label" class="regular-text" style="width: 200px;" />
										<input type="text" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[introduction][cta_url]" value="<?php echo esc_attr( $intro['cta_url'] ); ?>" placeholder="URL" class="regular-text" style="width: 250px;" />
									</td>
								</tr>
							</table>
						</div>
					</div>

				<?php
				// TAB 3: FACILITY & PROCESS
				elseif ( 'facility' === $active_tab ) :
					$facility = $settings['facility'];
					$process  = $settings['process'];
					?>
					<!-- Section 3: Facility Overview -->
					<div class="postbox" style="margin-bottom: 20px;">
						<div class="postbox-header"><h2 class="hndle"><?php esc_html_e( '3. Facility Overview', 'spicecraft-core' ); ?></h2></div>
						<div class="inside">
							<table class="form-table" role="presentation">
								<tr>
									<th scope="row"><label for="mfg_fac_eyebrow"><?php esc_html_e( 'Eyebrow', 'spicecraft-core' ); ?></label></th>
									<td>
										<input type="text" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[facility][eyebrow]" id="mfg_fac_eyebrow" value="<?php echo esc_attr( $facility['eyebrow'] ); ?>" class="regular-text" placeholder="e.g. Infrastructure Overview" />
									</td>
								</tr>
								<tr>
									<th scope="row"><label for="mfg_fac_heading"><?php esc_html_e( 'Heading', 'spicecraft-core' ); ?></label></th>
									<td>
										<input type="text" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[facility][heading]" id="mfg_fac_heading" value="<?php echo esc_attr( $facility['heading'] ); ?>" class="large-text" placeholder="e.g. Purpose-Built Spice Processing Facility" />
									</td>
								</tr>
								<tr>
									<th scope="row"><label for="mfg_fac_desc"><?php esc_html_e( 'Description', 'spicecraft-core' ); ?></label></th>
									<td>
										<textarea name="<?php echo esc_attr( self::OPTION_NAME ); ?>[facility][description]" id="mfg_fac_desc" rows="4" class="large-text"><?php echo esc_textarea( $facility['description'] ); ?></textarea>
									</td>
								</tr>
								<tr>
									<th scope="row"><?php esc_html_e( 'Facility Main Image', 'spicecraft-core' ); ?></th>
									<td>
										<?php spicecraft_render_admin_media_uploader( self::OPTION_NAME . '[facility][image_id]', $facility['image_id'] ); ?>
									</td>
								</tr>
								<tr>
									<th scope="row"><?php esc_html_e( 'Supporting Area Image', 'spicecraft-core' ); ?></th>
									<td>
										<?php spicecraft_render_admin_media_uploader( self::OPTION_NAME . '[facility][image_secondary_id]', $facility['image_secondary_id'] ); ?>
									</td>
								</tr>
							</table>
						</div>
					</div>

					<!-- Section 4: Manufacturing Process -->
					<div class="postbox">
						<div class="postbox-header"><h2 class="hndle"><?php esc_html_e( '4. Manufacturing Process Flow', 'spicecraft-core' ); ?></h2></div>
						<div class="inside">
							<table class="form-table" role="presentation">
								<tr>
									<th scope="row"><label for="mfg_pr_eyebrow"><?php esc_html_e( 'Eyebrow', 'spicecraft-core' ); ?></label></th>
									<td>
										<input type="text" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[process][eyebrow]" id="mfg_pr_eyebrow" value="<?php echo esc_attr( $process['eyebrow'] ); ?>" class="regular-text" placeholder="e.g. Production Sequence" />
									</td>
								</tr>
								<tr>
									<th scope="row"><label for="mfg_pr_heading"><?php esc_html_e( 'Heading', 'spicecraft-core' ); ?></label></th>
									<td>
										<input type="text" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[process][heading]" id="mfg_pr_heading" value="<?php echo esc_attr( $process['heading'] ); ?>" class="large-text" placeholder="e.g. From Raw Spice Receiving to Sealed Pack" />
									</td>
								</tr>
								<tr>
									<th scope="row"><label for="mfg_pr_desc"><?php esc_html_e( 'Description', 'spicecraft-core' ); ?></label></th>
									<td>
										<textarea name="<?php echo esc_attr( self::OPTION_NAME ); ?>[process][description]" id="mfg_pr_desc" rows="3" class="large-text"><?php echo esc_textarea( $process['description'] ); ?></textarea>
									</td>
								</tr>
								<tr>
									<th scope="row"><?php esc_html_e( 'Process Stages', 'spicecraft-core' ); ?></th>
									<td>
										<?php spicecraft_render_admin_process_rows( self::OPTION_NAME, $process['items'] ?? array(), 'sc-mfg-process-rows', 'sc-add-mfg-process-btn' ); ?>
									</td>
								</tr>
							</table>
						</div>
					</div>

				<?php
				// TAB 4: CAPABILITIES & EQUIPMENT
				elseif ( 'equipment' === $active_tab ) :
					$equip = $settings['equipment'];
					$caps  = $settings['capabilities'];
					?>
					<!-- Section 5: Capabilities -->
					<div class="postbox" style="margin-bottom: 20px;">
						<div class="postbox-header"><h2 class="hndle"><?php esc_html_e( '5. Plant Capabilities', 'spicecraft-core' ); ?></h2></div>
						<div class="inside">
							<table class="form-table" role="presentation">
								<tr>
									<th scope="row"><label for="mfg_cap_heading"><?php esc_html_e( 'Heading', 'spicecraft-core' ); ?></label></th>
									<td>
										<input type="text" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[capabilities][heading]" id="mfg_cap_heading" value="<?php echo esc_attr( $caps['heading'] ); ?>" class="large-text" placeholder="e.g. Custom Processing & Contract Milling Capabilities" />
									</td>
								</tr>
								<tr>
									<th scope="row"><label for="mfg_cap_desc"><?php esc_html_e( 'Description', 'spicecraft-core' ); ?></label></th>
									<td>
										<textarea name="<?php echo esc_attr( self::OPTION_NAME ); ?>[capabilities][description]" id="mfg_cap_desc" rows="3" class="large-text"><?php echo esc_textarea( $caps['description'] ); ?></textarea>
									</td>
								</tr>
							</table>
						</div>
					</div>

					<!-- Section 6: Technology & Equipment -->
					<div class="postbox">
						<div class="postbox-header"><h2 class="hndle"><?php esc_html_e( '6. Technology & Equipment Specifications', 'spicecraft-core' ); ?></h2></div>
						<div class="inside">
							<table class="form-table" role="presentation">
								<tr>
									<th scope="row"><label for="mfg_eq_heading"><?php esc_html_e( 'Heading', 'spicecraft-core' ); ?></label></th>
									<td>
										<input type="text" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[equipment][heading]" id="mfg_eq_heading" value="<?php echo esc_attr( $equip['heading'] ); ?>" class="large-text" placeholder="e.g. Specialized Industrial Milling Machinery" />
									</td>
								</tr>
								<tr>
									<th scope="row"><label for="mfg_eq_desc"><?php esc_html_e( 'Description', 'spicecraft-core' ); ?></label></th>
									<td>
										<textarea name="<?php echo esc_attr( self::OPTION_NAME ); ?>[equipment][description]" id="mfg_eq_desc" rows="3" class="large-text"><?php echo esc_textarea( $equip['description'] ); ?></textarea>
									</td>
								</tr>
								<tr>
									<th scope="row"><?php esc_html_e( 'Equipment Profiles', 'spicecraft-core' ); ?></th>
									<td>
										<?php spicecraft_render_admin_equipment_rows( self::OPTION_NAME, $equip['items'] ?? array() ); ?>
									</td>
								</tr>
							</table>
						</div>
					</div>

				<?php
				// TAB 5: HYGIENE & PACKAGING
				elseif ( 'hygiene' === $active_tab ) :
					$hygiene = $settings['hygiene'];
					$pkg     = $settings['packaging'];
					$wh      = $settings['warehousing'];
					?>
					<!-- Section 7: Hygiene -->
					<div class="postbox" style="margin-bottom: 20px;">
						<div class="postbox-header"><h2 class="hndle"><?php esc_html_e( '7. Hygiene & Food Safety Protocols', 'spicecraft-core' ); ?></h2></div>
						<div class="inside">
							<table class="form-table" role="presentation">
								<tr>
									<th scope="row"><label for="mfg_hyg_heading"><?php esc_html_e( 'Heading', 'spicecraft-core' ); ?></label></th>
									<td>
										<input type="text" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[hygiene][heading]" id="mfg_hyg_heading" value="<?php echo esc_attr( $hygiene['heading'] ); ?>" class="large-text" placeholder="e.g. Stringent Contamination Prevention Standards" />
									</td>
								</tr>
								<tr>
									<th scope="row"><label for="mfg_hyg_desc"><?php esc_html_e( 'Description', 'spicecraft-core' ); ?></label></th>
									<td>
										<textarea name="<?php echo esc_attr( self::OPTION_NAME ); ?>[hygiene][description]" id="mfg_hyg_desc" rows="3" class="large-text"><?php echo esc_textarea( $hygiene['description'] ); ?></textarea>
									</td>
								</tr>
								<tr>
									<th scope="row"><?php esc_html_e( 'Hygiene Image', 'spicecraft-core' ); ?></th>
									<td>
										<?php spicecraft_render_admin_media_uploader( self::OPTION_NAME . '[hygiene][image_id]', $hygiene['image_id'] ); ?>
									</td>
								</tr>
							</table>
						</div>
					</div>

					<!-- Section 8: Packaging -->
					<div class="postbox" style="margin-bottom: 20px;">
						<div class="postbox-header"><h2 class="hndle"><?php esc_html_e( '8. Packaging Capabilities', 'spicecraft-core' ); ?></h2></div>
						<div class="inside">
							<table class="form-table" role="presentation">
								<tr>
									<th scope="row"><label for="mfg_pkg_heading"><?php esc_html_e( 'Heading', 'spicecraft-core' ); ?></label></th>
									<td>
										<input type="text" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[packaging][heading]" id="mfg_pkg_heading" value="<?php echo esc_attr( $pkg['heading'] ); ?>" class="large-text" placeholder="e.g. Retail and Bulk Barrier Packaging Solutions" />
									</td>
								</tr>
								<tr>
									<th scope="row"><label for="mfg_pkg_desc"><?php esc_html_e( 'Description', 'spicecraft-core' ); ?></label></th>
									<td>
										<textarea name="<?php echo esc_attr( self::OPTION_NAME ); ?>[packaging][description]" id="mfg_pkg_desc" rows="3" class="large-text"><?php echo esc_textarea( $pkg['description'] ); ?></textarea>
									</td>
								</tr>
								<tr>
									<th scope="row"><?php esc_html_e( 'Packaging Photo', 'spicecraft-core' ); ?></th>
									<td>
										<?php spicecraft_render_admin_media_uploader( self::OPTION_NAME . '[packaging][image_id]', $pkg['image_id'] ); ?>
									</td>
								</tr>
							</table>
						</div>
					</div>

					<!-- Section 9: Warehousing -->
					<div class="postbox">
						<div class="postbox-header"><h2 class="hndle"><?php esc_html_e( '9. Warehousing & Handling (Optional)', 'spicecraft-core' ); ?></h2></div>
						<div class="inside">
							<table class="form-table" role="presentation">
								<tr>
									<th scope="row"><label for="mfg_wh_heading"><?php esc_html_e( 'Heading', 'spicecraft-core' ); ?></label></th>
									<td>
										<input type="text" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[warehousing][heading]" id="mfg_wh_heading" value="<?php echo esc_attr( $wh['heading'] ); ?>" class="large-text" placeholder="e.g. Humidity-Controlled Raw Material & Finished Goods Storage" />
									</td>
								</tr>
								<tr>
									<th scope="row"><label for="mfg_wh_desc"><?php esc_html_e( 'Description', 'spicecraft-core' ); ?></label></th>
									<td>
										<textarea name="<?php echo esc_attr( self::OPTION_NAME ); ?>[warehousing][description]" id="mfg_wh_desc" rows="3" class="large-text"><?php echo esc_textarea( $wh['description'] ); ?></textarea>
									</td>
								</tr>
							</table>
						</div>
					</div>

				<?php
				// TAB 6: STATS & GALLERY
				elseif ( 'gallery' === $active_tab ) :
					$stats = $settings['statistics'];
					$gal   = $settings['gallery'];
					?>
					<!-- Section 10: Manufacturing Statistics -->
					<div class="postbox" style="margin-bottom: 20px;">
						<div class="postbox-header"><h2 class="hndle"><?php esc_html_e( '10. Manufacturing Statistics (Optional)', 'spicecraft-core' ); ?></h2></div>
						<div class="inside">
							<div class="notice notice-warning inline" style="margin-bottom: 12px;">
								<p><strong><?php esc_html_e( 'Zero Fabricated Claims:', 'spicecraft-core' ); ?></strong> <?php esc_html_e( 'Only enter genuine, verifiable numbers. If empty, this section is cleanly suppressed.', 'spicecraft-core' ); ?></p>
							</div>
							<table class="form-table" role="presentation">
								<tr>
									<th scope="row"><label for="mfg_stat_heading"><?php esc_html_e( 'Heading', 'spicecraft-core' ); ?></label></th>
									<td>
										<input type="text" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[statistics][heading]" id="mfg_stat_heading" value="<?php echo esc_attr( $stats['heading'] ); ?>" class="large-text" placeholder="e.g. Production Metrics" />
									</td>
								</tr>
								<tr>
									<th scope="row"><?php esc_html_e( 'Repeatable Metrics', 'spicecraft-core' ); ?></th>
									<td>
										<?php spicecraft_render_admin_stat_rows( self::OPTION_NAME, $stats['items'] ?? array(), 'sc-mfg-stat-rows', 'sc-add-mfg-stat-btn' ); ?>
									</td>
								</tr>
							</table>
						</div>
					</div>

					<!-- Section 11: Facility Gallery -->
					<div class="postbox">
						<div class="postbox-header"><h2 class="hndle"><?php esc_html_e( '11. Facility Photo Gallery', 'spicecraft-core' ); ?></h2></div>
						<div class="inside">
							<table class="form-table" role="presentation">
								<tr>
									<th scope="row"><label for="mfg_gal_heading"><?php esc_html_e( 'Heading', 'spicecraft-core' ); ?></label></th>
									<td>
										<input type="text" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[gallery][heading]" id="mfg_gal_heading" value="<?php echo esc_attr( $gal['heading'] ); ?>" class="large-text" placeholder="e.g. Inside Our Processing Facility" />
									</td>
								</tr>
								<tr>
									<th scope="row"><?php esc_html_e( 'Gallery Photography', 'spicecraft-core' ); ?></th>
									<td>
										<?php spicecraft_render_admin_gallery_uploader( self::OPTION_NAME . '[gallery][attachment_ids]', $gal['attachment_ids'] ?? array() ); ?>
									</td>
								</tr>
							</table>
						</div>
					</div>

				<?php
				// TAB 7: TRUST & PRODUCTS
				elseif ( 'trust' === $active_tab ) :
					$certs = $settings['certifications'];
					$prod  = $settings['products'];
					?>
					<!-- Section 12: Certifications -->
					<div class="postbox" style="margin-bottom: 20px;">
						<div class="postbox-header"><h2 class="hndle"><?php esc_html_e( '12. Certifications & Standards', 'spicecraft-core' ); ?></h2></div>
						<div class="inside">
							<table class="form-table" role="presentation">
								<tr>
									<th scope="row"><label for="mfg_cert_heading"><?php esc_html_e( 'Heading', 'spicecraft-core' ); ?></label></th>
									<td>
										<input type="text" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[certifications][heading]" id="mfg_cert_heading" value="<?php echo esc_attr( $certs['heading'] ); ?>" class="large-text" placeholder="e.g. Manufacturing Standards & Statutory Compliance" />
									</td>
								</tr>
								<tr>
									<th scope="row"><?php esc_html_e( 'Select Certifications', 'spicecraft-core' ); ?></th>
									<td>
										<?php spicecraft_render_admin_taxonomy_multiselect( 'spicecraft_certification', self::OPTION_NAME . '[certifications][selected_ids]', $certs['selected_ids'] ); ?>
										<p class="description"><?php esc_html_e( 'Leave all unchecked to display all configured certifications.', 'spicecraft-core' ); ?></p>
									</td>
								</tr>
							</table>
						</div>
					</div>

					<!-- Section 13: Related Products -->
					<div class="postbox">
						<div class="postbox-header"><h2 class="hndle"><?php esc_html_e( '13. Manufactured Goods Connection', 'spicecraft-core' ); ?></h2></div>
						<div class="inside">
							<table class="form-table" role="presentation">
								<tr>
									<th scope="row"><label for="mfg_prod_heading"><?php esc_html_e( 'Heading', 'spicecraft-core' ); ?></label></th>
									<td>
										<input type="text" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[products][heading]" id="mfg_prod_heading" value="<?php echo esc_attr( $prod['heading'] ); ?>" class="large-text" placeholder="e.g. Products Originating from Our Facilities" />
									</td>
								</tr>
								<tr>
									<th scope="row"><?php esc_html_e( 'Categories Connection', 'spicecraft-core' ); ?></th>
									<td>
										<?php spicecraft_render_admin_taxonomy_multiselect( 'product_cat', self::OPTION_NAME . '[products][selected_ids]', $prod['selected_ids'] ); ?>
									</td>
								</tr>
							</table>
						</div>
					</div>

				<?php
				// TAB 8: BUSINESS CTAS
				elseif ( 'cta' === $active_tab ) :
					$b2b   = $settings['b2b_cta'];
					$final = $settings['final_cta'];
					?>
					<!-- Section 14: B2B CTA -->
					<div class="postbox" style="margin-bottom: 20px;">
						<div class="postbox-header"><h2 class="hndle"><?php esc_html_e( '14. B2B / Manufacturing Inquiry Band', 'spicecraft-core' ); ?></h2></div>
						<div class="inside">
							<table class="form-table" role="presentation">
								<tr>
									<th scope="row"><label for="mfg_b2b_heading"><?php esc_html_e( 'Heading', 'spicecraft-core' ); ?></label></th>
									<td>
										<input type="text" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[b2b_cta][heading]" id="mfg_b2b_heading" value="<?php echo esc_attr( $b2b['heading'] ); ?>" class="large-text" placeholder="e.g. Inquire on Custom Milling & Bulk Container Supply" />
									</td>
								</tr>
								<tr>
									<th scope="row"><label for="mfg_b2b_desc"><?php esc_html_e( 'Description', 'spicecraft-core' ); ?></label></th>
									<td>
										<textarea name="<?php echo esc_attr( self::OPTION_NAME ); ?>[b2b_cta][description]" id="mfg_b2b_desc" rows="3" class="large-text"><?php echo esc_textarea( $b2b['description'] ); ?></textarea>
									</td>
								</tr>
								<tr>
									<th scope="row"><?php esc_html_e( 'Background Image', 'spicecraft-core' ); ?></th>
									<td>
										<?php spicecraft_render_admin_media_uploader( self::OPTION_NAME . '[b2b_cta][bg_image_id]', $b2b['bg_image_id'] ); ?>
									</td>
								</tr>
								<tr>
									<th scope="row"><?php esc_html_e( 'Primary CTA', 'spicecraft-core' ); ?></th>
									<td>
										<input type="text" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[b2b_cta][primary_cta_label]" value="<?php echo esc_attr( $b2b['primary_cta_label'] ); ?>" placeholder="Button Label" class="regular-text" style="width: 200px;" />
										<input type="text" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[b2b_cta][primary_cta_url]" value="<?php echo esc_attr( $b2b['primary_cta_url'] ); ?>" placeholder="Destination URL" class="regular-text" style="width: 250px;" />
									</td>
								</tr>
								<tr>
									<th scope="row"><?php esc_html_e( 'WhatsApp Trade Enquiry', 'spicecraft-core' ); ?></th>
									<td>
										<label>
											<input type="checkbox" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[b2b_cta][show_whatsapp]" value="1" <?php checked( ! empty( $b2b['show_whatsapp'] ) ); ?> />
											<?php esc_html_e( 'Display WhatsApp Trade Chat Button (uses Global WhatsApp number)', 'spicecraft-core' ); ?>
										</label>
									</td>
								</tr>
							</table>
						</div>
					</div>

					<!-- Section 15: Final CTA -->
					<div class="postbox">
						<div class="postbox-header"><h2 class="hndle"><?php esc_html_e( '15. Final Contact Action', 'spicecraft-core' ); ?></h2></div>
						<div class="inside">
							<table class="form-table" role="presentation">
								<tr>
									<th scope="row"><label for="mfg_final_heading"><?php esc_html_e( 'Heading', 'spicecraft-core' ); ?></label></th>
									<td>
										<input type="text" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[final_cta][heading]" id="mfg_final_heading" value="<?php echo esc_attr( $final['heading'] ); ?>" class="large-text" placeholder="e.g. Schedule a Facility Audit or Technical Consultation" />
									</td>
								</tr>
								<tr>
									<th scope="row"><label for="mfg_final_desc"><?php esc_html_e( 'Description', 'spicecraft-core' ); ?></label></th>
									<td>
										<textarea name="<?php echo esc_attr( self::OPTION_NAME ); ?>[final_cta][description]" id="mfg_final_desc" rows="3" class="large-text"><?php echo esc_textarea( $final['description'] ); ?></textarea>
									</td>
								</tr>
								<tr>
									<th scope="row"><?php esc_html_e( 'Direct Contact Channels', 'spicecraft-core' ); ?></th>
									<td>
										<label style="display: block; margin-bottom: 6px;">
											<input type="checkbox" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[final_cta][show_whatsapp]" value="1" <?php checked( ! empty( $final['show_whatsapp'] ) ); ?> />
											<?php esc_html_e( 'Show WhatsApp Link (reusing Global Settings WhatsApp)', 'spicecraft-core' ); ?>
										</label>
										<label style="display: block;">
											<input type="checkbox" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[final_cta][show_email]" value="1" <?php checked( ! empty( $final['show_email'] ) ); ?> />
											<?php esc_html_e( 'Show Email Trade Desk (reusing Global Settings Email)', 'spicecraft-core' ); ?>
										</label>
									</td>
								</tr>
							</table>
						</div>
					</div>

				<?php endif; ?>

				<div style="margin-top: 20px;">
					<?php submit_button( __( 'Save Manufacturing Settings', 'spicecraft-core' ) ); ?>
				</div>
			</form>
		</div>
		<?php
	}

	/**
	 * Sanitize Settings Input preserving other tabs.
	 *
	 * @param array $input Raw input.
	 * @return array Sanitized settings.
	 */
	public function sanitize_settings( $input ) {
		if ( ! current_user_can( 'manage_options' ) ) {
			return get_option( self::OPTION_NAME, array() );
		}

		$current = spicecraft_get_manufacturing_settings();
		if ( ! is_array( $input ) ) {
			return $current;
		}

		$sections = array_keys( spicecraft_get_manufacturing_default_settings()['sections_order'] );

		// Section Order & Enabled
		if ( isset( $input['sections_order'] ) ) {
			$current['sections_order'] = spicecraft_sanitize_order_array( $input['sections_order'], $sections );
		}
		if ( isset( $input['sections_enabled'] ) ) {
			$current['sections_enabled'] = spicecraft_sanitize_enabled_array( $input['sections_enabled'], $sections );
		}

		// Hero
		if ( isset( $input['hero'] ) ) {
			$h = $input['hero'];
			$current['hero'] = array(
				'eyebrow'             => sanitize_text_field( $h['eyebrow'] ?? '' ),
				'heading'             => sanitize_text_field( $h['heading'] ?? '' ),
				'heading_highlight'   => sanitize_text_field( $h['heading_highlight'] ?? '' ),
				'intro'               => sanitize_textarea_field( $h['intro'] ?? '' ),
				'desktop_image_id'    => absint( $h['desktop_image_id'] ?? 0 ),
				'mobile_image_id'     => absint( $h['mobile_image_id'] ?? 0 ),
				'image_alt'           => sanitize_text_field( $h['image_alt'] ?? '' ),
				'cta_primary_label'   => sanitize_text_field( $h['cta_primary_label'] ?? '' ),
				'cta_primary_url'     => esc_url_raw( $h['cta_primary_url'] ?? '' ),
				'cta_secondary_label' => sanitize_text_field( $h['cta_secondary_label'] ?? '' ),
				'cta_secondary_url'   => esc_url_raw( $h['cta_secondary_url'] ?? '' ),
			);
		}

		// Introduction
		if ( isset( $input['introduction'] ) ) {
			$in = $input['introduction'];
			$current['introduction'] = array(
				'eyebrow'            => sanitize_text_field( $in['eyebrow'] ?? '' ),
				'heading'            => sanitize_text_field( $in['heading'] ?? '' ),
				'content'            => wp_kses_post( $in['content'] ?? '' ),
				'image_primary_id'   => absint( $in['image_primary_id'] ?? 0 ),
				'image_secondary_id' => absint( $in['image_secondary_id'] ?? 0 ),
				'cta_label'          => sanitize_text_field( $in['cta_label'] ?? '' ),
				'cta_url'            => esc_url_raw( $in['cta_url'] ?? '' ),
			);
		}

		// Facility
		if ( isset( $input['facility'] ) ) {
			$f = $input['facility'];
			$current['facility'] = array(
				'eyebrow'            => sanitize_text_field( $f['eyebrow'] ?? '' ),
				'heading'            => sanitize_text_field( $f['heading'] ?? '' ),
				'description'        => sanitize_textarea_field( $f['description'] ?? '' ),
				'image_id'           => absint( $f['image_id'] ?? 0 ),
				'image_secondary_id' => absint( $f['image_secondary_id'] ?? 0 ),
				'highlights'         => array(),
			);
		}

		// Process
		if ( isset( $input['process'] ) ) {
			$pr = $input['process'];
			$current['process'] = array(
				'eyebrow'     => sanitize_text_field( $pr['eyebrow'] ?? '' ),
				'heading'     => sanitize_text_field( $pr['heading'] ?? '' ),
				'description' => sanitize_textarea_field( $pr['description'] ?? '' ),
				'items'       => spicecraft_sanitize_process_items( $pr['items'] ?? array() ),
			);
		}

		// Capabilities
		if ( isset( $input['capabilities'] ) ) {
			$c = $input['capabilities'];
			$current['capabilities'] = array(
				'eyebrow'     => sanitize_text_field( $c['eyebrow'] ?? '' ),
				'heading'     => sanitize_text_field( $c['heading'] ?? '' ),
				'description' => sanitize_textarea_field( $c['description'] ?? '' ),
				'items'       => array(),
				'cta_label'   => sanitize_text_field( $c['cta_label'] ?? '' ),
				'cta_url'     => esc_url_raw( $c['cta_url'] ?? '' ),
			);
		}

		// Equipment
		if ( isset( $input['equipment'] ) ) {
			$eq = $input['equipment'];
			$current['equipment'] = array(
				'eyebrow'     => sanitize_text_field( $eq['eyebrow'] ?? '' ),
				'heading'     => sanitize_text_field( $eq['heading'] ?? '' ),
				'description' => sanitize_textarea_field( $eq['description'] ?? '' ),
				'items'       => spicecraft_sanitize_equipment_items( $eq['items'] ?? array() ),
			);
		}

		// Hygiene
		if ( isset( $input['hygiene'] ) ) {
			$hy = $input['hygiene'];
			$current['hygiene'] = array(
				'eyebrow'     => sanitize_text_field( $hy['eyebrow'] ?? '' ),
				'heading'     => sanitize_text_field( $hy['heading'] ?? '' ),
				'description' => sanitize_textarea_field( $hy['description'] ?? '' ),
				'image_id'    => absint( $hy['image_id'] ?? 0 ),
				'practices'   => array(),
				'cta_label'   => sanitize_text_field( $hy['cta_label'] ?? '' ),
				'cta_url'     => esc_url_raw( $hy['cta_url'] ?? '' ),
			);
		}

		// Packaging
		if ( isset( $input['packaging'] ) ) {
			$pk = $input['packaging'];
			$current['packaging'] = array(
				'eyebrow'            => sanitize_text_field( $pk['eyebrow'] ?? '' ),
				'heading'            => sanitize_text_field( $pk['heading'] ?? '' ),
				'description'        => sanitize_textarea_field( $pk['description'] ?? '' ),
				'image_id'           => absint( $pk['image_id'] ?? 0 ),
				'image_secondary_id' => absint( $pk['image_secondary_id'] ?? 0 ),
				'capabilities'       => array(),
				'category_ids'       => array_filter( array_map( 'absint', $pk['category_ids'] ?? array() ) ),
				'cta_label'          => sanitize_text_field( $pk['cta_label'] ?? '' ),
				'cta_url'            => esc_url_raw( $pk['cta_url'] ?? '' ),
			);
		}

		// Warehousing
		if ( isset( $input['warehousing'] ) ) {
			$wh = $input['warehousing'];
			$current['warehousing'] = array(
				'eyebrow'     => sanitize_text_field( $wh['eyebrow'] ?? '' ),
				'heading'     => sanitize_text_field( $wh['heading'] ?? '' ),
				'description' => sanitize_textarea_field( $wh['description'] ?? '' ),
				'image_id'    => absint( $wh['image_id'] ?? 0 ),
				'highlights'  => array(),
			);
		}

		// Statistics
		if ( isset( $input['statistics'] ) ) {
			$st = $input['statistics'];
			$current['statistics'] = array(
				'eyebrow'     => sanitize_text_field( $st['eyebrow'] ?? '' ),
				'heading'     => sanitize_text_field( $st['heading'] ?? '' ),
				'description' => sanitize_textarea_field( $st['description'] ?? '' ),
				'items'       => spicecraft_sanitize_stat_items( $st['items'] ?? array() ),
			);
		}

		// Gallery
		if ( isset( $input['gallery'] ) ) {
			$g = $input['gallery'];
			$current['gallery'] = array(
				'eyebrow'        => sanitize_text_field( $g['eyebrow'] ?? '' ),
				'heading'        => sanitize_text_field( $g['heading'] ?? '' ),
				'description'    => sanitize_textarea_field( $g['description'] ?? '' ),
				'attachment_ids' => spicecraft_sanitize_gallery_ids( $g['attachment_ids'] ?? array() ),
			);
		}

		// Certifications
		if ( isset( $input['certifications'] ) ) {
			$crt = $input['certifications'];
			$current['certifications'] = array(
				'eyebrow'      => sanitize_text_field( $crt['eyebrow'] ?? '' ),
				'heading'      => sanitize_text_field( $crt['heading'] ?? '' ),
				'description'  => sanitize_textarea_field( $crt['description'] ?? '' ),
				'selected_ids' => array_filter( array_map( 'absint', $crt['selected_ids'] ?? array() ) ),
				'limit'        => absint( $crt['limit'] ?? 6 ),
				'cta_label'    => sanitize_text_field( $crt['cta_label'] ?? '' ),
				'cta_url'      => esc_url_raw( $crt['cta_url'] ?? '' ),
			);
		}

		// Products
		if ( isset( $input['products'] ) ) {
			$p = $input['products'];
			$current['products'] = array(
				'eyebrow'      => sanitize_text_field( $p['eyebrow'] ?? '' ),
				'heading'      => sanitize_text_field( $p['heading'] ?? '' ),
				'description'  => sanitize_textarea_field( $p['description'] ?? '' ),
				'source'       => in_array( $p['source'] ?? '', array( 'categories', 'products' ), true ) ? $p['source'] : 'categories',
				'selected_ids' => array_filter( array_map( 'absint', $p['selected_ids'] ?? array() ) ),
				'limit'        => absint( $p['limit'] ?? 4 ),
				'cta_label'    => sanitize_text_field( $p['cta_label'] ?? '' ),
				'cta_url'      => esc_url_raw( $p['cta_url'] ?? '' ),
			);
		}

		// B2B CTA
		if ( isset( $input['b2b_cta'] ) ) {
			$b = $input['b2b_cta'];
			$current['b2b_cta'] = array(
				'eyebrow'             => sanitize_text_field( $b['eyebrow'] ?? '' ),
				'heading'             => sanitize_text_field( $b['heading'] ?? '' ),
				'description'         => sanitize_textarea_field( $b['description'] ?? '' ),
				'bg_image_id'         => absint( $b['bg_image_id'] ?? 0 ),
				'primary_cta_label'   => sanitize_text_field( $b['primary_cta_label'] ?? '' ),
				'primary_cta_url'     => esc_url_raw( $b['primary_cta_url'] ?? '' ),
				'secondary_cta_label' => sanitize_text_field( $b['secondary_cta_label'] ?? '' ),
				'secondary_cta_url'   => esc_url_raw( $b['secondary_cta_url'] ?? '' ),
				'show_whatsapp'       => ! empty( $b['show_whatsapp'] ) ? 1 : 0,
			);
		}

		// Final CTA
		if ( isset( $input['final_cta'] ) ) {
			$fc = $input['final_cta'];
			$current['final_cta'] = array(
				'heading'       => sanitize_text_field( $fc['heading'] ?? '' ),
				'description'   => sanitize_textarea_field( $fc['description'] ?? '' ),
				'cta_label'     => sanitize_text_field( $fc['cta_label'] ?? '' ),
				'cta_url'       => esc_url_raw( $fc['cta_url'] ?? '' ),
				'show_whatsapp' => ! empty( $fc['show_whatsapp'] ) ? 1 : 0,
				'show_email'    => ! empty( $fc['show_email'] ) ? 1 : 0,
			);
		}

		return $current;
	}
}
