<?php
/**
 * Certifications Section: Detail Related Products & Categories
 *
 * Displays WooCommerce spice catalog items explicitly certified under this standard,
 * rendered using the standard SpiceCraft catalog cards in strict inquiry/catalog mode.
 *
 * @package SpiceCraft
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$term    = $args['term'] ?? get_queried_object();
$meta    = $args['meta'] ?? array();
$term_id = $term->term_id ?? 0;

$settings = function_exists( 'spicecraft_get_certification_settings' ) ? spicecraft_get_certification_settings() : array();

$show_products   = ! empty( $settings['show_related_products'] );
$show_categories = ! empty( $settings['show_related_categories'] );

if ( ! $show_products && ! $show_categories ) {
	return;
}

// 1. Fetch related products
$product_ids = array();
if ( $show_products && function_exists( 'spicecraft_get_certification_related_products' ) ) {
	$product_ids = spicecraft_get_certification_related_products( $term_id, 8 );
}

// 2. Fetch related categories
$category_ids = array();
if ( $show_categories && ! empty( $meta['related_categories'] ) && is_array( $meta['related_categories'] ) ) {
	$category_ids = array_filter( array_map( 'absint', $meta['related_categories'] ) );
}

if ( empty( $product_ids ) && empty( $category_ids ) ) {
	return;
}
?>

<section class="sc-cert-content-block sc-cert-related-block" aria-labelledby="sc-cert-related-title">
	<h2 id="sc-cert-related-title" class="sc-cert-section-heading">
		<span class="dashicons dashicons-products" aria-hidden="true"></span>
		<?php esc_html_e( 'Covered Products & Categories', 'spicecraft' ); ?>
	</h2>

	<!-- Related Categories -->
	<?php if ( ! empty( $category_ids ) ) :
		$categories = get_terms( array(
			'taxonomy'   => 'product_cat',
			'include'    => $category_ids,
			'hide_empty' => false,
		) );
		if ( ! empty( $categories ) && ! is_wp_error( $categories ) ) :
			?>
			<div class="sc-cert-related-subgroup">
				<h3 class="sc-cert-scope-subheading"><?php esc_html_e( 'Certified Product Lines', 'spicecraft' ); ?></h3>
				<div class="sc-cat-grid sc-cat-grid--compact">
					<?php foreach ( $categories as $cat ) :
						$thumb_id = get_term_meta( $cat->term_id, 'thumbnail_id', true );
						$cat_url  = get_term_link( $cat );
						?>
						<a href="<?php echo esc_url( $cat_url ); ?>" class="sc-cat-card sc-card">
							<div class="sc-cat-card__media">
								<?php if ( ! empty( $thumb_id ) ) : ?>
									<?php echo wp_get_attachment_image( $thumb_id, 'medium', false, array( 'class' => 'sc-img-fluid sc-rounded', 'loading' => 'lazy' ) ); ?>
								<?php else : ?>
									<div class="sc-cat-card__placeholder" aria-hidden="true">
										<span class="dashicons dashicons-tag"></span>
									</div>
								<?php endif; ?>
							</div>
							<div class="sc-cat-card__body text-center" style="padding-top: 10px;">
								<h4 class="sc-cat-card__title" style="font-size: 1rem; margin-bottom: 2px;"><?php echo esc_html( $cat->name ); ?></h4>
								<span class="sc-cat-card__count" style="font-size: 0.8rem; color: var(--sc-color-text-muted);"><?php echo esc_html( sprintf( _n( '%d Product', '%d Products', $cat->count, 'spicecraft' ), $cat->count ) ); ?></span>
							</div>
						</a>
					<?php endforeach; ?>
				</div>
			</div>
			<?php
		endif;
	endif; ?>

	<!-- Related Individual Products -->
	<?php if ( ! empty( $product_ids ) && class_exists( 'WooCommerce' ) ) :
		$products_query = new WP_Query( array(
			'post_type'      => 'product',
			'post_status'    => 'publish',
			'post__in'       => $product_ids,
			'posts_per_page' => count( $product_ids ),
			'orderby'        => 'post__in',
		) );

		if ( $products_query->have_posts() ) :
			?>
			<div class="sc-cert-related-subgroup" style="margin-top: var(--sc-space-6, 24px);">
				<h3 class="sc-cert-scope-subheading"><?php esc_html_e( 'Certified Spice Products', 'spicecraft' ); ?></h3>
				<div class="sc-product-grid sc-product-grid--compact">
					<?php
					while ( $products_query->have_posts() ) :
						$products_query->the_post();
						wc_get_template_part( 'content', 'product' );
					endwhile;
					wp_reset_postdata();
					?>
				</div>
			</div>
			<?php
		endif;
	endif; ?>
</section>
