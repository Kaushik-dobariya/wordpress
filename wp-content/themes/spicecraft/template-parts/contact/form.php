<?php
/**
 * SpiceCraft Contact Page - Form Section
 *
 * Embeds the security-hardened SpiceCraft B2B Enquiry & Lead Engine Form.
 * Supports:
 * - Server-side product pre-population if coming from single product page (?product_id=X)
 * - Optional product selection dropdown if arriving directly to Contact Us
 * - Full AJAX submission with honeypot, rate limiting, and email dispatch
 *
 * @package SpiceCraft
 * @since 1.5.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// 1. Resolve product context from GET parameter if arriving from a product page
$url_product_id   = isset( $_GET['product_id'] ) ? absint( $_GET['product_id'] ) : 0;
$url_product_name = '';
$url_product_sku  = '';
$url_product_url  = '';
$url_pack_size    = isset( $_GET['pack_size'] ) ? sanitize_text_field( wp_unslash( $_GET['pack_size'] ) ) : '';

if ( $url_product_id && function_exists( 'wc_get_product' ) ) {
	$prod = wc_get_product( $url_product_id );
	if ( $prod && 'publish' === $prod->get_status() ) {
		$url_product_name = $prod->get_name();
		$url_product_sku  = $prod->get_sku();
		$url_product_url  = get_permalink( $url_product_id );
	} else {
		// Reset invalid or unpublished product ID
		$url_product_id = 0;
	}
}

// Default enquiry classification: if product is pre-selected, default to 'product', otherwise 'general'
$default_enquiry_type = $url_product_id ? 'product' : 'general';
?>

<section class="sc-contact-form-section" id="contact-form-section" aria-labelledby="contact-form-heading">
	<div class="sc-container sc-container--narrow">
		<header class="sc-section-header sc-section-header--center">
			<span class="sc-eyebrow"><?php esc_html_e( 'Direct B2B Transmission', 'spicecraft' ); ?></span>
			<h2 id="contact-form-heading" class="sc-section-title">
				<?php
				if ( $url_product_name ) {
					/* translators: %s: Product Name */
					printf( esc_html__( 'Commercial Enquiry for %s', 'spicecraft' ), esc_html( $url_product_name ) );
				} else {
					esc_html_e( 'Submit Commercial Trade Enquiry', 'spicecraft' );
				}
				?>
			</h2>
			<p class="sc-section-subtitle">
				<?php esc_html_e( 'Fill in your procurement specifications below. Your request will be directly routed to our trade desk and followed up within 24 business hours.', 'spicecraft' ); ?>
			</p>
		</header>

		<!-- Pre-populated Product Notice (if arriving with product parameter) -->
		<?php if ( $url_product_id && $url_product_name ) : ?>
			<div class="sc-contact-product-notice">
				<div class="sc-contact-product-notice__icon" aria-hidden="true">
					<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
						<path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path>
						<polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline>
						<line x1="12" y1="22.08" x2="12" y2="12"></line>
					</svg>
				</div>
				<div class="sc-contact-product-notice__body">
					<span class="sc-contact-product-notice__tag"><?php esc_html_e( 'Associated Product', 'spicecraft' ); ?></span>
					<strong class="sc-contact-product-notice__title"><?php echo esc_html( $url_product_name ); ?></strong>
					<?php if ( $url_product_sku ) : ?>
						<span class="sc-contact-product-notice__sku"><code><?php echo esc_html( $url_product_sku ); ?></code></span>
					<?php endif; ?>
					<?php if ( $url_pack_size ) : ?>
						<span class="sc-contact-product-notice__pack"><?php echo esc_html( $url_pack_size ); ?></span>
					<?php endif; ?>
				</div>
				<a href="<?php echo esc_url( remove_query_arg( array( 'product_id', 'pack_size' ) ) ); ?>" class="sc-contact-product-notice__clear" title="<?php esc_attr_e( 'Clear product association', 'spicecraft' ); ?>">
					&times; <?php esc_html_e( 'Clear', 'spicecraft' ); ?>
				</a>
			</div>
		<?php endif; ?>

		<!-- Form Card Container -->
		<div class="sc-contact-form-card">
			<?php
			get_template_part(
				'template-parts/components/enquiry-form',
				null,
				array(
					'form_id'              => 'sc-contact-page-form',
					'product_id'           => $url_product_id,
					'product_name'         => $url_product_name,
					'product_sku'          => $url_product_sku,
					'product_url'          => $url_product_url,
					'enquiry_type'         => $default_enquiry_type,
					'is_modal'             => false,
					'allow_product_select' => empty( $url_product_id ), // Allow dropdown if no product in URL
				)
			);
			?>
		</div>
	</div>
</section>
