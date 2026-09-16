<?php
/**
 * Homepage Template Part: B2B, Institutional & Export Callout
 * Semantic ID: #business-enquiry
 *
 * Dedicated wholesale and institutional buyer banner.
 * Consumes global contact endpoints without duplicate data.
 *
 * @package SpiceCraft
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$b2b = function_exists( 'spicecraft_get_homepage_section' )
	? spicecraft_get_homepage_section( 'b2b_cta' )
	: array();

$eyebrow         = ! empty( $b2b['eyebrow'] ) ? $b2b['eyebrow'] : '';
$heading         = ! empty( $b2b['heading'] ) ? $b2b['heading'] : '';
$description     = ! empty( $b2b['description'] ) ? $b2b['description'] : '';
$bg_img_id       = ! empty( $b2b['bg_image_id'] ) ? absint( $b2b['bg_image_id'] ) : 0;
$primary_label   = ! empty( $b2b['primary_cta_label'] ) ? $b2b['primary_cta_label'] : '';
$primary_url     = ! empty( $b2b['primary_cta_url'] ) ? $b2b['primary_cta_url'] : home_url( '/#contact' );
$secondary_label = ! empty( $b2b['secondary_cta_label'] ) ? $b2b['secondary_cta_label'] : '';
$secondary_url   = ! empty( $b2b['secondary_cta_url'] ) ? $b2b['secondary_cta_url'] : '';
$enable_wa       = ! empty( $b2b['enable_whatsapp'] );

if ( empty( $heading ) && empty( $description ) ) {
	return;
}

$bg_style = '';
if ( $bg_img_id && function_exists( 'spicecraft_get_media_image_url' ) ) {
	$bg_url = spicecraft_get_media_image_url( $bg_img_id, 'full' );
	if ( $bg_url ) {
		$bg_style = 'background-image: linear-gradient(rgba(18, 40, 32, 0.90), rgba(18, 40, 32, 0.95)), url(' . esc_url( $bg_url ) . ');';
	}
}

$whatsapp_url = ( $enable_wa && function_exists( 'spicecraft_get_whatsapp_enquiry_url' ) )
	? spicecraft_get_whatsapp_enquiry_url()
	: '';
?>

<section id="business-enquiry" class="sc-home-section sc-home-b2b" style="<?php echo esc_attr( $bg_style ); ?>" aria-labelledby="sec-heading-b2b">
	<div class="sc-container">
		<div class="sc-b2b-card">
			<?php if ( ! empty( $eyebrow ) ) : ?>
				<p class="sc-eyebrow sc-eyebrow--accent"><?php echo esc_html( $eyebrow ); ?></p>
			<?php endif; ?>

			<h2 id="sec-heading-b2b" class="sc-section-title sc-title--white"><?php echo esc_html( $heading ); ?></h2>

			<?php if ( ! empty( $description ) ) : ?>
				<p class="sc-section-subtitle sc-subtitle--light"><?php echo esc_html( $description ); ?></p>
			<?php endif; ?>

			<div class="sc-b2b-actions">
				<?php if ( ! empty( $primary_label ) ) : ?>
					<a href="<?php echo esc_url( $primary_url ); ?>" class="sc-btn sc-btn--primary sc-btn--lg">
						<span><?php echo esc_html( $primary_label ); ?></span>
						<svg class="sc-icon sc-icon-arrow" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
					</a>
				<?php endif; ?>

				<?php if ( ! empty( $secondary_label ) && ! empty( $secondary_url ) ) : ?>
					<a href="<?php echo esc_url( $secondary_url ); ?>" class="sc-btn sc-btn--outline-white sc-btn--lg">
						<span><?php echo esc_html( $secondary_label ); ?></span>
					</a>
				<?php endif; ?>

				<?php if ( ! empty( $whatsapp_url ) ) : ?>
					<a href="<?php echo esc_url( $whatsapp_url ); ?>" target="_blank" rel="noopener noreferrer" class="sc-btn sc-btn--whatsapp sc-btn--lg">
						<svg class="sc-icon" width="20" height="20" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
							<path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/>
						</svg>
						<span><?php esc_html_e( 'Direct WhatsApp Trade Desk', 'spicecraft' ); ?></span>
					</a>
				<?php endif; ?>
			</div>
		</div>
	</div>
</section>
