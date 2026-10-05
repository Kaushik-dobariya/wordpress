<?php
/**
 * Plugin Name: SpiceCraft Core - FMCG Product Data & Global CMS
 * Plugin URI: https://spicecraft.local
 * Description: Business logic, FMCG product data architecture, global settings, repeatable nutrition tables, homepage CMS, and custom taxonomies for SpiceCraft.
 * Version: 1.1.0
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
define( 'SPICECRAFT_CORE_VERSION', '1.1.0' );
define( 'SPICECRAFT_CORE_DIR', plugin_dir_path( __FILE__ ) );
define( 'SPICECRAFT_CORE_URI', plugin_dir_url( __FILE__ ) );

/**
 * Load Core Modules
 */
require_once SPICECRAFT_CORE_DIR . 'includes/helpers/api-helpers.php';
require_once SPICECRAFT_CORE_DIR . 'includes/helpers/homepage-helpers.php';
require_once SPICECRAFT_CORE_DIR . 'includes/helpers/about-helpers.php';
require_once SPICECRAFT_CORE_DIR . 'includes/helpers/shared-cms-helpers.php';
require_once SPICECRAFT_CORE_DIR . 'includes/helpers/manufacturing-helpers.php';
require_once SPICECRAFT_CORE_DIR . 'includes/helpers/quality-helpers.php';
require_once SPICECRAFT_CORE_DIR . 'includes/helpers/certification-helpers.php';
require_once SPICECRAFT_CORE_DIR . 'includes/helpers/recipe-helpers.php';
require_once SPICECRAFT_CORE_DIR . 'includes/helpers/careers-helpers.php';
require_once SPICECRAFT_CORE_DIR . 'includes/helpers/blog-helpers.php';
require_once SPICECRAFT_CORE_DIR . 'includes/helpers/testimonial-helpers.php';
require_once SPICECRAFT_CORE_DIR . 'includes/helpers/enquiry-helpers.php';
require_once SPICECRAFT_CORE_DIR . 'includes/helpers/contact-helpers.php';
require_once SPICECRAFT_CORE_DIR . 'includes/settings/class-global-settings.php';
require_once SPICECRAFT_CORE_DIR . 'includes/settings/class-homepage-settings.php';
require_once SPICECRAFT_CORE_DIR . 'includes/settings/class-about-settings.php';
require_once SPICECRAFT_CORE_DIR . 'includes/settings/class-manufacturing-settings.php';
require_once SPICECRAFT_CORE_DIR . 'includes/settings/class-quality-settings.php';
require_once SPICECRAFT_CORE_DIR . 'includes/settings/class-certification-settings.php';
require_once SPICECRAFT_CORE_DIR . 'includes/settings/class-recipe-settings.php';
require_once SPICECRAFT_CORE_DIR . 'includes/settings/class-careers-settings.php';
require_once SPICECRAFT_CORE_DIR . 'includes/settings/class-blog-settings.php';
require_once SPICECRAFT_CORE_DIR . 'includes/products/class-taxonomies.php';
require_once SPICECRAFT_CORE_DIR . 'includes/products/class-testimonial-cpt.php';
require_once SPICECRAFT_CORE_DIR . 'includes/products/class-team-cpt.php';
require_once SPICECRAFT_CORE_DIR . 'includes/products/class-product-meta.php';
require_once SPICECRAFT_CORE_DIR . 'includes/products/class-certification-meta.php';
require_once SPICECRAFT_CORE_DIR . 'includes/recipes/class-recipe-cpt.php';
require_once SPICECRAFT_CORE_DIR . 'includes/recipes/class-recipe-meta.php';
require_once SPICECRAFT_CORE_DIR . 'includes/careers/class-careers-cpt.php';
require_once SPICECRAFT_CORE_DIR . 'includes/careers/class-careers-meta.php';
require_once SPICECRAFT_CORE_DIR . 'includes/careers/class-careers-application.php';
require_once SPICECRAFT_CORE_DIR . 'includes/blog/class-blog-meta.php';
require_once SPICECRAFT_CORE_DIR . 'includes/enquiries/class-enquiry-cpt.php';
require_once SPICECRAFT_CORE_DIR . 'includes/enquiries/class-enquiry-meta.php';
require_once SPICECRAFT_CORE_DIR . 'includes/enquiries/class-enquiry-engine.php';
require_once SPICECRAFT_CORE_DIR . 'includes/quotations/class-quotation-engine.php';
require_once SPICECRAFT_CORE_DIR . 'includes/dashboard/class-dashboard.php';
require_once SPICECRAFT_CORE_DIR . 'includes/import-export/class-import-export.php';

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

		// Initialize Homepage Settings CMS
		if ( class_exists( 'SpiceCraft_Homepage_Settings' ) ) {
			SpiceCraft_Homepage_Settings::get_instance();
		}

		// Initialize About Us Settings CMS
		if ( class_exists( 'SpiceCraft_About_Settings' ) ) {
			SpiceCraft_About_Settings::get_instance();
		}

		// Initialize Manufacturing Settings CMS
		if ( class_exists( 'SpiceCraft_Manufacturing_Settings' ) ) {
			SpiceCraft_Manufacturing_Settings::get_instance();
		}

		// Initialize Quality & Sourcing Settings CMS
		if ( class_exists( 'SpiceCraft_Quality_Settings' ) ) {
			SpiceCraft_Quality_Settings::get_instance();
		}

		// Initialize Product Taxonomies
		if ( class_exists( 'SpiceCraft_Product_Taxonomies' ) ) {
			SpiceCraft_Product_Taxonomies::get_instance();
		}

		// Initialize Testimonial Custom Post Type
		if ( class_exists( 'SpiceCraft_Testimonial_CPT' ) ) {
			SpiceCraft_Testimonial_CPT::get_instance();
		}

		// Initialize Team Member Custom Post Type
		if ( class_exists( 'SpiceCraft_Team_CPT' ) ) {
			SpiceCraft_Team_CPT::get_instance();
		}

		// Initialize Product Metadata Architecture
		if ( class_exists( 'SpiceCraft_Product_Meta' ) ) {
			SpiceCraft_Product_Meta::get_instance();
		}

		// Initialize Certification Settings CMS
		if ( class_exists( 'SpiceCraft_Certification_Settings' ) ) {
			SpiceCraft_Certification_Settings::get_instance();
		}

		// Initialize Certification Taxonomy Meta & Admin Engine
		if ( class_exists( 'SpiceCraft_Certification_Meta' ) ) {
			SpiceCraft_Certification_Meta::get_instance();
		}

		// Initialize Recipe CPT & Taxonomies
		if ( class_exists( 'SpiceCraft_Recipe_CPT' ) ) {
			SpiceCraft_Recipe_CPT::get_instance();
		}

		// Initialize Recipe Meta Engine
		if ( class_exists( 'SpiceCraft_Recipe_Meta' ) ) {
			SpiceCraft_Recipe_Meta::get_instance();
		}

		// Initialize Recipe Settings CMS
		if ( class_exists( 'SpiceCraft_Recipe_Settings' ) ) {
			SpiceCraft_Recipe_Settings::get_instance();
		}

		// Initialize Careers CPT & Applications CPT
		if ( class_exists( 'SpiceCraft_Careers_CPT' ) ) {
			SpiceCraft_Careers_CPT::get_instance();
		}

		// Initialize Careers Meta Engine
		if ( class_exists( 'SpiceCraft_Careers_Meta' ) ) {
			SpiceCraft_Careers_Meta::get_instance();
		}

		// Initialize Careers Application Engine
		if ( class_exists( 'SpiceCraft_Careers_Application' ) ) {
			SpiceCraft_Careers_Application::get_instance();
		}

		// Initialize Careers Settings CMS
		if ( class_exists( 'SpiceCraft_Careers_Settings' ) ) {
			SpiceCraft_Careers_Settings::get_instance();
		}

		// Initialize Enquiry & Lead Management CPT
		if ( class_exists( 'SpiceCraft_Enquiry_CPT' ) ) {
			SpiceCraft_Enquiry_CPT::get_instance();
		}

		// Initialize Enquiry Meta Engine
		if ( class_exists( 'SpiceCraft_Enquiry_Meta' ) ) {
			SpiceCraft_Enquiry_Meta::get_instance();
		}

		// Initialize Enquiry Engine
		if ( class_exists( 'SpiceCraft_Enquiry_Engine' ) ) {
			SpiceCraft_Enquiry_Engine::get_instance();
		}

		// Initialize Quotation Engine
		if ( class_exists( 'SpiceCraft_Quotation_Engine' ) ) {
			SpiceCraft_Quotation_Engine::get_instance();
		}

		// Initialize Advanced Dashboard
		if ( class_exists( 'SpiceCraft_Dashboard' ) ) {
			SpiceCraft_Dashboard::get_instance();
		}

		// Initialize Import / Export Engine
		if ( class_exists( 'SpiceCraft_Import_Export' ) ) {
			SpiceCraft_Import_Export::get_instance();
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

		// Load assets on Product edit screen, Testimonials, Team Members, Recipes, Careers, Enquiries, and SpiceCraft screens
		$is_product_screen     = 'product' === $screen->post_type;
		$is_testimonial_screen = 'sc_testimonial' === $screen->post_type;
		$is_team_screen        = 'spicecraft_team' === $screen->post_type;
		$is_recipe_screen      = 'spicecraft_recipe' === $screen->post_type;
		$is_job_screen         = 'spicecraft_job' === $screen->post_type;
		$is_app_screen         = 'spicecraft_app' === $screen->post_type;
		$is_enquiry_screen     = 'spicecraft_enquiry' === $screen->post_type;
		$is_settings_screen    = false !== strpos( $screen->id, 'spicecraft' );
		$is_cert_screen        = isset( $screen->taxonomy ) && 'spicecraft_certification' === $screen->taxonomy;

		if ( $is_product_screen || $is_testimonial_screen || $is_team_screen || $is_recipe_screen || $is_job_screen || $is_app_screen || $is_enquiry_screen || $is_settings_screen || $is_cert_screen ) {
			// Ensure WordPress media library scripts/styles are loaded for image selectors
			wp_enqueue_media();

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
