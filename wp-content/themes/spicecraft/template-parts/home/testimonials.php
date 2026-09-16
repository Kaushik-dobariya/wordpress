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
	'post_type'      => 'spicecraft_testimonial',
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
				$post_id = get_the_ID();
				$role    = get_post_meta( $post_id, '_sc_testimonial_role', true );
				$company = get_post_meta( $post_id, '_sc_testimonial_company', true );
				$rating  = get_post_meta( $post_id, '_sc_testimonial_rating', true );
				?>
				<article class="sc-testimonial-card">
					<div class="sc-testimonial-quote-mark" aria-hidden="true">&ldquo;</div>

					<?php if ( ! empty( $rating ) && absint( $rating ) >= 1 ) : ?>
						<div class="sc-testimonial-stars" aria-label="<?php echo esc_attr( sprintf( _n( '%d star rating', '%d stars rating', $rating, 'spicecraft' ), $rating ) ); ?>">
							<?php for ( $s = 0; $s < absint( $rating ); $s++ ) : ?>
								<svg class="sc-star-icon" width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
							<?php endfor; ?>
						</div>
					<?php endif; ?>

					<blockquote class="sc-testimonial-text">
						<?php echo wp_kses_post( wpautop( get_the_content() ) ); ?>
					</blockquote>

					<div class="sc-testimonial-author">
						<?php if ( has_post_thumbnail() ) : ?>
							<div class="sc-author-avatar">
								<?php the_post_thumbnail( array( 54, 54 ), array( 'class' => 'sc-avatar-img', 'alt' => get_the_title() ) ); ?>
							</div>
						<?php endif; ?>
						<div class="sc-author-meta">
							<strong class="sc-author-name"><?php the_title(); ?></strong>
							<?php if ( ! empty( $role ) || ! empty( $company ) ) : ?>
								<span class="sc-author-role">
									<?php echo esc_html( implode( ' · ', array_filter( array( $role, $company ) ) ) ); ?>
								</span>
							<?php endif; ?>
						</div>
					</div>
				</article>
				<?php
			endwhile;
			wp_reset_postdata();
			?>
		</div>
	</div>
</section>
