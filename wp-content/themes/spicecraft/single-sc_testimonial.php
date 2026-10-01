<?php
/**
 * The template for displaying a single Testimonial / Client Review.
 *
 * Supports native WordPress admin previews and direct links.
 *
 * @package SpiceCraft
 * @since 1.3.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

$post_id         = get_the_ID();
$role            = get_post_meta( $post_id, '_sc_testimonial_role', true );
$company         = get_post_meta( $post_id, '_sc_testimonial_company', true );
$location        = get_post_meta( $post_id, '_sc_testimonial_location', true );
$rating          = get_post_meta( $post_id, '_sc_testimonial_rating', true );
$company_logo_id = get_post_meta( $post_id, '_sc_testimonial_company_logo_id', true );
$title           = get_the_title();

$meta_parts = array_filter(
	array(
		$role,
		$company,
		$location,
	)
);
?>

<main id="primary" class="site-main sc-testimonial-single-page">
	<div class="sc-container">
		<!-- Back to archive navigation -->
		<nav class="sc-testimonial-nav" aria-label="<?php esc_attr_e( 'Testimonials Navigation', 'spicecraft' ); ?>">
			<a href="<?php echo esc_url( function_exists( 'spicecraft_get_testimonials_url' ) ? spicecraft_get_testimonials_url() : home_url( '/testimonials/' ) ); ?>" class="sc-back-link">
				<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>
				<span><?php esc_html_e( 'Back to All Client Stories', 'spicecraft' ); ?></span>
			</a>
		</nav>

		<article id="post-<?php the_ID(); ?>" <?php post_class( 'sc-testimonial-single-card' ); ?> itemscope itemtype="https://schema.org/Review">
			<header class="sc-testimonial-single-header">
				<span class="sc-badge sc-badge--accent"><?php esc_html_e( 'Verified Client Endorsement', 'spicecraft' ); ?></span>
				<h1 class="sc-testimonial-single-title">
					<?php
					/* translators: %s: Client name */
					printf( esc_html__( 'Client Story: %s', 'spicecraft' ), esc_html( $title ) );
					?>
				</h1>

				<?php if ( ! empty( $rating ) && absint( $rating ) >= 1 ) : ?>
					<div class="sc-testimonial-single-rating" itemprop="reviewRating" itemscope itemtype="https://schema.org/Rating">
						<meta itemprop="ratingValue" content="<?php echo esc_attr( $rating ); ?>">
						<meta itemprop="bestRating" content="5">
						<?php
						if ( function_exists( 'spicecraft_render_testimonial_stars' ) ) {
							spicecraft_render_testimonial_stars( $rating );
						}
						?>
					</div>
				<?php endif; ?>
			</header>

			<div class="sc-testimonial-single-body" itemprop="reviewBody">
				<div class="sc-testimonial-single-quote-mark" aria-hidden="true">&ldquo;</div>
				<blockquote class="sc-testimonial-single-text">
					<?php the_content(); ?>
				</blockquote>
			</div>

			<footer class="sc-testimonial-single-footer">
				<div class="sc-testimonial-author" itemprop="author" itemscope itemtype="https://schema.org/Person">
					<?php if ( has_post_thumbnail() ) : ?>
						<div class="sc-author-avatar sc-author-avatar--lg">
							<?php the_post_thumbnail( array( 80, 80 ), array( 'class' => 'sc-avatar-img', 'alt' => esc_attr( $title ) ) ); ?>
						</div>
					<?php else : ?>
						<div class="sc-author-avatar sc-author-avatar--lg sc-author-avatar--initials" aria-hidden="true">
							<span><?php echo esc_html( mb_substr( $title, 0, 1 ) ); ?></span>
						</div>
					<?php endif; ?>

					<div class="sc-author-meta">
						<strong class="sc-author-name sc-author-name--lg" itemprop="name"><?php echo esc_html( $title ); ?></strong>
						<?php if ( ! empty( $meta_parts ) ) : ?>
							<span class="sc-author-role sc-author-role--lg">
								<?php echo esc_html( implode( ' · ', $meta_parts ) ); ?>
							</span>
						<?php endif; ?>
					</div>
				</div>

				<?php if ( ! empty( $company_logo_id ) ) : ?>
					<div class="sc-testimonial-company-logo-wrap sc-testimonial-company-logo-wrap--lg">
						<?php
						echo wp_get_attachment_image(
							$company_logo_id,
							'medium',
							false,
							array(
								'class'   => 'sc-testimonial-company-logo',
								'alt'     => esc_attr( ! empty( $company ) ? sprintf( __( '%s logo', 'spicecraft' ), $company ) : __( 'Client company logo', 'spicecraft' ) ),
								'loading' => 'lazy',
							)
						);
						?>
					</div>
				<?php endif; ?>
			</footer>
		</article>

		<!-- Bottom CTA -->
		<div class="sc-testimonial-single-cta">
			<h3><?php esc_html_e( 'Experience the SpiceCraft Difference in Your Kitchen', 'spicecraft' ); ?></h3>
			<p><?php esc_html_e( 'Contact our trade team for wholesale pricing, custom grinds, and sample spice batches.', 'spicecraft' ); ?></p>
			<a href="<?php echo esc_url( home_url( '/#contact' ) ); ?>" class="sc-btn sc-btn--primary">
				<?php esc_html_e( 'Contact Commercial Sales Desk', 'spicecraft' ); ?>
			</a>
		</div>
	</div>
</main>

<?php
get_footer();
