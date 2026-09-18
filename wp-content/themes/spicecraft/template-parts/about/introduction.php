<?php
/**
 * About Section: Company Introduction
 *
 * Answers Who We Are, What We Do, and What We Stand For through an editorial
 * dual-image composition and readable typography.
 *
 * @package SpiceCraft
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$intro = function_exists( 'spicecraft_get_about_section' )
	? spicecraft_get_about_section( 'introduction' )
	: array();

if ( empty( $intro ) ) {
	return;
}

$eyebrow       = $intro['eyebrow'] ?? '';
$heading       = $intro['heading'] ?? '';
$content       = $intro['content'] ?? '';
$primary_img   = absint( $intro['primary_image_id'] ?? 0 );
$secondary_img = absint( $intro['secondary_image_id'] ?? 0 );
$cta_label     = $intro['cta_label'] ?? '';
$cta_url       = $intro['cta_url'] ?? '';

if ( empty( $heading ) && empty( $content ) ) {
	return;
}
?>

<section id="company-intro" class="sc-about-intro" aria-label="<?php echo esc_attr( ! empty( $heading ) ? $heading : __( 'Company Introduction', 'spicecraft' ) ); ?>">
	<div class="sc-container">
		<div class="sc-about-intro__grid <?php echo empty( $primary_img ) ? 'sc-about-intro__grid--no-media' : ''; ?>">
			<!-- Text Content -->
			<div class="sc-about-intro__content">
				<?php if ( ! empty( $eyebrow ) ) : ?>
					<span class="sc-section-eyebrow"><?php echo esc_html( $eyebrow ); ?></span>
				<?php endif; ?>

				<?php if ( ! empty( $heading ) ) : ?>
					<h2 class="sc-about-intro__heading"><?php echo esc_html( $heading ); ?></h2>
				<?php endif; ?>

				<?php if ( ! empty( $content ) ) : ?>
					<div class="sc-about-intro__text entry-content">
						<?php echo wp_kses_post( wpautop( $content ) ); ?>
					</div>
				<?php endif; ?>

				<?php if ( ! empty( $cta_label ) && ! empty( $cta_url ) ) : ?>
					<div class="sc-about-intro__cta">
						<a href="<?php echo esc_url( $cta_url ); ?>" class="sc-btn sc-btn--secondary sc-btn--md">
							<?php echo esc_html( $cta_label ); ?> &rarr;
						</a>
					</div>
				<?php endif; ?>
			</div>

			<!-- Image Composition -->
			<?php if ( ! empty( $primary_img ) ) : ?>
				<div class="sc-about-intro__media">
					<div class="sc-about-intro__media-primary">
						<?php
						echo wp_get_attachment_image(
							$primary_img,
							'large',
							false,
							array(
								'class'   => 'sc-about-intro__img sc-about-intro__img--main',
								'loading' => 'lazy',
							)
						);
						?>
					</div>
					<?php if ( ! empty( $secondary_img ) ) : ?>
						<div class="sc-about-intro__media-secondary">
							<?php
							echo wp_get_attachment_image(
								$secondary_img,
								'medium_large',
								false,
								array(
									'class'   => 'sc-about-intro__img sc-about-intro__img--inset',
									'loading' => 'lazy',
								)
							);
							?>
						</div>
					<?php endif; ?>
				</div>
			<?php endif; ?>
		</div>
	</div>
</section>
