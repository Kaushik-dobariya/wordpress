<?php
/**
 * Manufacturing Section: Final Contact CTA
 *
 * Direct communication channels reusing centralized Global Settings contact channels
 * (WhatsApp, general trade email, and contact URL).
 *
 * @package SpiceCraft
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$fcta = function_exists( 'spicecraft_get_manufacturing_section' )
	? spicecraft_get_manufacturing_section( 'final_cta' )
	: array();

if ( empty( $fcta ) || ( empty( $fcta['heading'] ) && empty( $fcta['description'] ) ) ) {
	return;
}

$heading      = $fcta['heading'] ?? '';
$description  = $fcta['description'] ?? '';
$cta_label    = $fcta['cta_label'] ?? '';
$cta_url      = $fcta['cta_url'] ?? '';
$show_wa      = ! empty( $fcta['show_whatsapp'] );
$show_email   = ! empty( $fcta['show_email'] );

// Global settings reuse
$global_wa    = function_exists( 'spicecraft_get_setting' ) ? spicecraft_get_setting( 'whatsapp_number', '' ) : '';
$global_email = function_exists( 'spicecraft_get_setting' ) ? spicecraft_get_setting( 'email_general', '' ) : '';
?>

<section id="sc-mfg-final-cta" class="sc-mfg-final-cta sc-section" aria-label="<?php echo esc_attr( $heading ?: __( 'Contact SpiceCraft Manufacturing', 'spicecraft' ) ); ?>">
	<div class="sc-container sc-container--narrow text-center" style="max-width: 680px; margin: 0 auto;">
		<?php if ( ! empty( $heading ) ) : ?>
			<h2 class="sc-section-title"><?php echo esc_html( $heading ); ?></h2>
		<?php endif; ?>

		<?php if ( ! empty( $description ) ) : ?>
			<p class="sc-section-desc"><?php echo nl2br( esc_html( $description ) ); ?></p>
		<?php endif; ?>

		<div class="sc-mfg-final-cta__actions" style="display: flex; gap: 14px; justify-content: center; flex-wrap: wrap; margin-top: var(--sc-space-8, 32px);">
			<?php if ( ! empty( $cta_label ) && ! empty( $cta_url ) ) : ?>
				<a href="<?php echo esc_url( $cta_url ); ?>" class="sc-btn sc-btn--primary sc-btn--lg">
					<?php echo esc_html( $cta_label ); ?>
				</a>
			<?php endif; ?>

			<?php if ( $show_wa && ! empty( $global_wa ) ) :
				$wa_clean = preg_replace( '/[^0-9]/', '', $global_wa );
				$wa_link  = 'https://wa.me/' . $wa_clean . '?text=' . rawurlencode( __( 'Hello SpiceCraft, I would like to connect regarding spice manufacturing.', 'spicecraft' ) );
				?>
				<a href="<?php echo esc_url( $wa_link ); ?>" target="_blank" rel="noopener noreferrer" class="sc-btn sc-btn--whatsapp sc-btn--lg">
					<span class="dashicons dashicons-format-chat" style="margin-top: -2px;"></span>
					<?php esc_html_e( 'WhatsApp', 'spicecraft' ); ?>
				</a>
			<?php endif; ?>

			<?php if ( $show_email && ! empty( $global_email ) ) : ?>
				<a href="<?php echo esc_url( 'mailto:' . antispambot( $global_email ) ); ?>" class="sc-btn sc-btn--secondary sc-btn--lg">
					<span class="dashicons dashicons-email-alt" style="margin-top: -2px;"></span>
					<?php esc_html_e( 'Email Us', 'spicecraft' ); ?>
				</a>
			<?php endif; ?>
		</div>
	</div>
</section>
