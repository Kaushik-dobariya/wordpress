<?php
/**
 * SpiceCraft functions and definitions
 *
 * @package SpiceCraft
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Define Theme Constants
 */
define( 'SPICECRAFT_VERSION', '1.0.0' );
define( 'SPICECRAFT_DIR', __DIR__ );
define( 'SPICECRAFT_URI', get_template_directory_uri() );

/**
 * Modular Theme Includes
 *
 * All business logic, theme setup, enqueues, and WooCommerce catalog hooks
 * are segregated into focused, maintainable modules.
 */
require_once SPICECRAFT_DIR . '/inc/setup.php';
require_once SPICECRAFT_DIR . '/inc/enqueue.php';
require_once SPICECRAFT_DIR . '/inc/helpers.php';
require_once SPICECRAFT_DIR . '/inc/customizer.php';
require_once SPICECRAFT_DIR . '/inc/catalog-mode.php';
require_once SPICECRAFT_DIR . '/inc/woocommerce.php';
require_once SPICECRAFT_DIR . '/inc/product-discovery.php';
