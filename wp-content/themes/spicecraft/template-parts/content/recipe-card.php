<?php
/**
 * SpiceCraft Reusable Recipe Card Component
 *
 * Unified card layout used across:
 * - Recipe Archive & Taxonomy Hubs
 * - Homepage Recipe Section
 * - Related Recipes
 * - Single Product "Recipes Using This Product"
 * - Global Search
 *
 * Strict Compliance: Zero fake ratings, zero 0-value times/servings,
 * semantic markup, accessible links, and responsive imagery.
 *
 * @package SpiceCraft
 * @since 1.2.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $post;

$recipe = get_query_var( 'sc_recipe_card_post', $post );
if ( ! $recipe || 'spicecraft_recipe' !== $recipe->post_type ) {
	return;
}

$recipe_id = $recipe->ID;
$meta      = function_exists( 'spicecraft_get_recipe_meta' ) ? spicecraft_get_recipe_meta( $recipe_id ) : array();

// Taxonomies
$categories = get_the_terms( $recipe_id, 'spicecraft_recipe_category' );
$cat_name   = ( ! empty( $categories ) && ! is_wp_error( $categories ) ) ? $categories[0]->name : '';
$cat_link   = ( ! empty( $categories ) && ! is_wp_error( $categories ) ) ? get_term_link( $categories[0] ) : '';

$cuisines   = get_the_terms( $recipe_id, 'spicecraft_cuisine' );
$cuisine_name = ( ! empty( $cuisines ) && ! is_wp_error( $cuisines ) ) ? $cuisines[0]->name : '';

// Times & Servings
$total_time_str = $meta['formatted_total'] ?? '';
$yield_str      = $meta['yield'] ?? '';
$difficulty     = $meta['difficulty'] ?? '';
$diff_label     = function_exists( 'spicecraft_get_recipe_difficulty_label' ) ? spicecraft_get_recipe_difficulty_label( $difficulty ) : '';

// Excerpt
$excerpt = get_the_excerpt( $recipe_id );
if ( empty( $excerpt ) ) {
	$excerpt = wp_trim_words( $recipe->post_content, 18 );
} else {
	$excerpt = wp_trim_words( $excerpt, 18 );
}
?>

<article class="sc-recipe-card" id="recipe-card-<?php echo esc_attr( $recipe_id ); ?>">
	<div class="sc-recipe-card__media">
		<a href="<?php echo esc_url( get_permalink( $recipe_id ) ); ?>" class="sc-recipe-card__media-link" tabindex="-1" aria-hidden="true">
			<?php if ( has_post_thumbnail( $recipe_id ) ) : ?>
				<?php echo get_the_post_thumbnail( $recipe_id, 'medium_large', array(
					'class'   => 'sc-recipe-card__img',
					'alt'     => get_the_title( $recipe_id ),
					'loading' => 'lazy',
				) ); ?>
			<?php else : ?>
				<div class="sc-recipe-card__placeholder" aria-hidden="true">
					<svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round">
						<path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
					</svg>
					<span><?php esc_html_e( 'SpiceCraft Test Kitchen', 'spicecraft' ); ?></span>
				</div>
			<?php endif; ?>
		</a>

		<div class="sc-recipe-card__badges">
			<?php if ( ! empty( $cat_name ) ) : ?>
				<span class="sc-recipe-badge sc-recipe-badge--cat"><?php echo esc_html( $cat_name ); ?></span>
			<?php endif; ?>
			<?php if ( ! empty( $cuisine_name ) ) : ?>
				<span class="sc-recipe-badge sc-recipe-badge--cuisine"><?php echo esc_html( $cuisine_name ); ?></span>
			<?php endif; ?>
		</div>
	</div>

	<div class="sc-recipe-card__body">
		<!-- Quick Meta Bar (Only non-zero/configured values) -->
		<?php if ( ! empty( $total_time_str ) || ! empty( $yield_str ) || ! empty( $diff_label ) ) : ?>
			<div class="sc-recipe-card__meta-bar" aria-label="<?php esc_attr_e( 'Recipe Quick Facts', 'spicecraft' ); ?>">
				<?php if ( ! empty( $total_time_str ) ) : ?>
					<span class="sc-recipe-meta-pill" title="<?php esc_attr_e( 'Total Cooking Time', 'spicecraft' ); ?>">
						<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
						<span><?php echo esc_html( $total_time_str ); ?></span>
					</span>
				<?php endif; ?>

				<?php if ( ! empty( $yield_str ) ) : ?>
					<span class="sc-recipe-meta-pill" title="<?php esc_attr_e( 'Servings / Yield', 'spicecraft' ); ?>">
						<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
						<span><?php echo esc_html( $yield_str ); ?></span>
					</span>
				<?php endif; ?>

				<?php if ( ! empty( $diff_label ) ) : ?>
					<span class="sc-recipe-meta-pill sc-recipe-meta-pill--diff" data-difficulty="<?php echo esc_attr( $difficulty ); ?>">
						<span><?php echo esc_html( $diff_label ); ?></span>
					</span>
				<?php endif; ?>
			</div>
		<?php endif; ?>

		<h3 class="sc-recipe-card__title">
			<a href="<?php echo esc_url( get_permalink( $recipe_id ) ); ?>">
				<?php echo esc_html( get_the_title( $recipe_id ) ); ?>
			</a>
		</h3>

		<?php if ( ! empty( $excerpt ) ) : ?>
			<p class="sc-recipe-card__excerpt"><?php echo esc_html( $excerpt ); ?></p>
		<?php endif; ?>

		<div class="sc-recipe-card__footer">
			<a href="<?php echo esc_url( get_permalink( $recipe_id ) ); ?>" class="sc-link-arrow sc-recipe-card__link">
				<span><?php esc_html_e( 'View Recipe', 'spicecraft' ); ?></span>
				<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
			</a>
		</div>
	</div>
</article>
