<?php
/**
 * Quality & Sourcing Section: Quality Principles
 *
 * Core pillars guiding spice inspection, ethical origin sourcing, and analytical integrity.
 *
 * @package SpiceCraft
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$pr = function_exists( 'spicecraft_get_quality_section' )
	? spicecraft_get_quality_section( 'principles' )
	: array();

if ( empty( $pr ) || empty( $pr['items'] ) || ! is_array( $pr['items'] ) ) {
	return;
}

$eyebrow     = $pr['eyebrow'] ?? '';
$heading     = $pr['heading'] ?? '';
$description = $pr['description'] ?? '';
$items       = $pr['items'];
?>

<section id="sc-quality-principles" class="sc-quality-principles sc-section sc-section--alt" aria-label="<?php echo esc_attr( $heading ?: __( 'Quality Principles', 'spicecraft' ) ); ?>">
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

		<div class="sc-quality-principles__grid">
			<?php foreach ( $items as $it ) :
				$title = $it['title'] ?? '';
				$desc  = $it['description'] ?? '';
				if ( empty( $title ) && empty( $desc ) ) continue;
				?>
				<div class="sc-quality-principles__card sc-card">
					<div class="sc-quality-principles__card-icon">
						<span class="dashicons dashicons-yes-alt"></span>
					</div>
					<?php if ( ! empty( $title ) ) : ?>
						<h3 class="sc-quality-principles__card-title"><?php echo esc_html( $title ); ?></h3>
					<?php endif; ?>
					<?php if ( ! empty( $desc ) ) : ?>
						<p class="sc-quality-principles__card-desc"><?php echo esc_html( $desc ); ?></p>
					<?php endif; ?>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
</section>
