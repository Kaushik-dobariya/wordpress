<?php
/**
 * Template part for displaying the site navigation and header bar.
 *
 * Provides responsive navigation structure:
 * - Desktop: Logo, Primary Navigation (with Submenu/Dropdown support), Search, Favourites with count, Trade Enquiry CTA.
 * - Mobile: Logo, Search trigger, Favourite trigger with count, Menu toggle drawer.
 *
 * @package SpiceCraft
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$phone        = spicecraft_get_theme_option( 'spicecraft_phone_number', '+91 (0) 79 1234 5678' );
$clean_phone  = spicecraft_clean_phone_number( $phone );
$export_email = spicecraft_get_theme_option( 'spicecraft_export_email', 'exports@spicecraft.local' );
$whatsapp_url = spicecraft_get_whatsapp_enquiry_url();
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

		<div class="sc-topbar__actions">
			<a href="<?php echo esc_url( $whatsapp_url ); ?>" target="_blank" rel="noopener noreferrer" class="sc-topbar__link">
				<span style="color: var(--sc-color-whatsapp); font-weight: 700;">●</span>
				<span><?php esc_html_e( 'Quick WhatsApp Trade Chat', 'spicecraft' ); ?></span>
			</a>
		</div>
	</div>
</div>

<!-- Main Header Row -->
<div class="sc-header-main">
	<div class="sc-container sc-header-main__inner">
		<!-- Brand Logo & Title -->
		<div class="site-branding">
			<?php
			if ( has_custom_logo() ) {
				the_custom_logo();
			} else {
				?>
				<div>
					<h1 class="site-title"><a href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home"><?php bloginfo( 'name' ); ?></a></h1>
					<?php
					$description = get_bloginfo( 'description', 'display' );
					if ( $description || is_customize_preview() ) :
						?>
						<p class="site-description"><?php echo esc_html( $description ); ?></p>
					<?php endif; ?>
				</div>
				<?php
			}
			?>
		</div>

		<!-- Desktop Navigation with Dropdown/Submenu support -->
		<nav id="site-navigation" class="main-navigation" aria-label="<?php esc_attr_e( 'Primary Menu', 'spicecraft' ); ?>">
			<?php
			wp_nav_menu(
				array(
					'theme_location' => 'primary',
					'menu_id'        => 'primary-menu',
					'menu_class'     => 'sc-nav-menu',
					'container'      => false,
					'fallback_cb'    => function () {
						echo '<ul class="sc-nav-menu">';
						echo '<li><a href="' . esc_url( home_url( '/' ) ) . '">' . esc_html__( 'Home', 'spicecraft' ) . '</a></li>';
						if ( class_exists( 'WooCommerce' ) ) {
							echo '<li class="menu-item-has-children"><a href="' . esc_url( wc_get_page_permalink( 'shop' ) ) . '">' . esc_html__( 'Products Catalog', 'spicecraft' ) . '</a>';
							echo '<ul class="sub-menu">';
							echo '<li><a href="' . esc_url( wc_get_page_permalink( 'shop' ) ) . '">' . esc_html__( 'All Spices', 'spicecraft' ) . '</a></li>';
							echo '<li><a href="' . esc_url( home_url( '/#ground-spices' ) ) . '">' . esc_html__( 'Ground Spices', 'spicecraft' ) . '</a></li>';
							echo '<li><a href="' . esc_url( home_url( '/#whole-spices' ) ) . '">' . esc_html__( 'Whole Spices', 'spicecraft' ) . '</a></li>';
							echo '<li><a href="' . esc_url( home_url( '/#blended-masalas' ) ) . '">' . esc_html__( 'Blended Masalas', 'spicecraft' ) . '</a></li>';
							echo '<li><a href="' . esc_url( home_url( '/#export-bulk' ) ) . '">' . esc_html__( 'Export Supply', 'spicecraft' ) . '</a></li>';
							echo '</ul></li>';
						}
						echo '<li><a href="' . esc_url( home_url( '/#about' ) ) . '">' . esc_html__( 'About Us', 'spicecraft' ) . '</a></li>';
						echo '<li><a href="' . esc_url( home_url( '/#quality' ) ) . '">' . esc_html__( 'Quality Assurance', 'spicecraft' ) . '</a></li>';
						echo '<li><a href="' . esc_url( home_url( '/#contact' ) ) . '">' . esc_html__( 'Contact', 'spicecraft' ) . '</a></li>';
						echo '</ul>';
					},
				)
			);
			?>
		</nav>

		<!-- Header Actions: Search, Favourites, CTA & Mobile Toggle -->
		<div class="sc-header-actions">
			<!-- Search Trigger -->
			<button class="sc-header-action sc-search-toggle" aria-expanded="false" aria-controls="header-search-drawer" aria-label="<?php esc_attr_e( 'Search Products', 'spicecraft' ); ?>">
				<svg class="sc-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
					<circle cx="11" cy="11" r="8"/>
					<line x1="21" y1="21" x2="16.65" y2="16.65"/>
				</svg>
			</button>

			<!-- Favourites Trigger with Counter Badge Placeholder -->
			<a href="#favourites" class="sc-header-action sc-header-favourite" aria-label="<?php esc_attr_e( 'Favourite Products', 'spicecraft' ); ?>" title="<?php esc_attr_e( 'Favourite Products', 'spicecraft' ); ?>">
				<svg class="sc-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
					<path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/>
				</svg>
				<span class="sc-badge-count" aria-hidden="true">0</span>
			</a>

			<!-- Desktop Contact / Trade Enquiry CTA -->
			<a href="<?php echo esc_url( home_url( '/#contact' ) ); ?>" class="sc-btn sc-btn--primary sc-header-cta">
				<?php esc_html_e( 'Trade Enquiry', 'spicecraft' ); ?>
			</a>

			<!-- Mobile Menu Toggle Button -->
			<button class="sc-menu-toggle" aria-controls="mobile-navigation" aria-expanded="false" aria-label="<?php esc_attr_e( 'Open Navigation Menu', 'spicecraft' ); ?>">
				<svg class="sc-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
					<line x1="3" y1="12" x2="21" y2="12"/>
					<line x1="3" y1="6" x2="21" y2="6"/>
					<line x1="3" y1="18" x2="21" y2="18"/>
				</svg>
			</button>
		</div>
	</div>
</div>

<!-- Header Search Drawer (Accessible Dropdown/Slide-Down) -->
<div id="header-search-drawer" class="sc-search-drawer" aria-label="<?php esc_attr_e( 'Catalog Search Form', 'spicecraft' ); ?>">
	<div class="sc-container sc-container--narrow">
		<?php get_search_form(); ?>
	</div>
</div>

<!-- Mobile Navigation Drawer -->
<div id="mobile-navigation" class="sc-mobile-drawer" aria-label="<?php esc_attr_e( 'Mobile Menu', 'spicecraft' ); ?>">
	<div class="sc-container">
		<?php
		wp_nav_menu(
			array(
				'theme_location' => 'mobile',
				'menu_class'     => 'sc-mobile-menu',
				'container'      => false,
				'fallback_cb'    => function () {
					echo '<ul class="sc-mobile-menu">';
					echo '<li><a href="' . esc_url( home_url( '/' ) ) . '">' . esc_html__( 'Home', 'spicecraft' ) . '</a></li>';
					if ( class_exists( 'WooCommerce' ) ) {
						echo '<li><a href="' . esc_url( wc_get_page_permalink( 'shop' ) ) . '">' . esc_html__( 'Products Catalog', 'spicecraft' ) . '</a></li>';
					}
					echo '<li><a href="' . esc_url( home_url( '/#about' ) ) . '">' . esc_html__( 'About Us', 'spicecraft' ) . '</a></li>';
					echo '<li><a href="' . esc_url( home_url( '/#quality' ) ) . '">' . esc_html__( 'Quality Assurance', 'spicecraft' ) . '</a></li>';
					echo '<li><a href="' . esc_url( home_url( '/#contact' ) ) . '">' . esc_html__( 'Contact', 'spicecraft' ) . '</a></li>';
					echo '</ul>';
				},
			)
		);
		?>
		<div style="padding: var(--sc-space-4); border-top: 1px solid var(--sc-color-border-subtle); margin-top: var(--sc-space-4);">
			<a href="<?php echo esc_url( home_url( '/#contact' ) ); ?>" class="sc-btn sc-btn--primary sc-btn--full">
				<?php esc_html_e( 'Submit Trade Enquiry', 'spicecraft' ); ?>
			</a>
		</div>
	</div>
</div>
