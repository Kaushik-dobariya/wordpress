<?php
/**
 * SpiceCraft Reusable Component: Testimonials Carousel / Grid
 *
 * Parameters:
 * - heading (string)
 * - eyebrow (string)
 * - description (string)
 * - limit (int, default: 3)
 * - columns (int: 2, 3 - default 3)
 * - featured_only (bool, default: false)
 * - show_all_link (bool, default: true)
 * - cta_url (string)
 * - cta_text (string)
 *
 * @package SpiceCraft
 * @since 1.5.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$heading       = ! empty( $args['heading'] ) ? $args['heading'] : '';
$eyebrow       = ! empty( $args['eyebrow'] ) ? $args['eyebrow'] : '';
$description   = ! empty( $args['description'] ) ? $args['description'] : '';
$limit         = ! empty( $args['limit'] ) ? absint( $args['limit'] ) : 3;
$columns       = ! empty( $args['columns'] ) && 2 === (int) $args['columns'] ? 2 : 3;
$featured_only = ! empty( $args['featured_only'] );
$show_all      = ! isset( $args['show_all_link'] ) || ! empty( $args['show_all_link'] );
$cta_url       = ! empty( $args['cta_url'] ) ? $args['cta_url'] : ( function_exists( 'spicecraft_get_testimonials_url' ) ? spicecraft_get_testimonials_url() : home_url( '/testimonials/' ) );
$cta_text      = ! empty( $args['cta_text'] ) ? $args['cta_text'] : __( 'View All Client Stories', 'spicecraft' );

$query_args = array(
	'post_type'      => 'sc_testimonial',
	'post_status'    => 'publish',
	'posts_per_page' => $limit,
	'meta_key'       => '_sc_testimonial_order',
	'orderby'        => 'meta_value_num',
	'order'          => 'ASC',
	'no_found_rows'  => true,
);

if ( $featured_only ) {
	$query_args['meta_query'] = array(
		array(
			'key'   => '_sc_testimonial_featured',
			'value' => '1',
		),
	);
}

$testimonials_query = new WP_Query( $query_args );

if ( ! $testimonials_query->have_posts() ) {
	wp_reset_postdata();
	return;
}
?>
<section class="sc-comp-testimonials sc-surface-warm">
	<div class="sc-container">
		<?php if ( $heading || $eyebrow || $description ) : ?>
			<header class="sc-section-header sc-section-header--center">
				<?php if ( $eyebrow ) : ?>
					<p class="sc-eyebrow"><?php echo esc_html( $eyebrow ); ?></p>
				<?php endif; ?>
				<?php if ( $heading ) : ?>
					<h2 class="sc-section-title"><?php echo esc_html( $heading ); ?></h2>
				<?php endif; ?>
				<?php if ( $description ) : ?>
					<p class="sc-section-subtitle"><?php echo esc_html( $description ); ?></p>
				<?php endif; ?>
			</header>
		<?php endif; ?>

		<div class="sc-testimonials-grid sc-grid-cols-<?php echo esc_attr( $columns ); ?>">
			<?php
			while ( $testimonials_query->have_posts() ) :
				$testimonials_query->the_post();
				get_template_part(
					'template-parts/content/testimonial-card',
					null,
					array(
						'post_id' => get_the_ID(),
					)
				);
			endwhile;
			wp_reset_postdata();
			?>
		</div>

		<?php if ( $show_all && $cta_url ) : ?>
			<div class="sc-testimonials-footer sc-text-center">
				<a href="<?php echo esc_url( $cta_url ); ?>" class="sc-btn sc-btn--outline sc-btn--icon-right">
					<span><?php echo esc_html( $cta_text ); ?></span>
					<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
				</a>
			</div>
		<?php endif; ?>
	</div>
</section>
