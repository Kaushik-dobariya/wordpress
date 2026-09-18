<?php
/**
 * Manufacturing Section: Introduction
 *
 * Operational philosophy and overarching manufacturing standards.
 *
 * @package SpiceCraft
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$intro = function_exists( 'spicecraft_get_manufacturing_section' )
	? spicecraft_get_manufacturing_section( 'introduction' )
	: array();

if ( empty( $intro ) || ( empty( $intro['heading'] ) && empty( $intro['content'] ) ) ) {
	return;
}

$eyebrow       = $intro['eyebrow'] ?? '';
$heading       = $intro['heading'] ?? '';
$content       = $intro['content'] ?? '';
$primary_img   = absint( $intro['image_primary_id'] ?? 0 );
$secondary_img = absint( $intro['image_secondary_id'] ?? 0 );
$cta_label     = $intro['cta_label'] ?? '';
$cta_url       = $intro['cta_url'] ?? '';
?>

<section id="sc-mfg-intro" class="sc-mfg-intro sc-section" aria-label="<?php echo esc_attr( $heading ?: __( 'Manufacturing Philosophy', 'spicecraft' ) ); ?>">
	<div class="sc-container">
		<div class="sc-mfg-intro__grid">
			<div class="sc-mfg-intro__content">
				<?php if ( ! empty( $eyebrow ) ) : ?>
					<span class="sc-eyebrow"><?php echo esc_html( $eyebrow ); ?></span>
				<?php endif; ?>

				<?php if ( ! empty( $heading ) ) : ?>
					<h2 class="sc-section-title"><?php echo esc_html( $heading ); ?></h2>
				<?php endif; ?>

				<?php if ( ! empty( $content ) ) : ?>
					<div class="sc-mfg-intro__body sc-prose">
						<?php echo wp_kses_post( wpautop( $content ) ); ?>
					</div>
				<?php endif; ?>

				<?php if ( ! empty( $cta_label ) && ! empty( $cta_url ) ) : ?>
					<div class="sc-mfg-intro__action" style="margin-top: var(--sc-space-6, 24px);">
						<a href="<?php echo esc_url( $cta_url ); ?>" class="sc-btn sc-btn--secondary">
							<?php echo esc_html( $cta_label ); ?>
						</a>
					</div>
				<?php endif; ?>
			</div>

			<?php if ( $primary_img || $secondary_img ) : ?>
				<div class="sc-mfg-intro__visuals">
					<?php if ( $primary_img ) : ?>
						<div class="sc-mfg-intro__img-main">
							<?php echo wp_get_attachment_image( $primary_img, 'large', false, array( 'class' => 'sc-img-fluid', 'loading' => 'lazy' ) ); ?>
						</div>
					<?php endif; ?>
					<?php if ( $secondary_img ) : ?>
						<div class="sc-mfg-intro__img-sub">
							<?php echo wp_get_attachment_image( $secondary_img, 'medium_large', false, array( 'class' => 'sc-img-fluid', 'loading' => 'lazy' ) ); ?>
						</div>
					<?php endif; ?>
				</div>
			<?php endif; ?>
		</div>
	</div>
</section>
