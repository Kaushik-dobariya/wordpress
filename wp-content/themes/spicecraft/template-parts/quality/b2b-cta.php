<?php
/**
 * Quality & Sourcing Section: Technical Quality / B2B Inquiry CTA
 *
 * Dedicated commercial inquiry block for COA requests, batch analysis,
 * sample testing, and technical documentation.
 *
 * @package SpiceCraft
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$b2b = function_exists( 'spicecraft_get_quality_section' )
	? spicecraft_get_quality_section( 'b2b_cta' )
	: array();

if ( empty( $b2b ) || ( empty( $b2b['heading'] ) && empty( $b2b['description'] ) ) ) {
	return;
}

$eyebrow         = $b2b['eyebrow'] ?? '';
$heading         = $b2b['heading'] ?? '';
$description     = $b2b['description'] ?? '';
$primary_label   = $b2b['primary_cta_label'] ?? '';
$primary_url     = $b2b['primary_cta_url'] ?? '';
$secondary_label = $b2b['secondary_cta_label'] ?? '';
$secondary_url   = $b2b['secondary_cta_url'] ?? '';
$show_whatsapp   = ! empty( $b2b['show_whatsapp'] );
$global_wa       = function_exists( 'spicecraft_get_setting' ) ? spicecraft_get_setting( 'whatsapp_number', '' ) : '';
?>

<section id="sc-quality-b2b-cta" class="sc-quality-b2b-cta sc-section" aria-label="<?php echo esc_attr( $heading ?: __( 'Quality Inquiry & Specification Sheets', 'spicecraft' ) ); ?>">
	<div class="sc-container">
		<div class="sc-quality-b2b-cta__box sc-card text-center" style="background: var(--sc-color-surface-dark, #1f2937); color: #fff; padding: var(--sc-space-12, 48px) var(--sc-space-6, 24px); border-radius: 8px;">
			<?php if ( ! empty( $eyebrow ) ) : ?>
				<span class="sc-eyebrow" style="color: var(--sc-color-secondary, #d97706);"><?php echo esc_html( $eyebrow ); ?></span>
			<?php endif; ?>

			<?php if ( ! empty( $heading ) ) : ?>
				<h2 class="sc-quality-b2b-cta__title" style="color: #fff; font-size: clamp(1.75rem, 3vw, 2.5rem); margin: 8px 0 16px;"><?php echo esc_html( $heading ); ?></h2>
			<?php endif; ?>

			<?php if ( ! empty( $description ) ) : ?>
				<p class="sc-quality-b2b-cta__desc" style="max-width: 680px; margin: 0 auto var(--sc-space-8, 32px); color: rgba(255,255,255,0.85); font-size: 1.1rem; line-height: 1.6;"><?php echo nl2br( esc_html( $description ) ); ?></p>
			<?php endif; ?>

			<div class="sc-quality-b2b-cta__actions" style="display: flex; gap: 16px; justify-content: center; flex-wrap: wrap;">
				<?php if ( ! empty( $primary_label ) && ! empty( $primary_url ) ) : ?>
					<a href="<?php echo esc_url( $primary_url ); ?>" class="sc-btn sc-btn--primary sc-btn--lg">
						<?php echo esc_html( $primary_label ); ?>
					</a>
				<?php endif; ?>

				<?php if ( ! empty( $secondary_label ) && ! empty( $secondary_url ) ) : ?>
					<a href="<?php echo esc_url( $secondary_url ); ?>" class="sc-btn sc-btn--outline sc-btn--lg" style="color: #fff; border-color: rgba(255,255,255,0.5);">
						<?php echo esc_html( $secondary_label ); ?>
					</a>
				<?php endif; ?>

				<?php if ( $show_whatsapp && ! empty( $global_wa ) ) :
					$wa_clean = preg_replace( '/[^0-9]/', '', $global_wa );
					$wa_link  = 'https://wa.me/' . $wa_clean . '?text=' . rawurlencode( __( 'Hello SpiceCraft, I would like to request technical specifications and COA records for your spice products.', 'spicecraft' ) );
					?>
					<a href="<?php echo esc_url( $wa_link ); ?>" target="_blank" rel="noopener noreferrer" class="sc-btn sc-btn--whatsapp sc-btn--lg">
						<span class="dashicons dashicons-format-chat" style="margin-top: -2px;"></span>
						<?php esc_html_e( 'WhatsApp Technical Team', 'spicecraft' ); ?>
					</a>
				<?php endif; ?>
			</div>
		</div>
	</div>
</section>
