<?php
/**
 * SpiceCraft Core - Product Enquiry & Lead Processing Engine
 *
 * Handles:
 * 1. AJAX and POST submission of product and general trade enquiries
 * 2. Strict server-side validation, anti-spam honeypot, and rate-limiting
 * 3. Server-side WooCommerce product resolution and validation
 * 4. Confidential storage in private 'spicecraft_enquiry' CPT
 * 5. Dynamic notification email dispatch to current backend configured recipient
 * 6. Optional customer confirmation autoresponder
 *
 * @package SpiceCraft_Core
 * @since 1.4.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class SpiceCraft_Enquiry_Engine {

	/**
	 * Post type key.
	 */
	const POST_TYPE = 'spicecraft_enquiry';

	/**
	 * Rate limiting threshold (seconds between submissions from same IP).
	 */
	const RATE_LIMIT_SECONDS = 15;

	/**
	 * Singleton Instance.
	 *
	 * @var SpiceCraft_Enquiry_Engine|null
	 */
	private static $instance = null;

	/**
	 * Get Singleton Instance.
	 *
	 * @return SpiceCraft_Enquiry_Engine
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor.
	 */
	private function __construct() {
		// AJAX Endpoints
		add_action( 'wp_ajax_spicecraft_submit_enquiry', array( $this, 'handle_submission' ) );
		add_action( 'wp_ajax_nopriv_spicecraft_submit_enquiry', array( $this, 'handle_submission' ) );

		// Standard POST fallback
		add_action( 'admin_post_spicecraft_submit_enquiry', array( $this, 'handle_submission' ) );
		add_action( 'admin_post_nopriv_spicecraft_submit_enquiry', array( $this, 'handle_submission' ) );
	}

	/**
	 * Handle incoming enquiry submission (HTTP POST / AJAX).
	 */
	public function handle_submission() {
		// 1. Verify Nonce
		$nonce = isset( $_POST['spicecraft_enquiry_nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['spicecraft_enquiry_nonce'] ) ) : '';
		if ( ! wp_verify_nonce( $nonce, 'spicecraft_enquiry_action' ) ) {
			$this->send_response( false, __( 'Security validation failed. Please refresh the page and try again.', 'spicecraft-core' ), 403 );
			return;
		}

		// 2. Anti-Spam Honeypot Trap
		// Form contains hidden input '_sc_enquiry_hp'. Legitimate users do not fill it.
		if ( ! empty( $_POST['_sc_enquiry_hp'] ) ) {
			// Silently trap bot with a mock success response
			$this->send_response( true, __( 'Thank you for your enquiry. We have received your request.', 'spicecraft-core' ) );
			return;
		}

		// 3. Submitter IP & Rate Limiting
		$ip = $this->get_client_ip();
		$rate_transient = 'sc_enq_rate_' . md5( $ip );
		if ( get_transient( $rate_transient ) ) {
			$this->send_response( false, __( 'You submitted an enquiry very recently. Please wait a moment before sending another request.', 'spicecraft-core' ), 429 );
			return;
		}

		// Consent validation for frontend submissions
		if ( ! empty( $_POST['consent_required'] ) && empty( $_POST['consent'] ) ) {
			$this->send_response( false, __( 'Please agree to be contacted regarding this enquiry.', 'spicecraft-core' ), 400, array( 'field' => 'consent' ) );
			return;
		}

		// 4. Process submission data
		$result = $this->process_submission( $_POST );

		if ( is_wp_error( $result ) ) {
			$error_data = $result->get_error_data();
			$this->send_response( false, $result->get_error_message(), 400, is_array( $error_data ) ? $error_data : array() );
			return;
		}

		// Set rate limit transient (15 seconds)
		set_transient( $rate_transient, 1, self::RATE_LIMIT_SECONDS );

		// Send Success Response
		$success_msg = __( 'Thank you for your enquiry. We have received your request and our team will get back to you shortly.', 'spicecraft-core' );
		$this->send_response( true, $success_msg, 200, array( 'enquiry_id' => $result ) );
	}

	/**
	 * Programmatically process and validate an enquiry submission.
	 *
	 * @param array $data Raw submission data (or $_POST).
	 * @return int|\WP_Error Post ID on success, WP_Error on failure.
	 */
	public function process_submission( array $data ) {
		// 1. Sanitize Fields
		$name     = isset( $data['full_name'] ) ? sanitize_text_field( wp_unslash( $data['full_name'] ) ) : '';
		$email    = isset( $data['email'] ) ? sanitize_email( wp_unslash( $data['email'] ) ) : '';
		$phone    = isset( $data['phone'] ) ? sanitize_text_field( wp_unslash( $data['phone'] ) ) : '';
		$message  = isset( $data['message'] ) ? sanitize_textarea_field( wp_unslash( $data['message'] ) ) : '';
		$company  = isset( $data['company'] ) ? sanitize_text_field( wp_unslash( $data['company'] ) ) : '';
		$country  = isset( $data['country'] ) ? sanitize_text_field( wp_unslash( $data['country'] ) ) : '';
		$state    = isset( $data['state'] ) ? sanitize_text_field( wp_unslash( $data['state'] ) ) : '';
		$city     = isset( $data['city'] ) ? sanitize_text_field( wp_unslash( $data['city'] ) ) : '';
		$whatsapp = isset( $data['whatsapp_number'] ) ? sanitize_text_field( wp_unslash( $data['whatsapp_number'] ) ) : ( isset( $data['whatsapp'] ) ? sanitize_text_field( wp_unslash( $data['whatsapp'] ) ) : '' );

		$customer_type = isset( $data['customer_type'] ) ? sanitize_key( wp_unslash( $data['customer_type'] ) ) : '';
		$valid_cust_types = function_exists( 'spicecraft_get_customer_types' ) ? array_keys( spicecraft_get_customer_types() ) : array();
		if ( ! in_array( $customer_type, $valid_cust_types, true ) ) {
			$customer_type = '';
		}

		$consent   = ! empty( $data['consent'] ) ? 1 : 0;
		$quantity  = isset( $data['quantity'] ) ? sanitize_text_field( wp_unslash( $data['quantity'] ) ) : '';
		$packaging = isset( $data['packaging'] ) ? sanitize_text_field( wp_unslash( $data['packaging'] ) ) : '';
		$pack_size = isset( $data['pack_size'] ) ? sanitize_text_field( wp_unslash( $data['pack_size'] ) ) : '';

		$contact_pref = isset( $data['preferred_contact'] ) ? sanitize_key( wp_unslash( $data['preferred_contact'] ) ) : 'email';
		if ( ! in_array( $contact_pref, array( 'email', 'phone', 'whatsapp' ), true ) ) {
			$contact_pref = 'email';
		}

		$enquiry_type = isset( $data['enquiry_type'] ) ? sanitize_key( wp_unslash( $data['enquiry_type'] ) ) : '';
		$valid_types  = function_exists( 'spicecraft_get_enquiry_types' ) ? array_keys( spicecraft_get_enquiry_types() ) : array( 'product', 'general', 'bulk', 'export', 'private', 'partnership' );
		if ( ! in_array( $enquiry_type, $valid_types, true ) ) {
			$enquiry_type = ! empty( $data['product_id'] ) ? 'product' : 'general';
		}

		// 2. Validation
		if ( empty( $name ) ) {
			return new \WP_Error( 'missing_name', __( 'Please enter your full name.', 'spicecraft-core' ), array( 'field' => 'full_name' ) );
		}

		if ( empty( $email ) || ! is_email( $email ) ) {
			return new \WP_Error( 'invalid_email', __( 'Please provide a valid business email address.', 'spicecraft-core' ), array( 'field' => 'email' ) );
		}

		// Reasonable international phone validation (must have at least 7 digits)
		$digits = preg_replace( '/\D/', '', $phone );
		if ( empty( $phone ) || strlen( $digits ) < 7 || strlen( $digits ) > 18 ) {
			return new \WP_Error( 'missing_phone', __( 'Please enter a valid telephone or mobile number with country code.', 'spicecraft-core' ), array( 'field' => 'phone' ) );
		}

		if ( empty( $message ) ) {
			return new \WP_Error( 'missing_message', __( 'Please enter a message describing your enquiry or requirements.', 'spicecraft-core' ), array( 'field' => 'message' ) );
		}

		// 3. Server-Side Product Validation
		$product_id       = 0;
		$product_name     = '';
		$product_sku      = '';
		$product_url      = '';
		$product_category = '';

		if ( ! empty( $data['product_id'] ) ) {
			$raw_product_id = absint( $data['product_id'] );
			$product_post   = get_post( $raw_product_id );

			// Strictly verify that the submitted product is a valid, published WooCommerce product
			if ( $product_post && 'product' === $product_post->post_type && 'publish' === $product_post->post_status ) {
				$product_id   = $raw_product_id;
				$product_name = get_the_title( $product_id );
				$product_url  = get_permalink( $product_id );

				if ( function_exists( 'wc_get_product' ) ) {
					$wc_product = wc_get_product( $product_id );
					if ( $wc_product ) {
						$product_sku = $wc_product->get_sku();
					}
				}

				$cats = get_the_terms( $product_id, 'product_cat' );
				if ( ! empty( $cats ) && ! is_wp_error( $cats ) ) {
					$product_category = $cats[0]->name;
				}

				if ( empty( $enquiry_type ) ) {
					$enquiry_type = 'product';
				}
			} else {
				return new \WP_Error( 'invalid_product', __( 'The selected product could not be verified. Please try again.', 'spicecraft-core' ), array( 'field' => 'product_id' ) );
			}
		}

		// 4. Source & Metadata Gathering
		$source     = isset( $data['lead_source'] ) ? sanitize_text_field( wp_unslash( $data['lead_source'] ) ) : ( isset( $data['source'] ) ? sanitize_text_field( wp_unslash( $data['source'] ) ) : ( $product_id ? 'Product Page' : 'Contact Form' ) );
		$source_url = isset( $data['page_url'] ) ? esc_url_raw( wp_unslash( $data['page_url'] ) ) : ( wp_get_referer() ? wp_get_referer() : '' );
		$ip         = $this->get_client_ip();

		// 5. Assemble Title & Insert Enquiry
		$subject_title = ! empty( $product_name ) ? $product_name : ( function_exists( 'spicecraft_get_enquiry_types' ) && isset( spicecraft_get_enquiry_types()[ $enquiry_type ] ) ? spicecraft_get_enquiry_types()[ $enquiry_type ] : __( 'General Enquiry', 'spicecraft-core' ) );
		$post_title    = sprintf( '%s — %s', $name, $subject_title );

		$enquiry_post_id = wp_insert_post(
			array(
				'post_title'   => $post_title,
				'post_content' => $message,
				'post_type'    => self::POST_TYPE,
				'post_status'  => 'publish', // Internal private record
			),
			true
		);

		if ( is_wp_error( $enquiry_post_id ) || ! $enquiry_post_id ) {
			return new \WP_Error( 'save_failed', __( 'An error occurred while saving your enquiry. Please contact us directly via email or WhatsApp.', 'spicecraft-core' ) );
		}

		if ( empty( $pack_size ) && $product_id ) {
			$prod_packs = get_post_meta( $product_id, '_spicecraft_pack_sizes', true );
			if ( ! empty( $prod_packs ) && is_array( $prod_packs ) ) {
				$pack_size = implode( ', ', $prod_packs );
			}
		}

		// 6. Store Detailed Metadata
		update_post_meta( $enquiry_post_id, '_sc_enquiry_name', $name );
		update_post_meta( $enquiry_post_id, '_sc_enquiry_customer_name', $name );
		update_post_meta( $enquiry_post_id, '_sc_enquiry_email', $email );
		update_post_meta( $enquiry_post_id, '_sc_enquiry_phone', $phone );
		update_post_meta( $enquiry_post_id, '_sc_enquiry_company', $company );
		update_post_meta( $enquiry_post_id, '_sc_enquiry_country', $country );
		update_post_meta( $enquiry_post_id, '_sc_enquiry_state', $state );
		update_post_meta( $enquiry_post_id, '_sc_enquiry_city', $city );
		update_post_meta( $enquiry_post_id, '_sc_enquiry_whatsapp', $whatsapp );
		update_post_meta( $enquiry_post_id, '_sc_enquiry_customer_type', $customer_type );
		update_post_meta( $enquiry_post_id, '_sc_enquiry_consent', $consent );
		update_post_meta( $enquiry_post_id, '_sc_enquiry_preferred_contact', $contact_pref );

		update_post_meta( $enquiry_post_id, '_sc_enquiry_product_id', $product_id );
		update_post_meta( $enquiry_post_id, '_sc_enquiry_product_name', $product_name );
		update_post_meta( $enquiry_post_id, '_sc_enquiry_product_sku', $product_sku );
		update_post_meta( $enquiry_post_id, '_sc_enquiry_product_url', $product_url );
		update_post_meta( $enquiry_post_id, '_sc_enquiry_product_category', $product_category );
		update_post_meta( $enquiry_post_id, '_sc_enquiry_quantity', $quantity );
		update_post_meta( $enquiry_post_id, '_sc_enquiry_packaging', $packaging );
		update_post_meta( $enquiry_post_id, '_sc_enquiry_pack_size', $pack_size );
		update_post_meta( $enquiry_post_id, '_sc_enquiry_message', $message );

		update_post_meta( $enquiry_post_id, '_sc_enquiry_type', $enquiry_type );
		update_post_meta( $enquiry_post_id, '_sc_enquiry_status', 'new' );
		update_post_meta( $enquiry_post_id, '_sc_enquiry_source', $source );
		update_post_meta( $enquiry_post_id, '_sc_enquiry_source_url', $source_url );
		update_post_meta( $enquiry_post_id, '_sc_enquiry_ip', $ip );
		update_post_meta( $enquiry_post_id, '_sc_enquiry_user_agent', sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ?? '' ) ) );
		update_post_meta( $enquiry_post_id, '_sc_enquiry_submitted_at', current_time( 'mysql' ) );

		// Activity Log initialization
		$initial_activity = array(
			array(
				'time'    => current_time( 'mysql' ),
				'user'    => 'System',
				'action'  => __( 'Lead created', 'spicecraft-core' ),
				'details' => sprintf( __( 'Enquiry submitted via %s', 'spicecraft-core' ), $source ),
			),
		);
		update_post_meta( $enquiry_post_id, '_sc_enquiry_activity_log', $initial_activity );

		// 7. Dispatch Admin Notification Email (Dynamic Recipient)
		$recipient = function_exists( 'spicecraft_get_enquiry_receiving_email' )
			? spicecraft_get_enquiry_receiving_email()
			: get_option( 'admin_email' );

		$mail_sent = $this->dispatch_admin_notification(
			array(
				'enquiry_id'       => $enquiry_post_id,
				'recipient'        => $recipient,
				'name'             => $name,
				'email'            => $email,
				'phone'            => $phone,
				'company'          => $company,
				'country'          => $country,
				'city'             => $city,
				'whatsapp'         => $whatsapp,
				'customer_type'    => $customer_type,
				'product_name'     => $product_name,
				'product_sku'      => $product_sku,
				'product_url'      => $product_url,
				'product_category' => $product_category,
				'pack_size'        => $pack_size,
				'quantity'         => $quantity,
				'packaging'        => $packaging,
				'preferred_contact'=> $contact_pref,
				'message'          => $message,
				'enquiry_type'     => $enquiry_type,
				'source'           => $source,
			)
		);

		update_post_meta( $enquiry_post_id, '_sc_enquiry_notification_sent', $mail_sent ? 1 : 0 );
		update_post_meta( $enquiry_post_id, '_sc_enquiry_notification_recipient', $recipient );

		// 8. Optional Customer Confirmation Email
		$this->dispatch_customer_confirmation( $email, $name, $product_name );

		return $enquiry_post_id;
	}

	/**
	 * Dispatch administrative notification email.
	 *
	 * @param array $data Enquiry data.
	 * @return bool
	 */
	public function dispatch_admin_notification( $data ) {
		$recipient = $data['recipient'];
		if ( empty( $recipient ) || ! is_email( $recipient ) ) {
			return false;
		}

		if ( ! empty( $data['product_name'] ) ) {
			$subject = sprintf(
				/* translators: 1: Product Name, 2: Customer Name, 3: Lead ID */
				__( '[Lead #%3$d] Product Enquiry — %1$s — %2$s', 'spicecraft-core' ),
				$data['product_name'],
				$data['name'],
				$data['enquiry_id']
			);
		} else {
			$subject = sprintf(
				/* translators: 1: Customer Name, 2: Lead ID */
				__( '[Lead #%2$d] Trade Enquiry — %1$s', 'spicecraft-core' ),
				$data['name'],
				$data['enquiry_id']
			);
		}

		$site_name = get_bloginfo( 'name' );
		$admin_url = admin_url( 'post.php?post=' . $data['enquiry_id'] . '&action=edit' );

		// Assemble Clean HTML Email Body
		ob_start();
		?>
		<!DOCTYPE html>
		<html>
		<head>
			<meta charset="UTF-8">
			<title><?php echo esc_html( $subject ); ?></title>
		</head>
		<body style="font-family: Arial, sans-serif; background-color: #f8fafc; margin: 0; padding: 24px; color: #1e293b;">
			<table width="100%" border="0" cellspacing="0" cellpadding="0" style="max-width: 620px; margin: 0 auto; background: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 10px rgba(0,0,0,0.06); border: 1px solid #e2e8f0;">
				<tr>
					<td style="background-color: #1c1815; padding: 24px 30px; text-align: left;">
						<h1 style="color: #ffffff; margin: 0; font-size: 20px; font-weight: 700; letter-spacing: 0.02em;">SpiceCraft &bull; Trade Lead Desk</h1>
						<p style="color: #d4a373; margin: 5px 0 0 0; font-size: 13px;">
							<?php printf( esc_html__( 'Incoming Commercial Customer Enquiry &bull; Lead #%d', 'spicecraft-core' ), absint( $data['enquiry_id'] ) ); ?>
						</p>
					</td>
				</tr>
				<tr>
					<td style="padding: 28px 30px;">
						<p style="font-size: 15px; line-height: 1.5; margin: 0 0 20px 0;">
							<?php
							printf(
								/* translators: %s: Submitter name */
								esc_html__( 'A new customer enquiry has been submitted by %s through the website.', 'spicecraft-core' ),
								'<strong>' . esc_html( $data['name'] ) . '</strong>'
							);
							?>
						</p>

						<table width="100%" border="0" cellspacing="0" cellpadding="8" style="border-collapse: collapse; margin-bottom: 24px;">
							<tr style="background-color: #f1f5f9;">
								<th colspan="2" style="text-align: left; font-size: 14px; font-weight: 700; color: #0f172a; padding: 10px 12px; border-bottom: 2px solid #cbd5e1;">
									<?php esc_html_e( '1. Customer Profile', 'spicecraft-core' ); ?>
								</th>
							</tr>
							<tr>
								<td style="width: 35%; color: #64748b; font-size: 13px; font-weight: 600;"><?php esc_html_e( 'Lead ID', 'spicecraft-core' ); ?>:</td>
								<td style="color: #0f172a; font-size: 14px; font-weight: 700;">#<?php echo absint( $data['enquiry_id'] ); ?></td>
							</tr>
							<tr style="background-color: #f8fafc;">
								<td style="color: #64748b; font-size: 13px; font-weight: 600;"><?php esc_html_e( 'Full Name', 'spicecraft-core' ); ?>:</td>
								<td style="color: #0f172a; font-size: 14px; font-weight: 600;"><?php echo esc_html( $data['name'] ); ?></td>
							</tr>
							<tr>
								<td style="color: #64748b; font-size: 13px; font-weight: 600;"><?php esc_html_e( 'Company / Organization', 'spicecraft-core' ); ?>:</td>
								<td style="color: #0f172a; font-size: 14px;"><?php echo esc_html( $data['company'] ?: __( 'Not specified', 'spicecraft-core' ) ); ?></td>
							</tr>
							<?php if ( ! empty( $data['customer_type'] ) ) : 
								$c_types = function_exists( 'spicecraft_get_customer_types' ) ? spicecraft_get_customer_types() : array();
								$c_type_label = $c_types[ $data['customer_type'] ] ?? ucfirst( str_replace( '_', ' ', $data['customer_type'] ) );
							?>
							<tr style="background-color: #f8fafc;">
								<td style="color: #64748b; font-size: 13px; font-weight: 600;"><?php esc_html_e( 'Customer Type', 'spicecraft-core' ); ?>:</td>
								<td style="color: #0f172a; font-size: 14px; font-weight: 600;"><?php echo esc_html( $c_type_label ); ?></td>
							</tr>
							<?php endif; ?>
							<tr>
								<td style="color: #64748b; font-size: 13px; font-weight: 600;"><?php esc_html_e( 'Email Address', 'spicecraft-core' ); ?>:</td>
								<td style="color: #0f172a; font-size: 14px;"><a href="mailto:<?php echo esc_attr( $data['email'] ); ?>" style="color:#0284c7;"><?php echo esc_html( $data['email'] ); ?></a></td>
							</tr>
							<tr style="background-color: #f8fafc;">
								<td style="color: #64748b; font-size: 13px; font-weight: 600;"><?php esc_html_e( 'Phone Number', 'spicecraft-core' ); ?>:</td>
								<td style="color: #0f172a; font-size: 14px;"><a href="tel:<?php echo esc_attr( preg_replace( '/[^0-9+]/', '', $data['phone'] ) ); ?>" style="color:#0284c7;"><?php echo esc_html( $data['phone'] ); ?></a></td>
							</tr>
							<?php if ( ! empty( $data['whatsapp'] ) ) : ?>
							<tr>
								<td style="color: #64748b; font-size: 13px; font-weight: 600;"><?php esc_html_e( 'WhatsApp', 'spicecraft-core' ); ?>:</td>
								<td style="color: #15803d; font-size: 14px; font-weight: 600;"><?php echo esc_html( $data['whatsapp'] ); ?></td>
							</tr>
							<?php endif; ?>
							<tr style="background-color: #f8fafc;">
								<td style="color: #64748b; font-size: 13px; font-weight: 600;"><?php esc_html_e( 'Location / Country', 'spicecraft-core' ); ?>:</td>
								<td style="color: #0f172a; font-size: 14px;"><?php echo esc_html( implode( ', ', array_filter( array( $data['city'], $data['country'] ) ) ) ?: __( 'Not specified', 'spicecraft-core' ) ); ?></td>
							</tr>
							<tr>
								<td style="color: #64748b; font-size: 13px; font-weight: 600;"><?php esc_html_e( 'Preferred Contact', 'spicecraft-core' ); ?>:</td>
								<td style="color: #0369a1; font-size: 13px; font-weight: 700; text-transform: uppercase;"><?php echo esc_html( $data['preferred_contact'] ); ?></td>
							</tr>

							<!-- Product / Requirement Details -->
							<tr style="background-color: #f1f5f9;">
								<th colspan="2" style="text-align: left; font-size: 14px; font-weight: 700; color: #0f172a; padding: 10px 12px; border-bottom: 2px solid #cbd5e1; border-top: 1px solid #e2e8f0;">
									<?php esc_html_e( '2. Requirement & Product Specifications', 'spicecraft-core' ); ?>
								</th>
							</tr>
							<?php if ( ! empty( $data['product_name'] ) ) : ?>
							<tr>
								<td style="color: #64748b; font-size: 13px; font-weight: 600;"><?php esc_html_e( 'Target Product', 'spicecraft-core' ); ?>:</td>
								<td style="color: #c2593f; font-size: 14px; font-weight: 700;">
									<?php echo esc_html( $data['product_name'] ); ?>
									<?php if ( ! empty( $data['product_sku'] ) ) : ?>
										<span style="font-size:12px;color:#64748b;font-weight:normal;">(SKU: <?php echo esc_html( $data['product_sku'] ); ?>)</span>
									<?php endif; ?>
								</td>
							</tr>
							<?php endif; ?>
							<?php if ( ! empty( $data['pack_size'] ) ) : ?>
							<tr style="background-color: #f8fafc;">
								<td style="color: #64748b; font-size: 13px; font-weight: 600;"><?php esc_html_e( 'Pack Sizes', 'spicecraft-core' ); ?>:</td>
								<td style="color: #0f172a; font-size: 14px;"><?php echo esc_html( $data['pack_size'] ); ?></td>
							</tr>
							<?php endif; ?>
							<tr>
								<td style="color: #64748b; font-size: 13px; font-weight: 600;"><?php esc_html_e( 'Requested Quantity', 'spicecraft-core' ); ?>:</td>
								<td style="color: #0f172a; font-size: 14px;"><?php echo esc_html( $data['quantity'] ?: __( 'Open / Flexible', 'spicecraft-core' ) ); ?></td>
							</tr>
							<tr style="background-color: #f8fafc;">
								<td style="color: #64748b; font-size: 13px; font-weight: 600;"><?php esc_html_e( 'Packaging Requirement', 'spicecraft-core' ); ?>:</td>
								<td style="color: #0f172a; font-size: 14px;"><?php echo esc_html( $data['packaging'] ?: __( 'Standard Commercial Pack', 'spicecraft-core' ) ); ?></td>
							</tr>
							<tr>
								<td style="color: #64748b; font-size: 13px; font-weight: 600;"><?php esc_html_e( 'Enquiry Classification', 'spicecraft-core' ); ?>:</td>
								<td style="color: #0f172a; font-size: 14px; font-weight: 600;"><?php echo esc_html( ucfirst( $data['enquiry_type'] ) ); ?></td>
							</tr>
						</table>

						<!-- Message Block -->
						<div style="margin-bottom: 24px;">
							<p style="font-size: 13px; font-weight: 700; color: #64748b; text-transform: uppercase; margin: 0 0 6px 0;">
								<?php esc_html_e( 'Customer Message', 'spicecraft-core' ); ?>:
							</p>
							<div style="background: #f8fafc; border-left: 4px solid #c2593f; padding: 14px 16px; font-size: 14px; line-height: 1.6; color: #1e293b; border-radius: 0 6px 6px 0;">
								<?php echo nl2br( esc_html( $data['message'] ) ); ?>
							</div>
						</div>

						<!-- Admin CTA Link -->
						<div style="text-align: center; margin-top: 30px;">
							<a href="<?php echo esc_url( $admin_url ); ?>" style="display: inline-block; background-color: #c2593f; color: #ffffff; text-decoration: none; padding: 12px 28px; border-radius: 6px; font-weight: 700; font-size: 14px;">
								<?php esc_html_e( 'Open Lead in WordPress Admin &rarr;', 'spicecraft-core' ); ?>
							</a>
						</div>
					</td>
				</tr>
				<tr>
					<td style="background-color: #f8fafc; padding: 16px 30px; font-size: 12px; color: #94a3b8; text-align: center; border-top: 1px solid #e2e8f0;">
						<?php printf( esc_html__( 'Received on %s via %s', 'spicecraft-core' ), current_time( 'F j, Y g:i a' ), esc_html( $data['source'] ) ); ?>
					</td>
				</tr>
			</table>
		</body>
		</html>
		<?php
		$html_body = ob_get_clean();

		$headers = array(
			'Content-Type: text/html; charset=UTF-8',
			'From: ' . $site_name . ' <' . sanitize_email( get_option( 'admin_email' ) ) . '>',
			'Reply-To: ' . $data['name'] . ' <' . sanitize_email( $data['email'] ) . '>',
		);

		return wp_mail( $recipient, $subject, $html_body, $headers );
	}

	/**
	 * Dispatch customer confirmation autoresponder.
	 *
	 * @param string $customer_email Customer email.
	 * @param string $customer_name  Customer name.
	 * @param string $product_name   Product name if provided.
	 */
	public function dispatch_customer_confirmation( $customer_email, $customer_name, $product_name = '' ) {
		if ( empty( $customer_email ) || ! is_email( $customer_email ) ) {
			return;
		}

		$enabled = function_exists( 'spicecraft_get_setting' ) ? spicecraft_get_setting( 'enquiry_customer_email_enabled', '1' ) : '1';
		if ( '0' === $enabled ) {
			return;
		}

		$site_name = get_bloginfo( 'name' );
		$subject   = sprintf(
			/* translators: %s: Company name */
			__( 'Thank you for your enquiry — %s', 'spicecraft-core' ),
			$site_name
		);

		ob_start();
		?>
		<!DOCTYPE html>
		<html>
		<head><meta charset="UTF-8"><title><?php echo esc_html( $subject ); ?></title></head>
		<body style="font-family: Arial, sans-serif; background-color: #f8fafc; margin: 0; padding: 24px; color: #1e293b;">
			<table width="100%" border="0" cellspacing="0" cellpadding="0" style="max-width: 580px; margin: 0 auto; background: #ffffff; border-radius: 8px; overflow: hidden; border: 1px solid #e2e8f0;">
				<tr>
					<td style="background-color: #1c1815; padding: 24px 30px;">
						<h1 style="color: #ffffff; margin: 0; font-size: 20px; font-weight: 700;"><?php echo esc_html( $site_name ); ?></h1>
						<p style="color: #d4a373; margin: 4px 0 0 0; font-size: 13px;"><?php esc_html_e( 'Direct Manufacturer & Institutional Spice Supply', 'spicecraft-core' ); ?></p>
					</td>
				</tr>
				<tr>
					<td style="padding: 28px 30px;">
						<h2 style="font-size: 18px; color: #0f172a; margin: 0 0 12px 0;">
							<?php printf( esc_html__( 'Hello %s,', 'spicecraft-core' ), esc_html( $customer_name ) ); ?>
						</h2>
						<p style="font-size: 14px; line-height: 1.6; color: #334155; margin: 0 0 16px 0;">
							<?php esc_html_e( 'Thank you for contacting SpiceCraft. We have received your request and our commercial trade specialists are reviewing your requirements.', 'spicecraft-core' ); ?>
						</p>
						<?php if ( ! empty( $product_name ) ) : ?>
							<p style="font-size: 14px; line-height: 1.6; color: #334155; margin: 0 0 16px 0;">
								<strong><?php esc_html_e( 'Enquiry Regarding:', 'spicecraft-core' ); ?></strong> <?php echo esc_html( $product_name ); ?>
							</p>
						<?php endif; ?>
						<p style="font-size: 14px; line-height: 1.6; color: #334155; margin: 0 0 20px 0;">
							<?php esc_html_e( 'A representative will follow up via your preferred contact method within 24 business hours with specifications, pricing, and sample availability.', 'spicecraft-core' ); ?>
						</p>
						<div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 14px 18px; font-size: 13px; color: #64748b;">
							<strong><?php esc_html_e( 'Need immediate assistance?', 'spicecraft-core' ); ?></strong><br>
							<?php esc_html_e( 'Contact our trade team via WhatsApp or email directly at', 'spicecraft-core' ); ?> 
							<a href="mailto:<?php echo esc_attr( function_exists( 'spicecraft_get_enquiry_receiving_email' ) ? spicecraft_get_enquiry_receiving_email() : get_option( 'admin_email' ) ); ?>" style="color: #c2593f; font-weight: 600;">
								<?php echo esc_html( function_exists( 'spicecraft_get_enquiry_receiving_email' ) ? spicecraft_get_enquiry_receiving_email() : get_option( 'admin_email' ) ); ?>
							</a>.
						</div>
					</td>
				</tr>
				<tr>
					<td style="background-color: #f8fafc; padding: 16px 30px; font-size: 12px; color: #94a3b8; text-align: center; border-top: 1px solid #e2e8f0;">
						&copy; <?php echo esc_html( gmdate( 'Y' ) ); ?> <?php echo esc_html( $site_name ); ?>. <?php esc_html_e( 'All rights reserved.', 'spicecraft-core' ); ?>
					</td>
				</tr>
			</table>
		</body>
		</html>
		<?php
		$html_body = ob_get_clean();

		$headers = array(
			'Content-Type: text/html; charset=UTF-8',
			'From: ' . $site_name . ' <' . sanitize_email( get_option( 'admin_email' ) ) . '>',
		);

		wp_mail( $customer_email, $subject, $html_body, $headers );
	}

	/**
	 * Send structured JSON or redirect response.
	 *
	 * @param bool   $success Success status.
	 * @param string $message User friendly message.
	 * @param int    $code    HTTP response code.
	 * @param array  $data    Extra payload.
	 */
	private function send_response( $success, $message, $code = 200, $data = array() ) {
		if ( wp_doing_ajax() ) {
			if ( $success ) {
				wp_send_json_success( array_merge( array( 'message' => $message ), $data ), $code );
			} else {
				wp_send_json_error( array_merge( array( 'message' => $message ), $data ), $code );
			}
		} else {
			// POST Fallback
			$referer = wp_get_referer();
			if ( ! $referer ) {
				$referer = home_url( '/' );
			}

			$query_args = array(
				'enquiry_submitted' => $success ? '1' : '0',
				'enquiry_msg'       => rawurlencode( $message ),
			);

			wp_safe_redirect( add_query_arg( $query_args, $referer ) );
			exit;
		}
	}

	/**
	 * Retrieve client IP address safely.
	 *
	 * @return string
	 */
	private function get_client_ip() {
		$ip = '127.0.0.1';
		if ( ! empty( $_SERVER['HTTP_CLIENT_IP'] ) ) {
			$ip = sanitize_text_field( wp_unslash( $_SERVER['HTTP_CLIENT_IP'] ) );
		} elseif ( ! empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
			$ips = explode( ',', sanitize_text_field( wp_unslash( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) );
			$ip  = trim( $ips[0] );
		} elseif ( ! empty( $_SERVER['REMOTE_ADDR'] ) ) {
			$ip = sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) );
		}
		return filter_var( $ip, FILTER_VALIDATE_IP ) ? $ip : '127.0.0.1';
	}
}
