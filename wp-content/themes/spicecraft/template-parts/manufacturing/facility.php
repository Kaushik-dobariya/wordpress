<?php
/**
 * Manufacturing Section: Facility Overview
 *
 * Physical plant structure, operational layout, and key facility highlights.
 *
 * @package SpiceCraft
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$facility = function_exists( 'spicecraft_get_manufacturing_section' )
	? spicecraft_get_manufacturing_section( 'facility' )
	: array();

if ( empty( $facility ) || ( empty( $facility['heading'] ) && empty( $facility['description'] ) && empty( $facility['image_id'] ) ) ) {
	return;
}

$eyebrow       = $facility['eyebrow'] ?? '';
$heading       = $facility['heading'] ?? '';
$description   = $facility['description'] ?? '';
$image_id      = absint( $facility['image_id'] ?? 0 );
$image_sub_id  = absint( $facility['image_secondary_id'] ?? 0 );
$highlights    = $facility['highlights'] ?? array();
?>

<section id="sc-mfg-facility" class="sc-mfg-facility sc-section sc-section--alt" aria-label="<?php echo esc_attr( $heading ?: __( 'Facility Overview', 'spicecraft' ) ); ?>">
	<div class="sc-container">
		<div class="sc-section-header text-center" style="max-width: 720px; margin: 0 auto var(--sc-space-10, 40px);">
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

		<?php if ( $image_id || $image_sub_id ) : ?>
			<div class="sc-mfg-facility__media-wrap" style="margin-bottom: var(--sc-space-8, 32px);">
				<div class="sc-mfg-facility__main-media">
					<?php echo wp_get_attachment_image( $image_id, 'large', false, array( 'class' => 'sc-img-fluid sc-rounded', 'loading' => 'lazy' ) ); ?>
				</div>
				<?php if ( $image_sub_id ) : ?>
					<div class="sc-mfg-facility__sub-media">
						<?php echo wp_get_attachment_image( $image_sub_id, 'medium_large', false, array( 'class' => 'sc-img-fluid sc-rounded', 'loading' => 'lazy' ) ); ?>
					</div>
				<?php endif; ?>
			</div>
		<?php endif; ?>

		<?php if ( ! empty( $highlights ) && is_array( $highlights ) ) : ?>
			<div class="sc-mfg-facility__highlights-grid">
				<?php foreach ( $highlights as $hl ) :
					$hl_title = $hl['title'] ?? '';
					$hl_desc  = $hl['description'] ?? '';
					if ( empty( $hl_title ) && empty( $hl_desc ) ) continue;
					?>
					<div class="sc-mfg-facility__highlight-card sc-card">
						<?php if ( ! empty( $hl_title ) ) : ?>
							<h3 class="sc-mfg-facility__highlight-title"><?php echo esc_html( $hl_title ); ?></h3>
						<?php endif; ?>
						<?php if ( ! empty( $hl_desc ) ) : ?>
							<p class="sc-mfg-facility__highlight-desc"><?php echo esc_html( $hl_desc ); ?></p>
						<?php endif; ?>
					</div>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>
	</div>
</section>
