<?php
/**
 * Template part: Careers General Talent CTA Section
 *
 * "Don't See the Right Role?" section inviting general profile submissions.
 *
 * @package SpiceCraft
 * @since 1.3.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$settings    = function_exists( 'spicecraft_get_careers_settings' ) ? spicecraft_get_careers_settings() : array();
$title       = ! empty( $settings['general_application_title'] ) ? $settings['general_application_title'] : __( 'Don\'t See the Right Role?', 'spicecraft' );
$desc        = ! empty( $settings['general_application_desc'] ) ? $settings['general_application_desc'] : __( 'We are always eager to connect with passionate spice specialists, food technologists, and operations experts.', 'spicecraft' );
$btn_text    = ! empty( $settings['general_application_btn'] ) ? $settings['general_application_btn'] : __( 'Contact Talent Desk', 'spicecraft' );
$hr_email    = ! empty( $settings['contact_hr_email'] ) ? $settings['contact_hr_email'] : spicecraft_get_careers_profile_email();
$contact_url = 'mailto:' . esc_attr( $hr_email ) . '?subject=' . esc_attr( rawurlencode( 'General Profile Submission — Talent Pool' ) );
?>

<section class="sc-careers-general-cta" aria-labelledby="general-cta-heading">
	<div class="sc-container">
		<div class="sc-general-cta__card">
			<div class="sc-general-cta__content">
				<span class="sc-badge sc-badge--accent"><?php esc_html_e( 'Talent Network', 'spicecraft' ); ?></span>
				<h2 id="general-cta-heading" class="sc-general-cta__title"><?php echo esc_html( $title ); ?></h2>
				<p class="sc-general-cta__desc"><?php echo nl2br( esc_html( $desc ) ); ?></p>
				<div class="sc-general-cta__actions">
					<a href="<?php echo esc_url( $contact_url ); ?>" class="sc-btn sc-btn--primary sc-btn--lg">
						<svg class="sc-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
						<?php echo esc_html( $btn_text ); ?>
					</a>
					<span class="sc-general-cta__email-hint">
						<?php esc_html_e( 'Direct Desk:', 'spicecraft' ); ?> <strong><?php echo esc_html( $hr_email ); ?></strong>
					</span>
				</div>
			</div>
			<div class="sc-general-cta__badge-box" aria-hidden="true">
				<div class="sc-cta-crest">
					<span class="dashicons dashicons-id-alt"></span>
				</div>
			</div>
		</div>
	</div>
</section>
