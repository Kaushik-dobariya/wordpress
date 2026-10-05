<?php
/**
 * SpiceCraft Lead & Product Enquiry Form Component
 *
 * Fully accessible, security-hardened enquiry form supporting both single product
 * trade enquiries and general institutional/export business inquiries.
 *
 * @package SpiceCraft
 * @since 1.0.0
 *
 * @var array $args Optional arguments: 'form_id', 'product_id', 'product_name', 'product_sku', 'enquiry_type', 'is_modal'
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$form_id      = ! empty( $args['form_id'] ) ? sanitize_html_class( $args['form_id'] ) : 'sc-enquiry-form';
$product_id   = ! empty( $args['product_id'] ) ? absint( $args['product_id'] ) : 0;
$product_name = ! empty( $args['product_name'] ) ? sanitize_text_field( $args['product_name'] ) : '';
$product_sku  = ! empty( $args['product_sku'] ) ? sanitize_text_field( $args['product_sku'] ) : '';
$product_url  = ! empty( $args['product_url'] ) ? esc_url( $args['product_url'] ) : '';
$enquiry_type = ! empty( $args['enquiry_type'] ) ? sanitize_key( $args['enquiry_type'] ) : ( $product_id ? 'product' : 'general' );
$is_modal     = ! empty( $args['is_modal'] );

// If product ID is provided but product name is not, retrieve server-side product details
if ( $product_id && empty( $product_name ) && function_exists( 'wc_get_product' ) ) {
	$prod = wc_get_product( $product_id );
	if ( $prod ) {
		$product_name = $prod->get_name();
		$product_sku  = $prod->get_sku();
		$product_url  = get_permalink( $product_id );
	}
}

// WhatsApp URL for quick-action fallback
$whatsapp_url = function_exists( 'spicecraft_get_whatsapp_enquiry_url' )
	? spicecraft_get_whatsapp_enquiry_url( $product_name )
	: '';

$customer_types = function_exists( 'spicecraft_get_customer_types' )
	? spicecraft_get_customer_types()
	: array(
		'retailer'          => __( 'Retailer', 'spicecraft' ),
		'distributor'       => __( 'Distributor', 'spicecraft' ),
		'wholesaler'        => __( 'Wholesaler', 'spicecraft' ),
		'importer'          => __( 'Importer', 'spicecraft' ),
		'exporter'          => __( 'Exporter', 'spicecraft' ),
		'food_manufacturer' => __( 'Food Manufacturer', 'spicecraft' ),
		'restaurant'        => __( 'Restaurant / Hospitality', 'spicecraft' ),
		'other'             => __( 'Other', 'spicecraft' ),
	);

$pack_sizes = array();
if ( $product_id && function_exists( 'wc_get_product' ) && function_exists( 'spicecraft_get_product_pack_sizes' ) ) {
	$prod = wc_get_product( $product_id );
	if ( $prod ) {
		$pack_sizes = spicecraft_get_product_pack_sizes( $prod );
	}
}

$enquiry_types = function_exists( 'spicecraft_get_enquiry_types' )
	? spicecraft_get_enquiry_types()
	: array(
		'product'     => __( 'Product Enquiry', 'spicecraft' ),
		'bulk'        => __( 'Bulk Sourcing', 'spicecraft' ),
		'export'      => __( 'Export Enquiry', 'spicecraft' ),
		'private'     => __( 'Private Label / OEM', 'spicecraft' ),
		'partnership' => __( 'Distribution / Partnership', 'spicecraft' ),
		'general'     => __( 'General Business Enquiry', 'spicecraft' ),
	);
?>

<form id="<?php echo esc_attr( $form_id ); ?>" class="sc-enquiry-form" method="post" novalidate>
	<!-- Security Nonce & Action -->
	<?php wp_nonce_field( 'spicecraft_enquiry_action', 'spicecraft_enquiry_nonce' ); ?>
	<input type="hidden" name="action" value="spicecraft_submit_enquiry" />
	<input type="hidden" name="consent_required" value="1" />

	<!-- Server Validation / Product Tracking -->
	<input type="hidden" name="product_id" class="sc-enquiry-product-id" value="<?php echo esc_attr( $product_id ); ?>" />
	<input type="hidden" name="page_url" class="sc-enquiry-page-url" value="<?php echo esc_url( $product_url ? $product_url : ( isset( $_SERVER['REQUEST_URI'] ) ? esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '' ) ); ?>" />
	<input type="hidden" name="lead_source" class="sc-enquiry-lead-source" value="<?php echo esc_attr( $product_id ? 'Product Detail Page' : 'Website Enquiry' ); ?>" />

	<!-- Anti-Spam Invisible Honeypot Field -->
	<div class="sc-visually-hidden" aria-hidden="true" style="position: absolute; left: -9999px; width: 1px; height: 1px; overflow: hidden;">
		<label for="<?php echo esc_attr( $form_id ); ?>_hp"><?php esc_html_e( 'Leave this field blank', 'spicecraft' ); ?></label>
		<input type="text" name="_sc_enquiry_hp" id="<?php echo esc_attr( $form_id ); ?>_hp" value="" tabindex="-1" autocomplete="off" />
	</div>

	<!-- Selected Product Preview Banner (Auto-populated for Product Enquiries) -->
	<div class="sc-enquiry-product-banner <?php echo $product_id ? 'is-active' : 'is-hidden'; ?>" id="<?php echo esc_attr( $form_id ); ?>_product_banner">
		<div class="sc-enquiry-product-banner__icon" aria-hidden="true">
			<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
				<path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path>
				<polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline>
				<line x1="12" y1="22.08" x2="12" y2="12"></line>
			</svg>
		</div>
		<div class="sc-enquiry-product-banner__info">
			<span class="sc-enquiry-product-banner__label"><?php esc_html_e( 'Enquiring About Product', 'spicecraft' ); ?></span>
			<h4 class="sc-enquiry-product-banner__title sc-enquiry-display-name">
				<?php echo esc_html( $product_name ? $product_name : __( 'No product selected', 'spicecraft' ) ); ?>
			</h4>
			<span class="sc-enquiry-product-banner__sku sc-enquiry-display-sku">
				<?php if ( ! empty( $product_sku ) ) : ?>
					<?php /* translators: %s: SKU */ printf( esc_html__( 'SKU: %s', 'spicecraft' ), esc_html( $product_sku ) ); ?>
				<?php endif; ?>
			</span>
			<span class="sc-enquiry-product-banner__pack sc-enquiry-display-pack" style="display: none;"></span>
		</div>
		<?php if ( $is_modal ) : ?>
			<button type="button" class="sc-enquiry-product-banner__clear sc-enquiry-clear-product" title="<?php esc_attr_e( 'Switch to general enquiry', 'spicecraft' ); ?>" aria-label="<?php esc_attr_e( 'Clear selected product for general enquiry', 'spicecraft' ); ?>">
				&times;
			</button>
		<?php endif; ?>
	</div>

	<!-- Live Alert / Feedback Notification Box -->
	<div class="sc-enquiry-feedback" id="<?php echo esc_attr( $form_id ); ?>_feedback" role="alert" aria-live="polite" style="display: none;"></div>

	<!-- Form Fields Grid -->
	<div class="sc-enquiry-fields">

		<!-- Row 1: Customer Name & Company -->
		<div class="sc-enquiry-row sc-enquiry-row--2col">
			<div class="sc-enquiry-field">
				<label for="<?php echo esc_attr( $form_id ); ?>_name" class="sc-enquiry-label">
					<?php esc_html_e( 'Full Name', 'spicecraft' ); ?> <span class="sc-required" aria-hidden="true">*</span>
				</label>
				<input type="text" 
					id="<?php echo esc_attr( $form_id ); ?>_name" 
					name="full_name" 
					class="sc-enquiry-input" 
					placeholder="<?php esc_attr_e( 'e.g. John Doe', 'spicecraft' ); ?>" 
					required 
					autocomplete="name" />
			</div>

			<div class="sc-enquiry-field">
				<label for="<?php echo esc_attr( $form_id ); ?>_company" class="sc-enquiry-label">
					<?php esc_html_e( 'Company / Organisation', 'spicecraft' ); ?>
				</label>
				<input type="text" 
					id="<?php echo esc_attr( $form_id ); ?>_company" 
					name="company" 
					class="sc-enquiry-input" 
					placeholder="<?php esc_attr_e( 'e.g. Global Foods Ltd.', 'spicecraft' ); ?>" 
					autocomplete="organization" />
			</div>
		</div>

		<!-- Row 2: Email & Phone -->
		<div class="sc-enquiry-row sc-enquiry-row--2col">
			<div class="sc-enquiry-field">
				<label for="<?php echo esc_attr( $form_id ); ?>_email" class="sc-enquiry-label">
					<?php esc_html_e( 'Business Email', 'spicecraft' ); ?> <span class="sc-required" aria-hidden="true">*</span>
				</label>
				<input type="email" 
					id="<?php echo esc_attr( $form_id ); ?>_email" 
					name="email" 
					class="sc-enquiry-input" 
					placeholder="<?php esc_attr_e( 'name@company.com', 'spicecraft' ); ?>" 
					required 
					autocomplete="email" />
			</div>

			<div class="sc-enquiry-field">
				<label for="<?php echo esc_attr( $form_id ); ?>_phone" class="sc-enquiry-label">
					<?php esc_html_e( 'Phone / Mobile', 'spicecraft' ); ?> <span class="sc-required" aria-hidden="true">*</span>
				</label>
				<input type="tel" 
					id="<?php echo esc_attr( $form_id ); ?>_phone" 
					name="phone" 
					class="sc-enquiry-input" 
					placeholder="<?php esc_attr_e( '+1 234 567 8900 (with country code)', 'spicecraft' ); ?>" 
					required 
					autocomplete="tel" />
			</div>
		</div>

		<!-- Row 3: Country & City / State -->
		<div class="sc-enquiry-row sc-enquiry-row--2col">
			<div class="sc-enquiry-field">
				<label for="<?php echo esc_attr( $form_id ); ?>_country" class="sc-enquiry-label">
					<?php esc_html_e( 'Country / Destination', 'spicecraft' ); ?> <span class="sc-required" aria-hidden="true">*</span>
				</label>
				<input type="text" 
					id="<?php echo esc_attr( $form_id ); ?>_country" 
					name="country" 
					class="sc-enquiry-input" 
					placeholder="<?php esc_attr_e( 'e.g. United States, UAE, Germany, India', 'spicecraft' ); ?>" 
					required
					autocomplete="country-name" />
			</div>

			<div class="sc-enquiry-field">
				<label for="<?php echo esc_attr( $form_id ); ?>_city" class="sc-enquiry-label">
					<?php esc_html_e( 'City / State / Region', 'spicecraft' ); ?>
				</label>
				<input type="text" 
					id="<?php echo esc_attr( $form_id ); ?>_city" 
					name="city" 
					class="sc-enquiry-input" 
					placeholder="<?php esc_attr_e( 'e.g. Dubai, New York, Hamburg', 'spicecraft' ); ?>" />
			</div>
		</div>

		<?php if ( empty( $product_id ) && ! empty( $args['allow_product_select'] ) && function_exists( 'wc_get_products' ) ) : ?>
			<?php
			$available_prods = wc_get_products(
				array(
					'status'  => 'publish',
					'limit'   => 100,
					'orderby' => 'title',
					'order'   => 'ASC',
				)
			);
			if ( ! empty( $available_prods ) ) :
				?>
				<div class="sc-enquiry-field sc-enquiry-product-select-field" style="margin-bottom: 16px;">
					<label for="<?php echo esc_attr( $form_id ); ?>_select_product" class="sc-enquiry-label">
						<?php esc_html_e( 'Specific Product of Interest', 'spicecraft' ); ?> <span class="sc-optional">(<?php esc_html_e( 'Optional', 'spicecraft' ); ?>)</span>
					</label>
					<select id="<?php echo esc_attr( $form_id ); ?>_select_product" name="product_select_dropdown" class="sc-enquiry-select sc-enquiry-product-select">
						<option value=""><?php esc_html_e( '— Select a Spice Product (or General Sourcing Enquiry) —', 'spicecraft' ); ?></option>
						<?php foreach ( $available_prods as $p_item ) : ?>
							<option value="<?php echo esc_attr( $p_item->get_id() ); ?>" data-sku="<?php echo esc_attr( $p_item->get_sku() ); ?>" data-name="<?php echo esc_attr( $p_item->get_name() ); ?>" data-url="<?php echo esc_url( get_permalink( $p_item->get_id() ) ); ?>">
								<?php echo esc_html( $p_item->get_name() ); ?><?php echo $p_item->get_sku() ? ' (SKU: ' . esc_html( $p_item->get_sku() ) . ')' : ''; ?>
							</option>
						<?php endforeach; ?>
					</select>
				</div>
			<?php endif; ?>
		<?php endif; ?>

		<!-- Row 4: Customer Type & Enquiry Classification -->
		<div class="sc-enquiry-row sc-enquiry-row--2col">
			<div class="sc-enquiry-field">
				<label for="<?php echo esc_attr( $form_id ); ?>_customer_type" class="sc-enquiry-label">
					<?php esc_html_e( 'Customer Type', 'spicecraft' ); ?>
				</label>
				<select id="<?php echo esc_attr( $form_id ); ?>_customer_type" name="customer_type" class="sc-enquiry-select">
					<option value=""><?php esc_html_e( 'Select Business / Customer Type', 'spicecraft' ); ?></option>
					<?php foreach ( $customer_types as $c_key => $c_label ) : ?>
						<option value="<?php echo esc_attr( $c_key ); ?>"><?php echo esc_html( $c_label ); ?></option>
					<?php endforeach; ?>
				</select>
			</div>

			<div class="sc-enquiry-field">
				<label for="<?php echo esc_attr( $form_id ); ?>_type" class="sc-enquiry-label">
					<?php esc_html_e( 'Enquiry Classification', 'spicecraft' ); ?>
				</label>
				<select id="<?php echo esc_attr( $form_id ); ?>_type" name="enquiry_type" class="sc-enquiry-select">
					<?php foreach ( $enquiry_types as $type_key => $type_label ) : ?>
						<option value="<?php echo esc_attr( $type_key ); ?>" <?php selected( $enquiry_type, $type_key ); ?>>
							<?php echo esc_html( $type_label ); ?>
						</option>
					<?php endforeach; ?>
				</select>
			</div>
		</div>

		<!-- Row 5: Required Quantity & Pack Size -->
		<div class="sc-enquiry-row sc-enquiry-row--2col">
			<div class="sc-enquiry-field">
				<label for="<?php echo esc_attr( $form_id ); ?>_qty" class="sc-enquiry-label">
					<?php esc_html_e( 'Required Volume / Quantity', 'spicecraft' ); ?>
				</label>
				<input type="text" 
					id="<?php echo esc_attr( $form_id ); ?>_qty" 
					name="quantity" 
					class="sc-enquiry-input" 
					placeholder="<?php esc_attr_e( 'e.g. 500 kg, 2 Metric Tons, 200 Cartons', 'spicecraft' ); ?>" />
			</div>

			<div class="sc-enquiry-field">
				<label for="<?php echo esc_attr( $form_id ); ?>_pack_size" class="sc-enquiry-label">
					<?php esc_html_e( 'Pack Size', 'spicecraft' ); ?>
				</label>
				<?php if ( ! empty( $pack_sizes ) ) : ?>
					<select id="<?php echo esc_attr( $form_id ); ?>_pack_size" name="pack_size" class="sc-enquiry-select sc-enquiry-pack-select">
						<option value=""><?php esc_html_e( 'Select pack size (or enter custom packaging)', 'spicecraft' ); ?></option>
						<?php foreach ( $pack_sizes as $psize ) : ?>
							<option value="<?php echo esc_attr( $psize ); ?>"><?php echo esc_html( $psize ); ?></option>
						<?php endforeach; ?>
					</select>
				<?php else : ?>
					<input type="text" 
						id="<?php echo esc_attr( $form_id ); ?>_pack_size" 
						name="pack_size" 
						class="sc-enquiry-input sc-enquiry-pack-input" 
						placeholder="<?php esc_attr_e( 'e.g. 100g, 250g, 500g, 1kg, 25kg bulk', 'spicecraft' ); ?>" />
				<?php endif; ?>
			</div>
		</div>

		<!-- Row 6: Packaging Requirement & WhatsApp -->
		<div class="sc-enquiry-row sc-enquiry-row--2col">
			<div class="sc-enquiry-field">
				<label for="<?php echo esc_attr( $form_id ); ?>_packaging" class="sc-enquiry-label">
					<?php esc_html_e( 'Packaging Specification', 'spicecraft' ); ?>
				</label>
				<input type="text" 
					id="<?php echo esc_attr( $form_id ); ?>_packaging" 
					name="packaging" 
					class="sc-enquiry-input" 
					placeholder="<?php esc_attr_e( 'e.g. 25kg PP bags, 100g consumer pouches, custom OEM', 'spicecraft' ); ?>" />
			</div>

			<div class="sc-enquiry-field">
				<label for="<?php echo esc_attr( $form_id ); ?>_wa" class="sc-enquiry-label">
					<?php esc_html_e( 'WhatsApp Number', 'spicecraft' ); ?> <span class="sc-optional">(<?php esc_html_e( 'Optional', 'spicecraft' ); ?>)</span>
				</label>
				<input type="tel" 
					id="<?php echo esc_attr( $form_id ); ?>_wa" 
					name="whatsapp_number" 
					class="sc-enquiry-input" 
					placeholder="<?php esc_attr_e( 'Include country code if different from phone', 'spicecraft' ); ?>" />
			</div>
		</div>

		<!-- Row 7: Preferred Contact Method -->
		<div class="sc-enquiry-field">
			<span class="sc-enquiry-label"><?php esc_html_e( 'Preferred Communication Channel', 'spicecraft' ); ?></span>
			<div class="sc-enquiry-radio-group" role="radiogroup" aria-label="<?php esc_attr_e( 'Preferred Contact Channel', 'spicecraft' ); ?>">
				<label class="sc-enquiry-radio-pill">
					<input type="radio" name="preferred_contact" value="email" checked />
					<span>
						<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path><polyline points="22,6 12,13 2,6"></polyline></svg>
						<?php esc_html_e( 'Email', 'spicecraft' ); ?>
					</span>
				</label>
				<label class="sc-enquiry-radio-pill">
					<input type="radio" name="preferred_contact" value="phone" />
					<span>
						<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path></svg>
						<?php esc_html_e( 'Phone Call', 'spicecraft' ); ?>
					</span>
				</label>
				<label class="sc-enquiry-radio-pill">
					<input type="radio" name="preferred_contact" value="whatsapp" />
					<span>
						<svg width="15" height="15" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/></svg>
						<?php esc_html_e( 'WhatsApp', 'spicecraft' ); ?>
					</span>
				</label>
			</div>
		</div>

		<!-- Row 8: Message -->
		<div class="sc-enquiry-field">
			<label for="<?php echo esc_attr( $form_id ); ?>_message" class="sc-enquiry-label">
				<?php esc_html_e( 'Tell us about your requirement', 'spicecraft' ); ?> <span class="sc-required" aria-hidden="true">*</span>
			</label>
			<textarea id="<?php echo esc_attr( $form_id ); ?>_message" 
				name="message" 
				class="sc-enquiry-textarea" 
				rows="4" 
				placeholder="<?php esc_attr_e( 'Please detail your requirements, target destination port, testing or certification needs, and any questions for our specialists...', 'spicecraft' ); ?>" 
				required></textarea>
		</div>

		<!-- Row 9: Privacy / Consent Checkbox -->
		<div class="sc-enquiry-field sc-enquiry-consent-field">
			<label class="sc-enquiry-checkbox-label" for="<?php echo esc_attr( $form_id ); ?>_consent">
				<input type="checkbox" 
					name="consent" 
					id="<?php echo esc_attr( $form_id ); ?>_consent" 
					value="1" 
					class="sc-enquiry-checkbox" 
					required />
				<span class="sc-enquiry-checkbox-text">
					<?php esc_html_e( 'I agree to be contacted regarding this enquiry.', 'spicecraft' ); ?> <span class="sc-required" aria-hidden="true">*</span>
				</span>
			</label>
		</div>

	</div><!-- .sc-enquiry-fields -->

	<!-- Form Footer & Action Buttons -->
	<div class="sc-enquiry-footer">
		<div class="sc-enquiry-privacy-note">
			<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
			<span><?php esc_html_e( 'Your privacy is strictly guarded. We never share customer or commercial inquiry records.', 'spicecraft' ); ?></span>
		</div>

		<div class="sc-enquiry-actions">
			<button type="submit" class="sc-btn sc-btn--primary sc-btn--lg sc-enquiry-submit-btn">
				<span class="sc-btn__spinner" aria-hidden="true"></span>
				<span class="sc-btn__text"><?php esc_html_e( 'Submit Enquiry', 'spicecraft' ); ?></span>
			</button>

			<?php if ( ! empty( $whatsapp_url ) ) : ?>
				<a href="<?php echo esc_url( $whatsapp_url ); ?>" 
					class="sc-btn sc-btn--whatsapp sc-enquiry-whatsapp-btn" 
					target="_blank" 
					rel="noopener noreferrer">
					<svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/></svg>
					<span><?php esc_html_e( 'Or Chat on WhatsApp', 'spicecraft' ); ?></span>
				</a>
			<?php endif; ?>
		</div>
	</div>

</form>
