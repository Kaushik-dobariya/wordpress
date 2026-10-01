<?php
/**
 * SpiceCraft Core - Product Enquiry & Lead Detail Meta Box Engine
 *
 * Renders structured administrative view of incoming enquiries:
 * 1. Customer Profile
 * 2. Product & Requirement Specifications
 * 3. Lead Status & Classification
 * 4. Strictly Private Internal Notes
 * 5. Submission Audit Trail & Email Status
 *
 * @package SpiceCraft_Core
 * @since 1.4.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class SpiceCraft_Enquiry_Meta {

	/**
	 * Post type key.
	 */
	const POST_TYPE = 'spicecraft_enquiry';

	/**
	 * Singleton Instance.
	 *
	 * @var SpiceCraft_Enquiry_Meta|null
	 */
	private static $instance = null;

	/**
	 * Get Singleton Instance.
	 *
	 * @return SpiceCraft_Enquiry_Meta
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
		add_action( 'add_meta_boxes', array( $this, 'register_meta_boxes' ) );
		add_action( 'save_post_' . self::POST_TYPE, array( $this, 'save_enquiry_meta' ), 10, 2 );
	}

	/**
	 * Register meta boxes on the enquiry edit screen.
	 */
	public function register_meta_boxes() {
		add_meta_box(
			'spicecraft_enquiry_main_details',
			__( 'Lead Information & Specifications', 'spicecraft-core' ),
			array( $this, 'render_main_details_metabox' ),
			self::POST_TYPE,
			'normal',
			'high'
		);

		add_meta_box(
			'spicecraft_enquiry_status_box',
			__( 'Lead Workflow & Status', 'spicecraft-core' ),
			array( $this, 'render_status_metabox' ),
			self::POST_TYPE,
			'side',
			'high'
		);
	}

	/**
	 * Render the main details meta box.
	 *
	 * @param WP_Post $post Current post object.
	 */
	public function render_main_details_metabox( $post ) {
		wp_nonce_field( 'spicecraft_save_enquiry_meta', 'spicecraft_enquiry_meta_nonce' );

		$post_id          = $post->ID;
		$name             = get_post_meta( $post_id, '_sc_enquiry_name', true );
		$email            = get_post_meta( $post_id, '_sc_enquiry_email', true );
		$phone            = get_post_meta( $post_id, '_sc_enquiry_phone', true );
		$whatsapp         = get_post_meta( $post_id, '_sc_enquiry_whatsapp', true );
		$company          = get_post_meta( $post_id, '_sc_enquiry_company', true );
		$country          = get_post_meta( $post_id, '_sc_enquiry_country', true );
		$state            = get_post_meta( $post_id, '_sc_enquiry_state', true );
		$city             = get_post_meta( $post_id, '_sc_enquiry_city', true );
		$contact_pref     = get_post_meta( $post_id, '_sc_enquiry_preferred_contact', true );
		$customer_type    = get_post_meta( $post_id, '_sc_enquiry_customer_type', true );
		$consent          = get_post_meta( $post_id, '_sc_enquiry_consent', true );

		$product_id       = get_post_meta( $post_id, '_sc_enquiry_product_id', true );
		$product_name     = get_post_meta( $post_id, '_sc_enquiry_product_name', true );
		$product_sku      = get_post_meta( $post_id, '_sc_enquiry_product_sku', true );
		$product_url      = get_post_meta( $post_id, '_sc_enquiry_product_url', true );
		$product_cat      = get_post_meta( $post_id, '_sc_enquiry_product_category', true );
		$pack_size        = get_post_meta( $post_id, '_sc_enquiry_pack_size', true );
		$quantity         = get_post_meta( $post_id, '_sc_enquiry_quantity', true );
		$packaging        = get_post_meta( $post_id, '_sc_enquiry_packaging', true );
		$message          = get_post_meta( $post_id, '_sc_enquiry_message', true );

		$last_contacted   = get_post_meta( $post_id, '_sc_enquiry_last_contacted', true );
		$next_followup    = get_post_meta( $post_id, '_sc_enquiry_next_followup', true );
		$assigned_user    = get_post_meta( $post_id, '_sc_enquiry_assigned_user', true );
		$admin_notes      = get_post_meta( $post_id, '_sc_enquiry_admin_notes', true );
		$activity_log     = get_post_meta( $post_id, '_sc_enquiry_activity_log', true );
		if ( ! is_array( $activity_log ) ) {
			$activity_log = array();
		}

		$source           = get_post_meta( $post_id, '_sc_enquiry_source', true );
		$source_url       = get_post_meta( $post_id, '_sc_enquiry_source_url', true );
		$ip               = get_post_meta( $post_id, '_sc_enquiry_ip', true );
		$notif_sent       = get_post_meta( $post_id, '_sc_enquiry_notification_sent', true );
		$notif_recipient  = get_post_meta( $post_id, '_sc_enquiry_notification_recipient', true );

		$cust_types = function_exists( 'spicecraft_get_customer_types' ) ? spicecraft_get_customer_types() : array();
		$cust_type_label = isset( $cust_types[ $customer_type ] ) ? $cust_types[ $customer_type ] : ( ! empty( $customer_type ) ? ucfirst( str_replace( '_', ' ', $customer_type ) ) : __( 'Not specified', 'spicecraft-core' ) );
		?>

		<style>
			.sc-enquiry-admin-section { margin-bottom: 24px; padding-bottom: 20px; border-bottom: 1px solid #e2e8f0; }
			.sc-enquiry-admin-section:last-child { margin-bottom: 0; padding-bottom: 0; border-bottom: none; }
			.sc-enquiry-admin-heading { font-size: 15px; font-weight: 700; color: #1e293b; margin: 0 0 14px; display: flex; align-items: center; gap: 8px; }
			.sc-enquiry-grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
			.sc-enquiry-grid-3 { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 16px; }
			.sc-enquiry-grid-4 { display: grid; grid-template-columns: 1fr 1fr 1fr 1fr; gap: 16px; }
			.sc-enquiry-field { margin-bottom: 12px; }
			.sc-enquiry-field label { display: block; font-size: 12px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.03em; color: #64748b; margin-bottom: 4px; }
			.sc-enquiry-val { font-size: 14px; color: #0f172a; font-weight: 500; }
			.sc-enquiry-val a { color: #0284c7; text-decoration: none; font-weight: 600; }
			.sc-enquiry-val a:hover { text-decoration: underline; }
			.sc-enquiry-message-box { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 14px 18px; font-size: 14px; line-height: 1.6; color: #334155; white-space: pre-wrap; }
			.sc-badge-pref { display: inline-block; padding: 3px 8px; border-radius: 4px; font-size: 11px; font-weight: 700; text-transform: uppercase; background: #e0f2fe; color: #0369a1; }
			.sc-badge-cust { display: inline-block; padding: 3px 8px; border-radius: 4px; font-size: 11px; font-weight: 700; background: #f1f5f9; color: #475569; }
			.sc-badge-consent { display: inline-block; padding: 2px 7px; border-radius: 4px; font-size: 11px; font-weight: 600; background: #dcfce7; color: #15803d; }
			.sc-notes-textarea { width: 100%; border: 1px solid #cbd5e1; border-radius: 6px; padding: 10px; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; font-size: 13px; line-height: 1.5; color: #1e293b; }
			.sc-timeline-list { margin: 0; padding: 0; list-style: none; }
			.sc-timeline-item { position: relative; padding-left: 24px; margin-bottom: 12px; font-size: 13px; line-height: 1.5; }
			.sc-timeline-item::before { content: ""; position: absolute; left: 6px; top: 6px; width: 8px; height: 8px; border-radius: 50%; background: #0284c7; }
			.sc-timeline-item::after { content: ""; position: absolute; left: 9px; top: 16px; bottom: -12px; width: 2px; background: #e2e8f0; }
			.sc-timeline-item:last-child::after { display: none; }
			.sc-timeline-time { color: #64748b; font-size: 12px; font-weight: 600; margin-right: 8px; }
			.sc-timeline-actor { font-weight: 700; color: #1e293b; }
			.sc-timeline-action { font-weight: 600; color: #0369a1; }
			.sc-timeline-details { color: #475569; }
		</style>

		<!-- 1. Customer Profile -->
		<div class="sc-enquiry-admin-section">
			<h4 class="sc-enquiry-admin-heading">
				<span class="dashicons dashicons-id-alt" style="color:#0284c7;"></span>
				<?php esc_html_e( 'Customer & Organization Profile', 'spicecraft-core' ); ?>
			</h4>
			<div class="sc-enquiry-grid-4">
				<div class="sc-enquiry-field">
					<label><?php esc_html_e( 'Full Name', 'spicecraft-core' ); ?></label>
					<div class="sc-enquiry-val"><strong><?php echo esc_html( $name ?: get_the_title( $post_id ) ); ?></strong></div>
				</div>
				<div class="sc-enquiry-field">
					<label><?php esc_html_e( 'Company / Organization', 'spicecraft-core' ); ?></label>
					<div class="sc-enquiry-val"><?php echo esc_html( $company ?: __( 'Independent / Private Buyer', 'spicecraft-core' ) ); ?></div>
				</div>
				<div class="sc-enquiry-field">
					<label><?php esc_html_e( 'Customer Type', 'spicecraft-core' ); ?></label>
					<div class="sc-enquiry-val">
						<span class="sc-badge-cust"><?php echo esc_html( $cust_type_label ); ?></span>
					</div>
				</div>
				<div class="sc-enquiry-field">
					<label><?php esc_html_e( 'Preferred Contact', 'spicecraft-core' ); ?></label>
					<div class="sc-enquiry-val">
						<span class="sc-badge-pref"><?php echo esc_html( ! empty( $contact_pref ) ? ucfirst( $contact_pref ) : __( 'Email', 'spicecraft-core' ) ); ?></span>
					</div>
				</div>
			</div>

			<div class="sc-enquiry-grid-3">
				<div class="sc-enquiry-field">
					<label><?php esc_html_e( 'Email Address', 'spicecraft-core' ); ?></label>
					<div class="sc-enquiry-val">
						<?php if ( ! empty( $email ) ) : ?>
							<a href="mailto:<?php echo esc_attr( $email ); ?>" target="_blank"><?php echo esc_html( $email ); ?></a>
						<?php else : ?>
							&mdash;
						<?php endif; ?>
					</div>
				</div>
				<div class="sc-enquiry-field">
					<label><?php esc_html_e( 'Phone Number', 'spicecraft-core' ); ?></label>
					<div class="sc-enquiry-val">
						<?php if ( ! empty( $phone ) ) : ?>
							<a href="tel:<?php echo esc_attr( preg_replace( '/[^0-9+]/', '', $phone ) ); ?>"><?php echo esc_html( $phone ); ?></a>
						<?php else : ?>
							&mdash;
						<?php endif; ?>
					</div>
				</div>
				<div class="sc-enquiry-field">
					<label><?php esc_html_e( 'WhatsApp Direct', 'spicecraft-core' ); ?></label>
					<div class="sc-enquiry-val">
						<?php if ( ! empty( $whatsapp ) ) : ?>
							<a href="https://wa.me/<?php echo esc_attr( preg_replace( '/[^0-9]/', '', $whatsapp ) ); ?>" target="_blank" rel="noopener noreferrer" style="color:#16a34a;font-weight:600;">
								&#x2714; <?php echo esc_html( $whatsapp ); ?> (<?php esc_html_e( 'Open Chat', 'spicecraft-core' ); ?>)
							</a>
						<?php else : ?>
							<span style="color:#94a3b8;"><?php esc_html_e( 'Not provided', 'spicecraft-core' ); ?></span>
						<?php endif; ?>
					</div>
				</div>
			</div>

			<div class="sc-enquiry-grid-4">
				<div class="sc-enquiry-field">
					<label><?php esc_html_e( 'Country', 'spicecraft-core' ); ?></label>
					<div class="sc-enquiry-val"><?php echo esc_html( $country ?: '&mdash;' ); ?></div>
				</div>
				<div class="sc-enquiry-field">
					<label><?php esc_html_e( 'State / Region', 'spicecraft-core' ); ?></label>
					<div class="sc-enquiry-val"><?php echo esc_html( $state ?: '&mdash;' ); ?></div>
				</div>
				<div class="sc-enquiry-field">
					<label><?php esc_html_e( 'City / Destination', 'spicecraft-core' ); ?></label>
					<div class="sc-enquiry-val"><?php echo esc_html( $city ?: '&mdash;' ); ?></div>
				</div>
				<div class="sc-enquiry-field">
					<label><?php esc_html_e( 'Contact Consent', 'spicecraft-core' ); ?></label>
					<div class="sc-enquiry-val">
						<span class="sc-badge-consent">&#x2714; <?php esc_html_e( 'Agreed to contact', 'spicecraft-core' ); ?></span>
					</div>
				</div>
			</div>
		</div>

		<!-- 2. Product & Requirement Specifications -->
		<div class="sc-enquiry-admin-section">
			<h4 class="sc-enquiry-admin-heading">
				<span class="dashicons dashicons-cart" style="color:#c2593f;"></span>
				<?php esc_html_e( 'Product & Requirement Specifications', 'spicecraft-core' ); ?>
			</h4>
			<div class="sc-enquiry-grid-4">
				<div class="sc-enquiry-field">
					<label><?php esc_html_e( 'Target Product', 'spicecraft-core' ); ?></label>
					<div class="sc-enquiry-val">
						<?php if ( ! empty( $product_name ) ) : ?>
							<?php if ( ! empty( $product_id ) && get_post( $product_id ) ) : ?>
								<a href="<?php echo esc_url( get_edit_post_link( $product_id ) ); ?>" target="_blank">
									<?php echo esc_html( $product_name ); ?> &rarr;
								</a>
							<?php else : ?>
								<strong><?php echo esc_html( $product_name ); ?></strong>
							<?php endif; ?>
						<?php else : ?>
							<span style="color:#64748b;"><?php esc_html_e( 'General / Sourcing Requirement', 'spicecraft-core' ); ?></span>
						<?php endif; ?>
					</div>
				</div>
				<div class="sc-enquiry-field">
					<label><?php esc_html_e( 'Product SKU / Code', 'spicecraft-core' ); ?></label>
					<div class="sc-enquiry-val">
						<?php echo ! empty( $product_sku ) ? '<code>' . esc_html( $product_sku ) . '</code>' : '&mdash;'; ?>
					</div>
				</div>
				<div class="sc-enquiry-field">
					<label><?php esc_html_e( 'Category', 'spicecraft-core' ); ?></label>
					<div class="sc-enquiry-val"><?php echo esc_html( $product_cat ?: '&mdash;' ); ?></div>
				</div>
				<div class="sc-enquiry-field">
					<label><?php esc_html_e( 'Pack Sizes Available', 'spicecraft-core' ); ?></label>
					<div class="sc-enquiry-val"><?php echo esc_html( $pack_size ?: '&mdash;' ); ?></div>
				</div>
			</div>

			<div class="sc-enquiry-grid-2">
				<div class="sc-enquiry-field">
					<label><?php esc_html_e( 'Required Volume / Quantity', 'spicecraft-core' ); ?></label>
					<div class="sc-enquiry-val"><?php echo esc_html( $quantity ?: __( 'Open for discussion', 'spicecraft-core' ) ); ?></div>
				</div>
				<div class="sc-enquiry-field">
					<label><?php esc_html_e( 'Packaging Requirement', 'spicecraft-core' ); ?></label>
					<div class="sc-enquiry-val"><?php echo esc_html( $packaging ?: __( 'Standard Commercial Pack', 'spicecraft-core' ) ); ?></div>
				</div>
			</div>

			<div class="sc-enquiry-field">
				<label><?php esc_html_e( 'Customer Message & Requirements', 'spicecraft-core' ); ?></label>
				<div class="sc-enquiry-message-box"><?php echo esc_html( $message ?: __( 'No additional message provided.', 'spicecraft-core' ) ); ?></div>
			</div>
		</div>

		<!-- 3. Lead Management & Follow-up Details -->
		<div class="sc-enquiry-admin-section">
			<h4 class="sc-enquiry-admin-heading">
				<span class="dashicons dashicons-calendar-alt" style="color:#0284c7;"></span>
				<?php esc_html_e( 'Lead Management & Follow-up Information', 'spicecraft-core' ); ?>
			</h4>
			<div class="sc-enquiry-grid-3">
				<div class="sc-enquiry-field">
					<label for="_sc_enquiry_last_contacted"><?php esc_html_e( 'Last Contacted Date', 'spicecraft-core' ); ?></label>
					<input type="date" id="_sc_enquiry_last_contacted" name="_sc_enquiry_last_contacted" value="<?php echo esc_attr( $last_contacted ); ?>" class="widefat" />
				</div>
				<div class="sc-enquiry-field">
					<label for="_sc_enquiry_next_followup"><?php esc_html_e( 'Next Follow-up Date', 'spicecraft-core' ); ?></label>
					<input type="date" id="_sc_enquiry_next_followup" name="_sc_enquiry_next_followup" value="<?php echo esc_attr( $next_followup ); ?>" class="widefat" />
				</div>
				<div class="sc-enquiry-field">
					<label for="_sc_enquiry_assigned_user"><?php esc_html_e( 'Assigned Team Member', 'spicecraft-core' ); ?></label>
					<?php
					$users = get_users( array( 'capability' => 'edit_posts', 'orderby' => 'display_name' ) );
					?>
					<select id="_sc_enquiry_assigned_user" name="_sc_enquiry_assigned_user" class="widefat">
						<option value="0"><?php esc_html_e( '— Unassigned —', 'spicecraft-core' ); ?></option>
						<?php foreach ( $users as $u ) : ?>
							<option value="<?php echo esc_attr( $u->ID ); ?>" <?php selected( (int) $assigned_user, $u->ID ); ?>>
								<?php echo esc_html( $u->display_name . ' (' . $u->user_email . ')' ); ?>
							</option>
						<?php endforeach; ?>
					</select>
				</div>
			</div>
		</div>

		<!-- 4. Internal Admin Notes (Strictly Private) -->
		<div class="sc-enquiry-admin-section">
			<h4 class="sc-enquiry-admin-heading">
				<span class="dashicons dashicons-lock" style="color:#d97706;"></span>
				<?php esc_html_e( 'Internal Staff Notes (Strictly Confidential)', 'spicecraft-core' ); ?>
			</h4>
			<p class="description" style="margin-bottom: 8px;">
				<?php esc_html_e( 'Private staff records for quotes, dispatch schedules, and conversation logs. Never shared with customers or sent in notifications.', 'spicecraft-core' ); ?>
			</p>
			<textarea name="_sc_enquiry_admin_notes" rows="4" class="sc-notes-textarea" placeholder="<?php esc_attr_e( 'e.g. 02 Oct: Called client regarding 500kg minimum order quantity. Quotation #SQ-8819 sent via email.', 'spicecraft-core' ); ?>"><?php echo esc_textarea( $admin_notes ); ?></textarea>
		</div>

		<!-- 5. Activity History / Audit Trail -->
		<div class="sc-enquiry-admin-section">
			<h4 class="sc-enquiry-admin-heading">
				<span class="dashicons dashicons-backup" style="color:#6366f1;"></span>
				<?php esc_html_e( 'Activity Timeline & Event History', 'spicecraft-core' ); ?>
			</h4>
			<?php if ( ! empty( $activity_log ) ) : ?>
				<ul class="sc-timeline-list">
					<?php foreach ( array_reverse( $activity_log ) as $act ) : ?>
						<li class="sc-timeline-item">
							<span class="sc-timeline-time"><?php echo esc_html( date_i18n( 'M j, Y g:i a', strtotime( $act['time'] ) ) ); ?></span>
							<span class="sc-timeline-actor"><?php echo esc_html( $act['user'] ); ?>:</span>
							<span class="sc-timeline-action"><?php echo esc_html( $act['action'] ); ?></span>
							<?php if ( ! empty( $act['details'] ) ) : ?>
								&mdash; <span class="sc-timeline-details"><?php echo esc_html( $act['details'] ); ?></span>
							<?php endif; ?>
						</li>
					<?php endforeach; ?>
				</ul>
			<?php else : ?>
				<p style="color:#94a3b8;font-size:13px;margin:0;"><?php esc_html_e( 'No recorded history events yet.', 'spicecraft-core' ); ?></p>
			<?php endif; ?>
		</div>

		<!-- 6. Technical Audit Trail -->
		<div class="sc-enquiry-admin-section">
			<h4 class="sc-enquiry-admin-heading">
				<span class="dashicons dashicons-admin-site-alt3" style="color:#64748b;"></span>
				<?php esc_html_e( 'Technical Submission Metadata', 'spicecraft-core' ); ?>
			</h4>
			<div class="sc-enquiry-grid-3">
				<div class="sc-enquiry-field">
					<label><?php esc_html_e( 'Source Location', 'spicecraft-core' ); ?></label>
					<div class="sc-enquiry-val"><?php echo esc_html( $source ?: __( 'Product Page Drawer', 'spicecraft-core' ) ); ?></div>
				</div>
				<div class="sc-enquiry-field">
					<label><?php esc_html_e( 'Email Notification Status', 'spicecraft-core' ); ?></label>
					<div class="sc-enquiry-val">
						<?php if ( ! empty( $notif_sent ) ) : ?>
							<span style="color:#16a34a;font-weight:600;">&#x2714; <?php printf( esc_html__( 'Dispatched to %s', 'spicecraft-core' ), esc_html( $notif_recipient ?: __( 'Trade Desk', 'spicecraft-core' ) ) ); ?></span>
						<?php else : ?>
							<span style="color:#dc2626;"><?php esc_html_e( 'Local Logged / Not Dispatched', 'spicecraft-core' ); ?></span>
						<?php endif; ?>
					</div>
				</div>
				<div class="sc-enquiry-field">
					<label><?php esc_html_e( 'Submitter IP', 'spicecraft-core' ); ?></label>
					<div class="sc-enquiry-val"><code><?php echo esc_html( $ip ?: '127.0.0.1' ); ?></code></div>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Render the side workflow & status meta box.
	 *
	 * @param WP_Post $post Current post object.
	 */
	public function render_status_metabox( $post ) {
		$post_id        = $post->ID;
		$current_status = get_post_meta( $post_id, '_sc_enquiry_status', true );
		if ( empty( $current_status ) ) {
			$current_status = 'new';
		}
		$statuses = function_exists( 'spicecraft_get_enquiry_statuses' ) ? spicecraft_get_enquiry_statuses() : array();

		$current_type = get_post_meta( $post_id, '_sc_enquiry_type', true );
		if ( empty( $current_type ) ) {
			$current_type = 'product';
		}
		$types = function_exists( 'spicecraft_get_enquiry_types' ) ? spicecraft_get_enquiry_types() : array();

		$current_cust_type = get_post_meta( $post_id, '_sc_enquiry_customer_type', true );
		$cust_types        = function_exists( 'spicecraft_get_customer_types' ) ? spicecraft_get_customer_types() : array();

		$assigned_user     = get_post_meta( $post_id, '_sc_enquiry_assigned_user', true );
		$next_followup     = get_post_meta( $post_id, '_sc_enquiry_next_followup', true );
		$last_contacted    = get_post_meta( $post_id, '_sc_enquiry_last_contacted', true );
		?>
		<div class="sc-enquiry-field" style="margin-bottom: 14px;">
			<label for="sc_enquiry_status_select" style="display:block;font-weight:600;margin-bottom:5px;">
				<?php esc_html_e( 'Workflow Status', 'spicecraft-core' ); ?>
			</label>
			<select name="_sc_enquiry_status" id="sc_enquiry_status_select" style="width:100%;">
				<?php foreach ( $statuses as $st_key => $st_conf ) : ?>
					<option value="<?php echo esc_attr( $st_key ); ?>" <?php selected( $current_status, $st_key ); ?>>
						<?php echo esc_html( $st_conf['label'] ); ?>
					</option>
				<?php endforeach; ?>
			</select>
		</div>

		<div class="sc-enquiry-field" style="margin-bottom: 14px;">
			<label for="sc_enquiry_customer_type_select" style="display:block;font-weight:600;margin-bottom:5px;">
				<?php esc_html_e( 'Customer Type', 'spicecraft-core' ); ?>
			</label>
			<select name="_sc_enquiry_customer_type" id="sc_enquiry_customer_type_select" style="width:100%;">
				<option value=""><?php esc_html_e( '— Unspecified —', 'spicecraft-core' ); ?></option>
				<?php foreach ( $cust_types as $c_key => $c_label ) : ?>
					<option value="<?php echo esc_attr( $c_key ); ?>" <?php selected( $current_cust_type, $c_key ); ?>>
						<?php echo esc_html( $c_label ); ?>
					</option>
				<?php endforeach; ?>
			</select>
		</div>

		<div class="sc-enquiry-field" style="margin-bottom: 14px;">
			<label for="sc_enquiry_type_select" style="display:block;font-weight:600;margin-bottom:5px;">
				<?php esc_html_e( 'Enquiry Classification', 'spicecraft-core' ); ?>
			</label>
			<select name="_sc_enquiry_type" id="sc_enquiry_type_select" style="width:100%;">
				<?php foreach ( $types as $t_key => $t_label ) : ?>
					<option value="<?php echo esc_attr( $t_key ); ?>" <?php selected( $current_type, $t_key ); ?>>
						<?php echo esc_html( $t_label ); ?>
					</option>
				<?php endforeach; ?>
			</select>
		</div>

		<div class="sc-enquiry-field" style="margin-bottom: 14px;">
			<label for="_sc_enquiry_side_assigned_user" style="display:block;font-weight:600;margin-bottom:5px;">
				<?php esc_html_e( 'Assigned To', 'spicecraft-core' ); ?>
			</label>
			<?php
			$users = get_users( array( 'capability' => 'edit_posts', 'orderby' => 'display_name' ) );
			?>
			<select id="_sc_enquiry_side_assigned_user" name="_sc_enquiry_assigned_user" style="width:100%;">
				<option value="0"><?php esc_html_e( '— Unassigned —', 'spicecraft-core' ); ?></option>
				<?php foreach ( $users as $u ) : ?>
					<option value="<?php echo esc_attr( $u->ID ); ?>" <?php selected( (int) $assigned_user, $u->ID ); ?>>
						<?php echo esc_html( $u->display_name ); ?>
					</option>
				<?php endforeach; ?>
			</select>
		</div>

		<div class="sc-enquiry-field" style="margin-bottom: 14px;">
			<label for="_sc_enquiry_side_last_contacted" style="display:block;font-weight:600;margin-bottom:5px;">
				<?php esc_html_e( 'Last Contacted', 'spicecraft-core' ); ?>
			</label>
			<input type="date" id="_sc_enquiry_side_last_contacted" name="_sc_enquiry_last_contacted" value="<?php echo esc_attr( $last_contacted ); ?>" style="width:100%;" />
		</div>

		<div class="sc-enquiry-field" style="margin-bottom: 14px;">
			<label for="_sc_enquiry_side_next_followup" style="display:block;font-weight:600;margin-bottom:5px;">
				<?php esc_html_e( 'Next Follow-up', 'spicecraft-core' ); ?>
			</label>
			<input type="date" id="_sc_enquiry_side_next_followup" name="_sc_enquiry_next_followup" value="<?php echo esc_attr( $next_followup ); ?>" style="width:100%;" />
		</div>

		<div style="padding-top: 10px; border-top: 1px solid #f1f5f9; display: flex; justify-content: space-between; align-items: center;">
			<a href="<?php echo esc_url( admin_url( 'edit.php?post_type=' . self::POST_TYPE ) ); ?>" class="button button-link">
				&larr; <?php esc_html_e( 'All Enquiries', 'spicecraft-core' ); ?>
			</a>
			<button type="submit" name="save" class="button button-primary button-large">
				<?php esc_html_e( 'Update Lead', 'spicecraft-core' ); ?>
			</button>
		</div>
		<?php
	}

	/**
	 * Save enquiry status and internal notes.
	 *
	 * @param int     $post_id Post ID.
	 * @param WP_Post $post    Post object.
	 */
	public function save_enquiry_meta( $post_id, $post ) {
		// Nonce check
		if ( ! isset( $_POST['spicecraft_enquiry_meta_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['spicecraft_enquiry_meta_nonce'] ) ), 'spicecraft_save_enquiry_meta' ) ) {
			return;
		}

		// Autosave check
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		// Capability check
		if ( ! current_user_can( 'edit_post', $post_id ) && ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$current_user = wp_get_current_user();
		$user_name    = $current_user && $current_user->exists() ? $current_user->display_name : 'Admin';

		$activity_log = get_post_meta( $post_id, '_sc_enquiry_activity_log', true );
		if ( ! is_array( $activity_log ) ) {
			$activity_log = array();
		}

		// 1. Update Status
		$raw_status = isset( $_POST['_sc_enquiry_status'] ) ? $_POST['_sc_enquiry_status'] : ( isset( $_POST['enquiry_status'] ) ? $_POST['enquiry_status'] : null );
		if ( null !== $raw_status ) {
			$new_status = sanitize_key( $raw_status );
			$old_status = get_post_meta( $post_id, '_sc_enquiry_status', true );
			$statuses   = function_exists( 'spicecraft_get_enquiry_statuses' ) ? spicecraft_get_enquiry_statuses() : array();

			if ( array_key_exists( $new_status, $statuses ) ) {
				update_post_meta( $post_id, '_sc_enquiry_status', $new_status );

				if ( $old_status !== $new_status ) {
					$old_label = isset( $statuses[ $old_status ]['label'] ) ? $statuses[ $old_status ]['label'] : ucfirst( $old_status );
					$new_label = $statuses[ $new_status ]['label'];
					$activity_log[] = array(
						'time'    => current_time( 'mysql' ),
						'user'    => $user_name,
						'action'  => __( 'Status changed', 'spicecraft-core' ),
						'details' => sprintf( __( 'From "%1$s" to "%2$s"', 'spicecraft-core' ), $old_label, $new_label ),
					);
				}
			}
		}

		// 2. Update Type
		$raw_type = isset( $_POST['_sc_enquiry_type'] ) ? $_POST['_sc_enquiry_type'] : ( isset( $_POST['enquiry_type'] ) ? $_POST['enquiry_type'] : null );
		if ( null !== $raw_type ) {
			$type  = sanitize_key( $raw_type );
			$types = function_exists( 'spicecraft_get_enquiry_types' ) ? spicecraft_get_enquiry_types() : array();
			if ( array_key_exists( $type, $types ) ) {
				update_post_meta( $post_id, '_sc_enquiry_type', $type );
			}
		}

		// 3. Update Customer Type
		$raw_c_type = isset( $_POST['_sc_enquiry_customer_type'] ) ? $_POST['_sc_enquiry_customer_type'] : ( isset( $_POST['enquiry_customer_type'] ) ? $_POST['enquiry_customer_type'] : null );
		if ( null !== $raw_c_type ) {
			$c_type     = sanitize_key( $raw_c_type );
			$cust_types = function_exists( 'spicecraft_get_customer_types' ) ? spicecraft_get_customer_types() : array();
			if ( array_key_exists( $c_type, $cust_types ) || empty( $c_type ) ) {
				update_post_meta( $post_id, '_sc_enquiry_customer_type', $c_type );
			}
		}

		// 4. Update Assigned User
		$raw_assigned = isset( $_POST['_sc_enquiry_assigned_user'] ) ? $_POST['_sc_enquiry_assigned_user'] : ( isset( $_POST['enquiry_assigned_user'] ) ? $_POST['enquiry_assigned_user'] : null );
		if ( null !== $raw_assigned ) {
			$new_assigned = absint( $raw_assigned );
			$old_assigned = absint( get_post_meta( $post_id, '_sc_enquiry_assigned_user', true ) );

			if ( $new_assigned !== $old_assigned ) {
				update_post_meta( $post_id, '_sc_enquiry_assigned_user', $new_assigned );

				$assigned_user_obj = $new_assigned ? get_userdata( $new_assigned ) : null;
				$assigned_name     = $assigned_user_obj ? $assigned_user_obj->display_name : __( 'Unassigned', 'spicecraft-core' );

				$activity_log[] = array(
					'time'    => current_time( 'mysql' ),
					'user'    => $user_name,
					'action'  => __( 'Assignment updated', 'spicecraft-core' ),
					'details' => sprintf( __( 'Assigned to %s', 'spicecraft-core' ), $assigned_name ),
				);
			}
		}

		// 5. Update Follow-up Dates
		$raw_contacted = isset( $_POST['_sc_enquiry_last_contacted'] ) ? $_POST['_sc_enquiry_last_contacted'] : ( isset( $_POST['enquiry_last_contacted'] ) ? $_POST['enquiry_last_contacted'] : null );
		if ( null !== $raw_contacted ) {
			update_post_meta( $post_id, '_sc_enquiry_last_contacted', sanitize_text_field( wp_unslash( $raw_contacted ) ) );
		}

		$raw_followup = isset( $_POST['_sc_enquiry_next_followup'] ) ? $_POST['_sc_enquiry_next_followup'] : ( isset( $_POST['enquiry_next_followup'] ) ? $_POST['enquiry_next_followup'] : null );
		if ( null !== $raw_followup ) {
			$new_followup = sanitize_text_field( wp_unslash( $raw_followup ) );
			$old_followup = get_post_meta( $post_id, '_sc_enquiry_next_followup', true );

			if ( $new_followup !== $old_followup ) {
				update_post_meta( $post_id, '_sc_enquiry_next_followup', $new_followup );
				if ( ! empty( $new_followup ) ) {
					$activity_log[] = array(
						'time'    => current_time( 'mysql' ),
						'user'    => $user_name,
						'action'  => __( 'Next follow-up scheduled', 'spicecraft-core' ),
						'details' => sprintf( __( 'Scheduled for %s', 'spicecraft-core' ), $new_followup ),
					);
				}
			}
		}

		// 6. Update Internal Admin Notes
		$raw_notes = isset( $_POST['_sc_enquiry_admin_notes'] ) ? $_POST['_sc_enquiry_admin_notes'] : ( isset( $_POST['enquiry_admin_notes'] ) ? $_POST['enquiry_admin_notes'] : null );
		if ( null !== $raw_notes ) {
			$new_notes = sanitize_textarea_field( wp_unslash( $raw_notes ) );
			$old_notes = get_post_meta( $post_id, '_sc_enquiry_admin_notes', true );

			if ( $new_notes !== $old_notes ) {
				update_post_meta( $post_id, '_sc_enquiry_admin_notes', $new_notes );
				if ( ! empty( $new_notes ) ) {
					$activity_log[] = array(
						'time'    => current_time( 'mysql' ),
						'user'    => $user_name,
						'action'  => __( 'Staff notes updated', 'spicecraft-core' ),
						'details' => __( 'Internal staff comments were revised', 'spicecraft-core' ),
					);
				}
			}
		}

		// Save updated activity log
		update_post_meta( $post_id, '_sc_enquiry_activity_log', $activity_log );
	}
}
