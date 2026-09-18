<?php
/**
 * SpiceCraft Core - Centralized About Us CMS Settings Interface
 *
 * Implements a dedicated, WordPress-native admin settings interface under
 * SpiceCraft -> About Us. Provides structured management of 15 dynamic sections,
 * media uploaders, repeatable fields, and section visibility/ordering.
 *
 * @package SpiceCraft_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class SpiceCraft_About_Settings {

	/**
	 * Option Name in wp_options.
	 */
	const OPTION_NAME = 'spicecraft_about_settings';

	/**
	 * Singleton Instance.
	 *
	 * @var SpiceCraft_About_Settings|null
	 */
	private static $instance = null;

	/**
	 * Get Singleton Instance.
	 *
	 * @return SpiceCraft_About_Settings
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
		add_action( 'admin_menu', array( $this, 'register_admin_menu' ), 22 );
		add_action( 'admin_init', array( $this, 'register_settings' ) );
	}

	/**
	 * Register Admin Submenu.
	 */
	public function register_admin_menu() {
		add_submenu_page(
			'spicecraft-overview',
			__( 'About Us CMS Management', 'spicecraft-core' ),
			__( 'About Us', 'spicecraft-core' ),
			'manage_options',
			'spicecraft-about',
			array( $this, 'render_page' )
		);
	}

	/**
	 * Register Settings with Validation & Sanitization.
	 */
	public function register_settings() {
		register_setting(
			'spicecraft_about_group',
			self::OPTION_NAME,
			array(
				'sanitize_callback' => array( $this, 'sanitize_settings' ),
				'default'           => spicecraft_get_about_default_settings(),
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

		$current = get_option( self::OPTION_NAME, spicecraft_get_about_default_settings() );
		$clean   = $current;

		// 1. Section Order
		if ( isset( $input['sections_order'] ) && is_array( $input['sections_order'] ) ) {
			foreach ( $input['sections_order'] as $sec => $val ) {
				$clean['sections_order'][ sanitize_key( $sec ) ] = absint( $val );
			}
		}

		// 2. Section Enabled Map
		if ( isset( $input['sections_enabled'] ) && is_array( $input['sections_enabled'] ) ) {
			$defaults = spicecraft_get_about_default_settings();
			foreach ( array_keys( $defaults['sections_order'] ) as $sec_key ) {
				$clean['sections_enabled'][ $sec_key ] = ! empty( $input['sections_enabled'][ $sec_key ] ) ? 1 : 0;
			}
		}

		// 3. Hero Section
		if ( isset( $input['hero'] ) && is_array( $input['hero'] ) ) {
			$h = $input['hero'];
			$clean['hero']['eyebrow']             = isset( $h['eyebrow'] ) ? sanitize_text_field( $h['eyebrow'] ) : '';
			$clean['hero']['heading']             = isset( $h['heading'] ) ? sanitize_text_field( $h['heading'] ) : '';
			$clean['hero']['highlight_text']      = isset( $h['highlight_text'] ) ? sanitize_text_field( $h['highlight_text'] ) : '';
			$clean['hero']['description']         = isset( $h['description'] ) ? sanitize_textarea_field( $h['description'] ) : '';
			$clean['hero']['desktop_image_id']    = isset( $h['desktop_image_id'] ) ? absint( $h['desktop_image_id'] ) : 0;
			$clean['hero']['mobile_image_id']     = isset( $h['mobile_image_id'] ) ? absint( $h['mobile_image_id'] ) : 0;
			$clean['hero']['image_alt']           = isset( $h['image_alt'] ) ? sanitize_text_field( $h['image_alt'] ) : '';
			$clean['hero']['primary_cta_label']   = isset( $h['primary_cta_label'] ) ? sanitize_text_field( $h['primary_cta_label'] ) : '';
			$clean['hero']['primary_cta_url']     = isset( $h['primary_cta_url'] ) ? esc_url_raw( $h['primary_cta_url'] ) : '';
			$clean['hero']['secondary_cta_label'] = isset( $h['secondary_cta_label'] ) ? sanitize_text_field( $h['secondary_cta_label'] ) : '';
			$clean['hero']['secondary_cta_url']   = isset( $h['secondary_cta_url'] ) ? esc_url_raw( $h['secondary_cta_url'] ) : '';
		}

		// 4. Company Introduction
		if ( isset( $input['introduction'] ) && is_array( $input['introduction'] ) ) {
			$intro = $input['introduction'];
			$clean['introduction']['eyebrow']            = isset( $intro['eyebrow'] ) ? sanitize_text_field( $intro['eyebrow'] ) : '';
			$clean['introduction']['heading']            = isset( $intro['heading'] ) ? sanitize_text_field( $intro['heading'] ) : '';
			$clean['introduction']['content']            = isset( $intro['content'] ) ? wp_kses_post( $intro['content'] ) : '';
			$clean['introduction']['primary_image_id']   = isset( $intro['primary_image_id'] ) ? absint( $intro['primary_image_id'] ) : 0;
			$clean['introduction']['secondary_image_id'] = isset( $intro['secondary_image_id'] ) ? absint( $intro['secondary_image_id'] ) : 0;
			$clean['introduction']['cta_label']          = isset( $intro['cta_label'] ) ? sanitize_text_field( $intro['cta_label'] ) : '';
			$clean['introduction']['cta_url']            = isset( $intro['cta_url'] ) ? esc_url_raw( $intro['cta_url'] ) : '';
		}

		// 5. Our Story
		if ( isset( $input['story'] ) && is_array( $input['story'] ) ) {
			$st = $input['story'];
			$clean['story']['eyebrow']            = isset( $st['eyebrow'] ) ? sanitize_text_field( $st['eyebrow'] ) : '';
			$clean['story']['heading']            = isset( $st['heading'] ) ? sanitize_text_field( $st['heading'] ) : '';
			$clean['story']['content']            = isset( $st['content'] ) ? wp_kses_post( $st['content'] ) : '';
			$clean['story']['story_image_id']     = isset( $st['story_image_id'] ) ? absint( $st['story_image_id'] ) : 0;
			$clean['story']['secondary_image_id'] = isset( $st['secondary_image_id'] ) ? absint( $st['secondary_image_id'] ) : 0;
			$clean['story']['quote_text']         = isset( $st['quote_text'] ) ? sanitize_textarea_field( $st['quote_text'] ) : '';
			$clean['story']['quote_attribution']  = isset( $st['quote_attribution'] ) ? sanitize_text_field( $st['quote_attribution'] ) : '';
		}

		// 6. Vision & Mission
		if ( isset( $input['vision_mission'] ) && is_array( $input['vision_mission'] ) ) {
			$vm = $input['vision_mission'];
			$clean['vision_mission']['eyebrow']          = isset( $vm['eyebrow'] ) ? sanitize_text_field( $vm['eyebrow'] ) : '';
			$clean['vision_mission']['heading']          = isset( $vm['heading'] ) ? sanitize_text_field( $vm['heading'] ) : '';
			$clean['vision_mission']['description']      = isset( $vm['description'] ) ? sanitize_textarea_field( $vm['description'] ) : '';
			$clean['vision_mission']['vision_enabled']   = ! empty( $vm['vision_enabled'] ) ? 1 : 0;
			$clean['vision_mission']['vision_heading']   = isset( $vm['vision_heading'] ) ? sanitize_text_field( $vm['vision_heading'] ) : '';
			$clean['vision_mission']['vision_content']   = isset( $vm['vision_content'] ) ? sanitize_textarea_field( $vm['vision_content'] ) : '';
			$clean['vision_mission']['vision_image_id']  = isset( $vm['vision_image_id'] ) ? absint( $vm['vision_image_id'] ) : 0;
			$clean['vision_mission']['mission_enabled']  = ! empty( $vm['mission_enabled'] ) ? 1 : 0;
			$clean['vision_mission']['mission_heading']  = isset( $vm['mission_heading'] ) ? sanitize_text_field( $vm['mission_heading'] ) : '';
			$clean['vision_mission']['mission_content']  = isset( $vm['mission_content'] ) ? sanitize_textarea_field( $vm['mission_content'] ) : '';
			$clean['vision_mission']['mission_image_id'] = isset( $vm['mission_image_id'] ) ? absint( $vm['mission_image_id'] ) : 0;
		}

		// 7. Core Values
		if ( isset( $input['values'] ) && is_array( $input['values'] ) ) {
			$val = $input['values'];
			$clean['values']['eyebrow']     = isset( $val['eyebrow'] ) ? sanitize_text_field( $val['eyebrow'] ) : '';
			$clean['values']['heading']     = isset( $val['heading'] ) ? sanitize_text_field( $val['heading'] ) : '';
			$clean['values']['description'] = isset( $val['description'] ) ? sanitize_textarea_field( $val['description'] ) : '';

			$clean_vals = array();
			if ( isset( $val['items'] ) && is_array( $val['items'] ) ) {
				foreach ( $val['items'] as $item ) {
					$title = isset( $item['title'] ) ? sanitize_text_field( $item['title'] ) : '';
					$desc  = isset( $item['description'] ) ? sanitize_textarea_field( $item['description'] ) : '';
					$icon  = isset( $item['icon'] ) ? sanitize_text_field( $item['icon'] ) : '';
					$order = isset( $item['order'] ) ? absint( $item['order'] ) : 10;
					if ( '' !== $title ) {
						$clean_vals[] = array(
							'icon'        => $icon,
							'title'       => $title,
							'description' => $desc,
							'order'       => $order,
						);
					}
				}
			}
			$clean['values']['items'] = $clean_vals;
		}

		// 8. Quality Philosophy
		if ( isset( $input['quality'] ) && is_array( $input['quality'] ) ) {
			$q = $input['quality'];
			$clean['quality']['eyebrow']     = isset( $q['eyebrow'] ) ? sanitize_text_field( $q['eyebrow'] ) : '';
			$clean['quality']['heading']     = isset( $q['heading'] ) ? sanitize_text_field( $q['heading'] ) : '';
			$clean['quality']['description'] = isset( $q['description'] ) ? sanitize_textarea_field( $q['description'] ) : '';
			$clean['quality']['image_id']    = isset( $q['image_id'] ) ? absint( $q['image_id'] ) : 0;
			$clean['quality']['cta_label']   = isset( $q['cta_label'] ) ? sanitize_text_field( $q['cta_label'] ) : '';
			$clean['quality']['cta_url']     = isset( $q['cta_url'] ) ? esc_url_raw( $q['cta_url'] ) : '';

			$clean_points = array();
			if ( isset( $q['points'] ) && is_array( $q['points'] ) ) {
				foreach ( $q['points'] as $pt ) {
					$title = isset( $pt['title'] ) ? sanitize_text_field( $pt['title'] ) : '';
					$text  = isset( $pt['text'] ) ? sanitize_textarea_field( $pt['text'] ) : '';
					if ( '' !== $title || '' !== $text ) {
						$clean_points[] = array( 'title' => $title, 'text' => $text );
					}
				}
			}
			$clean['quality']['points'] = $clean_points;
		}

		// 9. Sourcing Philosophy
		if ( isset( $input['sourcing'] ) && is_array( $input['sourcing'] ) ) {
			$src = $input['sourcing'];
			$clean['sourcing']['eyebrow']          = isset( $src['eyebrow'] ) ? sanitize_text_field( $src['eyebrow'] ) : '';
			$clean['sourcing']['heading']          = isset( $src['heading'] ) ? sanitize_text_field( $src['heading'] ) : '';
			$clean['sourcing']['description']      = isset( $src['description'] ) ? sanitize_textarea_field( $src['description'] ) : '';
			$clean['sourcing']['image_id']         = isset( $src['image_id'] ) ? absint( $src['image_id'] ) : 0;
			$clean['sourcing']['support_image_id'] = isset( $src['support_image_id'] ) ? absint( $src['support_image_id'] ) : 0;
			$clean['sourcing']['cta_label']        = isset( $src['cta_label'] ) ? sanitize_text_field( $src['cta_label'] ) : '';
			$clean['sourcing']['cta_url']          = isset( $src['cta_url'] ) ? esc_url_raw( $src['cta_url'] ) : '';

			$clean_points = array();
			if ( isset( $src['points'] ) && is_array( $src['points'] ) ) {
				foreach ( $src['points'] as $pt ) {
					$title = isset( $pt['title'] ) ? sanitize_text_field( $pt['title'] ) : '';
					$text  = isset( $pt['text'] ) ? sanitize_textarea_field( $pt['text'] ) : '';
					if ( '' !== $title || '' !== $text ) {
						$clean_points[] = array( 'title' => $title, 'text' => $text );
					}
				}
			}
			$clean['sourcing']['points'] = $clean_points;
		}

		// 10. Manufacturing Philosophy
		if ( isset( $input['manufacturing'] ) && is_array( $input['manufacturing'] ) ) {
			$mfg = $input['manufacturing'];
			$clean['manufacturing']['eyebrow']       = isset( $mfg['eyebrow'] ) ? sanitize_text_field( $mfg['eyebrow'] ) : '';
			$clean['manufacturing']['heading']       = isset( $mfg['heading'] ) ? sanitize_text_field( $mfg['heading'] ) : '';
			$clean['manufacturing']['description']   = isset( $mfg['description'] ) ? sanitize_textarea_field( $mfg['description'] ) : '';
			$clean['manufacturing']['main_image_id'] = isset( $mfg['main_image_id'] ) ? absint( $mfg['main_image_id'] ) : 0;
			$clean['manufacturing']['video_url']     = isset( $mfg['video_url'] ) ? esc_url_raw( $mfg['video_url'] ) : '';
			$clean['manufacturing']['cta_label']     = isset( $mfg['cta_label'] ) ? sanitize_text_field( $mfg['cta_label'] ) : '';
			$clean['manufacturing']['cta_url']       = isset( $mfg['cta_url'] ) ? esc_url_raw( $mfg['cta_url'] ) : '';

			$clean_hl = array();
			if ( isset( $mfg['highlights'] ) && is_array( $mfg['highlights'] ) ) {
				foreach ( $mfg['highlights'] as $hl ) {
					$title = isset( $hl['title'] ) ? sanitize_text_field( $hl['title'] ) : '';
					$text  = isset( $hl['text'] ) ? sanitize_textarea_field( $hl['text'] ) : '';
					if ( '' !== $title || '' !== $text ) {
						$clean_hl[] = array( 'title' => $title, 'text' => $text );
					}
				}
			}
			$clean['manufacturing']['highlights'] = $clean_hl;
		}

		// 11. Company Statistics
		if ( isset( $input['statistics'] ) && is_array( $input['statistics'] ) ) {
			$stat = $input['statistics'];
			$clean['statistics']['eyebrow']     = isset( $stat['eyebrow'] ) ? sanitize_text_field( $stat['eyebrow'] ) : '';
			$clean['statistics']['heading']     = isset( $stat['heading'] ) ? sanitize_text_field( $stat['heading'] ) : '';
			$clean['statistics']['description'] = isset( $stat['description'] ) ? sanitize_textarea_field( $stat['description'] ) : '';

			$clean_stats = array();
			if ( isset( $stat['items'] ) && is_array( $stat['items'] ) ) {
				foreach ( $stat['items'] as $item ) {
					$val   = isset( $item['value'] ) ? sanitize_text_field( $item['value'] ) : '';
					$sfx   = isset( $item['suffix'] ) ? sanitize_text_field( $item['suffix'] ) : '';
					$lbl   = isset( $item['label'] ) ? sanitize_text_field( $item['label'] ) : '';
					$desc  = isset( $item['description'] ) ? sanitize_text_field( $item['description'] ) : '';
					$order = isset( $item['order'] ) ? absint( $item['order'] ) : 10;
					if ( '' !== $val && '' !== $lbl ) {
						$clean_stats[] = array(
							'value'       => $val,
							'suffix'      => $sfx,
							'label'       => $lbl,
							'description' => $desc,
							'order'       => $order,
						);
					}
				}
			}
			$clean['statistics']['items'] = $clean_stats;
		}

		// 12. Milestones
		if ( isset( $input['milestones'] ) && is_array( $input['milestones'] ) ) {
			$ms = $input['milestones'];
			$clean['milestones']['eyebrow']     = isset( $ms['eyebrow'] ) ? sanitize_text_field( $ms['eyebrow'] ) : '';
			$clean['milestones']['heading']     = isset( $ms['heading'] ) ? sanitize_text_field( $ms['heading'] ) : '';
			$clean['milestones']['description'] = isset( $ms['description'] ) ? sanitize_textarea_field( $ms['description'] ) : '';

			$clean_ms = array();
			if ( isset( $ms['items'] ) && is_array( $ms['items'] ) ) {
				foreach ( $ms['items'] as $item ) {
					$date_lbl = isset( $item['date_label'] ) ? sanitize_text_field( $item['date_label'] ) : '';
					$title    = isset( $item['title'] ) ? sanitize_text_field( $item['title'] ) : '';
					$desc     = isset( $item['description'] ) ? sanitize_textarea_field( $item['description'] ) : '';
					$img_id   = isset( $item['image_id'] ) ? absint( $item['image_id'] ) : 0;
					$order    = isset( $item['order'] ) ? absint( $item['order'] ) : 10;
					if ( '' !== $title || '' !== $date_lbl ) {
						$clean_ms[] = array(
							'date_label'  => $date_lbl,
							'title'       => $title,
							'description' => $desc,
							'image_id'    => $img_id,
							'order'       => $order,
						);
					}
				}
			}
			$clean['milestones']['items'] = $clean_ms;
		}

		// 13. Leadership
		if ( isset( $input['leadership'] ) && is_array( $input['leadership'] ) ) {
			$lead = $input['leadership'];
			$clean['leadership']['eyebrow']      = isset( $lead['eyebrow'] ) ? sanitize_text_field( $lead['eyebrow'] ) : '';
			$clean['leadership']['heading']      = isset( $lead['heading'] ) ? sanitize_text_field( $lead['heading'] ) : '';
			$clean['leadership']['description']  = isset( $lead['description'] ) ? sanitize_textarea_field( $lead['description'] ) : '';
			$clean['leadership']['limit']        = isset( $lead['limit'] ) ? max( 1, min( 20, absint( $lead['limit'] ) ) ) : 6;
			$clean['leadership']['selected_ids'] = isset( $lead['selected_ids'] ) && is_array( $lead['selected_ids'] ) ? array_map( 'absint', $lead['selected_ids'] ) : array();
		}

		// 14. Certifications
		if ( isset( $input['certifications'] ) && is_array( $input['certifications'] ) ) {
			$cert = $input['certifications'];
			$clean['certifications']['eyebrow']      = isset( $cert['eyebrow'] ) ? sanitize_text_field( $cert['eyebrow'] ) : '';
			$clean['certifications']['heading']      = isset( $cert['heading'] ) ? sanitize_text_field( $cert['heading'] ) : '';
			$clean['certifications']['description']  = isset( $cert['description'] ) ? sanitize_textarea_field( $cert['description'] ) : '';
			$clean['certifications']['limit']        = isset( $cert['limit'] ) ? max( 1, min( 20, absint( $cert['limit'] ) ) ) : 8;
			$clean['certifications']['selected_ids'] = isset( $cert['selected_ids'] ) && is_array( $cert['selected_ids'] ) ? array_map( 'absint', $cert['selected_ids'] ) : array();
			$clean['certifications']['cta_label']    = isset( $cert['cta_label'] ) ? sanitize_text_field( $cert['cta_label'] ) : '';
			$clean['certifications']['cta_url']      = isset( $cert['cta_url'] ) ? esc_url_raw( $cert['cta_url'] ) : '';
		}

		// 15. Product Connection
		if ( isset( $input['products'] ) && is_array( $input['products'] ) ) {
			$prod = $input['products'];
			$clean['products']['eyebrow']      = isset( $prod['eyebrow'] ) ? sanitize_text_field( $prod['eyebrow'] ) : '';
			$clean['products']['heading']      = isset( $prod['heading'] ) ? sanitize_text_field( $prod['heading'] ) : '';
			$clean['products']['description']  = isset( $prod['description'] ) ? sanitize_textarea_field( $prod['description'] ) : '';
			$clean['products']['source']       = isset( $prod['source'] ) && in_array( $prod['source'], array( 'categories', 'products' ), true ) ? $prod['source'] : 'categories';
			$clean['products']['limit']        = isset( $prod['limit'] ) ? max( 1, min( 20, absint( $prod['limit'] ) ) ) : 4;
			$clean['products']['selected_ids'] = isset( $prod['selected_ids'] ) && is_array( $prod['selected_ids'] ) ? array_map( 'absint', $prod['selected_ids'] ) : array();
			$clean['products']['cta_label']    = isset( $prod['cta_label'] ) ? sanitize_text_field( $prod['cta_label'] ) : '';
			$clean['products']['cta_url']      = isset( $prod['cta_url'] ) ? esc_url_raw( $prod['cta_url'] ) : '';
		}

		// 16. B2B / Export CTA
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

		// 17. Final Contact CTA
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
	 * Render the Settings Page.
	 */
	public function render_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$settings   = spicecraft_get_about_settings();
		$active_tab = isset( $_GET['tab'] ) ? sanitize_key( $_GET['tab'] ) : 'order';

		$tabs = array(
			'order'      => __( '1. Order & Visibility', 'spicecraft-core' ),
			'hero'       => __( '2. Hero & Intro', 'spicecraft-core' ),
			'story'      => __( '3. Story & Values', 'spicecraft-core' ),
			'pillars'    => __( '4. Philosophy Pillars', 'spicecraft-core' ),
			'journey'    => __( '5. Journey & Stats', 'spicecraft-core' ),
			'leadership' => __( '6. Leadership & Team', 'spicecraft-core' ),
			'trust'      => __( '7. Trust & Products', 'spicecraft-core' ),
			'cta'        => __( '8. Business CTAs', 'spicecraft-core' ),
		);

		$about_page = get_page_by_path( 'about' );
		$about_url  = $about_page ? get_permalink( $about_page->ID ) : home_url( '/about/' );
		?>
		<div class="wrap spicecraft-settings-wrap">
			<div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px; margin-bottom: 12px;">
				<h1 style="margin: 0;"><?php esc_html_e( 'SpiceCraft About Us CMS Management', 'spicecraft-core' ); ?></h1>
				<a href="<?php echo esc_url( $about_url ); ?>" target="_blank" rel="noopener noreferrer" class="button button-secondary" style="display: inline-flex; align-items: center; gap: 4px;">
					<span class="dashicons dashicons-external" style="margin-top: -2px;"></span>
					<?php esc_html_e( 'View About Page', 'spicecraft-core' ); ?>
				</a>
			</div>

			<p class="description" style="margin-bottom: 16px;">
				<?php esc_html_e( 'Manage the 15 structured About sections, adjust sequence, configure storytelling pillars, and manage business milestones without editing theme code.', 'spicecraft-core' ); ?>
			</p>

			<?php settings_errors(); ?>

			<!-- Navigation Tabs -->
			<h2 class="nav-tab-wrapper" style="margin-bottom: 20px;">
				<?php foreach ( $tabs as $tab_key => $tab_title ) : ?>
					<a href="?page=spicecraft-about&tab=<?php echo esc_attr( $tab_key ); ?>" class="nav-tab <?php echo $tab_key === $active_tab ? 'nav-tab-active' : ''; ?>">
						<?php echo esc_html( $tab_title ); ?>
					</a>
				<?php endforeach; ?>
			</h2>

			<form method="post" action="options.php" class="spicecraft-about-form">
				<?php
				settings_fields( 'spicecraft_about_group' );
				?>
				<input type="hidden" name="_wp_http_referer" value="<?php echo esc_attr( wp_unslash( $_SERVER['REQUEST_URI'] ?? '' ) ); ?>" />

				<?php
				// TAB 1: ORDER & VISIBILITY
				if ( 'order' === $active_tab ) :
					$section_names = array(
						'hero'           => array( 'name' => __( 'About Hero Banner', 'spicecraft-core' ), 'id' => '#about-hero', 'desc' => __( 'Editorial brand opening statement, spice imagery, and primary conversion links.', 'spicecraft-core' ) ),
						'introduction'   => array( 'name' => __( 'Company Introduction', 'spicecraft-core' ), 'id' => '#company-intro', 'desc' => __( 'Who we are, what we do, and our core manufacturer focus.', 'spicecraft-core' ) ),
						'story'          => array( 'name' => __( 'Our Story & Heritage', 'spicecraft-core' ), 'id' => '#our-story', 'desc' => __( 'Brand narrative, founding heritage, and founder/origin quote.', 'spicecraft-core' ) ),
						'vision_mission' => array( 'name' => __( 'Vision & Mission', 'spicecraft-core' ), 'id' => '#vision-mission', 'desc' => __( 'Paired architectural composition for corporate vision and manufacturing mission.', 'spicecraft-core' ) ),
						'values'         => array( 'name' => __( 'Core Values', 'spicecraft-core' ), 'id' => '#core-values', 'desc' => __( 'Repeatable company values (e.g. Purity, Traceability, Excellence).', 'spicecraft-core' ) ),
						'quality'        => array( 'name' => __( 'Quality Philosophy', 'spicecraft-core' ), 'id' => '#quality-philosophy', 'desc' => __( 'Brand-level commitment to safety, lab testing, and cold grinding.', 'spicecraft-core' ) ),
						'sourcing'       => array( 'name' => __( 'Sourcing Philosophy', 'spicecraft-core' ), 'id' => '#sourcing-philosophy', 'desc' => __( 'Direct farmer relationships, single-origin procurement, and harvest ethics.', 'spicecraft-core' ) ),
						'manufacturing'  => array( 'name' => __( 'Manufacturing Philosophy', 'spicecraft-core' ), 'id' => '#manufacturing-philosophy', 'desc' => __( 'Cleanroom processing, low-temperature milling, and hygienic packaging.', 'spicecraft-core' ) ),
						'statistics'     => array( 'name' => __( 'Company Statistics', 'spicecraft-core' ), 'id' => '#company-statistics', 'desc' => __( 'Verified numeric milestone metrics (hidden cleanly if unpopulated).', 'spicecraft-core' ) ),
						'milestones'     => array( 'name' => __( 'Journey & Milestones', 'spicecraft-core' ), 'id' => '#journey-milestones', 'desc' => __( 'Responsive timeline showcasing genuine business milestones.', 'spicecraft-core' ) ),
						'leadership'     => array( 'name' => __( 'Leadership & People', 'spicecraft-core' ), 'id' => '#leadership-team', 'desc' => __( 'Portraits, roles, and profiles of executive and culinary leaders.', 'spicecraft-core' ) ),
						'certifications' => array( 'name' => __( 'Certifications & Trust', 'spicecraft-core' ), 'id' => '#certifications-trust', 'desc' => __( 'Statutory credentials (FSSAI, ISO, HACCP, Spices Board) from taxonomy.', 'spicecraft-core' ) ),
						'products'       => array( 'name' => __( 'Product Connection', 'spicecraft-core' ), 'id' => '#product-connection', 'desc' => __( 'Catalog bridge directing visitors to spice collections.', 'spicecraft-core' ) ),
						'b2b_cta'        => array( 'name' => __( 'B2B & Export CTA', 'spicecraft-core' ), 'id' => '#b2b-export', 'desc' => __( 'Industrial procurement, private label, and bulk container export desk.', 'spicecraft-core' ) ),
						'final_cta'      => array( 'name' => __( 'Final Contact CTA', 'spicecraft-core' ), 'id' => '#final-contact', 'desc' => __( 'Direct communication channels utilizing centralized Global Settings.', 'spicecraft-core' ) ),
					);

					$order_map   = $settings['sections_order'];
					$enabled_map = $settings['sections_enabled'];
					asort( $order_map, SORT_NUMERIC );
					?>
					<div class="notice notice-info inline" style="margin-bottom: 20px;">
						<p><?php esc_html_e( 'Configure section visibility and order. Lower numbers render higher on the About page. Note: Empty sections are cleanly hidden on the frontend to prevent placeholder output.', 'spicecraft-core' ); ?></p>
					</div>

					<table class="wp-list-table widefat fixed striped" role="presentation">
						<thead>
							<tr>
								<th style="width: 80px;"><?php esc_html_e( 'Enabled', 'spicecraft-core' ); ?></th>
								<th style="width: 100px;"><?php esc_html_e( 'Order', 'spicecraft-core' ); ?></th>
								<th style="width: 220px;"><?php esc_html_e( 'Section Name', 'spicecraft-core' ); ?></th>
								<th style="width: 180px;"><?php esc_html_e( 'Semantic Anchor', 'spicecraft-core' ); ?></th>
								<th><?php esc_html_e( 'Purpose / Content Description', 'spicecraft-core' ); ?></th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ( $order_map as $sec_key => $ord_val ) :
								$sec_info   = $section_names[ $sec_key ] ?? array( 'name' => $sec_key, 'id' => '#' . $sec_key, 'desc' => '' );
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
				// TAB 2: HERO & INTRODUCTION
				elseif ( 'hero' === $active_tab ) :
					$h     = $settings['hero'];
					$intro = $settings['introduction'];
					?>
					<!-- Section 1: About Hero -->
					<div class="postbox" style="margin-bottom: 20px;">
						<div class="postbox-header"><h2 class="hndle"><?php esc_html_e( '1. About Hero Banner', 'spicecraft-core' ); ?></h2></div>
						<div class="inside">
							<table class="form-table" role="presentation">
								<tr>
									<th scope="row"><label for="hero_eyebrow"><?php esc_html_e( 'Eyebrow Tag', 'spicecraft-core' ); ?></label></th>
									<td>
										<input type="text" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[hero][eyebrow]" id="hero_eyebrow" value="<?php echo esc_attr( $h['eyebrow'] ); ?>" class="regular-text" placeholder="e.g. Rooted in Purity & Heritage" />
									</td>
								</tr>
								<tr>
									<th scope="row"><label for="hero_heading"><?php esc_html_e( 'Main Heading (H1)', 'spicecraft-core' ); ?></label></th>
									<td>
										<input type="text" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[hero][heading]" id="hero_heading" value="<?php echo esc_attr( $h['heading'] ); ?>" class="large-text" placeholder="e.g. Master Spice Crafters & FMCG Manufacturers" />
										<p class="description"><?php esc_html_e( 'The definitive statement representing company identity.', 'spicecraft-core' ); ?></p>
									</td>
								</tr>
								<tr>
									<th scope="row"><label for="hero_highlight"><?php esc_html_e( 'Highlighted Text Accent', 'spicecraft-core' ); ?></label></th>
									<td>
										<input type="text" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[hero][highlight_text]" id="hero_highlight" value="<?php echo esc_attr( $h['highlight_text'] ); ?>" class="regular-text" placeholder="e.g. Since 1998" />
										<p class="description"><?php esc_html_e( 'Accent text displayed in terracotta/saffron script styling.', 'spicecraft-core' ); ?></p>
									</td>
								</tr>
								<tr>
									<th scope="row"><label for="hero_desc"><?php esc_html_e( 'Short Introduction Narrative', 'spicecraft-core' ); ?></label></th>
									<td>
										<textarea name="<?php echo esc_attr( self::OPTION_NAME ); ?>[hero][description]" id="hero_desc" rows="3" class="large-text"><?php echo esc_textarea( $h['description'] ); ?></textarea>
									</td>
								</tr>
								<tr>
									<th scope="row"><?php esc_html_e( 'Desktop Hero Image', 'spicecraft-core' ); ?></th>
									<td>
										<?php $this->render_media_field( self::OPTION_NAME . '[hero][desktop_image_id]', $h['desktop_image_id'] ); ?>
										<p class="description"><?php esc_html_e( 'High-resolution manufacturing or spice composition (1600x900 recommended). Loaded with high fetch priority.', 'spicecraft-core' ); ?></p>
									</td>
								</tr>
								<tr>
									<th scope="row"><?php esc_html_e( 'Mobile Hero Image (Optional)', 'spicecraft-core' ); ?></th>
									<td>
										<?php $this->render_media_field( self::OPTION_NAME . '[hero][mobile_image_id]', $h['mobile_image_id'] ); ?>
										<p class="description"><?php esc_html_e( 'Optional vertical crop for mobile screens (800x1000).', 'spicecraft-core' ); ?></p>
									</td>
								</tr>
								<tr>
									<th scope="row"><label for="hero_img_alt"><?php esc_html_e( 'Hero Image Alt Text', 'spicecraft-core' ); ?></label></th>
									<td>
										<input type="text" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[hero][image_alt]" id="hero_img_alt" value="<?php echo esc_attr( $h['image_alt'] ); ?>" class="regular-text" placeholder="e.g. SpiceCraft master blending facility and organic spices" />
									</td>
								</tr>
								<tr>
									<th scope="row"><?php esc_html_e( 'Primary CTA', 'spicecraft-core' ); ?></th>
									<td>
										<input type="text" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[hero][primary_cta_label]" value="<?php echo esc_attr( $h['primary_cta_label'] ); ?>" placeholder="Label (e.g. Explore Our Story)" class="regular-text" style="width: 200px;" />
										<input type="text" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[hero][primary_cta_url]" value="<?php echo esc_attr( $h['primary_cta_url'] ); ?>" placeholder="URL or Anchor (#our-story)" class="regular-text" style="width: 250px;" />
									</td>
								</tr>
								<tr>
									<th scope="row"><?php esc_html_e( 'Secondary CTA', 'spicecraft-core' ); ?></th>
									<td>
										<input type="text" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[hero][secondary_cta_label]" value="<?php echo esc_attr( $h['secondary_cta_label'] ); ?>" placeholder="Label (e.g. Business Enquiry)" class="regular-text" style="width: 200px;" />
										<input type="text" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[hero][secondary_cta_url]" value="<?php echo esc_attr( $h['secondary_cta_url'] ); ?>" placeholder="URL or Anchor (#b2b-export)" class="regular-text" style="width: 250px;" />
									</td>
								</tr>
							</table>
						</div>
					</div>

					<!-- Section 2: Company Introduction -->
					<div class="postbox">
						<div class="postbox-header"><h2 class="hndle"><?php esc_html_e( '2. Company Introduction', 'spicecraft-core' ); ?></h2></div>
						<div class="inside">
							<table class="form-table" role="presentation">
								<tr>
									<th scope="row"><label for="intro_eyebrow"><?php esc_html_e( 'Eyebrow', 'spicecraft-core' ); ?></label></th>
									<td>
										<input type="text" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[introduction][eyebrow]" id="intro_eyebrow" value="<?php echo esc_attr( $intro['eyebrow'] ); ?>" class="regular-text" placeholder="e.g. Who We Are" />
									</td>
								</tr>
								<tr>
									<th scope="row"><label for="intro_heading"><?php esc_html_e( 'Heading', 'spicecraft-core' ); ?></label></th>
									<td>
										<input type="text" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[introduction][heading]" id="intro_heading" value="<?php echo esc_attr( $intro['heading'] ); ?>" class="large-text" placeholder="e.g. Crafted with Integrity, Scaled for Global FMCG Standards" />
									</td>
								</tr>
								<tr>
									<th scope="row"><label for="intro_content"><?php esc_html_e( 'Introduction Narrative', 'spicecraft-core' ); ?></label></th>
									<td>
										<textarea name="<?php echo esc_attr( self::OPTION_NAME ); ?>[introduction][content]" id="intro_content" rows="6" class="large-text"><?php echo esc_textarea( $intro['content'] ); ?></textarea>
										<p class="description"><?php esc_html_e( 'Rich narrative describing who we are, what we do, and our commitment to authenticity.', 'spicecraft-core' ); ?></p>
									</td>
								</tr>
								<tr>
									<th scope="row"><?php esc_html_e( 'Primary Showcase Image', 'spicecraft-core' ); ?></th>
									<td>
										<?php $this->render_media_field( self::OPTION_NAME . '[introduction][primary_image_id]', $intro['primary_image_id'] ); ?>
									</td>
								</tr>
								<tr>
									<th scope="row"><?php esc_html_e( 'Secondary Inset Image (Optional)', 'spicecraft-core' ); ?></th>
									<td>
										<?php $this->render_media_field( self::OPTION_NAME . '[introduction][secondary_image_id]', $intro['secondary_image_id'] ); ?>
									</td>
								</tr>
								<tr>
									<th scope="row"><?php esc_html_e( 'CTA Link (Optional)', 'spicecraft-core' ); ?></th>
									<td>
										<input type="text" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[introduction][cta_label]" value="<?php echo esc_attr( $intro['cta_label'] ); ?>" placeholder="Button Label" class="regular-text" style="width: 200px;" />
										<input type="text" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[introduction][cta_url]" value="<?php echo esc_attr( $intro['cta_url'] ); ?>" placeholder="Destination URL" class="regular-text" style="width: 250px;" />
									</td>
								</tr>
							</table>
						</div>
					</div>

				<?php
				// TAB 3: STORY & VALUES
				elseif ( 'story' === $active_tab ) :
					$st  = $settings['story'];
					$vm  = $settings['vision_mission'];
					$val = $settings['values'];
					?>
					<!-- Section 3: Our Story -->
					<div class="postbox" style="margin-bottom: 20px;">
						<div class="postbox-header"><h2 class="hndle"><?php esc_html_e( '3. Our Story & Heritage', 'spicecraft-core' ); ?></h2></div>
						<div class="inside">
							<table class="form-table" role="presentation">
								<tr>
									<th scope="row"><label for="story_eyebrow"><?php esc_html_e( 'Eyebrow', 'spicecraft-core' ); ?></label></th>
									<td>
										<input type="text" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[story][eyebrow]" id="story_eyebrow" value="<?php echo esc_attr( $st['eyebrow'] ); ?>" class="regular-text" placeholder="e.g. Heritage & Origins" />
									</td>
								</tr>
								<tr>
									<th scope="row"><label for="story_heading"><?php esc_html_e( 'Story Heading', 'spicecraft-core' ); ?></label></th>
									<td>
										<input type="text" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[story][heading]" id="story_heading" value="<?php echo esc_attr( $st['heading'] ); ?>" class="large-text" placeholder="e.g. From Generational Farms to State-of-the-Art Processing" />
									</td>
								</tr>
								<tr>
									<th scope="row"><label for="story_content"><?php esc_html_e( 'Story Narrative', 'spicecraft-core' ); ?></label></th>
									<td>
										<textarea name="<?php echo esc_attr( self::OPTION_NAME ); ?>[story][content]" id="story_content" rows="6" class="large-text"><?php echo esc_textarea( $st['content'] ); ?></textarea>
										<p class="description"><?php esc_html_e( 'Chronicle the journey, artisanal traditions, and transformation into modern manufacturing.', 'spicecraft-core' ); ?></p>
									</td>
								</tr>
								<tr>
									<th scope="row"><?php esc_html_e( 'Story Primary Image', 'spicecraft-core' ); ?></th>
									<td>
										<?php $this->render_media_field( self::OPTION_NAME . '[story][story_image_id]', $st['story_image_id'] ); ?>
									</td>
								</tr>
								<tr>
									<th scope="row"><?php esc_html_e( 'Secondary Image (Optional)', 'spicecraft-core' ); ?></th>
									<td>
										<?php $this->render_media_field( self::OPTION_NAME . '[story][secondary_image_id]', $st['secondary_image_id'] ); ?>
									</td>
								</tr>
								<tr>
									<th scope="row"><label for="story_quote"><?php esc_html_e( 'Heritage / Founder Quote', 'spicecraft-core' ); ?></label></th>
									<td>
										<textarea name="<?php echo esc_attr( self::OPTION_NAME ); ?>[story][quote_text]" id="story_quote" rows="2" class="large-text" placeholder="e.g. Authentic spices are not made; they are respected from soil to sealed pack."><?php echo esc_textarea( $st['quote_text'] ); ?></textarea>
									</td>
								</tr>
								<tr>
									<th scope="row"><label for="story_attr"><?php esc_html_e( 'Quote Attribution', 'spicecraft-core' ); ?></label></th>
									<td>
										<input type="text" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[story][quote_attribution]" id="story_attr" value="<?php echo esc_attr( $st['quote_attribution'] ); ?>" class="regular-text" placeholder="e.g. Founder & Master Blender" />
										<p class="description"><?php esc_html_e( 'Leave blank if attribution is not desired. Will not show empty attribution on frontend.', 'spicecraft-core' ); ?></p>
									</td>
								</tr>
							</table>
						</div>
					</div>

					<!-- Section 4: Vision & Mission -->
					<div class="postbox" style="margin-bottom: 20px;">
						<div class="postbox-header"><h2 class="hndle"><?php esc_html_e( '4. Vision & Mission', 'spicecraft-core' ); ?></h2></div>
						<div class="inside">
							<table class="form-table" role="presentation">
								<tr>
									<th scope="row"><label for="vm_eyebrow"><?php esc_html_e( 'Overarching Eyebrow', 'spicecraft-core' ); ?></label></th>
									<td>
										<input type="text" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[vision_mission][eyebrow]" id="vm_eyebrow" value="<?php echo esc_attr( $vm['eyebrow'] ); ?>" class="regular-text" placeholder="e.g. Guiding Purpose" />
									</td>
								</tr>
								<tr>
									<th scope="row"><label for="vm_heading"><?php esc_html_e( 'Section Heading', 'spicecraft-core' ); ?></label></th>
									<td>
										<input type="text" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[vision_mission][heading]" id="vm_heading" value="<?php echo esc_attr( $vm['heading'] ); ?>" class="large-text" placeholder="e.g. Purposed for Purity, Driven by Global Standards" />
									</td>
								</tr>
								<tr>
									<th scope="row"><?php esc_html_e( 'Vision Statement', 'spicecraft-core' ); ?></th>
									<td>
										<label style="margin-bottom: 8px; display: block;">
											<input type="checkbox" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[vision_mission][vision_enabled]" value="1" <?php checked( ! empty( $vm['vision_enabled'] ) ); ?> />
											<strong><?php esc_html_e( 'Enable Vision Card', 'spicecraft-core' ); ?></strong>
										</label>
										<input type="text" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[vision_mission][vision_heading]" value="<?php echo esc_attr( $vm['vision_heading'] ); ?>" placeholder="Vision Heading (e.g. Our Vision)" class="regular-text" style="display: block; margin-bottom: 8px; width: 100%; max-width: 400px;" />
										<textarea name="<?php echo esc_attr( self::OPTION_NAME ); ?>[vision_mission][vision_content]" rows="3" class="large-text" placeholder="Vision description statement..."><?php echo esc_textarea( $vm['vision_content'] ); ?></textarea>
									</td>
								</tr>
								<tr>
									<th scope="row"><?php esc_html_e( 'Mission Statement', 'spicecraft-core' ); ?></th>
									<td>
										<label style="margin-bottom: 8px; display: block;">
											<input type="checkbox" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[vision_mission][mission_enabled]" value="1" <?php checked( ! empty( $vm['mission_enabled'] ) ); ?> />
											<strong><?php esc_html_e( 'Enable Mission Card', 'spicecraft-core' ); ?></strong>
										</label>
										<input type="text" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[vision_mission][mission_heading]" value="<?php echo esc_attr( $vm['mission_heading'] ); ?>" placeholder="Mission Heading (e.g. Our Mission)" class="regular-text" style="display: block; margin-bottom: 8px; width: 100%; max-width: 400px;" />
										<textarea name="<?php echo esc_attr( self::OPTION_NAME ); ?>[vision_mission][mission_content]" rows="3" class="large-text" placeholder="Mission description statement..."><?php echo esc_textarea( $vm['mission_content'] ); ?></textarea>
									</td>
								</tr>
							</table>
						</div>
					</div>

					<!-- Section 5: Core Values -->
					<div class="postbox">
						<div class="postbox-header"><h2 class="hndle"><?php esc_html_e( '5. Core Values', 'spicecraft-core' ); ?></h2></div>
						<div class="inside">
							<table class="form-table" role="presentation">
								<tr>
									<th scope="row"><label for="val_eyebrow"><?php esc_html_e( 'Eyebrow', 'spicecraft-core' ); ?></label></th>
									<td>
										<input type="text" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[values][eyebrow]" id="val_eyebrow" value="<?php echo esc_attr( $val['eyebrow'] ); ?>" class="regular-text" placeholder="e.g. What We Stand For" />
									</td>
								</tr>
								<tr>
									<th scope="row"><label for="val_heading"><?php esc_html_e( 'Heading', 'spicecraft-core' ); ?></label></th>
									<td>
										<input type="text" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[values][heading]" id="val_heading" value="<?php echo esc_attr( $val['heading'] ); ?>" class="large-text" placeholder="e.g. Principles that Govern Every Batch" />
									</td>
								</tr>
								<tr>
									<th scope="row"><label for="val_desc"><?php esc_html_e( 'Description', 'spicecraft-core' ); ?></label></th>
									<td>
										<textarea name="<?php echo esc_attr( self::OPTION_NAME ); ?>[values][description]" id="val_desc" rows="2" class="large-text"><?php echo esc_textarea( $val['description'] ); ?></textarea>
									</td>
								</tr>
								<tr>
									<th scope="row"><?php esc_html_e( 'Repeatable Value Items', 'spicecraft-core' ); ?></th>
									<td>
										<p class="description" style="margin-bottom: 10px;"><?php esc_html_e( 'Add 3 to 6 company core values. Enter authentic business principles.', 'spicecraft-core' ); ?></p>
										<div id="sc-about-values-container">
											<?php
											$val_items = $val['items'] ?? array();
											if ( ! empty( $val_items ) && is_array( $val_items ) ) :
												foreach ( $val_items as $idx => $item ) :
													?>
													<div class="sc-repeatable-row sc-card" style="padding: 12px; margin-bottom: 8px; background: #f9f9f9; border: 1px solid #ccd0d4; border-radius: 4px;">
														<div style="display: flex; gap: 10px; width: 100%; align-items: center; margin-bottom: 8px;">
															<input type="text" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[values][items][<?php echo esc_attr( $idx ); ?>][icon]" value="<?php echo esc_attr( $item['icon'] ?? '' ); ?>" placeholder="Icon name (e.g. shield, leaf, award, heart)" style="width: 150px;" />
															<input type="text" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[values][items][<?php echo esc_attr( $idx ); ?>][title]" value="<?php echo esc_attr( $item['title'] ?? '' ); ?>" placeholder="Value Title (e.g. Uncompromising Purity)" class="regular-text" style="flex-grow: 1;" />
															<input type="number" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[values][items][<?php echo esc_attr( $idx ); ?>][order]" value="<?php echo esc_attr( $item['order'] ?? 10 ); ?>" placeholder="Order" style="width: 70px;" />
															<button type="button" class="button sc-remove-row-btn">&times;</button>
														</div>
														<div>
															<textarea name="<?php echo esc_attr( self::OPTION_NAME ); ?>[values][items][<?php echo esc_attr( $idx ); ?>][description]" placeholder="Explanation of this core principle..." rows="2" style="width: 100%;"><?php echo esc_textarea( $item['description'] ?? '' ); ?></textarea>
														</div>
													</div>
													<?php
												endforeach;
											endif;
											?>
										</div>
										<button type="button" class="button button-secondary" id="sc-add-about-value-btn">
											<span class="dashicons dashicons-plus-alt2" style="margin-top: -2px;"></span>
											<?php esc_html_e( 'Add Value Item', 'spicecraft-core' ); ?>
										</button>
									</td>
								</tr>
							</table>
						</div>
					</div>

				<?php
				// TAB 4: PHILOSOPHY PILLARS (Quality, Sourcing, Manufacturing)
				elseif ( 'pillars' === $active_tab ) :
					$q   = $settings['quality'];
					$src = $settings['sourcing'];
					$mfg = $settings['manufacturing'];
					?>
					<!-- Section 6: Quality Philosophy -->
					<div class="postbox" style="margin-bottom: 20px;">
						<div class="postbox-header"><h2 class="hndle"><?php esc_html_e( '6. Quality Philosophy (Brand Level)', 'spicecraft-core' ); ?></h2></div>
						<div class="inside">
							<p class="description" style="margin-bottom: 12px;"><?php esc_html_e( 'A concise brand-level introduction to quality standards. Do not invent arbitrary laboratory claims.', 'spicecraft-core' ); ?></p>
							<table class="form-table" role="presentation">
								<tr>
									<th scope="row"><label for="q_eyebrow"><?php esc_html_e( 'Eyebrow', 'spicecraft-core' ); ?></label></th>
									<td>
										<input type="text" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[quality][eyebrow]" id="q_eyebrow" value="<?php echo esc_attr( $q['eyebrow'] ); ?>" class="regular-text" placeholder="e.g. Quality Philosophy" />
									</td>
								</tr>
								<tr>
									<th scope="row"><label for="q_heading"><?php esc_html_e( 'Heading', 'spicecraft-core' ); ?></label></th>
									<td>
										<input type="text" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[quality][heading]" id="q_heading" value="<?php echo esc_attr( $q['heading'] ); ?>" class="large-text" placeholder="e.g. Total Quality Control from Seed to Seal" />
									</td>
								</tr>
								<tr>
									<th scope="row"><label for="q_desc"><?php esc_html_e( 'Description', 'spicecraft-core' ); ?></label></th>
									<td>
										<textarea name="<?php echo esc_attr( self::OPTION_NAME ); ?>[quality][description]" id="q_desc" rows="3" class="large-text"><?php echo esc_textarea( $q['description'] ); ?></textarea>
									</td>
								</tr>
								<tr>
									<th scope="row"><?php esc_html_e( 'Showcase Image', 'spicecraft-core' ); ?></th>
									<td>
										<?php $this->render_media_field( self::OPTION_NAME . '[quality][image_id]', $q['image_id'] ); ?>
									</td>
								</tr>
								<tr>
									<th scope="row"><?php esc_html_e( 'Quality Protocols / Points', 'spicecraft-core' ); ?></th>
									<td>
										<div id="sc-about-quality-points-container">
											<?php
											$q_points = $q['points'] ?? array();
											if ( ! empty( $q_points ) && is_array( $q_points ) ) :
												foreach ( $q_points as $idx => $pt ) :
													?>
													<div class="sc-repeatable-row sc-card" style="padding: 10px; margin-bottom: 6px; background: #f9f9f9; border: 1px solid #ccd0d4; border-radius: 4px;">
														<div style="display: flex; gap: 8px; width: 100%; margin-bottom: 6px;">
															<input type="text" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[quality][points][<?php echo esc_attr( $idx ); ?>][title]" value="<?php echo esc_attr( $pt['title'] ?? '' ); ?>" placeholder="Protocol Title (e.g. Gas Chromatography Testing)" class="regular-text" style="flex-grow: 1;" />
															<button type="button" class="button sc-remove-row-btn">&times;</button>
														</div>
														<textarea name="<?php echo esc_attr( self::OPTION_NAME ); ?>[quality][points][<?php echo esc_attr( $idx ); ?>][text]" placeholder="Point details..." rows="2" style="width: 100%;"><?php echo esc_textarea( $pt['text'] ?? '' ); ?></textarea>
													</div>
													<?php
												endforeach;
											endif;
											?>
										</div>
										<button type="button" class="button button-secondary" id="sc-add-about-quality-point-btn">
											<span class="dashicons dashicons-plus-alt2" style="margin-top: -2px;"></span>
											<?php esc_html_e( 'Add Quality Point', 'spicecraft-core' ); ?>
										</button>
									</td>
								</tr>
								<tr>
									<th scope="row"><?php esc_html_e( 'CTA Link (Optional)', 'spicecraft-core' ); ?></th>
									<td>
										<input type="text" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[quality][cta_label]" value="<?php echo esc_attr( $q['cta_label'] ); ?>" placeholder="Button Label" class="regular-text" style="width: 200px;" />
										<input type="text" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[quality][cta_url]" value="<?php echo esc_attr( $q['cta_url'] ); ?>" placeholder="Destination URL" class="regular-text" style="width: 250px;" />
									</td>
								</tr>
							</table>
						</div>
					</div>

					<!-- Section 7: Sourcing Philosophy -->
					<div class="postbox" style="margin-bottom: 20px;">
						<div class="postbox-header"><h2 class="hndle"><?php esc_html_e( '7. Sourcing Philosophy (Brand Level)', 'spicecraft-core' ); ?></h2></div>
						<div class="inside">
							<table class="form-table" role="presentation">
								<tr>
									<th scope="row"><label for="src_eyebrow"><?php esc_html_e( 'Eyebrow', 'spicecraft-core' ); ?></label></th>
									<td>
										<input type="text" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[sourcing][eyebrow]" id="src_eyebrow" value="<?php echo esc_attr( $src['eyebrow'] ); ?>" class="regular-text" placeholder="e.g. Sourcing Philosophy" />
									</td>
								</tr>
								<tr>
									<th scope="row"><label for="src_heading"><?php esc_html_e( 'Heading', 'spicecraft-core' ); ?></label></th>
									<td>
										<input type="text" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[sourcing][heading]" id="src_heading" value="<?php echo esc_attr( $src['heading'] ); ?>" class="large-text" placeholder="e.g. Single-Origin Sourcing Direct from Proven Soil" />
									</td>
								</tr>
								<tr>
									<th scope="row"><label for="src_desc"><?php esc_html_e( 'Description', 'spicecraft-core' ); ?></label></th>
									<td>
										<textarea name="<?php echo esc_attr( self::OPTION_NAME ); ?>[sourcing][description]" id="src_desc" rows="3" class="large-text"><?php echo esc_textarea( $src['description'] ); ?></textarea>
									</td>
								</tr>
								<tr>
									<th scope="row"><?php esc_html_e( 'Primary Farm Image', 'spicecraft-core' ); ?></th>
									<td>
										<?php $this->render_media_field( self::OPTION_NAME . '[sourcing][image_id]', $src['image_id'] ); ?>
									</td>
								</tr>
								<tr>
									<th scope="row"><?php esc_html_e( 'Supporting Image (Optional)', 'spicecraft-core' ); ?></th>
									<td>
										<?php $this->render_media_field( self::OPTION_NAME . '[sourcing][support_image_id]', $src['support_image_id'] ); ?>
									</td>
								</tr>
								<tr>
									<th scope="row"><?php esc_html_e( 'Sourcing Highlights / Points', 'spicecraft-core' ); ?></th>
									<td>
										<div id="sc-about-sourcing-points-container">
											<?php
											$src_points = $src['points'] ?? array();
											if ( ! empty( $src_points ) && is_array( $src_points ) ) :
												foreach ( $src_points as $idx => $pt ) :
													?>
													<div class="sc-repeatable-row sc-card" style="padding: 10px; margin-bottom: 6px; background: #f9f9f9; border: 1px solid #ccd0d4; border-radius: 4px;">
														<div style="display: flex; gap: 8px; width: 100%; margin-bottom: 6px;">
															<input type="text" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[sourcing][points][<?php echo esc_attr( $idx ); ?>][title]" value="<?php echo esc_attr( $pt['title'] ?? '' ); ?>" placeholder="Highlight (e.g. Zero Middlemen)" class="regular-text" style="flex-grow: 1;" />
															<button type="button" class="button sc-remove-row-btn">&times;</button>
														</div>
														<textarea name="<?php echo esc_attr( self::OPTION_NAME ); ?>[sourcing][points][<?php echo esc_attr( $idx ); ?>][text]" placeholder="Point details..." rows="2" style="width: 100%;"><?php echo esc_textarea( $pt['text'] ?? '' ); ?></textarea>
													</div>
													<?php
												endforeach;
											endif;
											?>
										</div>
										<button type="button" class="button button-secondary" id="sc-add-about-sourcing-point-btn">
											<span class="dashicons dashicons-plus-alt2" style="margin-top: -2px;"></span>
											<?php esc_html_e( 'Add Sourcing Point', 'spicecraft-core' ); ?>
										</button>
									</td>
								</tr>
								<tr>
									<th scope="row"><?php esc_html_e( 'CTA Link (Optional)', 'spicecraft-core' ); ?></th>
									<td>
										<input type="text" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[sourcing][cta_label]" value="<?php echo esc_attr( $src['cta_label'] ); ?>" placeholder="Button Label" class="regular-text" style="width: 200px;" />
										<input type="text" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[sourcing][cta_url]" value="<?php echo esc_attr( $src['cta_url'] ); ?>" placeholder="Destination URL" class="regular-text" style="width: 250px;" />
									</td>
								</tr>
							</table>
						</div>
					</div>

					<!-- Section 8: Manufacturing Philosophy -->
					<div class="postbox">
						<div class="postbox-header"><h2 class="hndle"><?php esc_html_e( '8. Manufacturing Philosophy (Brand Level)', 'spicecraft-core' ); ?></h2></div>
						<div class="inside">
							<table class="form-table" role="presentation">
								<tr>
									<th scope="row"><label for="mfg_eyebrow"><?php esc_html_e( 'Eyebrow', 'spicecraft-core' ); ?></label></th>
									<td>
										<input type="text" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[manufacturing][eyebrow]" id="mfg_eyebrow" value="<?php echo esc_attr( $mfg['eyebrow'] ); ?>" class="regular-text" placeholder="e.g. Modern Processing" />
									</td>
								</tr>
								<tr>
									<th scope="row"><label for="mfg_heading"><?php esc_html_e( 'Heading', 'spicecraft-core' ); ?></label></th>
									<td>
										<input type="text" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[manufacturing][heading]" id="mfg_heading" value="<?php echo esc_attr( $mfg['heading'] ); ?>" class="large-text" placeholder="e.g. Advanced FMCG Cleanroom Milling & Processing" />
									</td>
								</tr>
								<tr>
									<th scope="row"><label for="mfg_desc"><?php esc_html_e( 'Description', 'spicecraft-core' ); ?></label></th>
									<td>
										<textarea name="<?php echo esc_attr( self::OPTION_NAME ); ?>[manufacturing][description]" id="mfg_desc" rows="3" class="large-text"><?php echo esc_textarea( $mfg['description'] ); ?></textarea>
									</td>
								</tr>
								<tr>
									<th scope="row"><?php esc_html_e( 'Main Facility Image', 'spicecraft-core' ); ?></th>
									<td>
										<?php $this->render_media_field( self::OPTION_NAME . '[manufacturing][main_image_id]', $mfg['main_image_id'] ); ?>
									</td>
								</tr>
								<tr>
									<th scope="row"><label for="mfg_video"><?php esc_html_e( 'Optional Video URL', 'spicecraft-core' ); ?></label></th>
									<td>
										<input type="url" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[manufacturing][video_url]" id="mfg_video" value="<?php echo esc_url( $mfg['video_url'] ); ?>" class="large-text" placeholder="https://www.youtube.com/watch?v=..." />
										<p class="description"><?php esc_html_e( 'Optional link to facility tour or production overview video.', 'spicecraft-core' ); ?></p>
									</td>
								</tr>
								<tr>
									<th scope="row"><?php esc_html_e( 'Operational Highlights', 'spicecraft-core' ); ?></th>
									<td>
										<div id="sc-about-mfg-highlights-container">
											<?php
											$mfg_hl = $mfg['highlights'] ?? array();
											if ( ! empty( $mfg_hl ) && is_array( $mfg_hl ) ) :
												foreach ( $mfg_hl as $idx => $hl ) :
													?>
													<div class="sc-repeatable-row sc-card" style="padding: 10px; margin-bottom: 6px; background: #f9f9f9; border: 1px solid #ccd0d4; border-radius: 4px;">
														<div style="display: flex; gap: 8px; width: 100%; margin-bottom: 6px;">
															<input type="text" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[manufacturing][highlights][<?php echo esc_attr( $idx ); ?>][title]" value="<?php echo esc_attr( $hl['title'] ?? '' ); ?>" placeholder="Highlight (e.g. Cryogenic Low-Temp Milling)" class="regular-text" style="flex-grow: 1;" />
															<button type="button" class="button sc-remove-row-btn">&times;</button>
														</div>
														<textarea name="<?php echo esc_attr( self::OPTION_NAME ); ?>[manufacturing][highlights][<?php echo esc_attr( $idx ); ?>][text]" placeholder="Short explanation..." rows="2" style="width: 100%;"><?php echo esc_textarea( $hl['text'] ?? '' ); ?></textarea>
													</div>
													<?php
												endforeach;
											endif;
											?>
										</div>
										<button type="button" class="button button-secondary" id="sc-add-about-mfg-highlight-btn">
											<span class="dashicons dashicons-plus-alt2" style="margin-top: -2px;"></span>
											<?php esc_html_e( 'Add Manufacturing Highlight', 'spicecraft-core' ); ?>
										</button>
									</td>
								</tr>
								<tr>
									<th scope="row"><?php esc_html_e( 'CTA Link (Optional)', 'spicecraft-core' ); ?></th>
									<td>
										<input type="text" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[manufacturing][cta_label]" value="<?php echo esc_attr( $mfg['cta_label'] ); ?>" placeholder="Button Label" class="regular-text" style="width: 200px;" />
										<input type="text" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[manufacturing][cta_url]" value="<?php echo esc_attr( $mfg['cta_url'] ); ?>" placeholder="Destination URL" class="regular-text" style="width: 250px;" />
									</td>
								</tr>
							</table>
						</div>
					</div>

				<?php
				// TAB 5: JOURNEY & STATS
				elseif ( 'journey' === $active_tab ) :
					$stat = $settings['statistics'];
					$ms   = $settings['milestones'];
					?>
					<!-- Section 9: Company Statistics -->
					<div class="postbox" style="margin-bottom: 20px;">
						<div class="postbox-header"><h2 class="hndle"><?php esc_html_e( '9. Company Statistics (Optional)', 'spicecraft-core' ); ?></h2></div>
						<div class="inside">
							<div class="notice notice-warning inline" style="margin-bottom: 12px;">
								<p><strong><?php esc_html_e( 'Critical Rule — No Fabricated Data:', 'spicecraft-core' ); ?></strong> <?php esc_html_e( 'Only enter genuine, verified business numbers. If no items are configured, this section is completely hidden from the frontend.', 'spicecraft-core' ); ?></p>
							</div>
							<table class="form-table" role="presentation">
								<tr>
									<th scope="row"><label for="stat_eyebrow"><?php esc_html_e( 'Eyebrow', 'spicecraft-core' ); ?></label></th>
									<td>
										<input type="text" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[statistics][eyebrow]" id="stat_eyebrow" value="<?php echo esc_attr( $stat['eyebrow'] ); ?>" class="regular-text" placeholder="e.g. Proven Scale" />
									</td>
								</tr>
								<tr>
									<th scope="row"><label for="stat_heading"><?php esc_html_e( 'Heading', 'spicecraft-core' ); ?></label></th>
									<td>
										<input type="text" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[statistics][heading]" id="stat_heading" value="<?php echo esc_attr( $stat['heading'] ); ?>" class="large-text" placeholder="e.g. Numbers That Reflect Our Standards" />
									</td>
								</tr>
								<tr>
									<th scope="row"><?php esc_html_e( 'Repeatable Statistics', 'spicecraft-core' ); ?></th>
									<td>
										<div id="sc-about-stats-container">
											<?php
											$stats_items = $stat['items'] ?? array();
											if ( ! empty( $stats_items ) && is_array( $stats_items ) ) :
												foreach ( $stats_items as $idx => $st_item ) :
													?>
													<div class="sc-repeatable-row" style="display: flex; gap: 8px; align-items: center; margin-bottom: 8px; background: #f9f9f9; padding: 8px; border: 1px solid #ccd0d4; border-radius: 4px;">
														<input type="text" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[statistics][items][<?php echo esc_attr( $idx ); ?>][value]" value="<?php echo esc_attr( $st_item['value'] ?? '' ); ?>" placeholder="Value (e.g. 25, 100)" style="width: 100px;" />
														<input type="text" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[statistics][items][<?php echo esc_attr( $idx ); ?>][suffix]" value="<?php echo esc_attr( $st_item['suffix'] ?? '' ); ?>" placeholder="Suffix (+, %, K)" style="width: 80px;" />
														<input type="text" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[statistics][items][<?php echo esc_attr( $idx ); ?>][label]" value="<?php echo esc_attr( $st_item['label'] ?? '' ); ?>" placeholder="Label (e.g. Years of Heritage)" class="regular-text" style="flex-grow: 1;" />
														<input type="number" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[statistics][items][<?php echo esc_attr( $idx ); ?>][order]" value="<?php echo esc_attr( $st_item['order'] ?? 10 ); ?>" placeholder="Order" style="width: 60px;" />
														<button type="button" class="button sc-remove-row-btn">&times;</button>
													</div>
													<?php
												endforeach;
											endif;
											?>
										</div>
										<button type="button" class="button button-secondary" id="sc-add-about-stat-btn">
											<span class="dashicons dashicons-plus-alt2" style="margin-top: -2px;"></span>
											<?php esc_html_e( 'Add Statistic Item', 'spicecraft-core' ); ?>
										</button>
									</td>
								</tr>
							</table>
						</div>
					</div>

					<!-- Section 10: Journey & Milestones -->
					<div class="postbox">
						<div class="postbox-header"><h2 class="hndle"><?php esc_html_e( '10. Journey & Milestones', 'spicecraft-core' ); ?></h2></div>
						<div class="inside">
							<table class="form-table" role="presentation">
								<tr>
									<th scope="row"><label for="ms_eyebrow"><?php esc_html_e( 'Eyebrow', 'spicecraft-core' ); ?></label></th>
									<td>
										<input type="text" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[milestones][eyebrow]" id="ms_eyebrow" value="<?php echo esc_attr( $ms['eyebrow'] ); ?>" class="regular-text" placeholder="e.g. Our Evolution" />
									</td>
								</tr>
								<tr>
									<th scope="row"><label for="ms_heading"><?php esc_html_e( 'Heading', 'spicecraft-core' ); ?></label></th>
									<td>
										<input type="text" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[milestones][heading]" id="ms_heading" value="<?php echo esc_attr( $ms['heading'] ); ?>" class="large-text" placeholder="e.g. Key Milestones in Our Spice Journey" />
									</td>
								</tr>
								<tr>
									<th scope="row"><label for="ms_desc"><?php esc_html_e( 'Description', 'spicecraft-core' ); ?></label></th>
									<td>
										<textarea name="<?php echo esc_attr( self::OPTION_NAME ); ?>[milestones][description]" id="ms_desc" rows="2" class="large-text"><?php echo esc_textarea( $ms['description'] ); ?></textarea>
									</td>
								</tr>
								<tr>
									<th scope="row"><?php esc_html_e( 'Repeatable Milestones', 'spicecraft-core' ); ?></th>
									<td>
										<div id="sc-about-milestones-container">
											<?php
											$ms_items = $ms['items'] ?? array();
											if ( ! empty( $ms_items ) && is_array( $ms_items ) ) :
												foreach ( $ms_items as $idx => $ms_row ) :
													?>
													<div class="sc-repeatable-row sc-card" style="padding: 12px; margin-bottom: 8px; background: #f9f9f9; border: 1px solid #ccd0d4; border-radius: 4px;">
														<div style="display: flex; gap: 8px; align-items: center; margin-bottom: 8px;">
															<input type="text" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[milestones][items][<?php echo esc_attr( $idx ); ?>][date_label]" value="<?php echo esc_attr( $ms_row['date_label'] ?? '' ); ?>" placeholder="Year / Label (e.g. 1998, The Beginning)" style="width: 180px;" />
															<input type="text" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[milestones][items][<?php echo esc_attr( $idx ); ?>][title]" value="<?php echo esc_attr( $ms_row['title'] ?? '' ); ?>" placeholder="Milestone Title" class="regular-text" style="flex-grow: 1;" />
															<input type="number" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[milestones][items][<?php echo esc_attr( $idx ); ?>][order]" value="<?php echo esc_attr( $ms_row['order'] ?? 10 ); ?>" placeholder="Order" style="width: 60px;" />
															<button type="button" class="button sc-remove-row-btn">&times;</button>
														</div>
														<div>
															<textarea name="<?php echo esc_attr( self::OPTION_NAME ); ?>[milestones][items][<?php echo esc_attr( $idx ); ?>][description]" placeholder="Milestone details..." rows="2" style="width: 100%;"><?php echo esc_textarea( $ms_row['description'] ?? '' ); ?></textarea>
														</div>
													</div>
													<?php
												endforeach;
											endif;
											?>
										</div>
										<button type="button" class="button button-secondary" id="sc-add-about-milestone-btn">
											<span class="dashicons dashicons-plus-alt2" style="margin-top: -2px;"></span>
											<?php esc_html_e( 'Add Milestone', 'spicecraft-core' ); ?>
										</button>
									</td>
								</tr>
							</table>
						</div>
					</div>

				<?php
				// TAB 6: LEADERSHIP & PEOPLE
				elseif ( 'leadership' === $active_tab ) :
					$lead = $settings['leadership'];
					?>
					<div class="postbox">
						<div class="postbox-header"><h2 class="hndle"><?php esc_html_e( '11. Leadership & People', 'spicecraft-core' ); ?></h2></div>
						<div class="inside">
							<div style="display: flex; justify-content: flex-end; margin-bottom: 12px;">
								<a href="<?php echo esc_url( admin_url( 'post-new.php?post_type=spicecraft_team' ) ); ?>" class="button button-primary" target="_blank">
									<span class="dashicons dashicons-plus-alt2" style="margin-top: -2px;"></span>
									<?php esc_html_e( 'Add New Team Member', 'spicecraft-core' ); ?>
								</a>
							</div>
							<table class="form-table" role="presentation">
								<tr>
									<th scope="row"><label for="lead_eyebrow"><?php esc_html_e( 'Eyebrow', 'spicecraft-core' ); ?></label></th>
									<td>
										<input type="text" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[leadership][eyebrow]" id="lead_eyebrow" value="<?php echo esc_attr( $lead['eyebrow'] ); ?>" class="regular-text" placeholder="e.g. Leadership & Craft" />
									</td>
								</tr>
								<tr>
									<th scope="row"><label for="lead_heading"><?php esc_html_e( 'Heading', 'spicecraft-core' ); ?></label></th>
									<td>
										<input type="text" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[leadership][heading]" id="lead_heading" value="<?php echo esc_attr( $lead['heading'] ); ?>" class="large-text" placeholder="e.g. Guided by Generational Passion and Scientific Rigor" />
									</td>
								</tr>
								<tr>
									<th scope="row"><label for="lead_desc"><?php esc_html_e( 'Description', 'spicecraft-core' ); ?></label></th>
									<td>
										<textarea name="<?php echo esc_attr( self::OPTION_NAME ); ?>[leadership][description]" id="lead_desc" rows="3" class="large-text"><?php echo esc_textarea( $lead['description'] ); ?></textarea>
									</td>
								</tr>
								<tr>
									<th scope="row"><?php esc_html_e( 'Select Team Members', 'spicecraft-core' ); ?></th>
									<td>
										<?php $this->render_post_multiselect( 'spicecraft_team', self::OPTION_NAME . '[leadership][selected_ids]', $lead['selected_ids'] ); ?>
										<p class="description"><?php esc_html_e( 'Leave all unchecked to display all published team members ordered by display priority.', 'spicecraft-core' ); ?></p>
									</td>
								</tr>
								<tr>
									<th scope="row"><label for="lead_limit"><?php esc_html_e( 'Display Limit', 'spicecraft-core' ); ?></label></th>
									<td>
										<input type="number" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[leadership][limit]" id="lead_limit" value="<?php echo esc_attr( $lead['limit'] ); ?>" class="small-text" min="1" max="20" />
									</td>
								</tr>
							</table>
						</div>
					</div>

				<?php
				// TAB 7: TRUST & PRODUCTS
				elseif ( 'trust' === $active_tab ) :
					$cert = $settings['certifications'];
					$prod = $settings['products'];
					?>
					<!-- Section 12: Certifications -->
					<div class="postbox" style="margin-bottom: 20px;">
						<div class="postbox-header"><h2 class="hndle"><?php esc_html_e( '12. Certifications & Trust', 'spicecraft-core' ); ?></h2></div>
						<div class="inside">
							<p class="description" style="margin-bottom: 12px;"><?php esc_html_e( 'Consumes authentic terms from Product Certifications taxonomy. No duplicate credentials or fake logos.', 'spicecraft-core' ); ?></p>
							<table class="form-table" role="presentation">
								<tr>
									<th scope="row"><label for="cert_eyebrow"><?php esc_html_e( 'Eyebrow', 'spicecraft-core' ); ?></label></th>
									<td>
										<input type="text" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[certifications][eyebrow]" id="cert_eyebrow" value="<?php echo esc_attr( $cert['eyebrow'] ); ?>" class="regular-text" placeholder="e.g. Certified Compliance" />
									</td>
								</tr>
								<tr>
									<th scope="row"><label for="cert_heading"><?php esc_html_e( 'Heading', 'spicecraft-core' ); ?></label></th>
									<td>
										<input type="text" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[certifications][heading]" id="cert_heading" value="<?php echo esc_attr( $cert['heading'] ); ?>" class="large-text" placeholder="e.g. Accredited by Global Food Safety Standards" />
									</td>
								</tr>
								<tr>
									<th scope="row"><label for="cert_desc"><?php esc_html_e( 'Description', 'spicecraft-core' ); ?></label></th>
									<td>
										<textarea name="<?php echo esc_attr( self::OPTION_NAME ); ?>[certifications][description]" id="cert_desc" rows="2" class="large-text"><?php echo esc_textarea( $cert['description'] ); ?></textarea>
									</td>
								</tr>
								<tr>
									<th scope="row"><?php esc_html_e( 'Select Certifications', 'spicecraft-core' ); ?></th>
									<td>
										<?php $this->render_taxonomy_multiselect( 'spicecraft_certification', self::OPTION_NAME . '[certifications][selected_ids]', $cert['selected_ids'] ); ?>
										<p class="description"><?php esc_html_e( 'Leave unchecked to display all configured certifications.', 'spicecraft-core' ); ?></p>
									</td>
								</tr>
								<tr>
									<th scope="row"><?php esc_html_e( 'CTA Link (Optional)', 'spicecraft-core' ); ?></th>
									<td>
										<input type="text" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[certifications][cta_label]" value="<?php echo esc_attr( $cert['cta_label'] ); ?>" placeholder="Button Label" class="regular-text" style="width: 200px;" />
										<input type="text" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[certifications][cta_url]" value="<?php echo esc_attr( $cert['cta_url'] ); ?>" placeholder="Destination URL" class="regular-text" style="width: 250px;" />
									</td>
								</tr>
							</table>
						</div>
					</div>

					<!-- Section 13: Product Connection -->
					<div class="postbox">
						<div class="postbox-header"><h2 class="hndle"><?php esc_html_e( '13. Product Connection', 'spicecraft-core' ); ?></h2></div>
						<div class="inside">
							<p class="description" style="margin-bottom: 12px;"><?php esc_html_e( 'Connect visitors back to the spice catalog using WooCommerce products or product categories.', 'spicecraft-core' ); ?></p>
							<table class="form-table" role="presentation">
								<tr>
									<th scope="row"><label for="prod_eyebrow"><?php esc_html_e( 'Eyebrow', 'spicecraft-core' ); ?></label></th>
									<td>
										<input type="text" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[products][eyebrow]" id="prod_eyebrow" value="<?php echo esc_attr( $prod['eyebrow'] ); ?>" class="regular-text" placeholder="e.g. Taste the Purity" />
									</td>
								</tr>
								<tr>
									<th scope="row"><label for="prod_heading"><?php esc_html_e( 'Heading', 'spicecraft-core' ); ?></label></th>
									<td>
										<input type="text" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[products][heading]" id="prod_heading" value="<?php echo esc_attr( $prod['heading'] ); ?>" class="large-text" placeholder="e.g. Explore Our Range of Single-Origin Spices & Blends" />
									</td>
								</tr>
								<tr>
									<th scope="row"><label for="prod_desc"><?php esc_html_e( 'Description', 'spicecraft-core' ); ?></label></th>
									<td>
										<textarea name="<?php echo esc_attr( self::OPTION_NAME ); ?>[products][description]" id="prod_desc" rows="2" class="large-text"><?php echo esc_textarea( $prod['description'] ); ?></textarea>
									</td>
								</tr>
								<tr>
									<th scope="row"><label for="prod_source"><?php esc_html_e( 'Content Source', 'spicecraft-core' ); ?></label></th>
									<td>
										<select name="<?php echo esc_attr( self::OPTION_NAME ); ?>[products][source]" id="prod_source">
											<option value="categories" <?php selected( $prod['source'], 'categories' ); ?>><?php esc_html_e( 'Product Categories Grid', 'spicecraft-core' ); ?></option>
											<option value="products" <?php selected( $prod['source'], 'products' ); ?>><?php esc_html_e( 'Featured / Selected Products Grid', 'spicecraft-core' ); ?></option>
										</select>
									</td>
								</tr>
								<tr>
									<th scope="row"><label for="prod_limit"><?php esc_html_e( 'Display Limit', 'spicecraft-core' ); ?></label></th>
									<td>
										<input type="number" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[products][limit]" id="prod_limit" value="<?php echo esc_attr( $prod['limit'] ); ?>" class="small-text" min="1" max="12" />
									</td>
								</tr>
								<tr>
									<th scope="row"><?php esc_html_e( 'CTA Link', 'spicecraft-core' ); ?></th>
									<td>
										<input type="text" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[products][cta_label]" value="<?php echo esc_attr( $prod['cta_label'] ); ?>" placeholder="Label (e.g. Browse Full Catalog)" class="regular-text" style="width: 200px;" />
										<input type="text" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[products][cta_url]" value="<?php echo esc_attr( $prod['cta_url'] ); ?>" placeholder="URL (e.g. /shop/)" class="regular-text" style="width: 250px;" />
									</td>
								</tr>
							</table>
						</div>
					</div>

				<?php
				// TAB 8: BUSINESS CTAS
				elseif ( 'cta' === $active_tab ) :
					$b2b  = $settings['b2b_cta'];
					$fcta = $settings['final_cta'];
					?>
					<!-- Section 14: B2B & Export CTA -->
					<div class="postbox" style="margin-bottom: 20px;">
						<div class="postbox-header"><h2 class="hndle"><?php esc_html_e( '14. B2B / Export Call-To-Action', 'spicecraft-core' ); ?></h2></div>
						<div class="inside">
							<table class="form-table" role="presentation">
								<tr>
									<th scope="row"><label for="b2b_eyebrow"><?php esc_html_e( 'Eyebrow', 'spicecraft-core' ); ?></label></th>
									<td>
										<input type="text" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[b2b_cta][eyebrow]" id="b2b_eyebrow" value="<?php echo esc_attr( $b2b['eyebrow'] ); ?>" class="regular-text" placeholder="e.g. Institutional & Export Trade" />
									</td>
								</tr>
								<tr>
									<th scope="row"><label for="b2b_heading"><?php esc_html_e( 'Heading', 'spicecraft-core' ); ?></label></th>
									<td>
										<input type="text" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[b2b_cta][heading]" id="b2b_heading" value="<?php echo esc_attr( $b2b['heading'] ); ?>" class="large-text" placeholder="e.g. Bulk Procurement & Custom Private Label Formulation" />
									</td>
								</tr>
								<tr>
									<th scope="row"><label for="b2b_desc"><?php esc_html_e( 'Description', 'spicecraft-core' ); ?></label></th>
									<td>
										<textarea name="<?php echo esc_attr( self::OPTION_NAME ); ?>[b2b_cta][description]" id="b2b_desc" rows="3" class="large-text"><?php echo esc_textarea( $b2b['description'] ); ?></textarea>
									</td>
								</tr>
								<tr>
									<th scope="row"><?php esc_html_e( 'Background Image', 'spicecraft-core' ); ?></th>
									<td>
										<?php $this->render_media_field( self::OPTION_NAME . '[b2b_cta][bg_image_id]', $b2b['bg_image_id'] ); ?>
									</td>
								</tr>
								<tr>
									<th scope="row"><?php esc_html_e( 'Primary CTA', 'spicecraft-core' ); ?></th>
									<td>
										<input type="text" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[b2b_cta][primary_cta_label]" value="<?php echo esc_attr( $b2b['primary_cta_label'] ); ?>" placeholder="Label (e.g. Request Commercial Quote)" class="regular-text" style="width: 200px;" />
										<input type="text" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[b2b_cta][primary_cta_url]" value="<?php echo esc_attr( $b2b['primary_cta_url'] ); ?>" placeholder="Destination URL or mailto:" class="regular-text" style="width: 250px;" />
									</td>
								</tr>
								<tr>
									<th scope="row"><?php esc_html_e( 'Secondary CTA', 'spicecraft-core' ); ?></th>
									<td>
										<input type="text" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[b2b_cta][secondary_cta_label]" value="<?php echo esc_attr( $b2b['secondary_cta_label'] ); ?>" placeholder="Label (e.g. Download Specifications)" class="regular-text" style="width: 200px;" />
										<input type="text" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[b2b_cta][secondary_cta_url]" value="<?php echo esc_attr( $b2b['secondary_cta_url'] ); ?>" placeholder="Destination URL" class="regular-text" style="width: 250px;" />
									</td>
								</tr>
								<tr>
									<th scope="row"><?php esc_html_e( 'WhatsApp Trade Desk', 'spicecraft-core' ); ?></th>
									<td>
										<label>
											<input type="checkbox" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[b2b_cta][enable_whatsapp]" value="1" <?php checked( ! empty( $b2b['enable_whatsapp'] ) ); ?> />
											<?php esc_html_e( 'Display direct WhatsApp business enquiry button (uses global WhatsApp number)', 'spicecraft-core' ); ?>
										</label>
									</td>
								</tr>
							</table>
						</div>
					</div>

					<!-- Section 15: Final Contact CTA -->
					<div class="postbox">
						<div class="postbox-header"><h2 class="hndle"><?php esc_html_e( '15. Final Contact CTA', 'spicecraft-core' ); ?></h2></div>
						<div class="inside">
							<p class="description" style="margin-bottom: 12px;"><?php esc_html_e( 'Provides direct communication channels. Contact phone, email, and WhatsApp numbers are reused automatically from SpiceCraft -> Global Settings.', 'spicecraft-core' ); ?></p>
							<table class="form-table" role="presentation">
								<tr>
									<th scope="row"><label for="fcta_heading"><?php esc_html_e( 'Heading', 'spicecraft-core' ); ?></label></th>
									<td>
										<input type="text" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[final_cta][heading]" id="fcta_heading" value="<?php echo esc_attr( $fcta['heading'] ); ?>" class="large-text" placeholder="e.g. Ready to Experience Authentic Spice Purity?" />
									</td>
								</tr>
								<tr>
									<th scope="row"><label for="fcta_desc"><?php esc_html_e( 'Description', 'spicecraft-core' ); ?></label></th>
									<td>
										<textarea name="<?php echo esc_attr( self::OPTION_NAME ); ?>[final_cta][description]" id="fcta_desc" rows="3" class="large-text"><?php echo esc_textarea( $fcta['description'] ); ?></textarea>
									</td>
								</tr>
								<tr>
									<th scope="row"><?php esc_html_e( 'Primary Button', 'spicecraft-core' ); ?></th>
									<td>
										<input type="text" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[final_cta][primary_cta_label]" value="<?php echo esc_attr( $fcta['primary_cta_label'] ); ?>" placeholder="Label (e.g. Contact Our Team)" class="regular-text" style="width: 200px;" />
										<input type="text" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[final_cta][primary_cta_url]" value="<?php echo esc_attr( $fcta['primary_cta_url'] ); ?>" placeholder="URL or mailto:" class="regular-text" style="width: 250px;" />
									</td>
								</tr>
								<tr>
									<th scope="row"><?php esc_html_e( 'Direct Communication Channels', 'spicecraft-core' ); ?></th>
									<td>
										<label style="display: block; margin-bottom: 6px;">
											<input type="checkbox" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[final_cta][enable_whatsapp]" value="1" <?php checked( ! empty( $fcta['enable_whatsapp'] ) ); ?> />
											<?php esc_html_e( 'Enable WhatsApp Direct Action', 'spicecraft-core' ); ?>
										</label>
										<label style="display: block;">
											<input type="checkbox" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[final_cta][enable_email]" value="1" <?php checked( ! empty( $fcta['enable_email'] ) ); ?> />
											<?php esc_html_e( 'Enable Direct Email Action (uses General Email from Global Settings)', 'spicecraft-core' ); ?>
										</label>
									</td>
								</tr>
							</table>
						</div>
					</div>
				<?php endif; ?>

				<?php submit_button( __( 'Save About Us Settings', 'spicecraft-core' ) ); ?>
			</form>
		</div>
		<?php
	}

	/**
	 * Render WordPress Media Uploader Field.
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
