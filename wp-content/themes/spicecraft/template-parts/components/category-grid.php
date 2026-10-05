<?php
/**
 * SpiceCraft Reusable Component: Dynamic Product Category Grid
 *
 * Parameters:
 * - heading (string)
 * - eyebrow (string)
 * - description (string)
 * - limit (int, default: 6)
 * - columns (int: 2, 3, 4 - default 3)
 * - parent (int, default: 0 for top-level only)
 * - include (array of term IDs)
 * - alignment ('left'|'center' - default 'center')
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

$heading     = ! empty( $args['heading'] ) ? $args['heading'] : '';
$eyebrow     = ! empty( $args['eyebrow'] ) ? $args['eyebrow'] : '';
$description = ! empty( $args['description'] ) ? $args['description'] : '';
$limit       = ! empty( $args['limit'] ) ? absint( $args['limit'] ) : 6;
$columns     = ! empty( $args['columns'] ) && in_array( (int) $args['columns'], array( 2, 3, 4 ), true ) ? (int) $args['columns'] : 3;
$alignment   = ! empty( $args['alignment'] ) && 'left' === $args['alignment'] ? 'left' : 'center';
$include     = ! empty( $args['include'] ) && is_array( $args['include'] ) ? array_map( 'absint', $args['include'] ) : array();

$term_args = array(
	'taxonomy'   => 'product_cat',
	'hide_empty' => true,
	'number'     => $limit,
);

if ( ! empty( $include ) ) {
	$term_args['include'] = $include;
	unset( $term_args['number'] );
} elseif ( isset( $args['parent'] ) ) {
	$term_args['parent'] = absint( $args['parent'] );
}

$categories = get_terms( $term_args );

if ( empty( $categories ) || is_wp_error( $categories ) ) {
	return;
}
?>
<section class="sc-comp-category-grid sc-comp-category-grid--cols-<?php echo esc_attr( $columns ); ?>">
	<div class="sc-container">
		<?php if ( $heading || $eyebrow || $description ) : ?>
			<header class="sc-section-header sc-section-header--<?php echo esc_attr( $alignment ); ?>">
				<?php if ( $eyebrow ) : ?>
					<p class="sc-eyebrow"><?php echo esc_html( $eyebrow ); ?></p>
				<?php endif; ?>
				<?php if ( $heading ) : ?>
					<h2 class="sc-section-title"><?php echo esc_html( $heading ); ?></h2>
				<?php endif; ?>
				<?php if ( $description ) : ?>
					<p class="sc-section-subtitle"><?php echo esc_html( $description ); ?></p>
				<?php endif; ?>
			</header>
		<?php endif; ?>

		<div class="sc-grid sc-grid--categories sc-grid-cols-<?php echo esc_attr( $columns ); ?>">
			<?php foreach ( $categories as $category ) :
				$thumb_id   = get_term_meta( $category->term_id, 'thumbnail_id', true );
				$cat_url    = get_term_link( $category, 'product_cat' );
				$count_text = sprintf( _n( '%d Product', '%d Products', $category->count, 'spicecraft' ), $category->count );
				?>
				<article class="sc-card sc-card--category">
					<a href="<?php echo esc_url( is_wp_error( $cat_url ) ? '#' : $cat_url ); ?>" class="sc-card-category-link">
						<div class="sc-card-media">
							<?php if ( ! empty( $thumb_id ) ) : ?>
								<?php echo wp_get_attachment_image( absint( $thumb_id ), 'medium_large', false, array( 'class' => 'sc-category-img', 'alt' => $category->name, 'loading' => 'lazy' ) ); ?>
							<?php else : ?>
								<div class="sc-category-placeholder" aria-hidden="true">
									<svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
								</div>
							<?php endif; ?>
							<div class="sc-card-scrim" aria-hidden="true"></div>
						</div>
						<div class="sc-card-body">
							<div class="sc-card-text">
								<span class="sc-badge sc-badge--count"><?php echo esc_html( $count_text ); ?></span>
								<h3 class="sc-card-title"><?php echo esc_html( $category->name ); ?></h3>
								<?php if ( ! empty( $category->description ) ) : ?>
									<p class="sc-card-desc"><?php echo esc_html( wp_trim_words( $category->description, 10 ) ); ?></p>
								<?php endif; ?>
							</div>
							<span class="sc-card-action-circle" aria-hidden="true">
								<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
							</span>
						</div>
					</a>
				</article>
			<?php endforeach; ?>
		</div>
	</div>
</section>
