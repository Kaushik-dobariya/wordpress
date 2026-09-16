<?php
/**
 * Homepage Template Part: Product Discovery / Explore Range
 * Semantic ID: #product-discovery
 *
 * Provides an immersive range exploration experience.
 * Reuses WooCommerce product categories with a distinctive composition.
 *
 * @package SpiceCraft
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WooCommerce' ) ) {
	return;
}

$pd = function_exists( 'spicecraft_get_homepage_section' )
	? spicecraft_get_homepage_section( 'product_discovery' )
	: array();

$eyebrow      = ! empty( $pd['eyebrow'] ) ? $pd['eyebrow'] : '';
$heading      = ! empty( $pd['heading'] ) ? $pd['heading'] : '';
$description  = ! empty( $pd['description'] ) ? $pd['description'] : '';
$category_ids = ! empty( $pd['category_ids'] ) ? array_map( 'absint', (array) $pd['category_ids'] ) : array();
$cta_label    = ! empty( $pd['cta_label'] ) ? $pd['cta_label'] : __( 'Explore All Spice Ranges', 'spicecraft' );
$cta_url      = ! empty( $pd['cta_url'] ) ? $pd['cta_url'] : ( function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/shop/' ) );

if ( empty( $heading ) && empty( $description ) ) {
	return;
}

$term_args = array(
	'taxonomy'   => 'product_cat',
	'hide_empty' => true,
	'number'     => 5,
);

if ( ! empty( $category_ids ) ) {
	$term_args['include'] = $category_ids;
	unset( $term_args['number'] );
} else {
	$term_args['parent'] = 0;
}

$categories = get_terms( $term_args );

if ( empty( $categories ) || is_wp_error( $categories ) ) {
	return;
}

$lead_category   = array_shift( $categories );
$side_categories = $categories;
?>

<section id="product-discovery" class="sc-home-section sc-home-discovery sc-surface-warm" aria-labelledby="sec-heading-discovery">
	<div class="sc-container">
		<header class="sc-section-header sc-section-header--center">
			<?php if ( ! empty( $eyebrow ) ) : ?>
				<p class="sc-eyebrow"><?php echo esc_html( $eyebrow ); ?></p>
			<?php endif; ?>

			<h2 id="sec-heading-discovery" class="sc-section-title"><?php echo esc_html( $heading ); ?></h2>

			<?php if ( ! empty( $description ) ) : ?>
				<p class="sc-section-subtitle"><?php echo esc_html( $description ); ?></p>
			<?php endif; ?>
		</header>

		<div class="sc-discovery-composition">
			<?php if ( $lead_category ) :
				$lead_thumb_id = get_term_meta( $lead_category->term_id, 'thumbnail_id', true );
				$lead_url      = get_term_link( $lead_category, 'product_cat' );
				?>
				<div class="sc-discovery-lead">
					<a href="<?php echo esc_url( is_wp_error( $lead_url ) ? '#' : $lead_url ); ?>" class="sc-discovery-lead-card">
						<div class="sc-discovery-lead-media">
							<?php if ( ! empty( $lead_thumb_id ) ) : ?>
								<?php echo wp_get_attachment_image( absint( $lead_thumb_id ), 'large', false, array( 'class' => 'sc-discovery-lead-img', 'loading' => 'lazy' ) ); ?>
							<?php endif; ?>
							<div class="sc-discovery-lead-scrim" aria-hidden="true"></div>
						</div>
						<div class="sc-discovery-lead-body">
							<span class="sc-badge sc-badge--pure"><?php esc_html_e( 'Featured Range', 'spicecraft' ); ?></span>
							<h3 class="sc-discovery-lead-title"><?php echo esc_html( $lead_category->name ); ?></h3>
							<?php if ( ! empty( $lead_category->description ) ) : ?>
								<p class="sc-discovery-lead-desc"><?php echo esc_html( wp_trim_words( $lead_category->description, 16 ) ); ?></p>
							<?php endif; ?>
							<span class="sc-btn sc-btn--primary sc-btn--sm">
								<span><?php esc_html_e( 'Explore Range', 'spicecraft' ); ?></span>
								<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
							</span>
						</div>
					</a>
				</div>
			<?php endif; ?>

			<?php if ( ! empty( $side_categories ) ) : ?>
				<div class="sc-discovery-tiles">
					<?php foreach ( $side_categories as $side_cat ) :
						$side_thumb = get_term_meta( $side_cat->term_id, 'thumbnail_id', true );
						$side_url   = get_term_link( $side_cat, 'product_cat' );
						?>
						<a href="<?php echo esc_url( is_wp_error( $side_url ) ? '#' : $side_url ); ?>" class="sc-discovery-tile">
							<div class="sc-discovery-tile-thumb">
								<?php if ( ! empty( $side_thumb ) ) : ?>
									<?php echo wp_get_attachment_image( absint( $side_thumb ), 'thumbnail', false, array( 'class' => 'sc-tile-img', 'loading' => 'lazy' ) ); ?>
								<?php else : ?>
									<div class="sc-tile-placeholder" aria-hidden="true">
										<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor"><circle cx="12" cy="12" r="9"/></svg>
									</div>
								<?php endif; ?>
							</div>
							<div class="sc-discovery-tile-info">
								<h4 class="sc-discovery-tile-title"><?php echo esc_html( $side_cat->name ); ?></h4>
								<span class="sc-discovery-tile-count"><?php echo esc_html( sprintf( _n( '%d Product', '%d Products', $side_cat->count, 'spicecraft' ), $side_cat->count ) ); ?></span>
							</div>
							<span class="sc-discovery-tile-arrow" aria-hidden="true">&rarr;</span>
						</a>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>
		</div>

		<?php if ( ! empty( $cta_label ) ) : ?>
			<div class="sc-discovery-footer">
				<a href="<?php echo esc_url( $cta_url ); ?>" class="sc-btn sc-btn--secondary sc-btn--md">
					<span><?php echo esc_html( $cta_label ); ?></span>
					<svg class="sc-icon sc-icon-arrow" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
				</a>
			</div>
		<?php endif; ?>
	</div>
</section>
