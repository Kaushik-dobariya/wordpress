<?php
/**
 * SpiceCraft Core - Recipe Archive & Display Settings CMS
 *
 * Implements administrative configuration for the Recipe Archive / Inspiration Hub:
 * - Archive Hero & Featured Recipe promotions
 * - Search & Filter display options
 * - Sorting & pagination
 * - Final CTA section & enquiry channels
 *
 * @package SpiceCraft_Core
 * @since 1.2.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class SpiceCraft_Recipe_Settings {

	/**
	 * Option Name in wp_options.
	 */
	const OPTION_NAME = 'spicecraft_recipe_settings';

	/**
	 * Singleton Instance.
	 *
	 * @var SpiceCraft_Recipe_Settings|null
	 */
	private static $instance = null;

	/**
	 * Get Singleton Instance.
	 *
	 * @return SpiceCraft_Recipe_Settings
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
		add_action( 'admin_menu', array( $this, 'register_admin_menu' ), 27 );
		add_action( 'admin_init', array( $this, 'register_settings' ) );
	}

	/**
	 * Register Admin Submenus.
	 */
	public function register_admin_menu() {
		// 1. Submenu under SpiceCraft overview
		add_submenu_page(
			'spicecraft-overview',
			__( 'Recipe Archive Settings', 'spicecraft-core' ),
			__( 'Recipe Settings', 'spicecraft-core' ),
			'manage_options',
			'spicecraft-recipe-settings',
			array( $this, 'render_settings_page' )
		);

		// 2. Submenu under Recipes CPT menu for easy access
		add_submenu_page(
			'edit.php?post_type=spicecraft_recipe',
			__( 'Recipe Archive & Display Settings', 'spicecraft-core' ),
			__( 'Archive Settings', 'spicecraft-core' ),
			'manage_options',
			'spicecraft-recipe-archive-settings',
			array( $this, 'render_settings_page' )
		);
	}

	/**
	 * Register Settings with WordPress Settings API.
	 */
	public function register_settings() {
		register_setting(
			'spicecraft_recipe_settings_group',
			self::OPTION_NAME,
			array(
				'sanitize_callback' => array( $this, 'sanitize_settings' ),
				'default'           => function_exists( 'spicecraft_get_recipe_settings' ) ? spicecraft_get_recipe_settings() : array(),
			)
		);
	}

	/**
	 * Sanitize Settings Input.
	 *
	 * @param array $input Raw input.
	 * @return array Sanitized settings.
	 */
	public function sanitize_settings( $input ) {
		if ( ! is_array( $input ) ) {
			return array();
		}

		$clean = array();

		$clean['archive_enabled']        = ! empty( $input['archive_enabled'] ) ? 1 : 0;
		$clean['eyebrow']                = isset( $input['eyebrow'] ) ? sanitize_text_field( $input['eyebrow'] ) : '';
		$clean['heading']                = isset( $input['heading'] ) ? sanitize_text_field( $input['heading'] ) : '';
		$clean['introduction']           = isset( $input['introduction'] ) ? sanitize_textarea_field( $input['introduction'] ) : '';
		$clean['desktop_hero_id']        = isset( $input['desktop_hero_id'] ) ? absint( $input['desktop_hero_id'] ) : 0;
		$clean['mobile_hero_id']         = isset( $input['mobile_hero_id'] ) ? absint( $input['mobile_hero_id'] ) : 0;
		$clean['featured_recipe_id']     = isset( $input['featured_recipe_id'] ) ? absint( $input['featured_recipe_id'] ) : 0;

		$clean['show_search']            = ! empty( $input['show_search'] ) ? 1 : 0;
		$clean['show_category_filter']   = ! empty( $input['show_category_filter'] ) ? 1 : 0;
		$clean['show_cuisine_filter']    = ! empty( $input['show_cuisine_filter'] ) ? 1 : 0;
		$clean['show_meal_type_filter']  = ! empty( $input['show_meal_type_filter'] ) ? 1 : 0;
		$clean['show_difficulty_filter'] = ! empty( $input['show_difficulty_filter'] ) ? 1 : 0;

		$clean['recipes_per_page']       = isset( $input['recipes_per_page'] ) ? max( 3, min( 48, absint( $input['recipes_per_page'] ) ) ) : 9;

		$allowed_sorts = array( 'date_desc', 'title_asc', 'prep_time', 'total_time' );
		$clean['default_sort']           = isset( $input['default_sort'] ) && in_array( $input['default_sort'], $allowed_sorts, true )
			? $input['default_sort']
			: 'date_desc';

		$clean['final_cta_enabled']      = ! empty( $input['final_cta_enabled'] ) ? 1 : 0;
		$clean['cta_heading']            = isset( $input['cta_heading'] ) ? sanitize_text_field( $input['cta_heading'] ) : '';
		$clean['cta_description']        = isset( $input['cta_description'] ) ? sanitize_textarea_field( $input['cta_description'] ) : '';
		$clean['cta_image_id']           = isset( $input['cta_image_id'] ) ? absint( $input['cta_image_id'] ) : 0;
		$clean['cta_whatsapp_enabled']   = ! empty( $input['cta_whatsapp_enabled'] ) ? 1 : 0;
		$clean['cta_email_enabled']      = ! empty( $input['cta_email_enabled'] ) ? 1 : 0;

		return $clean;
	}

	/**
	 * Render Settings Page.
	 */
	public function render_settings_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have permission to access this page.', 'spicecraft-core' ) );
		}

		$settings = function_exists( 'spicecraft_get_recipe_settings' )
			? spicecraft_get_recipe_settings()
			: array();

		// Fetch published recipes for Featured Recipe dropdown
		$published_recipes = get_posts( array(
			'post_type'      => 'spicecraft_recipe',
			'post_status'    => 'publish',
			'posts_per_page' => 100,
			'orderby'        => 'title',
			'order'          => 'ASC',
		) );
		?>
		<div class="wrap spicecraft-settings-wrap">
			<h1><?php esc_html_e( 'Recipe Archive & Culinary Hub Settings', 'spicecraft-core' ); ?></h1>
			<p class="description">
				<?php esc_html_e( 'Manage the public recipe archive page (/recipes/), discovery filters, featured recipe hero card, and call-to-action sections.', 'spicecraft-core' ); ?>
			</p>

			<form method="post" action="options.php">
				<?php
				settings_fields( 'spicecraft_recipe_settings_group' );
				?>

				<table class="form-table" role="presentation">
					<tr>
						<th scope="row"><?php esc_html_e( 'Archive Hub Status', 'spicecraft-core' ); ?></th>
						<td>
							<label for="sc_rc_archive_enable">
								<input type="checkbox" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[archive_enabled]" id="sc_rc_archive_enable" value="1" <?php checked( ! empty( $settings['archive_enabled'] ) ); ?> />
								<?php esc_html_e( 'Enable public Recipe & Inspiration Hub (/recipes/)', 'spicecraft-core' ); ?>
							</label>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="sc_rc_eyebrow"><?php esc_html_e( 'Archive Eyebrow', 'spicecraft-core' ); ?></label></th>
						<td>
							<input type="text" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[eyebrow]" id="sc_rc_eyebrow" value="<?php echo esc_attr( $settings['eyebrow'] ); ?>" class="regular-text" />
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="sc_rc_h1"><?php esc_html_e( 'Page H1 Title', 'spicecraft-core' ); ?></label></th>
						<td>
							<input type="text" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[heading]" id="sc_rc_h1" value="<?php echo esc_attr( $settings['heading'] ); ?>" class="regular-text" />
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="sc_rc_intro"><?php esc_html_e( 'Introduction Description', 'spicecraft-core' ); ?></label></th>
						<td>
							<textarea name="<?php echo esc_attr( self::OPTION_NAME ); ?>[introduction]" id="sc_rc_intro" rows="3" class="large-text"><?php echo esc_textarea( $settings['introduction'] ); ?></textarea>
						</td>
					</tr>
					<tr>
						<th scope="row"><?php esc_html_e( 'Desktop Hero Background', 'spicecraft-core' ); ?></th>
						<td>
							<?php
							if ( function_exists( 'spicecraft_render_admin_media_uploader' ) ) {
								spicecraft_render_admin_media_uploader(
									self::OPTION_NAME . '[desktop_hero_id]',
									$settings['desktop_hero_id'],
									__( 'Recommended: 1920x600 high-resolution culinary photography.', 'spicecraft-core' )
								);
							}
							?>
						</td>
					</tr>
					<tr>
						<th scope="row"><?php esc_html_e( 'Mobile Hero Background', 'spicecraft-core' ); ?></th>
						<td>
							<?php
							if ( function_exists( 'spicecraft_render_admin_media_uploader' ) ) {
								spicecraft_render_admin_media_uploader(
									self::OPTION_NAME . '[mobile_hero_id]',
									$settings['mobile_hero_id'],
									__( 'Recommended: 768x500 mobile-optimized banner.', 'spicecraft-core' )
								);
							}
							?>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="sc_rc_featured"><?php esc_html_e( 'Promoted Featured Recipe', 'spicecraft-core' ); ?></label></th>
						<td>
							<select name="<?php echo esc_attr( self::OPTION_NAME ); ?>[featured_recipe_id]" id="sc_rc_featured" class="regular-text">
								<option value="0"><?php esc_html_e( '— None (Hide Featured Showcase) —', 'spicecraft-core' ); ?></option>
								<?php foreach ( $published_recipes as $pr ) : ?>
									<option value="<?php echo esc_attr( $pr->ID ); ?>" <?php selected( $settings['featured_recipe_id'], $pr->ID ); ?>>
										<?php echo esc_html( $pr->post_title ); ?>
									</option>
								<?php endforeach; ?>
							</select>
							<p class="description"><?php esc_html_e( 'Displays as an editorial hero showcase with large photography, timing, and direct CTA above the filter bar.', 'spicecraft-core' ); ?></p>
						</td>
					</tr>

					<tr>
						<th scope="row"><?php esc_html_e( 'Discovery & Filter Controls', 'spicecraft-core' ); ?></th>
						<td>
							<label style="display: block; margin-bottom: 6px;">
								<input type="checkbox" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[show_search]" value="1" <?php checked( ! empty( $settings['show_search'] ) ); ?> />
								<?php esc_html_e( 'Display Keyword Search Input', 'spicecraft-core' ); ?>
							</label>
							<label style="display: block; margin-bottom: 6px;">
								<input type="checkbox" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[show_category_filter]" value="1" <?php checked( ! empty( $settings['show_category_filter'] ) ); ?> />
								<?php esc_html_e( 'Display Recipe Category Filter Chips/Dropdown', 'spicecraft-core' ); ?>
							</label>
							<label style="display: block; margin-bottom: 6px;">
								<input type="checkbox" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[show_cuisine_filter]" value="1" <?php checked( ! empty( $settings['show_cuisine_filter'] ) ); ?> />
								<?php esc_html_e( 'Display Cuisine Filter', 'spicecraft-core' ); ?>
							</label>
							<label style="display: block; margin-bottom: 6px;">
								<input type="checkbox" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[show_meal_type_filter]" value="1" <?php checked( ! empty( $settings['show_meal_type_filter'] ) ); ?> />
								<?php esc_html_e( 'Display Meal Type Filter', 'spicecraft-core' ); ?>
							</label>
							<label style="display: block;">
								<input type="checkbox" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[show_difficulty_filter]" value="1" <?php checked( ! empty( $settings['show_difficulty_filter'] ) ); ?> />
								<?php esc_html_e( 'Display Difficulty Filter', 'spicecraft-core' ); ?>
							</label>
						</td>
					</tr>

					<tr>
						<th scope="row"><label for="sc_rc_per_page"><?php esc_html_e( 'Recipes Per Page', 'spicecraft-core' ); ?></label></th>
						<td>
							<input type="number" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[recipes_per_page]" id="sc_rc_per_page" value="<?php echo esc_attr( $settings['recipes_per_page'] ); ?>" class="small-text" min="3" max="48" step="3" />
							<span class="description"><?php esc_html_e( 'Number of recipe cards displayed per grid page (default: 9).', 'spicecraft-core' ); ?></span>
						</td>
					</tr>

					<tr>
						<th scope="row"><label for="sc_rc_default_sort"><?php esc_html_e( 'Default Sort Order', 'spicecraft-core' ); ?></label></th>
						<td>
							<select name="<?php echo esc_attr( self::OPTION_NAME ); ?>[default_sort]" id="sc_rc_default_sort">
								<option value="date_desc" <?php selected( $settings['default_sort'], 'date_desc' ); ?>><?php esc_html_e( 'Newest First', 'spicecraft-core' ); ?></option>
								<option value="title_asc" <?php selected( $settings['default_sort'], 'title_asc' ); ?>><?php esc_html_e( 'Alphabetical (A–Z)', 'spicecraft-core' ); ?></option>
								<option value="prep_time" <?php selected( $settings['default_sort'], 'prep_time' ); ?>><?php esc_html_e( 'Shortest Prep Time', 'spicecraft-core' ); ?></option>
								<option value="total_time" <?php selected( $settings['default_sort'], 'total_time' ); ?>><?php esc_html_e( 'Shortest Total Time', 'spicecraft-core' ); ?></option>
							</select>
						</td>
					</tr>

					<tr>
						<th colspan="2">
							<hr style="margin: 20px 0; border: 0; border-top: 1px solid #dcdcde;" />
							<h2 style="margin-top: 0;"><?php esc_html_e( 'Archive Bottom Call to Action', 'spicecraft-core' ); ?></h2>
						</th>
					</tr>

					<tr>
						<th scope="row"><?php esc_html_e( 'Final CTA Status', 'spicecraft-core' ); ?></th>
						<td>
							<label for="sc_rc_cta_enable">
								<input type="checkbox" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[final_cta_enabled]" id="sc_rc_cta_enable" value="1" <?php checked( ! empty( $settings['final_cta_enabled'] ) ); ?> />
								<?php esc_html_e( 'Enable bottom Call to Action block on recipe archive', 'spicecraft-core' ); ?>
							</label>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="sc_rc_cta_h"><?php esc_html_e( 'CTA Heading', 'spicecraft-core' ); ?></label></th>
						<td>
							<input type="text" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[cta_heading]" id="sc_rc_cta_h" value="<?php echo esc_attr( $settings['cta_heading'] ); ?>" class="regular-text" />
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="sc_rc_cta_desc"><?php esc_html_e( 'CTA Description', 'spicecraft-core' ); ?></label></th>
						<td>
							<textarea name="<?php echo esc_attr( self::OPTION_NAME ); ?>[cta_description]" id="sc_rc_cta_desc" rows="2" class="large-text"><?php echo esc_textarea( $settings['cta_description'] ); ?></textarea>
						</td>
					</tr>
					<tr>
						<th scope="row"><?php esc_html_e( 'CTA Image', 'spicecraft-core' ); ?></th>
						<td>
							<?php
							if ( function_exists( 'spicecraft_render_admin_media_uploader' ) ) {
								spicecraft_render_admin_media_uploader(
									self::OPTION_NAME . '[cta_image_id]',
									$settings['cta_image_id']
								);
							}
							?>
						</td>
					</tr>
					<tr>
						<th scope="row"><?php esc_html_e( 'Enquiry Actions', 'spicecraft-core' ); ?></th>
						<td>
							<label style="display: inline-block; margin-right: 20px;">
								<input type="checkbox" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[cta_whatsapp_enabled]" value="1" <?php checked( ! empty( $settings['cta_whatsapp_enabled'] ) ); ?> />
								<?php esc_html_e( 'Enable WhatsApp Direct Enquiry', 'spicecraft-core' ); ?>
							</label>
							<label style="display: inline-block;">
								<input type="checkbox" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[cta_email_enabled]" value="1" <?php checked( ! empty( $settings['cta_email_enabled'] ) ); ?> />
								<?php esc_html_e( 'Enable Email / Trade Contact Button', 'spicecraft-core' ); ?>
							</label>
						</td>
					</tr>
				</table>

				<?php submit_button( __( 'Save Recipe Archive Settings', 'spicecraft-core' ) ); ?>
			</form>
		</div>
		<?php
	}
}
