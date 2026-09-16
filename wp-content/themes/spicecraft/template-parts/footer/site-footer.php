<?php
/**
 * Template part for displaying the site footer content.
 *
 * @package SpiceCraft
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$corporate_address = spicecraft_get_theme_option( 'spicecraft_corporate_address', 'SpiceCraft Agro Foods Ltd., Heritage Spice Hub, Ahmedabad, Gujarat, India.' );
$factory_address   = spicecraft_get_theme_option( 'spicecraft_factory_address', 'GIDC Agro Park Phase II, Unjha - Sanand Industrial Corridor, Gujarat, India.' );
$fssai             = spicecraft_get_theme_option( 'spicecraft_fssai_license', 'FSSAI Lic. No.: 10012021000123' );
$certs             = spicecraft_get_theme_option( 'spicecraft_certifications_note', 'ISO 22000:2018 | HACCP | HALAL | US FDA Registered | Spices Board India Certified' );
$phone             = spicecraft_get_theme_option( 'spicecraft_phone_number', '+91 (0) 79 1234 5678' );
$clean_phone       = spicecraft_clean_phone_number( $phone );
$whatsapp          = spicecraft_get_theme_option( 'spicecraft_whatsapp_number', '+91 98765 43210' );
$export_email      = spicecraft_get_theme_option( 'spicecraft_export_email', 'exports@spicecraft.local' );
$custom_copyright  = spicecraft_get_theme_option( 'spicecraft_copyright_text', '' );
?>

<div class="sc-container">
	<div class="sc-footer-grid">
		<!-- Column 1: Brand & Plant Overview -->
		<div class="sc-footer-col">
			<h4><?php bloginfo( 'name' ); ?></h4>
			<p style="font-size: 0.9rem; line-height: 1.6; color: #d1d5db;">
				<?php bloginfo( 'description' ); ?>
			</p>
			<?php if ( ! empty( $fssai ) ) : ?>
				<p style="font-size: 0.8rem; color: var(--sc-color-accent-light); margin-bottom: 0.5rem;">
					<strong><?php echo esc_html( $fssai ); ?></strong>
				</p>
			<?php endif; ?>
			<?php if ( ! empty( $certs ) ) : ?>
				<p style="font-size: 0.75rem; color: #9ca3af; margin: 0;">
					<?php echo esc_html( $certs ); ?>
				</p>
			<?php endif; ?>
		</div>

		<!-- Column 2: Footer Menu 1 -->
		<div class="sc-footer-col">
			<h4><?php esc_html_e( 'Quick Links', 'spicecraft' ); ?></h4>
			<?php
			wp_nav_menu(
				array(
					'theme_location' => 'footer_1',
					'menu_class'     => 'sc-footer-menu',
					'container'      => false,
					'fallback_cb'    => function () {
						echo '<ul class="sc-footer-menu">';
						echo '<li><a href="' . esc_url( home_url( '/' ) ) . '">' . esc_html__( 'Home', 'spicecraft' ) . '</a></li>';
						echo '<li><a href="' . esc_url( home_url( '/#about' ) ) . '">' . esc_html__( 'About Us', 'spicecraft' ) . '</a></li>';
						echo '<li><a href="' . esc_url( home_url( '/#quality' ) ) . '">' . esc_html__( 'Quality Assurance', 'spicecraft' ) . '</a></li>';
						echo '<li><a href="' . esc_url( home_url( '/#contact' ) ) . '">' . esc_html__( 'Contact Us', 'spicecraft' ) . '</a></li>';
						echo '</ul>';
					},
				)
			);
			?>
		</div>

		<!-- Column 3: Footer Menu 2 -->
		<div class="sc-footer-col">
			<h4><?php esc_html_e( 'Spice Range', 'spicecraft' ); ?></h4>
			<?php
			wp_nav_menu(
				array(
					'theme_location' => 'footer_2',
					'menu_class'     => 'sc-footer-menu',
					'container'      => false,
					'fallback_cb'    => function () {
						echo '<ul class="sc-footer-menu">';
						echo '<li><a href="' . esc_url( home_url( '/#ground-spices' ) ) . '">' . esc_html__( 'Ground Spices', 'spicecraft' ) . '</a></li>';
						echo '<li><a href="' . esc_url( home_url( '/#whole-spices' ) ) . '">' . esc_html__( 'Whole Spices', 'spicecraft' ) . '</a></li>';
						echo '<li><a href="' . esc_url( home_url( '/#blended-masalas' ) ) . '">' . esc_html__( 'Blended Masalas', 'spicecraft' ) . '</a></li>';
						echo '<li><a href="' . esc_url( home_url( '/#export-bulk' ) ) . '">' . esc_html__( 'Export & Bulk Supply', 'spicecraft' ) . '</a></li>';
						echo '</ul>';
					},
				)
			);
			?>
		</div>

		<!-- Column 4: Contact & Trade Desks -->
		<div class="sc-footer-col">
			<h4><?php esc_html_e( 'Trade & Enquiries', 'spicecraft' ); ?></h4>
			<p style="font-size: 0.875rem; margin-bottom: 0.5rem;">
				<strong><?php esc_html_e( 'Export Desk:', 'spicecraft' ); ?></strong><br>
				<a href="mailto:<?php echo esc_attr( sanitize_email( $export_email ) ); ?>"><?php echo esc_html( $export_email ); ?></a>
			</p>
			<p style="font-size: 0.875rem; margin-bottom: 0.5rem;">
				<strong><?php esc_html_e( 'Direct Phone:', 'spicecraft' ); ?></strong><br>
				<a href="tel:<?php echo esc_attr( $clean_phone ); ?>"><?php echo esc_html( $phone ); ?></a>
			</p>
			<p style="font-size: 0.875rem; margin-bottom: 0.5rem;">
				<strong><?php esc_html_e( 'WhatsApp:', 'spicecraft' ); ?></strong><br>
				<a href="<?php echo esc_url( spicecraft_get_whatsapp_enquiry_url() ); ?>" target="_blank" rel="noopener noreferrer" style="color: var(--sc-color-whatsapp);">
					<?php echo esc_html( $whatsapp ); ?>
				</a>
			</p>
		</div>
	</div>
</div>

<!-- Footer Bottom Bar -->
<div class="sc-footer-bottom">
	<div class="sc-container sc-footer-bottom__inner">
		<div>
			<?php if ( ! empty( $custom_copyright ) ) : ?>
				<?php echo esc_html( $custom_copyright ); ?>
			<?php else : ?>
				&copy; <?php echo esc_html( gmdate( 'Y' ) ); ?> <?php bloginfo( 'name' ); ?>. <?php esc_html_e( 'All rights reserved.', 'spicecraft' ); ?>
			<?php endif; ?>
		</div>
		<div style="font-size: 0.8rem; color: #9ca3af;">
			<?php esc_html_e( 'Pure Indian Spices & Food Ingredients • Premium FMCG Catalog', 'spicecraft' ); ?>
		</div>
	</div>
</div>
