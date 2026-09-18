<?php
/**
 * Quality & Sourcing Section: Sourcing Philosophy
 *
 * Ethical procurement, origin relationships, and quality cultivation criteria.
 *
 * @package SpiceCraft
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$src = function_exists( 'spicecraft_get_quality_section' )
	? spicecraft_get_quality_section( 'sourcing' )
	: array();

if ( empty( $src ) || ( empty( $src['heading'] ) && empty( $src['description'] ) && empty( $src['highlights'] ) ) ) {
	return;
}

$eyebrow       = $src['eyebrow'] ?? '';
$heading       = $src['heading'] ?? '';
$description   = $src['description'] ?? '';
$primary_img   = absint( $src['image_id'] ?? 0 );
$secondary_img = absint( $src['image_secondary_id'] ?? 0 );
$highlights    = $src['highlights'] ?? array();
$cta_label     = $src['cta_label'] ?? '';
$cta_url       = $src['cta_url'] ?? '';
?>

<section id="sc-quality-sourcing" class="sc-quality-sourcing sc-section" aria-label="<?php echo esc_attr( $heading ?: __( 'Sourcing Philosophy', 'spicecraft' ) ); ?>">
	<div class="sc-container">
		<div class="sc-quality-sourcing__grid">
			<div class="sc-quality-sourcing__content">
				<?php if ( ! empty( $eyebrow ) ) : ?>
					<span class="sc-eyebrow"><?php echo esc_html( $eyebrow ); ?></span>
				<?php endif; ?>

				<?php if ( ! empty( $heading ) ) : ?>
					<h2 class="sc-section-title"><?php echo esc_html( $heading ); ?></h2>
				<?php endif; ?>

				<?php if ( ! empty( $description ) ) : ?>
					<p class="sc-section-desc"><?php echo nl2br( esc_html( $description ) ); ?></p>
				<?php endif; ?>

				<?php if ( ! empty( $highlights ) && is_array( $highlights ) ) : ?>
					<div class="sc-quality-sourcing__highlights" style="margin-top: var(--sc-space-6, 24px);">
						<?php foreach ( $highlights as $hl ) :
							$hlt = $hl['title'] ?? '';
							$hld = $hl['description'] ?? '';
							if ( empty( $hlt ) && empty( $hld ) ) continue;
							?>
							<div class="sc-quality-sourcing__hl-item">
								<span class="dashicons dashicons-location" style="color: var(--sc-color-secondary, #d97706); font-size: 20px; flex-shrink: 0;"></span>
								<div>
									<?php if ( ! empty( $hlt ) ) : ?>
										<h3 class="sc-quality-sourcing__hl-title"><?php echo esc_html( $hlt ); ?></h3>
									<?php endif; ?>
									<?php if ( ! empty( $hld ) ) : ?>
										<p class="sc-quality-sourcing__hl-desc"><?php echo esc_html( $hld ); ?></p>
									<?php endif; ?>
								</div>
							</div>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>

				<?php if ( ! empty( $cta_label ) && ! empty( $cta_url ) ) : ?>
					<div class="sc-quality-sourcing__cta" style="margin-top: var(--sc-space-8, 32px);">
						<a href="<?php echo esc_url( $cta_url ); ?>" class="sc-btn sc-btn--primary">
							<?php echo esc_html( $cta_label ); ?>
						</a>
					</div>
				<?php endif; ?>
			</div>

			<?php if ( $primary_img || $secondary_img ) : ?>
				<div class="sc-quality-sourcing__visuals">
					<?php if ( $primary_img ) : ?>
						<div class="sc-quality-sourcing__img-main">
							<?php echo wp_get_attachment_image( $primary_img, 'large', false, array( 'class' => 'sc-img-fluid sc-rounded', 'loading' => 'lazy' ) ); ?>
						</div>
					<?php endif; ?>
					<?php if ( $secondary_img ) : ?>
						<div class="sc-quality-sourcing__img-sub">
							<?php echo wp_get_attachment_image( $secondary_img, 'medium_large', false, array( 'class' => 'sc-img-fluid sc-rounded', 'loading' => 'lazy' ) ); ?>
						</div>
					<?php endif; ?>
				</div>
			<?php endif; ?>
		</div>
	</div>
</section>
