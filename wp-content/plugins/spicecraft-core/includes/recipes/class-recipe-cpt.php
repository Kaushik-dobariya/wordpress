<?php
/**
 * SpiceCraft Core - Recipe Custom Post Type & Taxonomies
 *
 * Registers the 'spicecraft_recipe' CPT and associated taxonomies:
 * - spicecraft_recipe_category (Hierarchical: e.g. Curries, Rice, Beverages)
 * - spicecraft_cuisine (Non-hierarchical: e.g. North Indian, Coastal, Mughal)
 * - spicecraft_meal_type (Non-hierarchical: e.g. Dinner, Lunch, Festive)
 *
 * Configures clean rewrite rules (/recipes/ and /recipes/{slug}/) and
 * rich administrative list columns.
 *
 * @package SpiceCraft_Core
 * @since 1.2.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class SpiceCraft_Recipe_CPT {

	/**
	 * Post Type key (<= 20 chars).
	 */
	const POST_TYPE = 'spicecraft_recipe';

	/**
	 * Taxonomy keys (<= 32 chars).
	 */
	const TAX_CATEGORY  = 'spicecraft_recipe_category';
	const TAX_CUISINE   = 'spicecraft_cuisine';
	const TAX_MEAL_TYPE = 'spicecraft_meal_type';

	/**
	 * Singleton Instance.
	 *
	 * @var SpiceCraft_Recipe_CPT|null
	 */
	private static $instance = null;

	/**
	 * Get Singleton Instance.
	 *
	 * @return SpiceCraft_Recipe_CPT
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
		add_action( 'init', array( $this, 'register_cpt_and_taxonomies' ), 5 );
		add_filter( 'manage_' . self::POST_TYPE . '_posts_columns', array( $this, 'filter_columns' ) );
		add_action( 'manage_' . self::POST_TYPE . '_posts_custom_column', array( $this, 'render_column_content' ), 10, 2 );
		add_filter( 'manage_edit-' . self::POST_TYPE . '_sortable_columns', array( $this, 'register_sortable_columns' ) );
	}

	/**
	 * Register Custom Post Type and Taxonomies.
	 */
	public function register_cpt_and_taxonomies() {
		$this->register_taxonomies();
		$this->register_post_type();
	}

	/**
	 * Register Taxonomies.
	 */
	private function register_taxonomies() {
		// 1. Recipe Category (Hierarchical)
		$cat_labels = array(
			'name'              => _x( 'Recipe Categories', 'taxonomy general name', 'spicecraft-core' ),
			'singular_name'     => _x( 'Recipe Category', 'taxonomy singular name', 'spicecraft-core' ),
			'search_items'      => __( 'Search Recipe Categories', 'spicecraft-core' ),
			'all_items'         => __( 'All Recipe Categories', 'spicecraft-core' ),
			'parent_item'       => __( 'Parent Category', 'spicecraft-core' ),
			'parent_item_colon' => __( 'Parent Category:', 'spicecraft-core' ),
			'edit_item'         => __( 'Edit Recipe Category', 'spicecraft-core' ),
			'update_item'       => __( 'Update Recipe Category', 'spicecraft-core' ),
			'add_new_item'      => __( 'Add New Recipe Category', 'spicecraft-core' ),
			'new_item_name'     => __( 'New Recipe Category Name', 'spicecraft-core' ),
			'menu_name'         => __( 'Categories', 'spicecraft-core' ),
		);

		register_taxonomy(
			self::TAX_CATEGORY,
			array( self::POST_TYPE ),
			array(
				'hierarchical'      => true,
				'labels'            => $cat_labels,
				'show_ui'           => true,
				'show_admin_column' => true,
				'query_var'         => true,
				'rewrite'           => array(
					'slug'         => 'recipe-category',
					'with_front'   => false,
					'hierarchical' => true,
				),
				'show_in_rest'      => true,
			)
		);

		// 2. Cuisine (Non-hierarchical)
		$cuisine_labels = array(
			'name'                       => _x( 'Cuisines', 'taxonomy general name', 'spicecraft-core' ),
			'singular_name'              => _x( 'Cuisine', 'taxonomy singular name', 'spicecraft-core' ),
			'search_items'               => __( 'Search Cuisines', 'spicecraft-core' ),
			'popular_items'              => __( 'Popular Cuisines', 'spicecraft-core' ),
			'all_items'                  => __( 'All Cuisines', 'spicecraft-core' ),
			'edit_item'                  => __( 'Edit Cuisine', 'spicecraft-core' ),
			'update_item'                => __( 'Update Cuisine', 'spicecraft-core' ),
			'add_new_item'               => __( 'Add New Cuisine', 'spicecraft-core' ),
			'new_item_name'              => __( 'New Cuisine Name', 'spicecraft-core' ),
			'separate_items_with_commas' => __( 'Separate cuisines with commas', 'spicecraft-core' ),
			'add_or_remove_items'        => __( 'Add or remove cuisines', 'spicecraft-core' ),
			'choose_from_most_used'      => __( 'Choose from the most used cuisines', 'spicecraft-core' ),
			'not_found'                  => __( 'No cuisines found.', 'spicecraft-core' ),
			'menu_name'                  => __( 'Cuisines', 'spicecraft-core' ),
		);

		register_taxonomy(
			self::TAX_CUISINE,
			array( self::POST_TYPE ),
			array(
				'hierarchical'          => false,
				'labels'                => $cuisine_labels,
				'show_ui'               => true,
				'show_admin_column'     => true,
				'update_count_callback' => '_update_post_term_count',
				'query_var'             => true,
				'rewrite'               => array(
					'slug'       => 'cuisine',
					'with_front' => false,
				),
				'show_in_rest'          => true,
			)
		);

		// 3. Meal Type (Non-hierarchical)
		$meal_labels = array(
			'name'                       => _x( 'Meal Types', 'taxonomy general name', 'spicecraft-core' ),
			'singular_name'              => _x( 'Meal Type', 'taxonomy singular name', 'spicecraft-core' ),
			'search_items'               => __( 'Search Meal Types', 'spicecraft-core' ),
			'popular_items'              => __( 'Popular Meal Types', 'spicecraft-core' ),
			'all_items'                  => __( 'All Meal Types', 'spicecraft-core' ),
			'edit_item'                  => __( 'Edit Meal Type', 'spicecraft-core' ),
			'update_item'                => __( 'Update Meal Type', 'spicecraft-core' ),
			'add_new_item'               => __( 'Add New Meal Type', 'spicecraft-core' ),
			'new_item_name'              => __( 'New Meal Type Name', 'spicecraft-core' ),
			'separate_items_with_commas' => __( 'Separate meal types with commas', 'spicecraft-core' ),
			'add_or_remove_items'        => __( 'Add or remove meal types', 'spicecraft-core' ),
			'choose_from_most_used'      => __( 'Choose from the most used meal types', 'spicecraft-core' ),
			'not_found'                  => __( 'No meal types found.', 'spicecraft-core' ),
			'menu_name'                  => __( 'Meal Types', 'spicecraft-core' ),
		);

		register_taxonomy(
			self::TAX_MEAL_TYPE,
			array( self::POST_TYPE ),
			array(
				'hierarchical'          => false,
				'labels'                => $meal_labels,
				'show_ui'               => true,
				'show_admin_column'     => true,
				'update_count_callback' => '_update_post_term_count',
				'query_var'             => true,
				'rewrite'               => array(
					'slug'       => 'meal-type',
					'with_front' => false,
				),
				'show_in_rest'          => true,
			)
		);
	}

	/**
	 * Register Custom Post Type.
	 */
	private function register_post_type() {
		$labels = array(
			'name'                  => _x( 'Recipes', 'Post type general name', 'spicecraft-core' ),
			'singular_name'         => _x( 'Recipe', 'Post type singular name', 'spicecraft-core' ),
			'menu_name'             => _x( 'Recipes', 'Admin Menu text', 'spicecraft-core' ),
			'name_admin_bar'        => _x( 'Recipe', 'Add New on Toolbar', 'spicecraft-core' ),
			'add_new'               => __( 'Add Recipe', 'spicecraft-core' ),
			'add_new_item'          => __( 'Add New Recipe', 'spicecraft-core' ),
			'new_item'              => __( 'New Recipe', 'spicecraft-core' ),
			'edit_item'             => __( 'Edit Recipe', 'spicecraft-core' ),
			'view_item'             => __( 'View Recipe', 'spicecraft-core' ),
			'all_items'             => __( 'All Recipes', 'spicecraft-core' ),
			'search_items'          => __( 'Search Recipes', 'spicecraft-core' ),
			'parent_item_colon'     => __( 'Parent Recipes:', 'spicecraft-core' ),
			'not_found'             => __( 'No recipes found.', 'spicecraft-core' ),
			'not_found_in_trash'    => __( 'No recipes found in Trash.', 'spicecraft-core' ),
			'featured_image'        => _x( 'Recipe Photo', 'Overrides the "Featured Image" phrase', 'spicecraft-core' ),
			'set_featured_image'    => _x( 'Set recipe photo', 'Overrides the "Set featured image" phrase', 'spicecraft-core' ),
			'remove_featured_image' => _x( 'Remove recipe photo', 'Overrides the "Remove featured image" phrase', 'spicecraft-core' ),
			'use_featured_image'    => _x( 'Use as recipe photo', 'Overrides the "Use as featured image" phrase', 'spicecraft-core' ),
			'archives'              => _x( 'Recipe Archives', 'The post type archive label', 'spicecraft-core' ),
			'insert_into_item'      => _x( 'Insert into recipe', 'Overrides the "Insert into post" phrase', 'spicecraft-core' ),
			'uploaded_to_this_item' => _x( 'Uploaded to this recipe', 'Overrides the "Uploaded to this post" phrase', 'spicecraft-core' ),
			'filter_items_list'     => _x( 'Filter recipes list', 'Screen reader text for the filter links', 'spicecraft-core' ),
			'items_list_navigation' => _x( 'Recipes list navigation', 'Screen reader text for pagination', 'spicecraft-core' ),
			'items_list'            => _x( 'Recipes list', 'Screen reader text for the items list', 'spicecraft-core' ),
		);

		$args = array(
			'labels'             => $labels,
			'description'        => __( 'Artisanal spice recipes, culinary pairings, and test kitchen creations.', 'spicecraft-core' ),
			'public'             => true,
			'publicly_queryable' => true,
			'show_ui'            => true,
			'show_in_menu'       => true,
			'query_var'          => true,
			'rewrite'            => array(
				'slug'       => 'recipes',
				'with_front' => false,
			),
			'capability_type'    => 'post',
			'has_archive'        => 'recipes',
			'hierarchical'       => false,
			'menu_position'      => 26,
			'menu_icon'          => 'dashicons-carrot',
			'supports'           => array( 'title', 'editor', 'thumbnail', 'excerpt', 'revisions' ),
			'show_in_rest'       => true,
			'exclude_from_search'=> false,
		);

		register_post_type( self::POST_TYPE, $args );
	}

	/**
	 * Filter Columns for the Recipe admin list table.
	 *
	 * @param array $columns Existing columns.
	 * @return array
	 */
	public function filter_columns( $columns ) {
		$new_columns = array(
			'cb'                => $columns['cb'] ?? '<input type="checkbox" />',
			'sc_recipe_thumb'   => __( 'Photo', 'spicecraft-core' ),
			'title'             => __( 'Recipe Title', 'spicecraft-core' ),
			'sc_recipe_cat'     => __( 'Category', 'spicecraft-core' ),
			'sc_recipe_cuisine' => __( 'Cuisine', 'spicecraft-core' ),
			'sc_recipe_timing'  => __( 'Prep / Cook', 'spicecraft-core' ),
			'sc_recipe_yield'   => __( 'Yield', 'spicecraft-core' ),
			'sc_recipe_diff'    => __( 'Difficulty', 'spicecraft-core' ),
			'sc_recipe_products'=> __( 'Linked Spices', 'spicecraft-core' ),
			'date'              => $columns['date'] ?? __( 'Date', 'spicecraft-core' ),
		);

		return $new_columns;
	}

	/**
	 * Render Custom Column Content.
	 *
	 * @param string $column  Column identifier.
	 * @param int    $post_id Current post ID.
	 */
	public function render_column_content( $column, $post_id ) {
		switch ( $column ) {
			case 'sc_recipe_thumb':
				if ( has_post_thumbnail( $post_id ) ) {
					echo get_the_post_thumbnail( $post_id, array( 50, 50 ), array(
						'style' => 'width: 44px; height: 44px; object-fit: cover; border-radius: 4px;',
					) );
				} else {
					echo '<span style="display:inline-block;width:44px;height:44px;background:#f0f0f1;border-radius:4px;line-height:44px;text-align:center;color:#8c8f94;font-size:10px;">—</span>';
				}
				break;

			case 'sc_recipe_cat':
				$terms = get_the_term_list( $post_id, self::TAX_CATEGORY, '', ', ' );
				echo ! empty( $terms ) ? wp_kses_post( $terms ) : '<span style="color:#8c8f94;">—</span>';
				break;

			case 'sc_recipe_cuisine':
				$cuisines = get_the_term_list( $post_id, self::TAX_CUISINE, '', ', ' );
				echo ! empty( $cuisines ) ? wp_kses_post( $cuisines ) : '<span style="color:#8c8f94;">—</span>';
				break;

			case 'sc_recipe_timing':
				$prep  = absint( get_post_meta( $post_id, '_spicecraft_recipe_prep_minutes', true ) );
				$cook  = absint( get_post_meta( $post_id, '_spicecraft_recipe_cook_minutes', true ) );
				$total = absint( get_post_meta( $post_id, '_spicecraft_recipe_total_minutes', true ) );
				if ( ! $total && ( $prep || $cook ) ) {
					$total = $prep + $cook;
				}

				if ( $total > 0 ) {
					printf(
						'<strong>%s min</strong><br><small style="color:#646970;">Prep: %dm | Cook: %dm</small>',
						esc_html( (string) $total ),
						esc_html( (string) $prep ),
						esc_html( (string) $cook )
					);
				} else {
					echo '<span style="color:#8c8f94;">—</span>';
				}
				break;

			case 'sc_recipe_yield':
				$yield = get_post_meta( $post_id, '_spicecraft_recipe_yield', true );
				echo ! empty( $yield ) ? esc_html( $yield ) : '<span style="color:#8c8f94;">—</span>';
				break;

			case 'sc_recipe_diff':
				$diff = get_post_meta( $post_id, '_spicecraft_recipe_difficulty', true );
				$labels = array(
					'easy'     => __( 'Easy', 'spicecraft-core' ),
					'medium'   => __( 'Medium', 'spicecraft-core' ),
					'advanced' => __( 'Advanced', 'spicecraft-core' ),
				);
				if ( ! empty( $diff ) && isset( $labels[ $diff ] ) ) {
					$colors = array(
						'easy'     => '#2e7d32',
						'medium'   => '#e65100',
						'advanced' => '#c62828',
					);
					$color = $colors[ $diff ] ?? '#646970';
					printf( '<span style="font-weight:600;color:%s;">%s</span>', esc_attr( $color ), esc_html( $labels[ $diff ] ) );
				} else {
					echo '<span style="color:#8c8f94;">—</span>';
				}
				break;

			case 'sc_recipe_products':
				if ( function_exists( 'spicecraft_get_recipe_linked_products' ) ) {
					$product_ids = spicecraft_get_recipe_linked_products( $post_id );
					if ( ! empty( $product_ids ) ) {
						echo '<strong>' . count( $product_ids ) . '</strong> ' . esc_html__( 'spice(s)', 'spicecraft-core' );
					} else {
						echo '<span style="color:#8c8f94;">—</span>';
					}
				} else {
					echo '<span style="color:#8c8f94;">—</span>';
				}
				break;
		}
	}

	/**
	 * Make columns sortable.
	 *
	 * @param array $columns Sortable columns.
	 * @return array
	 */
	public function register_sortable_columns( $columns ) {
		$columns['sc_recipe_cat'] = 'sc_recipe_cat';
		return $columns;
	}
}
