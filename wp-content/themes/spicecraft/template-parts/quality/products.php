<?php
/**
 * Quality & Sourcing Section: Tested Products Connection
 *
 * Connects quality assurance and sourcing transparency to verified catalog products
 * or categories in strict catalog mode.
 *
 * @package SpiceCraft
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$prod_sec = function_exists( 'spicecraft_get_quality_section' )
	? spicecraft_get_quality_section( 'products' )
	: array();

if ( empty( $prod_sec ) || empty( $prod_sec['heading'] ) ) {
	return;
}

$eyebrow     = $prod_sec['eyebrow'] ?? '';
$heading     = $prod_sec['heading'] ?? '';
$description = $prod_sec['description'] ?? '';
$source      = $prod_sec['source'] ?? 'categories';
$limit       = absint( $prod_sec['limit'] ?? 4 );
$selected    = $prod_sec['selected_ids'] ?? array();
$cta_label   = $prod_sec['cta_label'] ?? __( 'Explore Pure Spices', 'spicecraft' );
$cta_url     = ! empty( $prod_sec['cta_url'] ) ? $prod_sec['cta_url'] : ( function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/shop/' ) );
?>

<section id="sc-quality-products" class="sc-quality-products sc-section sc-section--alt" aria-label="<?php echo esc_attr( $heading ); ?>">
	<div class="sc-container">
		<div class="sc-section-header text-center" style="max-width: 720px; margin: 0 auto var(--sc-space-10, 40px);">
			<?php if ( ! empty( $eyebrow ) ) : ?>
				<span class="sc-eyebrow"><?php echo esc_html( $eyebrow ); ?></span>
			<?php endif; ?>
			<h2 class="sc-section-title"><?php echo esc_html( $heading ); ?></h2>
			<?php if ( ! empty( $description ) ) : ?>
				<p class="sc-section-desc"><?php echo nl2br( esc_html( $description ) ); ?></p>
			<?php endif; ?>
		</div>

		<?php
		if ( 'products' === $source && class_exists( 'WooCommerce' ) ) :
			$p_args = array(
				'post_type'      => 'product',
				'post_status'    => 'publish',
				'posts_per_page' => $limit > 0 ? $limit : 4,
			);
			if ( ! empty( $selected ) ) {
				$p_args['post__in'] = array_filter( array_map( 'absint', $selected ) );
			}
			$products_query = new WP_Query( $p_args );
			if ( $products_query->have_posts() ) :
				?>
				<div class="sc-product-grid">
					<?php
					while ( $products_query->have_posts() ) :
						$products_query->the_post();
						wc_get_template_part( 'content', 'product' );
					endwhile;
					wp_reset_postdata();
					?>
				</div>
				<?php
			endif;
		else :
			// Categories source
			$cat_args = array(
				'taxonomy'   => 'product_cat',
				'hide_empty' => false,
				'number'     => $limit > 0 ? $limit : 4,
				'exclude'    => array( get_option( 'default_product_cat' ) ),
			);
			if ( ! empty( $selected ) ) {
				$cat_args['include'] = array_filter( array_map( 'absint', $selected ) );
			}
			$categories = get_terms( $cat_args );
			if ( ! empty( $categories ) && ! is_wp_error( $categories ) ) :
				?>
				<div class="sc-cat-grid">
					<?php foreach ( $categories as $cat ) :
						$thumb_id = get_term_meta( $cat->term_id, 'thumbnail_id', true );
						$cat_url  = get_term_link( $cat );
						?>
						<a href="<?php echo esc_url( $cat_url ); ?>" class="sc-cat-card sc-card">
							<div class="sc-cat-card__media">
								<?php if ( ! empty( $thumb_id ) ) : ?>
									<?php echo wp_get_attachment_image( $thumb_id, 'medium_large', false, array( 'class' => 'sc-img-fluid sc-rounded', 'loading' => 'lazy' ) ); ?>
								<?php else : ?>
									<div class="sc-cat-card__placeholder" aria-hidden="true">
										<span class="dashicons dashicons-tag"></span>
									</div>
								<?php endif; ?>
							</div>
							<div class="sc-cat-card__body text-center" style="padding-top: 12px;">
								<h3 class="sc-cat-card__title" style="font-size: 1.1rem; margin-bottom: 4px;"><?php echo esc_html( $cat->name ); ?></h3>
								<span class="sc-cat-card__count" style="font-size: 0.85rem; color: var(--sc-color-text-muted);"><?php echo esc_html( sprintf( _n( '%d Product', '%d Products', $cat->count, 'spicecraft' ), $cat->count ) ); ?></span>
							</div>
						</a>
					<?php endforeach; ?>
				</div>
				<?php
			endif;
		endif;
		?>

		<?php if ( ! empty( $cta_label ) && ! empty( $cta_url ) ) : ?>
			<div class="text-center" style="margin-top: var(--sc-space-10, 40px);">
				<a href="<?php echo esc_url( $cta_url ); ?>" class="sc-btn sc-btn--primary sc-btn--lg">
					<?php echo esc_html( $cta_label ); ?> &rarr;
				</a>
			</div>
		<?php endif; ?>
	</div>
</section>
