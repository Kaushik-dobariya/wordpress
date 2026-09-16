<?php
/**
 * SpiceCraft - Custom Product Card Template
 *
 * Overrides WooCommerce's default loop product card to present a bespoke,
 * luxurious FMCG spices discovery layout without transactional purchasing artifacts.
 *
 * Used universally across:
 * - Shop catalog archive
 * - Product category archives
 * - Product search results
 * - Single product "Related Products" section
 *
 * @package SpiceCraft
 * @version 9.4.0
 */

defined( 'ABSPATH' ) || exit;

global $product;

// Ensure product is valid and visible.
if ( empty( $product ) || ! $product->is_visible() ) {
	return;
}

$product_id   = $product->get_id();
$permalink    = esc_url( get_permalink( $product_id ) );
$title        = get_the_title();
$badge_data   = spicecraft_get_product_badge_data( $product_id );
$badge        = $badge_data['label'];
$badge_style  = ! empty( $badge_data['style'] ) ? $badge_data['style'] : 'primary';
$pack_sizes   = spicecraft_get_product_pack_sizes( $product );
$primary_cat  = spicecraft_get_product_primary_category( $product_id );
$rating_count = $product->get_rating_count();
$average      = $product->get_average_rating();
?>
<li <?php wc_product_class( 'sc-product-card', $product ); ?> data-product-id="<?php echo esc_attr( $product_id ); ?>">

	<!-- 1. Media Surface Container -->
	<div class="sc-product-card__media">
		<?php if ( ! empty( $badge ) ) : ?>
			<span class="sc-product-card__badge sc-badge sc-badge--<?php echo esc_attr( $badge_style ); ?>">
				<?php echo esc_html( $badge ); ?>
			</span>
		<?php endif; ?>

		<button type="button" 
			class="sc-product-card__favourite" 
			aria-label="<?php echo esc_attr( sprintf( __( 'Add %s to favourites', 'spicecraft' ), $title ) ); ?>" 
			data-product-id="<?php echo esc_attr( $product_id ); ?>"
			aria-pressed="false"
			title="<?php esc_attr_e( 'Save to Favourites', 'spicecraft' ); ?>">
			<svg class="sc-heart-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
				<path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path>
			</svg>
		</button>

		<a href="<?php echo $permalink; ?>" class="sc-product-card__img-link" tabindex="-1" aria-hidden="true">
			<?php
			if ( has_post_thumbnail( $product_id ) ) {
				echo $product->get_image(
					'woocommerce_thumbnail',
					array(
						'class'   => 'sc-product-card__img',
						'loading' => 'lazy',
						'alt'     => esc_attr( $title ),
					)
				);
			} else {
				echo sprintf(
					'<div class="sc-product-card__placeholder"><svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg><span>%s</span></div>',
					esc_html__( 'SpiceCraft Pure Spices', 'spicecraft' )
				);
			}
			?>
		</a>
	</div><!-- .sc-product-card__media -->

	<!-- 2. Card Body -->
	<div class="sc-product-card__body">
		<?php if ( ! empty( $primary_cat ) ) : ?>
			<span class="sc-product-card__cat"><?php echo esc_html( $primary_cat ); ?></span>
		<?php endif; ?>

		<h3 class="sc-product-card__title">
			<a href="<?php echo $permalink; ?>"><?php echo esc_html( $title ); ?></a>
		</h3>

		<!-- Rating Information -->
		<?php if ( $rating_count > 0 ) : ?>
			<div class="sc-product-card__rating" aria-label="<?php echo esc_attr( sprintf( __( 'Rated %1$s out of 5 based on %2$s reviews', 'spicecraft' ), $average, $rating_count ) ); ?>">
				<span class="sc-stars" aria-hidden="true">
					<?php
					$score = round( (float) $average );
					for ( $i = 1; $i <= 5; $i++ ) {
						echo $i <= $score ? '★' : '☆';
					}
					?>
				</span>
				<span class="sc-rating-score"><?php echo esc_html( number_format( (float) $average, 1 ) ); ?></span>
				<span class="sc-rating-count">(<?php echo esc_html( $rating_count ); ?>)</span>
			</div>
		<?php else : ?>
			<div class="sc-product-card__rating sc-product-card__rating--empty">
				<span class="sc-rating-unrated"><?php esc_html_e( 'Batch Spec · Unrated', 'spicecraft' ); ?></span>
			</div>
		<?php endif; ?>

		<!-- Pack Sizes Preview -->
		<?php if ( ! empty( $pack_sizes ) ) : ?>
			<div class="sc-product-card__packs">
				<span class="sc-packs-label"><?php esc_html_e( 'Available in:', 'spicecraft' ); ?></span>
				<span class="sc-packs-list"><?php echo esc_html( implode( ' • ', $pack_sizes ) ); ?></span>
			</div>
		<?php endif; ?>

		<!-- Footer: Enquiry Price Status & CTA -->
		<div class="sc-product-card__footer">
			<div class="sc-product-card__price-status">
				<?php echo wp_kses_post( $product->get_price_html() ); ?>
			</div>

			<a href="<?php echo $permalink; ?>" class="sc-product-card__cta" aria-label="<?php echo esc_attr( sprintf( __( 'View details for %s', 'spicecraft' ), $title ) ); ?>">
				<span><?php esc_html_e( 'View Details', 'spicecraft' ); ?></span>
				<svg class="sc-cta-arrow" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
					<line x1="5" y1="12" x2="19" y2="12"></line>
					<polyline points="12 5 19 12 12 19"></polyline>
				</svg>
			</a>
		</div>
	</div><!-- .sc-product-card__body -->

</li>
