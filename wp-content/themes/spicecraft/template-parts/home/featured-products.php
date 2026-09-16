<?php
/**
 * Homepage Template Part: Featured Products Showcase
 * Semantic ID: #featured-products
 *
 * Consumes WooCommerce products and reuses the Phase 1 catalog product card component.
 * Suppresses itself gracefully if no matching products exist.
 *
 * @package SpiceCraft
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WooCommerce' ) ) {
	return;
}

$fp_settings = function_exists( 'spicecraft_get_homepage_section' )
	? spicecraft_get_homepage_section( 'featured_products' )
	: array();

$eyebrow      = ! empty( $fp_settings['eyebrow'] ) ? $fp_settings['eyebrow'] : '';
$heading      = ! empty( $fp_settings['heading'] ) ? $fp_settings['heading'] : '';
$description  = ! empty( $fp_settings['description'] ) ? $fp_settings['description'] : '';
$source       = ! empty( $fp_settings['source'] ) ? $fp_settings['source'] : 'featured';
$limit        = ! empty( $fp_settings['limit'] ) ? absint( $fp_settings['limit'] ) : 8;
$selected_ids = ! empty( $fp_settings['selected_ids'] ) ? array_map( 'absint', (array) $fp_settings['selected_ids'] ) : array();
$cta_label    = ! empty( $fp_settings['cta_label'] ) ? $fp_settings['cta_label'] : __( 'Explore Full Range', 'spicecraft' );
$cta_url      = ! empty( $fp_settings['cta_url'] ) ? $fp_settings['cta_url'] : ( function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/shop/' ) );

$query_args = array(
	'post_type'      => 'product',
	'post_status'    => 'publish',
	'posts_per_page' => $limit,
	'no_found_rows'  => true,
);

if ( 'manual' === $source && ! empty( $selected_ids ) ) {
	$query_args['post__in'] = $selected_ids;
	$query_args['orderby']  = 'post__in';
} elseif ( 'featured' === $source ) {
	$query_args['tax_query'] = array(
		array(
			'taxonomy' => 'product_visibility',
			'field'    => 'name',
			'terms'    => 'featured',
		),
	);
} else {
	$query_args['orderby'] = 'date';
	$query_args['order']   = 'DESC';
}

$products_query = new WP_Query( $query_args );

// Fallback: If featured products source was empty, fallback to latest products
if ( ! $products_query->have_posts() && 'featured' === $source ) {
	unset( $query_args['tax_query'] );
	$query_args['orderby'] = 'date';
	$query_args['order']   = 'DESC';
	$products_query = new WP_Query( $query_args );
}

if ( ! $products_query->have_posts() ) {
	wp_reset_postdata();
	return;
}
?>

<section id="featured-products" class="sc-home-section sc-home-featured-products" aria-labelledby="sec-heading-featured-products">
	<div class="sc-container">
		<div class="sc-section-header-split">
			<div class="sc-section-header-split__content">
				<?php if ( ! empty( $eyebrow ) ) : ?>
					<p class="sc-eyebrow"><?php echo esc_html( $eyebrow ); ?></p>
				<?php endif; ?>

				<?php if ( ! empty( $heading ) ) : ?>
					<h2 id="sec-heading-featured-products" class="sc-section-title"><?php echo esc_html( $heading ); ?></h2>
				<?php else : ?>
					<h2 id="sec-heading-featured-products" class="sc-section-title"><?php esc_html_e( 'Featured Spice Catalog', 'spicecraft' ); ?></h2>
				<?php endif; ?>

				<?php if ( ! empty( $description ) ) : ?>
					<p class="sc-section-subtitle"><?php echo esc_html( $description ); ?></p>
				<?php endif; ?>
			</div>

			<?php if ( ! empty( $cta_label ) ) : ?>
				<div class="sc-section-header-split__action">
					<a href="<?php echo esc_url( $cta_url ); ?>" class="sc-link-arrow">
						<span><?php echo esc_html( $cta_label ); ?></span>
						<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
					</a>
				</div>
			<?php endif; ?>
		</div>

		<div class="woocommerce columns-4">
			<ul class="products columns-4 sc-products-grid">
				<?php
				while ( $products_query->have_posts() ) :
					$products_query->the_post();
					wc_get_template_part( 'content', 'product' );
				endwhile;
				wp_reset_postdata();
				?>
			</ul>
		</div>
	</div>
</section>
