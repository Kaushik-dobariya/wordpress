<?php
/**
 * Homepage Template Part: Client & Chef Testimonials
 * Semantic ID: #testimonials
 *
 * Consumes the `spicecraft_testimonial` Custom Post Type.
 * Adheres strictly to the NO FAKE DATA policy: suppresses cleanly if no testimonials exist.
 *
 * @package SpiceCraft
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$tst_settings = function_exists( 'spicecraft_get_homepage_section' )
	? spicecraft_get_homepage_section( 'testimonials' )
	: array();

$eyebrow      = ! empty( $tst_settings['eyebrow'] ) ? $tst_settings['eyebrow'] : '';
$heading      = ! empty( $tst_settings['heading'] ) ? $tst_settings['heading'] : '';
$description  = ! empty( $tst_settings['description'] ) ? $tst_settings['description'] : '';
$limit        = ! empty( $tst_settings['limit'] ) ? absint( $tst_settings['limit'] ) : 6;
$selected_ids = ! empty( $tst_settings['selected_ids'] ) ? array_map( 'absint', (array) $tst_settings['selected_ids'] ) : array();

$query_args = array(
	'post_type'      => 'sc_testimonial',
	'post_status'    => 'publish',
	'posts_per_page' => $limit,
	'meta_key'       => '_sc_testimonial_order',
	'orderby'        => 'meta_value_num',
	'order'          => 'ASC',
	'no_found_rows'  => true,
);

if ( ! empty( $selected_ids ) ) {
	$query_args['post__in'] = $selected_ids;
}

$testimonials_query = new WP_Query( $query_args );

// Graceful empty state: If no testimonials exist in DB, omit section cleanly
if ( ! $testimonials_query->have_posts() ) {
	wp_reset_postdata();
	return;
}
?>

<section id="testimonials" class="sc-home-section sc-home-testimonials sc-surface-warm" aria-labelledby="sec-heading-testimonials">
	<div class="sc-container">
		<header class="sc-section-header sc-section-header--center">
			<?php if ( ! empty( $eyebrow ) ) : ?>
				<p class="sc-eyebrow"><?php echo esc_html( $eyebrow ); ?></p>
			<?php endif; ?>

			<?php if ( ! empty( $heading ) ) : ?>
				<h2 id="sec-heading-testimonials" class="sc-section-title"><?php echo esc_html( $heading ); ?></h2>
			<?php else : ?>
				<h2 id="sec-heading-testimonials" class="sc-section-title"><?php esc_html_e( 'Endorsements from the Culinary Community', 'spicecraft' ); ?></h2>
			<?php endif; ?>

			<?php if ( ! empty( $description ) ) : ?>
				<p class="sc-section-subtitle"><?php echo esc_html( $description ); ?></p>
			<?php endif; ?>
		</header>

		<div class="sc-testimonials-grid">
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

		<div class="sc-testimonials-footer sc-text-center">
			<a href="<?php echo esc_url( function_exists( 'spicecraft_get_testimonials_url' ) ? spicecraft_get_testimonials_url() : home_url( '/testimonials/' ) ); ?>" class="sc-btn sc-btn--outline sc-btn--icon-right">
				<span><?php esc_html_e( 'View All Client Stories', 'spicecraft' ); ?></span>
				<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
			</a>
		</div>
	</div>
</section>
