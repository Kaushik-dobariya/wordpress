<?php
/**
 * About Section: B2B / Export CTA
 *
 * Dedicated wholesale, institutional procurement, and export enquiry banner.
 * Reuses global WhatsApp number from SpiceCraft Global Settings.
 *
 * @package SpiceCraft
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$b2b = function_exists( 'spicecraft_get_about_section' )
	? spicecraft_get_about_section( 'b2b_cta' )
	: array();

if ( empty( $b2b ) ) {
	return;
}

$eyebrow        = $b2b['eyebrow'] ?? '';
$heading        = $b2b['heading'] ?? '';
$description    = $b2b['description'] ?? '';
$bg_img_id      = absint( $b2b['bg_image_id'] ?? 0 );
$primary_label  = $b2b['primary_cta_label'] ?? '';
$primary_url    = $b2b['primary_cta_url'] ?? '';
$secondary_label= $b2b['secondary_cta_label'] ?? '';
$secondary_url  = $b2b['secondary_cta_url'] ?? '';
$enable_wa      = ! empty( $b2b['enable_whatsapp'] );

// Global WhatsApp number
$global_wa = function_exists( 'spicecraft_get_setting' )
	? spicecraft_get_setting( 'whatsapp_number', '' )
	: '';

$bg_url = $bg_img_id ? wp_get_attachment_image_url( $bg_img_id, 'full' ) : '';

if ( empty( $heading ) && empty( $description ) ) {
	return;
}
?>

<section id="b2b-export" class="sc-about-b2b <?php echo ! empty( $bg_url ) ? 'sc-about-b2b--has-bg' : ''; ?>" aria-label="<?php echo esc_attr( ! empty( $heading ) ? $heading : __( 'B2B & Export Trade', 'spicecraft' ) ); ?>" style="<?php echo ! empty( $bg_url ) ? 'background-image: url(' . esc_url( $bg_url ) . ');' : ''; ?>">
	<div class="sc-about-b2b__overlay"></div>
	<div class="sc-container sc-container--narrow" style="position: relative; z-index: 2; text-align: center;">
		<?php if ( ! empty( $eyebrow ) ) : ?>
			<span class="sc-section-eyebrow sc-section-eyebrow--light"><?php echo esc_html( $eyebrow ); ?></span>
		<?php endif; ?>

		<?php if ( ! empty( $heading ) ) : ?>
			<h2 class="sc-about-b2b__heading"><?php echo esc_html( $heading ); ?></h2>
		<?php endif; ?>

		<?php if ( ! empty( $description ) ) : ?>
			<p class="sc-about-b2b__desc"><?php echo nl2br( esc_html( $description ) ); ?></p>
		<?php endif; ?>

		<div class="sc-about-b2b__actions">
			<?php if ( ! empty( $primary_label ) && ! empty( $primary_url ) ) : ?>
				<a href="<?php echo esc_url( $primary_url ); ?>" class="sc-btn sc-btn--primary sc-btn--lg">
					<?php echo esc_html( $primary_label ); ?>
				</a>
			<?php endif; ?>

			<?php if ( ! empty( $secondary_label ) && ! empty( $secondary_url ) ) : ?>
				<a href="<?php echo esc_url( $secondary_url ); ?>" class="sc-btn sc-btn--outline-white sc-btn--lg">
					<?php echo esc_html( $secondary_label ); ?>
				</a>
			<?php endif; ?>

			<?php if ( $enable_wa && ! empty( $global_wa ) ) :
				$wa_clean = preg_replace( '/[^0-9]/', '', $global_wa );
				$wa_link  = 'https://wa.me/' . $wa_clean . '?text=' . rawurlencode( __( 'Hello SpiceCraft Trade Desk, I would like to inquire about B2B procurement / export.', 'spicecraft' ) );
				?>
				<a href="<?php echo esc_url( $wa_link ); ?>" target="_blank" rel="noopener noreferrer" class="sc-btn sc-btn--whatsapp sc-btn--lg">
					<span class="dashicons dashicons-whatsapp" style="margin-top: -2px;"></span>
					<?php esc_html_e( 'WhatsApp Trade Desk', 'spicecraft' ); ?>
				</a>
			<?php endif; ?>
		</div>
	</div>
</section>
