<?php
/**
 * Homepage Template Part: Blog & Industry Insights
 * Semantic ID: #latest-insights
 *
 * Consumes native WordPress Posts.
 * Suppresses itself gracefully if no published posts exist.
 *
 * @package SpiceCraft
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$blog_settings = function_exists( 'spicecraft_get_homepage_section' )
	? spicecraft_get_homepage_section( 'blog' )
	: array();

$eyebrow      = ! empty( $blog_settings['eyebrow'] ) ? $blog_settings['eyebrow'] : '';
$heading      = ! empty( $blog_settings['heading'] ) ? $blog_settings['heading'] : '';
$description  = ! empty( $blog_settings['description'] ) ? $blog_settings['description'] : '';
$source       = ! empty( $blog_settings['source'] ) ? $blog_settings['source'] : 'latest';
$category_id  = ! empty( $blog_settings['category_id'] ) ? absint( $blog_settings['category_id'] ) : 0;
$selected_ids = ! empty( $blog_settings['selected_ids'] ) ? array_map( 'absint', (array) $blog_settings['selected_ids'] ) : array();
$limit        = ! empty( $blog_settings['limit'] ) ? absint( $blog_settings['limit'] ) : 3;
$cta_label    = ! empty( $blog_settings['cta_label'] ) ? $blog_settings['cta_label'] : '';
$cta_url      = ! empty( $blog_settings['cta_url'] ) ? $blog_settings['cta_url'] : home_url( '/blog/' );

$query_args = array(
	'post_type'      => 'post',
	'post_status'    => 'publish',
	'posts_per_page' => $limit,
	'no_found_rows'  => true,
);

if ( 'manual' === $source && ! empty( $selected_ids ) ) {
	$query_args['post__in'] = $selected_ids;
	$query_args['orderby']  = 'post__in';
} elseif ( 'category' === $source && $category_id > 0 ) {
	$query_args['cat'] = $category_id;
} else {
	$query_args['orderby'] = 'date';
	$query_args['order']   = 'DESC';
}

$blog_query = new WP_Query( $query_args );

if ( ! $blog_query->have_posts() ) {
	wp_reset_postdata();
	return;
}
?>

<section id="latest-insights" class="sc-home-section sc-home-blog" aria-labelledby="sec-heading-insights">
	<div class="sc-container">
		<div class="sc-section-header-split">
			<div class="sc-section-header-split__content">
				<?php if ( ! empty( $eyebrow ) ) : ?>
					<p class="sc-eyebrow"><?php echo esc_html( $eyebrow ); ?></p>
				<?php endif; ?>

				<?php if ( ! empty( $heading ) ) : ?>
					<h2 id="sec-heading-insights" class="sc-section-title"><?php echo esc_html( $heading ); ?></h2>
				<?php else : ?>
					<h2 id="sec-heading-insights" class="sc-section-title"><?php esc_html_e( 'Spice Knowledge & Market Insights', 'spicecraft' ); ?></h2>
				<?php endif; ?>

				<?php if ( ! empty( $description ) ) : ?>
					<p class="sc-section-subtitle"><?php echo esc_html( $description ); ?></p>
				<?php endif; ?>
			</div>

			<?php if ( ! empty( $cta_label ) ) : ?>
				<div class="sc-section-header-split__action">
					<a href="<?php echo esc_url( $cta_url ); ?>" class="sc-link-arrow">
						<span><?php echo esc_html( $cta_label ); ?></span>
						<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
					</a>
				</div>
			<?php endif; ?>
		</div>

		<div class="sc-grid sc-grid--3 sc-blog-grid">
			<?php
			while ( $blog_query->have_posts() ) :
				$blog_query->the_post();
				$post_cats = get_the_category();
				$cat_name  = ! empty( $post_cats ) ? $post_cats[0]->name : __( 'Industry', 'spicecraft' );
				?>
				<article class="sc-card sc-card--post">
					<div class="sc-card-media">
						<?php if ( has_post_thumbnail() ) : ?>
							<a href="<?php the_permalink(); ?>" class="sc-card-media-link">
								<?php the_post_thumbnail( 'medium_large', array( 'class' => 'sc-post-thumb', 'alt' => get_the_title(), 'loading' => 'lazy' ) ); ?>
							</a>
						<?php else : ?>
							<a href="<?php the_permalink(); ?>" class="sc-card-media-link sc-media-empty">
								<div class="sc-post-placeholder" aria-hidden="true">
									<svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>
								</div>
							</a>
						<?php endif; ?>
						<span class="sc-post-category-tag"><?php echo esc_html( $cat_name ); ?></span>
					</div>

					<div class="sc-card-body">
						<time class="sc-post-date" datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>">
							<?php echo esc_html( get_the_date( 'M j, Y' ) ); ?>
						</time>

						<h3 class="sc-card-title">
							<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
						</h3>

						<p class="sc-card-excerpt"><?php echo esc_html( wp_trim_words( get_the_excerpt(), 16 ) ); ?></p>

						<a href="<?php the_permalink(); ?>" class="sc-link-arrow sc-post-link">
							<span><?php esc_html_e( 'Read Article', 'spicecraft' ); ?></span>
							<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
						</a>
					</div>
				</article>
				<?php
			endwhile;
			wp_reset_postdata();
			?>
		</div>
	</div>
</section>
