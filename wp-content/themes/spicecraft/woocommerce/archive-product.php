<?php
/**
 * SpiceCraft - Custom Product Catalog / Shop Archive Template
 *
 * Overrides WooCommerce's default archive-product.php to deliver an exceptional
 * FMCG spices discovery experience. Features:
 * - Advanced search bar with query persistence, clear button, and live suggestions
 * - Category navigation pills
 * - Desktop compact horizontal filter bar (Categories, Pack Size, Rating)
 * - Active removable filter chips with "Clear All"
 * - Mobile off-canvas filter drawer with focus trapping and body scroll lock
 * - Clean catalog ordering (No price sorting)
 * - Contextual result counts and helpful empty states
 *
 * @package SpiceCraft
 * @version 10.0.0
 */

defined( 'ABSPATH' ) || exit;

get_header( 'shop' );

/**
 * Hook: woocommerce_before_main_content.
 *
 * @hooked spicecraft_woocommerce_wrapper_before - 10
 */
do_action( 'woocommerce_before_main_content' );

$shop_link       = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/shop/' );
$search_query    = ! empty( $_GET['s'] ) ? sanitize_text_field( wp_unslash( $_GET['s'] ) ) : get_search_query();
$active_filters  = function_exists( 'spicecraft_get_active_filters' ) ? spicecraft_get_active_filters() : array();
$available_packs = function_exists( 'spicecraft_get_catalog_available_pack_sizes' ) ? spicecraft_get_catalog_available_pack_sizes() : array();
$current_pack    = isset( $_GET['pack_size'] ) ? sanitize_text_field( wp_unslash( $_GET['pack_size'] ) ) : '';
$current_rating  = isset( $_GET['rating'] ) ? intval( $_GET['rating'] ) : 0;
$current_cat     = isset( $_GET['product_cat'] ) ? sanitize_title( wp_unslash( $_GET['product_cat'] ) ) : '';
$current_orderby = isset( $_GET['orderby'] ) ? sanitize_text_field( wp_unslash( $_GET['orderby'] ) ) : '';
?>

<div class="sc-catalog-experience">

	<!-- 1. Breadcrumbs Navigation -->
	<?php woocommerce_breadcrumb(); ?>

	<!-- 2. Refined Catalog Hero -->
	<header class="sc-catalog-hero">
		<div class="sc-catalog-hero__content">
			<span class="sc-catalog-hero__eyebrow"><?php esc_html_e( 'Direct Manufacturer Catalog', 'spicecraft' ); ?></span>
			
			<h1 class="sc-catalog-hero__title">
				<?php
				if ( is_search() || ! empty( $search_query ) ) {
					/* translators: %s: search query */
					printf( esc_html__( 'Search Results for: &ldquo;%s&rdquo;', 'spicecraft' ), esc_html( $search_query ) );
				} else {
					woocommerce_page_title();
				}
				?>
			</h1>

			<div class="sc-catalog-hero__description">
				<?php
				if ( is_product_category() || is_product_tag() ) {
					do_action( 'woocommerce_archive_description' );
				} elseif ( is_search() ) {
					echo '<p>' . esc_html__( 'Reviewing spice products, whole seeds, culinary masalas, and manufacturer grades matching your search term.', 'spicecraft' ) . '</p>';
				} else {
					$shop_page_id = wc_get_page_id( 'shop' );
					$shop_post    = $shop_page_id ? get_post( $shop_page_id ) : null;
					if ( $shop_post && ! empty( trim( $shop_post->post_content ) ) ) {
						echo wp_kses_post( wpautop( $shop_post->post_content ) );
					} else {
						echo '<p>' . esc_html__( 'Discover our artisanal spice range — pure whole spices, aromatic ground powders, and master-crafted blends manufactured to institutional, retail, and export quality standards.', 'spicecraft' ) . '</p>';
					}
				}
				?>
			</div>
		</div>

		<!-- 3. Advanced Catalog Search Field with Clear & Live Suggestions -->
		<div class="sc-catalog-hero__search">
			<form role="search" method="get" class="sc-catalog-search-form" id="sc-catalog-search-form" action="<?php echo esc_url( home_url( '/' ) ); ?>">
				<label for="sc-catalog-search-input" class="screen-reader-text"><?php esc_html_e( 'Search spices, masalas and products', 'spicecraft' ); ?></label>
				<div class="sc-catalog-search-field">
					<svg class="sc-search-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
						<circle cx="11" cy="11" r="8"></circle>
						<line x1="21" y1="21" x2="16.65" y2="16.65"></line>
					</svg>
					<input type="search" 
						id="sc-catalog-search-input" 
						class="sc-catalog-search-input" 
						placeholder="<?php esc_attr_e( 'Search spices, masalas and products...', 'spicecraft' ); ?>" 
						value="<?php echo esc_attr( $search_query ); ?>" 
						name="s" 
						autocomplete="off" />
					<input type="hidden" name="post_type" value="product" />
					
					<?php if ( ! empty( $search_query ) ) : ?>
						<a href="<?php echo esc_url( remove_query_arg( array( 's', 'paged' ) ) ); ?>" class="sc-catalog-search-clear" aria-label="<?php esc_attr_e( 'Clear search query', 'spicecraft' ); ?>" title="<?php esc_attr_e( 'Clear search', 'spicecraft' ); ?>">
							&times;
						</a>
					<?php endif; ?>

					<button type="submit" class="sc-btn sc-btn--primary sc-catalog-search-btn">
						<?php esc_html_e( 'Search', 'spicecraft' ); ?>
					</button>
				</div>

				<!-- Live Search Dropdown Suggestions Container -->
				<div class="sc-live-search-dropdown" id="sc-live-search-results" style="display: none;" role="listbox" aria-label="<?php esc_attr_e( 'Search Suggestions', 'spicecraft' ); ?>"></div>
			</form>
		</div>
	</header><!-- .sc-catalog-hero -->

	<!-- 4. Dynamic Category Navigation Pills -->
	<?php
	$categories = get_terms(
		array(
			'taxonomy'   => 'product_cat',
			'hide_empty' => true,
			'exclude'    => get_option( 'default_product_cat' ), // Exclude Uncategorized
		)
	);

	$is_all_active = is_shop() && ! is_product_category() && empty( $current_cat );
	?>
	<nav class="sc-category-nav" aria-label="<?php esc_attr_e( 'Product Categories', 'spicecraft' ); ?>">
		<div class="sc-category-nav__track">
			<a href="<?php echo esc_url( $shop_link ); ?>" class="sc-category-pill <?php echo $is_all_active ? 'is-active' : ''; ?>">
				<span class="sc-category-pill__label"><?php esc_html_e( 'All Products', 'spicecraft' ); ?></span>
			</a>

			<?php if ( ! empty( $categories ) && ! is_wp_error( $categories ) ) : ?>
				<?php foreach ( $categories as $category ) : ?>
					<?php
					$is_current = is_product_category( $category->term_id ) || ( $current_cat === $category->slug );
					$term_link  = get_term_link( $category );
					?>
					<a href="<?php echo esc_url( $term_link ); ?>" class="sc-category-pill <?php echo $is_current ? 'is-active' : ''; ?>">
						<span class="sc-category-pill__label"><?php echo esc_html( $category->name ); ?></span>
						<span class="sc-category-pill__count" aria-label="<?php echo esc_attr( sprintf( __( '%d products', 'spicecraft' ), $category->count ) ); ?>"><?php echo esc_html( $category->count ); ?></span>
					</a>
				<?php endforeach; ?>
			<?php endif; ?>
		</div>
	</nav><!-- .sc-category-nav -->

	<!-- 5. Compact Horizontal Desktop Filter Bar -->
	<div class="sc-desktop-filter-bar" id="sc-desktop-filter-bar">
		<form method="get" action="<?php echo esc_url( $shop_link ); ?>" class="sc-filter-form" id="sc-desktop-filter-form">
			<?php if ( ! empty( $search_query ) ) : ?>
				<input type="hidden" name="s" value="<?php echo esc_attr( $search_query ); ?>" />
				<input type="hidden" name="post_type" value="product" />
			<?php endif; ?>
			<?php if ( ! empty( $current_orderby ) ) : ?>
				<input type="hidden" name="orderby" value="<?php echo esc_attr( $current_orderby ); ?>" />
			<?php endif; ?>

			<div class="sc-filter-group">
				<!-- Category Filter -->
				<label for="sc-filter-cat" class="screen-reader-text"><?php esc_html_e( 'Filter by Category', 'spicecraft' ); ?></label>
				<select name="product_cat" id="sc-filter-cat" class="sc-filter-select">
					<option value=""><?php esc_html_e( 'All Categories', 'spicecraft' ); ?></option>
					<?php if ( ! empty( $categories ) && ! is_wp_error( $categories ) ) : ?>
						<?php foreach ( $categories as $cat ) : ?>
							<option value="<?php echo esc_attr( $cat->slug ); ?>" <?php selected( $current_cat, $cat->slug ); ?>>
								<?php echo esc_html( $cat->name ); ?> (<?php echo esc_html( $cat->count ); ?>)
							</option>
						<?php endforeach; ?>
					<?php endif; ?>
				</select>
			</div>

			<?php if ( ! empty( $available_packs ) ) : ?>
				<div class="sc-filter-group">
					<!-- Pack Size Filter -->
					<label for="sc-filter-pack" class="screen-reader-text"><?php esc_html_e( 'Filter by Pack Size', 'spicecraft' ); ?></label>
					<select name="pack_size" id="sc-filter-pack" class="sc-filter-select">
						<option value=""><?php esc_html_e( 'All Pack Sizes', 'spicecraft' ); ?></option>
						<?php foreach ( $available_packs as $pack_opt ) : ?>
							<option value="<?php echo esc_attr( $pack_opt ); ?>" <?php selected( $current_pack, $pack_opt ); ?>>
								<?php echo esc_html( $pack_opt ); ?>
							</option>
						<?php endforeach; ?>
					</select>
				</div>
			<?php endif; ?>

			<div class="sc-filter-group">
				<!-- Rating Filter -->
				<label for="sc-filter-rating" class="screen-reader-text"><?php esc_html_e( 'Filter by Rating', 'spicecraft' ); ?></label>
				<select name="rating" id="sc-filter-rating" class="sc-filter-select">
					<option value=""><?php esc_html_e( 'All Ratings', 'spicecraft' ); ?></option>
					<option value="4" <?php selected( $current_rating, 4 ); ?>><?php esc_html_e( '4★ & Above', 'spicecraft' ); ?></option>
					<option value="3" <?php selected( $current_rating, 3 ); ?>><?php esc_html_e( '3★ & Above', 'spicecraft' ); ?></option>
				</select>
			</div>

			<noscript>
				<button type="submit" class="sc-btn sc-btn--secondary sc-btn--sm"><?php esc_html_e( 'Apply', 'spicecraft' ); ?></button>
			</noscript>
		</form>
	</div>

	<!-- 6. Active Filter Chips -->
	<?php if ( ! empty( $active_filters ) ) : ?>
		<div class="sc-active-filter-chips" aria-label="<?php esc_attr_e( 'Active Filters', 'spicecraft' ); ?>">
			<span class="sc-chips-label"><?php esc_html_e( 'Active Filters:', 'spicecraft' ); ?></span>
			<div class="sc-chips-list">
				<?php foreach ( $active_filters as $chip ) : ?>
					<a href="<?php echo esc_url( $chip['remove_url'] ); ?>" class="sc-filter-chip" aria-label="<?php echo esc_attr( sprintf( __( 'Remove filter %s', 'spicecraft' ), $chip['label'] ) ); ?>">
						<span><?php echo esc_html( $chip['label'] ); ?></span>
						<span class="sc-chip-remove" aria-hidden="true">&times;</span>
					</a>
				<?php endforeach; ?>
				<a href="<?php echo esc_url( $shop_link ); ?>" class="sc-filter-clear-all">
					<?php esc_html_e( 'Clear All', 'spicecraft' ); ?>
				</a>
			</div>
		</div>
	<?php endif; ?>

	<!-- 7. Filter Foundation & Catalog Controls -->
	<div class="sc-catalog-controls">
		<div class="sc-catalog-controls__count">
			<?php
			if ( ! empty( $active_filters ) && wc_get_loop_prop( 'total' ) ) {
				printf(
					/* translators: %d: product count */
					esc_html( _n( '%d Product Found', '%d Products Found', wc_get_loop_prop( 'total' ), 'spicecraft' ) ),
					absint( wc_get_loop_prop( 'total' ) )
				);
			} else {
				woocommerce_result_count();
			}
			?>
		</div>

		<div class="sc-catalog-controls__actions">
			<!-- Mobile Filter Toggle Button (Opens Off-Canvas Drawer) -->
			<button type="button" class="sc-filter-toggle sc-btn sc-btn--outline" aria-expanded="false" aria-controls="sc-filter-drawer" id="sc-mobile-filter-btn">
				<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
					<line x1="4" y1="21" x2="4" y2="14"></line>
					<line x1="4" y1="10" x2="4" y2="3"></line>
					<line x1="12" y1="21" x2="12" y2="12"></line>
					<line x1="12" y1="8" x2="12" y2="3"></line>
					<line x1="20" y1="21" x2="20" y2="16"></line>
					<line x1="20" y1="12" x2="20" y2="3"></line>
					<line x1="1" y1="14" x2="7" y2="14"></line>
					<line x1="9" y1="8" x2="15" y2="8"></line>
					<line x1="17" y1="16" x2="23" y2="16"></line>
				</svg>
				<span><?php esc_html_e( 'Filters', 'spicecraft' ); ?></span>
				<?php if ( ! empty( $active_filters ) ) : ?>
					<span class="sc-badge-count"><?php echo esc_html( count( $active_filters ) ); ?></span>
				<?php endif; ?>
			</button>

			<div class="sc-catalog-controls__ordering">
				<?php woocommerce_catalog_ordering(); ?>
			</div>
		</div>
	</div><!-- .sc-catalog-controls -->

	<!-- 8. Mobile Off-Canvas Filter Drawer & Backdrop -->
	<div id="sc-filter-drawer-backdrop" class="sc-drawer-backdrop" aria-hidden="true"></div>
	<aside id="sc-filter-drawer" class="sc-filter-drawer" aria-labelledby="sc-filter-drawer-title" aria-modal="true" role="dialog">
		<div class="sc-drawer-header">
			<h2 id="sc-filter-drawer-title" class="sc-drawer-title"><?php esc_html_e( 'Filter Catalog', 'spicecraft' ); ?></h2>
			<button type="button" class="sc-drawer-close" id="sc-close-filter-drawer" aria-label="<?php esc_attr_e( 'Close filters', 'spicecraft' ); ?>">
				&times;
			</button>
		</div>

		<form method="get" action="<?php echo esc_url( $shop_link ); ?>" class="sc-drawer-form" id="sc-mobile-filter-form">
			<?php if ( ! empty( $search_query ) ) : ?>
				<input type="hidden" name="s" value="<?php echo esc_attr( $search_query ); ?>" />
				<input type="hidden" name="post_type" value="product" />
			<?php endif; ?>
			<?php if ( ! empty( $current_orderby ) ) : ?>
				<input type="hidden" name="orderby" value="<?php echo esc_attr( $current_orderby ); ?>" />
			<?php endif; ?>

			<div class="sc-drawer-body">
				<!-- Category Section -->
				<div class="sc-drawer-section">
					<h3 class="sc-drawer-section-title"><?php esc_html_e( 'Product Category', 'spicecraft' ); ?></h3>
					<div class="sc-drawer-options">
						<label class="sc-radio-label">
							<input type="radio" name="product_cat" value="" <?php checked( empty( $current_cat ) ); ?> />
							<span><?php esc_html_e( 'All Categories', 'spicecraft' ); ?></span>
						</label>
						<?php if ( ! empty( $categories ) && ! is_wp_error( $categories ) ) : ?>
							<?php foreach ( $categories as $cat ) : ?>
								<label class="sc-radio-label">
									<input type="radio" name="product_cat" value="<?php echo esc_attr( $cat->slug ); ?>" <?php checked( $current_cat, $cat->slug ); ?> />
									<span><?php echo esc_html( $cat->name ); ?> (<?php echo esc_html( $cat->count ); ?>)</span>
								</label>
							<?php endforeach; ?>
						<?php endif; ?>
					</div>
				</div>

				<!-- Pack Size Section -->
				<?php if ( ! empty( $available_packs ) ) : ?>
					<div class="sc-drawer-section">
						<h3 class="sc-drawer-section-title"><?php esc_html_e( 'Pack Size', 'spicecraft' ); ?></h3>
						<div class="sc-drawer-options">
							<label class="sc-radio-label">
								<input type="radio" name="pack_size" value="" <?php checked( empty( $current_pack ) ); ?> />
								<span><?php esc_html_e( 'All Sizes', 'spicecraft' ); ?></span>
							</label>
							<?php foreach ( $available_packs as $p_size ) : ?>
								<label class="sc-radio-label">
									<input type="radio" name="pack_size" value="<?php echo esc_attr( $p_size ); ?>" <?php checked( $current_pack, $p_size ); ?> />
									<span><?php echo esc_html( $p_size ); ?></span>
								</label>
							<?php endforeach; ?>
						</div>
					</div>
				<?php endif; ?>

				<!-- Rating Section -->
				<div class="sc-drawer-section">
					<h3 class="sc-drawer-section-title"><?php esc_html_e( 'Customer Rating', 'spicecraft' ); ?></h3>
					<div class="sc-drawer-options">
						<label class="sc-radio-label">
							<input type="radio" name="rating" value="" <?php checked( empty( $current_rating ) ); ?> />
							<span><?php esc_html_e( 'Any Rating', 'spicecraft' ); ?></span>
						</label>
						<label class="sc-radio-label">
							<input type="radio" name="rating" value="4" <?php checked( $current_rating, 4 ); ?> />
							<span>★★★★☆ <?php esc_html_e( '4 Stars & Above', 'spicecraft' ); ?></span>
						</label>
						<label class="sc-radio-label">
							<input type="radio" name="rating" value="3" <?php checked( $current_rating, 3 ); ?> />
							<span>★★★☆☆ <?php esc_html_e( '3 Stars & Above', 'spicecraft' ); ?></span>
						</label>
					</div>
				</div>
			</div>

			<div class="sc-drawer-footer">
				<a href="<?php echo esc_url( $shop_link ); ?>" class="sc-btn sc-btn--outline sc-drawer-clear">
					<?php esc_html_e( 'Clear All', 'spicecraft' ); ?>
				</a>
				<button type="submit" class="sc-btn sc-btn--primary sc-drawer-apply">
					<?php esc_html_e( 'Show Products', 'spicecraft' ); ?>
				</button>
			</div>
		</form>
	</aside>

	<!-- 9. Main Product Loop or Empty State -->
	<?php if ( woocommerce_product_loop() ) : ?>

		<?php
		/**
		 * Hook: woocommerce_before_shop_loop.
		 */
		do_action( 'woocommerce_before_shop_loop' );

		woocommerce_product_loop_start();

		if ( wc_get_loop_prop( 'total' ) ) {
			while ( have_posts() ) {
				the_post();

				/**
				 * Hook: woocommerce_shop_loop.
				 */
				do_action( 'woocommerce_shop_loop' );

				wc_get_template_part( 'content', 'product' );
			}
		}

		woocommerce_product_loop_end();

		/**
		 * Hook: woocommerce_after_shop_loop.
		 *
		 * @hooked woocommerce_pagination - 10
		 */
		do_action( 'woocommerce_after_shop_loop' );
		?>

	<?php else : ?>

		<div class="sc-catalog-empty">
			<div class="sc-catalog-empty__icon" aria-hidden="true">
				<svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
					<circle cx="11" cy="11" r="8"></circle>
					<line x1="21" y1="21" x2="16.65" y2="16.65"></line>
					<line x1="8" y1="11" x2="14" y2="11"></line>
				</svg>
			</div>
			<?php if ( ! empty( $search_query ) ) : ?>
				<h2 class="sc-catalog-empty__title">
					<?php
					/* translators: %s: search query */
					printf( esc_html__( 'No products found matching &ldquo;%s&rdquo;', 'spicecraft' ), esc_html( $search_query ) );
					?>
				</h2>
				<p class="sc-catalog-empty__message">
					<?php esc_html_e( 'We couldn\'t find any spices matching your search keyword. Try another keyword, clearing your filters, or browsing our full range.', 'spicecraft' ); ?>
				</p>
			<?php else : ?>
				<h2 class="sc-catalog-empty__title"><?php esc_html_e( 'No spices match this filter combination', 'spicecraft' ); ?></h2>
				<p class="sc-catalog-empty__message">
					<?php esc_html_e( 'There are currently no products matching your selected category, pack size, or rating filters. Try broadening your filter selection.', 'spicecraft' ); ?>
				</p>
			<?php endif; ?>

			<div class="sc-catalog-empty__actions" style="display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap; margin-top: 1.5rem;">
				<?php if ( ! empty( $active_filters ) ) : ?>
					<a href="<?php echo esc_url( $shop_link ); ?>" class="sc-btn sc-btn--outline">
						<?php esc_html_e( 'Clear All Filters', 'spicecraft' ); ?>
					</a>
				<?php endif; ?>
				<a href="<?php echo esc_url( $shop_link ); ?>" class="sc-btn sc-btn--primary">
					<?php esc_html_e( 'Browse Complete Range', 'spicecraft' ); ?> &rarr;
				</a>
			</div>
		</div>

	<?php endif; ?>

</div><!-- .sc-catalog-experience -->

<?php
/**
 * Hook: woocommerce_after_main_content.
 *
 * @hooked spicecraft_woocommerce_wrapper_after - 10
 */
do_action( 'woocommerce_after_main_content' );

get_footer( 'shop' );
