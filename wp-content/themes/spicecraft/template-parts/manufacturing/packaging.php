<?php
/**
 * Manufacturing Section: Packaging
 *
 * Configured retail, foodservice, and bulk packaging formats.
 *
 * @package SpiceCraft
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$pkg = function_exists( 'spicecraft_get_manufacturing_section' )
	? spicecraft_get_manufacturing_section( 'packaging' )
	: array();

if ( empty( $pkg ) || ( empty( $pkg['heading'] ) && empty( $pkg['description'] ) && empty( $pkg['capabilities'] ) ) ) {
	return;
}

$eyebrow       = $pkg['eyebrow'] ?? '';
$heading       = $pkg['heading'] ?? '';
$description   = $pkg['description'] ?? '';
$image_id      = absint( $pkg['image_id'] ?? 0 );
$image_sub_id  = absint( $pkg['image_secondary_id'] ?? 0 );
$capabilities  = $pkg['capabilities'] ?? array();
$cta_label     = $pkg['cta_label'] ?? '';
$cta_url       = $pkg['cta_url'] ?? '';
?>

<section id="sc-mfg-packaging" class="sc-mfg-packaging sc-section" aria-label="<?php echo esc_attr( $heading ?: __( 'Packaging Capabilities', 'spicecraft' ) ); ?>">
	<div class="sc-container">
		<div class="sc-section-header text-center" style="max-width: 740px; margin: 0 auto var(--sc-space-10, 40px);">
			<?php if ( ! empty( $eyebrow ) ) : ?>
				<span class="sc-eyebrow"><?php echo esc_html( $eyebrow ); ?></span>
			<?php endif; ?>

			<?php if ( ! empty( $heading ) ) : ?>
				<h2 class="sc-section-title"><?php echo esc_html( $heading ); ?></h2>
			<?php endif; ?>

			<?php if ( ! empty( $description ) ) : ?>
				<p class="sc-section-desc"><?php echo nl2br( esc_html( $description ) ); ?></p>
			<?php endif; ?>
		</div>

		<?php if ( ! empty( $capabilities ) && is_array( $capabilities ) ) : ?>
			<div class="sc-mfg-packaging__grid">
				<?php foreach ( $capabilities as $cap ) :
					$cap_title = $cap['title'] ?? '';
					$cap_desc  = $cap['description'] ?? '';
					if ( empty( $cap_title ) && empty( $cap_desc ) ) continue;
					?>
					<div class="sc-mfg-packaging__card sc-card">
						<div class="sc-mfg-packaging__card-icon">
							<span class="dashicons dashicons-archive"></span>
						</div>
						<?php if ( ! empty( $cap_title ) ) : ?>
							<h3 class="sc-mfg-packaging__card-title"><?php echo esc_html( $cap_title ); ?></h3>
						<?php endif; ?>
						<?php if ( ! empty( $cap_desc ) ) : ?>
							<p class="sc-mfg-packaging__card-desc"><?php echo esc_html( $cap_desc ); ?></p>
						<?php endif; ?>
					</div>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>

		<?php if ( $image_id || $image_sub_id ) : ?>
			<div class="sc-mfg-packaging__media-grid" style="margin-top: var(--sc-space-8, 32px);">
				<?php if ( $image_id ) : ?>
					<div class="sc-mfg-packaging__media-item">
						<?php echo wp_get_attachment_image( $image_id, 'large', false, array( 'class' => 'sc-img-fluid sc-rounded', 'loading' => 'lazy' ) ); ?>
					</div>
				<?php endif; ?>
				<?php if ( $image_sub_id ) : ?>
					<div class="sc-mfg-packaging__media-item">
						<?php echo wp_get_attachment_image( $image_sub_id, 'large', false, array( 'class' => 'sc-img-fluid sc-rounded', 'loading' => 'lazy' ) ); ?>
					</div>
				<?php endif; ?>
			</div>
		<?php endif; ?>

		<?php if ( ! empty( $cta_label ) && ! empty( $cta_url ) ) : ?>
			<div class="sc-mfg-packaging__cta text-center" style="margin-top: var(--sc-space-8, 32px);">
				<a href="<?php echo esc_url( $cta_url ); ?>" class="sc-btn sc-btn--primary">
					<?php echo esc_html( $cta_label ); ?>
				</a>
			</div>
		<?php endif; ?>
	</div>
</section>
