<?php
/**
 * Manufacturing Section: Warehousing & Material Handling
 *
 * Finished goods storage, climate controls, and inventory dispatch handling.
 *
 * @package SpiceCraft
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$wh = function_exists( 'spicecraft_get_manufacturing_section' )
	? spicecraft_get_manufacturing_section( 'warehousing' )
	: array();

if ( empty( $wh ) || ( empty( $wh['heading'] ) && empty( $wh['description'] ) && empty( $wh['highlights'] ) ) ) {
	return;
}

$eyebrow     = $wh['eyebrow'] ?? '';
$heading     = $wh['heading'] ?? '';
$description = $wh['description'] ?? '';
$image_id    = absint( $wh['image_id'] ?? 0 );
$highlights  = $wh['highlights'] ?? array();
?>

<section id="sc-mfg-warehousing" class="sc-mfg-warehousing sc-section sc-section--alt" aria-label="<?php echo esc_attr( $heading ?: __( 'Warehousing & Material Handling', 'spicecraft' ) ); ?>">
	<div class="sc-container">
		<div class="sc-mfg-warehousing__grid">
			<?php if ( $image_id ) : ?>
				<div class="sc-mfg-warehousing__media">
					<?php echo wp_get_attachment_image( $image_id, 'large', false, array( 'class' => 'sc-img-fluid sc-rounded', 'loading' => 'lazy' ) ); ?>
				</div>
			<?php endif; ?>

			<div class="sc-mfg-warehousing__content">
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
					<div class="sc-mfg-warehousing__highlights" style="margin-top: var(--sc-space-6, 24px);">
						<?php foreach ( $highlights as $hl ) :
							$hl_t = $hl['title'] ?? '';
							$hl_d = $hl['description'] ?? '';
							if ( empty( $hl_t ) && empty( $hl_d ) ) continue;
							?>
							<div class="sc-mfg-warehousing__highlight-item">
								<span class="dashicons dashicons-location-alt" style="color: var(--sc-color-secondary, #d97706); font-size: 20px; flex-shrink: 0;"></span>
								<div>
									<?php if ( ! empty( $hl_t ) ) : ?>
										<h3 class="sc-mfg-warehousing__highlight-title"><?php echo esc_html( $hl_t ); ?></h3>
									<?php endif; ?>
									<?php if ( ! empty( $hl_d ) ) : ?>
										<p class="sc-mfg-warehousing__highlight-desc"><?php echo esc_html( $hl_d ); ?></p>
									<?php endif; ?>
								</div>
							</div>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>
			</div>
		</div>
	</div>
</section>
