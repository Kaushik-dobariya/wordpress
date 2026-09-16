<?php
/**
 * Homepage Template Part: Product Categories Grid
 * Semantic ID: #product-categories
 *
 * Consumes WooCommerce product categories dynamically.
 * Suppresses itself gracefully if WooCommerce is inactive or no categories exist.
 *
 * @package SpiceCraft
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WooCommerce' ) ) {
	return;
}

$cat_settings = function_exists( 'spicecraft_get_homepage_section' )
	? spicecraft_get_homepage_section( 'categories' )
	: array();

$eyebrow      = ! empty( $cat_settings['eyebrow'] ) ? $cat_settings['eyebrow'] : '';
$heading      = ! empty( $cat_settings['heading'] ) ? $cat_settings['heading'] : '';
$description  = ! empty( $cat_settings['description'] ) ? $cat_settings['description'] : '';
$display_mode = ! empty( $cat_settings['display_mode'] ) ? $cat_settings['display_mode'] : 'all';
$limit        = ! empty( $cat_settings['limit'] ) ? absint( $cat_settings['limit'] ) : 6;
$selected_ids = ! empty( $cat_settings['selected_ids'] ) ? array_map( 'absint', (array) $cat_settings['selected_ids'] ) : array();

$term_args = array(
	'taxonomy'   => 'product_cat',
	'hide_empty' => true,
	'number'     => $limit,
);

if ( 'manual' === $display_mode && ! empty( $selected_ids ) ) {
	$term_args['include'] = $selected_ids;
	unset( $term_args['number'] );
} elseif ( 'top_level' === $display_mode ) {
	$term_args['parent'] = 0;
}

$categories = get_terms( $term_args );

if ( empty( $categories ) || is_wp_error( $categories ) ) {
	return;
}
?>

<section id="product-categories" class="sc-home-section sc-home-categories" aria-labelledby="sec-heading-categories">
	<div class="sc-container">
		<header class="sc-section-header sc-section-header--center">
			<?php if ( ! empty( $eyebrow ) ) : ?>
				<p class="sc-eyebrow"><?php echo esc_html( $eyebrow ); ?></p>
			<?php endif; ?>

			<?php if ( ! empty( $heading ) ) : ?>
				<h2 id="sec-heading-categories" class="sc-section-title"><?php echo esc_html( $heading ); ?></h2>
			<?php else : ?>
				<h2 id="sec-heading-categories" class="sc-section-title"><?php esc_html_e( 'Our Spice Portfolio', 'spicecraft' ); ?></h2>
			<?php endif; ?>

			<?php if ( ! empty( $description ) ) : ?>
				<p class="sc-section-subtitle"><?php echo esc_html( $description ); ?></p>
			<?php endif; ?>
		</header>

		<div class="sc-grid sc-grid--categories">
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
