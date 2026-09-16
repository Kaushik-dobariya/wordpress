<?php
/**
 * SpiceCraft - Advanced Product Discovery, Search, Filtering & Engagement Architecture
 *
 * Provides:
 * - Enhanced catalog search (Product Name, SKU, Categories, Tags, Content)
 * - Dynamic URL-driven catalog filters (Categories, Pack Size, Rating, Tags)
 * - Clean catalog sorting (Name A-Z, Name Z-A, Newest, Highest Rated - no price sorting)
 * - AJAX endpoints for client-side Favourites & Recently Viewed card rendering
 * - Live Search suggestion endpoint
 * - Favourites shortcode and page registration
 *
 * @package SpiceCraft
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * ============================================================================
 * 1. ENHANCED PRODUCT SEARCH (SKU + TERMS + TITLES)
 * ============================================================================
 */

/**
 * Extend WordPress search query to match WooCommerce product SKU and taxonomy terms.
 *
 * @param string   $search Search SQL condition.
 * @param WP_Query $query  The current WP_Query instance.
 * @return string Modified search SQL.
 */
function spicecraft_product_search_where( $search, $query ) {
	global $wpdb;

	if ( is_admin() || ! $query->is_search() ) {
		return $search;
	}

	$post_type = $query->get( 'post_type' );
	$is_product_search = ( 'product' === $post_type || is_post_type_archive( 'product' ) || ( function_exists( 'is_shop' ) && is_shop() ) );

	if ( ! $is_product_search ) {
		return $search;
	}

	$search_term = $query->get( 's' );
	if ( empty( $search_term ) ) {
		return $search;
	}

	$like = '%' . $wpdb->esc_like( $search_term ) . '%';

	// 1. Find product IDs matching SKU
	$sku_ids = $wpdb->get_col(
		$wpdb->prepare(
			"SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = '_sku' AND meta_value LIKE %s",
			$like
		)
	);

	// 2. Find product IDs matching Category or Tag names
	$term_ids = $wpdb->get_col(
		$wpdb->prepare(
			"SELECT tr.object_id FROM {$wpdb->term_relationships} tr
			 INNER JOIN {$wpdb->term_taxonomy} tt ON tr.term_taxonomy_id = tt.term_taxonomy_id
			 INNER JOIN {$wpdb->terms} t ON tt.term_id = t.term_id
			 WHERE tt.taxonomy IN ('product_cat', 'product_tag') AND t.name LIKE %s",
			$like
		)
	);

	$all_match_ids = array_unique( array_filter( array_merge( (array) $sku_ids, (array) $term_ids ) ) );

	if ( ! empty( $all_match_ids ) ) {
		$id_list = implode( ',', array_map( 'absint', $all_match_ids ) );
		// Extend the search clause so matched products are included in results
		$trimmed_search = preg_replace( '/^\s*AND\s*/i', '', $search );
		if ( ! empty( $trimmed_search ) ) {
			$search = " AND ( ({$trimmed_search}) OR ({$wpdb->posts}.ID IN ({$id_list})) ) ";
		} else {
			$search = " AND ({$wpdb->posts}.ID IN ({$id_list})) ";
		}
	}

	return $search;
}
add_filter( 'posts_search', 'spicecraft_product_search_where', 20, 2 );

/**
 * ============================================================================
 * 2. DYNAMIC CATALOG QUERY FILTERS (URL STATE DRIVEN)
 * ============================================================================
 */

/**
 * Filter WooCommerce catalog products based on URL parameters.
 * Supports:
 * - product_cat (Category slug)
 * - pack_size (Pack size value)
 * - rating (Minimum average rating e.g. 3, 4)
 * - tag (Product tag slug)
 *
 * @param WC_Query $query WooCommerce product query.
 */
function spicecraft_apply_catalog_filters( $query ) {
	if ( is_admin() || ! $query->is_main_query() ) {
		return;
	}

	$meta_query = (array) $query->get( 'meta_query' );
	$tax_query  = (array) $query->get( 'tax_query' );

	// 1. Pack Size Filter
	if ( ! empty( $_GET['pack_size'] ) ) {
		$pack_size = sanitize_text_field( wp_unslash( $_GET['pack_size'] ) );

		// Match either product attribute or FMCG _sc_pack_sizes meta
		$meta_query[] = array(
			'relation' => 'OR',
			array(
				'key'     => '_sc_pack_sizes',
				'value'   => $pack_size,
				'compare' => 'LIKE',
			),
			array(
				'key'     => '_product_attributes',
				'value'   => $pack_size,
				'compare' => 'LIKE',
			),
		);
	}

	// 2. Minimum Rating Filter
	if ( ! empty( $_GET['rating'] ) ) {
		$min_rating = floatval( $_GET['rating'] );
		if ( $min_rating > 0 ) {
			$meta_query[] = array(
				'key'     => '_wc_average_rating',
				'value'   => $min_rating,
				'compare' => '>=',
				'type'    => 'DECIMAL',
			);
		}
	}

	// 3. Category Filter (via URL query on shop page or archives)
	if ( ! empty( $_GET['product_cat'] ) && ! is_product_category() ) {
		$cat_slug = sanitize_title( wp_unslash( $_GET['product_cat'] ) );
		$tax_query[] = array(
			'taxonomy' => 'product_cat',
			'field'    => 'slug',
			'terms'    => $cat_slug,
		);
	}

	// 4. Product Tag Filter
	if ( ! empty( $_GET['tag'] ) && ! is_product_tag() ) {
		$tag_slug = sanitize_title( wp_unslash( $_GET['tag'] ) );
		$tax_query[] = array(
			'taxonomy' => 'product_tag',
			'field'    => 'slug',
			'terms'    => $tag_slug,
		);
	}

	$query->set( 'meta_query', $meta_query );
	$query->set( 'tax_query', $tax_query );
}
add_action( 'woocommerce_product_query', 'spicecraft_apply_catalog_filters' );

/**
 * ============================================================================
 * 3. CATALOG SORTING (NO PRICE SORTING)
 * ============================================================================
 */

/**
 * Remove irrelevant transactional price sorting options and add Name sorting.
 *
 * @param array $options Default WooCommerce sorting options.
 * @return array Filtered sorting options.
 */
function spicecraft_catalog_sorting_options( $options ) {
	// Remove price sorting (Not an e-commerce purchasing store)
	unset( $options['price'] );
	unset( $options['price-desc'] );

	// Custom relevant sorting options
	$custom_options = array(
		'menu_order' => __( 'Default Sorting', 'spicecraft' ),
		'date'       => __( 'Newest First', 'spicecraft' ),
		'title'      => __( 'Name: A to Z', 'spicecraft' ),
		'title-desc' => __( 'Name: Z to A', 'spicecraft' ),
		'rating'     => __( 'Highest Rated', 'spicecraft' ),
	);

	return $custom_options;
}
add_filter( 'woocommerce_catalog_orderby', 'spicecraft_catalog_sorting_options', 99 );
add_filter( 'woocommerce_default_catalog_orderby_options', 'spicecraft_catalog_sorting_options', 99 );

/**
 * Handle custom orderby arguments for Name A-Z and Z-A.
 *
 * @param array  $args    Query arguments.
 * @param string $orderby Ordering key.
 * @param string $order   Sort order.
 * @return array Modified query arguments.
 */
function spicecraft_catalog_ordering_args( $args, $orderby, $order ) {
	if ( 'title' === $orderby ) {
		$args['orderby'] = 'title';
		$args['order']   = 'ASC';
	} elseif ( 'title-desc' === $orderby ) {
		$args['orderby'] = 'title';
		$args['order']   = 'DESC';
	}
	return $args;
}
add_filter( 'woocommerce_get_catalog_ordering_args', 'spicecraft_catalog_ordering_args', 20, 3 );

/**
 * ============================================================================
 * 4. CATALOG FILTER DATA HELPERS
 * ============================================================================
 */

/**
 * Retrieve all unique, legitimate pack sizes present across published products.
 * Guarantees no fabricated or empty filter options.
 *
 * @return array Clean array of pack size strings.
 */
function spicecraft_get_catalog_available_pack_sizes() {
	static $cached_sizes = null;
	if ( null !== $cached_sizes ) {
		return $cached_sizes;
	}

	$sizes = array();
	$products = wc_get_products(
		array(
			'status' => 'publish',
			'limit'  => -1,
		)
	);

	foreach ( $products as $product ) {
		$prod_sizes = spicecraft_get_product_pack_sizes( $product );
		if ( ! empty( $prod_sizes ) ) {
			$sizes = array_merge( $sizes, $prod_sizes );
		}
	}

	$sizes = array_values( array_unique( array_filter( array_map( 'trim', $sizes ) ) ) );

	// Sort sizes logically (small to large)
	usort(
		$sizes,
		function ( $a, $b ) {
			// Extract numeric value if present
			preg_match( '/(\d+)/', $a, $mA );
			preg_match( '/(\d+)/', $b, $mB );
			$valA = ! empty( $mA[1] ) ? intval( $mA[1] ) : 0;
			$valB = ! empty( $mB[1] ) ? intval( $mB[1] ) : 0;
			// Convert kg to grams for sorting comparison
			if ( false !== stripos( $a, 'kg' ) ) {
				$valA *= 1000;
			}
			if ( false !== stripos( $b, 'kg' ) ) {
				$valB *= 1000;
			}
			return $valA <=> $valB;
		}
	);

	$cached_sizes = $sizes;
	return $sizes;
}

/**
 * Retrieve active filter chips with removal links.
 *
 * @return array List of active chips ['key', 'label', 'value', 'remove_url'].
 */
function spicecraft_get_active_filters() {
	$active = array();
	$current_url = remove_query_arg( 'paged' );

	// 1. Category Filter
	if ( ! empty( $_GET['product_cat'] ) && ! is_product_category() ) {
		$cat_slug = sanitize_title( wp_unslash( $_GET['product_cat'] ) );
		$term     = get_term_by( 'slug', $cat_slug, 'product_cat' );
		$label    = $term ? $term->name : $cat_slug;
		$active[] = array(
			'key'        => 'product_cat',
			'label'      => sprintf( __( 'Category: %s', 'spicecraft' ), $label ),
			'value'      => $cat_slug,
			'remove_url' => remove_query_arg( 'product_cat', $current_url ),
		);
	}

	// 2. Pack Size Filter
	if ( ! empty( $_GET['pack_size'] ) ) {
		$size = sanitize_text_field( wp_unslash( $_GET['pack_size'] ) );
		$active[] = array(
			'key'        => 'pack_size',
			'label'      => sprintf( __( 'Pack: %s', 'spicecraft' ), $size ),
			'value'      => $size,
			'remove_url' => remove_query_arg( 'pack_size', $current_url ),
		);
	}

	// 3. Rating Filter
	if ( ! empty( $_GET['rating'] ) ) {
		$rating = intval( $_GET['rating'] );
		$active[] = array(
			'key'        => 'rating',
			'label'      => sprintf( __( '%d★ & Above', 'spicecraft' ), $rating ),
			'value'      => $rating,
			'remove_url' => remove_query_arg( 'rating', $current_url ),
		);
	}

	// 4. Tag Filter
	if ( ! empty( $_GET['tag'] ) && ! is_product_tag() ) {
		$tag_slug = sanitize_title( wp_unslash( $_GET['tag'] ) );
		$term     = get_term_by( 'slug', $tag_slug, 'product_tag' );
		$label    = $term ? $term->name : $tag_slug;
		$active[] = array(
			'key'        => 'tag',
			'label'      => sprintf( __( 'Tag: %s', 'spicecraft' ), $label ),
			'value'      => $tag_slug,
			'remove_url' => remove_query_arg( 'tag', $current_url ),
		);
	}

	// 5. Search Filter
	if ( ! empty( $_GET['s'] ) ) {
		$query_str = sanitize_text_field( wp_unslash( $_GET['s'] ) );
		$active[]  = array(
			'key'        => 's',
			'label'      => sprintf( __( 'Search: &ldquo;%s&rdquo;', 'spicecraft' ), $query_str ),
			'value'      => $query_str,
			'remove_url' => remove_query_arg( 's', $current_url ),
		);
	}

	return $active;
}

/**
 * ============================================================================
 * 5. AJAX ENDPOINTS: FAVOURITES & RECENTLY VIEWED & LIVE SEARCH
 * ============================================================================
 */

/**
 * AJAX handler: Render product cards for client-side stored IDs (Favourites / Recently Viewed).
 * Ensures exact component reuse of woocommerce/content-product.php.
 */
function spicecraft_ajax_get_product_cards() {
	check_ajax_referer( 'spicecraft_frontend_nonce', 'nonce' );

	$raw_ids = isset( $_POST['product_ids'] ) ? (array) $_POST['product_ids'] : array();
	$product_ids = array_values( array_unique( array_filter( array_map( 'absint', $raw_ids ) ) ) );

	if ( empty( $product_ids ) ) {
		wp_send_json_success(
			array(
				'html'      => '',
				'valid_ids' => array(),
				'count'     => 0,
			)
		);
	}

	// Query published products matching IDs
	$query_args = array(
		'post_type'      => 'product',
		'post_status'    => 'publish',
		'post__in'       => $product_ids,
		'posts_per_page' => count( $product_ids ),
		'orderby'        => 'post__in',
	);

	$loop = new WP_Query( $query_args );
	$valid_ids = array();

	ob_start();
	if ( $loop->have_posts() ) {
		while ( $loop->have_posts() ) {
			$loop->the_post();
			$valid_ids[] = get_the_ID();
			wc_get_template_part( 'content', 'product' );
		}
		wp_reset_postdata();
	}
	$html = ob_get_clean();

	wp_send_json_success(
		array(
			'html'      => $html,
			'valid_ids' => $valid_ids,
			'count'     => count( $valid_ids ),
		)
	);
}
add_action( 'wp_ajax_spicecraft_get_product_cards', 'spicecraft_ajax_get_product_cards' );
add_action( 'wp_ajax_nopriv_spicecraft_get_product_cards', 'spicecraft_ajax_get_product_cards' );

/**
 * AJAX handler: Lightweight Live Search suggestions.
 * Returns 5-8 matching products with thumbnail, title, category, and URL.
 */
function spicecraft_ajax_live_search() {
	check_ajax_referer( 'spicecraft_frontend_nonce', 'nonce' );

	$query = isset( $_POST['query'] ) ? sanitize_text_field( wp_unslash( $_POST['query'] ) ) : '';
	if ( strlen( $query ) < 2 ) {
		wp_send_json_success( array( 'results' => array() ) );
	}

	$search_query = new WP_Query(
		array(
			'post_type'      => 'product',
			'post_status'    => 'publish',
			's'              => $query,
			'posts_per_page' => 6,
			'no_found_rows'  => true,
		)
	);

	$results = array();
	if ( $search_query->have_posts() ) {
		while ( $search_query->have_posts() ) {
			$search_query->the_post();
			$product_id  = get_the_ID();
			$product     = wc_get_product( $product_id );
			$thumb_url   = get_the_post_thumbnail_url( $product_id, 'thumbnail' );
			$primary_cat = spicecraft_get_product_primary_category( $product_id );

			$results[] = array(
				'id'        => $product_id,
				'title'     => get_the_title(),
				'permalink' => get_permalink(),
				'thumb'     => $thumb_url ? $thumb_url : '',
				'category'  => $primary_cat ? $primary_cat : '',
				'sku'       => $product ? $product->get_sku() : '',
			);
		}
		wp_reset_postdata();
	}

	wp_send_json_success( array( 'results' => $results ) );
}
add_action( 'wp_ajax_spicecraft_live_search', 'spicecraft_ajax_live_search' );
add_action( 'wp_ajax_nopriv_spicecraft_live_search', 'spicecraft_ajax_live_search' );

/**
 * ============================================================================
 * 6. FAVOURITES SHORTCODE & PAGE TEMPLATE INTEGRATION
 * ============================================================================
 */

/**
 * Locate or build the URL to the Favourites page.
 *
 * @return string Permalink to Favourites page.
 */
function spicecraft_get_favourites_url() {
	$fav_page = get_page_by_path( 'favourites' );
	if ( $fav_page && 'publish' === $fav_page->post_status ) {
		return get_permalink( $fav_page->ID );
	}
	return home_url( '/favourites/' );
}

/**
 * Render the Favourites catalog view container.
 * Populated client-side via localStorage.
 *
 * @return string HTML output.
 */
function spicecraft_favourites_shortcode() {
	$shop_link = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/shop/' );

	ob_start();
	?>
	<div class="sc-favourites-experience" id="sc-favourites-root">
		<!-- Header -->
		<header class="sc-catalog-hero sc-favourites-hero">
			<div class="sc-catalog-hero__content">
				<span class="sc-catalog-hero__eyebrow"><?php esc_html_e( 'Saved Selections', 'spicecraft' ); ?></span>
				<h1 class="sc-catalog-hero__title"><?php esc_html_e( 'Your Favourites', 'spicecraft' ); ?></h1>
				<p class="sc-catalog-hero__description">
					<?php esc_html_e( 'Review your saved whole spices, powders, and artisanal masala blends. Enquire directly for bulk orders or private labeling.', 'spicecraft' ); ?>
				</p>
			</div>
		</header>

		<!-- Dynamic Loading Spinner -->
		<div class="sc-favourites-loading" id="sc-favourites-loading" aria-live="polite" style="display: flex; justify-content: center; padding: 4rem 0;">
			<div class="sc-spinner" aria-hidden="true"></div>
			<span class="screen-reader-text"><?php esc_html_e( 'Loading saved favourites...', 'spicecraft' ); ?></span>
		</div>

		<!-- Products Grid (Injected via JS) -->
		<div class="woocommerce" id="sc-favourites-grid-wrap" style="display: none;">
			<div class="sc-favourites-meta-bar">
				<span class="sc-favourites-count" id="sc-favourites-count-label"></span>
				<button type="button" class="sc-btn sc-btn--outline sc-btn--sm" id="sc-clear-favourites-btn">
					<?php esc_html_e( 'Clear All Favourites', 'spicecraft' ); ?>
				</button>
			</div>
			<ul class="products columns-4 sc-products-grid" id="sc-favourites-grid"></ul>
		</div>

		<!-- Empty State (Shown when no items saved) -->
		<div class="sc-catalog-empty sc-favourites-empty" id="sc-favourites-empty" style="display: none;">
			<div class="sc-catalog-empty__icon" aria-hidden="true">
				<svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
					<path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path>
				</svg>
			</div>
			<h2 class="sc-catalog-empty__title"><?php esc_html_e( 'You haven\'t saved any products yet', 'spicecraft' ); ?></h2>
			<p class="sc-catalog-empty__message">
				<?php esc_html_e( 'Browse our catalog of authentic spices, cold-milled powders, and master blends, and click the heart icon to save products here.', 'spicecraft' ); ?>
			</p>
			<a href="<?php echo esc_url( $shop_link ); ?>" class="sc-btn sc-btn--primary">
				<?php esc_html_e( 'Explore Product Range', 'spicecraft' ); ?> &rarr;
			</a>
		</div>
	</div>
	<?php
	return ob_get_clean();
}
add_shortcode( 'spicecraft_favourites', 'spicecraft_favourites_shortcode' );

/**
 * Auto-create the Favourites page if it does not already exist.
 */
function spicecraft_ensure_favourites_page() {
	if ( ! get_page_by_path( 'favourites' ) ) {
		wp_insert_post(
			array(
				'post_title'     => __( 'Favourites', 'spicecraft' ),
				'post_name'      => 'favourites',
				'post_status'    => 'publish',
				'post_type'      => 'page',
				'post_content'   => '[spicecraft_favourites]',
				'comment_status' => 'closed',
			)
		);
	}
}
add_action( 'init', 'spicecraft_ensure_favourites_page', 20 );
