<?php
/**
 * Plugin Name: SpiceCraft Core - FMCG Product Data & Global CMS
 * Plugin URI: https://spicecraft.local
 * Description: Business logic, FMCG product data architecture, global settings, repeatable nutrition tables, and custom taxonomies for SpiceCraft.
 * Version: 1.0.0
 * Author: SpiceCraft Architecture Team
 * Author URI: https://spicecraft.local
 * Text Domain: spicecraft-core
 * Domain Path: /languages
 * Requires at least: 6.4
 * Requires PHP: 8.0
 *
 * @package SpiceCraft_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Define Constants
define( 'SPICECRAFT_CORE_VERSION', '1.0.0' );
define( 'SPICECRAFT_CORE_DIR', plugin_dir_path( __FILE__ ) );
define( 'SPICECRAFT_CORE_URI', plugin_dir_url( __FILE__ ) );

/**
 * Load Core Modules
 */
require_once SPICECRAFT_CORE_DIR . 'includes/helpers/api-helpers.php';
require_once SPICECRAFT_CORE_DIR . 'includes/settings/class-global-settings.php';
require_once SPICECRAFT_CORE_DIR . 'includes/products/class-taxonomies.php';
require_once SPICECRAFT_CORE_DIR . 'includes/products/class-product-meta.php';

/**
 * Plugin Bootstrap Class
 */
class SpiceCraft_Core {

	/**
	 * Singleton Instance
	 *
	 * @var SpiceCraft_Core|null
	 */
	private static $instance = null;

	/**
	 * Get Singleton Instance
	 *
	 * @return SpiceCraft_Core
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
		add_action( 'plugins_loaded', array( $this, 'init' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );
	}

	/**
	 * Initialize plugin modules
	 */
	public function init() {
		// Initialize Global Settings
		if ( class_exists( 'SpiceCraft_Global_Settings' ) ) {
			SpiceCraft_Global_Settings::get_instance();
		}

		// Initialize Product Taxonomies
		if ( class_exists( 'SpiceCraft_Product_Taxonomies' ) ) {
			SpiceCraft_Product_Taxonomies::get_instance();
		}

		// Initialize Product Metadata Architecture
		if ( class_exists( 'SpiceCraft_Product_Meta' ) ) {
			SpiceCraft_Product_Meta::get_instance();
		}
	}

	/**
	 * Enqueue Admin Styles and Scripts
	 *
	 * @param string $hook_suffix Current admin page hook.
	 */
	public function enqueue_admin_assets( $hook_suffix ) {
		$screen = get_current_screen();
		if ( ! $screen ) {
			return;
		}

		// Load assets on Product edit screen and SpiceCraft Global Settings page
		$is_product_screen = 'product' === $screen->post_type;
		$is_settings_screen = false !== strpos( $screen->id, 'spicecraft' );

		if ( $is_product_screen || $is_settings_screen ) {
			wp_enqueue_style(
				'spicecraft-admin-meta',
				SPICECRAFT_CORE_URI . 'assets/admin/admin-meta.css',
				array(),
				SPICECRAFT_CORE_VERSION
			);

			wp_enqueue_script(
				'spicecraft-admin-meta',
				SPICECRAFT_CORE_URI . 'assets/admin/admin-meta.js',
				array( 'jquery' ),
				SPICECRAFT_CORE_VERSION,
				true
			);

			wp_localize_script(
				'spicecraft-admin-meta',
				'spicecraftAdminConfig',
				array(
					'i18n' => array(
						'confirmDelete' => esc_html__( 'Are you sure you want to remove this row?', 'spicecraft-core' ),
					),
				)
			);
		}
	}
}

// Bootstrap the plugin
SpiceCraft_Core::get_instance();
