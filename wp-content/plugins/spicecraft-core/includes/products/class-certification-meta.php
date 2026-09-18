<?php
/**
 * SpiceCraft Core - Certification Taxonomy Metadata & Admin Interface
 *
 * Provides professional WordPress-native management for the spicecraft_certification taxonomy.
 * Adds custom fields across 8 logical groups, custom list columns with expiry alerts,
 * and strict nonces & capability enforcement.
 *
 * @package SpiceCraft_Core
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class SpiceCraft_Certification_Meta {

	/**
	 * Singleton instance
	 *
	 * @var SpiceCraft_Certification_Meta|null
	 */
	private static $instance = null;

	/**
	 * Get singleton instance
	 *
	 * @return SpiceCraft_Certification_Meta
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor
	 */
	private function __construct() {
		// Add custom fields to taxonomy add / edit screens
		add_action( 'spicecraft_certification_add_form_fields', array( $this, 'render_add_form_fields' ) );
		add_action( 'spicecraft_certification_edit_form_fields', array( $this, 'render_edit_form_fields' ), 10, 2 );

		// Save custom term metadata
		add_action( 'created_spicecraft_certification', array( $this, 'save_term_meta' ) );
		add_action( 'edited_spicecraft_certification', array( $this, 'save_term_meta' ) );

		// Custom admin columns
		add_filter( 'manage_edit-spicecraft_certification_columns', array( $this, 'register_admin_columns' ) );
		add_filter( 'manage_spicecraft_certification_custom_column', array( $this, 'render_admin_column' ), 10, 3 );
		add_filter( 'manage_edit-spicecraft_certification_sortable_columns', array( $this, 'register_sortable_columns' ) );

		// Admin notices for expiring certifications
		add_action( 'admin_notices', array( $this, 'render_admin_expiry_notices' ) );

		// Enqueue media scripts on taxonomy screen
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );
	}

	/**
	 * Enqueue WP media library assets on the certification taxonomy screen.
	 *
	 * @param string $hook
	 */
	public function enqueue_admin_assets( $hook ) {
		$screen = get_current_screen();
		if ( ! $screen || 'spicecraft_certification' !== $screen->taxonomy ) {
			return;
		}

		wp_enqueue_media();
		wp_enqueue_style( 'spicecraft-admin-css', SPICECRAFT_CORE_URL . 'assets/admin/admin.css', array(), SPICECRAFT_CORE_VERSION );
		wp_enqueue_script( 'spicecraft-admin-meta', SPICECRAFT_CORE_URL . 'assets/admin/admin-meta.js', array( 'jquery' ), SPICECRAFT_CORE_VERSION, true );
	}

	/**
	 * Render fields on the Add New Certification screen.
	 */
	public function render_add_form_fields() {
		wp_nonce_field( 'spicecraft_cert_meta_action', 'spicecraft_cert_meta_nonce' );
		?>
		<div class="form-field">
			<label for="_sc_cert_short_name"><?php esc_html_e( 'Short Name / Acronym', 'spicecraft-core' ); ?></label>
			<input type="text" name="_sc_cert_short_name" id="_sc_cert_short_name" value="" placeholder="e.g. ISO 22000, FSSAI, HACCP">
			<p class="description"><?php esc_html_e( 'Compact label used on product trust badges and mobile screens.', 'spicecraft-core' ); ?></p>
		</div>

		<div class="form-field">
			<label for="_sc_cert_status"><?php esc_html_e( 'Certification Status', 'spicecraft-core' ); ?></label>
			<select name="_sc_cert_status" id="_sc_cert_status">
				<option value="not_disclosed"><?php esc_html_e( 'Status on Request', 'spicecraft-core' ); ?></option>
				<option value="active"><?php esc_html_e( 'Active / Valid', 'spicecraft-core' ); ?></option>
				<option value="pending_renewal"><?php esc_html_e( 'Pending Renewal', 'spicecraft-core' ); ?></option>
				<option value="suspended"><?php esc_html_e( 'Suspended', 'spicecraft-core' ); ?></option>
				<option value="expired"><?php esc_html_e( 'Expired', 'spicecraft-core' ); ?></option>
				<option value="archived"><?php esc_html_e( 'Archived', 'spicecraft-core' ); ?></option>
			</select>
		</div>

		<div class="form-field">
			<label for="_sc_cert_number"><?php esc_html_e( 'Certificate / Registration Number', 'spicecraft-core' ); ?></label>
			<input type="text" name="_sc_cert_number" id="_sc_cert_number" value="">
		</div>

		<div class="form-field">
			<label for="_sc_cert_issuing_authority"><?php esc_html_e( 'Issuing Authority', 'spicecraft-core' ); ?></label>
			<input type="text" name="_sc_cert_issuing_authority" id="_sc_cert_issuing_authority" value="">
		</div>

		<div class="form-field">
			<label for="_sc_cert_expiry_date"><?php esc_html_e( 'Expiry Date', 'spicecraft-core' ); ?></label>
			<input type="date" name="_sc_cert_expiry_date" id="_sc_cert_expiry_date" value="">
		</div>

		<div class="form-field">
			<label for="_sc_cert_visibility"><?php esc_html_e( 'Public Visibility', 'spicecraft-core' ); ?></label>
			<select name="_sc_cert_visibility" id="_sc_cert_visibility">
				<option value="public"><?php esc_html_e( 'Public (Shown on website)', 'spicecraft-core' ); ?></option>
				<option value="internal"><?php esc_html_e( 'Internal / Admin Only (Hidden from frontend)', 'spicecraft-core' ); ?></option>
			</select>
		</div>

		<div class="form-field">
			<label for="_sc_cert_order"><?php esc_html_e( 'Display Order', 'spicecraft-core' ); ?></label>
			<input type="number" name="_sc_cert_order" id="_sc_cert_order" value="10" min="0" step="1">
		</div>
		<?php
	}

	/**
	 * Render fields on the Edit Certification screen (tabbed/grouped layout).
	 *
	 * @param WP_Term $term
	 */
	public function render_edit_form_fields( $term ) {
		$meta = function_exists( 'spicecraft_get_certification_meta' )
			? spicecraft_get_certification_meta( $term->term_id )
			: array();

		wp_nonce_field( 'spicecraft_cert_meta_action', 'spicecraft_cert_meta_nonce' );
		?>
		<tr class="form-field">
			<th colspan="2" style="padding-top: 24px; padding-bottom: 8px;">
				<div style="background: #f8fafc; border: 1px solid #e2e8f0; border-left: 4px solid var(--sc-brand-primary, #b45309); padding: 12px 16px; border-radius: 4px; display: flex; align-items: center; justify-content: space-between;">
					<div>
						<h3 style="margin: 0 0 4px 0; font-size: 1.1rem; color: #1e293b;"><?php esc_html_e( 'Certification Details & Trust Architecture', 'spicecraft-core' ); ?></h3>
						<p style="margin: 0; color: #64748b; font-size: 0.85rem;"><?php esc_html_e( 'Configure accredited statutory and quality details. Empty or internal fields remain hidden from the frontend.', 'spicecraft-core' ); ?></p>
					</div>
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=spicecraft-certification-settings' ) ); ?>" class="button button-secondary" style="font-size: 0.85rem;">
						<?php esc_html_e( '⚙ Display Settings', 'spicecraft-core' ); ?>
					</a>
				</div>
			</th>
		</tr>

		<!-- 1. IDENTITY & BRANDING -->
		<tr class="form-field">
			<th scope="row"><label for="_sc_cert_short_name"><?php esc_html_e( 'Short Name / Acronym', 'spicecraft-core' ); ?></label></th>
			<td>
				<input type="text" name="_sc_cert_short_name" id="_sc_cert_short_name" value="<?php echo esc_attr( $meta['short_name'] ?? '' ); ?>" class="regular-text">
				<p class="description"><?php esc_html_e( 'Used on compact product trust badges, mobile cards, and category headers (e.g. ISO 22000, FSSAI).', 'spicecraft-core' ); ?></p>
			</td>
		</tr>

		<tr class="form-field">
			<th scope="row"><label><?php esc_html_e( 'Certification Logo / Mark', 'spicecraft-core' ); ?></label></th>
			<td>
				<?php
				if ( function_exists( 'spicecraft_render_media_uploader' ) ) {
					spicecraft_render_media_uploader(
						'_sc_cert_logo_id',
						absint( $meta['logo_id'] ?? 0 ),
						__( 'Select Certification Logo', 'spicecraft-core' ),
						__( 'Recommended: Square or horizontal PNG/SVG with transparent background (approx 200x200px).', 'spicecraft-core' )
					);
				}
				?>
			</td>
		</tr>

		<tr class="form-field">
			<th scope="row"><label for="_sc_cert_featured"><?php esc_html_e( 'Featured Accreditation', 'spicecraft-core' ); ?></label></th>
			<td>
				<label>
					<input type="checkbox" name="_sc_cert_featured" id="_sc_cert_featured" value="1" <?php checked( ! empty( $meta['featured'] ) ); ?>>
					<?php esc_html_e( 'Highlight this certification on Homepage and Hero banners', 'spicecraft-core' ); ?>
				</label>
			</td>
		</tr>

		<!-- 2. REGISTRATION & AUTHORITY -->
		<tr class="form-field">
			<th scope="row"><label for="_sc_cert_number"><?php esc_html_e( 'Certificate / Registration Number', 'spicecraft-core' ); ?></label></th>
			<td>
				<input type="text" name="_sc_cert_number" id="_sc_cert_number" value="<?php echo esc_attr( $meta['number'] ?? '' ); ?>" class="regular-text">
				<p class="description"><?php esc_html_e( 'Official license, accreditation, or certificate tracking number.', 'spicecraft-core' ); ?></p>
			</td>
		</tr>

		<tr class="form-field">
			<th scope="row"><label for="_sc_cert_issuing_authority"><?php esc_html_e( 'Issuing Authority / Registrar', 'spicecraft-core' ); ?></label></th>
			<td>
				<input type="text" name="_sc_cert_issuing_authority" id="_sc_cert_issuing_authority" value="<?php echo esc_attr( $meta['issuing_authority'] ?? '' ); ?>" class="regular-text">
				<p class="description"><?php esc_html_e( 'e.g. Food Safety and Standards Authority of India, Bureau Veritas, DNV GL.', 'spicecraft-core' ); ?></p>
			</td>
		</tr>

		<tr class="form-field">
			<th scope="row"><label for="_sc_cert_accreditation_body"><?php esc_html_e( 'Accreditation Body', 'spicecraft-core' ); ?></label></th>
			<td>
				<input type="text" name="_sc_cert_accreditation_body" id="_sc_cert_accreditation_body" value="<?php echo esc_attr( $meta['accreditation_body'] ?? '' ); ?>" class="regular-text">
				<p class="description"><?php esc_html_e( 'National or international accreditation body overseeing the audit (e.g. NABCB, UKAS, ANSI).', 'spicecraft-core' ); ?></p>
			</td>
		</tr>

		<!-- 3. VALIDITY & EXPIRY -->
		<tr class="form-field">
			<th scope="row"><label for="_sc_cert_status"><?php esc_html_e( 'Controlled Status', 'spicecraft-core' ); ?></label></th>
			<td>
				<select name="_sc_cert_status" id="_sc_cert_status">
					<option value="not_disclosed" <?php selected( ( $meta['status'] ?? '' ), 'not_disclosed' ); ?>><?php esc_html_e( 'Status on Request', 'spicecraft-core' ); ?></option>
					<option value="active" <?php selected( ( $meta['status'] ?? '' ), 'active' ); ?>><?php esc_html_e( 'Active / Valid', 'spicecraft-core' ); ?></option>
					<option value="pending_renewal" <?php selected( ( $meta['status'] ?? '' ), 'pending_renewal' ); ?>><?php esc_html_e( 'Pending Renewal', 'spicecraft-core' ); ?></option>
					<option value="suspended" <?php selected( ( $meta['status'] ?? '' ), 'suspended' ); ?>><?php esc_html_e( 'Suspended', 'spicecraft-core' ); ?></option>
					<option value="expired" <?php selected( ( $meta['status'] ?? '' ), 'expired' ); ?>><?php esc_html_e( 'Expired', 'spicecraft-core' ); ?></option>
					<option value="archived" <?php selected( ( $meta['status'] ?? '' ), 'archived' ); ?>><?php esc_html_e( 'Archived', 'spicecraft-core' ); ?></option>
				</select>
				<p class="description"><?php esc_html_e( 'Controlled status. "Active" must be explicitly chosen and is never assumed.', 'spicecraft-core' ); ?></p>
			</td>
		</tr>

		<tr class="form-field">
			<th scope="row"><label for="_sc_cert_issue_date"><?php esc_html_e( 'Issue Date', 'spicecraft-core' ); ?></label></th>
			<td>
				<input type="date" name="_sc_cert_issue_date" id="_sc_cert_issue_date" value="<?php echo esc_attr( $meta['issue_date'] ?? '' ); ?>">
			</td>
		</tr>

		<tr class="form-field">
			<th scope="row"><label for="_sc_cert_valid_from"><?php esc_html_e( 'Valid From', 'spicecraft-core' ); ?></label></th>
			<td>
				<input type="date" name="_sc_cert_valid_from" id="_sc_cert_valid_from" value="<?php echo esc_attr( $meta['valid_from'] ?? '' ); ?>">
			</td>
		</tr>

		<tr class="form-field">
			<th scope="row"><label for="_sc_cert_expiry_date"><?php esc_html_e( 'Expiry Date', 'spicecraft-core' ); ?></label></th>
			<td>
				<input type="date" name="_sc_cert_expiry_date" id="_sc_cert_expiry_date" value="<?php echo esc_attr( $meta['expiry_date'] ?? '' ); ?>">
				<p class="description"><?php esc_html_e( 'Leave blank if this registration has perpetual or non-expiring validity.', 'spicecraft-core' ); ?></p>
				<?php
				$alert = function_exists( 'spicecraft_get_certification_expiry_alert' ) ? spicecraft_get_certification_expiry_alert( $term->term_id ) : null;
				if ( $alert ) :
					$bg_color = ( 'expired' === $alert['type'] ) ? '#fee2e2' : '#fef3c7';
					$text_color = ( 'expired' === $alert['type'] ) ? '#991b1b' : '#92400e';
					?>
					<div style="margin-top: 8px; padding: 6px 12px; background: <?php echo esc_attr( $bg_color ); ?>; color: <?php echo esc_attr( $text_color ); ?>; border-radius: 4px; display: inline-block; font-weight: 600; font-size: 0.85rem;">
						⚠️ <?php echo esc_html( $alert['label'] ); ?> (Admin Notice)
					</div>
				<?php endif; ?>
			</td>
		</tr>

		<tr class="form-field">
			<th scope="row"><label for="_sc_cert_auto_expiry"><?php esc_html_e( 'Automatic Expiry Detection', 'spicecraft-core' ); ?></label></th>
			<td>
				<label>
					<input type="checkbox" name="_sc_cert_auto_expiry" id="_sc_cert_auto_expiry" value="1" <?php checked( ! empty( $meta['auto_expiry'] ) ); ?>>
					<?php esc_html_e( 'Automatically transition effective status to "Expired" when current date passes Expiry Date', 'spicecraft-core' ); ?>
				</label>
				<p class="description"><?php esc_html_e( 'Default is disabled. When disabled, status is strictly governed by the dropdown above.', 'spicecraft-core' ); ?></p>
			</td>
		</tr>

		<!-- 4. SCOPE & FACILITIES -->
		<tr class="form-field">
			<th scope="row"><label for="_sc_cert_scope"><?php esc_html_e( 'Certification Scope', 'spicecraft-core' ); ?></label></th>
			<td>
				<textarea name="_sc_cert_scope" id="_sc_cert_scope" rows="3" class="large-text"><?php echo esc_textarea( $meta['scope'] ?? '' ); ?></textarea>
				<p class="description"><?php esc_html_e( 'Official scope of certification as stated on the certificate (e.g. Processing, blending, and packing of whole and ground spices).', 'spicecraft-core' ); ?></p>
			</td>
		</tr>

		<tr class="form-field">
			<th scope="row"><label for="_sc_cert_facility_scope"><?php esc_html_e( 'Facility / Location Scope', 'spicecraft-core' ); ?></label></th>
			<td>
				<input type="text" name="_sc_cert_facility_scope" id="_sc_cert_facility_scope" value="<?php echo esc_attr( $meta['facility_scope'] ?? '' ); ?>" class="regular-text">
				<p class="description"><?php esc_html_e( 'Facility units or geographic factory locations covered under this audit.', 'spicecraft-core' ); ?></p>
			</td>
		</tr>

		<!-- 5. MEDIA & DOCUMENTS -->
		<tr class="form-field">
			<th scope="row"><label><?php esc_html_e( 'Certificate Image Preview', 'spicecraft-core' ); ?></label></th>
			<td>
				<?php
				if ( function_exists( 'spicecraft_render_media_uploader' ) ) {
					spicecraft_render_media_uploader(
						'_sc_cert_image_id',
						absint( $meta['image_id'] ?? 0 ),
						__( 'Select Certificate Image', 'spicecraft-core' ),
						__( 'Scanned or digital image preview of the certificate document.', 'spicecraft-core' )
					);
				}
				?>
			</td>
		</tr>

		<tr class="form-field">
			<th scope="row"><label><?php esc_html_e( 'Certificate PDF / Document', 'spicecraft-core' ); ?></label></th>
			<td>
				<?php
				if ( function_exists( 'spicecraft_render_media_uploader' ) ) {
					spicecraft_render_media_uploader(
						'_sc_cert_doc_id',
						absint( $meta['doc_id'] ?? 0 ),
						__( 'Select Certificate Document (PDF)', 'spicecraft-core' ),
						__( 'Upload official certificate PDF or authorized compliance document.', 'spicecraft-core' )
					);
				}
				?>
			</td>
		</tr>

		<tr class="form-field">
			<th scope="row"><label for="_sc_cert_doc_visibility"><?php esc_html_e( 'Certificate Document Visibility', 'spicecraft-core' ); ?></label></th>
			<td>
				<select name="_sc_cert_doc_visibility" id="_sc_cert_doc_visibility">
					<option value="private" <?php selected( ( $meta['doc_visibility'] ?? '' ), 'private' ); ?>><?php esc_html_e( 'Private (Do NOT expose PDF on frontend)', 'spicecraft-core' ); ?></option>
					<option value="public" <?php selected( ( $meta['doc_visibility'] ?? '' ), 'public' ); ?>><?php esc_html_e( 'Public (Offer View/Download button to visitors)', 'spicecraft-core' ); ?></option>
				</select>
				<p class="description"><?php esc_html_e( 'If Private, no document URL or link will appear in frontend HTML or source code.', 'spicecraft-core' ); ?></p>
			</td>
		</tr>

		<!-- 6. VERIFICATION -->
		<tr class="form-field">
			<th scope="row"><label for="_sc_cert_verification_url"><?php esc_html_e( 'Official Verification URL', 'spicecraft-core' ); ?></label></th>
			<td>
				<input type="url" name="_sc_cert_verification_url" id="_sc_cert_verification_url" value="<?php echo esc_url( $meta['verification_url'] ?? '' ); ?>" class="regular-text" placeholder="https://">
				<p class="description"><?php esc_html_e( 'Direct verification link on the issuing registrar or government accreditation portal.', 'spicecraft-core' ); ?></p>
			</td>
		</tr>

		<!-- 7. RELATIONSHIPS -->
		<tr class="form-field">
			<th scope="row"><label><?php esc_html_e( 'Related Products', 'spicecraft-core' ); ?></label></th>
			<td>
				<?php
				if ( function_exists( 'spicecraft_render_admin_product_multiselect' ) ) {
					spicecraft_render_admin_product_multiselect(
						'_sc_cert_related_products',
						$meta['related_products'] ?? array(),
						__( 'Select products covered under this certification.', 'spicecraft-core' )
					);
				}
				?>
			</td>
		</tr>

		<tr class="form-field">
			<th scope="row"><label><?php esc_html_e( 'Related Product Categories', 'spicecraft-core' ); ?></label></th>
			<td>
				<?php
				if ( function_exists( 'spicecraft_render_admin_taxonomy_multiselect' ) ) {
					spicecraft_render_admin_taxonomy_multiselect(
						'product_cat',
						'_sc_cert_related_categories',
						$meta['related_categories'] ?? array(),
						__( 'Select product categories covered under this certification.', 'spicecraft-core' )
					);
				}
				?>
			</td>
		</tr>

		<!-- 8. VISIBILITY & CONTROLS -->
		<tr class="form-field">
			<th scope="row"><label for="_sc_cert_visibility"><?php esc_html_e( 'Public Visibility', 'spicecraft-core' ); ?></label></th>
			<td>
				<select name="_sc_cert_visibility" id="_sc_cert_visibility">
					<option value="public" <?php selected( ( $meta['visibility'] ?? '' ), 'public' ); ?>><?php esc_html_e( 'Public (Render on website)', 'spicecraft-core' ); ?></option>
					<option value="internal" <?php selected( ( $meta['visibility'] ?? '' ), 'internal' ); ?>><?php esc_html_e( 'Internal / Admin Only (Hide from all public views)', 'spicecraft-core' ); ?></option>
				</select>
				<p class="description"><?php esc_html_e( 'Internal certifications will be completely omitted from public grids, detail pages, and product badges.', 'spicecraft-core' ); ?></p>
			</td>
		</tr>

		<tr class="form-field">
			<th scope="row"><label for="_sc_cert_public_detail"><?php esc_html_e( 'Public Detail Page', 'spicecraft-core' ); ?></label></th>
			<td>
				<label>
					<input type="checkbox" name="_sc_cert_public_detail" id="_sc_cert_public_detail" value="1" <?php checked( ! empty( $meta['public_detail'] ) ); ?>>
					<?php esc_html_e( 'Enable standalone public detail page for this certification', 'spicecraft-core' ); ?>
				</label>
				<p class="description"><?php esc_html_e( 'If disabled, this accreditation appears on archive/trust grids but has no clickable dedicated detail page.', 'spicecraft-core' ); ?></p>
			</td>
		</tr>

		<tr class="form-field">
			<th scope="row"><label for="_sc_cert_order"><?php esc_html_e( 'Display Order', 'spicecraft-core' ); ?></label></th>
			<td>
				<input type="number" name="_sc_cert_order" id="_sc_cert_order" value="<?php echo esc_attr( $meta['order'] ?? 10 ); ?>" min="0" step="1" style="width: 100px;">
				<p class="description"><?php esc_html_e( 'Lower numbers display first.', 'spicecraft-core' ); ?></p>
			</td>
		</tr>

		<tr class="form-field">
			<th scope="row"><label for="_sc_cert_notes"><?php esc_html_e( 'Internal Admin Notes', 'spicecraft-core' ); ?></label></th>
			<td>
				<textarea name="_sc_cert_notes" id="_sc_cert_notes" rows="3" class="large-text"><?php echo esc_textarea( $meta['notes'] ?? '' ); ?></textarea>
				<p class="description"><?php esc_html_e( 'Private notes for compliance officers (audit dates, renewal contacts, internal reference codes). Never rendered on frontend.', 'spicecraft-core' ); ?></p>
			</td>
		</tr>
		<?php
	}

	/**
	 * Save metadata when a certification term is created or updated.
	 *
	 * @param int $term_id
	 */
	public function save_term_meta( $term_id ) {
		// Nonce check
		if ( ! isset( $_POST['spicecraft_cert_meta_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['spicecraft_cert_meta_nonce'] ), 'spicecraft_cert_meta_action' ) ) {
			return;
		}

		// Capability check
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		// Text fields
		if ( isset( $_POST['_sc_cert_short_name'] ) ) {
			update_term_meta( $term_id, '_sc_cert_short_name', sanitize_text_field( wp_unslash( $_POST['_sc_cert_short_name'] ) ) );
		}
		if ( isset( $_POST['_sc_cert_number'] ) ) {
			update_term_meta( $term_id, '_sc_cert_number', sanitize_text_field( wp_unslash( $_POST['_sc_cert_number'] ) ) );
		}
		if ( isset( $_POST['_sc_cert_issuing_authority'] ) ) {
			update_term_meta( $term_id, '_sc_cert_issuing_authority', sanitize_text_field( wp_unslash( $_POST['_sc_cert_issuing_authority'] ) ) );
		}
		if ( isset( $_POST['_sc_cert_accreditation_body'] ) ) {
			update_term_meta( $term_id, '_sc_cert_accreditation_body', sanitize_text_field( wp_unslash( $_POST['_sc_cert_accreditation_body'] ) ) );
		}

		// Controlled Status
		$allowed_statuses = array( 'active', 'expired', 'pending_renewal', 'suspended', 'archived', 'not_disclosed' );
		if ( isset( $_POST['_sc_cert_status'] ) && in_array( $_POST['_sc_cert_status'], $allowed_statuses, true ) ) {
			update_term_meta( $term_id, '_sc_cert_status', sanitize_text_field( wp_unslash( $_POST['_sc_cert_status'] ) ) );
		}

		// Dates (YYYY-MM-DD validation)
		$date_fields = array( '_sc_cert_issue_date', '_sc_cert_valid_from', '_sc_cert_expiry_date' );
		foreach ( $date_fields as $df ) {
			if ( isset( $_POST[ $df ] ) ) {
				$val = sanitize_text_field( wp_unslash( $_POST[ $df ] ) );
				if ( empty( $val ) || preg_match( '/^\d{4}-\d{2}-\d{2}$/', $val ) ) {
					update_term_meta( $term_id, $df, $val );
				}
			}
		}

		// Checkboxes
		update_term_meta( $term_id, '_sc_cert_auto_expiry', ! empty( $_POST['_sc_cert_auto_expiry'] ) ? 1 : 0 );
		update_term_meta( $term_id, '_sc_cert_featured', ! empty( $_POST['_sc_cert_featured'] ) ? 1 : 0 );
		update_term_meta( $term_id, '_sc_cert_public_detail', ! empty( $_POST['_sc_cert_public_detail'] ) ? 1 : 0 );

		// Textarea scopes & notes
		if ( isset( $_POST['_sc_cert_scope'] ) ) {
			update_term_meta( $term_id, '_sc_cert_scope', sanitize_textarea_field( wp_unslash( $_POST['_sc_cert_scope'] ) ) );
		}
		if ( isset( $_POST['_sc_cert_facility_scope'] ) ) {
			update_term_meta( $term_id, '_sc_cert_facility_scope', sanitize_text_field( wp_unslash( $_POST['_sc_cert_facility_scope'] ) ) );
		}
		if ( isset( $_POST['_sc_cert_notes'] ) ) {
			update_term_meta( $term_id, '_sc_cert_notes', sanitize_textarea_field( wp_unslash( $_POST['_sc_cert_notes'] ) ) );
		}

		// Media attachment IDs
		$media_fields = array( '_sc_cert_logo_id', '_sc_cert_image_id', '_sc_cert_doc_id' );
		foreach ( $media_fields as $mf ) {
			if ( isset( $_POST[ $mf ] ) ) {
				update_term_meta( $term_id, $mf, absint( $_POST[ $mf ] ) );
			}
		}

		// Verification URL
		if ( isset( $_POST['_sc_cert_verification_url'] ) ) {
			update_term_meta( $term_id, '_sc_cert_verification_url', esc_url_raw( wp_unslash( $_POST['_sc_cert_verification_url'] ) ) );
		}

		// Visibilities
		if ( isset( $_POST['_sc_cert_visibility'] ) && in_array( $_POST['_sc_cert_visibility'], array( 'public', 'internal' ), true ) ) {
			update_term_meta( $term_id, '_sc_cert_visibility', sanitize_text_field( wp_unslash( $_POST['_sc_cert_visibility'] ) ) );
		}
		if ( isset( $_POST['_sc_cert_doc_visibility'] ) && in_array( $_POST['_sc_cert_doc_visibility'], array( 'public', 'private' ), true ) ) {
			update_term_meta( $term_id, '_sc_cert_doc_visibility', sanitize_text_field( wp_unslash( $_POST['_sc_cert_doc_visibility'] ) ) );
		}

		// Display order
		if ( isset( $_POST['_sc_cert_order'] ) ) {
			update_term_meta( $term_id, '_sc_cert_order', (int) $_POST['_sc_cert_order'] );
		}

		// Relationships: Related Products
		if ( isset( $_POST['_sc_cert_related_products'] ) ) {
			$prod_ids = is_array( $_POST['_sc_cert_related_products'] )
				? array_map( 'absint', $_POST['_sc_cert_related_products'] )
				: array();
			update_term_meta( $term_id, '_sc_cert_related_products', array_filter( $prod_ids ) );
		} else {
			update_term_meta( $term_id, '_sc_cert_related_products', array() );
		}

		// Relationships: Related Product Categories
		if ( isset( $_POST['_sc_cert_related_categories'] ) ) {
			$cat_ids = is_array( $_POST['_sc_cert_related_categories'] )
				? array_map( 'absint', $_POST['_sc_cert_related_categories'] )
				: array();
			update_term_meta( $term_id, '_sc_cert_related_categories', array_filter( $cat_ids ) );
		} else {
			update_term_meta( $term_id, '_sc_cert_related_categories', array() );
		}
	}

	/**
	 * Register enhanced admin columns for taxonomy list table.
	 *
	 * @param array $columns
	 * @return array
	 */
	public function register_admin_columns( $columns ) {
		$new_columns = array();
		$new_columns['cb']          = $columns['cb'];
		$new_columns['logo']        = __( 'Logo', 'spicecraft-core' );
		$new_columns['name']        = __( 'Certification Name', 'spicecraft-core' );
		$new_columns['short_name']  = __( 'Short Name', 'spicecraft-core' );
		$new_columns['status']      = __( 'Status', 'spicecraft-core' );
		$new_columns['number']      = __( 'Cert Number', 'spicecraft-core' );
		$new_columns['authority']   = __( 'Authority', 'spicecraft-core' );
		$new_columns['expiry_date'] = __( 'Expiry Date', 'spicecraft-core' );
		$new_columns['visibility']  = __( 'Visibility', 'spicecraft-core' );
		$new_columns['featured']    = __( 'Featured', 'spicecraft-core' );
		$new_columns['order']       = __( 'Order', 'spicecraft-core' );
		$new_columns['posts']       = $columns['posts'] ?? __( 'Products', 'spicecraft-core' );

		return $new_columns;
	}

	/**
	 * Render content for enhanced admin columns.
	 *
	 * @param string $content
	 * @param string $column_name
	 * @param int    $term_id
	 * @return string
	 */
	public function render_admin_column( $content, $column_name, $term_id ) {
		$meta = function_exists( 'spicecraft_get_certification_meta' )
			? spicecraft_get_certification_meta( $term_id )
			: array();

		switch ( $column_name ) {
			case 'logo':
				if ( ! empty( $meta['logo_id'] ) ) {
					return wp_get_attachment_image( $meta['logo_id'], array( 40, 40 ), true, array( 'style' => 'width: 40px; height: 40px; object-fit: contain; border: 1px solid #e2e8f0; border-radius: 4px; padding: 2px; background: #fff;' ) );
				}
				return '<span style="color: #94a3b8;">&mdash;</span>';

			case 'short_name':
				return ! empty( $meta['short_name'] ) ? '<strong>' . esc_html( $meta['short_name'] ) . '</strong>' : '<span style="color: #94a3b8;">&mdash;</span>';

			case 'status':
				$effective = function_exists( 'spicecraft_get_certification_effective_status' )
					? spicecraft_get_certification_effective_status( $term_id )
					: ( $meta['status'] ?? 'not_disclosed' );
				$label = function_exists( 'spicecraft_get_certification_status_label' )
					? spicecraft_get_certification_status_label( $effective )
					: $effective;

				$styles = array(
					'active'          => 'background: #dcfce7; color: #166534;',
					'expired'         => 'background: #fee2e2; color: #991b1b;',
					'pending_renewal' => 'background: #fef3c7; color: #92400e;',
					'suspended'       => 'background: #f1f5f9; color: #475569;',
					'archived'        => 'background: #f1f5f9; color: #64748b;',
					'not_disclosed'   => 'background: #f1f5f9; color: #64748b;',
				);
				$badge_style = isset( $styles[ $effective ] ) ? $styles[ $effective ] : 'background: #f1f5f9; color: #64748b;';

				return sprintf(
					'<span style="display: inline-block; padding: 2px 8px; border-radius: 4px; font-size: 11px; font-weight: 600; %s">%s</span>',
					esc_attr( $badge_style ),
					esc_html( $label )
				);

			case 'number':
				return ! empty( $meta['number'] ) ? '<code>' . esc_html( $meta['number'] ) . '</code>' : '<span style="color: #94a3b8;">&mdash;</span>';

			case 'authority':
				return ! empty( $meta['issuing_authority'] ) ? esc_html( $meta['issuing_authority'] ) : '<span style="color: #94a3b8;">&mdash;</span>';

			case 'expiry_date':
				if ( empty( $meta['expiry_date'] ) ) {
					return '<span style="color: #64748b;">' . esc_html__( 'No Expiry', 'spicecraft-core' ) . '</span>';
				}
				$out = esc_html( $meta['expiry_date'] );
				$alert = function_exists( 'spicecraft_get_certification_expiry_alert' ) ? spicecraft_get_certification_expiry_alert( $term_id ) : null;
				if ( $alert ) {
					$color = ( 'expired' === $alert['type'] ) ? '#dc2626' : '#d97706';
					$out .= '<br><span style="color: ' . esc_attr( $color ) . '; font-size: 10px; font-weight: 600;">⚠️ ' . esc_html( $alert['label'] ) . '</span>';
				}
				return $out;

			case 'visibility':
				$vis = $meta['visibility'] ?? 'public';
				if ( 'internal' === $vis ) {
					return '<span style="display: inline-block; padding: 2px 6px; background: #e0e7ff; color: #3730a3; border-radius: 3px; font-size: 11px; font-weight: 600;">' . esc_html__( 'Internal', 'spicecraft-core' ) . '</span>';
				}
				return '<span style="color: #059669; font-size: 11px; font-weight: 600;">● ' . esc_html__( 'Public', 'spicecraft-core' ) . '</span>';

			case 'featured':
				return ! empty( $meta['featured'] ) ? '⭐ <span style="font-size: 11px; font-weight: 600; color: #d97706;">' . esc_html__( 'Yes', 'spicecraft-core' ) . '</span>' : '<span style="color: #cbd5e1;">&mdash;</span>';

			case 'order':
				return '<strong>' . esc_html( $meta['order'] ?? 10 ) . '</strong>';
		}

		return $content;
	}

	/**
	 * Register sortable admin columns.
	 *
	 * @param array $columns
	 * @return array
	 */
	public function register_sortable_columns( $columns ) {
		$columns['order']       = '_sc_cert_order';
		$columns['expiry_date'] = '_sc_cert_expiry_date';
		$columns['status']      = '_sc_cert_status';
		return $columns;
	}

	/**
	 * Render admin notification for certifications expiring within 90 days.
	 */
	public function render_admin_expiry_notices() {
		$screen = get_current_screen();
		if ( ! $screen || 'spicecraft_certification' !== $screen->taxonomy ) {
			return;
		}

		$terms = get_terms( array(
			'taxonomy'   => 'spicecraft_certification',
			'hide_empty' => false,
		) );

		if ( empty( $terms ) || is_wp_error( $terms ) ) {
			return;
		}

		$expiring_count = 0;
		$expired_count  = 0;

		foreach ( $terms as $term ) {
			$alert = function_exists( 'spicecraft_get_certification_expiry_alert' ) ? spicecraft_get_certification_expiry_alert( $term->term_id ) : null;
			if ( $alert ) {
				if ( 'expired' === $alert['type'] ) {
					$expired_count++;
				} elseif ( 'expiring_soon' === $alert['type'] ) {
					$expiring_count++;
				}
			}
		}

		if ( $expired_count > 0 || $expiring_count > 0 ) {
			?>
			<div class="notice notice-warning is-dismissible" style="border-left-color: #f59e0b;">
				<p>
					<strong><?php esc_html_e( 'SpiceCraft Certification Notice:', 'spicecraft-core' ); ?></strong>
					<?php
					$parts = array();
					if ( $expired_count > 0 ) {
						/* translators: %d: count */
						$parts[] = sprintf( _n( '%d certification has expired', '%d certifications have expired', $expired_count, 'spicecraft-core' ), $expired_count );
					}
					if ( $expiring_count > 0 ) {
						/* translators: %d: count */
						$parts[] = sprintf( _n( '%d certification expiring within 90 days', '%d certifications expiring within 90 days', $expiring_count, 'spicecraft-core' ), $expiring_count );
					}
					echo esc_html( implode( ' · ', $parts ) );
					?>
				</p>
			</div>
			<?php
		}
	}
}
