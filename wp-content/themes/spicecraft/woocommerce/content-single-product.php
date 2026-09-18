<?php
/**
 * SpiceCraft - Custom Single Product Template
 *
 * Overrides WooCommerce's default content-single-product.php to deliver an
 * exceptional FMCG spice discovery and conversion layout:
 * - 55% Gallery / 45% Product Information split
 * - Interactive pack-size selector linked to WhatsApp enquiry
 * - High-conversion Manufacturer Enquiry Box
 * - Dynamic FMCG highlights (Zero fake claims)
 * - Dynamic Certifications relationship chips
 * - Zero empty container dynamic tabbed specs: Description, Ingredients, Nutrition, Specs, Storage, Usage, Reviews
 * - Reusable related products cards
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

// FMCG Metadata & Badges
$badge_data   = spicecraft_get_product_badge_data( $product_id );
$badge        = $badge_data['label'];
$badge_style  = ! empty( $badge_data['style'] ) ? $badge_data['style'] : 'primary';
$pack_sizes   = spicecraft_get_product_pack_sizes( $product );
$first_pack   = ! empty( $pack_sizes ) ? $pack_sizes[0] : '';
$rating_count = $product->get_rating_count();
$average      = $product->get_average_rating();
$specs        = spicecraft_get_product_specs( $product );

// Rich FMCG Data Architecture
$highlights     = function_exists( 'spicecraft_get_product_highlights' ) ? spicecraft_get_product_highlights( $product_id ) : array();
$certifications = function_exists( 'spicecraft_get_product_certifications' ) ? spicecraft_get_product_certifications( $product_id ) : array();
$ingredients    = function_exists( 'spicecraft_get_product_ingredients' ) ? spicecraft_get_product_ingredients( $product_id ) : '';
$nutrition_data = function_exists( 'spicecraft_get_product_nutrition' ) ? spicecraft_get_product_nutrition( $product_id ) : array( 'serving_size' => '', 'rows' => array() );
$storage_info   = function_exists( 'spicecraft_get_product_storage' ) ? spicecraft_get_product_storage( $product_id ) : '';
$usage_info     = function_exists( 'spicecraft_get_product_usage' ) ? spicecraft_get_product_usage( $product_id ) : '';

// Pre-fill WhatsApp and Mailto enquiry links
$whatsapp_url  = function_exists( 'spicecraft_get_whatsapp_enquiry_url' ) ? spicecraft_get_whatsapp_enquiry_url( $product_name, $first_pack, $sku, get_permalink( $product_id ) ) : '';
$contact_email = function_exists( 'spicecraft_get_setting' ) ? spicecraft_get_setting( 'email_export', spicecraft_get_setting( 'email_sales', spicecraft_get_setting( 'email_general', '' ) ) ) : '';
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

<div id="product-<?php the_ID(); ?>" <?php wc_product_class( 'sc-single-product', $product ); ?> 
	data-product-id="<?php echo esc_attr( $product_id ); ?>" 
	data-product-name="<?php echo esc_attr( $product_name ); ?>"
	data-product-sku="<?php echo esc_attr( $sku ); ?>">

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
					<span class="sc-single-product__badge sc-badge sc-badge--<?php echo esc_attr( $badge_style ); ?>">
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

			<!-- Manufacturer Enquiry Box -->
			<?php if ( ! empty( $whatsapp_url ) || ! empty( $contact_email ) ) : ?>
				<div class="sc-enquiry-box" id="sc-enquiry-box">
					<div class="sc-enquiry-box__header">
						<span class="sc-enquiry-box__badge"><?php esc_html_e( 'Direct Manufacturer Enquiry', 'spicecraft' ); ?></span>
						<h2 class="sc-enquiry-box__title"><?php esc_html_e( 'Interested in this product?', 'spicecraft' ); ?></h2>
						<p class="sc-enquiry-box__desc">
							<?php esc_html_e( 'Contact our spice specialists directly for institutional bulk supply, distributor inquiries, custom packaging, or export pricing.', 'spicecraft' ); ?>
						</p>
					</div>

					<!-- Inline Pack Size Validation Alert -->
					<div class="sc-pack-validation-notice" id="sc-pack-validation-notice" role="alert" style="display: none;">
						<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
						<span><?php esc_html_e( 'Please select a pack size above before submitting an enquiry.', 'spicecraft' ); ?></span>
					</div>

					<div class="sc-enquiry-box__actions">
						<!-- Primary CTA: WhatsApp Enquiry (Dynamic pack-size aware) -->
						<?php if ( ! empty( $whatsapp_url ) ) : ?>
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
						<?php endif; ?>

						<!-- Secondary CTA: Email Product Enquiry -->
						<?php if ( ! empty( $contact_email ) ) : ?>
							<a href="<?php echo esc_url( $mailto_url ); ?>" 
								id="sc-email-enquiry-cta" 
								class="sc-btn sc-btn--secondary sc-btn--lg sc-btn--full">
								<svg class="sc-icon" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
									<path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
									<polyline points="22,6 12,13 2,6"></polyline>
								</svg>
								<span><?php esc_html_e( 'Email Trade Enquiry', 'spicecraft' ); ?></span>
							</a>
						<?php endif; ?>
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
			<?php endif; ?>

			<!-- Product Engagement Actions: Favourite & Share -->
			<div class="sc-single-product__actions-row" aria-label="<?php esc_attr_e( 'Product Actions', 'spicecraft' ); ?>">
				<button type="button" 
					class="sc-single-product__fav-btn sc-btn sc-btn--outline" 
					data-product-id="<?php echo esc_attr( $product_id ); ?>" 
					aria-pressed="false" 
					aria-label="<?php echo esc_attr( sprintf( __( 'Add %s to favourites', 'spicecraft' ), $product_name ) ); ?>">
					<svg class="sc-heart-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
						<path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path>
					</svg>
					<span class="sc-fav-text"><?php esc_html_e( 'Save to Favourites', 'spicecraft' ); ?></span>
				</button>

				<button type="button" 
					class="sc-single-product__share-btn sc-btn sc-btn--outline" 
					id="sc-share-product-btn" 
					aria-label="<?php esc_attr_e( 'Share this product', 'spicecraft' ); ?>" 
					data-title="<?php echo esc_attr( $product_name ); ?>" 
					data-url="<?php echo esc_url( get_permalink( $product_id ) ); ?>">
					<svg class="sc-share-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
						<circle cx="18" cy="5" r="3"></circle>
						<circle cx="6" cy="12" r="3"></circle>
						<circle cx="18" cy="19" r="3"></circle>
						<line x1="8.59" y1="13.51" x2="15.42" y2="17.49"></line>
						<line x1="15.41" y1="6.51" x2="8.59" y2="10.49"></line>
					</svg>
					<span><?php esc_html_e( 'Share Product', 'spicecraft' ); ?></span>
				</button>
				<span class="sc-share-toast" id="sc-share-toast" role="status" aria-live="polite" style="display: none;"></span>
			</div>

			<!-- Product Highlights System (Only rendered when configured by admin) -->
			<?php if ( ! empty( $highlights ) ) : ?>
				<div class="sc-trust-row" aria-label="<?php esc_attr_e( 'Product Highlights', 'spicecraft' ); ?>">
					<?php foreach ( $highlights as $highlight_item ) : ?>
						<div class="sc-trust-item">
							<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
								<polyline points="20 6 9 17 4 12"></polyline>
							</svg>
							<span><?php echo esc_html( $highlight_item ); ?></span>
						</div>
					<?php endforeach; ?>
				</div><!-- .sc-trust-row -->
			<?php endif; ?>

			<!-- Product Certified Standards (Strictly scoped to this product/category) -->
			<?php
			$prod_certs = function_exists( 'spicecraft_get_product_public_certifications' )
				? spicecraft_get_product_public_certifications( $product_id )
				: array();

			if ( ! empty( $prod_certs ) ) : ?>
				<div class="sc-product-certs" aria-label="<?php esc_attr_e( 'Verified Product Certifications', 'spicecraft' ); ?>">
					<span class="sc-product-certs__label"><?php esc_html_e( 'Certified Standards:', 'spicecraft' ); ?></span>
					<div class="sc-product-certs__badges">
						<?php foreach ( $prod_certs as $pc_term ) :
							$pc_meta       = function_exists( 'spicecraft_get_certification_meta' ) ? spicecraft_get_certification_meta( $pc_term->term_id ) : array();
							$pc_logo_id    = absint( $pc_meta['logo_id'] ?? 0 );
							$pc_short      = ! empty( $pc_meta['short_name'] ) ? $pc_meta['short_name'] : $pc_term->name;
							$pc_has_detail = function_exists( 'spicecraft_has_certification_public_detail' ) ? spicecraft_has_certification_public_detail( $pc_term->term_id ) : true;
							$pc_url        = get_term_link( $pc_term );
							?>
							<div class="sc-product-cert-pill">
								<?php if ( $pc_logo_id ) : ?>
									<?php echo wp_get_attachment_image( $pc_logo_id, 'thumbnail', false, array( 'class' => 'sc-product-cert-pill__logo', 'loading' => 'lazy', 'alt' => esc_attr( $pc_term->name ) ) ); ?>
								<?php else : ?>
									<span class="dashicons dashicons-awards sc-product-cert-pill__icon" aria-hidden="true"></span>
								<?php endif; ?>
								<span class="sc-product-cert-pill__title"><?php echo esc_html( $pc_short ); ?></span>
								<?php if ( $pc_has_detail && ! is_wp_error( $pc_url ) ) : ?>
									<a href="<?php echo esc_url( $pc_url ); ?>" class="sc-product-cert-pill__link" title="<?php echo esc_attr( sprintf( __( 'View %s audit specifications', 'spicecraft' ), $pc_term->name ) ); ?>" aria-label="<?php echo esc_attr( sprintf( __( 'View %s audit specifications', 'spicecraft' ), $pc_term->name ) ); ?>">
										<span class="dashicons dashicons-arrow-right-alt2" aria-hidden="true"></span>
									</a>
								<?php endif; ?>
							</div>
						<?php endforeach; ?>
					</div>
				</div>
			<?php endif; ?>

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
	     2. BELOW-THE-FOLD PRODUCT DETAILS TABS (Dynamic, Zero Empty Containers)
	     ==================================================================== -->
	<?php
	// Dynamically assemble tabs only for populated sections
	$active_tabs = array();

	$product_content = get_the_content();
	if ( ! empty( $product_content ) ) {
		$active_tabs['description'] = __( 'Product Description', 'spicecraft' );
	}

	if ( ! empty( $ingredients ) ) {
		$active_tabs['ingredients'] = __( 'Ingredients', 'spicecraft' );
	}

	if ( ! empty( $nutrition_data['rows'] ) ) {
		$active_tabs['nutrition'] = __( 'Nutrition Facts', 'spicecraft' );
	}

	if ( ! empty( $specs ) ) {
		$active_tabs['specifications'] = __( 'Specifications & Quality', 'spicecraft' );
	}

	if ( ! empty( $storage_info ) ) {
		$active_tabs['storage'] = __( 'Storage & Handling', 'spicecraft' );
	}

	if ( ! empty( $usage_info ) ) {
		$active_tabs['usage'] = __( 'Usage Suggestions', 'spicecraft' );
	}

	// Always offer Customer Reviews
	$active_tabs['reviews'] = sprintf( __( 'Customer Reviews (%s)', 'spicecraft' ), esc_html( $rating_count ) );

	$tab_keys  = array_keys( $active_tabs );
	$first_tab = ! empty( $tab_keys ) ? $tab_keys[0] : '';
	?>

	<div class="sc-single-product__details-tabs" id="product-details-sections">

		<!-- Tab Navigation Buttons -->
		<div class="sc-tabs-nav" role="tablist">
			<?php foreach ( $active_tabs as $tab_id => $tab_title ) : ?>
				<?php $is_first = ( $tab_id === $first_tab ); ?>
				<button type="button" 
					class="sc-tab-btn <?php echo $is_first ? 'is-active' : ''; ?>" 
					role="tab" 
					aria-selected="<?php echo $is_first ? 'true' : 'false'; ?>" 
					aria-controls="tab-<?php echo esc_attr( $tab_id ); ?>" 
					id="tab-btn-<?php echo esc_attr( $tab_id ); ?>">
					<?php echo esc_html( $tab_title ); ?>
				</button>
			<?php endforeach; ?>
		</div>

		<!-- Tab Panel 1: Description -->
		<?php if ( isset( $active_tabs['description'] ) ) : ?>
			<div class="sc-tab-panel <?php echo 'description' === $first_tab ? 'is-active' : ''; ?>" 
				id="tab-description" 
				role="tabpanel" 
				aria-labelledby="tab-btn-description"
				<?php echo 'description' !== $first_tab ? 'hidden' : ''; ?>>
				<div class="sc-prose">
					<h2><?php esc_html_e( 'Product Overview & Culinary Applications', 'spicecraft' ); ?></h2>
					<?php the_content(); ?>
				</div>
			</div>
		<?php endif; ?>

		<!-- Tab Panel 2: Ingredients -->
		<?php if ( isset( $active_tabs['ingredients'] ) ) : ?>
			<div class="sc-tab-panel <?php echo 'ingredients' === $first_tab ? 'is-active' : ''; ?>" 
				id="tab-ingredients" 
				role="tabpanel" 
				aria-labelledby="tab-btn-ingredients"
				<?php echo 'ingredients' !== $first_tab ? 'hidden' : ''; ?>>
				<div class="sc-prose">
					<h2><?php esc_html_e( 'Ingredients', 'spicecraft' ); ?></h2>
					<div class="sc-ingredients-card" style="padding: 1.5rem; background: #faf9f6; border: 1px solid #e5e7eb; border-radius: 8px; font-size: 1.05rem; line-height: 1.7;">
						<?php echo nl2br( esc_html( $ingredients ) ); ?>
					</div>
				</div>
			</div>
		<?php endif; ?>

		<!-- Tab Panel 3: Nutrition Facts -->
		<?php if ( isset( $active_tabs['nutrition'] ) ) : ?>
			<div class="sc-tab-panel <?php echo 'nutrition' === $first_tab ? 'is-active' : ''; ?>" 
				id="tab-nutrition" 
				role="tabpanel" 
				aria-labelledby="tab-btn-nutrition"
				<?php echo 'nutrition' !== $first_tab ? 'hidden' : ''; ?>>
				<div class="sc-prose">
					<h2><?php esc_html_e( 'Nutrition Facts', 'spicecraft' ); ?></h2>
					<?php if ( ! empty( $nutrition_data['serving_size'] ) ) : ?>
						<p class="sc-nutrition-serving" style="font-weight: 600; color: #4b5563; margin-bottom: 0.75rem;">
							<?php printf( esc_html__( 'Serving Size: %s', 'spicecraft' ), esc_html( $nutrition_data['serving_size'] ) ); ?>
						</p>
					<?php endif; ?>

					<table class="sc-specs-table sc-nutrition-table">
						<thead>
							<tr>
								<th style="width: 50%;"><?php esc_html_e( 'Nutrient', 'spicecraft' ); ?></th>
								<th style="width: 50%;"><?php esc_html_e( 'Amount per 100g / Serving', 'spicecraft' ); ?></th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ( $nutrition_data['rows'] as $nut_row ) : ?>
								<tr>
									<th scope="row"><?php echo esc_html( $nut_row['nutrient'] ); ?></th>
									<td>
										<strong><?php echo esc_html( $nut_row['value'] ); ?></strong>
										<?php if ( ! empty( $nut_row['unit'] ) ) : ?>
											<span><?php echo esc_html( $nut_row['unit'] ); ?></span>
										<?php endif; ?>
									</td>
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
				</div>
			</div>
		<?php endif; ?>

		<!-- Tab Panel 4: Specifications Table -->
		<?php if ( isset( $active_tabs['specifications'] ) ) : ?>
			<div class="sc-tab-panel <?php echo 'specifications' === $first_tab ? 'is-active' : ''; ?>" 
				id="tab-specifications" 
				role="tabpanel" 
				aria-labelledby="tab-btn-specifications"
				<?php echo 'specifications' !== $first_tab ? 'hidden' : ''; ?>>
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

		<!-- Tab Panel 5: Storage Instructions -->
		<?php if ( isset( $active_tabs['storage'] ) ) : ?>
			<div class="sc-tab-panel <?php echo 'storage' === $first_tab ? 'is-active' : ''; ?>" 
				id="tab-storage" 
				role="tabpanel" 
				aria-labelledby="tab-btn-storage"
				<?php echo 'storage' !== $first_tab ? 'hidden' : ''; ?>>
				<div class="sc-prose">
					<h2><?php esc_html_e( 'Storage Instructions & Freshness Guidelines', 'spicecraft' ); ?></h2>
					<div class="sc-storage-box" style="padding: 1.5rem; background: #faf9f6; border: 1px solid #e5e7eb; border-radius: 8px; line-height: 1.7;">
						<?php echo nl2br( esc_html( $storage_info ) ); ?>
					</div>
				</div>
			</div>
		<?php endif; ?>

		<!-- Tab Panel 6: Usage & Culinary Suggestions -->
		<?php if ( isset( $active_tabs['usage'] ) ) : ?>
			<div class="sc-tab-panel <?php echo 'usage' === $first_tab ? 'is-active' : ''; ?>" 
				id="tab-usage" 
				role="tabpanel" 
				aria-labelledby="tab-btn-usage"
				<?php echo 'usage' !== $first_tab ? 'hidden' : ''; ?>>
				<div class="sc-prose">
					<h2><?php esc_html_e( 'Usage & Culinary Suggestions', 'spicecraft' ); ?></h2>
					<div class="sc-usage-box" style="padding: 1.5rem; background: #faf9f6; border: 1px solid #e5e7eb; border-radius: 8px; line-height: 1.7;">
						<?php echo nl2br( esc_html( $usage_info ) ); ?>
					</div>
				</div>
			</div>
		<?php endif; ?>

		<!-- Tab Panel 7: Customer Reviews -->
		<div class="sc-tab-panel <?php echo 'reviews' === $first_tab ? 'is-active' : ''; ?>" 
			id="tab-reviews" 
			role="tabpanel" 
			aria-labelledby="tab-btn-reviews"
			<?php echo 'reviews' !== $first_tab ? 'hidden' : ''; ?>>
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
	<?php
	$related_ids = wc_get_related_products( $product_id, 4 );
	if ( ! empty( $related_ids ) ) :
	?>
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
	<?php endif; ?>

	<!-- ====================================================================
	     4. RECENTLY VIEWED PRODUCTS SECTION (Client-side localStorage driven)
	     ==================================================================== -->
	<section class="sc-recently-viewed" id="sc-recently-viewed" style="display: none;" aria-labelledby="recently-viewed-heading">
		<div class="sc-related-products__header">
			<span class="sc-related-products__eyebrow"><?php esc_html_e( 'Your History', 'spicecraft' ); ?></span>
			<h2 id="recently-viewed-heading" class="sc-related-products__title">
				<?php esc_html_e( 'Recently Viewed Spices', 'spicecraft' ); ?>
			</h2>
			<p class="sc-related-products__sub">
				<?php esc_html_e( 'Products you have recently explored from our artisanal spice catalog.', 'spicecraft' ); ?>
			</p>
		</div>

		<div class="woocommerce columns-4">
			<ul class="products columns-4 sc-products-grid" id="sc-recently-viewed-grid"></ul>
		</div>
	</section>

</div><!-- #product-<?php the_ID(); ?> -->

<?php
/**
 * Hook: woocommerce_after_single_product.
 */
do_action( 'woocommerce_after_single_product' );
