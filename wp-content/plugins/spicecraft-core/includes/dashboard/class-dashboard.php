<?php
/**
 * SpiceCraft Core - Advanced CMS Dashboard & Business Intelligence
 *
 * Implements:
 * 1. WordPress Admin Dashboard Widget ('wp_dashboard_setup') for commercial activity and catalog status
 * 2. Enhanced SpiceCraft Overview Screen with Content Summary, Business Summary, Recent Activity, and Quick Actions
 * 3. Strict capability gating (manage_options, edit_posts) to prevent PII exposure to unauthorized roles
 *
 * @package SpiceCraft_Core
 * @since 1.5.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class SpiceCraft_Dashboard {

	/**
	 * Singleton Instance.
	 *
	 * @var SpiceCraft_Dashboard|null
	 */
	private static $instance = null;

	/**
	 * Get Singleton Instance.
	 *
	 * @return SpiceCraft_Dashboard
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
		add_action( 'wp_dashboard_setup', array( $this, 'register_dashboard_widgets' ) );
	}

	/**
	 * Register WordPress Admin Dashboard Widget
	 */
	public function register_dashboard_widgets() {
		if ( ! current_user_can( 'edit_posts' ) ) {
			return;
		}

		wp_add_dashboard_widget(
			'spicecraft_commercial_dashboard',
			__( 'SpiceCraft — B2B Commercial & Catalog Overview', 'spicecraft-core' ),
			array( $this, 'render_dashboard_widget' )
		);
	}

	/**
	 * Render WordPress Dashboard Widget on wp-admin/index.php
	 */
	public function render_dashboard_widget() {
		$is_admin = current_user_can( 'manage_options' );

		// Content Counts
		$products_count = wp_count_posts( 'product' );
		$published_prods = isset( $products_count->publish ) ? (int) $products_count->publish : 0;

		// Business Metrics
		$new_enquiries = function_exists( 'spicecraft_count_new_enquiries' ) ? spicecraft_count_new_enquiries() : 0;
		$quote_metrics = class_exists( 'SpiceCraft_Quotation_Engine' ) ? SpiceCraft_Quotation_Engine::get_metrics() : array( 'draft' => 0, 'sent' => 0, 'accepted' => 0, 'follow_ups' => 0 );

		// Recent Enquiries
		$recent_enquiries = array();
		if ( $is_admin ) {
			$recent_enquiries = get_posts( array(
				'post_type'      => 'spicecraft_enquiry',
				'post_status'    => 'publish',
				'posts_per_page' => 4,
				'orderby'        => 'date',
				'order'          => 'DESC',
			) );
		}
		?>
		<div class="spicecraft-dash-widget">
			<style>
				.spicecraft-dash-widget { font-size: 13px; line-height: 1.5; color: #2c3338; }
				.sc-dash-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 10px; margin-bottom: 16px; }
				.sc-dash-stat-box { background: #faf7f2; border: 1px solid #e6ded1; border-radius: 6px; padding: 12px; }
				.sc-dash-stat-box strong { display: block; font-size: 20px; color: #6e1a24; font-weight: 700; line-height: 1.2; }
				.sc-dash-stat-box span { font-size: 11px; text-transform: uppercase; letter-spacing: 0.05em; color: #646970; font-weight: 600; }
				.sc-dash-stat-box.highlight { border-left: 3px solid #b83d27; background: #fff5f5; }
				.sc-dash-stat-box.highlight strong { color: #b83d27; }
				.sc-dash-activity-title { font-weight: 600; font-size: 13px; margin: 16px 0 8px 0; border-bottom: 1px solid #f0f0f1; padding-bottom: 4px; display: flex; justify-content: space-between; align-items: center; }
				.sc-dash-list { margin: 0; padding: 0; list-style: none; }
				.sc-dash-list li { padding: 8px 0; border-bottom: 1px dotted #e2e4e7; display: flex; justify-content: space-between; align-items: center; }
				.sc-dash-list li:last-child { border-bottom: none; }
				.sc-dash-actions { display: flex; gap: 8px; flex-wrap: wrap; margin-top: 16px; padding-top: 12px; border-top: 1px solid #f0f0f1; }
			</style>

			<div class="sc-dash-grid">
				<?php if ( $is_admin ) : ?>
					<div class="sc-dash-stat-box <?php echo $new_enquiries > 0 ? 'highlight' : ''; ?>">
						<strong><?php echo esc_html( $new_enquiries ); ?></strong>
						<span><?php esc_html_e( 'New Enquiries', 'spicecraft-core' ); ?></span>
					</div>
					<div class="sc-dash-stat-box">
						<strong><?php echo esc_html( $quote_metrics['sent'] + $quote_metrics['accepted'] ); ?></strong>
						<span><?php esc_html_e( 'Active Quotes', 'spicecraft-core' ); ?></span>
					</div>
				<?php endif; ?>
				<div class="sc-dash-stat-box">
					<strong><?php echo esc_html( $published_prods ); ?></strong>
					<span><?php esc_html_e( 'Catalog Products', 'spicecraft-core' ); ?></span>
				</div>
				<div class="sc-dash-stat-box">
					<strong><?php echo esc_html( $quote_metrics['follow_ups'] ); ?></strong>
					<span><?php esc_html_e( 'Open Leads', 'spicecraft-core' ); ?></span>
				</div>
			</div>

			<?php if ( $is_admin && ! empty( $recent_enquiries ) ) : ?>
				<div class="sc-dash-activity-title">
					<span><?php esc_html_e( 'Recent Trade Enquiries', 'spicecraft-core' ); ?></span>
					<a href="<?php echo esc_url( admin_url( 'edit.php?post_type=spicecraft_enquiry' ) ); ?>" style="font-size:11px;"><?php esc_html_e( 'View All &rarr;', 'spicecraft-core' ); ?></a>
				</div>
				<ul class="sc-dash-list">
					<?php foreach ( $recent_enquiries as $enq ) : ?>
						<?php
						$enq_name    = get_post_meta( $enq->ID, '_sc_enquiry_name', true ) ?: $enq->post_title;
						$enq_company = get_post_meta( $enq->ID, '_sc_enquiry_company', true );
						$enq_status  = get_post_meta( $enq->ID, '_sc_enquiry_status', true ) ?: 'new';
						$statuses    = function_exists( 'spicecraft_get_enquiry_statuses' ) ? spicecraft_get_enquiry_statuses() : array();
						$status_lbl  = isset( $statuses[ $enq_status ]['label'] ) ? $statuses[ $enq_status ]['label'] : ucfirst( $enq_status );
						$status_bg   = isset( $statuses[ $enq_status ]['bg'] ) ? $statuses[ $enq_status ]['bg'] : '#e5e7eb';
						$status_col  = isset( $statuses[ $enq_status ]['color'] ) ? $statuses[ $enq_status ]['color'] : '#374151';
						?>
						<li>
							<div>
								<a href="<?php echo esc_url( get_edit_post_link( $enq->ID ) ); ?>"><strong><?php echo esc_html( wp_trim_words( $enq_name, 3 ) ); ?></strong></a>
								<?php if ( $enq_company ) : ?>
									<span style="color:#646970; font-size:11px;">(<?php echo esc_html( wp_trim_words( $enq_company, 3 ) ); ?>)</span>
								<?php endif; ?>
							</div>
							<span style="background:<?php echo esc_attr( $status_bg ); ?>; color:<?php echo esc_attr( $status_col ); ?>; padding: 2px 6px; border-radius: 4px; font-size: 11px; font-weight: 600;">
								<?php echo esc_html( $status_lbl ); ?>
							</span>
						</li>
					<?php endforeach; ?>
				</ul>
			<?php endif; ?>

			<div class="sc-dash-actions">
				<a href="<?php echo esc_url( admin_url( 'post-new.php?post_type=product' ) ); ?>" class="button button-secondary"><?php esc_html_e( '+ Add Product', 'spicecraft-core' ); ?></a>
				<?php if ( $is_admin ) : ?>
					<a href="<?php echo esc_url( admin_url( 'edit.php?post_type=spicecraft_enquiry' ) ); ?>" class="button button-primary"><?php esc_html_e( 'View Enquiries', 'spicecraft-core' ); ?></a>
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=spicecraft-settings' ) ); ?>" class="button button-secondary"><?php esc_html_e( 'Global Settings', 'spicecraft-core' ); ?></a>
				<?php endif; ?>
			</div>
		</div>
		<?php
	}

	/**
	 * Render the Full SpiceCraft Overview & Business Intelligence Dashboard
	 */
	public static function render_overview_screen() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Unauthorized access.', 'spicecraft-core' ), 403 );
		}

		// 1. Content Summary Counts
		$products_count     = wp_count_posts( 'product' );
		$published_products = isset( $products_count->publish ) ? (int) $products_count->publish : 0;
		$categories_count   = wp_count_terms( array( 'taxonomy' => 'product_cat', 'hide_empty' => false ) );
		if ( is_wp_error( $categories_count ) ) { $categories_count = 0; }
		$posts_count        = wp_count_posts( 'post' );
		$published_posts    = isset( $posts_count->publish ) ? (int) $posts_count->publish : 0;
		$test_count         = wp_count_posts( 'sc_testimonial' );
		$published_tests    = isset( $test_count->publish ) ? (int) $test_count->publish : 0;
		$certs_count        = wp_count_terms( array( 'taxonomy' => 'spicecraft_certification', 'hide_empty' => false ) );
		if ( is_wp_error( $certs_count ) ) { $certs_count = 0; }
		$jobs_count         = wp_count_posts( 'spicecraft_job' );
		$published_jobs     = isset( $jobs_count->publish ) ? (int) $jobs_count->publish : 0;
		$pages_count        = wp_count_posts( 'page' );
		$published_pages    = isset( $pages_count->publish ) ? (int) $pages_count->publish : 0;

		// 2. Business Summary Counts
		$new_enquiries      = function_exists( 'spicecraft_count_new_enquiries' ) ? spicecraft_count_new_enquiries() : 0;
		$quote_metrics      = class_exists( 'SpiceCraft_Quotation_Engine' ) ? SpiceCraft_Quotation_Engine::get_metrics() : array( 'draft' => 0, 'sent' => 0, 'accepted' => 0, 'follow_ups' => 0 );

		// 3. Recent Activity Data
		$recent_enquiries = get_posts( array(
			'post_type'      => 'spicecraft_enquiry',
			'post_status'    => 'publish',
			'posts_per_page' => 5,
			'orderby'        => 'date',
			'order'          => 'DESC',
		) );

		$recent_applications = get_posts( array(
			'post_type'      => 'spicecraft_app',
			'post_status'    => 'publish',
			'posts_per_page' => 5,
			'orderby'        => 'date',
			'order'          => 'DESC',
		) );

		$contact_page = get_page_by_path( 'contact' );
		$contact_page_id = $contact_page ? $contact_page->ID : 0;
		?>
		<div class="wrap spicecraft-overview-wrap">
			<h1 class="wp-heading-inline"><?php esc_html_e( 'SpiceCraft CMS & Commercial Dashboard', 'spicecraft-core' ); ?></h1>
			<hr class="wp-header-end" />

			<!-- FMCG Catalog Mode Active Banner -->
			<div class="notice notice-info inline" style="margin-top: 15px; border-left-color: #285238; background: #fff; padding: 12px 18px; border-radius: 4px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
				<p style="margin: 0; font-size: 14px; line-height: 1.5;">
					<strong style="color: #285238; font-size: 15px;"><?php esc_html_e( 'B2B Manufacturer Catalog Mode Active:', 'spicecraft-core' ); ?></strong>
					<?php esc_html_e( 'Online checkout and consumer carts are disabled. SpiceCraft functions as an institutional FMCG manufacturer catalog with direct WhatsApp communication and trade inquiry lead workflows.', 'spicecraft-core' ); ?>
				</p>
			</div>

			<style>
				.sc-kpi-row { display: grid; grid-template-columns: repeat(auto-fit, minmax(170px, 1fr)); gap: 15px; margin-top: 24px; }
				.sc-kpi-card { background: #fff; border: 1px solid #dcdcde; border-radius: 8px; padding: 18px 20px; box-shadow: 0 1px 2px rgba(0,0,0,0.04); position: relative; overflow: hidden; transition: transform 0.2s, box-shadow 0.2s; }
				.sc-kpi-card:hover { transform: translateY(-2px); box-shadow: 0 4px 8px rgba(0,0,0,0.08); }
				.sc-kpi-card strong { display: block; font-size: 32px; font-weight: 700; line-height: 1.1; margin-bottom: 4px; }
				.sc-kpi-card span { font-size: 12px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; color: #646970; }
				.sc-kpi-card a { text-decoration: none; color: inherit; }
				.sc-kpi-card.kpi-new { border-left: 4px solid #b83d27; }
				.sc-kpi-card.kpi-new strong { color: #b83d27; }
				.sc-kpi-card.kpi-open { border-left: 4px solid #d97706; }
				.sc-kpi-card.kpi-open strong { color: #d97706; }
				.sc-kpi-card.kpi-draft { border-left: 4px solid #6366f1; }
				.sc-kpi-card.kpi-draft strong { color: #6366f1; }
				.sc-kpi-card.kpi-sent { border-left: 4px solid #0284c7; }
				.sc-kpi-card.kpi-sent strong { color: #0284c7; }
				.sc-kpi-card.kpi-accepted { border-left: 4px solid #16a34a; }
				.sc-kpi-card.kpi-accepted strong { color: #16a34a; }
				.sc-kpi-card.kpi-content { border-left: 4px solid #285238; }
				.sc-kpi-card.kpi-content strong { color: #285238; }

				.sc-quick-actions-bar { background: #faf7f2; border: 1px solid #e6ded1; border-radius: 8px; padding: 16px 20px; margin: 25px 0; display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 12px; }
				.sc-quick-actions-bar h3 { margin: 0; font-size: 14px; text-transform: uppercase; letter-spacing: 0.05em; color: #6e1a24; font-weight: 700; }
				.sc-actions-group { display: flex; flex-wrap: wrap; gap: 8px; }

				.sc-dashboard-layout { display: grid; grid-template-columns: 2fr 1fr; gap: 24px; margin-top: 25px; }
				@media (max-width: 1024px) {
					.sc-dashboard-layout { grid-template-columns: 1fr; }
				}
				.sc-dash-panel { background: #fff; border: 1px solid #dcdcde; border-radius: 8px; padding: 20px 24px; margin-bottom: 24px; box-shadow: 0 1px 2px rgba(0,0,0,0.04); }
				.sc-dash-panel h2 { font-size: 16px; font-weight: 600; color: #1d2327; margin: 0 0 16px 0; padding-bottom: 10px; border-bottom: 1px solid #f0f0f1; display: flex; justify-content: space-between; align-items: center; }
				.sc-dash-table { width: 100%; border-collapse: collapse; font-size: 13px; }
				.sc-dash-table th { text-align: left; padding: 8px 12px; background: #faf7f2; border-bottom: 1px solid #e6ded1; color: #6e1a24; font-weight: 600; }
				.sc-dash-table td { padding: 10px 12px; border-bottom: 1px solid #f0f0f1; vertical-align: middle; }
				.sc-dash-table tr:hover td { background: #fafafa; }
				.sc-status-pill { display: inline-block; padding: 2px 8px; border-radius: 4px; font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.03em; }
			</style>

			<!-- 1. BUSINESS KPI METRICS -->
			<h2 style="font-size: 16px; margin: 25px 0 0 0; color: #1d2327; font-weight: 600;">
				<span class="dashicons dashicons-chart-bar" style="color: #6e1a24;"></span>
				<?php esc_html_e( 'Commercial & Trade Metrics (Phase 4 & 5)', 'spicecraft-core' ); ?>
			</h2>
			<div class="sc-kpi-row">
				<div class="sc-kpi-card kpi-new">
					<a href="<?php echo esc_url( admin_url( 'edit.php?post_type=spicecraft_enquiry&enquiry_status=new' ) ); ?>">
						<strong><?php echo esc_html( $new_enquiries ); ?></strong>
						<span><?php esc_html_e( 'New Enquiries', 'spicecraft-core' ); ?></span>
					</a>
				</div>
				<div class="sc-kpi-card kpi-open">
					<a href="<?php echo esc_url( admin_url( 'edit.php?post_type=spicecraft_enquiry' ) ); ?>">
						<strong><?php echo esc_html( $quote_metrics['follow_ups'] ); ?></strong>
						<span><?php esc_html_e( 'Open Leads', 'spicecraft-core' ); ?></span>
					</a>
				</div>
				<div class="sc-kpi-card kpi-draft">
					<a href="<?php echo esc_url( admin_url( 'edit.php?post_type=spicecraft_enquiry' ) ); ?>">
						<strong><?php echo esc_html( $quote_metrics['draft'] ); ?></strong>
						<span><?php esc_html_e( 'Draft Quotations', 'spicecraft-core' ); ?></span>
					</a>
				</div>
				<div class="sc-kpi-card kpi-sent">
					<a href="<?php echo esc_url( admin_url( 'edit.php?post_type=spicecraft_enquiry&enquiry_status=quotation_sent' ) ); ?>">
						<strong><?php echo esc_html( $quote_metrics['sent'] ); ?></strong>
						<span><?php esc_html_e( 'Sent Quotations', 'spicecraft-core' ); ?></span>
					</a>
				</div>
				<div class="sc-kpi-card kpi-accepted">
					<a href="<?php echo esc_url( admin_url( 'edit.php?post_type=spicecraft_enquiry&enquiry_status=converted' ) ); ?>">
						<strong><?php echo esc_html( $quote_metrics['accepted'] ); ?></strong>
						<span><?php esc_html_e( 'Accepted / Won', 'spicecraft-core' ); ?></span>
					</a>
				</div>
			</div>

			<!-- 2. CONTENT SUMMARY METRICS -->
			<h2 style="font-size: 16px; margin: 30px 0 0 0; color: #1d2327; font-weight: 600;">
				<span class="dashicons dashicons-category" style="color: #285238;"></span>
				<?php esc_html_e( 'Content Architecture Summary', 'spicecraft-core' ); ?>
			</h2>
			<div class="sc-kpi-row">
				<div class="sc-kpi-card kpi-content">
					<a href="<?php echo esc_url( admin_url( 'edit.php?post_type=product' ) ); ?>">
						<strong><?php echo esc_html( $published_products ); ?></strong>
						<span><?php esc_html_e( 'Products', 'spicecraft-core' ); ?></span>
					</a>
				</div>
				<div class="sc-kpi-card kpi-content">
					<a href="<?php echo esc_url( admin_url( 'edit-tags.php?taxonomy=product_cat&post_type=product' ) ); ?>">
						<strong><?php echo esc_html( $categories_count ); ?></strong>
						<span><?php esc_html_e( 'Categories', 'spicecraft-core' ); ?></span>
					</a>
				</div>
				<div class="sc-kpi-card kpi-content">
					<a href="<?php echo esc_url( admin_url( 'edit.php' ) ); ?>">
						<strong><?php echo esc_html( $published_posts ); ?></strong>
						<span><?php esc_html_e( 'Articles & Blog', 'spicecraft-core' ); ?></span>
					</a>
				</div>
				<div class="sc-kpi-card kpi-content">
					<a href="<?php echo esc_url( admin_url( 'edit.php?post_type=sc_testimonial' ) ); ?>">
						<strong><?php echo esc_html( $published_tests ); ?></strong>
						<span><?php esc_html_e( 'Testimonials', 'spicecraft-core' ); ?></span>
					</a>
				</div>
				<div class="sc-kpi-card kpi-content">
					<a href="<?php echo esc_url( admin_url( 'edit-tags.php?taxonomy=spicecraft_certification&post_type=product' ) ); ?>">
						<strong><?php echo esc_html( $certs_count ); ?></strong>
						<span><?php esc_html_e( 'Certifications', 'spicecraft-core' ); ?></span>
					</a>
				</div>
				<div class="sc-kpi-card kpi-content">
					<a href="<?php echo esc_url( admin_url( 'edit.php?post_type=spicecraft_job' ) ); ?>">
						<strong><?php echo esc_html( $published_jobs ); ?></strong>
						<span><?php esc_html_e( 'Active Jobs', 'spicecraft-core' ); ?></span>
					</a>
				</div>
			</div>

			<!-- 3. QUICK ACTIONS SHORTCUT TOOLBAR -->
			<div class="sc-quick-actions-bar">
				<h3><span class="dashicons dashicons-admin-tools" style="vertical-align:text-bottom;"></span> <?php esc_html_e( 'Administrative Shortcuts', 'spicecraft-core' ); ?></h3>
				<div class="sc-actions-group">
					<?php if ( current_user_can( 'edit_posts' ) ) : ?>
						<a href="<?php echo esc_url( admin_url( 'post-new.php?post_type=product' ) ); ?>" class="button button-primary"><?php esc_html_e( '+ Add Product', 'spicecraft-core' ); ?></a>
						<a href="<?php echo esc_url( admin_url( 'post-new.php' ) ); ?>" class="button button-secondary"><?php esc_html_e( '+ Add Blog Post', 'spicecraft-core' ); ?></a>
					<?php endif; ?>

					<?php if ( current_user_can( 'publish_posts' ) ) : ?>
						<a href="<?php echo esc_url( admin_url( 'post-new.php?post_type=sc_testimonial' ) ); ?>" class="button button-secondary"><?php esc_html_e( '+ Add Testimonial', 'spicecraft-core' ); ?></a>
						<a href="<?php echo esc_url( admin_url( 'post-new.php?post_type=spicecraft_job' ) ); ?>" class="button button-secondary"><?php esc_html_e( '+ Add Job', 'spicecraft-core' ); ?></a>
					<?php endif; ?>

					<?php if ( current_user_can( 'manage_options' ) ) : ?>
						<a href="<?php echo esc_url( admin_url( 'edit.php?post_type=spicecraft_enquiry' ) ); ?>" class="button button-secondary"><?php esc_html_e( 'View Enquiries', 'spicecraft-core' ); ?></a>
						<a href="<?php echo esc_url( admin_url( 'admin.php?page=spicecraft-homepage-settings' ) ); ?>" class="button button-secondary"><?php esc_html_e( 'Edit Homepage', 'spicecraft-core' ); ?></a>
						<?php if ( $contact_page_id ) : ?>
							<a href="<?php echo esc_url( admin_url( 'post.php?post=' . $contact_page_id . '&action=edit' ) ); ?>" class="button button-secondary"><?php esc_html_e( 'Edit Contact Page', 'spicecraft-core' ); ?></a>
						<?php endif; ?>
						<a href="<?php echo esc_url( admin_url( 'admin.php?page=spicecraft-settings' ) ); ?>" class="button button-secondary"><?php esc_html_e( 'Global Settings', 'spicecraft-core' ); ?></a>
					<?php endif; ?>
				</div>
			</div>

			<!-- 4. RECENT ACTIVITY FEED & MANAGEMENT PANELS -->
			<div class="sc-dashboard-layout">
				<div class="sc-dashboard-main-col">
					<!-- Panel 1: Recent Trade Enquiries -->
					<div class="sc-dash-panel">
						<h2>
							<span><?php esc_html_e( 'Recent Trade Enquiries & Procurement Leads', 'spicecraft-core' ); ?></span>
							<a href="<?php echo esc_url( admin_url( 'edit.php?post_type=spicecraft_enquiry' ) ); ?>" class="button button-small"><?php esc_html_e( 'All Enquiries', 'spicecraft-core' ); ?> &rarr;</a>
						</h2>

						<?php if ( ! empty( $recent_enquiries ) ) : ?>
							<table class="sc-dash-table">
								<thead>
									<tr>
										<th><?php esc_html_e( 'Client / Company', 'spicecraft-core' ); ?></th>
										<th><?php esc_html_e( 'Type', 'spicecraft-core' ); ?></th>
										<th><?php esc_html_e( 'Date', 'spicecraft-core' ); ?></th>
										<th><?php esc_html_e( 'Status', 'spicecraft-core' ); ?></th>
										<th><?php esc_html_e( 'Action', 'spicecraft-core' ); ?></th>
									</tr>
								</thead>
								<tbody>
									<?php foreach ( $recent_enquiries as $enq ) : ?>
										<?php
										$client_name    = get_post_meta( $enq->ID, '_sc_enquiry_name', true ) ?: $enq->post_title;
										$client_company = get_post_meta( $enq->ID, '_sc_enquiry_company', true );
										$enq_type       = get_post_meta( $enq->ID, '_sc_enquiry_type', true );
										$status         = get_post_meta( $enq->ID, '_sc_enquiry_status', true ) ?: 'new';
										$statuses       = function_exists( 'spicecraft_get_enquiry_statuses' ) ? spicecraft_get_enquiry_statuses() : array();
										$types          = function_exists( 'spicecraft_get_enquiry_types' ) ? spicecraft_get_enquiry_types() : array();
										$status_label   = isset( $statuses[ $status ]['label'] ) ? $statuses[ $status ]['label'] : ucfirst( $status );
										$status_bg      = isset( $statuses[ $status ]['bg'] ) ? $statuses[ $status ]['bg'] : '#e5e7eb';
										$status_color   = isset( $statuses[ $status ]['color'] ) ? $statuses[ $status ]['color'] : '#374151';
										$type_label     = isset( $types[ $enq_type ] ) ? $types[ $enq_type ] : ucfirst( $enq_type );
										?>
										<tr>
											<td>
												<a href="<?php echo esc_url( get_edit_post_link( $enq->ID ) ); ?>"><strong><?php echo esc_html( $client_name ); ?></strong></a>
												<?php if ( $client_company ) : ?>
													<br><span style="color:#646970; font-size:12px;"><?php echo esc_html( $client_company ); ?></span>
												<?php endif; ?>
											</td>
											<td><span style="font-size:12px; color:#4b5563;"><?php echo esc_html( $type_label ); ?></span></td>
											<td><span style="color:#646970; font-size:12px;"><?php echo esc_html( get_the_date( 'M j, Y', $enq ) ); ?></span></td>
											<td>
												<span class="sc-status-pill" style="background:<?php echo esc_attr( $status_bg ); ?>; color:<?php echo esc_attr( $status_color ); ?>;">
													<?php echo esc_html( $status_label ); ?>
												</span>
											</td>
											<td>
												<a href="<?php echo esc_url( get_edit_post_link( $enq->ID ) ); ?>" class="button button-small"><?php esc_html_e( 'Review', 'spicecraft-core' ); ?></a>
											</td>
										</tr>
									<?php endforeach; ?>
								</tbody>
							</table>
						<?php else : ?>
							<p style="color:#646970; margin:15px 0;"><?php esc_html_e( 'No trade enquiries recorded yet.', 'spicecraft-core' ); ?></p>
						<?php endif; ?>
					</div>

					<!-- Panel 2: Recent Candidate Applications -->
					<div class="sc-dash-panel">
						<h2>
							<span><?php esc_html_e( 'Recent Careers & Talent Applications', 'spicecraft-core' ); ?></span>
							<a href="<?php echo esc_url( admin_url( 'edit.php?post_type=spicecraft_app' ) ); ?>" class="button button-small"><?php esc_html_e( 'All Applications', 'spicecraft-core' ); ?> &rarr;</a>
						</h2>

						<?php if ( ! empty( $recent_applications ) ) : ?>
							<table class="sc-dash-table">
								<thead>
									<tr>
										<th><?php esc_html_e( 'Candidate Name', 'spicecraft-core' ); ?></th>
										<th><?php esc_html_e( 'Role Applied', 'spicecraft-core' ); ?></th>
										<th><?php esc_html_e( 'Location', 'spicecraft-core' ); ?></th>
										<th><?php esc_html_e( 'Date', 'spicecraft-core' ); ?></th>
										<th><?php esc_html_e( 'Action', 'spicecraft-core' ); ?></th>
									</tr>
								</thead>
								<tbody>
									<?php foreach ( $recent_applications as $app ) : ?>
										<?php
										$cand_name = get_post_meta( $app->ID, '_sc_app_full_name', true ) ?: $app->post_title;
										$job_title = get_post_meta( $app->ID, '_sc_app_job_title', true );
										$loc       = get_post_meta( $app->ID, '_sc_app_location', true );
										?>
										<tr>
											<td><a href="<?php echo esc_url( get_edit_post_link( $app->ID ) ); ?>"><strong><?php echo esc_html( $cand_name ); ?></strong></a></td>
											<td><?php echo esc_html( $job_title ?: __( 'General Position', 'spicecraft-core' ) ); ?></td>
											<td><span style="color:#646970; font-size:12px;"><?php echo esc_html( $loc ); ?></span></td>
											<td><span style="color:#646970; font-size:12px;"><?php echo esc_html( get_the_date( 'M j, Y', $app ) ); ?></span></td>
											<td><a href="<?php echo esc_url( get_edit_post_link( $app->ID ) ); ?>" class="button button-small"><?php esc_html_e( 'View', 'spicecraft-core' ); ?></a></td>
										</tr>
									<?php endforeach; ?>
								</tbody>
							</table>
						<?php else : ?>
							<p style="color:#646970; margin:15px 0;"><?php esc_html_e( 'No candidate applications received yet.', 'spicecraft-core' ); ?></p>
						<?php endif; ?>
					</div>
				</div>

				<!-- Sidebar Column: CMS Infrastructure & Global Shortcuts -->
				<div class="sc-dashboard-sidebar-col">
					<div class="sc-dash-panel">
						<h2><?php esc_html_e( 'CMS Core Modules', 'spicecraft-core' ); ?></h2>
						<ul style="margin: 0; padding: 0; list-style: none; line-height: 2;">
							<li><a href="<?php echo esc_url( admin_url( 'admin.php?page=spicecraft-settings' ) ); ?>"><span class="dashicons dashicons-admin-settings" style="vertical-align:text-bottom;"></span> <?php esc_html_e( 'Global Settings', 'spicecraft-core' ); ?></a></li>
							<li><a href="<?php echo esc_url( admin_url( 'admin.php?page=spicecraft-homepage-settings' ) ); ?>"><span class="dashicons dashicons-admin-home" style="vertical-align:text-bottom;"></span> <?php esc_html_e( 'Homepage CMS', 'spicecraft-core' ); ?></a></li>
							<li><a href="<?php echo esc_url( admin_url( 'admin.php?page=spicecraft-about-settings' ) ); ?>"><span class="dashicons dashicons-building" style="vertical-align:text-bottom;"></span> <?php esc_html_e( 'About Us CMS', 'spicecraft-core' ); ?></a></li>
							<li><a href="<?php echo esc_url( admin_url( 'admin.php?page=spicecraft-manufacturing-settings' ) ); ?>"><span class="dashicons dashicons-hammer" style="vertical-align:text-bottom;"></span> <?php esc_html_e( 'Manufacturing CMS', 'spicecraft-core' ); ?></a></li>
							<li><a href="<?php echo esc_url( admin_url( 'admin.php?page=spicecraft-quality-settings' ) ); ?>"><span class="dashicons dashicons-shield" style="vertical-align:text-bottom;"></span> <?php esc_html_e( 'Quality & Sourcing CMS', 'spicecraft-core' ); ?></a></li>
							<li><a href="<?php echo esc_url( admin_url( 'admin.php?page=spicecraft-certification-settings' ) ); ?>"><span class="dashicons dashicons-awards" style="vertical-align:text-bottom;"></span> <?php esc_html_e( 'Certifications CMS', 'spicecraft-core' ); ?></a></li>
							<li><a href="<?php echo esc_url( admin_url( 'admin.php?page=spicecraft-careers-settings' ) ); ?>"><span class="dashicons dashicons-id-alt" style="vertical-align:text-bottom;"></span> <?php esc_html_e( 'Careers CMS', 'spicecraft-core' ); ?></a></li>
							<li><a href="<?php echo esc_url( admin_url( 'admin.php?page=spicecraft-blog-settings' ) ); ?>"><span class="dashicons dashicons-welcome-write-blog" style="vertical-align:text-bottom;"></span> <?php esc_html_e( 'Blog CMS', 'spicecraft-core' ); ?></a></li>
							<li><a href="<?php echo esc_url( admin_url( 'nav-menus.php' ) ); ?>"><span class="dashicons dashicons-menu" style="vertical-align:text-bottom;"></span> <?php esc_html_e( 'Navigation Menus', 'spicecraft-core' ); ?></a></li>
							<li><a href="<?php echo esc_url( admin_url( 'upload.php' ) ); ?>"><span class="dashicons dashicons-admin-media" style="vertical-align:text-bottom;"></span> <?php esc_html_e( 'Media Library', 'spicecraft-core' ); ?></a></li>
						</ul>
					</div>

					<div class="sc-dash-panel" style="background:#faf7f2; border:1px solid #e6ded1;">
						<h2 style="color:#6e1a24;"><?php esc_html_e( 'Need Bulk Data Transfer?', 'spicecraft-core' ); ?></h2>
						<p style="font-size:13px; color:#6b6360; line-height:1.5;">
							<?php esc_html_e( 'Use Phase 5.5 Import & Export to bulk migrate catalog products, export qualified commercial leads, or generate quotation reports.', 'spicecraft-core' ); ?>
						</p>
						<a href="<?php echo esc_url( admin_url( 'admin.php?page=spicecraft-import-export' ) ); ?>" class="button button-primary" style="width:100%; text-align:center;">
							<?php esc_html_e( 'Launch Import / Export Hub &rarr;', 'spicecraft-core' ); ?>
						</a>
					</div>
				</div>
			</div>
		</div>
		<?php
	}
}
