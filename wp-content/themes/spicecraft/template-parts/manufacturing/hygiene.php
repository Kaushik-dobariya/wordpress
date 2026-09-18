<?php
/**
 * Manufacturing Section: Hygiene & Food Safety
 *
 * Sanitation protocols and food processing hygiene controls.
 *
 * @package SpiceCraft
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$hygiene = function_exists( 'spicecraft_get_manufacturing_section' )
	? spicecraft_get_manufacturing_section( 'hygiene' )
	: array();

if ( empty( $hygiene ) || ( empty( $hygiene['heading'] ) && empty( $hygiene['description'] ) && empty( $hygiene['practices'] ) ) ) {
	return;
}

$eyebrow     = $hygiene['eyebrow'] ?? '';
$heading     = $hygiene['heading'] ?? '';
$description = $hygiene['description'] ?? '';
$image_id    = absint( $hygiene['image_id'] ?? 0 );
$practices   = $hygiene['practices'] ?? array();
$cta_label   = $hygiene['cta_label'] ?? '';
$cta_url     = $hygiene['cta_url'] ?? '';
?>

<section id="sc-mfg-hygiene" class="sc-mfg-hygiene sc-section sc-section--alt" aria-label="<?php echo esc_attr( $heading ?: __( 'Hygiene & Food Safety Protocols', 'spicecraft' ) ); ?>">
	<div class="sc-container">
		<div class="sc-mfg-hygiene__grid">
			<div class="sc-mfg-hygiene__content">
				<?php if ( ! empty( $eyebrow ) ) : ?>
					<span class="sc-eyebrow"><?php echo esc_html( $eyebrow ); ?></span>
				<?php endif; ?>

				<?php if ( ! empty( $heading ) ) : ?>
					<h2 class="sc-section-title"><?php echo esc_html( $heading ); ?></h2>
				<?php endif; ?>

				<?php if ( ! empty( $description ) ) : ?>
					<p class="sc-section-desc"><?php echo nl2br( esc_html( $description ) ); ?></p>
				<?php endif; ?>

				<?php if ( ! empty( $practices ) && is_array( $practices ) ) : ?>
					<div class="sc-mfg-hygiene__practices" style="margin-top: var(--sc-space-6, 24px);">
						<?php foreach ( $practices as $pr ) :
							$pr_title = $pr['title'] ?? '';
							$pr_desc  = $pr['description'] ?? '';
							if ( empty( $pr_title ) && empty( $pr_desc ) ) continue;
							?>
							<div class="sc-mfg-hygiene__practice-item">
								<span class="dashicons dashicons-shield" style="color: var(--sc-color-primary, #b33927); font-size: 20px; flex-shrink: 0;"></span>
								<div>
									<?php if ( ! empty( $pr_title ) ) : ?>
										<h3 class="sc-mfg-hygiene__practice-title"><?php echo esc_html( $pr_title ); ?></h3>
									<?php endif; ?>
									<?php if ( ! empty( $pr_desc ) ) : ?>
										<p class="sc-mfg-hygiene__practice-desc"><?php echo esc_html( $pr_desc ); ?></p>
									<?php endif; ?>
								</div>
							</div>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>

				<?php if ( ! empty( $cta_label ) && ! empty( $cta_url ) ) : ?>
					<div class="sc-mfg-hygiene__cta" style="margin-top: var(--sc-space-6, 24px);">
						<a href="<?php echo esc_url( $cta_url ); ?>" class="sc-btn sc-btn--secondary">
							<?php echo esc_html( $cta_label ); ?>
						</a>
					</div>
				<?php endif; ?>
			</div>

			<?php if ( $image_id ) : ?>
				<div class="sc-mfg-hygiene__media">
					<?php echo wp_get_attachment_image( $image_id, 'large', false, array( 'class' => 'sc-img-fluid sc-rounded', 'loading' => 'lazy' ) ); ?>
				</div>
			<?php endif; ?>
		</div>
	</div>
</section>
