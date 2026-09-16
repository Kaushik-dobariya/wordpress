<?php
/**
 * Homepage Template Part: Final Conversion & Lead CTA
 * Semantic ID: #contact-cta
 *
 * Direct conversion brand band providing dual-channel WhatsApp and email contact desks.
 * Consumes global contact endpoints from Global Settings without data duplication.
 *
 * @package SpiceCraft
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$fcta = function_exists( 'spicecraft_get_homepage_section' )
	? spicecraft_get_homepage_section( 'final_cta' )
	: array();

$heading       = ! empty( $fcta['heading'] ) ? $fcta['heading'] : '';
$description   = ! empty( $fcta['description'] ) ? $fcta['description'] : '';
$primary_label = ! empty( $fcta['primary_cta_label'] ) ? $fcta['primary_cta_label'] : __( 'Contact Our Team', 'spicecraft' );
$primary_url   = ! empty( $fcta['primary_cta_url'] ) ? $fcta['primary_cta_url'] : home_url( '/#contact' );
$enable_wa     = ! empty( $fcta['enable_whatsapp'] );
$enable_email  = ! empty( $fcta['enable_email'] );

if ( empty( $heading ) && empty( $description ) ) {
	return;
}

$whatsapp_url = ( $enable_wa && function_exists( 'spicecraft_get_whatsapp_enquiry_url' ) )
	? spicecraft_get_whatsapp_enquiry_url()
	: '';

$general_email = ( $enable_email && function_exists( 'spicecraft_get_setting' ) )
	? spicecraft_get_setting( 'email_general', '' )
	: '';
?>

<section id="contact-cta" class="sc-home-section sc-home-final-cta sc-surface-brand" aria-labelledby="sec-heading-final-cta">
	<div class="sc-container">
		<div class="sc-final-cta-inner">
			<div class="sc-final-cta-content">
				<span class="sc-final-cta-eyebrow"><?php esc_html_e( 'Ready to Partner?', 'spicecraft' ); ?></span>
				<h2 id="sec-heading-final-cta" class="sc-final-cta-title"><?php echo esc_html( $heading ); ?></h2>
				<?php if ( ! empty( $description ) ) : ?>
					<p class="sc-final-cta-desc"><?php echo esc_html( $description ); ?></p>
				<?php endif; ?>
			</div>

			<div class="sc-final-cta-actions">
				<?php if ( ! empty( $primary_label ) ) : ?>
					<a href="<?php echo esc_url( $primary_url ); ?>" class="sc-btn sc-btn--light sc-btn--lg">
						<span><?php echo esc_html( $primary_label ); ?></span>
						<svg class="sc-icon sc-icon-arrow" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
					</a>
				<?php endif; ?>

				<?php if ( ! empty( $whatsapp_url ) ) : ?>
					<a href="<?php echo esc_url( $whatsapp_url ); ?>" target="_blank" rel="noopener noreferrer" class="sc-btn sc-btn--whatsapp sc-btn--lg">
						<svg class="sc-icon" width="20" height="20" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
							<path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/>
						</svg>
						<span><?php esc_html_e( 'WhatsApp Us', 'spicecraft' ); ?></span>
					</a>
				<?php endif; ?>

				<?php if ( ! empty( $general_email ) ) : ?>
					<a href="mailto:<?php echo esc_attr( $general_email ); ?>" class="sc-btn sc-btn--outline-white sc-btn--lg">
						<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path><polyline points="22,6 12,13 2,6"></polyline></svg>
						<span><?php echo esc_html( $general_email ); ?></span>
					</a>
				<?php endif; ?>
			</div>
		</div>
	</div>
</section>
