<?php
/**
 * SpiceCraft Core - Team Member Custom Post Type
 *
 * Registers the 'spicecraft_team_member' custom post type for leadership,
 * master blenders, agronomists, and quality heads.
 * Reusable across About page and future corporate presentations.
 *
 * @package SpiceCraft_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class SpiceCraft_Team_CPT {

	/**
	 * Post type key (restricted to <= 20 chars by WordPress core register_post_type).
	 */
	const POST_TYPE = 'spicecraft_team';

	/**
	 * Singleton Instance.
	 *
	 * @var SpiceCraft_Team_CPT|null
	 */
	private static $instance = null;

	/**
	 * Get Singleton Instance.
	 *
	 * @return SpiceCraft_Team_CPT
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
			'name'               => _x( 'Team Members', 'post type general name', 'spicecraft-core' ),
			'singular_name'      => _x( 'Team Member', 'post type singular name', 'spicecraft-core' ),
			'menu_name'          => _x( 'Leadership & Team', 'admin menu', 'spicecraft-core' ),
			'name_admin_bar'     => _x( 'Team Member', 'add new on admin bar', 'spicecraft-core' ),
			'add_new'            => _x( 'Add New Member', 'team member', 'spicecraft-core' ),
			'add_new_item'       => __( 'Add New Team Member', 'spicecraft-core' ),
			'new_item'           => __( 'New Team Member', 'spicecraft-core' ),
			'edit_item'          => __( 'Edit Team Member', 'spicecraft-core' ),
			'view_item'          => __( 'View Team Member', 'spicecraft-core' ),
			'all_items'          => __( 'All Team Members', 'spicecraft-core' ),
			'search_items'       => __( 'Search Team Members', 'spicecraft-core' ),
			'parent_item_colon'  => __( 'Parent Members:', 'spicecraft-core' ),
			'not_found'          => __( 'No team members found.', 'spicecraft-core' ),
			'not_found_in_trash' => __( 'No team members found in Trash.', 'spicecraft-core' ),
		);

		$args = array(
			'labels'             => $labels,
			'description'        => __( 'SpiceCraft executive leadership, agronomists, and master blenders.', 'spicecraft-core' ),
			'public'             => false,
			'publicly_queryable' => false,
			'show_ui'            => true,
			'show_in_menu'       => 'spicecraft-overview',
			'query_var'          => false,
			'rewrite'            => false,
			'capability_type'    => 'post',
			'has_archive'        => false,
			'hierarchical'       => false,
			'menu_position'      => 35,
			'menu_icon'          => 'dashicons-groups',
			'supports'           => array( 'title', 'editor', 'thumbnail' ),
			'show_in_rest'       => true,
		);

		register_post_type( self::POST_TYPE, $args );
	}

	/**
	 * Register Team Meta Box.
	 */
	public function register_meta_boxes() {
		add_meta_box(
			'spicecraft_team_details',
			__( 'Leadership Details & Profile', 'spicecraft-core' ),
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
		wp_nonce_field( 'spicecraft_save_team_meta', 'spicecraft_team_nonce' );

		$role     = get_post_meta( $post->ID, '_sc_team_role', true );
		$linkedin = get_post_meta( $post->ID, '_sc_team_linkedin', true );
		$order    = get_post_meta( $post->ID, '_sc_team_order', true );
		?>
		<table class="form-table" role="presentation">
			<tr>
				<th scope="row"><label for="sc_team_role"><?php esc_html_e( 'Role / Designation', 'spicecraft-core' ); ?></label></th>
				<td>
					<input type="text" name="_sc_team_role" id="sc_team_role" value="<?php echo esc_attr( $role ); ?>" class="regular-text" placeholder="<?php esc_attr_e( 'e.g. Managing Director, Master Blender, Head of Sourcing', 'spicecraft-core' ); ?>" />
					<p class="description"><?php esc_html_e( 'Primary corporate title or culinary designation.', 'spicecraft-core' ); ?></p>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="sc_team_linkedin"><?php esc_html_e( 'LinkedIn Profile URL', 'spicecraft-core' ); ?></label></th>
				<td>
					<input type="url" name="_sc_team_linkedin" id="sc_team_linkedin" value="<?php echo esc_url( $linkedin ); ?>" class="regular-text" placeholder="https://www.linkedin.com/in/username" />
					<p class="description"><?php esc_html_e( 'Optional. When provided, an accessible professional link will be displayed.', 'spicecraft-core' ); ?></p>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="sc_team_order"><?php esc_html_e( 'Display Order / Priority', 'spicecraft-core' ); ?></label></th>
				<td>
					<input type="number" name="_sc_team_order" id="sc_team_order" value="<?php echo esc_attr( '' !== $order ? $order : 10 ); ?>" class="small-text" min="0" step="1" />
					<p class="description"><?php esc_html_e( 'Lower numbers display first (e.g. 10, 20, 30).', 'spicecraft-core' ); ?></p>
				</td>
			</tr>
		</table>
		<p class="description" style="margin-top: 10px;">
			<?php esc_html_e( 'Note: Use the "Featured Image" panel on the right for the portrait photograph and the main content editor above for the short professional bio.', 'spicecraft-core' ); ?>
		</p>
		<?php
	}

	/**
	 * Save Team Meta.
	 *
	 * @param int     $post_id Post ID.
	 * @param WP_Post $post    Post object.
	 */
	public function save_meta_boxes( $post_id, $post ) {
		if ( ! isset( $_POST['spicecraft_team_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['spicecraft_team_nonce'] ) ), 'spicecraft_save_team_meta' ) ) {
			return;
		}

		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		// Role
		if ( isset( $_POST['_sc_team_role'] ) ) {
			update_post_meta( $post_id, '_sc_team_role', sanitize_text_field( wp_unslash( $_POST['_sc_team_role'] ) ) );
		}

		// LinkedIn
		if ( isset( $_POST['_sc_team_linkedin'] ) ) {
			$linkedin_url = esc_url_raw( wp_unslash( $_POST['_sc_team_linkedin'] ) );
			if ( ! empty( $linkedin_url ) ) {
				update_post_meta( $post_id, '_sc_team_linkedin', $linkedin_url );
			} else {
				delete_post_meta( $post_id, '_sc_team_linkedin' );
			}
		}

		// Order
		if ( isset( $_POST['_sc_team_order'] ) ) {
			update_post_meta( $post_id, '_sc_team_order', absint( wp_unslash( $_POST['_sc_team_order'] ) ) );
		}
	}

	/**
	 * Custom Columns for Admin List.
	 *
	 * @param array $columns Default columns.
	 * @return array Modified columns.
	 */
	public function filter_columns( $columns ) {
		return array(
			'cb'        => $columns['cb'],
			'thumbnail' => __( 'Photo', 'spicecraft-core' ),
			'title'     => __( 'Name', 'spicecraft-core' ),
			'role'      => __( 'Role / Designation', 'spicecraft-core' ),
			'linkedin'  => __( 'LinkedIn', 'spicecraft-core' ),
			'order'     => __( 'Order', 'spicecraft-core' ),
			'date'      => $columns['date'],
		);
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
				$role = get_post_meta( $post_id, '_sc_team_role', true );
				echo ! empty( $role ) ? esc_html( $role ) : '<span style="color:#8c8f94;">—</span>';
				break;
			case 'linkedin':
				$linkedin = get_post_meta( $post_id, '_sc_team_linkedin', true );
				if ( ! empty( $linkedin ) ) {
					echo '<a href="' . esc_url( $linkedin ) . '" target="_blank" rel="noopener noreferrer"><span class="dashicons dashicons-networking"></span></a>';
				} else {
					echo '<span style="color:#8c8f94;">—</span>';
				}
				break;
			case 'order':
				$order = get_post_meta( $post_id, '_sc_team_order', true );
				echo esc_html( '' !== $order ? $order : '10' );
				break;
		}
	}
}
