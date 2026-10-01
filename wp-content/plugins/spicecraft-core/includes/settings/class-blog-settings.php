<?php
/**
 * SpiceCraft Core - Blog & Articles CMS Settings Page
 *
 * Implements administrative configuration for the public blog:
 * 1. Hero banner typography & lead content
 * 2. Listing & filter options (posts per page, search, category pills)
 * 3. Featured article banner controls
 * 4. Social sharing and related article rules
 * 5. B2B industry intelligence newsletter / enquiry CTA
 *
 * @package SpiceCraft_Core
 * @since 1.3.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class SpiceCraft_Blog_Settings {

	const SETTINGS_GROUP = 'spicecraft_blog_settings_group';
	const OPTION_NAME    = 'spicecraft_blog_settings';

	/**
	 * Singleton Instance.
	 *
	 * @var SpiceCraft_Blog_Settings|null
	 */
	private static $instance = null;

	/**
	 * Get Singleton Instance.
	 *
	 * @return SpiceCraft_Blog_Settings
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
	 * Register Admin Submenu under SpiceCraft.
	 */
	public function register_admin_menu() {
		add_submenu_page(
			'spicecraft-overview',
			__( 'SpiceCraft Blog & Articles Management', 'spicecraft-core' ),
			__( 'Blog / News', 'spicecraft-core' ),
			'manage_options',
			'spicecraft-blog',
			array( $this, 'render_settings_page' )
		);
	}

	/**
	 * Register Settings with Validation.
	 */
	public function register_settings() {
		register_setting(
			self::SETTINGS_GROUP,
			self::OPTION_NAME,
			array(
				'sanitize_callback' => array( $this, 'sanitize_settings' ),
				'default'           => spicecraft_get_blog_default_settings(),
			)
		);
	}

	/**
	 * Sanitize Settings Input.
	 *
	 * @param array $input Raw form data.
	 * @return array Clean sanitized data.
	 */
	public function sanitize_settings( $input ) {
		if ( ! is_array( $input ) ) {
			return array();
		}

		$clean = spicecraft_get_blog_default_settings();

		// 1. Hero & Header
		$clean['page_title']    = ! empty( $input['page_title'] ) ? sanitize_text_field( $input['page_title'] ) : $clean['page_title'];
		$clean['hero_badge']    = ! empty( $input['hero_badge'] ) ? sanitize_text_field( $input['hero_badge'] ) : $clean['hero_badge'];
		$clean['hero_title']    = ! empty( $input['hero_title'] ) ? sanitize_text_field( $input['hero_title'] ) : $clean['hero_title'];
		$clean['hero_subtitle'] = ! empty( $input['hero_subtitle'] ) ? sanitize_textarea_field( $input['hero_subtitle'] ) : $clean['hero_subtitle'];
		$clean['hero_image_id'] = ! empty( $input['hero_image_id'] ) ? absint( $input['hero_image_id'] ) : 0;

		// 2. Listing & Layout Controls
		$clean['posts_per_page']       = ! empty( $input['posts_per_page'] ) ? min( 30, max( 3, absint( $input['posts_per_page'] ) ) ) : 9;
		$clean['show_featured_banner'] = ! empty( $input['show_featured_banner'] ) ? 1 : 0;
		$clean['featured_post_id']     = ! empty( $input['featured_post_id'] ) ? absint( $input['featured_post_id'] ) : 0;
		$clean['show_category_filter'] = ! empty( $input['show_category_filter'] ) ? 1 : 0;
		$clean['show_search_bar']      = ! empty( $input['show_search_bar'] ) ? 1 : 0;
		$clean['show_author']          = ! empty( $input['show_author'] ) ? 1 : 0;
		$clean['show_published_date']  = ! empty( $input['show_published_date'] ) ? 1 : 0;
		$clean['show_reading_time']    = ! empty( $input['show_reading_time'] ) ? 1 : 0;
		$clean['show_tags_on_card']    = ! empty( $input['show_tags_on_card'] ) ? 1 : 0;

		// 3. Related Content
		$clean['related_count']    = ! empty( $input['related_count'] ) ? min( 6, max( 1, absint( $input['related_count'] ) ) ) : 3;
		$clean['related_strategy'] = in_array( $input['related_strategy'] ?? '', array( 'category', 'tags', 'latest' ), true ) ? $input['related_strategy'] : 'category';

		// 4. Social Sharing & Article Detail
		$clean['enable_social_share'] = ! empty( $input['enable_social_share'] ) ? 1 : 0;
		$clean['share_linkedin']      = ! empty( $input['share_linkedin'] ) ? 1 : 0;
		$clean['share_whatsapp']      = ! empty( $input['share_whatsapp'] ) ? 1 : 0;
		$clean['share_facebook']      = ! empty( $input['share_facebook'] ) ? 1 : 0;
		$clean['share_copy_link']     = ! empty( $input['share_copy_link'] ) ? 1 : 0;
		$clean['show_author_box']     = ! empty( $input['show_author_box'] ) ? 1 : 0;
		$clean['show_prev_next']      = ! empty( $input['show_prev_next'] ) ? 1 : 0;
		$clean['enable_schema']       = ! empty( $input['enable_schema'] ) ? 1 : 0;
		$clean['enable_open_graph']   = ! empty( $input['enable_open_graph'] ) ? 1 : 0;

		// 5. Bottom Lead CTA
		$clean['show_newsletter_cta']    = ! empty( $input['show_newsletter_cta'] ) ? 1 : 0;
		$clean['newsletter_badge']       = ! empty( $input['newsletter_badge'] ) ? sanitize_text_field( $input['newsletter_badge'] ) : $clean['newsletter_badge'];
		$clean['newsletter_heading']     = ! empty( $input['newsletter_heading'] ) ? sanitize_text_field( $input['newsletter_heading'] ) : $clean['newsletter_heading'];
		$clean['newsletter_description'] = ! empty( $input['newsletter_description'] ) ? sanitize_textarea_field( $input['newsletter_description'] ) : $clean['newsletter_description'];
		$clean['newsletter_btn_label']   = ! empty( $input['newsletter_btn_label'] ) ? sanitize_text_field( $input['newsletter_btn_label'] ) : $clean['newsletter_btn_label'];
		$clean['newsletter_btn_url']     = ! empty( $input['newsletter_btn_url'] ) ? esc_url_raw( $input['newsletter_btn_url'] ) : $clean['newsletter_btn_url'];

		return $clean;
	}

	/**
	 * Render the Settings Page HTML.
	 */
	public function render_settings_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$settings = spicecraft_get_blog_settings();
		?>
		<div class="wrap spicecraft-settings-wrap">
			<h1 class="wp-heading-inline">
				<span class="dashicons dashicons-welcome-write-blog" style="font-size: 28px; width: 28px; height: 28px; vertical-align: middle;"></span>
				<?php esc_html_e( 'SpiceCraft Blog & Articles Management', 'spicecraft-core' ); ?>
			</h1>
			<p class="description">
				<?php esc_html_e( 'Configure the public articles archive, hero presentations, category filtering, reading time options, social sharing, and B2B engagement.', 'spicecraft-core' ); ?>
			</p>
			<hr class="wp-header-end" />

			<?php settings_errors(); ?>

			<!-- Quick Management Sub-Navigation Toolbar -->
			<div style="margin: 15px 0 20px; display: flex; gap: 8px; flex-wrap: wrap;">
				<a href="<?php echo esc_url( admin_url( 'edit.php' ) ); ?>" class="button">
					<span class="dashicons dashicons-admin-post" style="vertical-align: middle;"></span> <?php esc_html_e( 'Manage Articles', 'spicecraft-core' ); ?>
				</a>
				<a href="<?php echo esc_url( admin_url( 'post-new.php' ) ); ?>" class="button button-primary">
					<span class="dashicons dashicons-plus-alt2" style="vertical-align: middle;"></span> <?php esc_html_e( 'Write New Article', 'spicecraft-core' ); ?>
				</a>
				<a href="<?php echo esc_url( admin_url( 'edit-tags.php?taxonomy=category' ) ); ?>" class="button">
					<span class="dashicons dashicons-category" style="vertical-align: middle;"></span> <?php esc_html_e( 'Manage Categories', 'spicecraft-core' ); ?>
				</a>
				<a href="<?php echo esc_url( admin_url( 'edit-tags.php?taxonomy=post_tag' ) ); ?>" class="button">
					<span class="dashicons dashicons-tag" style="vertical-align: middle;"></span> <?php esc_html_e( 'Manage Tags', 'spicecraft-core' ); ?>
				</a>
				<a href="<?php echo esc_url( spicecraft_get_blog_url() ); ?>" class="button" target="_blank" rel="noopener noreferrer">
					<span class="dashicons dashicons-external" style="vertical-align: middle;"></span> <?php esc_html_e( 'View Public Blog Page', 'spicecraft-core' ); ?>
				</a>
			</div>

			<form method="post" action="options.php" id="sc-blog-settings-form">
				<?php
				settings_fields( self::SETTINGS_GROUP );
				?>

				<div class="sc-metabox-wrapper" style="background: #fff; border: 1px solid #c3c4c7; border-radius: 6px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); margin-top: 15px;">
					<!-- Nav Tabs -->
					<div class="sc-metabox-tabs" role="tablist">
						<button type="button" class="sc-metabox-tab-btn is-active" data-tab="sc-blog-hero">
							<span class="dashicons dashicons-cover-image"></span> <?php esc_html_e( 'Hero & Intro', 'spicecraft-core' ); ?>
						</button>
						<button type="button" class="sc-metabox-tab-btn" data-tab="sc-blog-layout">
							<span class="dashicons dashicons-grid-view"></span> <?php esc_html_e( 'Listing & Filters', 'spicecraft-core' ); ?>
						</button>
						<button type="button" class="sc-metabox-tab-btn" data-tab="sc-blog-featured">
							<span class="dashicons dashicons-star-filled"></span> <?php esc_html_e( 'Featured & Related', 'spicecraft-core' ); ?>
						</button>
						<button type="button" class="sc-metabox-tab-btn" data-tab="sc-blog-sharing">
							<span class="dashicons dashicons-share"></span> <?php esc_html_e( 'Social & Article Detail', 'spicecraft-core' ); ?>
						</button>
						<button type="button" class="sc-metabox-tab-btn" data-tab="sc-blog-cta">
							<span class="dashicons dashicons-megaphone"></span> <?php esc_html_e( 'Bottom Lead CTA', 'spicecraft-core' ); ?>
						</button>
					</div>

					<!-- TAB 1: Hero & Intro -->
					<div class="sc-metabox-panel" id="sc-blog-hero" style="display: block; padding: 20px;">
						<table class="form-table" role="presentation">
							<tr>
								<th scope="row"><label for="sc_page_title"><?php esc_html_e( 'Blog Page Title', 'spicecraft-core' ); ?></label></th>
								<td>
									<input type="text" id="sc_page_title" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[page_title]" value="<?php echo esc_attr( $settings['page_title'] ); ?>" class="regular-text" />
									<p class="description"><?php esc_html_e( 'Used in browser title tag and breadcrumbs.', 'spicecraft-core' ); ?></p>
								</td>
							</tr>
							<tr>
								<th scope="row"><label for="sc_hero_badge"><?php esc_html_e( 'Hero Eyebrow / Badge', 'spicecraft-core' ); ?></label></th>
								<td>
									<input type="text" id="sc_hero_badge" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[hero_badge]" value="<?php echo esc_attr( $settings['hero_badge'] ); ?>" class="regular-text" placeholder="Knowledge & Insights" />
								</td>
							</tr>
							<tr>
								<th scope="row"><label for="sc_hero_title"><?php esc_html_e( 'Hero Main Heading (H1)', 'spicecraft-core' ); ?></label></th>
								<td>
									<input type="text" id="sc_hero_title" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[hero_title]" value="<?php echo esc_attr( $settings['hero_title'] ); ?>" class="large-text" />
									<p class="description"><?php esc_html_e( 'Single primary H1 heading for the Blog landing page.', 'spicecraft-core' ); ?></p>
								</td>
							</tr>
							<tr>
								<th scope="row"><label for="sc_hero_subtitle"><?php esc_html_e( 'Hero Subtitle / Description', 'spicecraft-core' ); ?></label></th>
								<td>
									<textarea id="sc_hero_subtitle" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[hero_subtitle]" rows="3" class="large-text"><?php echo esc_textarea( $settings['hero_subtitle'] ); ?></textarea>
								</td>
							</tr>
							<tr>
								<th scope="row"><label for="sc_hero_image_id"><?php esc_html_e( 'Hero Background Texture Image (Optional)', 'spicecraft-core' ); ?></label></th>
								<td>
									<div class="sc-image-field-wrap" style="display: flex; align-items: center; gap: 15px;">
										<input type="hidden" id="sc_hero_image_id" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[hero_image_id]" value="<?php echo esc_attr( $settings['hero_image_id'] ); ?>" />
										<div class="sc-image-preview" id="sc_hero_image_preview" style="width: 120px; height: 70px; border: 1px dashed #cbd5e1; border-radius: 4px; display: flex; align-items: center; justify-content: center; background: #f8fafc; overflow: hidden;">
											<?php if ( ! empty( $settings['hero_image_id'] ) ) : ?>
												<?php echo wp_get_attachment_image( $settings['hero_image_id'], 'thumbnail', false, array( 'style' => 'width:100%; height:100%; object-fit:cover;' ) ); ?>
											<?php else : ?>
												<span style="color: #94a3b8; font-size: 11px;"><?php esc_html_e( 'No Image', 'spicecraft-core' ); ?></span>
											<?php endif; ?>
										</div>
										<div>
											<button type="button" class="button sc-media-upload-btn" data-target="#sc_hero_image_id" data-preview="#sc_hero_image_preview">
												<?php esc_html_e( 'Select Image', 'spicecraft-core' ); ?>
											</button>
											<button type="button" class="button sc-media-remove-btn" data-target="#sc_hero_image_id" data-preview="#sc_hero_image_preview">
												<?php esc_html_e( 'Remove', 'spicecraft-core' ); ?>
											</button>
										</div>
									</div>
								</td>
							</tr>
						</table>
					</div>

					<!-- TAB 2: Listing & Layout -->
					<div class="sc-metabox-panel" id="sc-blog-layout" style="display: none; padding: 20px;">
						<table class="form-table" role="presentation">
							<tr>
								<th scope="row"><label for="sc_posts_per_page"><?php esc_html_e( 'Articles Per Page', 'spicecraft-core' ); ?></label></th>
								<td>
									<input type="number" id="sc_posts_per_page" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[posts_per_page]" value="<?php echo esc_attr( $settings['posts_per_page'] ); ?>" min="3" max="30" step="3" class="small-text" />
									<p class="description"><?php esc_html_e( 'Recommended: 6, 9, or 12 (divisible by 3 for a balanced desktop grid).', 'spicecraft-core' ); ?></p>
								</td>
							</tr>
							<tr>
								<th scope="row"><?php esc_html_e( 'Interactive Navigation & Filters', 'spicecraft-core' ); ?></th>
								<td>
									<fieldset>
										<label for="sc_show_category_filter" style="display: block; margin-bottom: 8px;">
											<input type="checkbox" id="sc_show_category_filter" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[show_category_filter]" value="1" <?php checked( ! empty( $settings['show_category_filter'] ) ); ?> />
											<?php esc_html_e( 'Display Category Filter Chips (Dynamic "All" + active categories with counts)', 'spicecraft-core' ); ?>
										</label>
										<label for="sc_show_search_bar" style="display: block; margin-bottom: 8px;">
											<input type="checkbox" id="sc_show_search_bar" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[show_search_bar]" value="1" <?php checked( ! empty( $settings['show_search_bar'] ) ); ?> />
											<?php esc_html_e( 'Display Responsive Article Search Bar', 'spicecraft-core' ); ?>
										</label>
									</fieldset>
								</td>
							</tr>
							<tr>
								<th scope="row"><?php esc_html_e( 'Article Card Metadata', 'spicecraft-core' ); ?></th>
								<td>
									<fieldset>
										<label for="sc_show_published_date" style="display: block; margin-bottom: 8px;">
											<input type="checkbox" id="sc_show_published_date" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[show_published_date]" value="1" <?php checked( ! empty( $settings['show_published_date'] ) ); ?> />
											<?php esc_html_e( 'Show Published Date', 'spicecraft-core' ); ?>
										</label>
										<label for="sc_show_reading_time" style="display: block; margin-bottom: 8px;">
											<input type="checkbox" id="sc_show_reading_time" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[show_reading_time]" value="1" <?php checked( ! empty( $settings['show_reading_time'] ) ); ?> />
											<?php esc_html_e( 'Show Reading Time Badge (e.g. 5 min read)', 'spicecraft-core' ); ?>
										</label>
										<label for="sc_show_author" style="display: block; margin-bottom: 8px;">
											<input type="checkbox" id="sc_show_author" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[show_author]" value="1" <?php checked( ! empty( $settings['show_author'] ) ); ?> />
											<?php esc_html_e( 'Show Author Name', 'spicecraft-core' ); ?>
										</label>
									</fieldset>
								</td>
							</tr>
						</table>
					</div>

					<!-- TAB 3: Featured & Related -->
					<div class="sc-metabox-panel" id="sc-blog-featured" style="display: none; padding: 20px;">
						<table class="form-table" role="presentation">
							<tr>
								<th scope="row"><?php esc_html_e( 'Top Featured Hero Banner', 'spicecraft-core' ); ?></th>
								<td>
									<label for="sc_show_featured_banner">
										<input type="checkbox" id="sc_show_featured_banner" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[show_featured_banner]" value="1" <?php checked( ! empty( $settings['show_featured_banner'] ) ); ?> />
										<strong><?php esc_html_e( 'Show Prominent Featured Article Banner on Blog Landing Page', 'spicecraft-core' ); ?></strong>
									</label>
									<p class="description"><?php esc_html_e( 'Renders a large full-width editorial banner showcasing the primary featured story before the article grid.', 'spicecraft-core' ); ?></p>
								</td>
							</tr>
							<tr>
								<th scope="row"><label for="sc_featured_post_id"><?php esc_html_e( 'Featured Article Source', 'spicecraft-core' ); ?></label></th>
								<td>
									<?php
									$published_posts = get_posts( array(
										'post_type'   => 'post',
										'post_status' => 'publish',
										'numberposts' => 50,
										'orderby'     => 'date',
										'order'       => 'DESC',
									) );
									?>
									<select id="sc_featured_post_id" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[featured_post_id]" style="max-width: 450px; width: 100%;">
										<option value="0" <?php selected( $settings['featured_post_id'], 0 ); ?>>
											<?php esc_html_e( '— Automatic (Latest Article Marked as Featured) —', 'spicecraft-core' ); ?>
										</option>
										<?php foreach ( $published_posts as $post_opt ) : ?>
											<option value="<?php echo esc_attr( $post_opt->ID ); ?>" <?php selected( $settings['featured_post_id'], $post_opt->ID ); ?>>
												<?php echo esc_html( $post_opt->post_title ); ?> (ID: <?php echo esc_html( $post_opt->ID ); ?>)
											</option>
										<?php endforeach; ?>
									</select>
									<p class="description"><?php esc_html_e( 'Select a specific article or leave on "Automatic" to show the most recently marked featured story.', 'spicecraft-core' ); ?></p>
								</td>
							</tr>
							<tr>
								<th scope="row"><label for="sc_related_count"><?php esc_html_e( 'Related Articles Count', 'spicecraft-core' ); ?></label></th>
								<td>
									<input type="number" id="sc_related_count" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[related_count]" value="<?php echo esc_attr( $settings['related_count'] ); ?>" min="1" max="6" class="small-text" />
									<p class="description"><?php esc_html_e( 'Number of related articles displayed at the foot of each single article page (typically 3).', 'spicecraft-core' ); ?></p>
								</td>
							</tr>
							<tr>
								<th scope="row"><label for="sc_related_strategy"><?php esc_html_e( 'Related Articles Matching Strategy', 'spicecraft-core' ); ?></label></th>
								<td>
									<select id="sc_related_strategy" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[related_strategy]">
										<option value="category" <?php selected( $settings['related_strategy'], 'category' ); ?>><?php esc_html_e( 'Same Category First (Recommended)', 'spicecraft-core' ); ?></option>
										<option value="tags" <?php selected( $settings['related_strategy'], 'tags' ); ?>><?php esc_html_e( 'Shared Tags First', 'spicecraft-core' ); ?></option>
										<option value="latest" <?php selected( $settings['related_strategy'], 'latest' ); ?>><?php esc_html_e( 'Latest Published Articles', 'spicecraft-core' ); ?></option>
									</select>
									<p class="description"><?php esc_html_e( 'Automatic waterfall fallback is always applied: Category &rarr; Tags &rarr; Latest.', 'spicecraft-core' ); ?></p>
								</td>
							</tr>
						</table>
					</div>

					<!-- TAB 4: Social & Article Detail -->
					<div class="sc-metabox-panel" id="sc-blog-sharing" style="display: none; padding: 20px;">
						<table class="form-table" role="presentation">
							<tr>
								<th scope="row"><?php esc_html_e( 'Social Sharing Integration', 'spicecraft-core' ); ?></th>
								<td>
									<label for="sc_enable_social_share" style="display: block; margin-bottom: 12px;">
										<input type="checkbox" id="sc_enable_social_share" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[enable_social_share]" value="1" <?php checked( ! empty( $settings['enable_social_share'] ) ); ?> />
										<strong><?php esc_html_e( 'Enable Lightweight Social Sharing on Article Detail Pages', 'spicecraft-core' ); ?></strong>
									</label>
									<div style="margin-left: 24px;">
										<label style="display: block; margin-bottom: 6px;">
											<input type="checkbox" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[share_linkedin]" value="1" <?php checked( ! empty( $settings['share_linkedin'] ) ); ?> />
											<?php esc_html_e( 'LinkedIn (Professional B2B Network)', 'spicecraft-core' ); ?>
										</label>
										<label style="display: block; margin-bottom: 6px;">
											<input type="checkbox" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[share_whatsapp]" value="1" <?php checked( ! empty( $settings['share_whatsapp'] ) ); ?> />
											<?php esc_html_e( 'WhatsApp (Direct Trade Messaging)', 'spicecraft-core' ); ?>
										</label>
										<label style="display: block; margin-bottom: 6px;">
											<input type="checkbox" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[share_facebook]" value="1" <?php checked( ! empty( $settings['share_facebook'] ) ); ?> />
											<?php esc_html_e( 'Facebook', 'spicecraft-core' ); ?>
										</label>
										<label style="display: block; margin-bottom: 6px;">
											<input type="checkbox" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[share_copy_link]" value="1" <?php checked( ! empty( $settings['share_copy_link'] ) ); ?> />
											<?php esc_html_e( 'Copy Article Link with Confirmation Tooltip', 'spicecraft-core' ); ?>
										</label>
									</div>
								</td>
							</tr>
							<tr>
								<th scope="row"><?php esc_html_e( 'Article Detail Page Elements', 'spicecraft-core' ); ?></th>
								<td>
									<fieldset>
										<label for="sc_show_author_box" style="display: block; margin-bottom: 8px;">
											<input type="checkbox" id="sc_show_author_box" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[show_author_box]" value="1" <?php checked( ! empty( $settings['show_author_box'] ) ); ?> />
											<?php esc_html_e( 'Display Author Bio & Credential Card', 'spicecraft-core' ); ?>
										</label>
										<label for="sc_show_prev_next" style="display: block; margin-bottom: 8px;">
											<input type="checkbox" id="sc_show_prev_next" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[show_prev_next]" value="1" <?php checked( ! empty( $settings['show_prev_next'] ) ); ?> />
											<?php esc_html_e( 'Display Previous / Next Article Navigation', 'spicecraft-core' ); ?>
										</label>
										<label for="sc_enable_schema" style="display: block; margin-bottom: 8px;">
											<input type="checkbox" id="sc_enable_schema" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[enable_schema]" value="1" <?php checked( ! empty( $settings['enable_schema'] ) ); ?> />
											<?php esc_html_e( 'Generate Schema.org BlogPosting Structured JSON-LD', 'spicecraft-core' ); ?>
										</label>
										<label for="sc_enable_open_graph" style="display: block; margin-bottom: 8px;">
											<input type="checkbox" id="sc_enable_open_graph" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[enable_open_graph]" value="1" <?php checked( ! empty( $settings['enable_open_graph'] ) ); ?> />
											<?php esc_html_e( 'Generate Open Graph and Twitter Card Social Metadata', 'spicecraft-core' ); ?>
										</label>
									</fieldset>
								</td>
							</tr>
						</table>
					</div>

					<!-- TAB 5: Bottom Lead CTA -->
					<div class="sc-metabox-panel" id="sc-blog-cta" style="display: none; padding: 20px;">
						<table class="form-table" role="presentation">
							<tr>
								<th scope="row"><?php esc_html_e( 'Enable Lead CTA', 'spicecraft-core' ); ?></th>
								<td>
									<label for="sc_show_newsletter_cta">
										<input type="checkbox" id="sc_show_newsletter_cta" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[show_newsletter_cta]" value="1" <?php checked( ! empty( $settings['show_newsletter_cta'] ) ); ?> />
										<strong><?php esc_html_e( 'Display B2B Intelligence & Contact CTA at bottom of Blog listing', 'spicecraft-core' ); ?></strong>
									</label>
								</td>
							</tr>
							<tr>
								<th scope="row"><label for="sc_newsletter_badge"><?php esc_html_e( 'CTA Badge', 'spicecraft-core' ); ?></label></th>
								<td>
									<input type="text" id="sc_newsletter_badge" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[newsletter_badge]" value="<?php echo esc_attr( $settings['newsletter_badge'] ); ?>" class="regular-text" />
								</td>
							</tr>
							<tr>
								<th scope="row"><label for="sc_newsletter_heading"><?php esc_html_e( 'CTA Heading', 'spicecraft-core' ); ?></label></th>
								<td>
									<input type="text" id="sc_newsletter_heading" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[newsletter_heading]" value="<?php echo esc_attr( $settings['newsletter_heading'] ); ?>" class="large-text" />
								</td>
							</tr>
							<tr>
								<th scope="row"><label for="sc_newsletter_description"><?php esc_html_e( 'CTA Description', 'spicecraft-core' ); ?></label></th>
								<td>
									<textarea id="sc_newsletter_description" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[newsletter_description]" rows="3" class="large-text"><?php echo esc_textarea( $settings['newsletter_description'] ); ?></textarea>
								</td>
							</tr>
							<tr>
								<th scope="row"><label for="sc_newsletter_btn_label"><?php esc_html_e( 'CTA Button Label', 'spicecraft-core' ); ?></label></th>
								<td>
									<input type="text" id="sc_newsletter_btn_label" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[newsletter_btn_label]" value="<?php echo esc_attr( $settings['newsletter_btn_label'] ); ?>" class="regular-text" />
								</td>
							</tr>
							<tr>
								<th scope="row"><label for="sc_newsletter_btn_url"><?php esc_html_e( 'CTA Button URL', 'spicecraft-core' ); ?></label></th>
								<td>
									<input type="url" id="sc_newsletter_btn_url" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[newsletter_btn_url]" value="<?php echo esc_attr( $settings['newsletter_btn_url'] ); ?>" class="regular-text" />
								</td>
							</tr>
						</table>
					</div>
				</div>

				<p class="submit" style="margin-top: 20px;">
					<?php submit_button( __( 'Save Blog CMS Settings', 'spicecraft-core' ), 'primary', 'submit', false ); ?>
				</p>
			</form>
		</div>

		<script>
		jQuery(document).ready(function($) {
			// Tab switching
			$('.sc-metabox-tab-btn').on('click', function(e) {
				e.preventDefault();
				var tabId = $(this).data('tab');
				$('.sc-metabox-tab-btn').removeClass('is-active');
				$(this).addClass('is-active');
				$('.sc-metabox-panel').hide();
				$('#' + tabId).show();
			});
		});
		</script>
		<?php
	}
}
