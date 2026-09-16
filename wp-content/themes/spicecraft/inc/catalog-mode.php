<?php
/**
 * SpiceCraft WooCommerce Catalog-Mode Architecture
 *
 * Enforces pure product discovery. Disables purchasing workflows, hides all
 * add-to-cart interfaces, intercepts cart/checkout endpoints, implements flexible
 * price strategy display filters, and routes customer interest to WhatsApp and
 * trade enquiry channels.
 *
 * @package SpiceCraft
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Safety Guard: Only initialize catalog mode when WooCommerce is active.
if ( ! class_exists( 'WooCommerce' ) ) {
	return;
}

/**
 * ============================================================================
 * 1. TRANSACTIONAL DISABLING (CORE ENFORCEMENT)
 * ============================================================================
 */

/**
 * Globally disable purchasing capability across all products and variations.
 * Ensures WooCommerce treats all items as catalog-only display items.
 */
add_filter( 'woocommerce_is_purchasable', '__return_false', 999 );
add_filter( 'woocommerce_variation_is_purchasable', '__return_false', 999 );

/**
 * Block any direct HTTP POST or programmatic cart additions.
 */
add_filter( 'woocommerce_add_to_cart_validation', '__return_false', 999 );

/**
 * ============================================================================
 * 2. INTERFACE MODIFICATIONS: REMOVE ADD TO CART & INJECT ENQUIRY CTAS
 * ============================================================================
 */

/**
 * Unhook WooCommerce purchasing actions from shop loops and single product pages.
 */
function spicecraft_remove_purchasing_ui() {
	// Remove Add-to-Cart button from archive & category loops
	remove_action( 'woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart', 10 );

	// Remove Add-to-Cart & Quantity selector form from single product summary
	remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_add_to_cart', 30 );

	// Attach custom catalog "View Details" button to product loops
	add_action( 'woocommerce_after_shop_loop_item', 'spicecraft_catalog_loop_view_details', 10 );

	// Attach Lead Enquiry CTA container to single product summary where add-to-cart was
	add_action( 'woocommerce_single_product_summary', 'spicecraft_catalog_single_enquiry_cta', 30 );
}
add_action( 'init', 'spicecraft_remove_purchasing_ui' );

/**
 * Output a clean, accessible "View Details" button on product loop cards.
 */
function spicecraft_catalog_loop_view_details() {
	global $product;
	if ( ! $product ) {
		return;
	}
	$permalink = esc_url( get_permalink( $product->get_id() ) );
	?>
	<div class="sc-catalog-card__actions">
		<a href="<?php echo $permalink; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>" class="sc-btn sc-btn--outline sc-btn--full">
			<?php esc_html_e( 'View Product Details', 'spicecraft' ); ?> &rarr;
		</a>
	</div>
	<?php
}

/**
 * Output Lead Enquiry Call-To-Action buttons in place of Add-to-Cart.
 * Provides Primary CTA (WhatsApp) and Secondary CTA (Product/Trade Enquiry Email).
 */
function spicecraft_catalog_single_enquiry_cta() {
	global $product;
	if ( ! $product ) {
		return;
	}

	$product_id    = $product->get_id();
	$product_name  = $product->get_name();
	$sku           = $product->get_sku();
	$whatsapp_url  = spicecraft_get_whatsapp_enquiry_url( $product_name );
	$contact_email = spicecraft_get_theme_option( 'spicecraft_export_email', get_option( 'admin_email' ) );

	$subject = sprintf(
		/* translators: 1: Product name, 2: SKU */
		esc_html__( 'Product & Trade Enquiry: %1$s (SKU: %2$s)', 'spicecraft' ),
		$product_name,
		! empty( $sku ) ? $sku : 'N/A'
	);

	$mail_lines = array(
		esc_html__( 'Hello SpiceCraft Sales Team,', 'spicecraft' ),
		'',
		sprintf(
			/* translators: 1: Product Name, 2: SKU */
			esc_html__( 'I would like to enquire about bulk pricing, minimum order quantities, and institutional supply for %1$s (SKU: %2$s).', 'spicecraft' ),
			$product_name,
			! empty( $sku ) ? $sku : 'N/A'
		),
		'',
		sprintf(
			/* translators: %s: Product URL */
			esc_html__( 'Product Link: %s', 'spicecraft' ),
			get_permalink( $product_id )
		),
		'',
		esc_html__( 'Company / Buyer Name:', 'spicecraft' ),
		esc_html__( 'Location / Country:', 'spicecraft' ),
		esc_html__( 'Required Pack Size / Volume:', 'spicecraft' ),
		esc_html__( 'Contact Number:', 'spicecraft' ),
	);
	$mail_body  = implode( "\r\n", $mail_lines );
	$mailto_url = 'mailto:' . sanitize_email( $contact_email ) . '?subject=' . rawurlencode( $subject ) . '&body=' . rawurlencode( $mail_body );
	?>
	<div class="sc-product-enquiry" id="product-enquiry-section">
		<div class="sc-product-enquiry__header">
			<span class="sc-product-enquiry__badge"><?php esc_html_e( 'B2B & Retail Distribution', 'spicecraft' ); ?></span>
			<h3 class="sc-product-enquiry__title"><?php esc_html_e( 'Direct Manufacturer Enquiry', 'spicecraft' ); ?></h3>
			<p class="sc-product-enquiry__note"><?php esc_html_e( 'Connect directly with our spice specialists for commercial packs, institutional bulk supply, or export orders.', 'spicecraft' ); ?></p>
		</div>

		<div class="sc-product-enquiry__buttons">
			<!-- Primary CTA: WhatsApp Enquiry -->
			<a href="<?php echo esc_url( $whatsapp_url ); ?>" target="_blank" rel="noopener noreferrer" class="sc-btn sc-btn--whatsapp sc-btn--lg">
				<svg class="sc-icon" width="20" height="20" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
					<path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/>
				</svg>
				<span><?php esc_html_e( 'Enquire on WhatsApp', 'spicecraft' ); ?></span>
			</a>

			<!-- Secondary CTA: Email Trade Enquiry -->
			<a href="<?php echo esc_url( $mailto_url ); ?>" class="sc-btn sc-btn--secondary sc-btn--lg">
				<svg class="sc-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
					<path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
					<polyline points="22,6 12,13 2,6"/>
				</svg>
				<span><?php esc_html_e( 'Email Product Enquiry', 'spicecraft' ); ?></span>
			</a>
		</div>

		<?php
		/**
		 * Pluggable Action: Allows subsequent modules (favourites, spec sheets, nutrition tables)
		 * to attach seamlessly.
		 */
		do_action( 'spicecraft_catalog_after_enquiry_cta', $product_id );
		?>
	</div>
	<?php
}

/**
 * ============================================================================
 * 3. PRODUCT PRICE STRATEGY ARCHITECTURE
 * ============================================================================
 */

/**
 * Filter price display to support flexible FMCG catalog modes:
 * - 'show_price'          : Display regular price if entered.
 * - 'hide_price'          : Strip price entirely.
 * - 'contact_for_price'   : Display "Contact for Price" label.
 * - 'enquire_for_details' : Display "Enquire for Details" label (Default).
 *
 * @param string     $price   Raw WooCommerce price string.
 * @param WC_Product $product Current product object.
 * @return string Formatted catalog price output.
 */
function spicecraft_catalog_price_html( $price, $product ) {
	$strategy = apply_filters( 'spicecraft_product_price_strategy', 'enquire_for_details', $product );

	switch ( $strategy ) {
		case 'show_price':
			return ! empty( $price ) ? '<span class="sc-price-display">' . $price . '</span>' : '<span class="sc-price-enquire">' . esc_html__( 'Price on Request', 'spicecraft' ) . '</span>';

		case 'hide_price':
			return '';

		case 'contact_for_price':
			return '<span class="sc-price-enquire">' . esc_html__( 'Contact for Price', 'spicecraft' ) . '</span>';

		case 'enquire_for_details':
		default:
			return '<span class="sc-price-enquire">' . esc_html__( 'Enquire for Details', 'spicecraft' ) . '</span>';
	}
}
add_filter( 'woocommerce_get_price_html', 'spicecraft_catalog_price_html', 99, 2 );

/**
 * ============================================================================
 * 4. ROUTE INTERCEPTION: REDIRECT CART & CHECKOUT PAGES
 * ============================================================================
 */

/**
 * Intercept manual visitor access to /cart and /checkout.
 * Redirects visitors safely to the product catalog archive or home page.
 */
function spicecraft_redirect_cart_and_checkout() {
	if ( is_cart() || is_checkout() ) {
		$shop_page_url = wc_get_page_permalink( 'shop' );
		$target_url    = ( $shop_page_url && ! is_wp_error( $shop_page_url ) ) ? $shop_page_url : home_url( '/' );

		wp_safe_redirect( $target_url, 302 );
		exit;
	}
}
add_action( 'template_redirect', 'spicecraft_redirect_cart_and_checkout' );

/**
 * ============================================================================
 * 5. PERFORMANCE: UNLOAD TRANSACTIONAL SCRIPTS & FRAGMENTS
 * ============================================================================
 */

/**
 * Dequeue transactional scripts that are obsolete in a catalog-only model.
 */
function spicecraft_dequeue_transactional_assets() {
	wp_dequeue_script( 'wc-cart-fragments' );
	wp_dequeue_script( 'wc-add-to-cart' );
	wp_dequeue_script( 'wc-add-to-cart-variation' );
	wp_dequeue_script( 'wc-cart' );
	wp_dequeue_script( 'wc-checkout' );
}
add_action( 'wp_enqueue_scripts', 'spicecraft_dequeue_transactional_assets', 99 );
