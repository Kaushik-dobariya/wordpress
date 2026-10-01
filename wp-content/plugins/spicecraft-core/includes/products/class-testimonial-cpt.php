<?php
/**
 * SpiceCraft Core - Testimonial Custom Post Type & CMS
 *
 * Registers the 'spicecraft_testimonial' custom post type for client reviews,
 * culinary chef endorsements, and wholesale partner testimonials.
 * Reusable across homepage, landing pages, and dedicated /testimonials/ archive.
 *
 * @package SpiceCraft_Core
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class SpiceCraft_Testimonial_CPT {

	/**
	 * Post type key (restricted to <= 20 chars by WordPress core register_post_type / sc_posts schema).
	 */
	const POST_TYPE = 'sc_testimonial';

	/**
	 * Singleton Instance.
	 *
	 * @var SpiceCraft_Testimonial_CPT|null
	 */
	private static $instance = null;

	/**
	 * Get Singleton Instance.
	 *
	 * @return SpiceCraft_Testimonial_CPT
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
		add_action( 'init', array( $this, 'register_post_type' ) );
		add_action( 'add_meta_boxes', array( $this, 'register_meta_boxes' ) );
		add_action( 'save_post_' . self::POST_TYPE, array( $this, 'save_meta_boxes' ), 10, 2 );
		add_filter( 'manage_' . self::POST_TYPE . '_posts_columns', array( $this, 'filter_columns' ) );
		add_action( 'manage_' . self::POST_TYPE . '_posts_custom_column', array( $this, 'render_column_content' ), 10, 2 );
		add_filter( 'manage_edit-' . self::POST_TYPE . '_sortable_columns', array( $this, 'sortable_columns' ) );
		add_action( 'pre_get_posts', array( $this, 'handle_custom_sorting' ) );
		add_action( 'restrict_manage_posts', array( $this, 'render_admin_filters' ) );
		add_action( 'admin_head', array( $this, 'admin_styles' ) );
	}

	/**
	 * Register Custom Post Type.
	 */
	public function register_post_type() {
		$labels = array(
			'name'               => _x( 'Testimonials', 'post type general name', 'spicecraft-core' ),
			'singular_name'      => _x( 'Testimonial', 'post type singular name', 'spicecraft-core' ),
			'menu_name'          => _x( 'Testimonials', 'admin menu', 'spicecraft-core' ),
			'name_admin_bar'     => _x( 'Testimonial', 'add new on admin bar', 'spicecraft-core' ),
			'add_new'            => _x( 'Add New', 'testimonial', 'spicecraft-core' ),
			'add_new_item'       => __( 'Add New Testimonial', 'spicecraft-core' ),
			'new_item'           => __( 'New Testimonial', 'spicecraft-core' ),
			'edit_item'          => __( 'Edit Testimonial', 'spicecraft-core' ),
			'view_item'          => __( 'View Testimonial', 'spicecraft-core' ),
			'all_items'          => __( 'All Testimonials', 'spicecraft-core' ),
			'search_items'       => __( 'Search Testimonials', 'spicecraft-core' ),
			'parent_item_colon'  => __( 'Parent Testimonials:', 'spicecraft-core' ),
			'not_found'          => __( 'No testimonials found.', 'spicecraft-core' ),
			'not_found_in_trash' => __( 'No testimonials found in Trash.', 'spicecraft-core' ),
		);

		$args = array(
			'labels'             => $labels,
			'description'        => __( 'Verified client, commercial brand, and culinary endorsements.', 'spicecraft-core' ),
			'public'             => true,
			'publicly_queryable' => true,
			'show_ui'            => true,
			'show_in_menu'       => 'spicecraft-overview', // Nest under SpiceCraft main menu
			'query_var'          => true,
			'rewrite'            => array(
				'slug'       => 'testimonials',
				'with_front' => false,
			),
			'capability_type'    => 'post',
			'has_archive'        => 'testimonials',
			'hierarchical'       => false,
			'menu_position'      => 30,
			'menu_icon'          => 'dashicons-testimonial',
			'supports'           => array( 'title', 'editor', 'thumbnail' ),
			'show_in_rest'       => true,
		);

		register_post_type( self::POST_TYPE, $args );
	}

	/**
	 * Register Testimonial Meta Box.
	 */
	public function register_meta_boxes() {
		add_meta_box(
			'spicecraft_testimonial_details',
			__( 'Testimonial Details & Customer Profile', 'spicecraft-core' ),
			array( $this, 'render_meta_box' ),
			self::POST_TYPE,
			'normal',
			'high'
		);
	}

	/**
	 * Render Meta Box fields.
	 *
	 * @param WP_Post $post Current post object.
	 */
	public function render_meta_box( $post ) {
		wp_nonce_field( 'spicecraft_save_testimonial_meta', 'spicecraft_testimonial_nonce' );

		$role        = get_post_meta( $post->ID, '_sc_testimonial_role', true );
		$company     = get_post_meta( $post->ID, '_sc_testimonial_company', true );
		$location    = get_post_meta( $post->ID, '_sc_testimonial_location', true );
		$rating      = get_post_meta( $post->ID, '_sc_testimonial_rating', true );
		$logo_id     = get_post_meta( $post->ID, '_sc_testimonial_company_logo_id', true );
		$is_featured = (bool) get_post_meta( $post->ID, '_sc_testimonial_featured', true );
		$order       = get_post_meta( $post->ID, '_sc_testimonial_order', true );
		$logo_url    = $logo_id ? wp_get_attachment_image_url( $logo_id, 'medium' ) : '';
		?>
		<div class="sc-testimonial-meta-wrap" style="padding: 10px 0;">
			<table class="form-table" role="presentation">
				<!-- Customer & Organization -->
				<tr>
					<th scope="row"><label for="sc_testimonial_role"><?php esc_html_e( 'Role / Designation', 'spicecraft-core' ); ?></label></th>
					<td>
						<input type="text" name="_sc_testimonial_role" id="sc_testimonial_role" value="<?php echo esc_attr( $role ); ?>" class="regular-text" placeholder="<?php esc_attr_e( 'e.g. Executive Chef, VP of Procurement, Head Food Scientist', 'spicecraft-core' ); ?>" />
						<p class="description"><?php esc_html_e( 'Customer professional title or role within their organization.', 'spicecraft-core' ); ?></p>
					</td>
				</tr>

				<tr>
					<th scope="row"><label for="sc_testimonial_company"><?php esc_html_e( 'Company / Brand Name', 'spicecraft-core' ); ?></label></th>
					<td>
						<input type="text" name="_sc_testimonial_company" id="sc_testimonial_company" value="<?php echo esc_attr( $company ); ?>" class="regular-text" placeholder="<?php esc_attr_e( 'e.g. Grand Heritage Hospitality, Artisanal Seasonings UK', 'spicecraft-core' ); ?>" />
						<p class="description"><?php esc_html_e( 'Name of the business, enterprise, or restaurant group.', 'spicecraft-core' ); ?></p>
					</td>
				</tr>

				<tr>
					<th scope="row"><label for="sc_testimonial_location"><?php esc_html_e( 'Customer / Plant Location', 'spicecraft-core' ); ?></label></th>
					<td>
						<input type="text" name="_sc_testimonial_location" id="sc_testimonial_location" value="<?php echo esc_attr( $location ); ?>" class="regular-text" placeholder="<?php esc_attr_e( 'e.g. London, United Kingdom / Mumbai, India / Dubai, UAE', 'spicecraft-core' ); ?>" />
						<p class="description"><?php esc_html_e( 'Optional geographic city or country for global credibility.', 'spicecraft-core' ); ?></p>
					</td>
				</tr>

				<!-- Rating -->
				<tr>
					<th scope="row"><label for="sc_testimonial_rating"><?php esc_html_e( 'Rating (1-5 Stars)', 'spicecraft-core' ); ?></label></th>
					<td>
						<select name="_sc_testimonial_rating" id="sc_testimonial_rating" style="min-width: 200px;">
							<option value=""><?php esc_html_e( 'No Star Rating Displayed', 'spicecraft-core' ); ?></option>
							<?php for ( $i = 5; $i >= 1; $i-- ) : ?>
								<option value="<?php echo esc_attr( $i ); ?>" <?php selected( (string) $rating, (string) $i ); ?>>
									<?php echo esc_html( sprintf( _n( '%d Star (★★★★★)', '%d Stars', $i, 'spicecraft-core' ), $i ) ); ?>
									<?php echo ' - ' . esc_html( str_repeat( '★', $i ) ); ?>
								</option>
							<?php endfor; ?>
						</select>
						<p class="description"><?php esc_html_e( 'Select the star rating provided by the customer. Leave empty if unrated.', 'spicecraft-core' ); ?></p>
					</td>
				</tr>

				<!-- Company Logo Media Uploader -->
				<tr>
					<th scope="row"><label><?php esc_html_e( 'Company Logo', 'spicecraft-core' ); ?></label></th>
					<td>
						<div class="sc-media-uploader-box" style="display: inline-block;">
							<input type="hidden" name="_sc_testimonial_company_logo_id" value="<?php echo esc_attr( $logo_id ); ?>" class="sc-media-id-input" />
							<div class="sc-media-preview-wrap" style="width: 180px; height: 90px; background: #f0f0f1; border: 1px dashed #c3c4c7; border-radius: 6px; display: flex; align-items: center; justify-content: center; overflow: hidden; margin-bottom: 8px; padding: 4px;">
								<img src="<?php echo esc_url( $logo_url ); ?>" alt="" class="sc-media-preview-img" style="max-width: 100%; max-height: 100%; object-fit: contain; <?php echo empty( $logo_url ) ? 'display:none;' : ''; ?>" />
								<span class="dashicons dashicons-building sc-media-placeholder-icon" style="font-size: 36px; width: 36px; height: 36px; color: #8c8f94; <?php echo ! empty( $logo_url ) ? 'display:none;' : ''; ?>"></span>
							</div>
							<div style="display: flex; gap: 8px;">
								<button type="button" class="button button-secondary sc-media-select-btn">
									<?php esc_html_e( 'Select Company Logo', 'spicecraft-core' ); ?>
								</button>
								<button type="button" class="button sc-media-remove-btn" style="<?php echo empty( $logo_url ) ? 'display:none;' : ''; ?>">
									<?php esc_html_e( 'Remove Logo', 'spicecraft-core' ); ?>
								</button>
							</div>
						</div>
						<p class="description"><?php esc_html_e( 'Optional. Upload a transparent PNG or SVG logo for B2B brand proof.', 'spicecraft-core' ); ?></p>
					</td>
				</tr>

				<!-- Featured & Order -->
				<tr>
					<th scope="row"><?php esc_html_e( 'Featured Testimonial', 'spicecraft-core' ); ?></th>
					<td>
						<label for="sc_testimonial_featured" style="font-weight: 600; display: inline-flex; align-items: center; gap: 8px;">
							<input type="checkbox" name="_sc_testimonial_featured" id="sc_testimonial_featured" value="1" <?php checked( $is_featured, true ); ?> />
							<span><?php esc_html_e( 'Highlight as Featured Endorsement (Prioritized on Homepage & Top Showcase)', 'spicecraft-core' ); ?></span>
						</label>
					</td>
				</tr>

				<tr>
					<th scope="row"><label for="sc_testimonial_order"><?php esc_html_e( 'Display Order / Priority', 'spicecraft-core' ); ?></label></th>
					<td>
						<input type="number" name="_sc_testimonial_order" id="sc_testimonial_order" value="<?php echo esc_attr( '' !== $order ? $order : 10 ); ?>" class="small-text" min="0" step="1" />
						<p class="description"><?php esc_html_e( 'Lower numbers display first (e.g. 1, 2, 3, 10). If left default, published date is used as fallback.', 'spicecraft-core' ); ?></p>
					</td>
				</tr>
			</table>
		</div>
		<?php
	}

	/**
	 * Save Testimonial Meta.
	 *
	 * @param int     $post_id Post ID.
	 * @param WP_Post $post    Post object.
	 */
	public function save_meta_boxes( $post_id, $post ) {
		if ( ! isset( $_POST['spicecraft_testimonial_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['spicecraft_testimonial_nonce'] ) ), 'spicecraft_save_testimonial_meta' ) ) {
			return;
		}

		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		// Role / Designation
		if ( isset( $_POST['_sc_testimonial_role'] ) ) {
			update_post_meta( $post_id, '_sc_testimonial_role', sanitize_text_field( wp_unslash( $_POST['_sc_testimonial_role'] ) ) );
		}

		// Company
		if ( isset( $_POST['_sc_testimonial_company'] ) ) {
			update_post_meta( $post_id, '_sc_testimonial_company', sanitize_text_field( wp_unslash( $_POST['_sc_testimonial_company'] ) ) );
		}

		// Location
		if ( isset( $_POST['_sc_testimonial_location'] ) ) {
			update_post_meta( $post_id, '_sc_testimonial_location', sanitize_text_field( wp_unslash( $_POST['_sc_testimonial_location'] ) ) );
		}

		// Rating
		if ( isset( $_POST['_sc_testimonial_rating'] ) ) {
			$rating_val = sanitize_text_field( wp_unslash( $_POST['_sc_testimonial_rating'] ) );
			$rating_int = absint( $rating_val );
			if ( $rating_int >= 1 && $rating_int <= 5 ) {
				update_post_meta( $post_id, '_sc_testimonial_rating', $rating_int );
			} else {
				delete_post_meta( $post_id, '_sc_testimonial_rating' );
			}
		}

		// Company Logo ID
		if ( isset( $_POST['_sc_testimonial_company_logo_id'] ) ) {
			$logo_id = absint( wp_unslash( $_POST['_sc_testimonial_company_logo_id'] ) );
			if ( $logo_id > 0 ) {
				update_post_meta( $post_id, '_sc_testimonial_company_logo_id', $logo_id );
			} else {
				delete_post_meta( $post_id, '_sc_testimonial_company_logo_id' );
			}
		}

		// Featured Toggle
		$is_featured = ! empty( $_POST['_sc_testimonial_featured'] ) ? 1 : 0;
		update_post_meta( $post_id, '_sc_testimonial_featured', $is_featured );

		// Display Order
		if ( isset( $_POST['_sc_testimonial_order'] ) ) {
			update_post_meta( $post_id, '_sc_testimonial_order', absint( wp_unslash( $_POST['_sc_testimonial_order'] ) ) );
		}
	}

	/**
	 * Custom Columns for Admin List.
	 *
	 * @param array $columns Default columns.
	 * @return array Modified columns.
	 */
	public function filter_columns( $columns ) {
		$new_columns = array(
			'cb'        => $columns['cb'],
			'thumbnail' => __( 'Photo', 'spicecraft-core' ),
			'title'     => __( 'Client / Customer Name', 'spicecraft-core' ),
			'company'   => __( 'Company & Location', 'spicecraft-core' ),
			'role'      => __( 'Role / Designation', 'spicecraft-core' ),
			'rating'    => __( 'Rating', 'spicecraft-core' ),
			'logo'      => __( 'Logo', 'spicecraft-core' ),
			'featured'  => __( 'Featured', 'spicecraft-core' ),
			'order'     => __( 'Order', 'spicecraft-core' ),
			'date'      => $columns['date'],
		);
		return $new_columns;
	}

	/**
	 * Render Custom Column Content.
	 *
	 * @param string $column  Column name.
	 * @param int    $post_id Post ID.
	 */
	public function render_column_content( $column, $post_id ) {
		switch ( $column ) {
			case 'thumbnail':
				if ( has_post_thumbnail( $post_id ) ) {
					echo get_the_post_thumbnail( $post_id, array( 44, 44 ), array( 'style' => 'border-radius: 50%; object-fit: cover;' ) );
				} else {
					echo '<span style="color:#8c8f94; font-size: 20px;" class="dashicons dashicons-admin-users"></span>';
				}
				break;

			case 'role':
				$role = get_post_meta( $post_id, '_sc_testimonial_role', true );
				echo ! empty( $role ) ? esc_html( $role ) : '<span style="color:#8c8f94;">—</span>';
				break;

			case 'company':
				$company  = get_post_meta( $post_id, '_sc_testimonial_company', true );
				$location = get_post_meta( $post_id, '_sc_testimonial_location', true );
				$parts    = array_filter( array( $company, $location ) );
				if ( ! empty( $parts ) ) {
					echo esc_html( implode( ' · ', $parts ) );
				} else {
					echo '<span style="color:#8c8f94;">—</span>';
				}
				break;

			case 'rating':
				$rating = get_post_meta( $post_id, '_sc_testimonial_rating', true );
				if ( ! empty( $rating ) ) {
					echo '<span style="color: #d4a373; font-size: 15px;" title="' . esc_attr( sprintf( __( '%d out of 5 stars', 'spicecraft-core' ), $rating ) ) . '">';
					echo esc_html( str_repeat( '★', absint( $rating ) ) );
					echo '</span> <small style="color: #646970;">(' . absint( $rating ) . '/5)</small>';
				} else {
					echo '<span style="color:#8c8f94;">—</span>';
				}
				break;

			case 'logo':
				$logo_id = get_post_meta( $post_id, '_sc_testimonial_company_logo_id', true );
				if ( $logo_id ) {
					echo wp_get_attachment_image( $logo_id, array( 48, 28 ), false, array( 'style' => 'max-height: 28px; width: auto; object-fit: contain;' ) );
				} else {
					echo '<span style="color:#8c8f94;">—</span>';
				}
				break;

			case 'featured':
				$is_featured = (bool) get_post_meta( $post_id, '_sc_testimonial_featured', true );
				if ( $is_featured ) {
					echo '<span class="dashicons dashicons-star-filled" style="color: #d4a373; font-size: 18px;" title="' . esc_attr__( 'Featured Testimonial', 'spicecraft-core' ) . '"></span> <strong style="color: #1b3d2f; font-size: 11px; text-transform: uppercase;">' . esc_html__( 'Featured', 'spicecraft-core' ) . '</strong>';
				} else {
					echo '<span style="color:#8c8f94;">—</span>';
				}
				break;

			case 'order':
				$order = get_post_meta( $post_id, '_sc_testimonial_order', true );
				echo esc_html( '' !== $order ? $order : '10' );
				break;
		}
	}

	/**
	 * Register sortable columns.
	 *
	 * @param array $columns Existing sortable columns.
	 * @return array
	 */
	public function sortable_columns( $columns ) {
		$columns['order']    = 'order';
		$columns['rating']   = 'rating';
		$columns['featured'] = 'featured';
		return $columns;
	}

	/**
	 * Handle custom sorting in admin list.
	 *
	 * @param WP_Query $query Main query.
	 */
	public function handle_custom_sorting( $query ) {
		if ( ! is_admin() || ! $query->is_main_query() ) {
			return;
		}

		if ( self::POST_TYPE !== $query->get( 'post_type' ) ) {
			return;
		}

		$orderby = $query->get( 'orderby' );

		if ( 'order' === $orderby ) {
			$query->set( 'meta_key', '_sc_testimonial_order' );
			$query->set( 'orderby', 'meta_value_num' );
		} elseif ( 'rating' === $orderby ) {
			$query->set( 'meta_key', '_sc_testimonial_rating' );
			$query->set( 'orderby', 'meta_value_num' );
		} elseif ( 'featured' === $orderby ) {
			$query->set( 'meta_key', '_sc_testimonial_featured' );
			$query->set( 'orderby', 'meta_value_num' );
		}
	}

	/**
	 * Render Rating and Featured dropdown filters in admin list.
	 *
	 * @param string $post_type Current post type.
	 */
	public function render_admin_filters( $post_type ) {
		if ( self::POST_TYPE !== $post_type ) {
			return;
		}

		// Filter by rating
		$selected_rating = isset( $_GET['filter_rating'] ) ? sanitize_text_field( wp_unslash( $_GET['filter_rating'] ) ) : '';
		?>
		<select name="filter_rating">
			<option value=""><?php esc_html_e( 'All Ratings', 'spicecraft-core' ); ?></option>
			<?php for ( $r = 5; $r >= 1; $r-- ) : ?>
				<option value="<?php echo esc_attr( $r ); ?>" <?php selected( $selected_rating, (string) $r ); ?>>
					<?php echo esc_html( sprintf( _n( '%d Star', '%d Stars', $r, 'spicecraft-core' ), $r ) ); ?>
				</option>
			<?php endfor; ?>
		</select>

		<?php
		$selected_featured = isset( $_GET['filter_featured'] ) ? sanitize_text_field( wp_unslash( $_GET['filter_featured'] ) ) : '';
		?>
		<select name="filter_featured">
			<option value=""><?php esc_html_e( 'All Visibility', 'spicecraft-core' ); ?></option>
			<option value="1" <?php selected( $selected_featured, '1' ); ?>><?php esc_html_e( 'Featured Only', 'spicecraft-core' ); ?></option>
			<option value="0" <?php selected( $selected_featured, '0' ); ?>><?php esc_html_e( 'Standard Only', 'spicecraft-core' ); ?></option>
		</select>
		<?php
	}

	/**
	 * Print minor admin table styles.
	 */
	public function admin_styles() {
		$screen = get_current_screen();
		if ( ! $screen || self::POST_TYPE !== $screen->post_type ) {
			return;
		}
		echo '<style>
			.column-thumbnail { width: 60px; text-align: center; }
			.column-rating { width: 140px; }
			.column-logo { width: 70px; text-align: center; }
			.column-featured { width: 100px; text-align: center; }
			.column-order { width: 70px; text-align: center; }
		</style>';
	}
}
