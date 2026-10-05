<?php
/**
 * SpiceCraft Core - Commercial Quotation Engine
 *
 * Implements B2B quotation generation, calculation, storage, and printable PDF/HTML dispatch
 * seamlessly integrated with the existing 'spicecraft_enquiry' CPT architecture.
 * Strictly maintains B2B catalog-only business model (no online checkout or payment gateway).
 *
 * @package SpiceCraft_Core
 * @since 1.5.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class SpiceCraft_Quotation_Engine {

	/**
	 * Singleton Instance.
	 *
	 * @var SpiceCraft_Quotation_Engine|null
	 */
	private static $instance = null;

	/**
	 * Get Singleton Instance.
	 *
	 * @return SpiceCraft_Quotation_Engine
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
		// Printable / PDF view endpoint
		add_action( 'admin_post_spicecraft_print_quotation', array( $this, 'render_print_view' ) );
		// Outbound Email dispatch endpoint
		add_action( 'admin_post_spicecraft_email_quotation', array( $this, 'handle_email_quotation' ) );
	}

	/**
	 * Supported Quotation Statuses
	 *
	 * @return array
	 */
	public static function get_statuses() {
		return array(
			'draft'    => array(
				'label' => __( 'Draft', 'spicecraft-core' ),
				'bg'    => '#fef3c7',
				'color' => '#92400e',
			),
			'sent'     => array(
				'label' => __( 'Sent to Client', 'spicecraft-core' ),
				'bg'    => '#e0f2fe',
				'color' => '#0369a1',
			),
			'accepted' => array(
				'label' => __( 'Accepted / Won', 'spicecraft-core' ),
				'bg'    => '#dcfce7',
				'color' => '#15803d',
			),
			'declined' => array(
				'label' => __( 'Declined / Closed', 'spicecraft-core' ),
				'bg'    => '#f3f4f6',
				'color' => '#4b5563',
			),
		);
	}

	/**
	 * Retrieve quotation data for a specific enquiry post.
	 *
	 * @param int $enquiry_id Enquiry Post ID.
	 * @return array
	 */
	public static function get_quotation_data( $enquiry_id ) {
		$number    = get_post_meta( $enquiry_id, '_sc_quotation_number', true );
		if ( empty( $number ) ) {
			$number = 'SQ-' . gmdate( 'Y' ) . '-' . str_pad( (string) $enquiry_id, 4, '0', STR_PAD_LEFT );
		}

		$date        = get_post_meta( $enquiry_id, '_sc_quotation_date', true );
		if ( empty( $date ) ) {
			$date = gmdate( 'Y-m-d' );
		}

		$valid_until = get_post_meta( $enquiry_id, '_sc_quotation_valid_until', true );
		if ( empty( $valid_until ) ) {
			$valid_until = gmdate( 'Y-m-d', strtotime( '+30 days' ) );
		}

		$currency    = get_post_meta( $enquiry_id, '_sc_quotation_currency', true ) ?: 'USD';
		$incoterms   = get_post_meta( $enquiry_id, '_sc_quotation_incoterms', true ) ?: 'CIF Felixstowe / Nhava Sheva';
		$status      = get_post_meta( $enquiry_id, '_sc_quotation_status', true ) ?: 'draft';
		$notes       = get_post_meta( $enquiry_id, '_sc_quotation_notes', true );
		$items       = get_post_meta( $enquiry_id, '_sc_quotation_items', true );

		if ( ! is_array( $items ) || empty( $items ) ) {
			// Populate default single line item from associated enquiry product if available
			$prod_name = get_post_meta( $enquiry_id, '_sc_enquiry_product_name', true );
			$prod_qty  = get_post_meta( $enquiry_id, '_sc_enquiry_quantity', true ) ?: '1 FCL Container';
			$items     = array(
				array(
					'product_name' => $prod_name ?: __( 'Custom Commercial Spice Lot', 'spicecraft-core' ),
					'grade'        => 'Standard Export Specification',
					'quantity'     => $prod_qty,
					'unit_price'   => '0.00',
					'total'        => '0.00',
				),
			);
		}

		$subtotal = (float) get_post_meta( $enquiry_id, '_sc_quotation_subtotal', true );
		$shipping = (float) get_post_meta( $enquiry_id, '_sc_quotation_shipping', true );
		$total    = (float) get_post_meta( $enquiry_id, '_sc_quotation_total', true );

		return array(
			'number'      => $number,
			'date'        => $date,
			'valid_until' => $valid_until,
			'currency'    => $currency,
			'incoterms'   => $incoterms,
			'status'      => $status,
			'notes'       => $notes,
			'items'       => $items,
			'subtotal'    => $subtotal,
			'shipping'    => $shipping,
			'total'       => $total,
		);
	}

	/**
	 * Save quotation data from admin POST payload.
	 *
	 * @param int $enquiry_id Enquiry Post ID.
	 * @return void
	 */
	public static function save_quotation_data( $enquiry_id ) {
		if ( ! isset( $_POST['_sc_quotation_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['_sc_quotation_nonce'] ), 'spicecraft_save_quotation' ) ) {
			return;
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$number      = sanitize_text_field( wp_unslash( $_POST['_sc_quotation_number'] ?? '' ) );
		$date        = sanitize_text_field( wp_unslash( $_POST['_sc_quotation_date'] ?? '' ) );
		$valid_until = sanitize_text_field( wp_unslash( $_POST['_sc_quotation_valid_until'] ?? '' ) );
		$currency    = sanitize_text_field( wp_unslash( $_POST['_sc_quotation_currency'] ?? 'USD' ) );
		$incoterms   = sanitize_text_field( wp_unslash( $_POST['_sc_quotation_incoterms'] ?? '' ) );
		$status      = sanitize_key( wp_unslash( $_POST['_sc_quotation_status'] ?? 'draft' ) );
		$notes       = sanitize_textarea_field( wp_unslash( $_POST['_sc_quotation_notes'] ?? '' ) );

		update_post_meta( $enquiry_id, '_sc_quotation_number', $number );
		update_post_meta( $enquiry_id, '_sc_quotation_date', $date );
		update_post_meta( $enquiry_id, '_sc_quotation_valid_until', $valid_until );
		update_post_meta( $enquiry_id, '_sc_quotation_currency', $currency );
		update_post_meta( $enquiry_id, '_sc_quotation_incoterms', $incoterms );
		update_post_meta( $enquiry_id, '_sc_quotation_status', $status );
		update_post_meta( $enquiry_id, '_sc_quotation_notes', $notes );

		// Process Line Items
		$items = array();
		$subtotal = 0.0;

		if ( isset( $_POST['_sc_quote_item_name'] ) && is_array( $_POST['_sc_quote_item_name'] ) ) {
			$names  = map_deep( wp_unslash( $_POST['_sc_quote_item_name'] ), 'sanitize_text_field' );
			$grades = map_deep( wp_unslash( $_POST['_sc_quote_item_grade'] ?? array() ), 'sanitize_text_field' );
			$qtys   = map_deep( wp_unslash( $_POST['_sc_quote_item_qty'] ?? array() ), 'sanitize_text_field' );
			$prices = map_deep( wp_unslash( $_POST['_sc_quote_item_price'] ?? array() ), 'sanitize_text_field' );

			foreach ( $names as $idx => $name ) {
				if ( empty( $name ) ) {
					continue;
				}
				$grade = isset( $grades[ $idx ] ) ? $grades[ $idx ] : '';
				$qty   = isset( $qtys[ $idx ] ) ? $qtys[ $idx ] : '1';
				$price = isset( $prices[ $idx ] ) ? (float) $prices[ $idx ] : 0.0;
				$qty_num = (float) filter_var( $qty, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION ) ?: 1.0;
				$line_total = $price * $qty_num;
				$subtotal += $line_total;

				$items[] = array(
					'product_name' => $name,
					'grade'        => $grade,
					'quantity'     => $qty,
					'unit_price'   => number_format( $price, 2, '.', '' ),
					'total'        => number_format( $line_total, 2, '.', '' ),
				);
			}
		}

		$shipping = isset( $_POST['_sc_quotation_shipping'] ) ? (float) sanitize_text_field( wp_unslash( $_POST['_sc_quotation_shipping'] ) ) : 0.0;
		$total    = $subtotal + $shipping;

		update_post_meta( $enquiry_id, '_sc_quotation_items', $items );
		update_post_meta( $enquiry_id, '_sc_quotation_subtotal', $subtotal );
		update_post_meta( $enquiry_id, '_sc_quotation_shipping', $shipping );
		update_post_meta( $enquiry_id, '_sc_quotation_total', $total );

		// Sync with lead status
		if ( 'sent' === $status ) {
			update_post_meta( $enquiry_id, '_sc_enquiry_status', 'quotation_sent' );
		} elseif ( 'accepted' === $status ) {
			update_post_meta( $enquiry_id, '_sc_enquiry_status', 'converted' );
		} elseif ( 'draft' === $status ) {
			$curr = get_post_meta( $enquiry_id, '_sc_enquiry_status', true );
			if ( empty( $curr ) || 'new' === $curr ) {
				update_post_meta( $enquiry_id, '_sc_enquiry_status', 'in_discussion' );
			}
		}
	}

	/**
	 * Retrieve quotation KPI metrics across all enquiries.
	 *
	 * @return array
	 */
	public static function get_metrics() {
		global $wpdb;

		// 1. Draft Quotations
		$drafts = (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(DISTINCT post_id) FROM {$wpdb->postmeta} WHERE meta_key = '_sc_quotation_status' AND meta_value = %s",
				'draft'
			)
		);

		// 2. Sent Quotations
		$sent = (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(DISTINCT post_id) FROM {$wpdb->postmeta} WHERE meta_key = '_sc_quotation_status' AND meta_value = %s",
				'sent'
			)
		);

		// 3. Accepted Quotations
		$accepted = (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(DISTINCT post_id) FROM {$wpdb->postmeta} WHERE meta_key = '_sc_quotation_status' AND meta_value = %s",
				'accepted'
			)
		);

		// 4. Follow-ups (Enquiries with status 'contacted', 'in_discussion', or sent quotations past 48 hours)
		$follow_ups = (int) $wpdb->get_var(
			"SELECT COUNT(DISTINCT post_id) FROM {$wpdb->postmeta} 
			 WHERE (meta_key = '_sc_enquiry_status' AND meta_value IN ('contacted', 'in_discussion', 'quotation_sent'))"
		);

		return array(
			'draft'      => $drafts,
			'sent'       => $sent,
			'accepted'   => $accepted,
			'follow_ups' => $follow_ups,
		);
	}

	/**
	 * Render Printable / PDF View
	 */
	public function render_print_view() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Unauthorized access.', 'spicecraft-core' ), 403 );
		}

		$enquiry_id = isset( $_GET['enquiry_id'] ) ? absint( $_GET['enquiry_id'] ) : 0;
		check_admin_referer( 'spicecraft_print_quotation_' . $enquiry_id );

		$enquiry = get_post( $enquiry_id );
		if ( ! $enquiry || 'spicecraft_enquiry' !== $enquiry->post_type ) {
			wp_die( esc_html__( 'Invalid enquiry record.', 'spicecraft-core' ), 404 );
		}

		$quote = self::get_quotation_data( $enquiry_id );
		$global_settings = function_exists( 'spicecraft_get_all_settings' ) ? spicecraft_get_all_settings() : array();
		$company_name    = ! empty( $global_settings['company_name'] ) ? $global_settings['company_name'] : 'SpiceCraft Premium Spices';
		$company_reg     = ! empty( $global_settings['registered_company_name'] ) ? $global_settings['registered_company_name'] : '';
		$address_primary = ! empty( $global_settings['address_primary'] ) ? $global_settings['address_primary'] : '';
		$phone           = ! empty( $global_settings['phone_primary'] ) ? $global_settings['phone_primary'] : '';
		$email           = ! empty( $global_settings['email_sales'] ) ? $global_settings['email_sales'] : get_option( 'admin_email' );
		$gst             = ! empty( $global_settings['gst_number'] ) ? $global_settings['gst_number'] : '';
		$fssai           = ! empty( $global_settings['fssai_license'] ) ? $global_settings['fssai_license'] : '';
		$iec             = ! empty( $global_settings['iec_code'] ) ? $global_settings['iec_code'] : '';

		$client_name     = get_post_meta( $enquiry_id, '_sc_enquiry_name', true );
		$client_company  = get_post_meta( $enquiry_id, '_sc_enquiry_company', true );
		$client_email    = get_post_meta( $enquiry_id, '_sc_enquiry_email', true );
		$client_phone    = get_post_meta( $enquiry_id, '_sc_enquiry_phone', true );
		$client_country  = get_post_meta( $enquiry_id, '_sc_enquiry_country', true );

		?>
		<!DOCTYPE html>
		<html lang="en">
		<head>
			<meta charset="UTF-8">
			<title><?php echo esc_html( $quote['number'] . ' - ' . ( $client_company ?: $client_name ) ); ?></title>
			<style>
				body { font-family: 'Helvetica Neue', Arial, sans-serif; color: #2b2625; margin: 0; padding: 40px; background: #fff; line-height: 1.5; font-size: 14px; }
				.sc-quote-box { max-width: 840px; margin: 0 auto; border: 1px solid #e6ded1; padding: 40px; border-radius: 8px; }
				.sc-quote-header { display: flex; justify-content: space-between; align-items: flex-start; border-bottom: 2px solid #9e2a2b; padding-bottom: 24px; margin-bottom: 30px; }
				.sc-quote-brand h1 { margin: 0 0 6px 0; color: #6e1a24; font-size: 26px; }
				.sc-quote-brand p { margin: 0; color: #6b6360; font-size: 13px; line-height: 1.4; }
				.sc-quote-meta { text-align: right; }
				.sc-quote-meta h2 { margin: 0 0 8px 0; color: #9e2a2b; font-size: 22px; }
				.sc-quote-meta table { margin-left: auto; font-size: 13px; }
				.sc-quote-meta td { padding: 3px 8px; }
				.sc-quote-parties { display: flex; justify-content: space-between; gap: 30px; margin-bottom: 30px; background: #faf7f2; padding: 20px; border-radius: 6px; }
				.sc-quote-party h3 { margin: 0 0 8px 0; font-size: 14px; text-transform: uppercase; color: #9e2a2b; letter-spacing: 0.05em; }
				.sc-quote-party p { margin: 0 0 4px 0; font-size: 13px; }
				.sc-quote-table { width: 100%; border-collapse: collapse; margin-bottom: 24px; }
				.sc-quote-table th { background: #6e1a24; color: #fff; padding: 10px 14px; font-weight: 600; text-align: left; font-size: 13px; }
				.sc-quote-table td { padding: 12px 14px; border-bottom: 1px solid #e6ded1; font-size: 13px; }
				.sc-quote-table .text-right { text-align: right; }
				.sc-quote-totals { width: 340px; margin-left: auto; margin-bottom: 30px; }
				.sc-quote-totals table { width: 100%; border-collapse: collapse; }
				.sc-quote-totals td { padding: 6px 12px; }
				.sc-quote-totals .total-row td { font-weight: 700; font-size: 16px; border-top: 2px solid #9e2a2b; color: #6e1a24; }
				.sc-quote-terms { border-top: 1px solid #e6ded1; padding-top: 20px; margin-top: 20px; font-size: 12px; color: #6b6360; }
				.sc-quote-print-bar { max-width: 840px; margin: 0 auto 20px auto; display: flex; justify-content: space-between; align-items: center; }
				@media print {
					.sc-quote-print-bar { display: none; }
					body { padding: 0; }
					.sc-quote-box { border: none; padding: 0; }
				}
			</style>
		</head>
		<body>
			<div class="sc-quote-print-bar">
				<a href="<?php echo esc_url( admin_url( 'post.php?post=' . $enquiry_id . '&action=edit' ) ); ?>">&larr; <?php esc_html_e( 'Back to Enquiry Editor', 'spicecraft-core' ); ?></a>
				<button onclick="window.print();" style="padding: 8px 18px; background: #9e2a2b; color: #fff; border: none; border-radius: 4px; cursor: pointer; font-weight: 600;"><?php esc_html_e( 'Print / Save as PDF', 'spicecraft-core' ); ?></button>
			</div>

			<div class="sc-quote-box">
				<div class="sc-quote-header">
					<div class="sc-quote-brand">
						<h1><?php echo esc_html( $company_name ); ?></h1>
						<?php if ( $company_reg ) : ?><p><strong><?php echo esc_html( $company_reg ); ?></strong></p><?php endif; ?>
						<p><?php echo nl2br( esc_html( $address_primary ) ); ?></p>
						<p>Tel: <?php echo esc_html( $phone ); ?> | Email: <?php echo esc_html( $email ); ?></p>
						<?php if ( $fssai || $gst || $iec ) : ?>
							<p style="font-size:11px; margin-top:4px;">
								<?php if ( $fssai ) echo 'FSSAI: ' . esc_html( $fssai ) . ' | '; ?>
								<?php if ( $gst ) echo 'GSTIN: ' . esc_html( $gst ) . ' | '; ?>
								<?php if ( $iec ) echo 'IEC: ' . esc_html( $iec ); ?>
							</p>
						<?php endif; ?>
					</div>
					<div class="sc-quote-meta">
						<h2><?php esc_html_e( 'COMMERCIAL QUOTATION', 'spicecraft-core' ); ?></h2>
						<table>
							<tr><td><strong>Quote #:</strong></td><td><?php echo esc_html( $quote['number'] ); ?></td></tr>
							<tr><td><strong>Date:</strong></td><td><?php echo esc_html( $quote['date'] ); ?></td></tr>
							<tr><td><strong>Valid Until:</strong></td><td><?php echo esc_html( $quote['valid_until'] ); ?></td></tr>
							<tr><td><strong>Incoterms:</strong></td><td><?php echo esc_html( $quote['incoterms'] ); ?></td></tr>
							<tr><td><strong>Status:</strong></td><td><span style="text-transform:uppercase; font-weight:700; color:#9e2a2b;"><?php echo esc_html( $quote['status'] ); ?></span></td></tr>
						</table>
					</div>
				</div>

				<div class="sc-quote-parties">
					<div class="sc-quote-party">
						<h3><?php esc_html_e( 'Issued By', 'spicecraft-core' ); ?></h3>
						<p><strong><?php echo esc_html( $company_name ); ?></strong></p>
						<p><?php esc_html_e( 'Export & Commercial Sourcing Desk', 'spicecraft-core' ); ?></p>
						<p><?php echo esc_html( $email ); ?></p>
					</div>
					<div class="sc-quote-party">
						<h3><?php esc_html_e( 'Prepared For', 'spicecraft-core' ); ?></h3>
						<p><strong><?php echo esc_html( $client_name ); ?></strong></p>
						<?php if ( $client_company ) : ?><p><?php echo esc_html( $client_company ); ?></p><?php endif; ?>
						<p><?php echo esc_html( $client_email ); ?> | <?php echo esc_html( $client_phone ); ?></p>
						<?php if ( $client_country ) : ?><p><?php echo esc_html( $client_country ); ?></p><?php endif; ?>
					</div>
				</div>

				<table class="sc-quote-table">
					<thead>
						<tr>
							<th>#</th>
							<th><?php esc_html_e( 'Product & Specification Grade', 'spicecraft-core' ); ?></th>
							<th><?php esc_html_e( 'Quantity / Packaging', 'spicecraft-core' ); ?></th>
							<th class="text-right"><?php esc_html_e( 'Unit Price', 'spicecraft-core' ); ?> (<?php echo esc_html( $quote['currency'] ); ?>)</th>
							<th class="text-right"><?php esc_html_e( 'Line Total', 'spicecraft-core' ); ?> (<?php echo esc_html( $quote['currency'] ); ?>)</th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ( $quote['items'] as $i => $item ) : ?>
							<tr>
								<td><?php echo esc_html( $i + 1 ); ?></td>
								<td>
									<strong><?php echo esc_html( $item['product_name'] ); ?></strong>
									<?php if ( ! empty( $item['grade'] ) ) : ?>
										<br><span style="color:#6b6360; font-size:12px;"><?php echo esc_html( $item['grade'] ); ?></span>
									<?php endif; ?>
								</td>
								<td><?php echo esc_html( $item['quantity'] ); ?></td>
								<td class="text-right"><?php echo esc_html( number_format( (float) $item['unit_price'], 2 ) ); ?></td>
								<td class="text-right"><strong><?php echo esc_html( number_format( (float) $item['total'], 2 ) ); ?></strong></td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>

				<div class="sc-quote-totals">
					<table>
						<tr>
							<td><?php esc_html_e( 'Subtotal:', 'spicecraft-core' ); ?></td>
							<td class="text-right"><?php echo esc_html( $quote['currency'] . ' ' . number_format( $quote['subtotal'], 2 ) ); ?></td>
						</tr>
						<?php if ( $quote['shipping'] > 0 ) : ?>
							<tr>
								<td><?php esc_html_e( 'Freight / Handling:', 'spicecraft-core' ); ?></td>
								<td class="text-right"><?php echo esc_html( $quote['currency'] . ' ' . number_format( $quote['shipping'], 2 ) ); ?></td>
							</tr>
						<?php endif; ?>
						<tr class="total-row">
							<td><?php esc_html_e( 'Total Amount:', 'spicecraft-core' ); ?></td>
							<td class="text-right"><?php echo esc_html( $quote['currency'] . ' ' . number_format( $quote['total'], 2 ) ); ?></td>
						</tr>
					</table>
				</div>

				<div class="sc-quote-terms">
					<p><strong><?php esc_html_e( 'Terms & Commercial Specifications:', 'spicecraft-core' ); ?></strong></p>
					<p><?php echo nl2br( esc_html( $quote['notes'] ?: __( 'Standard manufacturer payment terms: 30% advance on PO confirmation, balance against bill of lading (BL) / certificate of analysis (COA) documents. All spices strictly certified free from unauthorized additives and compliant with international food safety standards.', 'spicecraft-core' ) ) ); ?></p>
				</div>
			</div>
		</body>
		</html>
		<?php
		exit;
	}

	/**
	 * Outbound Email Quotation Handler
	 */
	public function handle_email_quotation() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Unauthorized access.', 'spicecraft-core' ), 403 );
		}

		$enquiry_id = isset( $_GET['enquiry_id'] ) ? absint( $_GET['enquiry_id'] ) : 0;
		check_admin_referer( 'spicecraft_email_quotation_' . $enquiry_id );

		$recipient_email = get_post_meta( $enquiry_id, '_sc_enquiry_email', true );
		$recipient_name  = get_post_meta( $enquiry_id, '_sc_enquiry_name', true );
		$quote           = self::get_quotation_data( $enquiry_id );
		$company_name    = function_exists( 'spicecraft_get_setting' ) ? spicecraft_get_setting( 'company_name', 'SpiceCraft' ) : 'SpiceCraft';

		if ( empty( $recipient_email ) || ! is_email( $recipient_email ) ) {
			wp_redirect( add_query_arg( array( 'post' => $enquiry_id, 'action' => 'edit', 'sc_quote_error' => 'no_email' ), admin_url( 'post.php' ) ) );
			exit;
		}

		$subject = sprintf( '[%s] Commercial Quotation #%s for %s', $company_name, $quote['number'], $recipient_name );
		$body    = sprintf(
			"Dear %s,\n\nThank you for reaching out to %s. Please find your official commercial quotation details below:\n\nQuotation #: %s\nDate: %s\nValid Until: %s\nIncoterms: %s\nTotal: %s %s\n\nOur export desk is ready to assist with lot samples, lab COA documentation, and delivery schedules.\n\nBest regards,\nTrade & Export Operations\n%s",
			$recipient_name,
			$company_name,
			$quote['number'],
			$quote['date'],
			$quote['valid_until'],
			$quote['incoterms'],
			$quote['currency'],
			number_format( $quote['total'], 2 ),
			$company_name
		);

		$headers = array( 'Content-Type: text/plain; charset=UTF-8' );
		$sent    = wp_mail( $recipient_email, $subject, $body, $headers );

		if ( $sent ) {
			update_post_meta( $enquiry_id, '_sc_quotation_status', 'sent' );
			update_post_meta( $enquiry_id, '_sc_enquiry_status', 'quotation_sent' );
			wp_redirect( add_query_arg( array( 'post' => $enquiry_id, 'action' => 'edit', 'sc_quote_sent' => '1' ), admin_url( 'post.php' ) ) );
		} else {
			wp_redirect( add_query_arg( array( 'post' => $enquiry_id, 'action' => 'edit', 'sc_quote_error' => 'send_failed' ), admin_url( 'post.php' ) ) );
		}
		exit;
	}
}
