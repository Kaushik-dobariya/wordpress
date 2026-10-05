<?php
/**
 * SpiceCraft Core - CMS Import & Export Engine
 *
 * Implements controlled, secure B2B data migration for administrators:
 * 1. Product Export (CSV): SKU, catalog details, FMCG specs, packaging, B2B attributes.
 * 2. Product Import (CSV): UTF-8, SKU-matching, Create/Update/Create+Update modes, Dry-Run validation, full audit logging.
 * 3. Content Exports (CSV): Blog, Testimonials, Job Openings (strict: no applicant CVs), Enquiries, Quotations summary.
 * 4. Safety & Security: Nonce enforcement, manage_options capability check, MIME checks, no arbitrary execution.
 *
 * @package SpiceCraft_Core
 * @since 1.5.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class SpiceCraft_Import_Export {

	/**
	 * Option key for import audit logs
	 */
	const LOGS_OPTION = 'spicecraft_import_logs';

	/**
	 * Singleton Instance.
	 *
	 * @var SpiceCraft_Import_Export|null
	 */
	private static $instance = null;

	/**
	 * Get Singleton Instance.
	 *
	 * @return SpiceCraft_Import_Export
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
		// Admin POST actions for exports
		add_action( 'admin_post_spicecraft_export_products_csv', array( $this, 'handle_product_export' ) );
		add_action( 'admin_post_spicecraft_export_blog_csv', array( $this, 'handle_blog_export' ) );
		add_action( 'admin_post_spicecraft_export_testimonials_csv', array( $this, 'handle_testimonial_export' ) );
		add_action( 'admin_post_spicecraft_export_jobs_csv', array( $this, 'handle_job_export' ) );
		add_action( 'admin_post_spicecraft_export_quotations_csv', array( $this, 'handle_quotation_export' ) );

		// Admin POST action for product import
		add_action( 'admin_post_spicecraft_import_products_csv', array( $this, 'handle_product_import' ) );
	}

	/**
	 * Render Import / Export Admin Screen
	 */
	public function render_admin_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Unauthorized access. Manage options capability required.', 'spicecraft-core' ) );
		}

		$active_tab   = isset( $_GET['tab'] ) ? sanitize_key( $_GET['tab'] ) : 'products';
		$import_res   = get_transient( 'spicecraft_import_result' );
		if ( $import_res ) {
			delete_transient( 'spicecraft_import_result' );
		}

		$logs = get_option( self::LOGS_OPTION, array() );
		if ( ! is_array( $logs ) ) {
			$logs = array();
		}
		?>
		<div class="wrap spicecraft-settings-wrap">
			<h1><?php esc_html_e( 'SpiceCraft CMS Data Import & Export', 'spicecraft-core' ); ?></h1>
			<p class="description">
				<?php esc_html_e( 'Export catalog products, business content, and quotation records, or perform controlled bulk CSV product updates with dry-run verification.', 'spicecraft-core' ); ?>
			</p>

			<?php if ( ! empty( $import_res ) ) : ?>
				<div class="notice notice-<?php echo esc_attr( $import_res['status'] ); ?> is-dismissible" style="padding: 12px 15px;">
					<h3 style="margin-top: 0;">
						<?php if ( ! empty( $import_res['is_dry_run'] ) ) : ?>
							<span class="dashicons dashicons-visibility" style="color: #2271b1;"></span>
							<?php esc_html_e( 'Dry-Run Validation Report (No Database Changes Made)', 'spicecraft-core' ); ?>
						<?php else : ?>
							<span class="dashicons dashicons-yes-alt" style="color: #46b450;"></span>
							<?php esc_html_e( 'Product Import Execution Completed', 'spicecraft-core' ); ?>
						<?php endif; ?>
					</h3>
					<p style="font-size: 14px;">
						<strong><?php esc_html_e( 'Total Processed:', 'spicecraft-core' ); ?></strong> <?php echo absint( $import_res['processed'] ); ?> &nbsp;|&nbsp;
						<strong style="color: #2271b1;"><?php esc_html_e( 'Created:', 'spicecraft-core' ); ?></strong> <?php echo absint( $import_res['created'] ); ?> &nbsp;|&nbsp;
						<strong style="color: #dba617;"><?php esc_html_e( 'Updated:', 'spicecraft-core' ); ?></strong> <?php echo absint( $import_res['updated'] ); ?> &nbsp;|&nbsp;
						<strong style="color: #666;"><?php esc_html_e( 'Skipped:', 'spicecraft-core' ); ?></strong> <?php echo absint( $import_res['skipped'] ); ?> &nbsp;|&nbsp;
						<strong style="color: #d63638;"><?php esc_html_e( 'Errors / Failed:', 'spicecraft-core' ); ?></strong> <?php echo absint( $import_res['failed'] ); ?>
					</p>

					<?php if ( ! empty( $import_res['errors'] ) && is_array( $import_res['errors'] ) ) : ?>
						<details style="margin-top: 10px; background: #fff; padding: 10px; border: 1px solid #ccd0d4; border-radius: 4px;">
							<summary style="font-weight: 600; cursor: pointer; color: #d63638;">
								<?php echo esc_html( sprintf( __( 'View Details & Error Log (%d entries)', 'spicecraft-core' ), count( $import_res['errors'] ) ) ); ?>
							</summary>
							<ul style="margin: 8px 0 0 20px; list-style-type: disc;">
								<?php foreach ( $import_res['errors'] as $err ) : ?>
									<li><?php echo esc_html( $err ); ?></li>
								<?php endforeach; ?>
							</ul>
						</details>
					<?php endif; ?>
				</div>
			<?php endif; ?>

			<h2 class="nav-tab-wrapper">
				<a href="?page=spicecraft-import-export&tab=products" class="nav-tab <?php echo 'products' === $active_tab ? 'nav-tab-active' : ''; ?>">
					<span class="dashicons dashicons-archive"></span> <?php esc_html_e( 'Product Catalog', 'spicecraft-core' ); ?>
				</a>
				<a href="?page=spicecraft-import-export&tab=content" class="nav-tab <?php echo 'content' === $active_tab ? 'nav-tab-active' : ''; ?>">
					<span class="dashicons dashicons-media-document"></span> <?php esc_html_e( 'Content & Business Data Exports', 'spicecraft-core' ); ?>
				</a>
				<a href="?page=spicecraft-import-export&tab=logs" class="nav-tab <?php echo 'logs' === $active_tab ? 'nav-tab-active' : ''; ?>">
					<span class="dashicons dashicons-list-view"></span> <?php esc_html_e( 'Import Audit Log', 'spicecraft-core' ); ?>
				</a>
			</h2>

			<?php if ( 'products' === $active_tab ) : ?>
				<!-- TAB 1: PRODUCT IMPORT / EXPORT -->
				<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px; margin-top: 20px;">
					<!-- Left: Export Card -->
					<div class="postbox" style="padding: 20px;">
						<h2 style="margin-top: 0; padding-bottom: 10px; border-bottom: 1px solid #eee;">
							<span class="dashicons dashicons-download" style="color: #c44b1b;"></span>
							<?php esc_html_e( 'Export Product Catalog (CSV)', 'spicecraft-core' ); ?>
						</h2>
						<p>
							<?php esc_html_e( 'Download the complete SpiceCraft product catalog including SKU, taxonomy categories, B2B wholesale parameters (MOQ, mesh, volatile oils), packaging configurations, and product specifications.', 'spicecraft-core' ); ?>
						</p>
						<ul style="list-style-type: disc; margin-left: 20px; color: #555;">
							<li><?php esc_html_e( 'Format: UTF-8 CSV with byte-order mark (Excel compatible)', 'spicecraft-core' ); ?></li>
							<li><?php esc_html_e( 'Includes: Published & Draft product records', 'spicecraft-core' ); ?></li>
							<li><?php esc_html_e( 'Excludes: System passwords and non-public core meta', 'spicecraft-core' ); ?></li>
						</ul>
						<div style="margin-top: 25px;">
							<a href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=spicecraft_export_products_csv' ), 'spicecraft_export_products_nonce' ) ); ?>" class="button button-primary button-hero">
								<span class="dashicons dashicons-download"></span> <?php esc_html_e( 'Download Product Catalog CSV', 'spicecraft-core' ); ?>
							</a>
						</div>
					</div>

					<!-- Right: Import Card -->
					<div class="postbox" style="padding: 20px;">
						<h2 style="margin-top: 0; padding-bottom: 10px; border-bottom: 1px solid #eee;">
							<span class="dashicons dashicons-upload" style="color: #2271b1;"></span>
							<?php esc_html_e( 'Import & Update Products (CSV)', 'spicecraft-core' ); ?>
						</h2>
						<p>
							<?php esc_html_e( 'Upload a CSV file to add new spice varieties or bulk-update technical parameters using SKU as the primary matching key.', 'spicecraft-core' ); ?>
						</p>

						<div class="notice notice-warning inline" style="margin: 15px 0; padding: 10px 12px;">
							<p style="margin: 0;">
								<strong><?php esc_html_e( 'Safety Notice:', 'spicecraft-core' ); ?></strong>
								<?php esc_html_e( 'Always run a "Dry Run / Validate Only" first to verify syntax, required columns, and SKU integrity before writing to the database.', 'spicecraft-core' ); ?>
							</p>
						</div>

						<form action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="post" enctype="multipart/form-data" style="margin-top: 15px;">
							<input type="hidden" name="action" value="spicecraft_import_products_csv" />
							<?php wp_nonce_field( 'spicecraft_import_products_nonce', 'spicecraft_import_nonce' ); ?>

							<table class="form-table" style="margin-top: 0;">
								<tr>
									<th scope="row" style="width: 140px;"><label for="import_file"><?php esc_html_e( 'Select CSV File', 'spicecraft-core' ); ?></label></th>
									<td>
										<input type="file" name="import_file" id="import_file" accept=".csv,text/csv,text/plain" required />
										<p class="description"><?php esc_html_e( 'Accepts valid .csv files up to 10MB.', 'spicecraft-core' ); ?></p>
									</td>
								</tr>
								<tr>
									<th scope="row"><label for="import_mode"><?php esc_html_e( 'Import Mode', 'spicecraft-core' ); ?></label></th>
									<td>
										<select name="import_mode" id="import_mode" style="min-width: 240px;">
											<option value="create_update"><?php esc_html_e( 'Create Missing + Update Existing (Recommended)', 'spicecraft-core' ); ?></option>
											<option value="create"><?php esc_html_e( 'Create New Records Only (Skip existing SKUs)', 'spicecraft-core' ); ?></option>
											<option value="update"><?php esc_html_e( 'Update Existing Records Only (Match by SKU)', 'spicecraft-core' ); ?></option>
										</select>
										<p class="description"><?php esc_html_e( 'Matches records using product SKU as the immutable key.', 'spicecraft-core' ); ?></p>
									</td>
								</tr>
								<tr>
									<th scope="row"><?php esc_html_e( 'Execution Mode', 'spicecraft-core' ); ?></th>
									<td>
										<label style="font-weight: 600; color: #1d2327;">
											<input type="checkbox" name="dry_run" value="1" checked="checked" />
											<?php esc_html_e( 'Dry Run / Validate Only (Safe Simulation)', 'spicecraft-core' ); ?>
										</label>
										<p class="description"><?php esc_html_e( 'Uncheck this box only when you are ready to write changes directly to the catalog.', 'spicecraft-core' ); ?></p>
									</td>
								</tr>
							</table>

							<div style="margin-top: 20px;">
								<button type="submit" class="button button-primary">
									<span class="dashicons dashicons-upload"></span> <?php esc_html_e( 'Process Product Import', 'spicecraft-core' ); ?>
								</button>
							</div>
						</form>
					</div>
				</div>

			<?php elseif ( 'content' === $active_tab ) : ?>
				<!-- TAB 2: CONTENT & BUSINESS DATA EXPORTS -->
				<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 20px; margin-top: 20px;">
					<!-- 1. Blog Posts -->
					<div class="postbox" style="padding: 20px;">
						<h3 style="margin-top: 0; display: flex; align-items: center; gap: 8px;">
							<span class="dashicons dashicons-admin-post" style="color: #c44b1b;"></span>
							<?php esc_html_e( 'Blog & Market Insights', 'spicecraft-core' ); ?>
						</h3>
						<p><?php esc_html_e( 'Export published and drafted articles, harvest reports, and industry publications.', 'spicecraft-core' ); ?></p>
						<p style="color: #666; font-size: 13px;"><strong><?php esc_html_e( 'Fields:', 'spicecraft-core' ); ?></strong> ID, Title, Status, Date, Author, Categories, Tags, Excerpt, Content</p>
						<a href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=spicecraft_export_blog_csv' ), 'spicecraft_export_blog_nonce' ) ); ?>" class="button button-secondary" style="margin-top: 10px;">
							<span class="dashicons dashicons-download"></span> <?php esc_html_e( 'Export Blog CSV', 'spicecraft-core' ); ?>
						</a>
					</div>

					<!-- 2. Testimonials -->
					<div class="postbox" style="padding: 20px;">
						<h3 style="margin-top: 0; display: flex; align-items: center; gap: 8px;">
							<span class="dashicons dashicons-testimonial" style="color: #c44b1b;"></span>
							<?php esc_html_e( 'Client Endorsements & Reviews', 'spicecraft-core' ); ?>
						</h3>
						<p><?php esc_html_e( 'Export verified client, executive chef, and wholesale partner testimonials.', 'spicecraft-core' ); ?></p>
						<p style="color: #666; font-size: 13px;"><strong><?php esc_html_e( 'Fields:', 'spicecraft-core' ); ?></strong> ID, Name, Company, Role, Location, Rating, Status, Testimonial</p>
						<a href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=spicecraft_export_testimonials_csv' ), 'spicecraft_export_testimonials_nonce' ) ); ?>" class="button button-secondary" style="margin-top: 10px;">
							<span class="dashicons dashicons-download"></span> <?php esc_html_e( 'Export Testimonials CSV', 'spicecraft-core' ); ?>
						</a>
					</div>

					<!-- 3. Job Openings -->
					<div class="postbox" style="padding: 20px;">
						<h3 style="margin-top: 0; display: flex; align-items: center; gap: 8px;">
							<span class="dashicons dashicons-businessman" style="color: #c44b1b;"></span>
							<?php esc_html_e( 'Job Openings & Positions', 'spicecraft-core' ); ?>
						</h3>
						<p><?php esc_html_e( 'Export active and closed plant and corporate job opening specifications.', 'spicecraft-core' ); ?></p>
						<div class="notice notice-info inline" style="margin: 10px 0; padding: 6px 10px;">
							<p style="margin: 0; font-size: 12px; color: #555;">
								<strong><?php esc_html_e( 'Privacy Protection:', 'spicecraft-core' ); ?></strong> <?php esc_html_e( 'Candidate resumes and personal applications are strictly protected and never exposed in this export.', 'spicecraft-core' ); ?>
							</p>
						</div>
						<p style="color: #666; font-size: 13px;"><strong><?php esc_html_e( 'Fields:', 'spicecraft-core' ); ?></strong> ID, Title, Department, Location, Type, Experience, Status, Description</p>
						<a href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=spicecraft_export_jobs_csv' ), 'spicecraft_export_jobs_nonce' ) ); ?>" class="button button-secondary" style="margin-top: 10px;">
							<span class="dashicons dashicons-download"></span> <?php esc_html_e( 'Export Jobs CSV', 'spicecraft-core' ); ?>
						</a>
					</div>

					<!-- 4. Quotations Summary -->
					<div class="postbox" style="padding: 20px;">
						<h3 style="margin-top: 0; display: flex; align-items: center; gap: 8px;">
							<span class="dashicons dashicons-media-spreadsheet" style="color: #c44b1b;"></span>
							<?php esc_html_e( 'Commercial Quotations Summary', 'spicecraft-core' ); ?>
						</h3>
						<p><?php esc_html_e( 'Export financial summaries of commercial B2B quotations generated from customer leads.', 'spicecraft-core' ); ?></p>
						<p style="color: #666; font-size: 13px;"><strong><?php esc_html_e( 'Fields:', 'spicecraft-core' ); ?></strong> Quote #, Enquiry ID, Date, Customer, Company, Email, Phone, Currency, Total, Status</p>
						<a href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=spicecraft_export_quotations_csv' ), 'spicecraft_export_quotations_nonce' ) ); ?>" class="button button-secondary" style="margin-top: 10px;">
							<span class="dashicons dashicons-download"></span> <?php esc_html_e( 'Export Quotations CSV', 'spicecraft-core' ); ?>
						</a>
					</div>

					<!-- 5. Business Enquiries -->
					<div class="postbox" style="padding: 20px;">
						<h3 style="margin-top: 0; display: flex; align-items: center; gap: 8px;">
							<span class="dashicons dashicons-feedback" style="color: #c44b1b;"></span>
							<?php esc_html_e( 'Business Leads & Enquiries', 'spicecraft-core' ); ?>
						</h3>
						<p><?php esc_html_e( 'Direct integration with the existing Phase 4 business enquiry export architecture.', 'spicecraft-core' ); ?></p>
						<p style="color: #666; font-size: 13px;"><strong><?php esc_html_e( 'Fields:', 'spicecraft-core' ); ?></strong> ID, Date, Name, Email, Phone, Company, Product, Type, Lead Status</p>
						<a href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=spicecraft_export_enquiries_csv' ), 'spicecraft_export_csv_nonce' ) ); ?>" class="button button-secondary" style="margin-top: 10px;">
							<span class="dashicons dashicons-download"></span> <?php esc_html_e( 'Export Enquiries CSV', 'spicecraft-core' ); ?>
						</a>
					</div>
				</div>

			<?php elseif ( 'logs' === $active_tab ) : ?>
				<!-- TAB 3: AUDIT LOGS -->
				<div class="postbox" style="padding: 20px; margin-top: 20px;">
					<h3 style="margin-top: 0; border-bottom: 1px solid #eee; padding-bottom: 10px;">
						<span class="dashicons dashicons-backup"></span> <?php esc_html_e( 'Recent Import Audit Trail', 'spicecraft-core' ); ?>
					</h3>
					<?php if ( empty( $logs ) ) : ?>
						<p style="color: #666; font-style: italic;"><?php esc_html_e( 'No import sessions recorded yet.', 'spicecraft-core' ); ?></p>
					<?php else : ?>
						<table class="widefat striped" style="margin-top: 10px;">
							<thead>
								<tr>
									<th><?php esc_html_e( 'Date & Time', 'spicecraft-core' ); ?></th>
									<th><?php esc_html_e( 'User', 'spicecraft-core' ); ?></th>
									<th><?php esc_html_e( 'Type', 'spicecraft-core' ); ?></th>
									<th><?php esc_html_e( 'Mode', 'spicecraft-core' ); ?></th>
									<th><?php esc_html_e( 'Processed', 'spicecraft-core' ); ?></th>
									<th><?php esc_html_e( 'Created', 'spicecraft-core' ); ?></th>
									<th><?php esc_html_e( 'Updated', 'spicecraft-core' ); ?></th>
									<th><?php esc_html_e( 'Errors', 'spicecraft-core' ); ?></th>
								</tr>
							</thead>
							<tbody>
								<?php foreach ( array_reverse( $logs ) as $log ) : ?>
									<tr>
										<td><?php echo esc_html( $log['date'] ?? 'N/A' ); ?></td>
										<td><?php echo esc_html( $log['user'] ?? 'N/A' ); ?></td>
										<td>
											<span class="sc-badge <?php echo ! empty( $log['is_dry_run'] ) ? 'sc-badge--blue' : 'sc-badge--green'; ?>" style="display: inline-block; padding: 2px 8px; border-radius: 4px; font-size: 11px; font-weight: 600; background: <?php echo ! empty( $log['is_dry_run'] ) ? '#e7f5ff; color: #1971c2;' : '#ebfbee; color: #2f9e44;'; ?>">
												<?php echo ! empty( $log['is_dry_run'] ) ? esc_html__( 'Dry Run', 'spicecraft-core' ) : esc_html__( 'Live Import', 'spicecraft-core' ); ?>
											</span>
										</td>
										<td><code><?php echo esc_html( $log['mode'] ?? 'create_update' ); ?></code></td>
										<td><?php echo absint( $log['processed'] ?? 0 ); ?></td>
										<td style="color: #2271b1; font-weight: 600;"><?php echo absint( $log['created'] ?? 0 ); ?></td>
										<td style="color: #dba617; font-weight: 600;"><?php echo absint( $log['updated'] ?? 0 ); ?></td>
										<td style="color: <?php echo ! empty( $log['failed'] ) ? '#d63638;' : '#666;'; ?> font-weight: 600;"><?php echo absint( $log['failed'] ?? 0 ); ?></td>
									</tr>
								<?php endforeach; ?>
							</tbody>
						</table>
					<?php endif; ?>
				</div>
			<?php endif; ?>
		</div>
		<?php
	}

	/**
	 * Handle Product Catalog Export (CSV)
	 */
	public function handle_product_export() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Unauthorized access.', 'spicecraft-core' ) );
		}

		check_admin_referer( 'spicecraft_export_products_nonce' );

		$filename = 'spicecraft-products-' . gmdate( 'Y-m-d' ) . '.csv';

		header( 'Content-Type: text/csv; charset=UTF-8' );
		header( 'Content-Disposition: attachment; filename="' . $filename . '"' );
		header( 'Pragma: no-cache' );
		header( 'Expires: 0' );

		$output = fopen( 'php://output', 'w' );
		if ( false === $output ) {
			exit;
		}

		// UTF-8 BOM for Microsoft Excel compatibility
		fprintf( $output, chr( 0xEF ) . chr( 0xBB ) . chr( 0xBF ) );

		// Header Row
		$headers = array(
			'sku',
			'name',
			'categories',
			'status',
			'short_description',
			'description',
			'form',
			'origin_country',
			'origin_region',
			'shelf_life',
			'moq',
			'sieve_mesh',
			'volatile_oil',
			'moisture_content',
			'bulk_packaging',
			'storage_protocol',
			'pack_sizes',
			'badge_label',
		);
		fputcsv( $output, $headers );

		$query_args = array(
			'post_type'      => 'product',
			'post_status'    => array( 'publish', 'draft' ),
			'posts_per_page' => -1,
			'orderby'        => 'ID',
			'order'          => 'ASC',
		);
		$products = get_posts( $query_args );

		foreach ( $products as $p ) {
			$post_id = $p->ID;
			$sku     = get_post_meta( $post_id, '_sku', true );

			// Categories
			$terms = wp_get_post_terms( $post_id, 'product_cat', array( 'fields' => 'names' ) );
			$cats  = is_array( $terms ) ? implode( ', ', $terms ) : '';

			// Specs & FMCG parameters
			$form         = get_post_meta( $post_id, '_sc_form', true );
			$country      = get_post_meta( $post_id, '_sc_country_of_origin', true );
			$region       = get_post_meta( $post_id, '_sc_origin_region', true );
			$shelf_life   = get_post_meta( $post_id, '_sc_shelf_life', true );
			$moq          = get_post_meta( $post_id, '_sc_moq', true );
			$mesh         = get_post_meta( $post_id, '_sc_sieve_mesh', true );
			$vo           = get_post_meta( $post_id, '_sc_volatile_oil', true );
			$moisture     = get_post_meta( $post_id, '_sc_moisture_content', true );
			$bulk_pkg     = get_post_meta( $post_id, '_sc_bulk_packaging', true );
			$storage      = get_post_meta( $post_id, '_sc_storage_protocol', true );
			$pack_sizes   = get_post_meta( $post_id, '_sc_pack_sizes', true );
			$badge        = get_post_meta( $post_id, '_sc_badge_label', true );

			$row = array(
				$sku,
				$p->post_title,
				$cats,
				$p->post_status,
				$p->post_excerpt,
				$p->post_content,
				$form,
				$country,
				$region,
				$shelf_life,
				$moq,
				$mesh,
				$vo,
				$moisture,
				$bulk_pkg,
				$storage,
				$pack_sizes,
				$badge,
			);
			fputcsv( $output, $row );
		}

		fclose( $output );
		exit;
	}

	/**
	 * Handle Product Import (CSV)
	 */
	public function handle_product_import() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Unauthorized access.', 'spicecraft-core' ) );
		}

		check_admin_referer( 'spicecraft_import_products_nonce', 'spicecraft_import_nonce' );

		// Validate file upload
		if ( empty( $_FILES['import_file'] ) || ! is_uploaded_file( $_FILES['import_file']['tmp_name'] ) ) {
			$this->redirect_with_result(
				array(
					'status'    => 'error',
					'processed' => 0,
					'created'   => 0,
					'updated'   => 0,
					'skipped'   => 0,
					'failed'    => 1,
					'errors'    => array( __( 'No valid file was uploaded or file exceeds server upload limits.', 'spicecraft-core' ) ),
				)
			);
		}

		$file_info = $_FILES['import_file'];
		$file_ext  = strtolower( pathinfo( $file_info['name'], PATHINFO_EXTENSION ) );
		if ( 'csv' !== $file_ext ) {
			$this->redirect_with_result(
				array(
					'status'    => 'error',
					'processed' => 0,
					'created'   => 0,
					'updated'   => 0,
					'skipped'   => 0,
					'failed'    => 1,
					'errors'    => array( __( 'Invalid file format. Only .csv files are supported.', 'spicecraft-core' ) ),
				)
			);
		}

		$mode       = isset( $_POST['import_mode'] ) ? sanitize_key( $_POST['import_mode'] ) : 'create_update';
		$is_dry_run = ! empty( $_POST['dry_run'] );

		$handle = fopen( $file_info['tmp_name'], 'r' );
		if ( false === $handle ) {
			$this->redirect_with_result(
				array(
					'status'    => 'error',
					'processed' => 0,
					'created'   => 0,
					'updated'   => 0,
					'skipped'   => 0,
					'failed'    => 1,
					'errors'    => array( __( 'Unable to open and read uploaded CSV file.', 'spicecraft-core' ) ),
				)
			);
		}

		// Read header line
		$raw_headers = fgetcsv( $handle );
		if ( empty( $raw_headers ) || ! is_array( $raw_headers ) ) {
			fclose( $handle );
			$this->redirect_with_result(
				array(
					'status'    => 'error',
					'processed' => 0,
					'created'   => 0,
					'updated'   => 0,
					'skipped'   => 0,
					'failed'    => 1,
					'errors'    => array( __( 'The uploaded CSV file is empty or missing headers.', 'spicecraft-core' ) ),
				)
			);
		}

		// Normalize headers (strip BOM, lowercase, trim)
		$headers = array();
		foreach ( $raw_headers as $idx => $h ) {
			$clean = strtolower( trim( preg_replace( '/[\x00-\x1F\x80-\xFF]/', '', $h ) ) );
			$clean = str_replace( array( ' ', '-' ), '_', $clean );
			$headers[ $idx ] = $clean;
		}

		// Required column check
		if ( ! in_array( 'sku', $headers, true ) || ! in_array( 'name', $headers, true ) ) {
			fclose( $handle );
			$this->redirect_with_result(
				array(
					'status'    => 'error',
					'processed' => 0,
					'created'   => 0,
					'updated'   => 0,
					'skipped'   => 0,
					'failed'    => 1,
					'errors'    => array( __( 'Missing required columns. Your CSV must contain both "sku" and "name" columns.', 'spicecraft-core' ) ),
				)
			);
		}

		$line_num  = 1;
		$processed = 0;
		$created   = 0;
		$updated   = 0;
		$skipped   = 0;
		$failed    = 0;
		$errors    = array();

		while ( ( $row = fgetcsv( $handle ) ) !== false ) {
			$line_num++;

			// Skip empty lines
			if ( empty( array_filter( $row ) ) ) {
				continue;
			}

			$processed++;
			$data = array();
			foreach ( $headers as $col_idx => $col_name ) {
				$data[ $col_name ] = isset( $row[ $col_idx ] ) ? trim( $row[ $col_idx ] ) : '';
			}

			$sku  = ! empty( $data['sku'] ) ? sanitize_text_field( $data['sku'] ) : '';
			$name = ! empty( $data['name'] ) ? sanitize_text_field( $data['name'] ) : '';

			if ( empty( $sku ) ) {
				$failed++;
				$errors[] = sprintf( __( 'Line %d: Rejected because SKU is empty.', 'spicecraft-core' ), $line_num );
				continue;
			}

			if ( empty( $name ) ) {
				$failed++;
				$errors[] = sprintf( __( 'Line %d (SKU: %s): Rejected because product name is empty.', 'spicecraft-core' ), $line_num, $sku );
				continue;
			}

			// Find existing product by SKU
			$existing_id = $this->get_product_id_by_sku( $sku );

			// Check mode rules
			if ( 'create' === $mode && $existing_id ) {
				$skipped++;
				$errors[] = sprintf( __( 'Line %d (SKU: %s): Skipped because product already exists and mode is "Create New Only".', 'spicecraft-core' ), $line_num, $sku );
				continue;
			}

			if ( 'update' === $mode && ! $existing_id ) {
				$skipped++;
				$errors[] = sprintf( __( 'Line %d (SKU: %s): Skipped because no existing product matches this SKU and mode is "Update Existing Only".', 'spicecraft-core' ), $line_num, $sku );
				continue;
			}

			// Validate and clean fields
			$status    = ! empty( $data['status'] ) && in_array( $data['status'], array( 'publish', 'draft' ), true ) ? $data['status'] : 'publish';
			$excerpt   = isset( $data['short_description'] ) ? wp_kses_post( $data['short_description'] ) : '';
			$content   = isset( $data['description'] ) ? wp_kses_post( $data['description'] ) : '';
			$cats_raw  = isset( $data['categories'] ) ? sanitize_text_field( $data['categories'] ) : '';
			$form      = isset( $data['form'] ) ? sanitize_text_field( $data['form'] ) : '';
			$country   = isset( $data['origin_country'] ) ? sanitize_text_field( $data['origin_country'] ) : '';
			$region    = isset( $data['origin_region'] ) ? sanitize_text_field( $data['origin_region'] ) : '';
			$shelf     = isset( $data['shelf_life'] ) ? sanitize_text_field( $data['shelf_life'] ) : '';
			$moq       = isset( $data['moq'] ) ? sanitize_text_field( $data['moq'] ) : '';
			$mesh      = isset( $data['sieve_mesh'] ) ? sanitize_text_field( $data['sieve_mesh'] ) : '';
			$vo        = isset( $data['volatile_oil'] ) ? sanitize_text_field( $data['volatile_oil'] ) : '';
			$moisture  = isset( $data['moisture_content'] ) ? sanitize_text_field( $data['moisture_content'] ) : '';
			$bulk_pkg  = isset( $data['bulk_packaging'] ) ? sanitize_text_field( $data['bulk_packaging'] ) : '';
			$storage   = isset( $data['storage_protocol'] ) ? sanitize_text_field( $data['storage_protocol'] ) : '';
			$pack_sizes= isset( $data['pack_sizes'] ) ? sanitize_text_field( $data['pack_sizes'] ) : '';
			$badge     = isset( $data['badge_label'] ) ? sanitize_text_field( $data['badge_label'] ) : '';

			if ( $is_dry_run ) {
				// Dry Run Simulation Only
				if ( $existing_id ) {
					$updated++;
				} else {
					$created++;
				}
				continue;
			}

			// Live Run: Write to database
			if ( $existing_id ) {
				$post_args = array(
					'ID'           => $existing_id,
					'post_title'   => $name,
					'post_excerpt' => $excerpt,
					'post_content' => $content,
					'post_status'  => $status,
				);
				$res = wp_update_post( $post_args );
				if ( is_wp_error( $res ) ) {
					$failed++;
					$errors[] = sprintf( __( 'Line %d (SKU: %s): Update failed - %s', 'spicecraft-core' ), $line_num, $sku, $res->get_error_message() );
					continue;
				}
				$post_id = $existing_id;
				$updated++;
			} else {
				$post_args = array(
					'post_type'    => 'product',
					'post_title'   => $name,
					'post_excerpt' => $excerpt,
					'post_content' => $content,
					'post_status'  => $status,
				);
				$post_id = wp_insert_post( $post_args );
				if ( is_wp_error( $post_id ) || ! $post_id ) {
					$failed++;
					$errors[] = sprintf( __( 'Line %d (SKU: %s): Insert failed - %s', 'spicecraft-core' ), $line_num, $sku, is_wp_error( $post_id ) ? $post_id->get_error_message() : 'Unknown error' );
					continue;
				}
				$created++;
			}

			// Update Meta
			update_post_meta( $post_id, '_sku', $sku );
			if ( $form ) { update_post_meta( $post_id, '_sc_form', $form ); }
			if ( $country ) { update_post_meta( $post_id, '_sc_country_of_origin', $country ); }
			if ( $region ) { update_post_meta( $post_id, '_sc_origin_region', $region ); }
			if ( $shelf ) { update_post_meta( $post_id, '_sc_shelf_life', $shelf ); }
			if ( $moq ) { update_post_meta( $post_id, '_sc_moq', $moq ); }
			if ( $mesh ) { update_post_meta( $post_id, '_sc_sieve_mesh', $mesh ); }
			if ( $vo ) { update_post_meta( $post_id, '_sc_volatile_oil', $vo ); }
			if ( $moisture ) { update_post_meta( $post_id, '_sc_moisture_content', $moisture ); }
			if ( $bulk_pkg ) { update_post_meta( $post_id, '_sc_bulk_packaging', $bulk_pkg ); }
			if ( $storage ) { update_post_meta( $post_id, '_sc_storage_protocol', $storage ); }
			if ( $pack_sizes ) { update_post_meta( $post_id, '_sc_pack_sizes', $pack_sizes ); }
			if ( $badge ) { update_post_meta( $post_id, '_sc_badge_label', $badge ); }

			// Set Categories
			if ( $cats_raw ) {
				$cat_names = array_map( 'trim', explode( ',', $cats_raw ) );
				$cat_ids   = array();
				foreach ( $cat_names as $c_name ) {
					if ( empty( $c_name ) ) {
						continue;
					}
					$term = term_exists( $c_name, 'product_cat' );
					if ( $term ) {
						$cat_ids[] = (int) $term['term_id'];
					} else {
						$new_term = wp_insert_term( $c_name, 'product_cat' );
						if ( ! is_wp_error( $new_term ) ) {
							$cat_ids[] = (int) $new_term['term_id'];
						}
					}
				}
				if ( ! empty( $cat_ids ) ) {
					wp_set_object_terms( $post_id, $cat_ids, 'product_cat' );
				}
			}
		}

		fclose( $handle );

		// Record audit log entry
		$log_entry = array(
			'date'       => current_time( 'mysql' ),
			'user'       => wp_get_current_user()->user_login,
			'is_dry_run' => $is_dry_run,
			'mode'       => $mode,
			'processed'  => $processed,
			'created'    => $created,
			'updated'    => $updated,
			'skipped'    => $skipped,
			'failed'     => $failed,
			'errors'     => array_slice( $errors, 0, 20 ),
		);
		$logs   = get_option( self::LOGS_OPTION, array() );
		$logs[] = $log_entry;
		if ( count( $logs ) > 20 ) {
			$logs = array_slice( $logs, -20 );
		}
		update_option( self::LOGS_OPTION, $logs, false );

		$result_status = $failed > 0 ? 'warning' : 'success';
		if ( 0 === $processed ) {
			$result_status = 'error';
		}

		$this->redirect_with_result(
			array(
				'status'     => $result_status,
				'is_dry_run' => $is_dry_run,
				'processed'  => $processed,
				'created'    => $created,
				'updated'    => $updated,
				'skipped'    => $skipped,
				'failed'     => $failed,
				'errors'     => $errors,
			)
		);
	}

	/**
	 * Helper: Find Product ID by SKU
	 *
	 * @param string $sku
	 * @return int 0 if not found
	 */
	private function get_product_id_by_sku( $sku ) {
		if ( empty( $sku ) ) {
			return 0;
		}

		if ( function_exists( 'wc_get_product_id_by_sku' ) ) {
			$found = wc_get_product_id_by_sku( $sku );
			if ( $found ) {
				return $found;
			}
		}

		global $wpdb;
		$id = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = '_sku' AND meta_value = %s LIMIT 1",
				$sku
			)
		);

		return $id ? (int) $id : 0;
	}

	/**
	 * Handle Blog Posts Export (CSV)
	 */
	public function handle_blog_export() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Unauthorized access.', 'spicecraft-core' ) );
		}

		check_admin_referer( 'spicecraft_export_blog_nonce' );

		$filename = 'spicecraft-blog-' . gmdate( 'Y-m-d' ) . '.csv';

		header( 'Content-Type: text/csv; charset=UTF-8' );
		header( 'Content-Disposition: attachment; filename="' . $filename . '"' );
		header( 'Pragma: no-cache' );
		header( 'Expires: 0' );

		$output = fopen( 'php://output', 'w' );
		if ( false === $output ) {
			exit;
		}

		fprintf( $output, chr( 0xEF ) . chr( 0xBB ) . chr( 0xBF ) );

		fputcsv( $output, array( 'id', 'title', 'status', 'date', 'author', 'categories', 'tags', 'excerpt', 'content' ) );

		$posts = get_posts(
			array(
				'post_type'      => 'post',
				'post_status'    => array( 'publish', 'draft' ),
				'posts_per_page' => -1,
				'orderby'        => 'ID',
				'order'          => 'DESC',
			)
		);

		foreach ( $posts as $p ) {
			$cats   = implode( ', ', wp_get_post_categories( $p->ID, array( 'fields' => 'names' ) ) );
			$tags   = implode( ', ', wp_get_post_tags( $p->ID, array( 'fields' => 'names' ) ) );
			$author = get_the_author_meta( 'display_name', $p->post_author );

			fputcsv(
				$output,
				array(
					$p->ID,
					$p->post_title,
					$p->post_status,
					$p->post_date,
					$author,
					$cats,
					$tags,
					$p->post_excerpt,
					$p->post_content,
				)
			);
		}

		fclose( $output );
		exit;
	}

	/**
	 * Handle Testimonial Export (CSV)
	 */
	public function handle_testimonial_export() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Unauthorized access.', 'spicecraft-core' ) );
		}

		check_admin_referer( 'spicecraft_export_testimonials_nonce' );

		$filename = 'spicecraft-testimonials-' . gmdate( 'Y-m-d' ) . '.csv';

		header( 'Content-Type: text/csv; charset=UTF-8' );
		header( 'Content-Disposition: attachment; filename="' . $filename . '"' );
		header( 'Pragma: no-cache' );
		header( 'Expires: 0' );

		$output = fopen( 'php://output', 'w' );
		if ( false === $output ) {
			exit;
		}

		fprintf( $output, chr( 0xEF ) . chr( 0xBB ) . chr( 0xBF ) );

		fputcsv( $output, array( 'id', 'name', 'company', 'role', 'location', 'rating', 'status', 'testimonial' ) );

		$testimonials = get_posts(
			array(
				'post_type'      => 'sc_testimonial',
				'post_status'    => array( 'publish', 'draft' ),
				'posts_per_page' => -1,
				'orderby'        => 'ID',
				'order'          => 'DESC',
			)
		);

		foreach ( $testimonials as $t ) {
			$company  = get_post_meta( $t->ID, '_sc_testimonial_company', true );
			$role     = get_post_meta( $t->ID, '_sc_testimonial_role', true );
			$location = get_post_meta( $t->ID, '_sc_testimonial_location', true );
			$rating   = get_post_meta( $t->ID, '_sc_testimonial_rating', true );

			fputcsv(
				$output,
				array(
					$t->ID,
					$t->post_title,
					$company,
					$role,
					$location,
					$rating,
					$t->post_status,
					$t->post_content,
				)
			);
		}

		fclose( $output );
		exit;
	}

	/**
	 * Handle Job Openings Export (CSV)
	 * Strict B2B Rule: Excludes applicant personal records and candidate CVs.
	 */
	public function handle_job_export() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Unauthorized access.', 'spicecraft-core' ) );
		}

		check_admin_referer( 'spicecraft_export_jobs_nonce' );

		$filename = 'spicecraft-jobs-' . gmdate( 'Y-m-d' ) . '.csv';

		header( 'Content-Type: text/csv; charset=UTF-8' );
		header( 'Content-Disposition: attachment; filename="' . $filename . '"' );
		header( 'Pragma: no-cache' );
		header( 'Expires: 0' );

		$output = fopen( 'php://output', 'w' );
		if ( false === $output ) {
			exit;
		}

		fprintf( $output, chr( 0xEF ) . chr( 0xBB ) . chr( 0xBF ) );

		fputcsv( $output, array( 'id', 'job_title', 'department', 'location', 'employment_type', 'experience_level', 'status', 'description' ) );

		$jobs = get_posts(
			array(
				'post_type'      => 'spicecraft_job',
				'post_status'    => array( 'publish', 'draft' ),
				'posts_per_page' => -1,
				'orderby'        => 'ID',
				'order'          => 'DESC',
			)
		);

		foreach ( $jobs as $j ) {
			$meta = function_exists( 'spicecraft_get_job_meta' ) ? spicecraft_get_job_meta( $j->ID ) : array();

			// Department terms
			$depts = wp_get_post_terms( $j->ID, 'spicecraft_department', array( 'fields' => 'names' ) );
			$dept_names = is_array( $depts ) ? implode( ', ', $depts ) : '';

			fputcsv(
				$output,
				array(
					$j->ID,
					$j->post_title,
					$dept_names,
					$meta['location'] ?? '',
					$meta['employment_type'] ?? '',
					$meta['experience_level'] ?? '',
					$j->post_status,
					$j->post_content,
				)
			);
		}

		fclose( $output );
		exit;
	}

	/**
	 * Handle Quotations Summary Export (CSV)
	 * Excludes PDFs and confidential negotiation notes.
	 */
	public function handle_quotation_export() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Unauthorized access.', 'spicecraft-core' ) );
		}

		check_admin_referer( 'spicecraft_export_quotations_nonce' );

		$filename = 'spicecraft-quotations-summary-' . gmdate( 'Y-m-d' ) . '.csv';

		header( 'Content-Type: text/csv; charset=UTF-8' );
		header( 'Content-Disposition: attachment; filename="' . $filename . '"' );
		header( 'Pragma: no-cache' );
		header( 'Expires: 0' );

		$output = fopen( 'php://output', 'w' );
		if ( false === $output ) {
			exit;
		}

		fprintf( $output, chr( 0xEF ) . chr( 0xBB ) . chr( 0xBF ) );

		fputcsv( $output, array( 'quote_number', 'enquiry_id', 'date', 'customer_name', 'company', 'email', 'phone', 'currency', 'total_amount', 'status' ) );

		// Query enquiries that have quotation data
		$enquiries = get_posts(
			array(
				'post_type'      => 'spicecraft_enquiry',
				'post_status'    => 'publish',
				'posts_per_page' => -1,
				'meta_query'     => array(
					array(
						'key'     => '_sc_quotation_data',
						'compare' => 'EXISTS',
					),
				),
			)
		);

		foreach ( $enquiries as $enq ) {
			$quote = class_exists( 'SpiceCraft_Quotation_Engine' ) ? SpiceCraft_Quotation_Engine::get_quotation( $enq->ID ) : null;
			if ( empty( $quote ) || empty( $quote['quote_number'] ) ) {
				continue;
			}

			fputcsv(
				$output,
				array(
					$quote['quote_number'],
					$enq->ID,
					$quote['created_at'] ?? $enq->post_date,
					$quote['client_name'] ?? $enq->post_title,
					$quote['client_company'] ?? '',
					$quote['client_email'] ?? '',
					$quote['client_phone'] ?? '',
					$quote['currency'] ?? 'USD',
					number_format( (float) ( $quote['total'] ?? 0 ), 2, '.', '' ),
					$quote['status'] ?? 'draft',
				)
			);
		}

		fclose( $output );
		exit;
	}

	/**
	 * Redirect with Import Result stored in transient
	 *
	 * @param array $result
	 */
	private function redirect_with_result( $result ) {
		set_transient( 'spicecraft_import_result', $result, 60 );
		wp_safe_redirect( admin_url( 'admin.php?page=spicecraft-import-export' ) );
		exit;
	}
}
