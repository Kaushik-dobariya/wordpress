<?php
/**
 * Certifications Section: Final Compliance & Commercial CTA
 *
 * Dedicated procurement inquiry section connecting regulatory assurance
 * to commercial B2B conversations. Reuses centralized Global Settings channels.
 *
 * @package SpiceCraft
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$settings = function_exists( 'spicecraft_get_certification_settings' ) ? spicecraft_get_certification_settings() : array();

if ( empty( $settings['final_cta_enable'] ) ) {
	return;
}

$heading     = ! empty( $settings['final_cta_heading'] ) ? $settings['final_cta_heading'] : __( 'Require Verified Audit Reports or Custom Dossiers?', 'spicecraft' );
$description = ! empty( $settings['final_cta_desc'] ) ? $settings['final_cta_desc'] : __( 'Our regulatory compliance team supplies full audit dossiers, Certificate of Analysis (CoA) batches, and third-party laboratory accreditations for commercial food manufacturers.', 'spicecraft' );

$show_wa    = ! empty( $settings['final_cta_whatsapp_enable'] );
$show_email = ! empty( $settings['final_cta_email_enable'] );

// Global settings destinations
$global_wa    = function_exists( 'spicecraft_get_setting' ) ? spicecraft_get_setting( 'whatsapp_number', '' ) : '';
$global_email = function_exists( 'spicecraft_get_setting' ) ? spicecraft_get_setting( 'email_general', '' ) : '';
$contact_url  = function_exists( 'spicecraft_get_setting' ) ? spicecraft_get_setting( 'contact_page_url', '' ) : home_url( '/contact/' );
if ( empty( $contact_url ) ) {
	$contact_url = home_url( '/contact/' );
}
?>

<section class="sc-cert-cta-section" aria-label="<?php echo esc_attr( $heading ); ?>">
	<div class="sc-container">
		<div class="sc-cert-cta-card">
			<div class="sc-cert-cta-content text-center">
				<span class="sc-cert-cta-icon dashicons dashicons-shield-alt" aria-hidden="true"></span>
				<h2 class="sc-cert-cta-title"><?php echo esc_html( $heading ); ?></h2>
				<p class="sc-cert-cta-desc"><?php echo nl2br( esc_html( $description ) ); ?></p>

				<div class="sc-cert-cta-actions">
					<a href="<?php echo esc_url( $contact_url ); ?>" class="sc-btn sc-btn--primary sc-btn--lg">
						<?php esc_html_e( 'Request Regulatory Dossier', 'spicecraft' ); ?>
					</a>

					<?php if ( $show_wa && ! empty( $global_wa ) ) :
						$wa_clean = preg_replace( '/[^0-9]/', '', $global_wa );
						$wa_link  = 'https://wa.me/' . $wa_clean . '?text=' . rawurlencode( __( 'Hello SpiceCraft, I would like to request regulatory certification dossiers for commercial spice procurement.', 'spicecraft' ) );
						?>
						<a href="<?php echo esc_url( $wa_link ); ?>" target="_blank" rel="noopener noreferrer" class="sc-btn sc-btn--whatsapp sc-btn--lg" aria-label="<?php esc_attr_e( 'Chat with compliance desk on WhatsApp (opens in new tab)', 'spicecraft' ); ?>">
							<span class="dashicons dashicons-format-chat" aria-hidden="true"></span>
							<?php esc_html_e( 'WhatsApp Compliance', 'spicecraft' ); ?>
						</a>
					<?php endif; ?>

					<?php if ( $show_email && ! empty( $global_email ) ) : ?>
						<a href="mailto:<?php echo esc_attr( $global_email ); ?>?subject=<?php echo rawurlencode( __( 'Inquiry: SpiceCraft Certification & Compliance Dossier', 'spicecraft' ) ); ?>" class="sc-btn sc-btn--secondary sc-btn--lg" aria-label="<?php esc_attr_e( 'Email compliance desk', 'spicecraft' ); ?>">
							<span class="dashicons dashicons-email-alt" aria-hidden="true"></span>
							<?php esc_html_e( 'Email Quality Desk', 'spicecraft' ); ?>
						</a>
					<?php endif; ?>
				</div>
			</div>
		</div>
	</div>
</section>
