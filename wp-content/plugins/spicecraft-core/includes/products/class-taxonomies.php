<?php
/**
 * SpiceCraft Core - Product Taxonomies Architecture
 *
 * Registers custom taxonomies for FMCG spice products such as Certifications.
 * Allows reusable assignment of statutory and quality certifications across products.
 *
 * @package SpiceCraft_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class SpiceCraft_Product_Taxonomies {

	/**
	 * Singleton Instance
	 *
	 * @var SpiceCraft_Product_Taxonomies|null
	 */
	private static $instance = null;

	/**
	 * Get Singleton Instance
	 *
	 * @return SpiceCraft_Product_Taxonomies
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
		add_action( 'init', array( $this, 'register_taxonomies' ), 5 );
	}

	/**
	 * Register Custom Taxonomies for Product Post Type
	 */
	public function register_taxonomies() {
		$labels = array(
			'name'              => _x( 'Certifications', 'taxonomy general name', 'spicecraft-core' ),
			'singular_name'     => _x( 'Certification', 'taxonomy singular name', 'spicecraft-core' ),
			'search_items'      => __( 'Search Certifications', 'spicecraft-core' ),
			'all_items'         => __( 'All Certifications', 'spicecraft-core' ),
			'parent_item'       => __( 'Parent Certification', 'spicecraft-core' ),
			'parent_item_colon' => __( 'Parent Certification:', 'spicecraft-core' ),
			'edit_item'         => __( 'Edit Certification', 'spicecraft-core' ),
			'update_item'       => __( 'Update Certification', 'spicecraft-core' ),
			'add_new_item'      => __( 'Add New Certification', 'spicecraft-core' ),
			'new_item_name'     => __( 'New Certification Name', 'spicecraft-core' ),
			'menu_name'         => __( 'Certifications', 'spicecraft-core' ),
		);

		$args = array(
			'hierarchical'          => true, // Checkbox UI in product editor
			'labels'                => $labels,
			'show_ui'               => true,
			'show_admin_column'     => true,
			'query_var'             => true,
			'rewrite'               => array( 'slug' => 'certification' ),
			'show_in_rest'          => true,
			'update_count_callback' => '_update_post_term_count',
		);

		register_taxonomy( 'spicecraft_certification', array( 'product' ), $args );
	}
}
