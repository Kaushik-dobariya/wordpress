<?php
/**
 * SpiceCraft Navigation & Mega Menu Architecture (Phase 5.3)
 *
 * Implements accessible B2B mega menu for Products catalog:
 * - Dynamic generation from existing 'product_cat' taxonomy
 * - Desktop: 4-column rich mega menu with category groups, top items, and B2B trade desk CTA
 * - Mobile: Graceful accordion degradation (expand/collapse) without desktop layout squeezing
 * - Complete URL preservation: all URLs point to existing taxonomy and shop permalinks
 *
 * @package SpiceCraft
 * @since 1.5.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Filter nav menu CSS classes to mark Products menu item as having mega menu.
 */
function spicecraft_filter_nav_menu_classes( $classes, $item, $args ) {
	if ( 'primary' === ( $args->theme_location ?? '' ) ) {
		$title = strtolower( trim( $item->title ) );
		$url   = trim( $item->url );

		if ( 'products' === $title || 'product catalog' === $title || strpos( $url, '/shop' ) !== false || in_array( 'mega-menu', $classes, true ) ) {
			$classes[] = 'menu-item-has-mega-menu';
			$classes[] = 'menu-item-has-children';
		}
	} elseif ( 'mobile' === ( $args->theme_location ?? '' ) ) {
		$title = strtolower( trim( $item->title ) );
		$url   = trim( $item->url );

		if ( 'products' === $title || 'product catalog' === $title || strpos( $url, '/shop' ) !== false || in_array( 'mega-menu', $classes, true ) ) {
			$classes[] = 'menu-item-has-children';
			$classes[] = 'sc-mobile-mega-parent';
		}
	}

	return array_unique( $classes );
}
add_filter( 'nav_menu_css_class', 'spicecraft_filter_nav_menu_classes', 10, 3 );

/**
 * Custom Desktop Nav Walker with Mega Menu Support
 */
class SpiceCraft_Mega_Menu_Walker extends Walker_Nav_Menu {

	/**
	 * Starts the element output.
	 */
	public function start_el( &$output, $data_object, $depth = 0, $args = null, $current_object_id = 0 ) {
		// Standard start element
		parent::start_el( $output, $data_object, $depth, $args, $current_object_id );

		// Check if this item is the Products / Mega Menu trigger at top-level
		if ( 0 === $depth ) {
			$classes = empty( $data_object->classes ) ? array() : (array) $data_object->classes;
			$title   = strtolower( trim( $data_object->title ) );
			$url     = trim( $data_object->url );

			if ( in_array( 'menu-item-has-mega-menu', $classes, true ) || 'products' === $title || strpos( $url, '/shop' ) !== false ) {
				$output .= $this->get_mega_menu_html( $data_object );
			}
		}
	}

	/**
	 * Generate the dynamic Mega Menu HTML panel
	 *
	 * @param WP_Post $menu_item The current menu item object.
	 * @return string
	 */
	private function get_mega_menu_html( $menu_item ) {
		$shop_url = class_exists( 'WooCommerce' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/shop/' );

		// Fetch top product categories from existing product_cat taxonomy
		$categories = get_terms(
			array(
				'taxonomy'   => 'product_cat',
				'hide_empty' => false,
				'parent'     => 0,
				'exclude'    => array( get_option( 'default_product_cat' ) ), // Exclude Uncategorized
				'number'     => 4,
				'orderby'    => 'count',
				'order'      => 'DESC',
			)
		);

		ob_start();
		?>
		<div class="sc-mega-menu" role="region" aria-label="<?php esc_attr_e( 'Products Mega Menu', 'spicecraft' ); ?>">
			<div class="sc-mega-menu__container">
				<div class="sc-mega-menu__header">
					<span class="sc-mega-menu__title"><?php esc_html_e( 'SpiceCraft FMCG Product Catalogue', 'spicecraft' ); ?></span>
					<a href="<?php echo esc_url( $shop_url ); ?>" class="sc-mega-menu__view-all">
						<?php esc_html_e( 'View Complete Catalog', 'spicecraft' ); ?> &rarr;
					</a>
				</div>

				<div class="sc-mega-menu__grid">
					<?php if ( ! empty( $categories ) && ! is_wp_error( $categories ) ) : ?>
						<?php foreach ( $categories as $cat ) : ?>
							<?php
							$cat_link = get_term_link( $cat );
							// Fetch up to 4 sample products for this category
							$sample_prods = get_posts(
								array(
									'post_type'      => 'product',
									'post_status'    => 'publish',
									'posts_per_page' => 4,
									'tax_query'      => array(
										array(
											'taxonomy' => 'product_cat',
											'field'    => 'term_id',
											'terms'    => $cat->term_id,
										),
									),
								)
							);
							?>
							<div class="sc-mega-col">
								<h3 class="sc-mega-col__title">
									<a href="<?php echo esc_url( $cat_link ); ?>"><?php echo esc_html( $cat->name ); ?></a>
								</h3>
								<ul class="sc-mega-col__list">
									<?php if ( ! empty( $sample_prods ) ) : ?>
										<?php foreach ( $sample_prods as $sp ) : ?>
											<li>
												<a href="<?php echo esc_url( get_permalink( $sp->ID ) ); ?>">
													<?php echo esc_html( $sp->post_title ); ?>
												</a>
											</li>
										<?php endforeach; ?>
									<?php else : ?>
										<li>
											<a href="<?php echo esc_url( $cat_link ); ?>" style="color:var(--sc-color-text-muted);">
												<?php esc_html_e( 'Browse Category &rarr;', 'spicecraft' ); ?>
											</a>
										</li>
									<?php endif; ?>
									<li class="sc-mega-col__all-link">
										<a href="<?php echo esc_url( $cat_link ); ?>" style="color:var(--sc-color-primary); font-weight:600; margin-top:4px;">
											<?php esc_html_e( 'All', 'spicecraft' ); ?> <?php echo esc_html( $cat->name ); ?> &rarr;
										</a>
									</li>
								</ul>
							</div>
						<?php endforeach; ?>
					<?php endif; ?>

					<!-- Column 4: B2B Commercial Procurement Desk Card -->
					<div class="sc-mega-col sc-mega-col--featured">
						<div class="sc-mega-featured-card">
							<div>
								<span class="sc-eyebrow sc-eyebrow--accent" style="font-size:0.7rem;"><?php esc_html_e( 'Institutional Supply', 'spicecraft' ); ?></span>
								<h4><?php esc_html_e( 'B2B Procurement Desk', 'spicecraft' ); ?></h4>
								<p>
									<?php esc_html_e( 'Custom blending, cryogenic grinding, institutional pack sizes (25kg - FCL), and certified export phytosanitary documentation.', 'spicecraft' ); ?>
								</p>
							</div>
							<div style="display:flex; flex-direction:column; gap:8px;">
								<a href="<?php echo esc_url( home_url( '/contact/' ) ); ?>" class="sc-btn sc-btn--primary sc-btn--sm" style="text-align:center;">
									<?php esc_html_e( 'Request Bulk Quotation', 'spicecraft' ); ?>
								</a>
								<a href="<?php echo esc_url( home_url( '/manufacturing/' ) ); ?>" style="font-size:0.8rem; color:var(--sc-color-secondary); font-weight:600; text-align:center;">
									<?php esc_html_e( 'Tour Milling Infrastructure &rarr;', 'spicecraft' ); ?>
								</a>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<?php
		return ob_get_clean();
	}
}

/**
 * Custom Mobile Nav Walker with Expandable Submenus & Product Categories
 */
class SpiceCraft_Mobile_Menu_Walker extends Walker_Nav_Menu {

	/**
	 * Starts the element output.
	 */
	public function start_el( &$output, $data_object, $depth = 0, $args = null, $current_object_id = 0 ) {
		parent::start_el( $output, $data_object, $depth, $args, $current_object_id );

		// For Mobile Products Menu Item, append an accessible category list submenu
		if ( 0 === $depth ) {
			$title = strtolower( trim( $data_object->title ) );
			$url   = trim( $data_object->url );

			if ( 'products' === $title || strpos( $url, '/shop' ) !== false ) {
				$output .= $this->get_mobile_products_submenu();
			}
		}
	}

	/**
	 * Generate Mobile Submenu with Category Accordion
	 *
	 * @return string
	 */
	private function get_mobile_products_submenu() {
		$shop_url = class_exists( 'WooCommerce' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/shop/' );
		$categories = get_terms(
			array(
				'taxonomy'   => 'product_cat',
				'hide_empty' => false,
				'parent'     => 0,
				'exclude'    => array( get_option( 'default_product_cat' ) ),
				'number'     => 6,
			)
		);

		ob_start();
		?>
		<ul class="sub-menu sc-mobile-submenu-accordion">
			<li><a href="<?php echo esc_url( $shop_url ); ?>" style="font-weight:700; color:var(--sc-color-primary);"><?php esc_html_e( 'View All Products', 'spicecraft' ); ?> &rarr;</a></li>
			<?php if ( ! empty( $categories ) && ! is_wp_error( $categories ) ) : ?>
				<?php foreach ( $categories as $cat ) : ?>
					<li>
						<a href="<?php echo esc_url( get_term_link( $cat ) ); ?>">
							<?php echo esc_html( $cat->name ); ?>
							<?php if ( $cat->count > 0 ) : ?>
								<span style="font-size:0.75rem; color:var(--sc-color-text-muted);">(<?php echo esc_html( $cat->count ); ?>)</span>
							<?php endif; ?>
						</a>
					</li>
				<?php endforeach; ?>
			<?php endif; ?>
			<li><a href="<?php echo esc_url( home_url( '/contact/' ) ); ?>" style="font-weight:600; color:var(--sc-color-secondary);"><?php esc_html_e( 'Commercial Bulk Enquiry', 'spicecraft' ); ?> &rarr;</a></li>
		</ul>
		<?php
		return ob_get_clean();
	}
}
