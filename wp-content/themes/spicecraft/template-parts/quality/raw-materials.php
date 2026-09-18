<?php
/**
 * Quality & Sourcing Section: Raw Material & Supplier Standards
 *
 * Configured criteria required from cultivators and raw spice lots.
 *
 * @package SpiceCraft
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$raw_m = function_exists( 'spicecraft_get_quality_section' )
	? spicecraft_get_quality_section( 'raw_materials' )
	: array();

if ( empty( $raw_m ) || empty( $raw_m['items'] ) || ! is_array( $raw_m['items'] ) ) {
	return;
}

$eyebrow     = $raw_m['eyebrow'] ?? '';
$heading     = $raw_m['heading'] ?? '';
$description = $raw_m['description'] ?? '';
$items       = $raw_m['items'];
?>

<section id="sc-quality-raw-materials" class="sc-quality-raw-materials sc-section" aria-label="<?php echo esc_attr( $heading ?: __( 'Raw Material Standards', 'spicecraft' ) ); ?>">
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

		<div class="sc-quality-raw-materials__grid">
			<?php foreach ( $items as $it ) :
				$title = $it['title'] ?? '';
				$desc  = $it['description'] ?? '';
				if ( empty( $title ) && empty( $desc ) ) continue;
				?>
				<div class="sc-quality-raw-materials__card sc-card">
					<div class="sc-quality-raw-materials__card-icon">
						<span class="dashicons dashicons-forms"></span>
					</div>
					<?php if ( ! empty( $title ) ) : ?>
						<h3 class="sc-quality-raw-materials__card-title"><?php echo esc_html( $title ); ?></h3>
					<?php endif; ?>
					<?php if ( ! empty( $desc ) ) : ?>
						<p class="sc-quality-raw-materials__card-desc"><?php echo esc_html( $desc ); ?></p>
					<?php endif; ?>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
</section>
