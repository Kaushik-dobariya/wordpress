<?php
/**
 * SpiceCraft Contact Page - Hero Section
 *
 * Displays breadcrumb navigation, editorial eyebrow, single accessible H1,
 * concise B2B introduction, and 24-hour response guarantee badge.
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

$eyebrow  = ! empty( $contact_settings['hero_eyebrow'] ) ? $contact_settings['hero_eyebrow'] : __( 'Direct Manufacturer Trade Desks', 'spicecraft' );
$title    = ! empty( $contact_settings['hero_title'] ) ? $contact_settings['hero_title'] : __( 'Contact SpiceCraft', 'spicecraft' );
$subtitle = ! empty( $contact_settings['hero_subtitle'] ) ? $contact_settings['hero_subtitle'] : __( 'Connect with our team for product enquiries, bulk requirements, export opportunities, private-label manufacturing and business partnerships.', 'spicecraft' );
$badge    = ! empty( $contact_settings['hero_badge'] ) ? $contact_settings['hero_badge'] : __( 'Response within 24 business hours', 'spicecraft' );
?>

<section class="sc-contact-hero" id="contact-hero" aria-labelledby="contact-hero-heading">
	<div class="sc-container">
		<!-- Accessible Breadcrumb -->
		<nav class="sc-breadcrumb" aria-label="<?php esc_attr_e( 'Breadcrumb', 'spicecraft' ); ?>">
			<ol class="sc-breadcrumb__list">
				<li class="sc-breadcrumb__item"><a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Home', 'spicecraft' ); ?></a></li>
				<li class="sc-breadcrumb__separator" aria-hidden="true">&rsaquo;</li>
				<li class="sc-breadcrumb__item sc-breadcrumb__item--active" aria-current="page"><?php esc_html_e( 'Contact Us', 'spicecraft' ); ?></li>
			</ol>
		</nav>

		<div class="sc-contact-hero__content">
			<?php if ( ! empty( $eyebrow ) ) : ?>
				<div class="sc-contact-hero__eyebrow-wrap">
					<span class="sc-contact-hero__eyebrow"><?php echo esc_html( $eyebrow ); ?></span>
					<?php if ( ! empty( $badge ) ) : ?>
						<span class="sc-contact-hero__badge">
							<span class="sc-contact-hero__badge-dot" aria-hidden="true"></span>
							<?php echo esc_html( $badge ); ?>
						</span>
					<?php endif; ?>
				</div>
			<?php endif; ?>

			<h1 id="contact-hero-heading" class="sc-contact-hero__title">
				<?php echo esc_html( $title ); ?>
			</h1>

			<?php if ( ! empty( $subtitle ) ) : ?>
				<p class="sc-contact-hero__subtitle">
					<?php echo esc_html( $subtitle ); ?>
				</p>
			<?php endif; ?>

			<div class="sc-contact-hero__quick-actions">
				<a href="#contact-form-section" class="sc-btn sc-btn--primary">
					<?php esc_html_e( 'Send Trade Enquiry', 'spicecraft' ); ?> &darr;
				</a>
				<?php
				$wa_num = function_exists( 'spicecraft_get_setting' ) ? spicecraft_get_setting( 'whatsapp_number', '' ) : '';
				if ( ! empty( $wa_num ) ) :
					$wa_url = function_exists( 'spicecraft_get_whatsapp_enquiry_url' ) ? spicecraft_get_whatsapp_enquiry_url() : 'https://wa.me/' . preg_replace( '/\D/', '', $wa_num );
					?>
					<a href="<?php echo esc_url( $wa_url ); ?>" class="sc-btn sc-btn--whatsapp" target="_blank" rel="noopener noreferrer">
						<svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/></svg>
						<?php esc_html_e( 'WhatsApp Business', 'spicecraft' ); ?>
					</a>
				<?php endif; ?>
			</div>
		</div>
	</div>
</section>
