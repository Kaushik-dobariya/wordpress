<?php
/**
 * SpiceCraft Reusable Component: CTA Banner
 *
 * Parameters:
 * - heading (string)
 * - eyebrow (string)
 * - description (string)
 * - bg_image (string URL)
 * - cta_text (string)
 * - cta_url (string)
 * - cta_secondary_text (string)
 * - cta_secondary_url (string)
 * - enable_whatsapp (bool)
 * - theme ('dark'|'brand'|'light' - default 'dark')
 *
 * @package SpiceCraft
 * @since 1.5.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$heading      = ! empty( $args['heading'] ) ? $args['heading'] : '';
$eyebrow      = ! empty( $args['eyebrow'] ) ? $args['eyebrow'] : '';
$description  = ! empty( $args['description'] ) ? $args['description'] : '';
$bg_image     = ! empty( $args['bg_image'] ) ? $args['bg_image'] : '';
$cta_text     = ! empty( $args['cta_text'] ) ? $args['cta_text'] : __( 'Contact Our Wholesale Team', 'spicecraft' );
$cta_url      = ! empty( $args['cta_url'] ) ? $args['cta_url'] : home_url( '/contact/' );
$cta_sec_text = ! empty( $args['cta_secondary_text'] ) ? $args['cta_secondary_text'] : '';
$cta_sec_url  = ! empty( $args['cta_secondary_url'] ) ? $args['cta_secondary_url'] : '';
$enable_wa    = ! empty( $args['enable_whatsapp'] );
$theme        = ! empty( $args['theme'] ) && in_array( $args['theme'], array( 'brand', 'light' ), true ) ? $args['theme'] : 'dark';

if ( empty( $heading ) && empty( $description ) ) {
	return;
}

$bg_style = '';
if ( $bg_image ) {
	$overlay = 'light' === $theme
		? 'rgba(250, 247, 242, 0.94), rgba(250, 247, 242, 0.97)'
		: ( 'brand' === $theme
			? 'rgba(196, 75, 27, 0.92), rgba(196, 75, 27, 0.96)'
			: 'rgba(18, 40, 32, 0.92), rgba(18, 40, 32, 0.96)' );
	$bg_style = 'style="background-image: linear-gradient(' . $overlay . '), url(' . esc_url( $bg_image ) . '); background-size: cover; background-position: center;"';
}

$whatsapp_url = ( $enable_wa && function_exists( 'spicecraft_get_whatsapp_enquiry_url' ) )
	? spicecraft_get_whatsapp_enquiry_url()
	: '';
?>
<section class="sc-comp-cta-banner sc-comp-cta-banner--<?php echo esc_attr( $theme ); ?>" <?php echo $bg_style; // phpcs:ignore ?>>
	<div class="sc-container">
		<div class="sc-comp-cta-banner__card">
			<?php if ( $eyebrow ) : ?>
				<p class="sc-eyebrow sc-eyebrow--accent"><?php echo esc_html( $eyebrow ); ?></p>
			<?php endif; ?>

			<h2 class="sc-comp-cta-banner__title"><?php echo esc_html( $heading ); ?></h2>

			<?php if ( $description ) : ?>
				<p class="sc-comp-cta-banner__subtitle"><?php echo esc_html( $description ); ?></p>
			<?php endif; ?>

			<div class="sc-comp-cta-banner__actions">
				<?php if ( $cta_text && $cta_url ) : ?>
					<a href="<?php echo esc_url( $cta_url ); ?>" class="sc-btn sc-btn--primary sc-btn--large">
						<?php echo esc_html( $cta_text ); ?>
					</a>
				<?php endif; ?>

				<?php if ( $cta_sec_text && $cta_sec_url ) : ?>
					<a href="<?php echo esc_url( $cta_sec_url ); ?>" class="sc-btn sc-btn--outline sc-btn--large">
						<?php echo esc_html( $cta_sec_text ); ?>
					</a>
				<?php endif; ?>

				<?php if ( $whatsapp_url ) : ?>
					<a href="<?php echo esc_url( $whatsapp_url ); ?>" target="_blank" rel="noopener noreferrer" class="sc-btn sc-btn--wa sc-btn--large">
						<svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12.04 2c-5.46 0-9.91 4.45-9.91 9.91 0 1.75.46 3.45 1.32 4.95L2.05 22l5.25-1.38c1.45.79 3.08 1.21 4.74 1.21 5.46 0 9.91-4.45 9.91-9.91 0-2.65-1.03-5.14-2.9-7.01A9.816 9.816 0 0 0 12.04 2z"/></svg>
						<span><?php esc_html_e( 'WhatsApp Enquiry', 'spicecraft' ); ?></span>
					</a>
				<?php endif; ?>
			</div>
		</div>
	</div>
</section>
