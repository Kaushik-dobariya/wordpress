<?php
/**
 * Template part for displaying a single testimonial card.
 *
 * Can be included in loops or passed a specific $args['post_id'].
 *
 * @package SpiceCraft
 * @since 1.3.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$post_id = ! empty( $args['post_id'] ) ? absint( $args['post_id'] ) : get_the_ID();
if ( ! $post_id ) {
	return;
}

$role            = get_post_meta( $post_id, '_sc_testimonial_role', true );
$company         = get_post_meta( $post_id, '_sc_testimonial_company', true );
$location        = get_post_meta( $post_id, '_sc_testimonial_location', true );
$rating          = get_post_meta( $post_id, '_sc_testimonial_rating', true );
$featured        = get_post_meta( $post_id, '_sc_testimonial_featured', true );
$company_logo_id = get_post_meta( $post_id, '_sc_testimonial_company_logo_id', true );
$title           = get_the_title( $post_id );
$content         = get_post_field( 'post_content', $post_id );

// Assemble meta parts cleanly without trailing/empty separators
$meta_parts = array_filter(
	array(
		$role,
		$company,
		$location,
	)
);

$extra_class = ! empty( $args['class'] ) ? ' ' . esc_attr( $args['class'] ) : '';
if ( ! empty( $featured ) ) {
	$extra_class .= ' sc-testimonial-card--featured';
}
?>

<article id="testimonial-<?php echo esc_attr( $post_id ); ?>" class="sc-testimonial-card<?php echo esc_attr( $extra_class ); ?>" itemscope itemtype="https://schema.org/Review">
	<div class="sc-testimonial-card-header">
		<div class="sc-testimonial-quote-mark" aria-hidden="true">&ldquo;</div>
		<?php if ( ! empty( $rating ) && absint( $rating ) >= 1 ) : ?>
			<div class="sc-testimonial-card-rating" itemprop="reviewRating" itemscope itemtype="https://schema.org/Rating">
				<meta itemprop="ratingValue" content="<?php echo esc_attr( $rating ); ?>">
				<meta itemprop="bestRating" content="5">
				<?php
				if ( function_exists( 'spicecraft_render_testimonial_stars' ) ) {
					spicecraft_render_testimonial_stars( $rating );
				}
				?>
			</div>
		<?php endif; ?>
	</div>

	<blockquote class="sc-testimonial-text" itemprop="reviewBody">
		<?php echo wp_kses_post( wpautop( $content ) ); ?>
	</blockquote>

	<footer class="sc-testimonial-footer">
		<div class="sc-testimonial-author" itemprop="author" itemscope itemtype="https://schema.org/Person">
			<?php if ( has_post_thumbnail( $post_id ) ) : ?>
				<div class="sc-author-avatar">
					<?php
					echo get_the_post_thumbnail(
						$post_id,
						array( 64, 64 ),
						array(
							'class'   => 'sc-avatar-img',
							'alt'     => esc_attr( sprintf( __( 'Portrait of %s', 'spicecraft' ), $title ) ),
							'loading' => 'lazy',
						)
					);
					?>
				</div>
			<?php else : ?>
				<div class="sc-author-avatar sc-author-avatar--initials" aria-hidden="true">
					<span><?php echo esc_html( mb_substr( $title, 0, 1 ) ); ?></span>
				</div>
			<?php endif; ?>

			<div class="sc-author-meta">
				<strong class="sc-author-name" itemprop="name"><?php echo esc_html( $title ); ?></strong>
				<?php if ( ! empty( $meta_parts ) ) : ?>
					<span class="sc-author-role">
						<?php echo esc_html( implode( ' · ', $meta_parts ) ); ?>
					</span>
				<?php endif; ?>
			</div>
		</div>

		<?php if ( ! empty( $company_logo_id ) ) : ?>
			<div class="sc-testimonial-company-logo-wrap">
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
