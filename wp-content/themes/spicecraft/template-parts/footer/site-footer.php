<?php
/**
 * Template part for displaying the site footer content.
 *
 * Fully dynamic and driven by SpiceCraft Global Settings:
 * - Admin-controlled brand description, addresses, and contacts
 * - Strictly optional regulatory fields (FSSAI, GST, IEC) with zero fake claims
 * - Dynamic social channels rendered only when valid URLs exist
 * - Dynamic copyright and legal disclaimer
 *
 * @package SpiceCraft
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Global Business Information
$company_name       = function_exists( 'spicecraft_get_setting' ) ? spicecraft_get_setting( 'company_name', get_bloginfo( 'name' ) ) : get_bloginfo( 'name' );
$footer_desc        = function_exists( 'spicecraft_get_setting' ) ? spicecraft_get_setting( 'footer_description', get_bloginfo( 'description' ) ) : get_bloginfo( 'description' );
$address_primary    = function_exists( 'spicecraft_get_setting' ) ? spicecraft_get_setting( 'address_primary', '' ) : '';
$address_factory    = function_exists( 'spicecraft_get_setting' ) ? spicecraft_get_setting( 'address_factory', '' ) : '';
$fssai              = function_exists( 'spicecraft_get_setting' ) ? spicecraft_get_setting( 'fssai_license', '' ) : '';
$gst                = function_exists( 'spicecraft_get_setting' ) ? spicecraft_get_setting( 'gst_number', '' ) : '';
$iec                = function_exists( 'spicecraft_get_setting' ) ? spicecraft_get_setting( 'iec_code', '' ) : '';
$certs_summary      = function_exists( 'spicecraft_get_setting' ) ? spicecraft_get_setting( 'certifications_summary', '' ) : '';

// Communication & Desks
$phone_primary      = function_exists( 'spicecraft_get_setting' ) ? spicecraft_get_setting( 'phone_primary', '' ) : '';
$clean_phone        = ! empty( $phone_primary ) ? spicecraft_clean_phone_number( $phone_primary ) : '';
$whatsapp           = function_exists( 'spicecraft_get_setting' ) ? spicecraft_get_setting( 'whatsapp_number', '' ) : '';
$email_export       = function_exists( 'spicecraft_get_setting' ) ? spicecraft_get_setting( 'email_export', '' ) : '';
$email_sales        = function_exists( 'spicecraft_get_setting' ) ? spicecraft_get_setting( 'email_sales', '' ) : '';
$email_general      = function_exists( 'spicecraft_get_setting' ) ? spicecraft_get_setting( 'email_general', '' ) : '';
$preferred_email    = ! empty( $email_export ) ? $email_export : ( ! empty( $email_sales ) ? $email_sales : $email_general );

// Legal & Copyright
$custom_copyright   = function_exists( 'spicecraft_get_setting' ) ? spicecraft_get_setting( 'footer_copyright', '' ) : '';
$disclaimer         = function_exists( 'spicecraft_get_setting' ) ? spicecraft_get_setting( 'footer_disclaimer', '' ) : '';
$footer_logo_url    = function_exists( 'spicecraft_get_setting' ) ? spicecraft_get_setting( 'footer_logo_url', '' ) : '';

// Social Channels
$social_channels = array(
	'facebook'  => array(
		'url'   => function_exists( 'spicecraft_get_setting' ) ? spicecraft_get_setting( 'social_facebook', '' ) : '',
		'label' => 'Facebook',
		'svg'   => '<path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"/>',
	),
	'instagram' => array(
		'url'   => function_exists( 'spicecraft_get_setting' ) ? spicecraft_get_setting( 'social_instagram', '' ) : '',
		'label' => 'Instagram',
		'svg'   => '<rect x="2" y="2" width="20" height="20" rx="5" ry="5"/><path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"/><line x1="17.5" y1="6.5" x2="17.51" y2="6.5"/>',
	),
	'linkedin'  => array(
		'url'   => function_exists( 'spicecraft_get_setting' ) ? spicecraft_get_setting( 'social_linkedin', '' ) : '',
		'label' => 'LinkedIn',
		'svg'   => '<path d="M16 8a6 6 0 0 1 6 6v7h-4v-7a2 2 0 0 0-2-2 2 2 0 0 0-2 2v7h-4v-7a6 6 0 0 1 6-6z"/><rect x="2" y="9" width="4" height="12"/><circle cx="4" cy="4" r="2"/>',
	),
	'youtube'   => array(
		'url'   => function_exists( 'spicecraft_get_setting' ) ? spicecraft_get_setting( 'social_youtube', '' ) : '',
		'label' => 'YouTube',
		'svg'   => '<path d="M22.54 6.42a2.78 2.78 0 0 0-1.94-2C18.88 4 12 4 12 4s-6.88 0-8.6.46a2.78 2.78 0 0 0-1.94 2A29 29 0 0 0 1 11.75a29 29 0 0 0 .46 5.33A2.78 2.78 0 0 0 3.4 19c1.72.46 8.6.46 8.6.46s6.88 0 8.6-.46a2.78 2.78 0 0 0 1.94-2 29 29 0 0 0 .46-5.25 29 29 0 0 0-.46-5.33z"/><polygon points="9.75 15.02 15.5 11.75 9.75 8.48 9.75 15.02"/>',
	),
	'twitter'   => array(
		'url'   => function_exists( 'spicecraft_get_setting' ) ? spicecraft_get_setting( 'social_twitter', '' ) : '',
		'label' => 'X (Twitter)',
		'svg'   => '<path d="M4 4l11.733 16h4.267l-11.733-16zM4 20l6.768-6.768m2.464-2.464L20 4"/>',
	),
	'pinterest' => array(
		'url'   => function_exists( 'spicecraft_get_setting' ) ? spicecraft_get_setting( 'social_pinterest', '' ) : '',
		'label' => 'Pinterest',
		'svg'   => '<path d="M12 2C6.48 2 2 6.48 2 12c0 4.24 2.64 7.86 6.38 9.29-.09-.79-.17-2 .04-2.86.19-.78 1.22-5.18 1.22-5.18s-.31-.63-.31-1.55c0-1.45.84-2.54 1.89-2.54.89 0 1.32.67 1.32 1.47 0 .9-.57 2.24-.87 3.49-.25 1.04.52 1.89 1.54 1.89 1.85 0 3.28-1.95 3.28-4.77 0-2.49-1.79-4.24-4.35-4.24-2.97 0-4.71 2.22-4.71 4.52 0 .89.34 1.85.77 2.37.09.1.1.19.07.33-.08.33-.26 1.07-.3 1.22-.05.19-.16.23-.37.14-1.39-.65-2.26-2.68-2.26-4.32 0-3.52 2.56-6.75 7.37-6.75 3.87 0 6.88 2.76 6.88 6.44 0 3.85-2.43 6.94-5.79 6.94-1.13 0-2.2-.59-2.56-1.29l-.7 2.66c-.25.98-.94 2.2-1.4 2.95 1.05.32 2.16.5 3.32.5 5.52 0 10-4.48 10-10S17.52 2 12 2z"/>',
	),
);
?>

<div class="sc-container">
	<div class="sc-footer-grid">
		<!-- Column 1: Brand & Plant Overview -->
		<div class="sc-footer-col">
			<?php if ( ! empty( $footer_logo_url ) ) : ?>
				<div class="sc-footer-logo" style="margin-bottom: 1rem;">
					<img src="<?php echo esc_url( $footer_logo_url ); ?>" alt="<?php echo esc_attr( $company_name ); ?>" style="max-height: 48px; width: auto;" />
				</div>
			<?php else : ?>
				<h4><?php echo esc_html( $company_name ); ?></h4>
			<?php endif; ?>

			<?php if ( ! empty( $footer_desc ) ) : ?>
				<p style="font-size: 0.9rem; line-height: 1.6; color: #d1d5db; margin-bottom: 1rem;">
					<?php echo nl2br( esc_html( $footer_desc ) ); ?>
				</p>
			<?php endif; ?>

			<!-- Regulatory Information (Rendered only if saved by admin) -->
			<?php if ( ! empty( $fssai ) ) : ?>
				<p style="font-size: 0.8rem; color: var(--sc-color-accent-light, #d4a373); margin-bottom: 0.35rem;">
					<strong><?php esc_html_e( 'FSSAI Lic. No.:', 'spicecraft' ); ?></strong> <?php echo esc_html( $fssai ); ?>
				</p>
			<?php endif; ?>

			<?php if ( ! empty( $gst ) ) : ?>
				<p style="font-size: 0.8rem; color: #9ca3af; margin-bottom: 0.35rem;">
					<strong><?php esc_html_e( 'GSTIN:', 'spicecraft' ); ?></strong> <?php echo esc_html( $gst ); ?>
				</p>
			<?php endif; ?>

			<?php if ( ! empty( $iec ) ) : ?>
				<p style="font-size: 0.8rem; color: #9ca3af; margin-bottom: 0.35rem;">
					<strong><?php esc_html_e( 'IEC Code:', 'spicecraft' ); ?></strong> <?php echo esc_html( $iec ); ?>
				</p>
			<?php endif; ?>

			<?php if ( ! empty( $certs_summary ) ) : ?>
				<p style="font-size: 0.75rem; color: #9ca3af; margin: 0.5rem 0 0;">
					<?php echo esc_html( $certs_summary ); ?>
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
						if ( class_exists( 'WooCommerce' ) ) {
							echo '<li><a href="' . esc_url( wc_get_page_permalink( 'shop' ) ) . '">' . esc_html__( 'Products Catalog', 'spicecraft' ) . '</a></li>';
						}
						echo '<li><a href="' . esc_url( home_url( '/about/' ) ) . '">' . esc_html__( 'About SpiceCraft', 'spicecraft' ) . '</a></li>';
						echo '<li><a href="' . esc_url( home_url( '/manufacturing/' ) ) . '">' . esc_html__( 'Manufacturing', 'spicecraft' ) . '</a></li>';
						echo '<li><a href="' . esc_url( home_url( '/quality/' ) ) . '">' . esc_html__( 'Quality & Sourcing', 'spicecraft' ) . '</a></li>';
						echo '<li><a href="' . esc_url( home_url( '/certifications/' ) ) . '">' . esc_html__( 'Certifications', 'spicecraft' ) . '</a></li>';
						echo '<li><a href="' . esc_url( home_url( '/#contact' ) ) . '">' . esc_html__( 'Contact Desks', 'spicecraft' ) . '</a></li>';
						echo '</ul>';
					},
				)
			);
			?>
		</div>

		<!-- Column 3: Footer Menu 2 -->
		<div class="sc-footer-col">
			<h4><?php esc_html_e( 'Spice Categories', 'spicecraft' ); ?></h4>
			<?php
			wp_nav_menu(
				array(
					'theme_location' => 'footer_2',
					'menu_class'     => 'sc-footer-menu',
					'container'      => false,
					'fallback_cb'    => function () {
						echo '<ul class="sc-footer-menu">';
						if ( class_exists( 'WooCommerce' ) ) {
							$cats = get_terms( array( 'taxonomy' => 'product_cat', 'hide_empty' => false, 'parent' => 0, 'number' => 5 ) );
							if ( ! empty( $cats ) && ! is_wp_error( $cats ) ) {
								foreach ( $cats as $c ) {
									if ( 'uncategorized' !== $c->slug ) {
										echo '<li><a href="' . esc_url( get_term_link( $c ) ) . '">' . esc_html( $c->name ) . '</a></li>';
									}
								}
							} else {
								echo '<li><a href="' . esc_url( wc_get_page_permalink( 'shop' ) ) . '">' . esc_html__( 'All Spices', 'spicecraft' ) . '</a></li>';
							}
						}
						echo '</ul>';
					},
				)
			);
			?>
		</div>

		<!-- Column 4: Contact, Trade Desks & Social Channels -->
		<div class="sc-footer-col">
			<h4><?php esc_html_e( 'Trade & Enquiries', 'spicecraft' ); ?></h4>

			<?php if ( ! empty( $address_primary ) ) : ?>
				<p style="font-size: 0.85rem; line-height: 1.5; color: #d1d5db; margin-bottom: 0.75rem;">
					<?php echo nl2br( esc_html( $address_primary ) ); ?>
				</p>
			<?php endif; ?>

			<?php if ( ! empty( $preferred_email ) ) : ?>
				<p style="font-size: 0.875rem; margin-bottom: 0.5rem;">
					<strong><?php esc_html_e( 'Enquiry Desk:', 'spicecraft' ); ?></strong><br>
					<a href="mailto:<?php echo esc_attr( sanitize_email( $preferred_email ) ); ?>"><?php echo esc_html( $preferred_email ); ?></a>
				</p>
			<?php endif; ?>

			<?php if ( ! empty( $phone_primary ) ) : ?>
				<p style="font-size: 0.875rem; margin-bottom: 0.5rem;">
					<strong><?php esc_html_e( 'Direct Phone:', 'spicecraft' ); ?></strong><br>
					<a href="tel:<?php echo esc_attr( $clean_phone ); ?>"><?php echo esc_html( $phone_primary ); ?></a>
				</p>
			<?php endif; ?>

			<?php if ( ! empty( $whatsapp ) ) : ?>
				<p style="font-size: 0.875rem; margin-bottom: 0.75rem;">
					<strong><?php esc_html_e( 'WhatsApp Trade Desk:', 'spicecraft' ); ?></strong><br>
					<a href="<?php echo esc_url( spicecraft_get_whatsapp_enquiry_url() ); ?>" target="_blank" rel="noopener noreferrer" style="color: var(--sc-color-whatsapp, #25D366); font-weight: 600;">
						<?php echo esc_html( $whatsapp ); ?>
					</a>
				</p>
			<?php endif; ?>

			<!-- Social Channels (Only rendered when URLs exist) -->
			<?php
			$active_socials = array_filter(
				$social_channels,
				function( $item ) {
					return ! empty( $item['url'] );
				}
			);
			?>
			<?php if ( ! empty( $active_socials ) ) : ?>
				<div class="sc-footer-socials" style="margin-top: 1rem; display: flex; gap: 0.75rem; align-items: center; flex-wrap: wrap;">
					<?php foreach ( $active_socials as $social_key => $social_item ) : ?>
						<a href="<?php echo esc_url( $social_item['url'] ); ?>" 
							target="_blank" 
							rel="noopener noreferrer" 
							aria-label="<?php echo esc_attr( $social_item['label'] ); ?>"
							title="<?php echo esc_attr( $social_item['label'] ); ?>"
							class="sc-social-icon-link"
							style="display: inline-flex; align-items: center; justify-content: center; width: 34px; height: 34px; border-radius: 50%; background: rgba(255,255,255,0.08); color: #fff; transition: background 0.2s, color 0.2s;">
							<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
								<?php echo $social_item['svg']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
							</svg>
						</a>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>
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
				&copy; <?php echo esc_html( gmdate( 'Y' ) ); ?> <?php echo esc_html( $company_name ); ?>. <?php esc_html_e( 'All rights reserved.', 'spicecraft' ); ?>
			<?php endif; ?>
		</div>
		<?php if ( ! empty( $disclaimer ) ) : ?>
			<div style="font-size: 0.8rem; color: #9ca3af; max-width: 600px; text-align: right;">
				<?php echo esc_html( $disclaimer ); ?>
			</div>
		<?php else : ?>
			<div style="font-size: 0.8rem; color: #9ca3af;">
				<?php esc_html_e( 'Pure Indian Spices & Food Ingredients • Premium FMCG Catalog', 'spicecraft' ); ?>
			</div>
		<?php endif; ?>
	</div>
</div>
