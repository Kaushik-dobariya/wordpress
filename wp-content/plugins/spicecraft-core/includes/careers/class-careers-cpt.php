<?php
/**
 * SpiceCraft Core - Careers Custom Post Types & Department Taxonomy
 *
 * Registers:
 * 1. 'spicecraft_job' CPT (Job Openings with clean /careers/ and /careers/{slug}/ permalinks)
 * 2. 'spicecraft_department' Taxonomy (Hierarchical organization of jobs)
 * 3. 'spicecraft_application' CPT (Secure, private storage of candidate applications)
 *
 * @package SpiceCraft_Core
 * @since 1.3.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class SpiceCraft_Careers_CPT {

	/**
	 * CPT Slugs
	 */
	const JOB_CPT         = 'spicecraft_job';
	const APPLICATION_CPT = 'spicecraft_app';
	const DEPT_TAX        = 'spicecraft_department';

	/**
	 * Singleton Instance.
	 *
	 * @var SpiceCraft_Careers_CPT|null
	 */
	private static $instance = null;

	/**
	 * Get Singleton Instance.
	 *
	 * @return SpiceCraft_Careers_CPT
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
		add_action( 'init', array( $this, 'register_content_types' ), 5 );
		add_action( 'admin_menu', array( $this, 'adjust_admin_menus' ), 30 );

		// Job list table columns
		add_filter( 'manage_' . self::JOB_CPT . '_posts_columns', array( $this, 'filter_job_columns' ) );
		add_action( 'manage_' . self::JOB_CPT . '_posts_custom_column', array( $this, 'render_job_column_content' ), 10, 2 );
		add_filter( 'manage_edit-' . self::JOB_CPT . '_sortable_columns', array( $this, 'register_job_sortable_columns' ) );

		// Application list table columns
		add_filter( 'manage_' . self::APPLICATION_CPT . '_posts_columns', array( $this, 'filter_app_columns' ) );
		add_action( 'manage_' . self::APPLICATION_CPT . '_posts_custom_column', array( $this, 'render_app_column_content' ), 10, 2 );
		add_filter( 'manage_edit-' . self::APPLICATION_CPT . '_sortable_columns', array( $this, 'register_app_sortable_columns' ) );

		// Restrict views / filtering in Application list
		add_action( 'restrict_manage_posts', array( $this, 'render_application_list_filters' ) );
		add_filter( 'parse_query', array( $this, 'filter_applications_query' ) );

		// Post row actions
		add_filter( 'post_row_actions', array( $this, 'modify_row_actions' ), 10, 2 );
	}

	/**
	 * Register CPTs and Taxonomies.
	 */
	public function register_content_types() {
		$this->register_department_taxonomy();
		$this->register_job_post_type();
		$this->register_application_post_type();
	}

	/**
	 * Register Department Taxonomy.
	 */
	private function register_department_taxonomy() {
		$labels = array(
			'name'              => _x( 'Departments', 'taxonomy general name', 'spicecraft-core' ),
			'singular_name'     => _x( 'Department', 'taxonomy singular name', 'spicecraft-core' ),
			'search_items'      => __( 'Search Departments', 'spicecraft-core' ),
			'all_items'         => __( 'All Departments', 'spicecraft-core' ),
			'parent_item'       => __( 'Parent Department', 'spicecraft-core' ),
			'parent_item_colon' => __( 'Parent Department:', 'spicecraft-core' ),
			'edit_item'         => __( 'Edit Department', 'spicecraft-core' ),
			'update_item'       => __( 'Update Department', 'spicecraft-core' ),
			'add_new_item'      => __( 'Add New Department', 'spicecraft-core' ),
			'new_item_name'     => __( 'New Department Name', 'spicecraft-core' ),
			'menu_name'         => __( 'Departments', 'spicecraft-core' ),
		);

		register_taxonomy(
			self::DEPT_TAX,
			array( self::JOB_CPT ),
			array(
				'hierarchical'      => true,
				'labels'            => $labels,
				'show_ui'           => true,
				'show_admin_column' => true,
				'query_var'         => true,
				'rewrite'           => array(
					'slug'         => 'job-department',
					'with_front'   => false,
					'hierarchical' => true,
				),
				'show_in_rest'      => true,
			)
		);
	}

	/**
	 * Register Job Custom Post Type.
	 */
	private function register_job_post_type() {
		$labels = array(
			'name'                  => _x( 'Careers & Jobs', 'Post type general name', 'spicecraft-core' ),
			'singular_name'         => _x( 'Job Opening', 'Post type singular name', 'spicecraft-core' ),
			'menu_name'             => _x( 'Careers: Jobs', 'Admin Menu text', 'spicecraft-core' ),
			'name_admin_bar'        => _x( 'Job Opening', 'Add New on Toolbar', 'spicecraft-core' ),
			'add_new'               => __( 'Add Job', 'spicecraft-core' ),
			'add_new_item'          => __( 'Add New Job Opening', 'spicecraft-core' ),
			'new_item'              => __( 'New Job Opening', 'spicecraft-core' ),
			'edit_item'             => __( 'Edit Job Opening', 'spicecraft-core' ),
			'view_item'             => __( 'View Job Opening', 'spicecraft-core' ),
			'all_items'             => __( 'All Job Openings', 'spicecraft-core' ),
			'search_items'          => __( 'Search Jobs', 'spicecraft-core' ),
			'parent_item_colon'     => __( 'Parent Jobs:', 'spicecraft-core' ),
			'not_found'             => __( 'No job openings found.', 'spicecraft-core' ),
			'not_found_in_trash'    => __( 'No job openings found in Trash.', 'spicecraft-core' ),
			'featured_image'        => _x( 'Role Image (Optional)', 'Featured Image', 'spicecraft-core' ),
			'set_featured_image'    => _x( 'Set role image', 'Set featured image', 'spicecraft-core' ),
			'remove_featured_image' => _x( 'Remove role image', 'Remove featured image', 'spicecraft-core' ),
			'use_featured_image'    => _x( 'Use as role image', 'Use as featured image', 'spicecraft-core' ),
			'archives'              => _x( 'Careers Archives', 'The post type archive label', 'spicecraft-core' ),
		);

		$args = array(
			'labels'              => $labels,
			'description'         => __( 'Artisanal spice production, quality, food science, and operational vacancies.', 'spicecraft-core' ),
			'public'              => true,
			'publicly_queryable'  => true,
			'show_ui'             => true,
			'show_in_menu'        => 'spicecraft-overview', // Integrated cleanly under SpiceCraft hierarchy
			'query_var'           => true,
			'rewrite'             => array(
				'slug'       => 'careers',
				'with_front' => false,
			),
			'capability_type'     => 'post',
			'has_archive'         => 'careers',
			'hierarchical'        => false,
			'menu_position'       => 28,
			'menu_icon'           => 'dashicons-businessman',
			'supports'            => array( 'title', 'editor', 'thumbnail', 'excerpt', 'revisions' ),
			'show_in_rest'        => true,
			'exclude_from_search' => false,
		);

		register_post_type( self::JOB_CPT, $args );
	}

	/**
	 * Register Application Custom Post Type.
	 *
	 * CRITICAL SECURITY REQUIREMENT:
	 * Applications contain confidential candidate information (names, emails, resumes).
	 * Must be completely private: public=false, show_in_rest=false, exclude_from_search=true.
	 */
	private function register_application_post_type() {
		$labels = array(
			'name'                  => _x( 'Applications', 'Post type general name', 'spicecraft-core' ),
			'singular_name'         => _x( 'Application', 'Post type singular name', 'spicecraft-core' ),
			'menu_name'             => _x( 'Careers: Applications', 'Admin Menu text', 'spicecraft-core' ),
			'name_admin_bar'        => _x( 'Job Application', 'Add New on Toolbar', 'spicecraft-core' ),
			'add_new'               => __( 'Add Application', 'spicecraft-core' ),
			'add_new_item'          => __( 'Add New Application', 'spicecraft-core' ),
			'new_item'              => __( 'New Application', 'spicecraft-core' ),
			'edit_item'             => __( 'Review Candidate Application', 'spicecraft-core' ),
			'view_item'             => __( 'View Application', 'spicecraft-core' ),
			'all_items'             => __( 'All Applications', 'spicecraft-core' ),
			'search_items'          => __( 'Search Applications', 'spicecraft-core' ),
			'parent_item_colon'     => __( 'Job Opening:', 'spicecraft-core' ),
			'not_found'             => __( 'No job applications received yet.', 'spicecraft-core' ),
			'not_found_in_trash'    => __( 'No applications in Trash.', 'spicecraft-core' ),
		);

		$args = array(
			'labels'              => $labels,
			'description'         => __( 'Confidential candidate resumes and applications.', 'spicecraft-core' ),
			'public'              => false, // Strictly private
			'publicly_queryable'  => false, // Not queryable by public
			'show_ui'             => true,
			'show_in_menu'        => 'spicecraft-overview', // Under SpiceCraft hierarchy
			'query_var'           => false,
			'rewrite'             => false,
			'capability_type'     => 'post',
			'capabilities'        => array(
				'create_posts' => 'do_not_allow', // Admin creates via candidate submissions, not manual editor
			),
			'map_meta_cap'        => true,
			'has_archive'         => false,
			'hierarchical'        => false,
			'menu_position'       => 29,
			'menu_icon'           => 'dashicons-feedback',
			'supports'            => array( 'title' ),
			'show_in_rest'        => false, // Strictly prohibited from REST API
			'exclude_from_search' => true,  // Never in site search
		);

		register_post_type( self::APPLICATION_CPT, $args );
	}

	/**
	 * Adjust admin submenus to present a logical Careers hierarchy.
	 */
	public function adjust_admin_menus() {
		// Also add submenu links under edit.php?post_type=spicecraft_job so admin has
		// full Careers ecosystem readily accessible whether they enter via SpiceCraft or Jobs.
		add_submenu_page(
			'edit.php?post_type=' . self::JOB_CPT,
			__( 'Candidate Applications', 'spicecraft-core' ),
			__( 'Applications', 'spicecraft-core' ),
			'manage_options',
			'edit.php?post_type=' . self::APPLICATION_CPT
		);

		add_submenu_page(
			'edit.php?post_type=' . self::JOB_CPT,
			__( 'Careers & Application Settings', 'spicecraft-core' ),
			__( 'Careers Settings', 'spicecraft-core' ),
			'manage_options',
			'spicecraft-careers-settings'
		);
	}

	/**
	 * Filter columns for the Job admin list table.
	 *
	 * @param array $columns Existing columns.
	 * @return array
	 */
	public function filter_job_columns( $columns ) {
		$new_cols = array(
			'cb'              => $columns['cb'] ?? '<input type="checkbox" />',
			'title'           => __( 'Position Title', 'spicecraft-core' ),
			'sc_job_dept'     => __( 'Department', 'spicecraft-core' ),
			'sc_job_location' => __( 'Location', 'spicecraft-core' ),
			'sc_job_type'     => __( 'Employment', 'spicecraft-core' ),
			'sc_job_exp'      => __( 'Experience', 'spicecraft-core' ),
			'sc_job_status'   => __( 'Status', 'spicecraft-core' ),
			'sc_job_deadline' => __( 'Deadline', 'spicecraft-core' ),
			'sc_job_apps'     => __( 'Applications', 'spicecraft-core' ),
			'date'            => __( 'Posted', 'spicecraft-core' ),
		);
		return $new_cols;
	}

	/**
	 * Render content for custom Job columns.
	 *
	 * @param string $column  Column name.
	 * @param int    $post_id Post ID.
	 */
	public function render_job_column_content( $column, $post_id ) {
		$meta = spicecraft_get_job_meta( $post_id );

		switch ( $column ) {
			case 'sc_job_dept':
				$terms = get_the_term_list( $post_id, self::DEPT_TAX, '', ', ' );
				if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
					echo wp_kses_post( $terms );
				} elseif ( ! empty( $meta['department'] ) ) {
					echo esc_html( $meta['department'] );
				} else {
					echo '<span style="color: #8c8f94;">&mdash;</span>';
				}
				break;

			case 'sc_job_location':
				if ( ! empty( $meta['location'] ) ) {
					echo '<span class="dashicons dashicons-location" style="font-size: 15px; width: 16px; height: 16px; vertical-align: text-top; color: #646970;"></span> ' . esc_html( $meta['location'] );
				} else {
					echo '<span style="color: #8c8f94;">&mdash;</span>';
				}
				break;

			case 'sc_job_type':
				echo '<span class="sc-badge-emp" style="display: inline-block; padding: 2px 8px; border-radius: 3px; font-size: 11px; font-weight: 600; background: #eef2f6; color: #1e3a8a;">' . esc_html( $meta['employment_type_label'] ) . '</span>';
				break;

			case 'sc_job_exp':
				echo ! empty( $meta['experience'] ) ? esc_html( $meta['experience'] ) : '<span style="color: #8c8f94;">&mdash;</span>';
				break;

			case 'sc_job_status':
				if ( $meta['is_closed'] ) {
					echo '<span style="display: inline-block; padding: 2px 8px; border-radius: 3px; font-size: 11px; font-weight: 600; background: #fee2e2; color: #991b1b;">' . esc_html__( 'Closed', 'spicecraft-core' ) . '</span>';
				} else {
					echo '<span style="display: inline-block; padding: 2px 8px; border-radius: 3px; font-size: 11px; font-weight: 600; background: #dcfce7; color: #166534;">' . esc_html__( 'Active', 'spicecraft-core' ) . '</span>';
				}
				if ( ! empty( $meta['is_featured'] ) ) {
					echo '<span style="display: inline-block; margin-left: 4px; padding: 2px 6px; border-radius: 3px; font-size: 10px; font-weight: 700; background: #fef3c7; color: #92400e;">' . esc_html__( 'FEATURED', 'spicecraft-core' ) . '</span>';
				}
				break;

			case 'sc_job_deadline':
				if ( ! empty( $meta['deadline'] ) ) {
					$is_past = strtotime( $meta['deadline'] . ' 23:59:59' ) < time();
					$color   = $is_past ? '#b32d2e' : '#2b2625';
					echo '<span style="color: ' . esc_attr( $color ) . '; font-weight: ' . ( $is_past ? '600' : 'normal' ) . ';">' . esc_html( $meta['deadline_formatted'] ) . '</span>';
					if ( $is_past ) {
						echo ' <span style="font-size: 10px; color: #b32d2e;">(' . esc_html__( 'Expired', 'spicecraft-core' ) . ')</span>';
					}
				} else {
					echo '<span style="color: #8c8f94;">' . esc_html__( 'No deadline', 'spicecraft-core' ) . '</span>';
				}
				break;

			case 'sc_job_apps':
				$count     = spicecraft_get_job_applications_count( $post_id );
				$apps_url  = admin_url( 'edit.php?post_type=' . self::APPLICATION_CPT . '&job_filter=' . $post_id );
				if ( $count > 0 ) {
					echo '<a href="' . esc_url( $apps_url ) . '" style="font-weight: 700; color: #1d70b8; text-decoration: underline;">' . sprintf( esc_html__( '%d Candidates', 'spicecraft-core' ), $count ) . '</a>';
				} else {
					echo '<span style="color: #8c8f94;">0</span>';
				}
				break;
		}
	}

	/**
	 * Register sortable columns for Jobs.
	 *
	 * @param array $columns Columns.
	 * @return array
	 */
	public function register_job_sortable_columns( $columns ) {
		$columns['title']           = 'title';
		$columns['sc_job_deadline'] = 'sc_job_deadline';
		$columns['date']            = 'date';
		return $columns;
	}

	/**
	 * Filter columns for the Applications admin list table.
	 *
	 * @param array $columns Columns.
	 * @return array
	 */
	public function filter_app_columns( $columns ) {
		$new_cols = array(
			'cb'              => $columns['cb'] ?? '<input type="checkbox" />',
			'title'           => __( 'Candidate Name', 'spicecraft-core' ),
			'sc_app_job'      => __( 'Position Applied For', 'spicecraft-core' ),
			'sc_app_email'    => __( 'Email Address', 'spicecraft-core' ),
			'sc_app_phone'    => __( 'Phone Number', 'spicecraft-core' ),
			'sc_app_exp'      => __( 'Experience', 'spicecraft-core' ),
			'sc_app_status'   => __( 'Application Status', 'spicecraft-core' ),
			'sc_app_resume'   => __( 'Resume / CV', 'spicecraft-core' ),
			'date'            => __( 'Submitted Date', 'spicecraft-core' ),
		);
		return $new_cols;
	}

	/**
	 * Render content for custom Application columns.
	 *
	 * @param string $column  Column name.
	 * @param int    $post_id Post ID.
	 */
	public function render_app_column_content( $column, $post_id ) {
		switch ( $column ) {
			case 'sc_app_job':
				$job_id    = get_post_meta( $post_id, '_sc_app_job_id', true );
				$job_title = get_post_meta( $post_id, '_sc_app_job_title', true );
				if ( $job_id && ( $job = get_post( $job_id ) ) ) {
					$edit_url = get_edit_post_link( $job_id );
					echo '<a href="' . esc_url( $edit_url ) . '"><strong>' . esc_html( $job->post_title ) . '</strong></a>';
				} elseif ( ! empty( $job_title ) ) {
					echo '<strong>' . esc_html( $job_title ) . '</strong>';
				} else {
					echo '<span style="color: #8c8f94;">' . esc_html__( 'General Profile', 'spicecraft-core' ) . '</span>';
				}
				break;

			case 'sc_app_email':
				$email = get_post_meta( $post_id, '_sc_app_email', true );
				if ( ! empty( $email ) ) {
					echo '<a href="mailto:' . esc_attr( $email ) . '">' . esc_html( $email ) . '</a>';
				} else {
					echo '&mdash;';
				}
				break;

			case 'sc_app_phone':
				$phone = get_post_meta( $post_id, '_sc_app_phone', true );
				if ( ! empty( $phone ) ) {
					echo '<a href="tel:' . esc_attr( $phone ) . '">' . esc_html( $phone ) . '</a>';
				} else {
					echo '&mdash;';
				}
				break;

			case 'sc_app_exp':
				$exp = get_post_meta( $post_id, '_sc_app_experience', true );
				echo ! empty( $exp ) ? esc_html( $exp ) : '&mdash;';
				break;

			case 'sc_app_status':
				$status_key = get_post_meta( $post_id, '_sc_app_status', true );
				$statuses   = spicecraft_get_application_statuses();
				$status_cfg = $statuses[ $status_key ] ?? $statuses['new'];
				echo '<span style="display: inline-block; padding: 3px 10px; border-radius: 4px; font-size: 12px; font-weight: 600; background: ' . esc_attr( $status_cfg['bg'] ) . '; color: ' . esc_attr( $status_cfg['color'] ) . ';">' . esc_html( $status_cfg['label'] ) . '</span>';
				break;

			case 'sc_app_resume':
				$resume_url  = get_post_meta( $post_id, '_sc_app_resume_url', true );
				$resume_name = get_post_meta( $post_id, '_sc_app_resume_name', true );
				if ( ! empty( $resume_url ) ) {
					$download_url = wp_nonce_url(
						admin_url( 'admin-post.php?action=spicecraft_download_resume&app_id=' . $post_id ),
						'spicecraft_download_resume_' . $post_id
					);
					echo '<a href="' . esc_url( $download_url ) . '" class="button button-small" style="display: inline-flex; align-items: center; gap: 4px;">';
					echo '<span class="dashicons dashicons-pdf" style="font-size: 16px; width: 16px; height: 16px;"></span> ';
					echo esc_html__( 'Download Resume', 'spicecraft-core' );
					echo '</a>';
				} else {
					echo '<span style="color: #8c8f94;">' . esc_html__( 'No file', 'spicecraft-core' ) . '</span>';
				}
				break;
		}
	}

	/**
	 * Register sortable columns for Applications.
	 *
	 * @param array $columns Columns.
	 * @return array
	 */
	public function register_app_sortable_columns( $columns ) {
		$columns['title']         = 'title';
		$columns['sc_app_status'] = 'sc_app_status';
		$columns['date']          = 'date';
		return $columns;
	}

	/**
	 * Render application list dropdown filters (by Job and Status).
	 *
	 * @param string $post_type Current post type.
	 */
	public function render_application_list_filters( $post_type ) {
		if ( self::APPLICATION_CPT !== $post_type ) {
			return;
		}

		// Filter 1: By Job
		$selected_job = isset( $_GET['job_filter'] ) ? absint( $_GET['job_filter'] ) : 0;
		$jobs = get_posts( array(
			'post_type'      => self::JOB_CPT,
			'post_status'    => array( 'publish', 'draft' ),
			'posts_per_page' => 100,
			'orderby'        => 'title',
			'order'          => 'ASC',
		) );

		echo '<select name="job_filter" id="job_filter">';
		echo '<option value="">' . esc_html__( 'All Job Openings', 'spicecraft-core' ) . '</option>';
		foreach ( $jobs as $j ) {
			echo '<option value="' . esc_attr( $j->ID ) . '" ' . selected( $selected_job, $j->ID, false ) . '>' . esc_html( $j->post_title ) . '</option>';
		}
		echo '</select>';

		// Filter 2: By Status
		$selected_status = isset( $_GET['status_filter'] ) ? sanitize_key( $_GET['status_filter'] ) : '';
		$statuses        = spicecraft_get_application_statuses();

		echo '<select name="status_filter" id="status_filter">';
		echo '<option value="">' . esc_html__( 'All Application Statuses', 'spicecraft-core' ) . '</option>';
		foreach ( $statuses as $k => $st ) {
			echo '<option value="' . esc_attr( $k ) . '" ' . selected( $selected_status, $k, false ) . '>' . esc_html( $st['label'] ) . '</option>';
		}
		echo '</select>';
	}

	/**
	 * Filter query for Applications list table based on dropdown selections.
	 *
	 * @param WP_Query $query Query instance.
	 */
	public function filter_applications_query( $query ) {
		global $pagenow;
		if ( ! is_admin() || 'edit.php' !== $pagenow || ! $query->is_main_query() ) {
			return;
		}

		if ( isset( $query->query_vars['post_type'] ) && self::APPLICATION_CPT === $query->query_vars['post_type'] ) {
			$meta_query = array();

			// Filter by Job ID
			if ( ! empty( $_GET['job_filter'] ) ) {
				$job_id = absint( $_GET['job_filter'] );
				$meta_query[] = array(
					'key'   => '_sc_app_job_id',
					'value' => $job_id,
				);
			}

			// Filter by Application Status
			if ( ! empty( $_GET['status_filter'] ) ) {
				$status = sanitize_key( $_GET['status_filter'] );
				$meta_query[] = array(
					'key'   => '_sc_app_status',
					'value' => $status,
				);
			}

			if ( ! empty( $meta_query ) ) {
				$meta_query['relation'] = 'AND';
				$query->set( 'meta_query', $meta_query );
			}
		}
	}

	/**
	 * Remove 'view' action from private application row actions.
	 *
	 * @param array   $actions Row actions.
	 * @param WP_Post $post    Post object.
	 * @return array
	 */
	public function modify_row_actions( $actions, $post ) {
		if ( self::APPLICATION_CPT === $post->post_type ) {
			unset( $actions['view'] );
			unset( $actions['inline hide-if-no-js'] ); // Remove Quick Edit for applications
		}
		return $actions;
	}
}
