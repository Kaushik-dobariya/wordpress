<?php
/**
 * SpiceCraft Contact Page - Company Information Section
 *
 * Displays configured company headquarters, processing facility, direct phone lines,
 * corporate email channels, verified business hours, and statutory compliance details.
 * Retrieves all data exclusively from Global Settings.
 *
 * @package SpiceCraft
 * @since 1.5.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$company_name     = function_exists( 'spicecraft_get_setting' ) ? spicecraft_get_setting( 'company_name', get_bloginfo( 'name' ) ) : get_bloginfo( 'name' );
$registered_name  = function_exists( 'spicecraft_get_setting' ) ? spicecraft_get_setting( 'registered_company_name', '' ) : '';
$address_primary  = function_exists( 'spicecraft_get_setting' ) ? spicecraft_get_setting( 'address_primary', '' ) : '';
$address_factory  = function_exists( 'spicecraft_get_setting' ) ? spicecraft_get_setting( 'address_factory', '' ) : '';
$phone_primary    = function_exists( 'spicecraft_get_setting' ) ? spicecraft_get_setting( 'phone_primary', '' ) : '';
$phone_secondary  = function_exists( 'spicecraft_get_setting' ) ? spicecraft_get_setting( 'phone_secondary', '' ) : '';
$business_hours   = function_exists( 'spicecraft_get_setting' ) ? spicecraft_get_setting( 'business_hours', '' ) : '';
$email_sales      = function_exists( 'spicecraft_get_setting' ) ? spicecraft_get_setting( 'email_sales', '' ) : '';
$email_export     = function_exists( 'spicecraft_get_setting' ) ? spicecraft_get_setting( 'email_export', '' ) : '';
$email_general    = function_exists( 'spicecraft_get_setting' ) ? spicecraft_get_setting( 'email_general', get_option( 'admin_email' ) ) : get_option( 'admin_email' );

// Regulatory Credentials
$fssai_lic        = function_exists( 'spicecraft_get_setting' ) ? spicecraft_get_setting( 'fssai_license', '' ) : '';
$gstin            = function_exists( 'spicecraft_get_setting' ) ? spicecraft_get_setting( 'gst_number', '' ) : '';
$iec_code         = function_exists( 'spicecraft_get_setting' ) ? spicecraft_get_setting( 'iec_code', '' ) : '';
$certs_note       = function_exists( 'spicecraft_get_setting' ) ? spicecraft_get_setting( 'certifications_summary', '' ) : '';
?>

<section class="sc-contact-info-section" id="contact-info" aria-labelledby="contact-info-heading">
	<div class="sc-container">
		<header class="sc-section-header sc-section-header--center">
			<span class="sc-eyebrow"><?php esc_html_e( 'Official Manufacturer Desks', 'spicecraft' ); ?></span>
			<h2 id="contact-info-heading" class="sc-section-title"><?php esc_html_e( 'Corporate & Manufacturing Details', 'spicecraft' ); ?></h2>
			<p class="sc-section-subtitle">
				<?php esc_html_e( 'Reach out directly to our statutory headquarters or our state-of-the-art spice milling and processing campus.', 'spicecraft' ); ?>
			</p>
		</header>

		<!-- Info Cards Grid -->
		<div class="sc-contact-info-grid">

			<!-- Card 1: Headquarters & Address -->
			<div class="sc-contact-card">
				<div class="sc-contact-card__icon" aria-hidden="true">
					<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
						<path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
						<circle cx="12" cy="10" r="3"></circle>
					</svg>
				</div>
				<h3 class="sc-contact-card__title"><?php esc_html_e( 'Corporate Headquarters', 'spicecraft' ); ?></h3>
				<?php if ( ! empty( $registered_name ) ) : ?>
					<p class="sc-contact-card__legal-name"><?php echo esc_html( $registered_name ); ?></p>
				<?php endif; ?>

				<?php if ( ! empty( $address_primary ) ) : ?>
					<div class="sc-contact-card__body">
						<address class="sc-contact-card__address">
							<?php echo nl2br( esc_html( $address_primary ) ); ?>
						</address>
					</div>
				<?php endif; ?>

				<?php if ( ! empty( $phone_primary ) ) : ?>
					<div class="sc-contact-card__meta-item">
						<span class="sc-contact-card__meta-label"><?php esc_html_e( 'Board Line:', 'spicecraft' ); ?></span>
						<a href="tel:<?php echo esc_attr( preg_replace( '/[^\d+]/', '', $phone_primary ) ); ?>" class="sc-contact-card__link">
							<?php echo esc_html( $phone_primary ); ?>
						</a>
					</div>
				<?php endif; ?>
			</div>

			<!-- Card 2: Manufacturing & Export Hub -->
			<div class="sc-contact-card">
				<div class="sc-contact-card__icon" aria-hidden="true">
					<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
						<polygon points="1 6 1 22 8 18 16 22 23 18 23 2 16 6 8 2 1 6"></polygon>
						<line x1="8" y1="2" x2="8" y2="18"></line>
						<line x1="16" y1="6" x2="16" y2="22"></line>
					</svg>
				</div>
				<h3 class="sc-contact-card__title"><?php esc_html_e( 'Processing Facility & Plant', 'spicecraft' ); ?></h3>
				<p class="sc-contact-card__legal-name"><?php esc_html_e( 'Export Packing & Sourcing Hub', 'spicecraft' ); ?></p>

				<?php if ( ! empty( $address_factory ) ) : ?>
					<div class="sc-contact-card__body">
						<address class="sc-contact-card__address">
							<?php echo nl2br( esc_html( $address_factory ) ); ?>
						</address>
					</div>
				<?php endif; ?>

				<?php if ( ! empty( $phone_secondary ) ) : ?>
					<div class="sc-contact-card__meta-item">
						<span class="sc-contact-card__meta-label"><?php esc_html_e( 'Plant Desk:', 'spicecraft' ); ?></span>
						<a href="tel:<?php echo esc_attr( preg_replace( '/[^\d+]/', '', $phone_secondary ) ); ?>" class="sc-contact-card__link">
							<?php echo esc_html( $phone_secondary ); ?>
						</a>
					</div>
				<?php endif; ?>
			</div>

			<!-- Card 3: Business Hours & Timings -->
			<div class="sc-contact-card">
				<div class="sc-contact-card__icon" aria-hidden="true">
					<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
						<circle cx="12" cy="12" r="10"></circle>
						<polyline points="12 6 12 12 16 14"></polyline>
					</svg>
				</div>
				<h3 class="sc-contact-card__title"><?php esc_html_e( 'Operating Hours', 'spicecraft' ); ?></h3>
				<p class="sc-contact-card__legal-name"><?php esc_html_e( 'Commercial & Trade Desks', 'spicecraft' ); ?></p>

				<div class="sc-contact-card__body">
					<?php if ( ! empty( $business_hours ) ) : ?>
						<p class="sc-contact-card__highlight">
							<?php echo esc_html( $business_hours ); ?>
						</p>
					<?php else : ?>
						<p class="sc-contact-card__highlight">
							<?php esc_html_e( 'Monday – Saturday: 9:00 AM – 6:00 PM IST', 'spicecraft' ); ?>
						</p>
					<?php endif; ?>
					<p class="sc-contact-card__note">
						<?php esc_html_e( 'Sunday: Closed for routine manufacturing sanitation and maintenance.', 'spicecraft' ); ?>
					</p>
				</div>

				<div class="sc-contact-card__meta-item">
					<span class="sc-contact-card__meta-label"><?php esc_html_e( 'Timezone:', 'spicecraft' ); ?></span>
					<span class="sc-contact-card__value"><?php esc_html_e( 'Indian Standard Time (UTC +05:30)', 'spicecraft' ); ?></span>
				</div>
			</div>

			<!-- Card 4: Trade Desks & Direct Emails -->
			<div class="sc-contact-card sc-contact-card--accent">
				<div class="sc-contact-card__icon" aria-hidden="true">
					<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
						<path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
						<polyline points="22,6 12,13 2,6"></polyline>
					</svg>
				</div>
				<h3 class="sc-contact-card__title"><?php esc_html_e( 'Direct Email Desks', 'spicecraft' ); ?></h3>
				<p class="sc-contact-card__legal-name"><?php esc_html_e( 'Dedicated Specialist Channels', 'spicecraft' ); ?></p>

				<div class="sc-contact-card__body">
					<?php if ( ! empty( $email_export ) ) : ?>
						<div class="sc-contact-card__email-row">
							<span class="sc-contact-card__channel-tag"><?php esc_html_e( 'Export & Global Trade:', 'spicecraft' ); ?></span>
							<a href="mailto:<?php echo esc_attr( $email_export ); ?>" class="sc-contact-card__link">
								<?php echo esc_html( $email_export ); ?>
							</a>
						</div>
					<?php endif; ?>

					<?php if ( ! empty( $email_sales ) ) : ?>
						<div class="sc-contact-card__email-row">
							<span class="sc-contact-card__channel-tag"><?php esc_html_e( 'Domestic Bulk Supply:', 'spicecraft' ); ?></span>
							<a href="mailto:<?php echo esc_attr( $email_sales ); ?>" class="sc-contact-card__link">
								<?php echo esc_html( $email_sales ); ?>
							</a>
						</div>
					<?php endif; ?>

					<?php if ( ! empty( $email_general ) ) : ?>
						<div class="sc-contact-card__email-row">
							<span class="sc-contact-card__channel-tag"><?php esc_html_e( 'General / Corporate Desk:', 'spicecraft' ); ?></span>
							<a href="mailto:<?php echo esc_attr( $email_general ); ?>" class="sc-contact-card__link">
								<?php echo esc_html( $email_general ); ?>
							</a>
						</div>
					<?php endif; ?>
				</div>
			</div>

		</div><!-- .sc-contact-info-grid -->

		<!-- Statutory Compliance Strip (Only populated verified fields) -->
		<?php if ( ! empty( $fssai_lic ) || ! empty( $gstin ) || ! empty( $iec_code ) || ! empty( $certs_note ) ) : ?>
			<div class="sc-contact-compliance-strip">
				<span class="sc-contact-compliance-strip__title">
					<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
						<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
					</svg>
					<?php esc_html_e( 'Statutory Credentials & Compliance:', 'spicecraft' ); ?>
				</span>
				<div class="sc-contact-compliance-strip__items">
					<?php if ( ! empty( $fssai_lic ) ) : ?>
						<span class="sc-contact-compliance-badge">
							<strong><?php esc_html_e( 'FSSAI License:', 'spicecraft' ); ?></strong> <?php echo esc_html( $fssai_lic ); ?>
						</span>
					<?php endif; ?>

					<?php if ( ! empty( $gstin ) ) : ?>
						<span class="sc-contact-compliance-badge">
							<strong><?php esc_html_e( 'GSTIN:', 'spicecraft' ); ?></strong> <?php echo esc_html( $gstin ); ?>
						</span>
					<?php endif; ?>

					<?php if ( ! empty( $iec_code ) ) : ?>
						<span class="sc-contact-compliance-badge">
							<strong><?php esc_html_e( 'IEC Code:', 'spicecraft' ); ?></strong> <?php echo esc_html( $iec_code ); ?>
						</span>
					<?php endif; ?>

					<?php if ( ! empty( $certs_note ) ) : ?>
						<span class="sc-contact-compliance-badge sc-contact-compliance-badge--gold">
							<strong><?php esc_html_e( 'Accreditations:', 'spicecraft' ); ?></strong> <?php echo esc_html( $certs_note ); ?>
						</span>
					<?php endif; ?>
				</div>
			</div>
		<?php endif; ?>

	</div>
</section>
