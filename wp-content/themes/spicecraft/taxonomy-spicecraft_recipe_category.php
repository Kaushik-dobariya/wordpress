<?php
/**
 * Taxonomy Template: Recipe Categories
 *
 * @package SpiceCraft
 * @since 1.2.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Reuses the comprehensive recipe archive template with taxonomy context
require locate_template( 'archive-spicecraft_recipe.php' );
