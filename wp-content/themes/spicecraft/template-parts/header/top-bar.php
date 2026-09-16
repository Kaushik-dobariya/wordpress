<?php
/**
 * Template part for displaying the top announcement and contact bar.
 *
 * Renders outside of the sticky header to allow it to scroll naturally out of view
 * while preserving vertical viewport real estate on desktop and mobile.
 *
 * @package SpiceCraft
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$phone        = function_exists( 'spicecraft_get_setting' ) ? spicecraft_get_setting( 'phone_primary', '' ) : spicecraft_get_theme_option( 'spicecraft_phone_number', '' );
$clean_phone  = ! empty( $phone ) ? spicecraft_clean_phone_number( $phone ) : '';
$export_email = function_exists( 'spicecraft_get_setting' ) ? spicecraft_get_setting( 'email_export', spicecraft_get_setting( 'email_sales', '' ) ) : spicecraft_get_theme_option( 'spicecraft_export_email', '' );
$whatsapp_num = function_exists( 'spicecraft_get_setting' ) ? spicecraft_get_setting( 'whatsapp_number', '' ) : spicecraft_get_theme_option( 'spicecraft_whatsapp_number', '' );
$whatsapp_url = ! empty( $whatsapp_num ) ? spicecraft_get_whatsapp_enquiry_url() : '';

if ( ! empty( $phone ) || ! empty( $export_email ) || ! empty( $whatsapp_url ) ) :
?>
<!-- Top Announcement & Contact Bar -->
<div class="sc-topbar">
	<div class="sc-container sc-topbar__inner">
		<div class="sc-topbar__contact">
			<?php if ( ! empty( $phone ) ) : ?>
				<a href="tel:<?php echo esc_attr( $clean_phone ); ?>" class="sc-topbar__link">
					<svg class="sc-icon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
						<path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/>
					</svg>
					<span><?php echo esc_html( $phone ); ?></span>
				</a>
			<?php endif; ?>

			<?php if ( ! empty( $export_email ) ) : ?>
				<a href="mailto:<?php echo esc_attr( sanitize_email( $export_email ) ); ?>" class="sc-topbar__link">
					<svg class="sc-icon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
						<path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
						<polyline points="22,6 12,13 2,6"/>
					</svg>
					<span><?php echo esc_html( $export_email ); ?></span>
				</a>
			<?php endif; ?>
		</div>

		<?php if ( ! empty( $whatsapp_url ) ) : ?>
			<div class="sc-topbar__actions">
				<a href="<?php echo esc_url( $whatsapp_url ); ?>" target="_blank" rel="noopener noreferrer" class="sc-topbar__link">
					<span style="color: var(--sc-color-whatsapp); font-weight: 700;">●</span>
					<span><?php esc_html_e( 'Quick WhatsApp Trade Chat', 'spicecraft' ); ?></span>
				</a>
			</div>
		<?php endif; ?>
	</div>
</div>
<?php endif; ?>
