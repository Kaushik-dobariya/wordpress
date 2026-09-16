<?php
/**
 * SpiceCraft - Custom Single Product Template
 *
 * Overrides WooCommerce's default content-single-product.php to deliver an
 * exceptional FMCG spice discovery and conversion layout:
 * - 55% Gallery / 45% Product Information split
 * - Interactive pack-size selector linked to WhatsApp enquiry
 * - High-conversion Manufacturer Enquiry Box
 * - Trust highlight row
 * - Detailed specifications and purity information
 * - Custom customer reviews presentation
 * - Cohesive related products section
 *
 * @package SpiceCraft
 * @version 3.6.0
 */

defined( 'ABSPATH' ) || exit;

global $product;

/**
 * Hook: woocommerce_before_single_product.
 */
do_action( 'woocommerce_before_single_product' );

if ( post_password_required() ) {
	echo get_the_password_form(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	return;
}

$product_id   = $product->get_id();
$product_name = $product->get_name();
$sku          = $product->get_sku();
$primary_cat  = spicecraft_get_product_primary_category( $product_id );
$badge        = spicecraft_get_product_badge( $product_id );
$pack_sizes   = spicecraft_get_product_pack_sizes( $product );
$first_pack   = ! empty( $pack_sizes ) ? $pack_sizes[0] : '';
$rating_count = $product->get_rating_count();
$average      = $product->get_average_rating();
$specs        = spicecraft_get_product_specs( $product );

// Pre-fill WhatsApp and Mailto enquiry links
$whatsapp_url  = spicecraft_get_whatsapp_enquiry_url( $product_name, $first_pack );
$contact_email = spicecraft_get_theme_option( 'spicecraft_export_email', get_option( 'admin_email' ) );
$subject       = sprintf(
	/* translators: 1: Product name, 2: SKU */
	esc_html__( 'Commercial Supply & Trade Enquiry: %1$s (SKU: %2$s)', 'spicecraft' ),
	$product_name,
	! empty( $sku ) ? $sku : 'N/A'
);
$mail_lines = array(
	esc_html__( 'Hello SpiceCraft Commercial Team,', 'spicecraft' ),
	'',
	sprintf(
		/* translators: 1: Product Name, 2: SKU */
		esc_html__( 'I am inquiring about commercial supply, bulk wholesale pricing, or private labeling for %1$s (SKU: %2$s).', 'spicecraft' ),
		$product_name,
		! empty( $sku ) ? $sku : 'N/A'
	),
	'',
	sprintf(
		/* translators: %s: Pack size */
		esc_html__( 'Interested Pack Size / Packaging: %s', 'spicecraft' ),
		! empty( $first_pack ) ? $first_pack : esc_html__( 'Standard Commercial Pack', 'spicecraft' )
	),
	sprintf(
		/* translators: %s: Product URL */
		esc_html__( 'Product URL: %s', 'spicecraft' ),
		get_permalink( $product_id )
	),
	'',
	esc_html__( 'Company / Business Name:', 'spicecraft' ),
	esc_html__( 'Country / Delivery Location:', 'spicecraft' ),
	esc_html__( 'Estimated Monthly or Batch Volume:', 'spicecraft' ),
	esc_html__( 'Contact Person & Phone:', 'spicecraft' ),
);
$mailto_url = 'mailto:' . sanitize_email( $contact_email ) . '?subject=' . rawurlencode( $subject ) . '&body=' . rawurlencode( implode( "\r\n", $mail_lines ) );
?>

<div id="product-<?php the_ID(); ?>" <?php wc_product_class( 'sc-single-product', $product ); ?> data-product-id="<?php echo esc_attr( $product_id ); ?>" data-product-name="<?php echo esc_attr( $product_name ); ?>">

	<!-- Breadcrumb Navigation -->
	<?php woocommerce_breadcrumb(); ?>

	<!-- ====================================================================
	     1. ABOVE-THE-FOLD HERO GRID (55% Gallery / 45% Conversion Engine)
	     ==================================================================== -->
	<div class="sc-single-product__hero-grid">

		<!-- LEFT 55%: Product Gallery Column -->
		<div class="sc-single-product__gallery-col">
			<div class="sc-single-product__gallery-wrapper">
				<?php if ( ! empty( $badge ) ) : ?>
					<span class="sc-single-product__badge sc-badge sc-badge--<?php echo esc_attr( sanitize_title( $badge ) ); ?>">
						<?php echo esc_html( $badge ); ?>
					</span>
				<?php endif; ?>

				<?php
				/**
				 * Standard WooCommerce Gallery Hook.
				 * Preserves WooCommerce core lightbox, zoom, and thumbnail carousel support.
				 */
				woocommerce_show_product_images();
				?>
			</div>
		</div><!-- .sc-single-product__gallery-col -->

		<!-- RIGHT 45%: Product Information & Enquiry Engine -->
		<div class="sc-single-product__summary-col">

			<!-- Category & Title -->
			<?php if ( ! empty( $primary_cat ) ) : ?>
				<div class="sc-single-product__cat-wrap">
					<span class="sc-single-product__cat"><?php echo esc_html( $primary_cat ); ?></span>
				</div>
			<?php endif; ?>

			<h1 class="product_title entry-title sc-single-product__title"><?php the_title(); ?></h1>

			<!-- Ratings & Review Count -->
			<div class="sc-single-product__rating-row">
				<?php if ( $rating_count > 0 ) : ?>
					<div class="sc-single-product__rating">
						<span class="sc-stars" aria-hidden="true">
							<?php
							$score = round( (float) $average );
							for ( $i = 1; $i <= 5; $i++ ) {
								echo $i <= $score ? '★' : '☆';
							}
							?>
						</span>
						<span class="sc-rating-score"><?php echo esc_html( number_format( (float) $average, 1 ) ); ?></span>
						<a href="#tab-reviews" class="sc-rating-count-link">
							<?php
							/* translators: %s: review count */
							printf( esc_html( _n( '(%s Customer Review)', '(%s Customer Reviews)', $rating_count, 'spicecraft' ) ), esc_html( $rating_count ) );
							?>
						</a>
					</div>
				<?php else : ?>
					<div class="sc-single-product__rating sc-single-product__rating--empty">
						<span class="sc-stars sc-stars--muted" aria-hidden="true">★★★★★</span>
						<span class="sc-rating-unrated"><?php esc_html_e( 'Batch Spec · Unrated', 'spicecraft' ); ?></span>
						<span class="sc-rating-sep">·</span>
						<a href="#tab-reviews" class="sc-rating-count-link"><?php esc_html_e( 'Be the first to review', 'spicecraft' ); ?></a>
					</div>
				<?php endif; ?>
			</div><!-- .sc-single-product__rating-row -->

			<!-- Short Description -->
			<div class="sc-single-product__short-desc">
				<?php woocommerce_template_single_excerpt(); ?>
			</div>

			<!-- Interactive Pack Size Selector UI -->
			<?php if ( ! empty( $pack_sizes ) ) : ?>
				<div class="sc-pack-selector" id="sc-pack-selector">
					<div class="sc-pack-selector__header">
						<span class="sc-pack-selector__label"><?php esc_html_e( 'Available Pack Size:', 'spicecraft' ); ?></span>
						<span class="sc-pack-selector__selected" id="sc-selected-pack-label"><?php echo esc_html( $first_pack ); ?></span>
					</div>

					<div class="sc-pack-selector__options" role="radiogroup" aria-label="<?php esc_attr_e( 'Select pack size for enquiry', 'spicecraft' ); ?>">
						<?php foreach ( $pack_sizes as $index => $size ) : ?>
							<button type="button" 
								class="sc-pack-pill <?php echo 0 === $index ? 'is-selected' : ''; ?>" 
								data-pack-size="<?php echo esc_attr( $size ); ?>"
								role="radio" 
								aria-checked="<?php echo 0 === $index ? 'true' : 'false'; ?>">
								<span><?php echo esc_html( $size ); ?></span>
							</button>
						<?php endforeach; ?>
					</div>
				</div><!-- .sc-pack-selector -->
			<?php endif; ?>

			<!-- Price Strategy Status Display -->
			<div class="sc-single-product__price-status">
				<?php echo wp_kses_post( $product->get_price_html() ); ?>
			</div>

			<!-- Redesigned Manufacturer Enquiry Box -->
			<div class="sc-enquiry-box" id="sc-enquiry-box">
				<div class="sc-enquiry-box__header">
					<span class="sc-enquiry-box__badge"><?php esc_html_e( 'Direct Manufacturer Enquiry', 'spicecraft' ); ?></span>
					<h2 class="sc-enquiry-box__title"><?php esc_html_e( 'Interested in this product?', 'spicecraft' ); ?></h2>
					<p class="sc-enquiry-box__desc">
						<?php esc_html_e( 'Contact our spice specialists directly for institutional bulk supply, distributor inquiries, custom packaging, or export pricing.', 'spicecraft' ); ?>
					</p>
				</div>

				<div class="sc-enquiry-box__actions">
					<!-- Primary CTA: WhatsApp Enquiry (Dynamic pack-size aware) -->
					<a href="<?php echo esc_url( $whatsapp_url ); ?>" 
						id="sc-whatsapp-enquiry-cta" 
						target="_blank" 
						rel="noopener noreferrer" 
						class="sc-btn sc-btn--whatsapp sc-btn--lg sc-btn--full">
						<svg class="sc-icon" width="22" height="22" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
							<path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/>
						</svg>
						<span><?php esc_html_e( 'Enquire on WhatsApp', 'spicecraft' ); ?></span>
					</a>

					<!-- Secondary CTA: Email Product Enquiry -->
					<a href="<?php echo esc_url( $mailto_url ); ?>" 
						id="sc-email-enquiry-cta" 
						class="sc-btn sc-btn--secondary sc-btn--lg sc-btn--full">
						<svg class="sc-icon" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
							<path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
							<polyline points="22,6 12,13 2,6"></polyline>
						</svg>
						<span><?php esc_html_e( 'Email Trade Enquiry', 'spicecraft' ); ?></span>
					</a>
				</div>

				<div class="sc-enquiry-box__notice">
					<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
						<circle cx="12" cy="12" r="10"></circle>
						<line x1="12" y1="16" x2="12" y2="12"></line>
						<line x1="12" y1="8" x2="12.01" y2="8"></line>
					</svg>
					<span><?php esc_html_e( 'Bulk / Distribution Inquiries Welcome · Fast Response within 24 Hours', 'spicecraft' ); ?></span>
				</div>
			</div><!-- .sc-enquiry-box -->

			<!-- Compact Trust Information Row -->
			<div class="sc-trust-row" aria-label="<?php esc_attr_e( 'Quality Assurance Highlights', 'spicecraft' ); ?>">
				<div class="sc-trust-item">
					<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
						<polyline points="20 6 9 17 4 12"></polyline>
					</svg>
					<span><?php esc_html_e( 'Quality Assured', 'spicecraft' ); ?></span>
				</div>
				<div class="sc-trust-item">
					<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
						<polyline points="20 6 9 17 4 12"></polyline>
					</svg>
					<span><?php esc_html_e( 'Hygienically Packed', 'spicecraft' ); ?></span>
				</div>
				<div class="sc-trust-item">
					<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
						<polyline points="20 6 9 17 4 12"></polyline>
					</svg>
					<span><?php esc_html_e( 'Carefully Sourced', 'spicecraft' ); ?></span>
				</div>
				<div class="sc-trust-item">
					<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
						<polyline points="20 6 9 17 4 12"></polyline>
					</svg>
					<span><?php esc_html_e( 'Manufacturer Direct', 'spicecraft' ); ?></span>
				</div>
			</div><!-- .sc-trust-row -->

			<!-- Product Metadata (SKU, Categories, Tags) -->
			<div class="sc-single-product__meta">
				<?php if ( ! empty( $sku ) ) : ?>
					<div class="sc-meta-item">
						<span class="sc-meta-label"><?php esc_html_e( 'SKU:', 'spicecraft' ); ?></span>
						<span class="sc-meta-value"><?php echo esc_html( $sku ); ?></span>
					</div>
				<?php endif; ?>

				<?php echo wc_get_product_category_list( $product_id, ', ', '<div class="sc-meta-item"><span class="sc-meta-label">' . _n( 'Category:', 'Categories:', count( $product->get_category_ids() ), 'spicecraft' ) . '</span> <span class="sc-meta-value">', '</span></div>' ); ?>

				<?php echo wc_get_product_tag_list( $product_id, ', ', '<div class="sc-meta-item"><span class="sc-meta-label">' . _n( 'Tag:', 'Tags:', count( $product->get_tag_ids() ), 'spicecraft' ) . '</span> <span class="sc-meta-value">', '</span></div>' ); ?>
			</div>

		</div><!-- .sc-single-product__summary-col -->

	</div><!-- .sc-single-product__hero-grid -->

	<!-- ====================================================================
	     2. BELOW-THE-FOLD PRODUCT DETAILS & REVIEWS SECTIONS
	     ==================================================================== -->
	<div class="sc-single-product__details-tabs" id="product-details-sections">

		<div class="sc-tabs-nav" role="tablist">
			<button type="button" class="sc-tab-btn is-active" role="tab" aria-selected="true" aria-controls="tab-description" id="tab-btn-description">
				<?php esc_html_e( 'Product Description', 'spicecraft' ); ?>
			</button>

			<?php if ( ! empty( $specs ) ) : ?>
				<button type="button" class="sc-tab-btn" role="tab" aria-selected="false" aria-controls="tab-specifications" id="tab-btn-specifications">
					<?php esc_html_e( 'Specifications & Quality', 'spicecraft' ); ?>
				</button>
			<?php endif; ?>

			<button type="button" class="sc-tab-btn" role="tab" aria-selected="false" aria-controls="tab-storage" id="tab-btn-storage">
				<?php esc_html_e( 'Storage & Handling', 'spicecraft' ); ?>
			</button>

			<button type="button" class="sc-tab-btn" role="tab" aria-selected="false" aria-controls="tab-reviews" id="tab-btn-reviews">
				<?php
				/* translators: %s: number of reviews */
				printf( esc_html__( 'Customer Reviews (%s)', 'spicecraft' ), esc_html( $rating_count ) );
				?>
			</button>
		</div>

		<!-- Panel 1: In-depth Description -->
		<div class="sc-tab-panel is-active" id="tab-description" role="tabpanel" aria-labelledby="tab-btn-description">
			<div class="sc-prose">
				<h2><?php esc_html_e( 'Product Overview & Culinary Applications', 'spicecraft' ); ?></h2>
				<?php the_content(); ?>
			</div>
		</div>

		<!-- Panel 2: Product Specifications Table -->
		<?php if ( ! empty( $specs ) ) : ?>
			<div class="sc-tab-panel" id="tab-specifications" role="tabpanel" aria-labelledby="tab-btn-specifications" hidden>
				<div class="sc-prose">
					<h2><?php esc_html_e( 'Manufacturing & Quality Specifications', 'spicecraft' ); ?></h2>
					<p class="sc-lead-text">
						<?php esc_html_e( 'All our spice products undergo rigorous quality inspection, foreign matter sorting, and moisture-controlled packaging.', 'spicecraft' ); ?>
					</p>

					<table class="sc-specs-table">
						<tbody>
							<?php foreach ( $specs as $spec_label => $spec_value ) : ?>
								<tr>
									<th scope="row"><?php echo esc_html( $spec_label ); ?></th>
									<td><?php echo esc_html( $spec_value ); ?></td>
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
				</div>
			</div>
		<?php endif; ?>

		<!-- Panel 3: Storage & Usage Instructions -->
		<div class="sc-tab-panel" id="tab-storage" role="tabpanel" aria-labelledby="tab-btn-storage" hidden>
			<div class="sc-prose">
				<h2><?php esc_html_e( 'Storage Instructions & Freshness Guidelines', 'spicecraft' ); ?></h2>
				<div class="sc-storage-grid">
					<div class="sc-storage-card">
						<div class="sc-storage-card__icon" aria-hidden="true">
							<svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2v2M12 20v2M4.93 4.93l1.41 1.41M17.66 17.66l1.41 1.41M2 12h2M20 12h2M6.34 17.66l-1.41 1.41M19.07 4.93l-1.41 1.41"/></svg>
						</div>
						<h3><?php esc_html_e( 'Cool & Dry Environment', 'spicecraft' ); ?></h3>
						<p><?php esc_html_e( 'Store in a cool, dry place away from direct sunlight, steam, and high-heat cooking areas to preserve volatile essential oils.', 'spicecraft' ); ?></p>
					</div>

					<div class="sc-storage-card">
						<div class="sc-storage-card__icon" aria-hidden="true">
							<svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M9 3v18"/><path d="M15 3v18"/></svg>
						</div>
						<h3><?php esc_html_e( 'Airtight Containment', 'spicecraft' ); ?></h3>
						<p><?php esc_html_e( 'Transfer to an airtight glass or food-grade stainless steel container after opening the pouch for maximum potency and aroma retention.', 'spicecraft' ); ?></p>
					</div>

					<div class="sc-storage-card">
						<div class="sc-storage-card__icon" aria-hidden="true">
							<svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
						</div>
						<h3><?php esc_html_e( 'Optimal Shelf Life', 'spicecraft' ); ?></h3>
						<p><?php esc_html_e( 'Best before 12 months from manufacturing date when unopened. Maintain hygienic spoon usage to avoid moisture contamination.', 'spicecraft' ); ?></p>
					</div>
				</div>
			</div>
		</div>

		<!-- Panel 4: Customer Reviews (Native WooCommerce integration) -->
		<div class="sc-tab-panel" id="tab-reviews" role="tabpanel" aria-labelledby="tab-btn-reviews" hidden>
			<?php
			if ( comments_open() || '0' !== get_comments_number() ) {
				comments_template();
			} else {
				echo '<p>' . esc_html__( 'Customer reviews are currently closed for this product batch.', 'spicecraft' ) . '</p>';
			}
			?>
		</div>

	</div><!-- .sc-single-product__details-tabs -->

	<!-- ====================================================================
	     3. RELATED PRODUCTS SECTION (Uses the EXACT SAME unified card!)
	     ==================================================================== -->
	<section class="sc-related-products" aria-labelledby="related-products-heading">
		<div class="sc-related-products__header">
			<span class="sc-related-products__eyebrow"><?php esc_html_e( 'Discover More', 'spicecraft' ); ?></span>
			<h2 id="related-products-heading" class="sc-related-products__title">
				<?php esc_html_e( 'Related Spices & Masala Blends', 'spicecraft' ); ?>
			</h2>
			<p class="sc-related-products__sub">
				<?php esc_html_e( 'Explore complementary whole spices and artisanal masalas from our manufacturing range.', 'spicecraft' ); ?>
			</p>
		</div>

		<?php
		/**
		 * WooCommerce Related Products.
		 * Automatically loads content-product.php for each card, guaranteeing
		 * 100% component consistency with the catalog.
		 */
		woocommerce_related_products(
			array(
				'posts_per_page' => 4,
				'columns'        => 4,
				'orderby'        => 'rand',
			)
		);
		?>
	</section>

</div><!-- #product-<?php the_ID(); ?> -->

<?php
/**
 * Hook: woocommerce_after_single_product.
 */
do_action( 'woocommerce_after_single_product' );
