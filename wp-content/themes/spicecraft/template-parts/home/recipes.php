<?php
/**
 * Homepage Template Part: Recipes & Culinary Inspiration
 * Semantic ID: #recipes
 *
 * Consumes structured recipes from the dedicated Recipe CMS (spicecraft_recipe).
 * Styled as an editorial culinary journal / magazine grid.
 *
 * @package SpiceCraft
 * @since 1.2.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$recipes = function_exists( 'spicecraft_get_homepage_section' )
	? spicecraft_get_homepage_section( 'recipes' )
	: array();

$eyebrow      = ! empty( $recipes['eyebrow'] ) ? $recipes['eyebrow'] : __( 'From Our Test Kitchen', 'spicecraft' );
$heading      = ! empty( $recipes['heading'] ) ? $recipes['heading'] : __( 'Recipes & Spice Pairings', 'spicecraft' );
$description  = ! empty( $recipes['description'] ) ? $recipes['description'] : '';
$source_type  = ! empty( $recipes['source_type'] ) ? $recipes['source_type'] : 'latest';
$category_id  = ! empty( $recipes['category_id'] ) ? absint( $recipes['category_id'] ) : 0;
$selected_ids = ! empty( $recipes['selected_ids'] ) && is_array( $recipes['selected_ids'] ) ? array_map( 'absint', $recipes['selected_ids'] ) : array();
$limit        = ! empty( $recipes['limit'] ) ? absint( $recipes['limit'] ) : 3;
$cta_label    = ! empty( $recipes['cta_label'] ) ? $recipes['cta_label'] : __( 'Explore All Recipes', 'spicecraft' );
$cta_url      = ! empty( $recipes['cta_url'] ) ? $recipes['cta_url'] : get_post_type_archive_link( 'spicecraft_recipe' );
if ( empty( $cta_url ) ) {
	$cta_url = home_url( '/recipes/' );
}

$query_args = array(
	'post_type'      => 'spicecraft_recipe',
	'post_status'    => 'publish',
	'posts_per_page' => $limit,
	'no_found_rows'  => true,
);

if ( 'selected' === $source_type && ! empty( $selected_ids ) ) {
	$query_args['post__in'] = $selected_ids;
	$query_args['orderby']  = 'post__in';
} elseif ( 'category' === $source_type && $category_id > 0 ) {
	$query_args['tax_query'] = array(
		array(
			'taxonomy' => 'spicecraft_recipe_category',
			'field'    => 'term_id',
			'terms'    => $category_id,
		),
	);
} else {
	$query_args['orderby'] = 'date';
	$query_args['order']   = 'DESC';
}

$recipes_query = new WP_Query( $query_args );

// Graceful empty state: Suppress section cleanly if no published recipes exist
if ( ! $recipes_query->have_posts() ) {
	wp_reset_postdata();
	return;
}
?>

<section id="recipes" class="sc-home-section sc-home-recipes" aria-labelledby="sec-heading-recipes">
	<div class="sc-container">
		<header class="sc-section-header sc-section-header--center">
			<?php if ( ! empty( $eyebrow ) ) : ?>
				<p class="sc-eyebrow"><?php echo esc_html( $eyebrow ); ?></p>
			<?php endif; ?>

			<h2 id="sec-heading-recipes" class="sc-section-title"><?php echo esc_html( $heading ); ?></h2>

			<?php if ( ! empty( $description ) ) : ?>
				<p class="sc-section-subtitle"><?php echo esc_html( $description ); ?></p>
			<?php endif; ?>
		</header>

		<div class="sc-grid sc-grid--3 sc-recipes-grid">
			<?php
			while ( $recipes_query->have_posts() ) :
				$recipes_query->the_post();
				if ( function_exists( 'spicecraft_render_recipe_card' ) ) {
					spicecraft_render_recipe_card( get_post() );
				}
			endwhile;
			wp_reset_postdata();
			?>
		</div>

		<?php if ( ! empty( $cta_label ) ) : ?>
			<div class="sc-recipes-footer" style="text-align: center; margin-top: var(--sc-space-10, 40px);">
				<a href="<?php echo esc_url( $cta_url ); ?>" class="sc-btn sc-btn--secondary sc-btn--md">
					<span><?php echo esc_html( $cta_label ); ?></span>
					<svg class="sc-icon sc-icon-arrow" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
				</a>
			</div>
		<?php endif; ?>
	</div>
</section>
