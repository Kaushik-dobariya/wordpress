<?php
/**
 * SpiceCraft - Custom Product Catalog / Shop Archive Template
 *
 * Overrides WooCommerce's default archive-product.php to deliver a bespoke,
 * prestigious FMCG spices discovery experience. Features an elegant catalog hero,
 * integrated WordPress-compatible product search, dynamic category pills,
 * refined filter foundation, and responsive product grid.
 *
 * @package SpiceCraft
 * @version 8.6.0
 */

defined( 'ABSPATH' ) || exit;

get_header( 'shop' );

/**
 * Hook: woocommerce_before_main_content.
 *
 * @hooked spicecraft_woocommerce_wrapper_before - 10
 */
do_action( 'woocommerce_before_main_content' );
?>

<div class="sc-catalog-experience">

	<!-- 1. Breadcrumbs Navigation -->
	<?php woocommerce_breadcrumb(); ?>

	<!-- 2. Refined Catalog Hero -->
	<header class="sc-catalog-hero">
		<div class="sc-catalog-hero__content">
			<span class="sc-catalog-hero__eyebrow"><?php esc_html_e( 'Direct Manufacturer Catalog', 'spicecraft' ); ?></span>
			
			<h1 class="sc-catalog-hero__title">
				<?php woocommerce_page_title(); ?>
			</h1>

			<div class="sc-catalog-hero__description">
				<?php
				if ( is_product_category() || is_product_tag() ) {
					do_action( 'woocommerce_archive_description' );
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

		<!-- 3. Visually Strong Product Search Field -->
		<div class="sc-catalog-hero__search">
			<form role="search" method="get" class="sc-catalog-search-form" action="<?php echo esc_url( home_url( '/' ) ); ?>">
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
						value="<?php echo get_search_query(); ?>" 
						name="s" 
						autocomplete="off" />
					<input type="hidden" name="post_type" value="product" />
					<button type="submit" class="sc-btn sc-btn--primary sc-catalog-search-btn">
						<?php esc_html_e( 'Search', 'spicecraft' ); ?>
					</button>
				</div>
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

	$is_all_active = is_shop() && ! is_product_category();
	$shop_link     = wc_get_page_permalink( 'shop' );
	?>
	<nav class="sc-category-nav" aria-label="<?php esc_attr_e( 'Product Categories', 'spicecraft' ); ?>">
		<div class="sc-category-nav__track">
			<a href="<?php echo esc_url( $shop_link ); ?>" class="sc-category-pill <?php echo $is_all_active ? 'is-active' : ''; ?>">
				<span class="sc-category-pill__label"><?php esc_html_e( 'All Products', 'spicecraft' ); ?></span>
			</a>

			<?php if ( ! empty( $categories ) && ! is_wp_error( $categories ) ) : ?>
				<?php foreach ( $categories as $category ) : ?>
					<?php
					$is_current = is_product_category( $category->term_id );
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

	<!-- 5. Filter Foundation & Catalog Controls -->
	<div class="sc-catalog-controls">
		<div class="sc-catalog-controls__count">
			<?php woocommerce_result_count(); ?>
		</div>

		<div class="sc-catalog-controls__actions">
			<!-- Mobile Filter Toggle Button (Foundation for future drawer) -->
			<button type="button" class="sc-filter-toggle sc-btn sc-btn--outline" aria-expanded="false" aria-controls="sc-catalog-filters" id="sc-mobile-filter-btn">
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
			</button>

			<div class="sc-catalog-controls__ordering">
				<?php woocommerce_catalog_ordering(); ?>
			</div>
		</div>
	</div><!-- .sc-catalog-controls -->

	<!-- 6. Main Product Loop or Empty State -->
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
			<h2 class="sc-catalog-empty__title"><?php esc_html_e( 'No spices or products found', 'spicecraft' ); ?></h2>
			<p class="sc-catalog-empty__message">
				<?php esc_html_e( 'We couldn\'t find any products matching your selection. Try clearing filters or searching for another keyword.', 'spicecraft' ); ?>
			</p>
			<a href="<?php echo esc_url( $shop_link ); ?>" class="sc-btn sc-btn--primary">
				<?php esc_html_e( 'Browse Complete Range', 'spicecraft' ); ?> &rarr;
			</a>
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
