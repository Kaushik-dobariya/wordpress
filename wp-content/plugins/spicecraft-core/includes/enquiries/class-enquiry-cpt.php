<?php
/**
 * SpiceCraft Core - Product Enquiry & Lead Management Custom Post Type
 *
 * Registers the private 'spicecraft_enquiry' CPT, configures admin list table,
 * custom search, status/type filters, admin menu counter badges, and CSV export.
 *
 * @package SpiceCraft_Core
 * @since 1.4.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class SpiceCraft_Enquiry_CPT {

	/**
	 * Post type key (restricted to <= 20 chars by WordPress core).
	 */
	const POST_TYPE = 'spicecraft_enquiry';

	/**
	 * Singleton Instance.
	 *
	 * @var SpiceCraft_Enquiry_CPT|null
	 */
	private static $instance = null;

	/**
	 * Get Singleton Instance.
	 *
	 * @return SpiceCraft_Enquiry_CPT
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
		add_action( 'init', array( $this, 'register_post_type' ), 6 );
		add_action( 'admin_menu', array( $this, 'adjust_admin_menu_badge' ), 99 );

		// Admin list table columns
		add_filter( 'manage_' . self::POST_TYPE . '_posts_columns', array( $this, 'filter_columns' ) );
		add_action( 'manage_' . self::POST_TYPE . '_posts_custom_column', array( $this, 'render_column_content' ), 10, 2 );
		add_filter( 'manage_edit-' . self::POST_TYPE . '_sortable_columns', array( $this, 'register_sortable_columns' ) );

		// Filtering & search
		add_action( 'restrict_manage_posts', array( $this, 'render_list_filters' ) );
		add_filter( 'parse_query', array( $this, 'filter_admin_query' ) );
		add_filter( 'posts_search', array( $this, 'extend_admin_search' ), 10, 2 );

		// Row actions
		add_filter( 'post_row_actions', array( $this, 'modify_row_actions' ), 10, 2 );

		// CSV Export Action
		add_action( 'admin_post_spicecraft_export_enquiries_csv', array( $this, 'handle_csv_export' ) );

		// Admin list table empty state & helper notices
		add_action( 'all_admin_notices', array( $this, 'render_table_header_notice' ) );
	}

	/**
	 * Register 'spicecraft_enquiry' CPT.
	 */
	public function register_post_type() {
		$labels = array(
			'name'               => _x( 'Enquiries & Leads', 'post type general name', 'spicecraft-core' ),
			'singular_name'      => _x( 'Enquiry', 'post type singular name', 'spicecraft-core' ),
			'menu_name'          => _x( 'Enquiries / Leads', 'admin menu', 'spicecraft-core' ),
			'name_admin_bar'     => _x( 'Enquiry', 'add new on admin bar', 'spicecraft-core' ),
			'edit_item'          => __( 'View & Manage Enquiry', 'spicecraft-core' ),
			'view_item'          => __( 'View Enquiry', 'spicecraft-core' ),
			'all_items'          => __( 'All Enquiries & Leads', 'spicecraft-core' ),
			'search_items'       => __( 'Search Enquiries', 'spicecraft-core' ),
			'not_found'          => __( 'No enquiries found.', 'spicecraft-core' ),
			'not_found_in_trash' => __( 'No enquiries in Trash.', 'spicecraft-core' ),
		);

		$args = array(
			'labels'              => $labels,
			'description'         => __( 'Confidential customer product enquiries and wholesale leads.', 'spicecraft-core' ),
			'public'              => false, // Strictly private - zero public frontend exposure
			'publicly_queryable'  => false,
			'show_ui'             => true,
			'show_in_menu'        => 'spicecraft-overview', // Under SpiceCraft main menu hierarchy
			'query_var'           => false,
			'rewrite'             => false,
			'capability_type'     => 'post',
			'capabilities'        => array(
				'create_posts' => 'do_not_allow', // Enquiries are created via customer frontend form submissions
			),
			'map_meta_cap'        => true,
			'has_archive'         => false,
			'hierarchical'        => false,
			'menu_position'       => 28,
			'menu_icon'           => 'dashicons-email-alt',
			'supports'            => array( 'title' ),
			'show_in_rest'        => false, // Strictly prohibited from REST API
			'exclude_from_search' => true,  // Never in site search
		);

		register_post_type( self::POST_TYPE, $args );
	}

	/**
	 * Append an unread/new badge counter to the admin menu.
	 */
	public function adjust_admin_menu_badge() {
		global $submenu;

		if ( ! isset( $submenu['spicecraft-overview'] ) ) {
			return;
		}

		$count = function_exists( 'spicecraft_count_new_enquiries' ) ? spicecraft_count_new_enquiries() : 0;
		if ( $count <= 0 ) {
			return;
		}

		foreach ( $submenu['spicecraft-overview'] as &$item ) {
			if ( isset( $item[2] ) && 'edit.php?post_type=' . self::POST_TYPE === $item[2] ) {
				$item[0] .= sprintf(
					' <span class="update-plugins count-%1$d" style="background:#c2593f;border-radius:10px;padding:1px 7px;color:#fff;font-size:11px;font-weight:700;"><span class="plugin-count">%1$d</span></span>',
					$count
				);
				break;
			}
		}
	}

	/**
	 * Customize admin list table columns.
	 *
	 * @param array $columns Existing columns.
	 * @return array
	 */
	public function filter_columns( $columns ) {
		$new_cols = array(
			'cb'                   => '<input type="checkbox" />',
			'sc_enq_id'            => __( 'Lead ID', 'spicecraft-core' ),
			'title'                => __( 'Customer / Lead', 'spicecraft-core' ),
			'sc_enq_company'       => __( 'Company', 'spicecraft-core' ),
			'sc_enq_product'       => __( 'Product', 'spicecraft-core' ),
			'sc_enq_country'       => __( 'Country', 'spicecraft-core' ),
			'sc_enq_customer_type' => __( 'Customer Type', 'spicecraft-core' ),
			'sc_enq_status'        => __( 'Status', 'spicecraft-core' ),
			'sc_enq_followup'      => __( 'Next Follow-up', 'spicecraft-core' ),
			'date'                 => __( 'Submitted', 'spicecraft-core' ),
		);

		return $new_cols;
	}

	/**
	 * Render custom column content.
	 *
	 * @param string $column  Column name.
	 * @param int    $post_id Post ID.
	 */
	public function render_column_content( $column, $post_id ) {
		switch ( $column ) {
			case 'sc_enq_id':
				echo '<strong>#' . absint( $post_id ) . '</strong>';
				break;

			case 'sc_enq_company':
				$company = get_post_meta( $post_id, '_sc_enquiry_company', true );
				echo ! empty( $company ) ? '<strong>' . esc_html( $company ) . '</strong>' : '<span style="color:#94a3b8;">&mdash;</span>';
				break;

			case 'sc_enq_country':
				$country = get_post_meta( $post_id, '_sc_enquiry_country', true );
				$city    = get_post_meta( $post_id, '_sc_enquiry_city', true );
				$loc     = implode( ', ', array_filter( array( $city, $country ) ) );
				echo ! empty( $loc ) ? esc_html( $loc ) : '<span style="color:#94a3b8;">&mdash;</span>';
				break;

			case 'sc_enq_customer_type':
				$c_type     = get_post_meta( $post_id, '_sc_enquiry_customer_type', true );
				$cust_types = function_exists( 'spicecraft_get_customer_types' ) ? spicecraft_get_customer_types() : array();
				$c_label    = isset( $cust_types[ $c_type ] ) ? $cust_types[ $c_type ] : ( ! empty( $c_type ) ? ucfirst( str_replace( '_', ' ', $c_type ) ) : '' );
				if ( ! empty( $c_label ) ) {
					echo '<span style="display:inline-block;padding:2px 8px;border-radius:4px;font-size:11px;font-weight:600;background:#f1f5f9;color:#334155;">' . esc_html( $c_label ) . '</span>';
				} else {
					echo '<span style="color:#94a3b8;">&mdash;</span>';
				}
				break;

			case 'sc_enq_product':
				$product_id   = get_post_meta( $post_id, '_sc_enquiry_product_id', true );
				$product_name = get_post_meta( $post_id, '_sc_enquiry_product_name', true );
				$sku          = get_post_meta( $post_id, '_sc_enquiry_product_sku', true );
				$quantity     = get_post_meta( $post_id, '_sc_enquiry_quantity', true );

				if ( ! empty( $product_name ) ) {
					if ( ! empty( $product_id ) && get_post( $product_id ) ) {
						echo '<a href="' . esc_url( get_edit_post_link( $product_id ) ) . '" style="font-weight:600;">' . esc_html( $product_name ) . '</a>';
					} else {
						echo '<strong>' . esc_html( $product_name ) . '</strong>';
					}

					if ( ! empty( $sku ) ) {
						echo '<br><code style="font-size:11px;">SKU: ' . esc_html( $sku ) . '</code>';
					}
					if ( ! empty( $quantity ) ) {
						echo '<br><span style="color:#0369a1;font-size:12px;font-weight:500;">' . sprintf( esc_html__( 'Qty: %s', 'spicecraft-core' ), esc_html( $quantity ) ) . '</span>';
					}
				} else {
					$type       = get_post_meta( $post_id, '_sc_enquiry_type', true );
					$types      = function_exists( 'spicecraft_get_enquiry_types' ) ? spicecraft_get_enquiry_types() : array();
					$type_label = isset( $types[ $type ] ) ? $types[ $type ] : __( 'General Enquiry', 'spicecraft-core' );
					echo '<span style="color:#64748b;font-size:12px;">' . esc_html( $type_label ) . '</span>';
				}
				break;

			case 'sc_enq_status':
				$status   = get_post_meta( $post_id, '_sc_enquiry_status', true );
				$statuses = function_exists( 'spicecraft_get_enquiry_statuses' ) ? spicecraft_get_enquiry_statuses() : array();
				$conf     = isset( $statuses[ $status ] ) ? $statuses[ $status ] : array(
					'label' => ! empty( $status ) ? ucfirst( str_replace( '_', ' ', $status ) ) : __( 'New', 'spicecraft-core' ),
					'bg'    => '#dbeafe',
					'color' => '#1e40af',
				);

				echo '<span style="display:inline-block;padding:3px 10px;border-radius:12px;font-size:12px;font-weight:700;background:' . esc_attr( $conf['bg'] ) . ';color:' . esc_attr( $conf['color'] ) . ';">' . esc_html( $conf['label'] ) . '</span>';
				break;

			case 'sc_enq_followup':
				$followup = get_post_meta( $post_id, '_sc_enquiry_next_followup', true );
				if ( ! empty( $followup ) ) {
					$today      = gmdate( 'Y-m-d' );
					$status     = get_post_meta( $post_id, '_sc_enquiry_status', true );
					$is_overdue = ( $followup < $today ) && ! in_array( $status, array( 'closed', 'converted' ), true );
					$is_today   = ( $followup === $today );

					if ( $is_overdue ) {
						echo '<span style="color:#dc2626;font-weight:700;font-size:12px;">&#x26A0; ' . esc_html( $followup ) . '</span>';
					} elseif ( $is_today ) {
						echo '<span style="color:#d97706;font-weight:700;font-size:12px;">&#x23F0; ' . esc_html__( 'Today', 'spicecraft-core' ) . '</span>';
					} else {
						echo '<span style="color:#334155;font-size:12px;">' . esc_html( $followup ) . '</span>';
					}
				} else {
					echo '<span style="color:#94a3b8;">&mdash;</span>';
				}
				break;
		}
	}

	/**
	 * Register sortable columns.
	 *
	 * @param array $columns Sortable columns.
	 * @return array
	 */
	public function register_sortable_columns( $columns ) {
		$columns['sc_enq_id']       = 'ID';
		$columns['title']           = 'title';
		$columns['sc_enq_status']   = 'sc_enq_status';
		$columns['sc_enq_followup'] = 'sc_enq_followup';
		$columns['date']            = 'date';
		return $columns;
	}

	/**
	 * Render status, customer type, product, and classification filter dropdowns above list table.
	 */
	public function render_list_filters( $post_type ) {
		if ( self::POST_TYPE !== $post_type ) {
			return;
		}

		// Status filter
		$current_status = isset( $_GET['sc_status_filter'] ) ? sanitize_text_field( wp_unslash( $_GET['sc_status_filter'] ) ) : '';
		$statuses       = function_exists( 'spicecraft_get_enquiry_statuses' ) ? spicecraft_get_enquiry_statuses() : array();

		echo '<select name="sc_status_filter" id="filter-by-enq-status">';
		echo '<option value="">' . esc_html__( 'All Statuses', 'spicecraft-core' ) . '</option>';
		foreach ( $statuses as $key => $st ) {
			printf(
				'<option value="%s" %s>%s</option>',
				esc_attr( $key ),
				selected( $current_status, $key, false ),
				esc_html( $st['label'] )
			);
		}
		echo '</select>';

		// Customer Type filter
		$current_cust_type = isset( $_GET['sc_cust_type_filter'] ) ? sanitize_text_field( wp_unslash( $_GET['sc_cust_type_filter'] ) ) : '';
		$cust_types        = function_exists( 'spicecraft_get_customer_types' ) ? spicecraft_get_customer_types() : array();

		echo '<select name="sc_cust_type_filter" id="filter-by-cust-type">';
		echo '<option value="">' . esc_html__( 'All Customer Types', 'spicecraft-core' ) . '</option>';
		foreach ( $cust_types as $c_key => $c_label ) {
			printf(
				'<option value="%s" %s>%s</option>',
				esc_attr( $c_key ),
				selected( $current_cust_type, $c_key, false ),
				esc_html( $c_label )
			);
		}
		echo '</select>';

		// Product filter
		$current_product = isset( $_GET['sc_product_filter'] ) ? absint( $_GET['sc_product_filter'] ) : 0;
		$products        = get_posts(
			array(
				'post_type'      => 'product',
				'post_status'    => 'publish',
				'posts_per_page' => 50,
				'orderby'        => 'title',
				'order'          => 'ASC',
			)
		);

		if ( ! empty( $products ) ) {
			echo '<select name="sc_product_filter" id="filter-by-enq-product">';
			echo '<option value="">' . esc_html__( 'All Products', 'spicecraft-core' ) . '</option>';
			foreach ( $products as $p ) {
				printf(
					'<option value="%d" %s>%s</option>',
					absint( $p->ID ),
					selected( $current_product, $p->ID, false ),
					esc_html( $p->post_title )
				);
			}
			echo '</select>';
		}

		// CSV Export Button
		$export_url = wp_nonce_url( admin_url( 'admin-post.php?action=spicecraft_export_enquiries_csv' ), 'spicecraft_export_csv_nonce' );
		if ( ! empty( $current_status ) ) {
			$export_url = add_query_arg( 'sc_status_filter', $current_status, $export_url );
		}
		if ( ! empty( $current_cust_type ) ) {
			$export_url = add_query_arg( 'sc_cust_type_filter', $current_cust_type, $export_url );
		}
		if ( ! empty( $current_product ) ) {
			$export_url = add_query_arg( 'sc_product_filter', $current_product, $export_url );
		}

		echo ' <a href="' . esc_url( $export_url ) . '" class="button button-secondary" style="margin-left: 6px;">' . esc_html__( 'Export Enquiries CSV', 'spicecraft-core' ) . '</a>';
	}

	/**
	 * Apply custom admin list query filters.
	 *
	 * @param WP_Query $query Query object.
	 */
	public function filter_admin_query( $query ) {
		global $pagenow;

		if ( ! is_admin() || 'edit.php' !== $pagenow || self::POST_TYPE !== $query->get( 'post_type' ) ) {
			return;
		}

		$meta_query = $query->get( 'meta_query' );
		if ( ! is_array( $meta_query ) ) {
			$meta_query = array();
		}

		// Status filter
		if ( ! empty( $_GET['sc_status_filter'] ) ) {
			$meta_query[] = array(
				'key'     => '_sc_enquiry_status',
				'value'   => sanitize_text_field( wp_unslash( $_GET['sc_status_filter'] ) ),
				'compare' => '=',
			);
		}

		// Customer Type filter
		if ( ! empty( $_GET['sc_cust_type_filter'] ) ) {
			$meta_query[] = array(
				'key'     => '_sc_enquiry_customer_type',
				'value'   => sanitize_text_field( wp_unslash( $_GET['sc_cust_type_filter'] ) ),
				'compare' => '=',
			);
		}

		// Product filter
		if ( ! empty( $_GET['sc_product_filter'] ) ) {
			$meta_query[] = array(
				'key'     => '_sc_enquiry_product_id',
				'value'   => absint( $_GET['sc_product_filter'] ),
				'compare' => '=',
			);
		}

		// Enquiry Type filter
		if ( ! empty( $_GET['sc_type_filter'] ) ) {
			$meta_query[] = array(
				'key'     => '_sc_enquiry_type',
				'value'   => sanitize_text_field( wp_unslash( $_GET['sc_type_filter'] ) ),
				'compare' => '=',
			);
		}

		if ( ! empty( $meta_query ) ) {
			$query->set( 'meta_query', $meta_query );
		}

		// Sorting by status or followup
		$orderby = $query->get( 'orderby' );
		if ( 'sc_enq_status' === $orderby ) {
			$query->set( 'meta_key', '_sc_enquiry_status' );
			$query->set( 'orderby', 'meta_value' );
		} elseif ( 'sc_enq_followup' === $orderby ) {
			$query->set( 'meta_key', '_sc_enquiry_next_followup' );
			$query->set( 'orderby', 'meta_value' );
		}
	}

	/**
	 * Extend admin search to search customer name, company, email, phone, product, country, and customer type.
	 *
	 * @param string   $search Search SQL.
	 * @param WP_Query $query  Query instance.
	 * @return string
	 */
	public function extend_admin_search( $search, $query ) {
		global $wpdb, $pagenow;

		if ( ! is_admin() || 'edit.php' !== $pagenow || self::POST_TYPE !== $query->get( 'post_type' ) || empty( $query->get( 's' ) ) ) {
			return $search;
		}

		$term = sanitize_text_field( $query->get( 's' ) );
		$like = '%' . $wpdb->esc_like( $term ) . '%';

		$search = $wpdb->prepare(
			" AND (
				{$wpdb->posts}.post_title LIKE %s
				OR {$wpdb->posts}.ID IN (
					SELECT post_id FROM {$wpdb->postmeta}
					WHERE meta_key IN ('_sc_enquiry_name', '_sc_enquiry_email', '_sc_enquiry_company', '_sc_enquiry_phone', '_sc_enquiry_product_name', '_sc_enquiry_product_sku', '_sc_enquiry_country', '_sc_enquiry_customer_type')
					AND meta_value LIKE %s
				)
			)",
			$like,
			$like
		);

		return $search;
	}

	/**
	 * Clean row actions: Remove 'View' and 'Quick Edit' since this is an internal CRM record.
	 *
	 * @param array   $actions Action links.
	 * @param WP_Post $post    Current post.
	 * @return array
	 */
	public function modify_row_actions( $actions, $post ) {
		if ( self::POST_TYPE === $post->post_type ) {
			unset( $actions['view'] );
			unset( $actions['inline hide-if-no-js'] );
		}
		return $actions;
	}

	/**
	 * Handle CSV Export of Enquiries.
	 *
	 * Enforces capability check, nonce verification, proper UTF-8 BOM,
	 * respects active filters, and cleanly excludes internal admin notes.
	 */
	public function handle_csv_export() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Unauthorized access.', 'spicecraft-core' ), 403 );
		}

		check_admin_referer( 'spicecraft_export_csv_nonce' );

		$args = array(
			'post_type'      => self::POST_TYPE,
			'post_status'    => 'publish',
			'posts_per_page' => -1,
			'orderby'        => 'date',
			'order'          => 'DESC',
		);

		$meta_query = array();
		if ( ! empty( $_GET['sc_status_filter'] ) ) {
			$meta_query[] = array(
				'key'     => '_sc_enquiry_status',
				'value'   => sanitize_text_field( wp_unslash( $_GET['sc_status_filter'] ) ),
				'compare' => '=',
			);
		}
		if ( ! empty( $_GET['sc_cust_type_filter'] ) ) {
			$meta_query[] = array(
				'key'     => '_sc_enquiry_customer_type',
				'value'   => sanitize_text_field( wp_unslash( $_GET['sc_cust_type_filter'] ) ),
				'compare' => '=',
			);
		}
		if ( ! empty( $_GET['sc_product_filter'] ) ) {
			$meta_query[] = array(
				'key'     => '_sc_enquiry_product_id',
				'value'   => absint( $_GET['sc_product_filter'] ),
				'compare' => '=',
			);
		}
		if ( ! empty( $_GET['sc_type_filter'] ) ) {
			$meta_query[] = array(
				'key'     => '_sc_enquiry_type',
				'value'   => sanitize_text_field( wp_unslash( $_GET['sc_type_filter'] ) ),
				'compare' => '=',
			);
		}
		if ( ! empty( $meta_query ) ) {
			$args['meta_query'] = $meta_query;
		}

		$query = new WP_Query( $args );

		$filename = 'spicecraft-enquiries-' . gmdate( 'Y-m-d-His' ) . '.csv';

		// Clean output buffers
		if ( ob_get_level() ) {
			ob_end_clean();
		}

		header( 'Content-Type: text/csv; charset=UTF-8' );
		header( 'Content-Disposition: attachment; filename="' . $filename . '"' );
		header( 'Pragma: no-cache' );
		header( 'Expires: 0' );

		$output = fopen( 'php://output', 'w' );

		// UTF-8 BOM for Microsoft Excel compatibility
		fprintf( $output, chr( 0xEF ) . chr( 0xBB ) . chr( 0xBF ) );

		// CSV Header (internal admin notes strictly excluded)
		fputcsv(
			$output,
			array(
				'Lead ID',
				'Date Received',
				'Status',
				'Customer Type',
				'Enquiry Classification',
				'Full Name',
				'Email',
				'Phone',
				'WhatsApp',
				'Company',
				'Country',
				'City',
				'Target Product',
				'SKU',
				'Pack Sizes',
				'Quantity',
				'Packaging',
				'Preferred Contact',
				'Next Follow-up',
				'Message',
				'Referrer Source',
			)
		);

		if ( $query->have_posts() ) {
			while ( $query->have_posts() ) {
				$query->the_post();
				$pid = get_the_ID();

				fputcsv(
					$output,
					array(
						$pid,
						get_the_date( 'Y-m-d H:i:s', $pid ),
						get_post_meta( $pid, '_sc_enquiry_status', true ),
						get_post_meta( $pid, '_sc_enquiry_customer_type', true ),
						get_post_meta( $pid, '_sc_enquiry_type', true ),
						get_post_meta( $pid, '_sc_enquiry_name', true ),
						get_post_meta( $pid, '_sc_enquiry_email', true ),
						get_post_meta( $pid, '_sc_enquiry_phone', true ),
						get_post_meta( $pid, '_sc_enquiry_whatsapp', true ),
						get_post_meta( $pid, '_sc_enquiry_company', true ),
						get_post_meta( $pid, '_sc_enquiry_country', true ),
						get_post_meta( $pid, '_sc_enquiry_city', true ),
						get_post_meta( $pid, '_sc_enquiry_product_name', true ),
						get_post_meta( $pid, '_sc_enquiry_product_sku', true ),
						get_post_meta( $pid, '_sc_enquiry_pack_size', true ),
						get_post_meta( $pid, '_sc_enquiry_quantity', true ),
						get_post_meta( $pid, '_sc_enquiry_packaging', true ),
						get_post_meta( $pid, '_sc_enquiry_preferred_contact', true ),
						get_post_meta( $pid, '_sc_enquiry_next_followup', true ),
						get_post_meta( $pid, '_sc_enquiry_message', true ),
						get_post_meta( $pid, '_sc_enquiry_source', true ),
					)
				);
			}
			wp_reset_postdata();
		}

		fclose( $output );
		exit;
	}

	/**
	 * Render empty state and workflow guidance on the enquiry list table.
	 */
	public function render_table_header_notice() {
		$screen = get_current_screen();
		if ( ! $screen || 'edit-' . self::POST_TYPE !== $screen->id ) {
			return;
		}

		$counts = wp_count_posts( self::POST_TYPE );
		$total  = isset( $counts->publish ) ? (int) $counts->publish : 0;

		if ( 0 === $total ) {
			?>
			<div class="notice notice-info inline" style="margin: 20px 0; padding: 16px 20px; background: #fff; border-left: 4px solid #6e1a24; border-radius: 4px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
				<h3 style="margin: 0 0 8px 0; color: #6e1a24;"><?php esc_html_e( 'No Commercial Enquiries or Leads Recorded Yet', 'spicecraft-core' ); ?></h3>
				<p style="margin: 0 0 12px 0; font-size: 13px; color: #50575e; line-height: 1.5;">
					<?php esc_html_e( 'Institutional buyer leads and export requests submitted via your single product pages, catalogue modal, or Contact Us page will be securely routed here in real-time.', 'spicecraft-core' ); ?>
				</p>
				<div style="display: flex; gap: 10px;">
					<a href="<?php echo esc_url( home_url( '/contact/' ) ); ?>" target="_blank" class="button button-secondary"><?php esc_html_e( 'Test Contact Page Form', 'spicecraft-core' ); ?> &rarr;</a>
					<a href="<?php echo esc_url( home_url( '/shop/' ) ); ?>" target="_blank" class="button button-secondary"><?php esc_html_e( 'Test Product Catalog Form', 'spicecraft-core' ); ?> &rarr;</a>
				</div>
			</div>
			<?php
		}
	}
}

