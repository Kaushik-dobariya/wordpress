<?php
/**
 * SpiceCraft Contact Page - Final B2B Conversion CTA Banner
 *
 * @package SpiceCraft
 * @since 1.5.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$contact_settings = function_exists( 'spicecraft_get_contact_settings' )
	? spicecraft_get_contact_settings()
	: array();

$heading   = ! empty( $contact_settings['cta_heading'] ) ? $contact_settings['cta_heading'] : __( 'Looking for Custom Sourcing or Institutional Supply?', 'spicecraft' );
$subtitle  = ! empty( $contact_settings['cta_subtitle'] ) ? $contact_settings['cta_subtitle'] : __( 'Our spice technologists and export desk can formulate custom blends, match sieve mesh specs, and provide full laboratory COA documentation.', 'spicecraft' );
$btn_text  = ! empty( $contact_settings['cta_button_text'] ) ? $contact_settings['cta_button_text'] : __( 'Download Product Spec Sheet', 'spicecraft' );
$btn_url   = ! empty( $contact_settings['cta_button_url'] ) ? $contact_settings['cta_button_url'] : home_url( '/shop/' );
?>

<section class="sc-contact-final-cta" id="contact-final-cta" aria-labelledby="contact-final-cta-heading">
	<div class="sc-container">
		<div class="sc-contact-final-cta__box">
			<span class="sc-eyebrow sc-eyebrow--gold"><?php esc_html_e( 'B2B Trade Excellence', 'spicecraft' ); ?></span>
			<h2 id="contact-final-cta-heading" class="sc-contact-final-cta__heading">
				<?php echo esc_html( $heading ); ?>
			</h2>
			<?php if ( ! empty( $subtitle ) ) : ?>
				<p class="sc-contact-final-cta__subtitle">
					<?php echo esc_html( $subtitle ); ?>
				</p>
			<?php endif; ?>

			<div class="sc-contact-final-cta__actions">
				<a href="<?php echo esc_url( $btn_url ); ?>" class="sc-btn sc-btn--accent sc-btn--lg">
					<?php echo esc_html( $btn_text ); ?> &rarr;
				</a>
				<a href="#contact-form-section" class="sc-btn sc-btn--outline sc-btn--white sc-btn--lg">
					<?php esc_html_e( 'Request Custom Formulation', 'spicecraft' ); ?>
				</a>
			</div>
		</div>
	</div>
</section>
