<?php
/**
 * SpiceCraft Reusable Component: Contact Information Strip
 *
 * Parameters:
 * - heading (string)
 * - eyebrow (string)
 * - description (string)
 * - show_phone (bool, default: true)
 * - show_email (bool, default: true)
 * - show_address (bool, default: true)
 * - show_hours (bool, default: true)
 * - theme ('light'|'dark' - default 'light')
 *
 * @package SpiceCraft
 * @since 1.5.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$heading     = ! empty( $args['heading'] ) ? $args['heading'] : '';
$eyebrow     = ! empty( $args['eyebrow'] ) ? $args['eyebrow'] : '';
$description = ! empty( $args['description'] ) ? $args['description'] : '';
$theme       = ! empty( $args['theme'] ) && 'dark' === $args['theme'] ? 'dark' : 'light';

$show_phone   = ! isset( $args['show_phone'] ) || ! empty( $args['show_phone'] );
$show_email   = ! isset( $args['show_email'] ) || ! empty( $args['show_email'] );
$show_address = ! isset( $args['show_address'] ) || ! empty( $args['show_address'] );
$show_hours   = ! isset( $args['show_hours'] ) || ! empty( $args['show_hours'] );

$company = function_exists( 'spicecraft_get_company_info' ) ? spicecraft_get_company_info() : array();
$contact = function_exists( 'spicecraft_get_contact_settings' ) ? spicecraft_get_contact_settings() : array();

$phone   = ! empty( $contact['contact_phone'] ) ? $contact['contact_phone'] : ( $company['phone'] ?? '' );
$email   = ! empty( $contact['contact_email'] ) ? $contact['contact_email'] : ( $company['email'] ?? '' );
$address = ! empty( $contact['contact_address'] ) ? $contact['contact_address'] : ( $company['address'] ?? '' );
$hours   = ! empty( $contact['business_hours'] ) ? $contact['business_hours'] : ( $company['business_hours'] ?? '' );
?>
<section class="sc-comp-contact-strip sc-comp-contact-strip--<?php echo esc_attr( $theme ); ?>">
	<div class="sc-container">
		<?php if ( $heading || $eyebrow || $description ) : ?>
			<header class="sc-section-header sc-section-header--center">
				<?php if ( $eyebrow ) : ?>
					<p class="sc-eyebrow"><?php echo esc_html( $eyebrow ); ?></p>
				<?php endif; ?>
				<?php if ( $heading ) : ?>
					<h2 class="sc-section-title"><?php echo esc_html( $heading ); ?></h2>
				<?php endif; ?>
				<?php if ( $description ) : ?>
					<p class="sc-section-subtitle"><?php echo esc_html( $description ); ?></p>
				<?php endif; ?>
			</header>
		<?php endif; ?>

		<div class="sc-contact-strip__grid">
			<?php if ( $show_address && $address ) : ?>
				<div class="sc-contact-strip__card">
					<div class="sc-contact-strip__icon" aria-hidden="true">
						<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg>
					</div>
					<h3 class="sc-contact-strip__label"><?php esc_html_e( 'Plant & Corporate HQ', 'spicecraft' ); ?></h3>
					<p class="sc-contact-strip__val"><?php echo nl2br( esc_html( $address ) ); ?></p>
				</div>
			<?php endif; ?>

			<?php if ( $show_phone && $phone ) : ?>
				<div class="sc-contact-strip__card">
					<div class="sc-contact-strip__icon" aria-hidden="true">
						<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path></svg>
					</div>
					<h3 class="sc-contact-strip__label"><?php esc_html_e( 'Direct Telephony', 'spicecraft' ); ?></h3>
					<p class="sc-contact-strip__val">
						<a href="tel:<?php echo esc_attr( preg_replace( '/[^\d+]/', '', $phone ) ); ?>"><?php echo esc_html( $phone ); ?></a>
					</p>
				</div>
			<?php endif; ?>

			<?php if ( $show_email && $email ) : ?>
				<div class="sc-contact-strip__card">
					<div class="sc-contact-strip__icon" aria-hidden="true">
						<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path><polyline points="22,6 12,13 2,6"></polyline></svg>
					</div>
					<h3 class="sc-contact-strip__label"><?php esc_html_e( 'Direct Email', 'spicecraft' ); ?></h3>
					<p class="sc-contact-strip__val">
						<a href="mailto:<?php echo esc_attr( $email ); ?>"><?php echo esc_html( $email ); ?></a>
					</p>
				</div>
			<?php endif; ?>

			<?php if ( $show_hours && $hours ) : ?>
				<div class="sc-contact-strip__card">
					<div class="sc-contact-strip__icon" aria-hidden="true">
						<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
					</div>
					<h3 class="sc-contact-strip__label"><?php esc_html_e( 'Plant Operational Hours', 'spicecraft' ); ?></h3>
					<p class="sc-contact-strip__val"><?php echo nl2br( esc_html( $hours ) ); ?></p>
				</div>
			<?php endif; ?>
		</div>
	</div>
</section>
