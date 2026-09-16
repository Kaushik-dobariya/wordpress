<?php
/**
 * SpiceCraft Core - Centralized Homepage CMS Settings Interface
 *
 * Implements a dedicated, WordPress-native admin settings interface under
 * SpiceCraft -> Homepage. Provides structured management of 14 dynamic sections,
 * media uploaders, repeatable fields, and section visibility/ordering.
 *
 * @package SpiceCraft_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class SpiceCraft_Homepage_Settings {

	/**
	 * Option Name in wp_options.
	 */
	const OPTION_NAME = 'spicecraft_homepage_settings';

	/**
	 * Singleton Instance.
	 *
	 * @var SpiceCraft_Homepage_Settings|null
	 */
	private static $instance = null;

	/**
	 * Get Singleton Instance.
	 *
	 * @return SpiceCraft_Homepage_Settings
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
		add_action( 'admin_menu', array( $this, 'register_admin_menu' ), 20 );
		add_action( 'admin_init', array( $this, 'register_settings' ) );
	}

	/**
	 * Register Admin Submenu.
	 */
	public function register_admin_menu() {
		add_submenu_page(
			'spicecraft-overview',
			__( 'Homepage CMS Management', 'spicecraft-core' ),
			__( 'Homepage', 'spicecraft-core' ),
			'manage_options',
			'spicecraft-homepage',
			array( $this, 'render_page' )
		);
	}

	/**
	 * Register Settings with Validation & Sanitization.
	 */
	public function register_settings() {
		register_setting(
			'spicecraft_homepage_group',
			self::OPTION_NAME,
			array(
				'sanitize_callback' => array( $this, 'sanitize_settings' ),
				'default'           => spicecraft_get_homepage_default_settings(),
			)
		);
	}

	/**
	 * Sanitize and validate settings input.
	 * Merges with existing settings to prevent accidental erasure of inactive tabs.
	 *
	 * @param array $input Raw form data.
	 * @return array Clean sanitized data.
	 */
	public function sanitize_settings( $input ) {
		if ( ! is_array( $input ) ) {
			return array();
		}

		$current = get_option( self::OPTION_NAME, spicecraft_get_homepage_default_settings() );
		$clean   = $current;

		// 1. Section Order
		if ( isset( $input['sections_order'] ) && is_array( $input['sections_order'] ) ) {
			foreach ( $input['sections_order'] as $sec => $val ) {
				$clean['sections_order'][ sanitize_key( $sec ) ] = absint( $val );
			}
		}

		// 2. Section Enabled Map
		if ( isset( $input['sections_enabled'] ) && is_array( $input['sections_enabled'] ) ) {
			$defaults = spicecraft_get_homepage_default_settings();
			foreach ( array_keys( $defaults['sections_order'] ) as $sec_key ) {
				$clean['sections_enabled'][ $sec_key ] = ! empty( $input['sections_enabled'][ $sec_key ] ) ? 1 : 0;
			}
		}

		// 3. Hero Section
		if ( isset( $input['hero'] ) && is_array( $input['hero'] ) ) {
			$hero = $input['hero'];
			$clean['hero']['eyebrow']             = isset( $hero['eyebrow'] ) ? sanitize_text_field( $hero['eyebrow'] ) : '';
			$clean['hero']['heading']             = isset( $hero['heading'] ) ? sanitize_text_field( $hero['heading'] ) : '';
			$clean['hero']['highlight_text']      = isset( $hero['highlight_text'] ) ? sanitize_text_field( $hero['highlight_text'] ) : '';
			$clean['hero']['description']         = isset( $hero['description'] ) ? sanitize_textarea_field( $hero['description'] ) : '';
			$clean['hero']['primary_cta_label']   = isset( $hero['primary_cta_label'] ) ? sanitize_text_field( $hero['primary_cta_label'] ) : '';
			$clean['hero']['primary_cta_url']     = isset( $hero['primary_cta_url'] ) ? esc_url_raw( $hero['primary_cta_url'] ) : '';
			$clean['hero']['secondary_cta_label'] = isset( $hero['secondary_cta_label'] ) ? sanitize_text_field( $hero['secondary_cta_label'] ) : '';
			$clean['hero']['secondary_cta_url']   = isset( $hero['secondary_cta_url'] ) ? esc_url_raw( $hero['secondary_cta_url'] ) : '';
			$clean['hero']['desktop_image_id']    = isset( $hero['desktop_image_id'] ) ? absint( $hero['desktop_image_id'] ) : 0;
			$clean['hero']['mobile_image_id']     = isset( $hero['mobile_image_id'] ) ? absint( $hero['mobile_image_id'] ) : 0;
			$clean['hero']['image_alt']           = isset( $hero['image_alt'] ) ? sanitize_text_field( $hero['image_alt'] ) : '';
			$clean['hero']['badge_text']          = isset( $hero['badge_text'] ) ? sanitize_text_field( $hero['badge_text'] ) : '';
			$clean['hero']['bg_treatment']        = isset( $hero['bg_treatment'] ) && in_array( $hero['bg_treatment'], array( 'gradient', 'dark', 'subtle' ), true ) ? $hero['bg_treatment'] : 'gradient';
		}

		// 4. Product Categories
		if ( isset( $input['categories'] ) && is_array( $input['categories'] ) ) {
			$cat = $input['categories'];
			$clean['categories']['eyebrow']      = isset( $cat['eyebrow'] ) ? sanitize_text_field( $cat['eyebrow'] ) : '';
			$clean['categories']['heading']      = isset( $cat['heading'] ) ? sanitize_text_field( $cat['heading'] ) : '';
			$clean['categories']['description']  = isset( $cat['description'] ) ? sanitize_textarea_field( $cat['description'] ) : '';
			$clean['categories']['display_mode'] = isset( $cat['display_mode'] ) && in_array( $cat['display_mode'], array( 'all', 'top_level', 'manual' ), true ) ? $cat['display_mode'] : 'all';
			$clean['categories']['limit']        = isset( $cat['limit'] ) ? max( 1, min( 24, absint( $cat['limit'] ) ) ) : 6;
			$clean['categories']['selected_ids'] = isset( $cat['selected_ids'] ) && is_array( $cat['selected_ids'] ) ? array_map( 'absint', $cat['selected_ids'] ) : array();
			$clean['categories']['cta_label']    = isset( $cat['cta_label'] ) ? sanitize_text_field( $cat['cta_label'] ) : '';
			$clean['categories']['cta_url']      = isset( $cat['cta_url'] ) ? esc_url_raw( $cat['cta_url'] ) : '';
		}

		// 5. Featured Products
		if ( isset( $input['featured_products'] ) && is_array( $input['featured_products'] ) ) {
			$fp = $input['featured_products'];
			$clean['featured_products']['eyebrow']      = isset( $fp['eyebrow'] ) ? sanitize_text_field( $fp['eyebrow'] ) : '';
			$clean['featured_products']['heading']      = isset( $fp['heading'] ) ? sanitize_text_field( $fp['heading'] ) : '';
			$clean['featured_products']['description']  = isset( $fp['description'] ) ? sanitize_textarea_field( $fp['description'] ) : '';
			$clean['featured_products']['source']       = isset( $fp['source'] ) && in_array( $fp['source'], array( 'featured', 'manual', 'latest' ), true ) ? $fp['source'] : 'featured';
			$clean['featured_products']['limit']        = isset( $fp['limit'] ) ? max( 1, min( 24, absint( $fp['limit'] ) ) ) : 8;
			$clean['featured_products']['selected_ids'] = isset( $fp['selected_ids'] ) && is_array( $fp['selected_ids'] ) ? array_map( 'absint', $fp['selected_ids'] ) : array();
			$clean['featured_products']['cta_label']    = isset( $fp['cta_label'] ) ? sanitize_text_field( $fp['cta_label'] ) : '';
			$clean['featured_products']['cta_url']      = isset( $fp['cta_url'] ) ? esc_url_raw( $fp['cta_url'] ) : '';
		}

		// 6. Brand Story
		if ( isset( $input['brand_story'] ) && is_array( $input['brand_story'] ) ) {
			$bs = $input['brand_story'];
			$clean['brand_story']['eyebrow']            = isset( $bs['eyebrow'] ) ? sanitize_text_field( $bs['eyebrow'] ) : '';
			$clean['brand_story']['heading']            = isset( $bs['heading'] ) ? sanitize_text_field( $bs['heading'] ) : '';
			$clean['brand_story']['description']        = isset( $bs['description'] ) ? wp_kses_post( $bs['description'] ) : '';
			$clean['brand_story']['primary_image_id']   = isset( $bs['primary_image_id'] ) ? absint( $bs['primary_image_id'] ) : 0;
			$clean['brand_story']['secondary_image_id'] = isset( $bs['secondary_image_id'] ) ? absint( $bs['secondary_image_id'] ) : 0;
			$clean['brand_story']['stat_label']         = isset( $bs['stat_label'] ) ? sanitize_text_field( $bs['stat_label'] ) : '';
			$clean['brand_story']['stat_value']         = isset( $bs['stat_value'] ) ? sanitize_text_field( $bs['stat_value'] ) : '';
			$clean['brand_story']['cta_label']          = isset( $bs['cta_label'] ) ? sanitize_text_field( $bs['cta_label'] ) : '';
			$clean['brand_story']['cta_url']            = isset( $bs['cta_url'] ) ? esc_url_raw( $bs['cta_url'] ) : '';
		}

		// 7. Why Choose Us (Repeatable items)
		if ( isset( $input['why_choose_us'] ) && is_array( $input['why_choose_us'] ) ) {
			$wcu = $input['why_choose_us'];
			$clean['why_choose_us']['eyebrow']     = isset( $wcu['eyebrow'] ) ? sanitize_text_field( $wcu['eyebrow'] ) : '';
			$clean['why_choose_us']['heading']     = isset( $wcu['heading'] ) ? sanitize_text_field( $wcu['heading'] ) : '';
			$clean['why_choose_us']['description'] = isset( $wcu['description'] ) ? sanitize_textarea_field( $wcu['description'] ) : '';

			$clean_items = array();
			if ( isset( $wcu['items'] ) && is_array( $wcu['items'] ) ) {
				foreach ( $wcu['items'] as $item ) {
					$title = isset( $item['title'] ) ? sanitize_text_field( $item['title'] ) : '';
					$desc  = isset( $item['description'] ) ? sanitize_textarea_field( $item['description'] ) : '';
					$icon  = isset( $item['icon'] ) ? sanitize_text_field( $item['icon'] ) : '';
					$order = isset( $item['order'] ) ? absint( $item['order'] ) : 10;

					if ( ! empty( $title ) || ! empty( $desc ) ) {
						$clean_items[] = array(
							'icon'        => $icon,
							'title'       => $title,
							'description' => $desc,
							'order'       => $order,
						);
					}
				}
			}
			$clean['why_choose_us']['items'] = $clean_items;
		}

		// 8. Quality & Sourcing
		if ( isset( $input['quality_sourcing'] ) && is_array( $input['quality_sourcing'] ) ) {
			$qs = $input['quality_sourcing'];
			$clean['quality_sourcing']['eyebrow']          = isset( $qs['eyebrow'] ) ? sanitize_text_field( $qs['eyebrow'] ) : '';
			$clean['quality_sourcing']['heading']          = isset( $qs['heading'] ) ? sanitize_text_field( $qs['heading'] ) : '';
			$clean['quality_sourcing']['description']      = isset( $qs['description'] ) ? wp_kses_post( $qs['description'] ) : '';
			$clean['quality_sourcing']['main_image_id']    = isset( $qs['main_image_id'] ) ? absint( $qs['main_image_id'] ) : 0;
			$clean['quality_sourcing']['support_image_id'] = isset( $qs['support_image_id'] ) ? absint( $qs['support_image_id'] ) : 0;
			$clean['quality_sourcing']['cta_label']        = isset( $qs['cta_label'] ) ? sanitize_text_field( $qs['cta_label'] ) : '';
			$clean['quality_sourcing']['cta_url']          = isset( $qs['cta_url'] ) ? esc_url_raw( $qs['cta_url'] ) : '';

			$clean_points = array();
			if ( isset( $qs['points'] ) && is_array( $qs['points'] ) ) {
				foreach ( $qs['points'] as $pt ) {
					$title = isset( $pt['title'] ) ? sanitize_text_field( $pt['title'] ) : '';
					$text  = isset( $pt['text'] ) ? sanitize_textarea_field( $pt['text'] ) : '';
					if ( ! empty( $title ) || ! empty( $text ) ) {
						$clean_points[] = array(
							'title' => $title,
							'text'  => $text,
						);
					}
				}
			}
			$clean['quality_sourcing']['points'] = $clean_points;
		}

		// 9. Manufacturing
		if ( isset( $input['manufacturing'] ) && is_array( $input['manufacturing'] ) ) {
			$mfg = $input['manufacturing'];
			$clean['manufacturing']['eyebrow']          = isset( $mfg['eyebrow'] ) ? sanitize_text_field( $mfg['eyebrow'] ) : '';
			$clean['manufacturing']['heading']          = isset( $mfg['heading'] ) ? sanitize_text_field( $mfg['heading'] ) : '';
			$clean['manufacturing']['description']      = isset( $mfg['description'] ) ? wp_kses_post( $mfg['description'] ) : '';
			$clean['manufacturing']['main_image_id']    = isset( $mfg['main_image_id'] ) ? absint( $mfg['main_image_id'] ) : 0;
			$clean['manufacturing']['support_image_id'] = isset( $mfg['support_image_id'] ) ? absint( $mfg['support_image_id'] ) : 0;
			$clean['manufacturing']['video_url']        = isset( $mfg['video_url'] ) ? esc_url_raw( $mfg['video_url'] ) : '';
			$clean['manufacturing']['cta_label']        = isset( $mfg['cta_label'] ) ? sanitize_text_field( $mfg['cta_label'] ) : '';
			$clean['manufacturing']['cta_url']          = isset( $mfg['cta_url'] ) ? esc_url_raw( $mfg['cta_url'] ) : '';

			$clean_stats = array();
			if ( isset( $mfg['stats'] ) && is_array( $mfg['stats'] ) ) {
				foreach ( $mfg['stats'] as $st ) {
					$label = isset( $st['label'] ) ? sanitize_text_field( $st['label'] ) : '';
					$val   = isset( $st['value'] ) ? sanitize_text_field( $st['value'] ) : '';
					if ( ! empty( $label ) || ! empty( $val ) ) {
						$clean_stats[] = array(
							'label' => $label,
							'value' => $val,
						);
					}
				}
			}
			$clean['manufacturing']['stats'] = $clean_stats;
		}

		// 10. Certifications
		if ( isset( $input['certifications'] ) && is_array( $input['certifications'] ) ) {
			$cert = $input['certifications'];
			$clean['certifications']['eyebrow']      = isset( $cert['eyebrow'] ) ? sanitize_text_field( $cert['eyebrow'] ) : '';
			$clean['certifications']['heading']      = isset( $cert['heading'] ) ? sanitize_text_field( $cert['heading'] ) : '';
			$clean['certifications']['description']  = isset( $cert['description'] ) ? sanitize_textarea_field( $cert['description'] ) : '';
			$clean['certifications']['limit']        = isset( $cert['limit'] ) ? max( 1, min( 20, absint( $cert['limit'] ) ) ) : 6;
			$clean['certifications']['selected_ids'] = isset( $cert['selected_ids'] ) && is_array( $cert['selected_ids'] ) ? array_map( 'absint', $cert['selected_ids'] ) : array();
		}

		// 11. Product Discovery
		if ( isset( $input['product_discovery'] ) && is_array( $input['product_discovery'] ) ) {
			$pd = $input['product_discovery'];
			$clean['product_discovery']['eyebrow']      = isset( $pd['eyebrow'] ) ? sanitize_text_field( $pd['eyebrow'] ) : '';
			$clean['product_discovery']['heading']      = isset( $pd['heading'] ) ? sanitize_text_field( $pd['heading'] ) : '';
			$clean['product_discovery']['description']  = isset( $pd['description'] ) ? sanitize_textarea_field( $pd['description'] ) : '';
			$clean['product_discovery']['category_ids'] = isset( $pd['category_ids'] ) && is_array( $pd['category_ids'] ) ? array_map( 'absint', $pd['category_ids'] ) : array();
			$clean['product_discovery']['cta_label']    = isset( $pd['cta_label'] ) ? sanitize_text_field( $pd['cta_label'] ) : '';
			$clean['product_discovery']['cta_url']      = isset( $pd['cta_url'] ) ? esc_url_raw( $pd['cta_url'] ) : '';
		}

		// 12. Recipes
		if ( isset( $input['recipes'] ) && is_array( $input['recipes'] ) ) {
			$rc = $input['recipes'];
			$clean['recipes']['eyebrow']     = isset( $rc['eyebrow'] ) ? sanitize_text_field( $rc['eyebrow'] ) : '';
			$clean['recipes']['heading']     = isset( $rc['heading'] ) ? sanitize_text_field( $rc['heading'] ) : '';
			$clean['recipes']['description'] = isset( $rc['description'] ) ? sanitize_textarea_field( $rc['description'] ) : '';
			$clean['recipes']['source_type'] = isset( $rc['source_type'] ) ? sanitize_key( $rc['source_type'] ) : 'post_category';
			$clean['recipes']['category_id'] = isset( $rc['category_id'] ) ? absint( $rc['category_id'] ) : 0;
			$clean['recipes']['limit']       = isset( $rc['limit'] ) ? max( 1, min( 12, absint( $rc['limit'] ) ) ) : 3;
			$clean['recipes']['cta_label']   = isset( $rc['cta_label'] ) ? sanitize_text_field( $rc['cta_label'] ) : '';
			$clean['recipes']['cta_url']     = isset( $rc['cta_url'] ) ? esc_url_raw( $rc['cta_url'] ) : '';
		}

		// 13. Testimonials
		if ( isset( $input['testimonials'] ) && is_array( $input['testimonials'] ) ) {
			$tst = $input['testimonials'];
			$clean['testimonials']['eyebrow']      = isset( $tst['eyebrow'] ) ? sanitize_text_field( $tst['eyebrow'] ) : '';
			$clean['testimonials']['heading']      = isset( $tst['heading'] ) ? sanitize_text_field( $tst['heading'] ) : '';
			$clean['testimonials']['description']  = isset( $tst['description'] ) ? sanitize_textarea_field( $tst['description'] ) : '';
			$clean['testimonials']['limit']        = isset( $tst['limit'] ) ? max( 1, min( 20, absint( $tst['limit'] ) ) ) : 6;
			$clean['testimonials']['selected_ids'] = isset( $tst['selected_ids'] ) && is_array( $tst['selected_ids'] ) ? array_map( 'absint', $tst['selected_ids'] ) : array();
		}

		// 14. Blog
		if ( isset( $input['blog'] ) && is_array( $input['blog'] ) ) {
			$blg = $input['blog'];
			$clean['blog']['eyebrow']      = isset( $blg['eyebrow'] ) ? sanitize_text_field( $blg['eyebrow'] ) : '';
			$clean['blog']['heading']      = isset( $blg['heading'] ) ? sanitize_text_field( $blg['heading'] ) : '';
			$clean['blog']['description']  = isset( $blg['description'] ) ? sanitize_textarea_field( $blg['description'] ) : '';
			$clean['blog']['source']       = isset( $blg['source'] ) && in_array( $blg['source'], array( 'latest', 'category', 'manual' ), true ) ? $blg['source'] : 'latest';
			$clean['blog']['category_id']  = isset( $blg['category_id'] ) ? absint( $blg['category_id'] ) : 0;
			$clean['blog']['selected_ids'] = isset( $blg['selected_ids'] ) && is_array( $blg['selected_ids'] ) ? array_map( 'absint', $blg['selected_ids'] ) : array();
			$clean['blog']['limit']        = isset( $blg['limit'] ) ? max( 1, min( 12, absint( $blg['limit'] ) ) ) : 3;
			$clean['blog']['cta_label']    = isset( $blg['cta_label'] ) ? sanitize_text_field( $blg['cta_label'] ) : '';
			$clean['blog']['cta_url']      = isset( $blg['cta_url'] ) ? esc_url_raw( $blg['cta_url'] ) : '';
		}

		// 15. B2B CTA
		if ( isset( $input['b2b_cta'] ) && is_array( $input['b2b_cta'] ) ) {
			$b2b = $input['b2b_cta'];
			$clean['b2b_cta']['eyebrow']             = isset( $b2b['eyebrow'] ) ? sanitize_text_field( $b2b['eyebrow'] ) : '';
			$clean['b2b_cta']['heading']             = isset( $b2b['heading'] ) ? sanitize_text_field( $b2b['heading'] ) : '';
			$clean['b2b_cta']['description']         = isset( $b2b['description'] ) ? sanitize_textarea_field( $b2b['description'] ) : '';
			$clean['b2b_cta']['bg_image_id']         = isset( $b2b['bg_image_id'] ) ? absint( $b2b['bg_image_id'] ) : 0;
			$clean['b2b_cta']['primary_cta_label']   = isset( $b2b['primary_cta_label'] ) ? sanitize_text_field( $b2b['primary_cta_label'] ) : '';
			$clean['b2b_cta']['primary_cta_url']     = isset( $b2b['primary_cta_url'] ) ? esc_url_raw( $b2b['primary_cta_url'] ) : '';
			$clean['b2b_cta']['secondary_cta_label'] = isset( $b2b['secondary_cta_label'] ) ? sanitize_text_field( $b2b['secondary_cta_label'] ) : '';
			$clean['b2b_cta']['secondary_cta_url']   = isset( $b2b['secondary_cta_url'] ) ? esc_url_raw( $b2b['secondary_cta_url'] ) : '';
			$clean['b2b_cta']['enable_whatsapp']     = ! empty( $b2b['enable_whatsapp'] ) ? 1 : 0;
		}

		// 16. Final CTA
		if ( isset( $input['final_cta'] ) && is_array( $input['final_cta'] ) ) {
			$fcta = $input['final_cta'];
			$clean['final_cta']['heading']           = isset( $fcta['heading'] ) ? sanitize_text_field( $fcta['heading'] ) : '';
			$clean['final_cta']['description']       = isset( $fcta['description'] ) ? sanitize_textarea_field( $fcta['description'] ) : '';
			$clean['final_cta']['primary_cta_label'] = isset( $fcta['primary_cta_label'] ) ? sanitize_text_field( $fcta['primary_cta_label'] ) : '';
			$clean['final_cta']['primary_cta_url']   = isset( $fcta['primary_cta_url'] ) ? esc_url_raw( $fcta['primary_cta_url'] ) : '';
			$clean['final_cta']['enable_whatsapp']   = ! empty( $fcta['enable_whatsapp'] ) ? 1 : 0;
			$clean['final_cta']['enable_email']      = ! empty( $fcta['enable_email'] ) ? 1 : 0;
		}

		return $clean;
	}

	/**
	 * Render Homepage Settings Interface.
	 */
	public function render_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'spicecraft-core' ) );
		}

		$settings   = spicecraft_get_homepage_settings();
		$active_tab = isset( $_GET['tab'] ) ? sanitize_key( $_GET['tab'] ) : 'order';

		// Available admin tabs
		$tabs = array(
			'order'          => __( '1. Order & Visibility', 'spicecraft-core' ),
			'hero'           => __( '2. Hero', 'spicecraft-core' ),
			'catalog'        => __( '3. Categories & Featured', 'spicecraft-core' ),
			'story'          => __( '4. Story & Why Us', 'spicecraft-core' ),
			'infrastructure' => __( '5. Quality & Facility', 'spicecraft-core' ),
			'trust'          => __( '6. Certs & Testimonials', 'spicecraft-core' ),
			'discovery'      => __( '7. Discovery, Recipes & Blog', 'spicecraft-core' ),
			'cta'            => __( '8. Business & Final CTAs', 'spicecraft-core' ),
		);
		?>
		<div class="wrap spicecraft-settings-wrap">
			<div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px; margin-bottom: 12px;">
				<h1 style="margin: 0;"><?php esc_html_e( 'SpiceCraft Homepage CMS Management', 'spicecraft-core' ); ?></h1>
				<a href="<?php echo esc_url( home_url( '/' ) ); ?>" target="_blank" rel="noopener noreferrer" class="button button-secondary" style="display: inline-flex; align-items: center; gap: 4px;">
					<span class="dashicons dashicons-external" style="margin-top: -2px;"></span>
					<?php esc_html_e( 'View Homepage', 'spicecraft-core' ); ?>
				</a>
			</div>

			<p class="description" style="margin-bottom: 16px;">
				<?php esc_html_e( 'Configure the 14 modular homepage sections, adjust display order, upload media assets, and control conversion callouts without touching template code.', 'spicecraft-core' ); ?>
			</p>

			<?php settings_errors(); ?>

			<!-- Navigation Tabs -->
			<h2 class="nav-tab-wrapper" style="margin-bottom: 20px;">
				<?php foreach ( $tabs as $tab_key => $tab_title ) : ?>
					<a href="?page=spicecraft-homepage&tab=<?php echo esc_attr( $tab_key ); ?>" class="nav-tab <?php echo $tab_key === $active_tab ? 'nav-tab-active' : ''; ?>">
						<?php echo esc_html( $tab_title ); ?>
					</a>
				<?php endforeach; ?>
			</h2>

			<form method="post" action="options.php" class="spicecraft-homepage-form">
				<?php
				settings_fields( 'spicecraft_homepage_group' );
				?>
				<input type="hidden" name="_wp_http_referer" value="<?php echo esc_attr( wp_unslash( $_SERVER['REQUEST_URI'] ?? '' ) ); ?>" />

				<?php
				// TAB 1: ORDER & VISIBILITY
				if ( 'order' === $active_tab ) :
					$section_names = array(
						'hero'              => array( 'name' => __( 'Hero Banner', 'spicecraft-core' ), 'id' => '#home-hero', 'desc' => __( 'Primary brand statement, badges, CTAs and hero imagery.', 'spicecraft-core' ) ),
						'categories'        => array( 'name' => __( 'Product Categories', 'spicecraft-core' ), 'id' => '#product-categories', 'desc' => __( 'WooCommerce category grid with product counts.', 'spicecraft-core' ) ),
						'featured_products' => array( 'name' => __( 'Featured Products', 'spicecraft-core' ), 'id' => '#featured-products', 'desc' => __( 'Curated spice showcase consuming product card components.', 'spicecraft-core' ) ),
						'brand_story'       => array( 'name' => __( 'Brand Story & Heritage', 'spicecraft-core' ), 'id' => '#brand-story', 'desc' => __( 'Brand narrative, heritage stats, and sourcing legacy.', 'spicecraft-core' ) ),
						'why_choose_us'     => array( 'name' => __( 'Why Choose Us', 'spicecraft-core' ), 'id' => '#why-choose-us', 'desc' => __( 'Key FMCG manufacturing and quality differentiators.', 'spicecraft-core' ) ),
						'quality_sourcing'  => array( 'name' => __( 'Quality & Sourcing', 'spicecraft-core' ), 'id' => '#quality-sourcing', 'desc' => __( 'Purity protocols, farm origins, and laboratory testing.', 'spicecraft-core' ) ),
						'manufacturing'     => array( 'name' => __( 'Manufacturing Plant', 'spicecraft-core' ), 'id' => '#manufacturing', 'desc' => __( 'Processing facility, hygienic packaging lines, and capacity.', 'spicecraft-core' ) ),
						'certifications'    => array( 'name' => __( 'Product Certifications', 'spicecraft-core' ), 'id' => '#certifications', 'desc' => __( 'Statutory and quality food safety credentials.', 'spicecraft-core' ) ),
						'product_discovery' => array( 'name' => __( 'Product Discovery Range', 'spicecraft-core' ), 'id' => '#product-discovery', 'desc' => __( 'Interactive spice exploration by culinary category.', 'spicecraft-core' ) ),
						'recipes'           => array( 'name' => __( 'Recipes & Inspiration', 'spicecraft-core' ), 'id' => '#recipes', 'desc' => __( 'Culinary pairings and chef inspiration articles.', 'spicecraft-core' ) ),
						'testimonials'      => array( 'name' => __( 'Testimonials & Reviews', 'spicecraft-core' ), 'id' => '#testimonials', 'desc' => __( 'Executive chef and institutional buyer endorsements.', 'spicecraft-core' ) ),
						'blog'              => array( 'name' => __( 'Spice Insights & Blog', 'spicecraft-core' ), 'id' => '#latest-insights', 'desc' => __( 'Articles on harvest cycles, export trends, and spices.', 'spicecraft-core' ) ),
						'b2b_cta'           => array( 'name' => __( 'B2B & Export CTA', 'spicecraft-core' ), 'id' => '#business-enquiry', 'desc' => __( 'Dedicated wholesale, institutional, and private label desk.', 'spicecraft-core' ) ),
						'final_cta'         => array( 'name' => __( 'Final Conversion CTA', 'spicecraft-core' ), 'id' => '#contact-cta', 'desc' => __( 'Dual channel WhatsApp and direct trade desk conversion.', 'spicecraft-core' ) ),
					);

					$order_map   = $settings['sections_order'];
					$enabled_map = $settings['sections_enabled'];
					asort( $order_map, SORT_NUMERIC );
					?>
					<div class="notice notice-info inline" style="margin-bottom: 20px;">
						<p><?php esc_html_e( 'Control the visibility and display priority of each homepage section. Lower numeric values (e.g., 10, 20) render higher up the page.', 'spicecraft-core' ); ?></p>
					</div>

					<table class="wp-list-table widefat fixed striped" role="presentation">
						<thead>
							<tr>
								<th style="width: 80px;"><?php esc_html_e( 'Enabled', 'spicecraft-core' ); ?></th>
								<th style="width: 100px;"><?php esc_html_e( 'Order', 'spicecraft-core' ); ?></th>
								<th style="width: 220px;"><?php esc_html_e( 'Section Name', 'spicecraft-core' ); ?></th>
								<th style="width: 160px;"><?php esc_html_e( 'Semantic ID', 'spicecraft-core' ); ?></th>
								<th><?php esc_html_e( 'Purpose / Content Description', 'spicecraft-core' ); ?></th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ( $order_map as $sec_key => $ord_val ) :
								$sec_info = $section_names[ $sec_key ] ?? array( 'name' => $sec_key, 'id' => '#' . $sec_key, 'desc' => '' );
								$is_checked = ! empty( $enabled_map[ $sec_key ] );
								?>
								<tr>
									<td>
										<label class="screen-reader-text" for="sec_enable_<?php echo esc_attr( $sec_key ); ?>"><?php echo esc_html( $sec_info['name'] ); ?></label>
										<input type="checkbox" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[sections_enabled][<?php echo esc_attr( $sec_key ); ?>]" id="sec_enable_<?php echo esc_attr( $sec_key ); ?>" value="1" <?php checked( $is_checked ); ?> />
									</td>
									<td>
										<input type="number" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[sections_order][<?php echo esc_attr( $sec_key ); ?>]" value="<?php echo esc_attr( $ord_val ); ?>" class="small-text" step="5" min="0" />
									</td>
									<td>
										<strong><?php echo esc_html( $sec_info['name'] ); ?></strong>
									</td>
									<td>
										<code><?php echo esc_html( $sec_info['id'] ); ?></code>
									</td>
									<td style="color: #646970;">
										<?php echo esc_html( $sec_info['desc'] ); ?>
									</td>
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>

				<?php
				// TAB 2: HERO SECTION
				elseif ( 'hero' === $active_tab ) :
					$hero = $settings['hero'];
					?>
					<h3><?php esc_html_e( 'Hero Banner Configuration', 'spicecraft-core' ); ?></h3>
					<table class="form-table" role="presentation">
						<tr>
							<th scope="row"><label for="sc_hero_eyebrow"><?php esc_html_e( 'Eyebrow / Small Label', 'spicecraft-core' ); ?></label></th>
							<td>
								<input type="text" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[hero][eyebrow]" id="sc_hero_eyebrow" value="<?php echo esc_attr( $hero['eyebrow'] ); ?>" class="regular-text" placeholder="<?php esc_attr_e( 'Pure Indian Spice Heritage', 'spicecraft-core' ); ?>" />
							</td>
						</tr>
						<tr>
							<th scope="row"><label for="sc_hero_heading"><?php esc_html_e( 'Main Heading', 'spicecraft-core' ); ?></label></th>
							<td>
								<input type="text" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[hero][heading]" id="sc_hero_heading" value="<?php echo esc_attr( $hero['heading'] ); ?>" class="large-text" placeholder="<?php esc_attr_e( 'Master Crafted Spices for Culinary Excellence', 'spicecraft-core' ); ?>" />
							</td>
						</tr>
						<tr>
							<th scope="row"><label for="sc_hero_highlight"><?php esc_html_e( 'Highlighted Heading Accent', 'spicecraft-core' ); ?></label></th>
							<td>
								<input type="text" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[hero][highlight_text]" id="sc_hero_highlight" value="<?php echo esc_attr( $hero['highlight_text'] ); ?>" class="regular-text" placeholder="<?php esc_attr_e( 'Culinary Excellence', 'spicecraft-core' ); ?>" />
								<p class="description"><?php esc_html_e( 'Substring within the heading that receives warm saffron/gold gradient emphasis.', 'spicecraft-core' ); ?></p>
							</td>
						</tr>
						<tr>
							<th scope="row"><label for="sc_hero_desc"><?php esc_html_e( 'Hero Description', 'spicecraft-core' ); ?></label></th>
							<td>
								<textarea name="<?php echo esc_attr( self::OPTION_NAME ); ?>[hero][description]" id="sc_hero_desc" rows="3" class="large-text"><?php echo esc_textarea( $hero['description'] ); ?></textarea>
							</td>
						</tr>
						<tr>
							<th scope="row"><label for="sc_hero_badge"><?php esc_html_e( 'Badge / Trust Text', 'spicecraft-core' ); ?></label></th>
							<td>
								<input type="text" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[hero][badge_text]" id="sc_hero_badge" value="<?php echo esc_attr( $hero['badge_text'] ); ?>" class="regular-text" placeholder="<?php esc_attr_e( 'Export Grade &bull; 100% Pure Origin', 'spicecraft-core' ); ?>" />
							</td>
						</tr>
						<tr>
							<th scope="row"><?php esc_html_e( 'Primary CTA Button', 'spicecraft-core' ); ?></th>
							<td>
								<input type="text" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[hero][primary_cta_label]" value="<?php echo esc_attr( $hero['primary_cta_label'] ); ?>" class="regular-text" placeholder="<?php esc_attr_e( 'Explore Spice Range', 'spicecraft-core' ); ?>" style="margin-bottom: 6px;" /><br>
								<input type="url" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[hero][primary_cta_url]" value="<?php echo esc_attr( $hero['primary_cta_url'] ); ?>" class="regular-text" placeholder="<?php echo esc_attr( home_url( '/shop/' ) ); ?>" />
								<p class="description"><?php esc_html_e( 'Label and destination URL for the primary button.', 'spicecraft-core' ); ?></p>
							</td>
						</tr>
						<tr>
							<th scope="row"><?php esc_html_e( 'Secondary CTA Button', 'spicecraft-core' ); ?></th>
							<td>
								<input type="text" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[hero][secondary_cta_label]" value="<?php echo esc_attr( $hero['secondary_cta_label'] ); ?>" class="regular-text" placeholder="<?php esc_attr_e( 'Wholesale Enquiry', 'spicecraft-core' ); ?>" style="margin-bottom: 6px;" /><br>
								<input type="url" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[hero][secondary_cta_url]" value="<?php echo esc_attr( $hero['secondary_cta_url'] ); ?>" class="regular-text" placeholder="<?php echo esc_attr( home_url( '/#business-enquiry' ) ); ?>" />
							</td>
						</tr>
						<tr>
							<th scope="row"><?php esc_html_e( 'Desktop Hero Image', 'spicecraft-core' ); ?></th>
							<td>
								<?php $this->render_media_field( self::OPTION_NAME . '[hero][desktop_image_id]', $hero['desktop_image_id'] ); ?>
								<p class="description"><?php esc_html_e( 'Primary desktop landscape image (recommended: 1920x1080 or high-res showcase).', 'spicecraft-core' ); ?></p>
							</td>
						</tr>
						<tr>
							<th scope="row"><?php esc_html_e( 'Mobile Hero Image', 'spicecraft-core' ); ?></th>
							<td>
								<?php $this->render_media_field( self::OPTION_NAME . '[hero][mobile_image_id]', $hero['mobile_image_id'] ); ?>
								<p class="description"><?php esc_html_e( 'Optional vertical/square image optimized for mobile screens. Falls back to desktop image if left empty.', 'spicecraft-core' ); ?></p>
							</td>
						</tr>
						<tr>
							<th scope="row"><label for="sc_hero_alt"><?php esc_html_e( 'Image Alt Text Override', 'spicecraft-core' ); ?></label></th>
							<td>
								<input type="text" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[hero][image_alt]" id="sc_hero_alt" value="<?php echo esc_attr( $hero['image_alt'] ); ?>" class="regular-text" />
								<p class="description"><?php esc_html_e( 'Overrides default Media Library alt text for SEO accessibility.', 'spicecraft-core' ); ?></p>
							</td>
						</tr>
						<tr>
							<th scope="row"><label for="sc_hero_bg"><?php esc_html_e( 'Background Treatment', 'spicecraft-core' ); ?></label></th>
							<td>
								<select name="<?php echo esc_attr( self::OPTION_NAME ); ?>[hero][bg_treatment]" id="sc_hero_bg">
									<option value="gradient" <?php selected( $hero['bg_treatment'], 'gradient' ); ?>><?php esc_html_e( 'Deep Forest Gradient Overlay', 'spicecraft-core' ); ?></option>
									<option value="dark" <?php selected( $hero['bg_treatment'], 'dark' ); ?>><?php esc_html_e( 'Dark Solid Charcoal', 'spicecraft-core' ); ?></option>
									<option value="subtle" <?php selected( $hero['bg_treatment'], 'subtle' ); ?>><?php esc_html_e( 'Warm Saffron Tint', 'spicecraft-core' ); ?></option>
								</select>
							</td>
						</tr>
					</table>

				<?php
				// TAB 3: CATEGORIES & FEATURED PRODUCTS
				elseif ( 'catalog' === $active_tab ) :
					$cat = $settings['categories'];
					$fp  = $settings['featured_products'];
					?>
					<h3><?php esc_html_e( 'Section 2: Product Categories', 'spicecraft-core' ); ?></h3>
					<table class="form-table" role="presentation">
						<tr>
							<th scope="row"><label for="sc_cat_eyebrow"><?php esc_html_e( 'Eyebrow', 'spicecraft-core' ); ?></label></th>
							<td>
								<input type="text" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[categories][eyebrow]" id="sc_cat_eyebrow" value="<?php echo esc_attr( $cat['eyebrow'] ); ?>" class="regular-text" placeholder="<?php esc_attr_e( 'Our Product Portfolio', 'spicecraft-core' ); ?>" />
							</td>
						</tr>
						<tr>
							<th scope="row"><label for="sc_cat_heading"><?php esc_html_e( 'Heading', 'spicecraft-core' ); ?></label></th>
							<td>
								<input type="text" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[categories][heading]" id="sc_cat_heading" value="<?php echo esc_attr( $cat['heading'] ); ?>" class="regular-text" placeholder="<?php esc_attr_e( 'Explore By Category', 'spicecraft-core' ); ?>" />
							</td>
						</tr>
						<tr>
							<th scope="row"><label for="sc_cat_desc"><?php esc_html_e( 'Description', 'spicecraft-core' ); ?></label></th>
							<td>
								<textarea name="<?php echo esc_attr( self::OPTION_NAME ); ?>[categories][description]" id="sc_cat_desc" rows="2" class="large-text"><?php echo esc_textarea( $cat['description'] ); ?></textarea>
							</td>
						</tr>
						<tr>
							<th scope="row"><label for="sc_cat_mode"><?php esc_html_e( 'Display Mode', 'spicecraft-core' ); ?></label></th>
							<td>
								<select name="<?php echo esc_attr( self::OPTION_NAME ); ?>[categories][display_mode]" id="sc_cat_mode">
									<option value="all" <?php selected( $cat['display_mode'], 'all' ); ?>><?php esc_html_e( 'All Active Categories', 'spicecraft-core' ); ?></option>
									<option value="top_level" <?php selected( $cat['display_mode'], 'top_level' ); ?>><?php esc_html_e( 'Top-Level Parent Categories Only', 'spicecraft-core' ); ?></option>
									<option value="manual" <?php selected( $cat['display_mode'], 'manual' ); ?>><?php esc_html_e( 'Manually Selected Categories Below', 'spicecraft-core' ); ?></option>
								</select>
							</td>
						</tr>
						<tr>
							<th scope="row"><?php esc_html_e( 'Select Categories (for manual mode)', 'spicecraft-core' ); ?></th>
							<td>
								<?php $this->render_taxonomy_multiselect( 'product_cat', self::OPTION_NAME . '[categories][selected_ids]', $cat['selected_ids'] ); ?>
								<p class="description"><?php esc_html_e( 'Consumes WooCommerce product categories. Category images and descriptions are managed under Products &rarr; Categories.', 'spicecraft-core' ); ?></p>
							</td>
						</tr>
						<tr>
							<th scope="row"><label for="sc_cat_limit"><?php esc_html_e( 'Maximum Categories to Show', 'spicecraft-core' ); ?></label></th>
							<td>
								<input type="number" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[categories][limit]" id="sc_cat_limit" value="<?php echo esc_attr( $cat['limit'] ); ?>" class="small-text" min="1" max="24" />
							</td>
						</tr>
					</table>

					<hr style="margin: 30px 0; border: 0; border-top: 1px solid #dcdcde;" />

					<h3><?php esc_html_e( 'Section 3: Featured Products Showcase', 'spicecraft-core' ); ?></h3>
					<table class="form-table" role="presentation">
						<tr>
							<th scope="row"><label for="sc_fp_eyebrow"><?php esc_html_e( 'Eyebrow', 'spicecraft-core' ); ?></label></th>
							<td>
								<input type="text" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[featured_products][eyebrow]" id="sc_fp_eyebrow" value="<?php echo esc_attr( $fp['eyebrow'] ); ?>" class="regular-text" placeholder="<?php esc_attr_e( 'Signature Selections', 'spicecraft-core' ); ?>" />
							</td>
						</tr>
						<tr>
							<th scope="row"><label for="sc_fp_heading"><?php esc_html_e( 'Heading', 'spicecraft-core' ); ?></label></th>
							<td>
								<input type="text" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[featured_products][heading]" id="sc_fp_heading" value="<?php echo esc_attr( $fp['heading'] ); ?>" class="regular-text" placeholder="<?php esc_attr_e( 'Featured Spice Catalog', 'spicecraft-core' ); ?>" />
							</td>
						</tr>
						<tr>
							<th scope="row"><label for="sc_fp_desc"><?php esc_html_e( 'Description', 'spicecraft-core' ); ?></label></th>
							<td>
								<textarea name="<?php echo esc_attr( self::OPTION_NAME ); ?>[featured_products][description]" id="sc_fp_desc" rows="2" class="large-text"><?php echo esc_textarea( $fp['description'] ); ?></textarea>
							</td>
						</tr>
						<tr>
							<th scope="row"><label for="sc_fp_source"><?php esc_html_e( 'Product Source', 'spicecraft-core' ); ?></label></th>
							<td>
								<select name="<?php echo esc_attr( self::OPTION_NAME ); ?>[featured_products][source]" id="sc_fp_source">
									<option value="featured" <?php selected( $fp['source'], 'featured' ); ?>><?php esc_html_e( 'WooCommerce Featured Products (Star marked)', 'spicecraft-core' ); ?></option>
									<option value="manual" <?php selected( $fp['source'], 'manual' ); ?>><?php esc_html_e( 'Manually Selected Products Below', 'spicecraft-core' ); ?></option>
									<option value="latest" <?php selected( $fp['source'], 'latest' ); ?>><?php esc_html_e( 'Latest Published Products', 'spicecraft-core' ); ?></option>
								</select>
							</td>
						</tr>
						<tr>
							<th scope="row"><?php esc_html_e( 'Manual Product Selection', 'spicecraft-core' ); ?></th>
							<td>
								<?php $this->render_post_multiselect( 'product', self::OPTION_NAME . '[featured_products][selected_ids]', $fp['selected_ids'] ); ?>
								<p class="description"><?php esc_html_e( 'Select products to highlight. Reuses the Phase 1 catalog card component with inquiry triggers.', 'spicecraft-core' ); ?></p>
							</td>
						</tr>
						<tr>
							<th scope="row"><label for="sc_fp_limit"><?php esc_html_e( 'Display Count Limit', 'spicecraft-core' ); ?></label></th>
							<td>
								<input type="number" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[featured_products][limit]" id="sc_fp_limit" value="<?php echo esc_attr( $fp['limit'] ); ?>" class="small-text" min="1" max="24" />
							</td>
						</tr>
						<tr>
							<th scope="row"><?php esc_html_e( 'Section CTA Button', 'spicecraft-core' ); ?></th>
							<td>
								<input type="text" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[featured_products][cta_label]" value="<?php echo esc_attr( $fp['cta_label'] ); ?>" class="regular-text" placeholder="<?php esc_attr_e( 'View Complete Catalog', 'spicecraft-core' ); ?>" style="margin-bottom: 6px;" /><br>
								<input type="url" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[featured_products][cta_url]" value="<?php echo esc_attr( $fp['cta_url'] ); ?>" class="regular-text" placeholder="<?php echo esc_attr( home_url( '/shop/' ) ); ?>" />
							</td>
						</tr>
					</table>

				<?php
				// TAB 4: BRAND STORY & WHY CHOOSE US
				elseif ( 'story' === $active_tab ) :
					$bs  = $settings['brand_story'];
					$wcu = $settings['why_choose_us'];
					?>
					<h3><?php esc_html_e( 'Section 4: Brand Story & Heritage', 'spicecraft-core' ); ?></h3>
					<table class="form-table" role="presentation">
						<tr>
							<th scope="row"><label for="sc_bs_eyebrow"><?php esc_html_e( 'Eyebrow', 'spicecraft-core' ); ?></label></th>
							<td>
								<input type="text" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[brand_story][eyebrow]" id="sc_bs_eyebrow" value="<?php echo esc_attr( $bs['eyebrow'] ); ?>" class="regular-text" placeholder="<?php esc_attr_e( 'Generations of Purity', 'spicecraft-core' ); ?>" />
							</td>
						</tr>
						<tr>
							<th scope="row"><label for="sc_bs_heading"><?php esc_html_e( 'Heading', 'spicecraft-core' ); ?></label></th>
							<td>
								<input type="text" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[brand_story][heading]" id="sc_bs_heading" value="<?php echo esc_attr( $bs['heading'] ); ?>" class="large-text" placeholder="<?php esc_attr_e( 'Rooted in Tradition, Perfected by Science', 'spicecraft-core' ); ?>" />
							</td>
						</tr>
						<tr>
							<th scope="row"><label for="sc_bs_desc"><?php esc_html_e( 'Story Narrative (HTML permitted)', 'spicecraft-core' ); ?></label></th>
							<td>
								<textarea name="<?php echo esc_attr( self::OPTION_NAME ); ?>[brand_story][description]" id="sc_bs_desc" rows="5" class="large-text"><?php echo esc_textarea( $bs['description'] ); ?></textarea>
							</td>
						</tr>
						<tr>
							<th scope="row"><?php esc_html_e( 'Heritage Stat Highlight', 'spicecraft-core' ); ?></th>
							<td>
								<div style="display: flex; gap: 12px; flex-wrap: wrap;">
									<div>
										<label style="display:block; font-size:12px; color:#646970;"><?php esc_html_e( 'Value / Number (e.g. 1984 or 35+)', 'spicecraft-core' ); ?></label>
										<input type="text" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[brand_story][stat_value]" value="<?php echo esc_attr( $bs['stat_value'] ); ?>" class="regular-text" />
									</div>
									<div>
										<label style="display:block; font-size:12px; color:#646970;"><?php esc_html_e( 'Label (e.g. Years of Heritage)', 'spicecraft-core' ); ?></label>
										<input type="text" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[brand_story][stat_label]" value="<?php echo esc_attr( $bs['stat_label'] ); ?>" class="regular-text" />
									</div>
								</div>
								<p class="description"><?php esc_html_e( 'Only displayed if both value and label are provided by admin. No fake defaults.', 'spicecraft-core' ); ?></p>
							</td>
						</tr>
						<tr>
							<th scope="row"><?php esc_html_e( 'Primary Heritage Image', 'spicecraft-core' ); ?></th>
							<td>
								<?php $this->render_media_field( self::OPTION_NAME . '[brand_story][primary_image_id]', $bs['primary_image_id'] ); ?>
							</td>
						</tr>
						<tr>
							<th scope="row"><?php esc_html_e( 'Secondary / Accent Image', 'spicecraft-core' ); ?></th>
							<td>
								<?php $this->render_media_field( self::OPTION_NAME . '[brand_story][secondary_image_id]', $bs['secondary_image_id'] ); ?>
							</td>
						</tr>
						<tr>
							<th scope="row"><?php esc_html_e( 'Learn More CTA Button', 'spicecraft-core' ); ?></th>
							<td>
								<input type="text" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[brand_story][cta_label]" value="<?php echo esc_attr( $bs['cta_label'] ); ?>" class="regular-text" placeholder="<?php esc_attr_e( 'Our Heritage & Philosophy', 'spicecraft-core' ); ?>" style="margin-bottom: 6px;" /><br>
								<input type="url" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[brand_story][cta_url]" value="<?php echo esc_attr( $bs['cta_url'] ); ?>" class="regular-text" placeholder="<?php echo esc_attr( home_url( '/about/' ) ); ?>" />
							</td>
						</tr>
					</table>

					<hr style="margin: 30px 0; border: 0; border-top: 1px solid #dcdcde;" />

					<h3><?php esc_html_e( 'Section 5: Why Choose Us (Repeatable Differentiators)', 'spicecraft-core' ); ?></h3>
					<table class="form-table" role="presentation">
						<tr>
							<th scope="row"><label for="sc_wcu_eyebrow"><?php esc_html_e( 'Eyebrow', 'spicecraft-core' ); ?></label></th>
							<td>
								<input type="text" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[why_choose_us][eyebrow]" id="sc_wcu_eyebrow" value="<?php echo esc_attr( $wcu['eyebrow'] ); ?>" class="regular-text" placeholder="<?php esc_attr_e( 'The SpiceCraft Advantage', 'spicecraft-core' ); ?>" />
							</td>
						</tr>
						<tr>
							<th scope="row"><label for="sc_wcu_heading"><?php esc_html_e( 'Heading', 'spicecraft-core' ); ?></label></th>
							<td>
								<input type="text" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[why_choose_us][heading]" id="sc_wcu_heading" value="<?php echo esc_attr( $wcu['heading'] ); ?>" class="regular-text" placeholder="<?php esc_attr_e( 'Why Leading Chefs & Importers Trust Us', 'spicecraft-core' ); ?>" />
							</td>
						</tr>
						<tr>
							<th scope="row"><label for="sc_wcu_desc"><?php esc_html_e( 'Description', 'spicecraft-core' ); ?></label></th>
							<td>
								<textarea name="<?php echo esc_attr( self::OPTION_NAME ); ?>[why_choose_us][description]" id="sc_wcu_desc" rows="2" class="large-text"><?php echo esc_textarea( $wcu['description'] ); ?></textarea>
							</td>
						</tr>
						<tr>
							<th scope="row"><?php esc_html_e( 'Differentiator Items (3 to 6)', 'spicecraft-core' ); ?></th>
							<td>
								<div id="sc-wcu-items-container" class="sc-repeatable-list">
									<?php
									$items = ! empty( $wcu['items'] ) ? $wcu['items'] : array();
									foreach ( $items as $idx => $item ) :
										?>
										<div class="sc-repeatable-row sc-card" style="padding: 12px; margin-bottom: 8px; background: #f9f9f9; border: 1px solid #ccd0d4; border-radius: 4px;">
											<div style="display: flex; gap: 10px; width: 100%; align-items: center; margin-bottom: 8px;">
												<input type="text" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[why_choose_us][items][<?php echo esc_attr( $idx ); ?>][icon]" value="<?php echo esc_attr( $item['icon'] ?? '' ); ?>" placeholder="Icon (e.g. leaf, shield, award)" style="width: 140px;" />
												<input type="text" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[why_choose_us][items][<?php echo esc_attr( $idx ); ?>][title]" value="<?php echo esc_attr( $item['title'] ?? '' ); ?>" placeholder="Feature Title" class="regular-text" style="flex-grow: 1;" />
												<input type="number" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[why_choose_us][items][<?php echo esc_attr( $idx ); ?>][order]" value="<?php echo esc_attr( $item['order'] ?? 10 ); ?>" placeholder="Order" style="width: 70px;" />
												<button type="button" class="button sc-remove-row-btn">&times;</button>
											</div>
											<div>
												<textarea name="<?php echo esc_attr( self::OPTION_NAME ); ?>[why_choose_us][items][<?php echo esc_attr( $idx ); ?>][description]" placeholder="Short explanation of differentiator" rows="2" style="width: 100%;"><?php echo esc_textarea( $item['description'] ?? '' ); ?></textarea>
											</div>
										</div>
									<?php endforeach; ?>
								</div>
								<button type="button" class="button button-secondary" id="sc-add-wcu-item-btn">
									+ <?php esc_html_e( 'Add Differentiator Item', 'spicecraft-core' ); ?>
								</button>
							</td>
						</tr>
					</table>

				<?php
				// TAB 5: QUALITY & SOURCING + MANUFACTURING
				elseif ( 'infrastructure' === $active_tab ) :
					$qs  = $settings['quality_sourcing'];
					$mfg = $settings['manufacturing'];
					?>
					<h3><?php esc_html_e( 'Section 6: Quality & Sourcing Architecture', 'spicecraft-core' ); ?></h3>
					<table class="form-table" role="presentation">
						<tr>
							<th scope="row"><label for="sc_qs_eyebrow"><?php esc_html_e( 'Eyebrow', 'spicecraft-core' ); ?></label></th>
							<td>
								<input type="text" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[quality_sourcing][eyebrow]" id="sc_qs_eyebrow" value="<?php echo esc_attr( $qs['eyebrow'] ); ?>" class="regular-text" placeholder="<?php esc_attr_e( 'Farm To Table Purity', 'spicecraft-core' ); ?>" />
							</td>
						</tr>
						<tr>
							<th scope="row"><label for="sc_qs_heading"><?php esc_html_e( 'Heading', 'spicecraft-core' ); ?></label></th>
							<td>
								<input type="text" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[quality_sourcing][heading]" id="sc_qs_heading" value="<?php echo esc_attr( $qs['heading'] ); ?>" class="large-text" placeholder="<?php esc_attr_e( 'Uncompromising Sourcing & Quality Control', 'spicecraft-core' ); ?>" />
							</td>
						</tr>
						<tr>
							<th scope="row"><label for="sc_qs_desc"><?php esc_html_e( 'Description (HTML permitted)', 'spicecraft-core' ); ?></label></th>
							<td>
								<textarea name="<?php echo esc_attr( self::OPTION_NAME ); ?>[quality_sourcing][description]" id="sc_qs_desc" rows="4" class="large-text"><?php echo esc_textarea( $qs['description'] ); ?></textarea>
							</td>
						</tr>
						<tr>
							<th scope="row"><?php esc_html_e( 'Sourcing Images', 'spicecraft-core' ); ?></th>
							<td>
								<div style="display: flex; gap: 24px; flex-wrap: wrap;">
									<div>
										<label style="display:block; font-weight:600; margin-bottom:4px;"><?php esc_html_e( 'Main Laboratory / Origin Image', 'spicecraft-core' ); ?></label>
										<?php $this->render_media_field( self::OPTION_NAME . '[quality_sourcing][main_image_id]', $qs['main_image_id'] ); ?>
									</div>
									<div>
										<label style="display:block; font-weight:600; margin-bottom:4px;"><?php esc_html_e( 'Supporting Image', 'spicecraft-core' ); ?></label>
										<?php $this->render_media_field( self::OPTION_NAME . '[quality_sourcing][support_image_id]', $qs['support_image_id'] ); ?>
									</div>
								</div>
							</td>
						</tr>
						<tr>
							<th scope="row"><?php esc_html_e( 'Quality Control Key Points', 'spicecraft-core' ); ?></th>
							<td>
								<div id="sc-qs-points-container" class="sc-repeatable-list">
									<?php
									$points = ! empty( $qs['points'] ) ? $qs['points'] : array();
									foreach ( $points as $idx => $pt ) :
										?>
										<div class="sc-repeatable-row sc-card" style="padding: 10px; margin-bottom: 6px; background: #f9f9f9; border: 1px solid #ccd0d4; border-radius: 4px;">
											<div style="display: flex; gap: 8px; width: 100%; margin-bottom: 6px;">
												<input type="text" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[quality_sourcing][points][<?php echo esc_attr( $idx ); ?>][title]" value="<?php echo esc_attr( $pt['title'] ?? '' ); ?>" placeholder="Point Title (e.g. Origin Traceability)" class="regular-text" style="flex-grow: 1;" />
												<button type="button" class="button sc-remove-row-btn">&times;</button>
											</div>
											<textarea name="<?php echo esc_attr( self::OPTION_NAME ); ?>[quality_sourcing][points][<?php echo esc_attr( $idx ); ?>][text]" placeholder="Short point details" rows="2" style="width: 100%;"><?php echo esc_textarea( $pt['text'] ?? '' ); ?></textarea>
										</div>
									<?php endforeach; ?>
								</div>
								<button type="button" class="button button-secondary" id="sc-add-qs-point-btn">
									+ <?php esc_html_e( 'Add Quality Point', 'spicecraft-core' ); ?>
								</button>
							</td>
						</tr>
						<tr>
							<th scope="row"><?php esc_html_e( 'CTA Button', 'spicecraft-core' ); ?></th>
							<td>
								<input type="text" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[quality_sourcing][cta_label]" value="<?php echo esc_attr( $qs['cta_label'] ); ?>" class="regular-text" placeholder="<?php esc_attr_e( 'Learn About Our Standards', 'spicecraft-core' ); ?>" style="margin-bottom: 6px;" /><br>
								<input type="url" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[quality_sourcing][cta_url]" value="<?php echo esc_attr( $qs['cta_url'] ); ?>" class="regular-text" />
							</td>
						</tr>
					</table>

					<hr style="margin: 30px 0; border: 0; border-top: 1px solid #dcdcde;" />

					<h3><?php esc_html_e( 'Section 7: Manufacturing Plant & Infrastructure', 'spicecraft-core' ); ?></h3>
					<table class="form-table" role="presentation">
						<tr>
							<th scope="row"><label for="sc_mfg_eyebrow"><?php esc_html_e( 'Eyebrow', 'spicecraft-core' ); ?></label></th>
							<td>
								<input type="text" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[manufacturing][eyebrow]" id="sc_mfg_eyebrow" value="<?php echo esc_attr( $mfg['eyebrow'] ); ?>" class="regular-text" placeholder="<?php esc_attr_e( 'Infrastructure & Capacity', 'spicecraft-core' ); ?>" />
							</td>
						</tr>
						<tr>
							<th scope="row"><label for="sc_mfg_heading"><?php esc_html_e( 'Heading', 'spicecraft-core' ); ?></label></th>
							<td>
								<input type="text" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[manufacturing][heading]" id="sc_mfg_heading" value="<?php echo esc_attr( $mfg['heading'] ); ?>" class="large-text" placeholder="<?php esc_attr_e( 'Advanced Milling, Cryogenic Grinding & Processing', 'spicecraft-core' ); ?>" />
							</td>
						</tr>
						<tr>
							<th scope="row"><label for="sc_mfg_desc"><?php esc_html_e( 'Description (HTML permitted)', 'spicecraft-core' ); ?></label></th>
							<td>
								<textarea name="<?php echo esc_attr( self::OPTION_NAME ); ?>[manufacturing][description]" id="sc_mfg_desc" rows="4" class="large-text"><?php echo esc_textarea( $mfg['description'] ); ?></textarea>
							</td>
						</tr>
						<tr>
							<th scope="row"><?php esc_html_e( 'Plant Media', 'spicecraft-core' ); ?></th>
							<td>
								<div style="display: flex; gap: 24px; flex-wrap: wrap;">
									<div>
										<label style="display:block; font-weight:600; margin-bottom:4px;"><?php esc_html_e( 'Primary Facility Photo', 'spicecraft-core' ); ?></label>
										<?php $this->render_media_field( self::OPTION_NAME . '[manufacturing][main_image_id]', $mfg['main_image_id'] ); ?>
									</div>
									<div>
										<label style="display:block; font-weight:600; margin-bottom:4px;"><?php esc_html_e( 'Secondary Facility Photo', 'spicecraft-core' ); ?></label>
										<?php $this->render_media_field( self::OPTION_NAME . '[manufacturing][support_image_id]', $mfg['support_image_id'] ); ?>
									</div>
								</div>
							</td>
						</tr>
						<tr>
							<th scope="row"><label for="sc_mfg_video"><?php esc_html_e( 'Facility Video URL', 'spicecraft-core' ); ?></label></th>
							<td>
								<input type="url" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[manufacturing][video_url]" id="sc_mfg_video" value="<?php echo esc_attr( $mfg['video_url'] ); ?>" class="large-text" placeholder="https://youtube.com/... or https://vimeo.com/..." />
								<p class="description"><?php esc_html_e( 'Optional video showcase link (YouTube, Vimeo, or MP4).', 'spicecraft-core' ); ?></p>
							</td>
						</tr>
						<tr>
							<th scope="row"><?php esc_html_e( 'Facility Metrics & Capability Stats', 'spicecraft-core' ); ?></th>
							<td>
								<div id="sc-mfg-stats-container" class="sc-repeatable-list">
									<?php
									$stats = ! empty( $mfg['stats'] ) ? $mfg['stats'] : array();
									foreach ( $stats as $idx => $st ) :
										?>
										<div class="sc-repeatable-row" style="display: flex; gap: 8px; margin-bottom: 6px;">
											<input type="text" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[manufacturing][stats][<?php echo esc_attr( $idx ); ?>][label]" value="<?php echo esc_attr( $st['label'] ?? '' ); ?>" placeholder="Metric Label (e.g. Processing Lines)" class="regular-text" />
											<input type="text" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[manufacturing][stats][<?php echo esc_attr( $idx ); ?>][value]" value="<?php echo esc_attr( $st['value'] ?? '' ); ?>" placeholder="Value (e.g. 4 Dedicated)" class="regular-text" />
											<button type="button" class="button sc-remove-row-btn">&times;</button>
										</div>
									<?php endforeach; ?>
								</div>
								<button type="button" class="button button-secondary" id="sc-add-mfg-stat-btn">
									+ <?php esc_html_e( 'Add Facility Metric', 'spicecraft-core' ); ?>
								</button>
								<p class="description"><?php esc_html_e( 'Actual metrics entered by administrator. Do not enter fabricated claims.', 'spicecraft-core' ); ?></p>
							</td>
						</tr>
						<tr>
							<th scope="row"><?php esc_html_e( 'CTA Button', 'spicecraft-core' ); ?></th>
							<td>
								<input type="text" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[manufacturing][cta_label]" value="<?php echo esc_attr( $mfg['cta_label'] ); ?>" class="regular-text" placeholder="<?php esc_attr_e( 'Tour Our Facility', 'spicecraft-core' ); ?>" style="margin-bottom: 6px;" /><br>
								<input type="url" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[manufacturing][cta_url]" value="<?php echo esc_attr( $mfg['cta_url'] ); ?>" class="regular-text" />
							</td>
						</tr>
					</table>

				<?php
				// TAB 6: CERTIFICATIONS & TESTIMONIALS
				elseif ( 'trust' === $active_tab ) :
					$cert = $settings['certifications'];
					$tst  = $settings['testimonials'];
					?>
					<h3><?php esc_html_e( 'Section 8: Product Certifications & Accreditations', 'spicecraft-core' ); ?></h3>
					<div class="notice notice-info inline">
						<p><?php esc_html_e( 'Reuses the Phase 1 `spicecraft_certification` taxonomy. If no certifications have been added to the system, this section hides cleanly.', 'spicecraft-core' ); ?> <a href="<?php echo esc_url( admin_url( 'edit-tags.php?taxonomy=spicecraft_certification&post_type=product' ) ); ?>"><?php esc_html_e( 'Manage Certifications &rarr;', 'spicecraft-core' ); ?></a></p>
					</div>
					<table class="form-table" role="presentation">
						<tr>
							<th scope="row"><label for="sc_cert_eyebrow"><?php esc_html_e( 'Eyebrow', 'spicecraft-core' ); ?></label></th>
							<td>
								<input type="text" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[certifications][eyebrow]" id="sc_cert_eyebrow" value="<?php echo esc_attr( $cert['eyebrow'] ); ?>" class="regular-text" placeholder="<?php esc_attr_e( 'Statutory & Quality Compliance', 'spicecraft-core' ); ?>" />
							</td>
						</tr>
						<tr>
							<th scope="row"><label for="sc_cert_heading"><?php esc_html_e( 'Heading', 'spicecraft-core' ); ?></label></th>
							<td>
								<input type="text" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[certifications][heading]" id="sc_cert_heading" value="<?php echo esc_attr( $cert['heading'] ); ?>" class="regular-text" placeholder="<?php esc_attr_e( 'Global Food Safety Standards', 'spicecraft-core' ); ?>" />
							</td>
						</tr>
						<tr>
							<th scope="row"><label for="sc_cert_desc"><?php esc_html_e( 'Description', 'spicecraft-core' ); ?></label></th>
							<td>
								<textarea name="<?php echo esc_attr( self::OPTION_NAME ); ?>[certifications][description]" id="sc_cert_desc" rows="2" class="large-text"><?php echo esc_textarea( $cert['description'] ); ?></textarea>
							</td>
						</tr>
						<tr>
							<th scope="row"><?php esc_html_e( 'Certifications to Display', 'spicecraft-core' ); ?></th>
							<td>
								<?php $this->render_taxonomy_multiselect( 'spicecraft_certification', self::OPTION_NAME . '[certifications][selected_ids]', $cert['selected_ids'] ); ?>
								<p class="description"><?php esc_html_e( 'Leave empty to automatically display all registered certifications up to the limit.', 'spicecraft-core' ); ?></p>
							</td>
						</tr>
						<tr>
							<th scope="row"><label for="sc_cert_limit"><?php esc_html_e( 'Display Limit', 'spicecraft-core' ); ?></label></th>
							<td>
								<input type="number" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[certifications][limit]" id="sc_cert_limit" value="<?php echo esc_attr( $cert['limit'] ); ?>" class="small-text" min="1" max="20" />
							</td>
						</tr>
					</table>

					<hr style="margin: 30px 0; border: 0; border-top: 1px solid #dcdcde;" />

					<h3><?php esc_html_e( 'Section 11: Testimonials & Endorsements', 'spicecraft-core' ); ?></h3>
					<div class="notice notice-info inline">
						<p><?php esc_html_e( 'Testimonials are managed via the reusable Custom Post Type. If no testimonials are published, this section hides cleanly.', 'spicecraft-core' ); ?> <a href="<?php echo esc_url( admin_url( 'edit.php?post_type=spicecraft_testimonial' ) ); ?>"><?php esc_html_e( 'Manage Testimonials &rarr;', 'spicecraft-core' ); ?></a></p>
					</div>
					<table class="form-table" role="presentation">
						<tr>
							<th scope="row"><label for="sc_tst_eyebrow"><?php esc_html_e( 'Eyebrow', 'spicecraft-core' ); ?></label></th>
							<td>
								<input type="text" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[testimonials][eyebrow]" id="sc_tst_eyebrow" value="<?php echo esc_attr( $tst['eyebrow'] ); ?>" class="regular-text" placeholder="<?php esc_attr_e( 'Client Endorsements', 'spicecraft-core' ); ?>" />
							</td>
						</tr>
						<tr>
							<th scope="row"><label for="sc_tst_heading"><?php esc_html_e( 'Heading', 'spicecraft-core' ); ?></label></th>
							<td>
								<input type="text" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[testimonials][heading]" id="sc_tst_heading" value="<?php echo esc_attr( $tst['heading'] ); ?>" class="regular-text" placeholder="<?php esc_attr_e( 'What Culinary Professionals Say', 'spicecraft-core' ); ?>" />
							</td>
						</tr>
						<tr>
							<th scope="row"><label for="sc_tst_desc"><?php esc_html_e( 'Description', 'spicecraft-core' ); ?></label></th>
							<td>
								<textarea name="<?php echo esc_attr( self::OPTION_NAME ); ?>[testimonials][description]" id="sc_tst_desc" rows="2" class="large-text"><?php echo esc_textarea( $tst['description'] ); ?></textarea>
							</td>
						</tr>
						<tr>
							<th scope="row"><?php esc_html_e( 'Specific Testimonials', 'spicecraft-core' ); ?></th>
							<td>
								<?php $this->render_post_multiselect( 'spicecraft_testimonial', self::OPTION_NAME . '[testimonials][selected_ids]', $tst['selected_ids'] ); ?>
								<p class="description"><?php esc_html_e( 'Leave unselected to automatically show published testimonials in display order.', 'spicecraft-core' ); ?></p>
							</td>
						</tr>
						<tr>
							<th scope="row"><label for="sc_tst_limit"><?php esc_html_e( 'Display Limit', 'spicecraft-core' ); ?></label></th>
							<td>
								<input type="number" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[testimonials][limit]" id="sc_tst_limit" value="<?php echo esc_attr( $tst['limit'] ); ?>" class="small-text" min="1" max="20" />
							</td>
						</tr>
					</table>

				<?php
				// TAB 7: DISCOVERY, RECIPES & BLOG
				elseif ( 'discovery' === $active_tab ) :
					$pd  = $settings['product_discovery'];
					$rc  = $settings['recipes'];
					$blg = $settings['blog'];
					?>
					<h3><?php esc_html_e( 'Section 9: Product Discovery / Explore Range', 'spicecraft-core' ); ?></h3>
					<table class="form-table" role="presentation">
						<tr>
							<th scope="row"><label for="sc_pd_eyebrow"><?php esc_html_e( 'Eyebrow', 'spicecraft-core' ); ?></label></th>
							<td>
								<input type="text" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[product_discovery][eyebrow]" id="sc_pd_eyebrow" value="<?php echo esc_attr( $pd['eyebrow'] ); ?>" class="regular-text" placeholder="<?php esc_attr_e( 'Culinary Exploration', 'spicecraft-core' ); ?>" />
							</td>
						</tr>
						<tr>
							<th scope="row"><label for="sc_pd_heading"><?php esc_html_e( 'Heading', 'spicecraft-core' ); ?></label></th>
							<td>
								<input type="text" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[product_discovery][heading]" id="sc_pd_heading" value="<?php echo esc_attr( $pd['heading'] ); ?>" class="regular-text" placeholder="<?php esc_attr_e( 'Find the Perfect Spice for Every Dish', 'spicecraft-core' ); ?>" />
							</td>
						</tr>
						<tr>
							<th scope="row"><label for="sc_pd_desc"><?php esc_html_e( 'Description', 'spicecraft-core' ); ?></label></th>
							<td>
								<textarea name="<?php echo esc_attr( self::OPTION_NAME ); ?>[product_discovery][description]" id="sc_pd_desc" rows="2" class="large-text"><?php echo esc_textarea( $pd['description'] ); ?></textarea>
							</td>
						</tr>
						<tr>
							<th scope="row"><?php esc_html_e( 'Highlighted Categories', 'spicecraft-core' ); ?></th>
							<td>
								<?php $this->render_taxonomy_multiselect( 'product_cat', self::OPTION_NAME . '[product_discovery][category_ids]', $pd['category_ids'] ); ?>
							</td>
						</tr>
						<tr>
							<th scope="row"><?php esc_html_e( 'CTA Button', 'spicecraft-core' ); ?></th>
							<td>
								<input type="text" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[product_discovery][cta_label]" value="<?php echo esc_attr( $pd['cta_label'] ); ?>" class="regular-text" placeholder="<?php esc_attr_e( 'Explore Entire Range', 'spicecraft-core' ); ?>" style="margin-bottom: 6px;" /><br>
								<input type="url" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[product_discovery][cta_url]" value="<?php echo esc_attr( $pd['cta_url'] ); ?>" class="regular-text" placeholder="<?php echo esc_attr( home_url( '/shop/' ) ); ?>" />
							</td>
						</tr>
					</table>

					<hr style="margin: 30px 0; border: 0; border-top: 1px solid #dcdcde;" />

					<h3><?php esc_html_e( 'Section 10: Recipes & Culinary Inspiration', 'spicecraft-core' ); ?></h3>
					<div class="notice notice-info inline">
						<p><?php esc_html_e( 'Extensible foundation: Currently consumes WordPress Posts from a chosen category. Full Recipe CPT will be introduced in subsequent phases without breaking this architecture.', 'spicecraft-core' ); ?></p>
					</div>
					<table class="form-table" role="presentation">
						<tr>
							<th scope="row"><label for="sc_rc_eyebrow"><?php esc_html_e( 'Eyebrow', 'spicecraft-core' ); ?></label></th>
							<td>
								<input type="text" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[recipes][eyebrow]" id="sc_rc_eyebrow" value="<?php echo esc_attr( $rc['eyebrow'] ); ?>" class="regular-text" placeholder="<?php esc_attr_e( 'From Our Test Kitchen', 'spicecraft-core' ); ?>" />
							</td>
						</tr>
						<tr>
							<th scope="row"><label for="sc_rc_heading"><?php esc_html_e( 'Heading', 'spicecraft-core' ); ?></label></th>
							<td>
								<input type="text" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[recipes][heading]" id="sc_rc_heading" value="<?php echo esc_attr( $rc['heading'] ); ?>" class="regular-text" placeholder="<?php esc_attr_e( 'Recipes & Spice Pairings', 'spicecraft-core' ); ?>" />
							</td>
						</tr>
						<tr>
							<th scope="row"><label for="sc_rc_desc"><?php esc_html_e( 'Description', 'spicecraft-core' ); ?></label></th>
							<td>
								<textarea name="<?php echo esc_attr( self::OPTION_NAME ); ?>[recipes][description]" id="sc_rc_desc" rows="2" class="large-text"><?php echo esc_textarea( $rc['description'] ); ?></textarea>
							</td>
						</tr>
						<tr>
							<th scope="row"><label for="sc_rc_cat"><?php esc_html_e( 'Post Category Source', 'spicecraft-core' ); ?></label></th>
							<td>
								<?php
								wp_dropdown_categories( array(
									'show_option_all' => __( '— All Post Categories —', 'spicecraft-core' ),
									'name'            => self::OPTION_NAME . '[recipes][category_id]',
									'id'              => 'sc_rc_cat',
									'selected'        => $rc['category_id'],
									'hierarchical'    => true,
									'hide_empty'      => false,
								) );
								?>
							</td>
						</tr>
						<tr>
							<th scope="row"><label for="sc_rc_limit"><?php esc_html_e( 'Display Limit', 'spicecraft-core' ); ?></label></th>
							<td>
								<input type="number" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[recipes][limit]" id="sc_rc_limit" value="<?php echo esc_attr( $rc['limit'] ); ?>" class="small-text" min="1" max="12" />
							</td>
						</tr>
						<tr>
							<th scope="row"><?php esc_html_e( 'CTA Button', 'spicecraft-core' ); ?></th>
							<td>
								<input type="text" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[recipes][cta_label]" value="<?php echo esc_attr( $rc['cta_label'] ); ?>" class="regular-text" placeholder="<?php esc_attr_e( 'View All Recipes', 'spicecraft-core' ); ?>" style="margin-bottom: 6px;" /><br>
								<input type="url" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[recipes][cta_url]" value="<?php echo esc_attr( $rc['cta_url'] ); ?>" class="regular-text" />
							</td>
						</tr>
					</table>

					<hr style="margin: 30px 0; border: 0; border-top: 1px solid #dcdcde;" />

					<h3><?php esc_html_e( 'Section 12: Blog & Industry Insights', 'spicecraft-core' ); ?></h3>
					<table class="form-table" role="presentation">
						<tr>
							<th scope="row"><label for="sc_blg_eyebrow"><?php esc_html_e( 'Eyebrow', 'spicecraft-core' ); ?></label></th>
							<td>
								<input type="text" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[blog][eyebrow]" id="sc_blg_eyebrow" value="<?php echo esc_attr( $blg['eyebrow'] ); ?>" class="regular-text" placeholder="<?php esc_attr_e( 'Knowledge & Market Trends', 'spicecraft-core' ); ?>" />
							</td>
						</tr>
						<tr>
							<th scope="row"><label for="sc_blg_heading"><?php esc_html_e( 'Heading', 'spicecraft-core' ); ?></label></th>
							<td>
								<input type="text" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[blog][heading]" id="sc_blg_heading" value="<?php echo esc_attr( $blg['heading'] ); ?>" class="regular-text" placeholder="<?php esc_attr_e( 'Latest Articles & Updates', 'spicecraft-core' ); ?>" />
							</td>
						</tr>
						<tr>
							<th scope="row"><label for="sc_blg_desc"><?php esc_html_e( 'Description', 'spicecraft-core' ); ?></label></th>
							<td>
								<textarea name="<?php echo esc_attr( self::OPTION_NAME ); ?>[blog][description]" id="sc_blg_desc" rows="2" class="large-text"><?php echo esc_textarea( $blg['description'] ); ?></textarea>
							</td>
						</tr>
						<tr>
							<th scope="row"><label for="sc_blg_source"><?php esc_html_e( 'Article Source', 'spicecraft-core' ); ?></label></th>
							<td>
								<select name="<?php echo esc_attr( self::OPTION_NAME ); ?>[blog][source]" id="sc_blg_source">
									<option value="latest" <?php selected( $blg['source'], 'latest' ); ?>><?php esc_html_e( 'Latest Published Posts', 'spicecraft-core' ); ?></option>
									<option value="category" <?php selected( $blg['source'], 'category' ); ?>><?php esc_html_e( 'Filter By Post Category', 'spicecraft-core' ); ?></option>
									<option value="manual" <?php selected( $blg['source'], 'manual' ); ?>><?php esc_html_e( 'Manual Post Selection Below', 'spicecraft-core' ); ?></option>
								</select>
							</td>
						</tr>
						<tr>
							<th scope="row"><label for="sc_blg_cat"><?php esc_html_e( 'Category Filter (if selected)', 'spicecraft-core' ); ?></label></th>
							<td>
								<?php
								wp_dropdown_categories( array(
									'show_option_all' => __( '— All Categories —', 'spicecraft-core' ),
									'name'            => self::OPTION_NAME . '[blog][category_id]',
									'id'              => 'sc_blg_cat',
									'selected'        => $blg['category_id'],
									'hierarchical'    => true,
									'hide_empty'      => false,
								) );
								?>
							</td>
						</tr>
						<tr>
							<th scope="row"><?php esc_html_e( 'Manual Post Selection', 'spicecraft-core' ); ?></th>
							<td>
								<?php $this->render_post_multiselect( 'post', self::OPTION_NAME . '[blog][selected_ids]', $blg['selected_ids'] ); ?>
							</td>
						</tr>
						<tr>
							<th scope="row"><label for="sc_blg_limit"><?php esc_html_e( 'Display Limit', 'spicecraft-core' ); ?></label></th>
							<td>
								<input type="number" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[blog][limit]" id="sc_blg_limit" value="<?php echo esc_attr( $blg['limit'] ); ?>" class="small-text" min="1" max="12" />
							</td>
						</tr>
						<tr>
							<th scope="row"><?php esc_html_e( 'CTA Button', 'spicecraft-core' ); ?></th>
							<td>
								<input type="text" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[blog][cta_label]" value="<?php echo esc_attr( $blg['cta_label'] ); ?>" class="regular-text" placeholder="<?php esc_attr_e( 'Read All Articles', 'spicecraft-core' ); ?>" style="margin-bottom: 6px;" /><br>
								<input type="url" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[blog][cta_url]" value="<?php echo esc_attr( $blg['cta_url'] ); ?>" class="regular-text" placeholder="<?php echo esc_attr( home_url( '/blog/' ) ); ?>" />
							</td>
						</tr>
					</table>

				<?php
				// TAB 8: CONVERSION & CALL TO ACTIONS
				elseif ( 'cta' === $active_tab ) :
					$b2b  = $settings['b2b_cta'];
					$fcta = $settings['final_cta'];
					$global_whatsapp = spicecraft_get_setting( 'whatsapp_number', '' );
					$global_email    = spicecraft_get_setting( 'email_general', '' );
					?>
					<h3><?php esc_html_e( 'Section 13: B2B, Institutional & Export Callout', 'spicecraft-core' ); ?></h3>
					<table class="form-table" role="presentation">
						<tr>
							<th scope="row"><label for="sc_b2b_eyebrow"><?php esc_html_e( 'Eyebrow', 'spicecraft-core' ); ?></label></th>
							<td>
								<input type="text" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[b2b_cta][eyebrow]" id="sc_b2b_eyebrow" value="<?php echo esc_attr( $b2b['eyebrow'] ); ?>" class="regular-text" placeholder="<?php esc_attr_e( 'Wholesale & Export Supply', 'spicecraft-core' ); ?>" />
							</td>
						</tr>
						<tr>
							<th scope="row"><label for="sc_b2b_heading"><?php esc_html_e( 'Heading', 'spicecraft-core' ); ?></label></th>
							<td>
								<input type="text" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[b2b_cta][heading]" id="sc_b2b_heading" value="<?php echo esc_attr( $b2b['heading'] ); ?>" class="large-text" placeholder="<?php esc_attr_e( 'Partner With a Trusted Spice Manufacturer', 'spicecraft-core' ); ?>" />
							</td>
						</tr>
						<tr>
							<th scope="row"><label for="sc_b2b_desc"><?php esc_html_e( 'Description', 'spicecraft-core' ); ?></label></th>
							<td>
								<textarea name="<?php echo esc_attr( self::OPTION_NAME ); ?>[b2b_cta][description]" id="sc_b2b_desc" rows="3" class="large-text"><?php echo esc_textarea( $b2b['description'] ); ?></textarea>
							</td>
						</tr>
						<tr>
							<th scope="row"><?php esc_html_e( 'Background Image', 'spicecraft-core' ); ?></th>
							<td>
								<?php $this->render_media_field( self::OPTION_NAME . '[b2b_cta][bg_image_id]', $b2b['bg_image_id'] ); ?>
								<p class="description"><?php esc_html_e( 'High-resolution textured background image (spices, packaging, or factory floor).', 'spicecraft-core' ); ?></p>
							</td>
						</tr>
						<tr>
							<th scope="row"><?php esc_html_e( 'Primary CTA Button', 'spicecraft-core' ); ?></th>
							<td>
								<input type="text" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[b2b_cta][primary_cta_label]" value="<?php echo esc_attr( $b2b['primary_cta_label'] ); ?>" class="regular-text" placeholder="<?php esc_attr_e( 'Request Trade Quote', 'spicecraft-core' ); ?>" style="margin-bottom: 6px;" /><br>
								<input type="url" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[b2b_cta][primary_cta_url]" value="<?php echo esc_attr( $b2b['primary_cta_url'] ); ?>" class="regular-text" placeholder="<?php echo esc_attr( home_url( '/#contact' ) ); ?>" />
							</td>
						</tr>
						<tr>
							<th scope="row"><?php esc_html_e( 'Secondary CTA Button', 'spicecraft-core' ); ?></th>
							<td>
								<input type="text" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[b2b_cta][secondary_cta_label]" value="<?php echo esc_attr( $b2b['secondary_cta_label'] ); ?>" class="regular-text" placeholder="<?php esc_attr_e( 'Download Product Catalog', 'spicecraft-core' ); ?>" style="margin-bottom: 6px;" /><br>
								<input type="url" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[b2b_cta][secondary_cta_url]" value="<?php echo esc_attr( $b2b['secondary_cta_url'] ); ?>" class="regular-text" />
							</td>
						</tr>
						<tr>
							<th scope="row"><?php esc_html_e( 'WhatsApp Trade Trigger', 'spicecraft-core' ); ?></th>
							<td>
								<label>
									<input type="checkbox" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[b2b_cta][enable_whatsapp]" value="1" <?php checked( ! empty( $b2b['enable_whatsapp'] ) ); ?> />
									<?php esc_html_e( 'Display direct WhatsApp Trade Chat button in B2B banner', 'spicecraft-core' ); ?>
								</label>
							</td>
						</tr>
					</table>

					<hr style="margin: 30px 0; border: 0; border-top: 1px solid #dcdcde;" />

					<h3><?php esc_html_e( 'Section 14: Final Enquiry & Lead Generation Banner', 'spicecraft-core' ); ?></h3>
					<div class="notice notice-info inline">
						<p>
							<strong><?php esc_html_e( 'Lead Routing Note:', 'spicecraft-core' ); ?></strong>
							<?php esc_html_e( 'Contact endpoints are controlled via SpiceCraft -> Global Settings to avoid duplicate data entry.', 'spicecraft-core' ); ?><br>
							<?php esc_html_e( 'Current WhatsApp Number:', 'spicecraft-core' ); ?> <code><?php echo esc_html( $global_whatsapp ?: __( 'Unconfigured', 'spicecraft-core' ) ); ?></code> |
							<?php esc_html_e( 'Current Enquiry Email:', 'spicecraft-core' ); ?> <code><?php echo esc_html( $global_email ?: __( 'Unconfigured', 'spicecraft-core' ) ); ?></code>
						</p>
					</div>
					<table class="form-table" role="presentation">
						<tr>
							<th scope="row"><label for="sc_fcta_heading"><?php esc_html_e( 'Heading', 'spicecraft-core' ); ?></label></th>
							<td>
								<input type="text" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[final_cta][heading]" id="sc_fcta_heading" value="<?php echo esc_attr( $fcta['heading'] ); ?>" class="large-text" placeholder="<?php esc_attr_e( 'Ready to Experience True Spice Purity?', 'spicecraft-core' ); ?>" />
							</td>
						</tr>
						<tr>
							<th scope="row"><label for="sc_fcta_desc"><?php esc_html_e( 'Description', 'spicecraft-core' ); ?></label></th>
							<td>
								<textarea name="<?php echo esc_attr( self::OPTION_NAME ); ?>[final_cta][description]" id="sc_fcta_desc" rows="3" class="large-text"><?php echo esc_textarea( $fcta['description'] ); ?></textarea>
							</td>
						</tr>
						<tr>
							<th scope="row"><?php esc_html_e( 'Primary CTA Button', 'spicecraft-core' ); ?></th>
							<td>
								<input type="text" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[final_cta][primary_cta_label]" value="<?php echo esc_attr( $fcta['primary_cta_label'] ); ?>" class="regular-text" placeholder="<?php esc_attr_e( 'Contact Trade Desk', 'spicecraft-core' ); ?>" style="margin-bottom: 6px;" /><br>
								<input type="url" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[final_cta][primary_cta_url]" value="<?php echo esc_attr( $fcta['primary_cta_url'] ); ?>" class="regular-text" placeholder="<?php echo esc_attr( home_url( '/#contact' ) ); ?>" />
							</td>
						</tr>
						<tr>
							<th scope="row"><?php esc_html_e( 'Dual Conversion Channels', 'spicecraft-core' ); ?></th>
							<td>
								<label style="display:block; margin-bottom: 6px;">
									<input type="checkbox" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[final_cta][enable_whatsapp]" value="1" <?php checked( ! empty( $fcta['enable_whatsapp'] ) ); ?> />
									<?php esc_html_e( 'Include Direct WhatsApp Action', 'spicecraft-core' ); ?>
								</label>
								<label style="display:block;">
									<input type="checkbox" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[final_cta][enable_email]" value="1" <?php checked( ! empty( $fcta['enable_email'] ) ); ?> />
									<?php esc_html_e( 'Include Direct Email Action', 'spicecraft-core' ); ?>
								</label>
							</td>
						</tr>
					</table>

				<?php endif; ?>

				<div style="margin-top: 24px;">
					<?php submit_button( __( 'Save Homepage Settings', 'spicecraft-core' ), 'primary', 'submit', false ); ?>
				</div>
			</form>
		</div>
		<?php
	}

	/**
	 * Render WordPress Media Library uploader component.
	 *
	 * @param string $field_name HTML name attribute.
	 * @param int    $image_id   Current attachment ID.
	 */
	private function render_media_field( $field_name, $image_id ) {
		$id        = absint( $image_id );
		$thumb_src = $id ? wp_get_attachment_image_url( $id, 'medium' ) : '';
		?>
		<div class="sc-media-uploader-box" style="display: inline-block;">
			<input type="hidden" name="<?php echo esc_attr( $field_name ); ?>" value="<?php echo esc_attr( $id ); ?>" class="sc-media-id-input" />
			<div class="sc-media-preview-wrap" style="width: 160px; height: 100px; background: #f0f0f1; border: 1px dashed #c3c4c7; border-radius: 4px; display: flex; align-items: center; justify-content: center; overflow: hidden; margin-bottom: 8px;">
				<img src="<?php echo esc_url( $thumb_src ); ?>" alt="" class="sc-media-preview-img" style="max-width: 100%; max-height: 100%; object-fit: cover; <?php echo empty( $thumb_src ) ? 'display:none;' : ''; ?>" />
				<span class="dashicons dashicons-format-image sc-media-placeholder-icon" style="font-size: 32px; width: 32px; height: 32px; color: #8c8f94; <?php echo ! empty( $thumb_src ) ? 'display:none;' : ''; ?>"></span>
			</div>
			<div style="display: flex; gap: 6px;">
				<button type="button" class="button button-secondary sc-media-select-btn">
					<?php esc_html_e( 'Select Image', 'spicecraft-core' ); ?>
				</button>
				<button type="button" class="button sc-media-remove-btn" style="<?php echo empty( $thumb_src ) ? 'display:none;' : ''; ?>">
					<?php esc_html_e( 'Remove', 'spicecraft-core' ); ?>
				</button>
			</div>
		</div>
		<?php
	}

	/**
	 * Render taxonomy terms multiselect list.
	 *
	 * @param string $taxonomy     Taxonomy name.
	 * @param string $field_name   Field name attribute.
	 * @param array  $selected_ids Array of selected term IDs.
	 */
	private function render_taxonomy_multiselect( $taxonomy, $field_name, $selected_ids = array() ) {
		$terms = get_terms( array(
			'taxonomy'   => $taxonomy,
			'hide_empty' => false,
		) );

		if ( empty( $terms ) || is_wp_error( $terms ) ) {
			echo '<p style="color: #646970; font-style: italic;">' . esc_html__( 'No items registered in this taxonomy yet.', 'spicecraft-core' ) . '</p>';
			return;
		}

		$selected_ids = is_array( $selected_ids ) ? array_map( 'absint', $selected_ids ) : array();
		?>
		<div style="max-height: 160px; overflow-y: auto; border: 1px solid #dcdcde; background: #fff; padding: 8px; border-radius: 4px; max-width: 420px;">
			<?php foreach ( $terms as $term ) : ?>
				<label style="display: block; margin-bottom: 4px; font-size: 13px;">
					<input type="checkbox" name="<?php echo esc_attr( $field_name ); ?>[]" value="<?php echo esc_attr( $term->term_id ); ?>" <?php checked( in_array( $term->term_id, $selected_ids, true ) ); ?> />
					<?php echo esc_html( $term->name ); ?>
					<span style="color: #8c8f94; font-size: 11px;">(<?php echo esc_html( $term->count ); ?>)</span>
				</label>
			<?php endforeach; ?>
		</div>
		<?php
	}

	/**
	 * Render post type multiselect list.
	 *
	 * @param string $post_type    Post type name.
	 * @param string $field_name   Field name attribute.
	 * @param array  $selected_ids Selected post IDs.
	 */
	private function render_post_multiselect( $post_type, $field_name, $selected_ids = array() ) {
		$posts = get_posts( array(
			'post_type'      => $post_type,
			'posts_per_page' => 50,
			'post_status'    => 'publish',
			'orderby'        => 'title',
			'order'          => 'ASC',
		) );

		if ( empty( $posts ) ) {
			echo '<p style="color: #646970; font-style: italic;">' . esc_html__( 'No published items found for this content type.', 'spicecraft-core' ) . '</p>';
			return;
		}

		$selected_ids = is_array( $selected_ids ) ? array_map( 'absint', $selected_ids ) : array();
		?>
		<div style="max-height: 160px; overflow-y: auto; border: 1px solid #dcdcde; background: #fff; padding: 8px; border-radius: 4px; max-width: 420px;">
			<?php foreach ( $posts as $p ) : ?>
				<label style="display: block; margin-bottom: 4px; font-size: 13px;">
					<input type="checkbox" name="<?php echo esc_attr( $field_name ); ?>[]" value="<?php echo esc_attr( $p->ID ); ?>" <?php checked( in_array( $p->ID, $selected_ids, true ) ); ?> />
					<?php echo esc_html( $p->post_title ); ?>
				</label>
			<?php endforeach; ?>
		</div>
		<?php
	}
}
