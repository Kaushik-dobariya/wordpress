<?php
/**
 * Template part for displaying results in search.php
 *
 * Distinctly identifies post type (Product, Article, or Page) with semantic badges,
 * displays thumbnail where available, title, clean excerpt, and contextual CTA.
 *
 * @package SpiceCraft
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$post_type = get_post_type();
$badge_label = __( 'Content', 'spicecraft' );
$badge_class = 'sc-badge--secondary';
$cta_label   = __( 'Read More', 'spicecraft' );

if ( 'product' === $post_type ) {
	$badge_label = __( 'Spice Product', 'spicecraft' );
	$badge_class = 'sc-badge--primary';
	$cta_label   = __( 'View Product Details', 'spicecraft' );
} elseif ( 'post' === $post_type ) {
	$badge_label = __( 'Article & Insight', 'spicecraft' );
	$badge_class = 'sc-badge--secondary';
	$cta_label   = __( 'Read Article', 'spicecraft' );
} elseif ( 'page' === $post_type ) {
	$badge_label = __( 'Page', 'spicecraft' );
	$badge_class = 'sc-badge--outline';
	$cta_label   = __( 'Visit Page', 'spicecraft' );
}
?>

<article id="post-<?php the_ID(); ?>" <?php post_class( 'sc-card sc-search-result-card' ); ?> style="padding: var(--sc-space-6); margin-bottom: var(--sc-space-6); display: flex; gap: var(--sc-space-6); align-items: flex-start;">
	<?php if ( has_post_thumbnail() ) : ?>
		<div class="sc-search-result-thumb" style="width: 140px; height: 140px; flex-shrink: 0; border-radius: var(--sc-radius-md); overflow: hidden; background: var(--sc-color-surface-subtle, #f9fafb);">
			<a href="<?php the_permalink(); ?>" tabindex="-1" aria-hidden="true" style="display: block; width: 100%; height: 100%;">
				<?php the_post_thumbnail( 'medium', array( 'style' => 'width: 100%; height: 100%; object-fit: cover;' ) ); ?>
			</a>
		</div>
	<?php endif; ?>

	<div class="sc-search-result-body" style="flex-grow: 1;">
		<div style="display: flex; align-items: center; gap: var(--sc-space-3); margin-bottom: var(--sc-space-2); flex-wrap: wrap;">
			<span class="sc-badge <?php echo esc_attr( $badge_class ); ?>" style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em;">
				<?php echo esc_html( $badge_label ); ?>
			</span>
			<?php if ( 'post' === $post_type ) : ?>
				<span style="font-size: 0.8rem; color: var(--sc-color-text-muted);">
					<?php echo esc_html( get_the_date() ); ?>
				</span>
			<?php elseif ( 'product' === $post_type && function_exists( 'wc_get_product' ) ) : 
				$product = wc_get_product( get_the_ID() );
				if ( $product ) :
					$cats = wc_get_product_category_list( get_the_ID(), ', ' );
					if ( ! empty( $cats ) ) : ?>
						<span style="font-size: 0.8rem; color: var(--sc-color-text-muted);">
							<?php echo wp_kses_post( $cats ); ?>
						</span>
					<?php endif;
				endif;
			endif; ?>
		</div>

		<h2 class="sc-search-result-title" style="margin-top: 0; margin-bottom: var(--sc-space-2); font-size: 1.25rem;">
			<a href="<?php the_permalink(); ?>" style="color: var(--sc-color-text-heading); text-decoration: none;">
				<?php the_title(); ?>
			</a>
		</h2>

		<div class="sc-search-result-excerpt" style="font-size: 0.95rem; line-height: 1.6; color: var(--sc-color-text-muted); margin-bottom: var(--sc-space-4);">
			<?php the_excerpt(); ?>
		</div>

		<div>
			<a href="<?php the_permalink(); ?>" class="sc-btn sc-btn--secondary sc-btn--sm">
				<?php echo esc_html( $cta_label ); ?> &rarr;
			</a>
		</div>
	</div>
</article>
