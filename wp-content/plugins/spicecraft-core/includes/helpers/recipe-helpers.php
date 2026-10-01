<?php
/**
 * SpiceCraft Core - Recipe Helper Functions
 *
 * Centralized business logic, time formatting, schema helpers, relationship resolution,
 * and component loaders for recipes.
 *
 * @package SpiceCraft_Core
 * @since 1.2.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Retrieve recipe archive and display settings with safe defaults.
 *
 * @return array
 */
function spicecraft_get_recipe_settings() {
	$defaults = array(
		'archive_enabled'        => 1,
		'eyebrow'                => __( 'Culinary Inspiration & Test Kitchen', 'spicecraft-core' ),
		'heading'                => __( 'Artisanal Spice Recipes', 'spicecraft-core' ),
		'introduction'           => __( 'Discover time-honored recipes, spice-pairings, and culinary techniques formulated by our master blenders to bring out the authentic soul of Indian and global cuisine.', 'spicecraft-core' ),
		'desktop_hero_id'        => 0,
		'mobile_hero_id'         => 0,
		'featured_recipe_id'     => 0,
		'show_search'            => 1,
		'show_category_filter'   => 1,
		'show_cuisine_filter'    => 1,
		'show_meal_type_filter'  => 1,
		'show_difficulty_filter' => 1,
		'recipes_per_page'       => 9,
		'default_sort'           => 'date_desc',
		'final_cta_enabled'      => 1,
		'cta_heading'            => __( 'Bring These Recipes to Life with Authentic Spices', 'spicecraft-core' ),
		'cta_description'        => __( 'Browse our catalog of pure, cold-ground spices and master artisanal blends engineered for superior flavor retention and aroma.', 'spicecraft-core' ),
		'cta_image_id'           => 0,
		'cta_whatsapp_enabled'   => 1,
		'cta_email_enabled'      => 1,
	);

	$saved = get_option( 'spicecraft_recipe_settings', array() );
	if ( ! is_array( $saved ) ) {
		$saved = array();
	}

	return wp_parse_args( $saved, $defaults );
}

/**
 * Format numeric minutes into a clean, human-readable string.
 * Examples:
 *   15 -> "15 mins"
 *   60 -> "1 hr"
 *   65 -> "1 hr 5 mins"
 *   120 -> "2 hrs"
 * Suppresses 0 or negative values.
 *
 * @param int $minutes Numeric minutes.
 * @return string Formatted string, or empty if 0.
 */
function spicecraft_format_recipe_time( $minutes ) {
	$minutes = absint( $minutes );
	if ( $minutes <= 0 ) {
		return '';
	}

	$hours     = floor( $minutes / 60 );
	$remaining = $minutes % 60;

	if ( $hours > 0 ) {
		$hr_str = 1 === (int) $hours ? __( '1 hr', 'spicecraft-core' ) : sprintf( __( '%d hrs', 'spicecraft-core' ), $hours );
		if ( $remaining > 0 ) {
			return sprintf( '%s %d %s', $hr_str, $remaining, __( 'mins', 'spicecraft-core' ) );
		}
		return $hr_str;
	}

	return sprintf( '%d %s', $minutes, __( 'mins', 'spicecraft-core' ) );
}

/**
 * Convert numeric minutes into an ISO 8601 duration string for Schema.org Recipe.
 * Example: 65 -> "PT1H5M", 30 -> "PT30M".
 *
 * @param int $minutes Numeric minutes.
 * @return string ISO 8601 duration, or empty if 0.
 */
function spicecraft_minutes_to_iso8601( $minutes ) {
	$minutes = absint( $minutes );
	if ( $minutes <= 0 ) {
		return '';
	}

	$hours     = floor( $minutes / 60 );
	$remaining = $minutes % 60;

	$iso = 'PT';
	if ( $hours > 0 ) {
		$iso .= $hours . 'H';
	}
	if ( $remaining > 0 ) {
		$iso .= $remaining . 'M';
	}

	return $iso;
}

/**
 * Retrieve all structured metadata for a recipe.
 *
 * @param int $post_id Recipe post ID.
 * @return array Structured metadata.
 */
function spicecraft_get_recipe_meta( $post_id = 0 ) {
	$post_id = $post_id ?: get_the_ID();
	if ( ! $post_id ) {
		return array();
	}

	// Times
	$prep_minutes = absint( get_post_meta( $post_id, '_spicecraft_recipe_prep_minutes', true ) );
	$cook_minutes = absint( get_post_meta( $post_id, '_spicecraft_recipe_cook_minutes', true ) );
	$add_minutes  = absint( get_post_meta( $post_id, '_spicecraft_recipe_additional_minutes', true ) );
	$total_minutes= absint( get_post_meta( $post_id, '_spicecraft_recipe_total_minutes', true ) );

	if ( ! $total_minutes && ( $prep_minutes || $cook_minutes || $add_minutes ) ) {
		$total_minutes = $prep_minutes + $cook_minutes + $add_minutes;
	}

	// Servings & Difficulty
	$yield      = get_post_meta( $post_id, '_spicecraft_recipe_yield', true );
	$difficulty = get_post_meta( $post_id, '_spicecraft_recipe_difficulty', true );

	// Dietary Classifications (Explicit Admin Controlled)
	$dietary = get_post_meta( $post_id, '_spicecraft_recipe_dietary', true );
	if ( ! is_array( $dietary ) ) {
		$dietary = array();
	}

	// Ingredient Groups
	$ingredient_groups = get_post_meta( $post_id, '_spicecraft_recipe_ingredient_groups', true );
	if ( ! is_array( $ingredient_groups ) ) {
		$ingredient_groups = array();
	}

	// Instruction Steps
	$instruction_steps = get_post_meta( $post_id, '_spicecraft_recipe_instruction_steps', true );
	if ( ! is_array( $instruction_steps ) ) {
		$instruction_steps = array();
	}

	// Culinary Notes
	$notes_chef    = get_post_meta( $post_id, '_spicecraft_recipe_notes_chef', true );
	$notes_serving = get_post_meta( $post_id, '_spicecraft_recipe_notes_serving', true );
	$notes_storage = get_post_meta( $post_id, '_spicecraft_recipe_notes_storage', true );
	$notes_subs    = get_post_meta( $post_id, '_spicecraft_recipe_notes_subs', true );

	// Nutrition Facts (Optional)
	$nutrition = get_post_meta( $post_id, '_spicecraft_recipe_nutrition', true );
	if ( ! is_array( $nutrition ) ) {
		$nutrition = array();
	}

	// Product Relationships
	$featured_products = get_post_meta( $post_id, '_spicecraft_recipe_featured_products', true );
	if ( ! is_array( $featured_products ) ) {
		$featured_products = array();
	}
	$related_categories = get_post_meta( $post_id, '_spicecraft_recipe_related_categories', true );
	if ( ! is_array( $related_categories ) ) {
		$related_categories = array();
	}

	// Media & Video
	$hero_image_id        = absint( get_post_meta( $post_id, '_spicecraft_recipe_hero_image_id', true ) );
	$mobile_hero_image_id = absint( get_post_meta( $post_id, '_spicecraft_recipe_mobile_hero_image_id', true ) );
	$gallery_ids          = get_post_meta( $post_id, '_spicecraft_recipe_gallery_ids', true );
	if ( ! is_array( $gallery_ids ) ) {
		$gallery_ids = array();
	}
	$video_url = get_post_meta( $post_id, '_spicecraft_recipe_video_url', true );

	return array(
		'prep_minutes'        => $prep_minutes,
		'cook_minutes'        => $cook_minutes,
		'additional_minutes'  => $add_minutes,
		'total_minutes'       => $total_minutes,
		'formatted_prep'      => spicecraft_format_recipe_time( $prep_minutes ),
		'formatted_cook'      => spicecraft_format_recipe_time( $cook_minutes ),
		'formatted_total'     => spicecraft_format_recipe_time( $total_minutes ),
		'yield'               => $yield,
		'difficulty'          => $difficulty,
		'dietary'             => $dietary,
		'ingredient_groups'   => $ingredient_groups,
		'instruction_steps'   => $instruction_steps,
		'notes_chef'          => $notes_chef,
		'notes_serving'       => $notes_serving,
		'notes_storage'       => $notes_storage,
		'notes_subs'          => $notes_subs,
		'nutrition'           => $nutrition,
		'featured_products'   => array_filter( array_map( 'absint', $featured_products ) ),
		'related_categories'  => array_filter( array_map( 'absint', $related_categories ) ),
		'hero_image_id'       => $hero_image_id,
		'mobile_hero_image_id'=> $mobile_hero_image_id,
		'gallery_ids'         => array_filter( array_map( 'absint', $gallery_ids ) ),
		'video_url'           => $video_url,
	);
}

/**
 * Get a deduplicated array of all WooCommerce product IDs linked to a recipe.
 * Combines ingredient-level product links with recipe-level featured products.
 *
 * @param int $post_id Recipe post ID.
 * @return array Array of unique WooCommerce product IDs.
 */
function spicecraft_get_recipe_linked_products( $post_id = 0 ) {
	$post_id = $post_id ?: get_the_ID();
	if ( ! $post_id ) {
		return array();
	}

	$meta = spicecraft_get_recipe_meta( $post_id );
	$product_ids = array();

	// 1. Collect from ingredient groups
	if ( ! empty( $meta['ingredient_groups'] ) ) {
		foreach ( $meta['ingredient_groups'] as $group ) {
			if ( ! empty( $group['items'] ) && is_array( $group['items'] ) ) {
				foreach ( $group['items'] as $item ) {
					if ( ! empty( $item['product_id'] ) ) {
						$product_ids[] = absint( $item['product_id'] );
					}
				}
			}
		}
	}

	// 2. Collect from recipe-level featured products
	if ( ! empty( $meta['featured_products'] ) ) {
		foreach ( $meta['featured_products'] as $prod_id ) {
			$product_ids[] = absint( $prod_id );
		}
	}

	$unique_ids = array_values( array_unique( array_filter( $product_ids ) ) );

	// Filter only published WooCommerce products
	$valid_ids = array();
	foreach ( $unique_ids as $pid ) {
		if ( 'publish' === get_post_status( $pid ) && 'product' === get_post_type( $pid ) ) {
			$valid_ids[] = $pid;
		}
	}

	return $valid_ids;
}

/**
 * Retrieve recipes that explicitly use a given WooCommerce product.
 *
 * @param int $product_id WooCommerce product ID.
 * @param int $limit      Maximum recipes to return.
 * @return array Array of WP_Post objects.
 */
function spicecraft_get_recipes_for_product( $product_id, $limit = 4 ) {
	$product_id = absint( $product_id );
	if ( ! $product_id ) {
		return array();
	}

	// Query recipes where product_id is either in featured products or in linked ingredient rows
	// Meta query matches serialized array in featured_products or ingredient_groups
	$args = array(
		'post_type'      => 'spicecraft_recipe',
		'post_status'    => 'publish',
		'posts_per_page' => $limit,
		'no_found_rows'  => true,
		'meta_query'     => array(
			'relation' => 'OR',
			array(
				'key'     => '_spicecraft_recipe_featured_products',
				'value'   => '"' . $product_id . '"',
				'compare' => 'LIKE',
			),
			array(
				'key'     => '_spicecraft_recipe_featured_products',
				'value'   => 'i:' . $product_id . ';',
				'compare' => 'LIKE',
			),
			array(
				'key'     => '_spicecraft_recipe_linked_product_ids',
				'value'   => '"' . $product_id . '"',
				'compare' => 'LIKE',
			),
			array(
				'key'     => '_spicecraft_recipe_linked_product_ids',
				'value'   => 'i:' . $product_id . ';',
				'compare' => 'LIKE',
			),
		),
	);

	$query = new WP_Query( $args );
	return $query->posts;
}

/**
 * Retrieve related recipes based on shared category, cuisine, or meal type.
 * Deterministic matching, deduplicated, excluding current recipe.
 *
 * @param int $post_id Current recipe ID.
 * @param int $limit   Maximum results.
 * @return array Array of WP_Post objects.
 */
function spicecraft_get_related_recipes( $post_id = 0, $limit = 3 ) {
	$post_id = $post_id ?: get_the_ID();
	if ( ! $post_id ) {
		return array();
	}

	$tax_query = array( 'relation' => 'OR' );

	$categories = wp_get_post_terms( $post_id, 'spicecraft_recipe_category', array( 'fields' => 'ids' ) );
	if ( ! empty( $categories ) && ! is_wp_error( $categories ) ) {
		$tax_query[] = array(
			'taxonomy' => 'spicecraft_recipe_category',
			'field'    => 'term_id',
			'terms'    => $categories,
		);
	}

	$cuisines = wp_get_post_terms( $post_id, 'spicecraft_cuisine', array( 'fields' => 'ids' ) );
	if ( ! empty( $cuisines ) && ! is_wp_error( $cuisines ) ) {
		$tax_query[] = array(
			'taxonomy' => 'spicecraft_cuisine',
			'field'    => 'term_id',
			'terms'    => $cuisines,
		);
	}

	$meal_types = wp_get_post_terms( $post_id, 'spicecraft_meal_type', array( 'fields' => 'ids' ) );
	if ( ! empty( $meal_types ) && ! is_wp_error( $meal_types ) ) {
		$tax_query[] = array(
			'taxonomy' => 'spicecraft_meal_type',
			'field'    => 'term_id',
			'terms'    => $meal_types,
		);
	}

	$args = array(
		'post_type'      => 'spicecraft_recipe',
		'post_status'    => 'publish',
		'posts_per_page' => $limit,
		'post__not_in'   => array( $post_id ),
		'no_found_rows'  => true,
		'orderby'        => 'rand',
	);

	if ( count( $tax_query ) > 1 ) {
		$args['tax_query'] = $tax_query;
	}

	$query = new WP_Query( $args );
	return $query->posts;
}

/**
 * Render the unified recipe card component.
 *
 * @param int|WP_Post $post Recipe post ID or object.
 * @param array       $args Optional display flags.
 */
function spicecraft_render_recipe_card( $post = null, $args = array() ) {
	if ( empty( $post ) ) {
		$post = get_the_ID();
	}
	if ( is_numeric( $post ) ) {
		$post = get_post( $post );
	}
	if ( ! $post || 'spicecraft_recipe' !== $post->post_type ) {
		return;
	}

	set_query_var( 'sc_recipe_card_post', $post );
	set_query_var( 'sc_recipe_card_args', $args );

	get_template_part( 'template-parts/content/recipe-card' );
}

/**
 * Get human-readable difficulty label.
 *
 * @param string $key Difficulty key ('easy', 'medium', 'advanced').
 * @return string Human label.
 */
function spicecraft_get_recipe_difficulty_label( $key ) {
	$labels = array(
		'easy'     => __( 'Easy', 'spicecraft-core' ),
		'medium'   => __( 'Medium', 'spicecraft-core' ),
		'advanced' => __( 'Advanced', 'spicecraft-core' ),
	);
	return $labels[ $key ] ?? '';
}

/**
 * Get human-readable dietary claim label.
 *
 * @param string $key Dietary key.
 * @return string Label.
 */
function spicecraft_get_dietary_label( $key ) {
	$labels = array(
		'vegetarian'  => __( 'Vegetarian', 'spicecraft-core' ),
		'vegan'       => __( 'Vegan', 'spicecraft-core' ),
		'gluten_free' => __( 'Gluten-Free', 'spicecraft-core' ),
		'dairy_free'  => __( 'Dairy-Free', 'spicecraft-core' ),
		'jain'        => __( 'Jain Friendly', 'spicecraft-core' ),
		'nut_free'    => __( 'Nut-Free', 'spicecraft-core' ),
	);
	return $labels[ $key ] ?? '';
}
