<?php
/**
 * About Section: Company Statistics
 *
 * Optional numeric proof points with bold typography and high-contrast styling.
 * Completely omitted if no verified items are populated by admin.
 *
 * @package SpiceCraft
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$stat_sec = function_exists( 'spicecraft_get_about_section' )
	? spicecraft_get_about_section( 'statistics' )
	: array();

if ( empty( $stat_sec ) ) {
	return;
}

$items = function_exists( 'spicecraft_get_about_statistics' )
	? spicecraft_get_about_statistics()
	: array();

// Strict: hide entire section if no items
if ( empty( $items ) ) {
	return;
}

$eyebrow     = $stat_sec['eyebrow'] ?? '';
$heading     = $stat_sec['heading'] ?? '';
$description = $stat_sec['description'] ?? '';
?>

<section id="company-statistics" class="sc-about-stats" aria-label="<?php echo esc_attr( ! empty( $heading ) ? $heading : __( 'Company Statistics', 'spicecraft' ) ); ?>">
	<div class="sc-container">
		<?php if ( ! empty( $eyebrow ) || ! empty( $heading ) || ! empty( $description ) ) : ?>
			<div class="sc-section-header sc-section-header--center" style="margin-bottom: var(--sc-space-10);">
				<?php if ( ! empty( $eyebrow ) ) : ?>
					<span class="sc-section-eyebrow sc-section-eyebrow--light"><?php echo esc_html( $eyebrow ); ?></span>
				<?php endif; ?>

				<?php if ( ! empty( $heading ) ) : ?>
					<h2 class="sc-section-title" style="color: #ffffff;"><?php echo esc_html( $heading ); ?></h2>
				<?php endif; ?>

				<?php if ( ! empty( $description ) ) : ?>
					<p class="sc-section-subtitle" style="color: rgba(255, 255, 255, 0.8);"><?php echo esc_html( $description ); ?></p>
				<?php endif; ?>
			</div>
		<?php endif; ?>

		<div class="sc-about-stats__grid">
			<?php foreach ( $items as $st ) : ?>
				<div class="sc-about-stats__card">
					<div class="sc-about-stats__value-wrap">
						<span class="sc-about-stats__number"><?php echo esc_html( $st['value'] ); ?></span>
						<?php if ( ! empty( $st['suffix'] ) ) : ?>
							<span class="sc-about-stats__suffix"><?php echo esc_html( $st['suffix'] ); ?></span>
						<?php endif; ?>
					</div>
					<h3 class="sc-about-stats__label"><?php echo esc_html( $st['label'] ); ?></h3>
					<?php if ( ! empty( $st['description'] ) ) : ?>
						<p class="sc-about-stats__subtext"><?php echo esc_html( $st['description'] ); ?></p>
					<?php endif; ?>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
</section>
