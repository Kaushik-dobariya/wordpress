<?php
/**
 * About Section: Final Contact CTA
 *
 * Direct communication channels reusing centralized Global Settings contact channels
 * (WhatsApp, general trade email, and contact URL).
 *
 * @package SpiceCraft
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$fcta = function_exists( 'spicecraft_get_about_section' )
	? spicecraft_get_about_section( 'final_cta' )
	: array();

if ( empty( $fcta ) ) {
	return;
}

$heading       = $fcta['heading'] ?? '';
$description   = $fcta['description'] ?? '';
$primary_label = $fcta['primary_cta_label'] ?? '';
$primary_url   = $fcta['primary_cta_url'] ?? '';
$enable_wa     = ! empty( $fcta['enable_whatsapp'] );
$enable_email  = ! empty( $fcta['enable_email'] );

// Global settings reuse
$global_wa    = function_exists( 'spicecraft_get_setting' ) ? spicecraft_get_setting( 'whatsapp_number', '' ) : '';
$global_email = function_exists( 'spicecraft_get_setting' ) ? spicecraft_get_setting( 'email_general', '' ) : '';

if ( empty( $heading ) && empty( $description ) ) {
	return;
}
?>

<section id="final-contact" class="sc-about-final-cta" aria-label="<?php echo esc_attr( ! empty( $heading ) ? $heading : __( 'Contact SpiceCraft', 'spicecraft' ) ); ?>">
	<div class="sc-container sc-container--narrow" style="text-align: center;">
		<?php if ( ! empty( $heading ) ) : ?>
			<h2 class="sc-about-final-cta__heading"><?php echo esc_html( $heading ); ?></h2>
		<?php endif; ?>

		<?php if ( ! empty( $description ) ) : ?>
			<p class="sc-about-final-cta__desc"><?php echo nl2br( esc_html( $description ) ); ?></p>
		<?php endif; ?>

		<div class="sc-about-final-cta__actions">
			<?php if ( ! empty( $primary_label ) && ! empty( $primary_url ) ) : ?>
				<a href="<?php echo esc_url( $primary_url ); ?>" class="sc-btn sc-btn--primary sc-btn--lg">
					<?php echo esc_html( $primary_label ); ?>
				</a>
			<?php endif; ?>

			<?php if ( $enable_wa && ! empty( $global_wa ) ) :
				$wa_clean = preg_replace( '/[^0-9]/', '', $global_wa );
				$wa_link  = 'https://wa.me/' . $wa_clean . '?text=' . rawurlencode( __( 'Hello SpiceCraft, I would like to connect with your team regarding your spice products.', 'spicecraft' ) );
				?>
				<a href="<?php echo esc_url( $wa_link ); ?>" target="_blank" rel="noopener noreferrer" class="sc-btn sc-btn--whatsapp sc-btn--lg">
					<span class="dashicons dashicons-whatsapp" style="margin-top: -2px;"></span>
					<?php esc_html_e( 'Message on WhatsApp', 'spicecraft' ); ?>
				</a>
			<?php endif; ?>

			<?php if ( $enable_email && ! empty( $global_email ) ) : ?>
				<a href="mailto:<?php echo esc_attr( $global_email ); ?>" class="sc-btn sc-btn--secondary sc-btn--lg">
					<span class="dashicons dashicons-email" style="margin-top: -2px;"></span>
					<?php echo esc_html( $global_email ); ?>
				</a>
			<?php endif; ?>
		</div>
	</div>
</section>
