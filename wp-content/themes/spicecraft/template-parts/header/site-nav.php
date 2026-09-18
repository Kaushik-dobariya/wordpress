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

// Dynamic Header CTA
$cta_text     = function_exists( 'spicecraft_get_setting' ) ? spicecraft_get_setting( 'header_cta_text', '' ) : '';
$cta_text     = ! empty( $cta_text ) ? $cta_text : __( 'Trade Enquiry', 'spicecraft' );
$cta_url      = function_exists( 'spicecraft_get_setting' ) ? spicecraft_get_setting( 'header_cta_url', '' ) : '';
$cta_url      = ! empty( $cta_url ) ? $cta_url : home_url( '/#contact' );
?>

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
					<p class="site-title"><a href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home"><?php bloginfo( 'name' ); ?></a></p>
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
						echo '<li><a href="' . esc_url( home_url( '/about/' ) ) . '">' . esc_html__( 'About Us', 'spicecraft' ) . '</a></li>';
						echo '<li><a href="' . esc_url( home_url( '/manufacturing/' ) ) . '">' . esc_html__( 'Manufacturing', 'spicecraft' ) . '</a></li>';
						echo '<li><a href="' . esc_url( home_url( '/quality/' ) ) . '">' . esc_html__( 'Quality & Sourcing', 'spicecraft' ) . '</a></li>';
						echo '<li><a href="' . esc_url( home_url( '/certifications/' ) ) . '">' . esc_html__( 'Certifications', 'spicecraft' ) . '</a></li>';
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

			<!-- Favourites Trigger with Counter Badge -->
			<?php $fav_url = function_exists( 'spicecraft_get_favourites_url' ) ? spicecraft_get_favourites_url() : home_url( '/favourites/' ); ?>
			<a href="<?php echo esc_url( $fav_url ); ?>" class="sc-header-action sc-header-favourite" aria-label="<?php esc_attr_e( 'Saved Favourite Products', 'spicecraft' ); ?>" title="<?php esc_attr_e( 'Saved Favourite Products', 'spicecraft' ); ?>">
				<svg class="sc-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
					<path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/>
				</svg>
				<span class="sc-badge-count" aria-hidden="true">0</span>
			</a>

			<!-- Desktop Contact / Trade Enquiry CTA -->
			<a href="<?php echo esc_url( $cta_url ); ?>" class="sc-btn sc-btn--primary sc-header-cta">
				<?php echo esc_html( $cta_text ); ?>
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

<!-- Search Drawer Overlay -->
<div id="header-search-drawer" class="sc-search-drawer" aria-hidden="true">
	<div class="sc-container sc-search-drawer__inner">
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
					echo '<li><a href="' . esc_url( home_url( '/about/' ) ) . '">' . esc_html__( 'About Us', 'spicecraft' ) . '</a></li>';
					echo '<li><a href="' . esc_url( home_url( '/manufacturing/' ) ) . '">' . esc_html__( 'Manufacturing', 'spicecraft' ) . '</a></li>';
					echo '<li><a href="' . esc_url( home_url( '/quality/' ) ) . '">' . esc_html__( 'Quality & Sourcing', 'spicecraft' ) . '</a></li>';
					echo '<li><a href="' . esc_url( home_url( '/certifications/' ) ) . '">' . esc_html__( 'Certifications', 'spicecraft' ) . '</a></li>';
					echo '<li><a href="' . esc_url( home_url( '/#contact' ) ) . '">' . esc_html__( 'Contact', 'spicecraft' ) . '</a></li>';
					echo '</ul>';
				},
			)
		);
		?>
		<div style="padding: var(--sc-space-4); border-top: 1px solid var(--sc-color-border-subtle); margin-top: var(--sc-space-4);">
			<a href="<?php echo esc_url( $cta_url ); ?>" class="sc-btn sc-btn--primary sc-btn--full">
				<?php echo esc_html( $cta_text ); ?>
			</a>
		</div>
	</div>
</div>
