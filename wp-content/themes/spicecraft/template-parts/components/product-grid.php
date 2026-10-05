<?php
/**
 * SpiceCraft Reusable Component: Dynamic Product Grid
 *
 * Parameters:
 * - heading (string)
 * - eyebrow (string)
 * - description (string)
 * - category (string slug|int ID)
 * - count (int, default: 8)
 * - orderby (string, default: 'date')
 * - order (string, default: 'DESC')
 * - columns (int: 2, 3, 4 - default 4)
 * - featured_only (bool, default: false)
 * - cta_text (string)
 * - cta_url (string)
 * - alignment ('left'|'center' - default 'left')
 *
 * @package SpiceCraft
 * @since 1.5.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WooCommerce' ) ) {
	return;
}

$heading       = ! empty( $args['heading'] ) ? $args['heading'] : '';
$eyebrow       = ! empty( $args['eyebrow'] ) ? $args['eyebrow'] : '';
$description   = ! empty( $args['description'] ) ? $args['description'] : '';
$category      = ! empty( $args['category'] ) ? $args['category'] : '';
$count         = ! empty( $args['count'] ) ? absint( $args['count'] ) : 8;
$orderby       = ! empty( $args['orderby'] ) ? sanitize_key( $args['orderby'] ) : 'date';
$order         = ! empty( $args['order'] ) && 'ASC' === strtoupper( $args['order'] ) ? 'ASC' : 'DESC';
$columns       = ! empty( $args['columns'] ) && in_array( (int) $args['columns'], array( 2, 3, 4 ), true ) ? (int) $args['columns'] : 4;
$featured_only = ! empty( $args['featured_only'] );
$cta_text      = ! empty( $args['cta_text'] ) ? $args['cta_text'] : __( 'Explore Full Catalog', 'spicecraft' );
$cta_url       = ! empty( $args['cta_url'] ) ? $args['cta_url'] : ( function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/shop/' ) );
$alignment     = ! empty( $args['alignment'] ) && 'center' === $args['alignment'] ? 'center' : 'left';

$query_args = array(
	'post_type'      => 'product',
	'post_status'    => 'publish',
	'posts_per_page' => $count,
	'orderby'        => $orderby,
	'order'          => $order,
	'no_found_rows'  => true,
);

$tax_queries = array();

if ( $category ) {
	$field = is_numeric( $category ) ? 'term_id' : 'slug';
	$tax_queries[] = array(
		'taxonomy' => 'product_cat',
		'field'    => $field,
		'terms'    => $category,
	);
}

if ( $featured_only ) {
	$tax_queries[] = array(
		'taxonomy' => 'product_visibility',
		'field'    => 'name',
		'terms'    => 'featured',
	);
}

if ( ! empty( $tax_queries ) ) {
	if ( count( $tax_queries ) > 1 ) {
		$tax_queries['relation'] = 'AND';
	}
	$query_args['tax_query'] = $tax_queries;
}

$products_query = new WP_Query( $query_args );

if ( ! $products_query->have_posts() ) {
	wp_reset_postdata();
	return;
}
?>
<section class="sc-comp-product-grid sc-comp-product-grid--cols-<?php echo esc_attr( $columns ); ?>">
	<div class="sc-container">
		<?php if ( $heading || $eyebrow || $description || $cta_text ) : ?>
			<div class="sc-section-header-split">
				<div class="sc-section-header-split__content sc-section-header-split--<?php echo esc_attr( $alignment ); ?>">
					<?php if ( $eyebrow ) : ?>
						<p class="sc-eyebrow"><?php echo esc_html( $eyebrow ); ?></p>
					<?php endif; ?>
					<?php if ( $heading ) : ?>
						<h2 class="sc-section-title"><?php echo esc_html( $heading ); ?></h2>
					<?php endif; ?>
					<?php if ( $description ) : ?>
						<p class="sc-section-subtitle"><?php echo esc_html( $description ); ?></p>
					<?php endif; ?>
				</div>

				<?php if ( $cta_text && $cta_url && 'left' === $alignment ) : ?>
					<div class="sc-section-header-split__action">
						<a href="<?php echo esc_url( $cta_url ); ?>" class="sc-link-arrow">
							<span><?php echo esc_html( $cta_text ); ?></span>
							<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
						</a>
					</div>
				<?php endif; ?>
			</div>
		<?php endif; ?>

		<div class="woocommerce columns-<?php echo esc_attr( $columns ); ?>">
			<ul class="products columns-<?php echo esc_attr( $columns ); ?> sc-products-grid">
				<?php
				while ( $products_query->have_posts() ) :
					$products_query->the_post();
					wc_get_template_part( 'content', 'product' );
				endwhile;
				wp_reset_postdata();
				?>
			</ul>
		</div>

		<?php if ( $cta_text && $cta_url && 'center' === $alignment ) : ?>
			<div class="sc-product-grid__footer sc-text-center">
				<a href="<?php echo esc_url( $cta_url ); ?>" class="sc-btn sc-btn--primary">
					<?php echo esc_html( $cta_text ); ?>
				</a>
			</div>
		<?php endif; ?>
	</div>
</section>
