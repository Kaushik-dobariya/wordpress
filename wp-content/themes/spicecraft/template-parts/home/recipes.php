<?php
/**
 * Homepage Template Part: Recipes & Culinary Inspiration
 * Semantic ID: #recipes
 *
 * Extensible foundation consuming WordPress Posts from a configured category.
 * Styled as an editorial culinary journal / magazine grid.
 *
 * @package SpiceCraft
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$recipes = function_exists( 'spicecraft_get_homepage_section' )
	? spicecraft_get_homepage_section( 'recipes' )
	: array();

$eyebrow     = ! empty( $recipes['eyebrow'] ) ? $recipes['eyebrow'] : '';
$heading     = ! empty( $recipes['heading'] ) ? $recipes['heading'] : '';
$description = ! empty( $recipes['description'] ) ? $recipes['description'] : '';
$category_id = ! empty( $recipes['category_id'] ) ? absint( $recipes['category_id'] ) : 0;
$limit       = ! empty( $recipes['limit'] ) ? absint( $recipes['limit'] ) : 3;
$cta_label   = ! empty( $recipes['cta_label'] ) ? $recipes['cta_label'] : '';
$cta_url     = ! empty( $recipes['cta_url'] ) ? $recipes['cta_url'] : home_url( '/recipes/' );

$query_args = array(
	'post_type'      => 'post',
	'post_status'    => 'publish',
	'posts_per_page' => $limit,
	'no_found_rows'  => true,
);

if ( $category_id > 0 ) {
	$query_args['cat'] = $category_id;
}

$recipes_query = new WP_Query( $query_args );

// Graceful empty state: Suppress if no posts available
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

			<?php if ( ! empty( $heading ) ) : ?>
				<h2 id="sec-heading-recipes" class="sc-section-title"><?php echo esc_html( $heading ); ?></h2>
			<?php else : ?>
				<h2 id="sec-heading-recipes" class="sc-section-title"><?php esc_html_e( 'Culinary Pairings & Recipes', 'spicecraft' ); ?></h2>
			<?php endif; ?>

			<?php if ( ! empty( $description ) ) : ?>
				<p class="sc-section-subtitle"><?php echo esc_html( $description ); ?></p>
			<?php endif; ?>
		</header>

		<div class="sc-grid sc-grid--3 sc-recipes-grid">
			<?php
			while ( $recipes_query->have_posts() ) :
				$recipes_query->the_post();
				$post_cats = get_the_category();
				$cat_name  = ! empty( $post_cats ) ? $post_cats[0]->name : __( 'Culinary', 'spicecraft' );
				?>
				<article class="sc-card sc-card--recipe">
					<div class="sc-card-media">
						<?php if ( has_post_thumbnail() ) : ?>
							<a href="<?php the_permalink(); ?>" class="sc-card-media-link">
								<?php the_post_thumbnail( 'medium_large', array( 'class' => 'sc-recipe-thumb', 'alt' => get_the_title(), 'loading' => 'lazy' ) ); ?>
							</a>
						<?php else : ?>
							<a href="<?php the_permalink(); ?>" class="sc-card-media-link sc-media-empty">
								<div class="sc-recipe-placeholder" aria-hidden="true">
									<svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
								</div>
							</a>
						<?php endif; ?>
						<span class="sc-recipe-category-tag"><?php echo esc_html( $cat_name ); ?></span>
					</div>

					<div class="sc-card-body">
						<h3 class="sc-card-title">
							<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
						</h3>
						<p class="sc-card-excerpt"><?php echo esc_html( wp_trim_words( get_the_excerpt(), 16 ) ); ?></p>
						<a href="<?php the_permalink(); ?>" class="sc-link-arrow sc-card-readmore">
							<span><?php esc_html_e( 'View Recipe', 'spicecraft' ); ?></span>
							<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
						</a>
					</div>
				</article>
				<?php
			endwhile;
			wp_reset_postdata();
			?>
		</div>

		<?php if ( ! empty( $cta_label ) ) : ?>
			<div class="sc-recipes-footer">
				<a href="<?php echo esc_url( $cta_url ); ?>" class="sc-btn sc-btn--secondary sc-btn--md">
					<span><?php echo esc_html( $cta_label ); ?></span>
					<svg class="sc-icon sc-icon-arrow" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
				</a>
			</div>
		<?php endif; ?>
	</div>
</section>
