<?php
/**
 * Manufacturing Section: Statistics
 *
 * Operational scale and verified metrics. Hidden when unconfigured.
 *
 * @package SpiceCraft
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$stats = function_exists( 'spicecraft_get_manufacturing_section' )
	? spicecraft_get_manufacturing_section( 'statistics' )
	: array();

if ( empty( $stats ) || empty( $stats['items'] ) || ! is_array( $stats['items'] ) ) {
	return;
}

$eyebrow     = $stats['eyebrow'] ?? '';
$heading     = $stats['heading'] ?? '';
$description = $stats['description'] ?? '';
$items       = $stats['items'];
?>

<section id="sc-mfg-stats" class="sc-mfg-stats sc-section" aria-label="<?php echo esc_attr( $heading ?: __( 'Manufacturing Metrics', 'spicecraft' ) ); ?>">
	<div class="sc-container">
		<?php if ( ! empty( $heading ) || ! empty( $eyebrow ) ) : ?>
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
		<?php endif; ?>

		<div class="sc-mfg-stats__grid">
			<?php foreach ( $items as $st ) :
				$val  = $st['value'] ?? '';
				$suf  = $st['suffix'] ?? '';
				$lbl  = $st['label'] ?? '';
				$desc = $st['description'] ?? '';
				if ( empty( $val ) && empty( $lbl ) ) continue;
				?>
				<div class="sc-mfg-stats__card sc-card text-center">
					<div class="sc-mfg-stats__figure">
						<span class="sc-mfg-stats__value"><?php echo esc_html( $val ); ?></span>
						<?php if ( ! empty( $suf ) ) : ?>
							<span class="sc-mfg-stats__suffix"><?php echo esc_html( $suf ); ?></span>
						<?php endif; ?>
					</div>
					<?php if ( ! empty( $lbl ) ) : ?>
						<h3 class="sc-mfg-stats__label"><?php echo esc_html( $lbl ); ?></h3>
					<?php endif; ?>
					<?php if ( ! empty( $desc ) ) : ?>
						<p class="sc-mfg-stats__desc"><?php echo esc_html( $desc ); ?></p>
					<?php endif; ?>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
</section>
