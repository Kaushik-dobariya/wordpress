<?php
/**
 * Template part: Careers Final CTA Section
 *
 * Bottom closing call-to-action encouraging applicants to explore positions or contact HR.
 *
 * @package SpiceCraft
 * @since 1.3.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$settings    = function_exists( 'spicecraft_get_careers_settings' ) ? spicecraft_get_careers_settings() : array();
$cta_text    = ! empty( $settings['careers_cta_text'] ) ? $settings['careers_cta_text'] : __( 'Explore Open Positions', 'spicecraft' );
$hr_btn_text = ! empty( $settings['contact_hr_cta_text'] ) ? $settings['contact_hr_cta_text'] : __( 'Contact Talent Desk', 'spicecraft' );
$hr_email    = ! empty( $settings['contact_hr_email'] ) ? $settings['contact_hr_email'] : spicecraft_get_careers_profile_email();
$contact_url = 'mailto:' . esc_attr( $hr_email ) . '?subject=' . esc_attr( rawurlencode( 'Careers Inquiry — SpiceCraft Talent Desk' ) );
?>

<section class="sc-careers-final-cta" aria-labelledby="careers-final-cta-heading">
	<div class="sc-container">
		<div class="sc-careers-final-cta__inner">
			<div class="sc-careers-final-cta__content">
				<span class="sc-badge sc-badge--secondary"><?php esc_html_e( 'Begin Your Journey', 'spicecraft' ); ?></span>
				<h2 id="careers-final-cta-heading" class="sc-careers-final-cta__title">
					<?php esc_html_e( 'Ready to Craft the Future of Botanical Purity?', 'spicecraft' ); ?>
				</h2>
				<p class="sc-careers-final-cta__subtitle">
					<?php esc_html_e( 'Whether in cryogenic milling research, procurement quality validation, or global supply chain management, your work at SpiceCraft directly touches gourmet kitchens and food manufacturers worldwide.', 'spicecraft' ); ?>
				</p>
				<div class="sc-careers-final-cta__actions">
					<a href="#open-positions" class="sc-btn sc-btn--primary sc-btn--lg">
						<?php echo esc_html( $cta_text ); ?> &darr;
					</a>
					<a href="<?php echo esc_url( $contact_url ); ?>" class="sc-btn sc-btn--outline sc-btn--lg">
						<?php echo esc_html( $hr_btn_text ); ?>
					</a>
				</div>
			</div>
		</div>
	</div>
</section>
