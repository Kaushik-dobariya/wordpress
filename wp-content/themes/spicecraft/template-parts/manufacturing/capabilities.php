<?php
/**
 * Manufacturing Section: Capabilities
 *
 * Core processing capabilities configured by administrator.
 *
 * @package SpiceCraft
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$caps = function_exists( 'spicecraft_get_manufacturing_section' )
	? spicecraft_get_manufacturing_section( 'capabilities' )
	: array();

if ( empty( $caps ) || empty( $caps['items'] ) || ! is_array( $caps['items'] ) ) {
	return;
}

$eyebrow     = $caps['eyebrow'] ?? '';
$heading     = $caps['heading'] ?? '';
$description = $caps['description'] ?? '';
$items       = $caps['items'];
$cta_label   = $caps['cta_label'] ?? '';
$cta_url     = $caps['cta_url'] ?? '';
?>

<section id="sc-mfg-capabilities" class="sc-mfg-capabilities sc-section sc-section--alt" aria-label="<?php echo esc_attr( $heading ?: __( 'Processing Capabilities', 'spicecraft' ) ); ?>">
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

		<div class="sc-mfg-capabilities__grid">
			<?php foreach ( $items as $it ) :
				$title = $it['title'] ?? '';
				$desc  = $it['description'] ?? '';
				if ( empty( $title ) && empty( $desc ) ) continue;
				?>
				<div class="sc-mfg-capabilities__card sc-card">
					<div class="sc-mfg-capabilities__card-icon">
						<span class="dashicons dashicons-admin-generic"></span>
					</div>
					<?php if ( ! empty( $title ) ) : ?>
						<h3 class="sc-mfg-capabilities__card-title"><?php echo esc_html( $title ); ?></h3>
					<?php endif; ?>
					<?php if ( ! empty( $desc ) ) : ?>
						<p class="sc-mfg-capabilities__card-desc"><?php echo esc_html( $desc ); ?></p>
					<?php endif; ?>
				</div>
			<?php endforeach; ?>
		</div>

		<?php if ( ! empty( $cta_label ) && ! empty( $cta_url ) ) : ?>
			<div class="sc-mfg-capabilities__cta text-center" style="margin-top: var(--sc-space-10, 40px);">
				<a href="<?php echo esc_url( $cta_url ); ?>" class="sc-btn sc-btn--primary">
					<?php echo esc_html( $cta_label ); ?>
				</a>
			</div>
		<?php endif; ?>
	</div>
</section>
