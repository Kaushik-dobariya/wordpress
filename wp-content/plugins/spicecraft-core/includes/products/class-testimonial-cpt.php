<?php
/**
 * SpiceCraft Core - Testimonial Custom Post Type
 *
 * Registers the 'spicecraft_testimonial' custom post type for client reviews,
 * culinary chef endorsements, and wholesale partner testimonials.
 * Reusable across homepage and landing pages.
 *
 * @package SpiceCraft_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class SpiceCraft_Testimonial_CPT {

	/**
	 * Post type key.
	 */
	const POST_TYPE = 'spicecraft_testimonial';

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
			'description'        => __( 'Verified client and culinary endorsements.', 'spicecraft-core' ),
			'public'             => false,
			'publicly_queryable' => false,
			'show_ui'            => true,
			'show_in_menu'       => 'spicecraft-overview', // Nest under SpiceCraft menu
			'query_var'          => false,
			'rewrite'            => false,
			'capability_type'    => 'post',
			'has_archive'        => false,
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
			__( 'Testimonial Details & Author Info', 'spicecraft-core' ),
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

		$role    = get_post_meta( $post->ID, '_sc_testimonial_role', true );
		$company = get_post_meta( $post->ID, '_sc_testimonial_company', true );
		$rating  = get_post_meta( $post->ID, '_sc_testimonial_rating', true );
		$order   = get_post_meta( $post->ID, '_sc_testimonial_order', true );
		?>
		<table class="form-table" role="presentation">
			<tr>
				<th scope="row"><label for="sc_testimonial_role"><?php esc_html_e( 'Role / Designation', 'spicecraft-core' ); ?></label></th>
				<td>
					<input type="text" name="_sc_testimonial_role" id="sc_testimonial_role" value="<?php echo esc_attr( $role ); ?>" class="regular-text" placeholder="<?php esc_attr_e( 'e.g. Executive Chef, Procurement Head', 'spicecraft-core' ); ?>" />
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="sc_testimonial_company"><?php esc_html_e( 'Company / Location', 'spicecraft-core' ); ?></label></th>
				<td>
					<input type="text" name="_sc_testimonial_company" id="sc_testimonial_company" value="<?php echo esc_attr( $company ); ?>" class="regular-text" placeholder="<?php esc_attr_e( 'e.g. Grand Heritage Hotels, Mumbai', 'spicecraft-core' ); ?>" />
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="sc_testimonial_rating"><?php esc_html_e( 'Rating (1-5 Stars)', 'spicecraft-core' ); ?></label></th>
				<td>
					<select name="_sc_testimonial_rating" id="sc_testimonial_rating">
						<option value=""><?php esc_html_e( 'No Star Rating Displayed', 'spicecraft-core' ); ?></option>
						<?php for ( $i = 5; $i >= 1; $i-- ) : ?>
							<option value="<?php echo esc_attr( $i ); ?>" <?php selected( (string) $rating, (string) $i ); ?>>
								<?php echo esc_html( sprintf( _n( '%d Star', '%d Stars', $i, 'spicecraft-core' ), $i ) ); ?>
							</option>
						<?php endfor; ?>
					</select>
					<p class="description"><?php esc_html_e( 'Optional. Leave unselected if no formal star rating was given.', 'spicecraft-core' ); ?></p>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="sc_testimonial_order"><?php esc_html_e( 'Display Order / Priority', 'spicecraft-core' ); ?></label></th>
				<td>
					<input type="number" name="_sc_testimonial_order" id="sc_testimonial_order" value="<?php echo esc_attr( $order !== '' ? $order : 10 ); ?>" class="small-text" min="0" step="1" />
					<p class="description"><?php esc_html_e( 'Lower numbers display first (e.g. 10, 20, 30).', 'spicecraft-core' ); ?></p>
				</td>
			</tr>
		</table>
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

		// Role
		if ( isset( $_POST['_sc_testimonial_role'] ) ) {
			update_post_meta( $post_id, '_sc_testimonial_role', sanitize_text_field( wp_unslash( $_POST['_sc_testimonial_role'] ) ) );
		}

		// Company
		if ( isset( $_POST['_sc_testimonial_company'] ) ) {
			update_post_meta( $post_id, '_sc_testimonial_company', sanitize_text_field( wp_unslash( $_POST['_sc_testimonial_company'] ) ) );
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

		// Order
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
			'title'     => __( 'Client / Endorser Name', 'spicecraft-core' ),
			'role'      => __( 'Role / Designation', 'spicecraft-core' ),
			'company'   => __( 'Company / Location', 'spicecraft-core' ),
			'rating'    => __( 'Rating', 'spicecraft-core' ),
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
					echo get_the_post_thumbnail( $post_id, array( 40, 40 ), array( 'style' => 'border-radius: 50%; object-fit: cover;' ) );
				} else {
					echo '<span style="color:#8c8f94;">—</span>';
				}
				break;
			case 'role':
				$role = get_post_meta( $post_id, '_sc_testimonial_role', true );
				echo ! empty( $role ) ? esc_html( $role ) : '<span style="color:#8c8f94;">—</span>';
				break;
			case 'company':
				$company = get_post_meta( $post_id, '_sc_testimonial_company', true );
				echo ! empty( $company ) ? esc_html( $company ) : '<span style="color:#8c8f94;">—</span>';
				break;
			case 'rating':
				$rating = get_post_meta( $post_id, '_sc_testimonial_rating', true );
				if ( ! empty( $rating ) ) {
					echo esc_html( str_repeat( '★', absint( $rating ) ) );
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
}
