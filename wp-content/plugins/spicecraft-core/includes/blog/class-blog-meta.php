<?php
/**
 * SpiceCraft Core - Article Editorial Meta Boxes & Admin Columns
 *
 * Extends native WordPress Posts with:
 * 1. Featured Article toggle (_sc_post_is_featured)
 * 2. Editorial Subtitle / Deck (_sc_post_subtitle)
 * 3. Reading time calculator & override (_sc_post_reading_time)
 * 4. Curated Related Articles selector (_sc_post_related_ids)
 * 5. Custom columns in post list table (Featured badge, Reading Time)
 *
 * @package SpiceCraft_Core
 * @since 1.3.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class SpiceCraft_Blog_Meta {

	const NONCE_ACTION = 'spicecraft_post_meta_save';
	const NONCE_NAME   = 'spicecraft_post_meta_nonce';

	/**
	 * Singleton Instance.
	 *
	 * @var SpiceCraft_Blog_Meta|null
	 */
	private static $instance = null;

	/**
	 * Get Singleton Instance.
	 *
	 * @return SpiceCraft_Blog_Meta
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
		add_action( 'save_post_post', array( $this, 'save_post_meta' ), 10, 2 );

		// Custom admin columns for Posts
		add_filter( 'manage_post_posts_columns', array( $this, 'add_admin_columns' ) );
		add_action( 'manage_post_posts_custom_column', array( $this, 'render_admin_columns' ), 10, 2 );
		add_filter( 'manage_edit-post_sortable_columns', array( $this, 'register_sortable_columns' ) );
	}

	/**
	 * Register Article Meta Box.
	 */
	public function register_meta_boxes() {
		add_meta_box(
			'spicecraft_article_editorial_data',
			__( 'SpiceCraft Editorial Specifications & Article Controls', 'spicecraft-core' ),
			array( $this, 'render_article_meta_box' ),
			'post',
			'normal',
			'high'
		);
	}

	/**
	 * Render Article Meta Box.
	 *
	 * @param WP_Post $post Current post object.
	 */
	public function render_article_meta_box( $post ) {
		wp_nonce_field( self::NONCE_ACTION, self::NONCE_NAME );

		$is_featured  = '1' === (string) get_post_meta( $post->ID, '_sc_post_is_featured', true );
		$subtitle     = get_post_meta( $post->ID, '_sc_post_subtitle', true );
		$reading_time = get_post_meta( $post->ID, '_sc_post_reading_time', true );
		$related_ids  = get_post_meta( $post->ID, '_sc_post_related_ids', true );
		if ( ! is_array( $related_ids ) ) {
			$related_ids = array();
		}

		$calculated_time = spicecraft_calculate_reading_time( $post->post_content );
		?>
		<div class="spicecraft-article-meta-box" style="padding: 12px 0;">
			<table class="form-table" role="presentation" style="margin: 0;">
				<!-- 1. Featured Article Toggle -->
				<tr>
					<th scope="row" style="width: 220px;">
						<label for="sc_post_is_featured" style="font-weight: 600; color: #1e293b;">
							<?php esc_html_e( 'Featured Article', 'spicecraft-core' ); ?>
						</label>
					</th>
					<td>
						<label for="sc_post_is_featured" style="display: flex; align-items: center; gap: 8px; font-weight: 500;">
							<input
								type="checkbox"
								id="sc_post_is_featured"
								name="_sc_post_is_featured"
								value="1"
								<?php checked( $is_featured ); ?>
							/>
							<span style="color: #0f172a;">
								<?php esc_html_e( 'Mark as Featured Story (Highlights in Top Hero Banner on Blog landing page)', 'spicecraft-core' ); ?>
							</span>
						</label>
						<p class="description" style="margin-top: 4px;">
							<?php esc_html_e( 'Featured articles receive prominent hero placement and special visual badges across the archive and homepage.', 'spicecraft-core' ); ?>
						</p>
					</td>
				</tr>

				<!-- 2. Editorial Subtitle / Deck -->
				<tr>
					<th scope="row">
						<label for="sc_post_subtitle" style="font-weight: 600; color: #1e293b;">
							<?php esc_html_e( 'Editorial Subtitle / Deck', 'spicecraft-core' ); ?>
						</label>
					</th>
					<td>
						<input
							type="text"
							id="sc_post_subtitle"
							name="_sc_post_subtitle"
							value="<?php echo esc_attr( $subtitle ); ?>"
							class="large-text"
							placeholder="<?php esc_attr_e( 'e.g. How cryogenic milling preserves volatile piperine and essential oils in black pepper processing.', 'spicecraft-core' ); ?>"
						/>
						<p class="description">
							<?php esc_html_e( 'Optional single-sentence synopsis displayed beneath the article title on detail pages and featured cards.', 'spicecraft-core' ); ?>
						</p>
					</td>
				</tr>

				<!-- 3. Reading Time Override -->
				<tr>
					<th scope="row">
						<label for="sc_post_reading_time" style="font-weight: 600; color: #1e293b;">
							<?php esc_html_e( 'Estimated Reading Time', 'spicecraft-core' ); ?>
						</label>
					</th>
					<td>
						<div style="display: flex; align-items: center; gap: 10px;">
							<input
								type="number"
								id="sc_post_reading_time"
								name="_sc_post_reading_time"
								value="<?php echo esc_attr( $reading_time ); ?>"
								class="small-text"
								min="1"
								max="120"
								style="width: 80px;"
								placeholder="<?php echo esc_attr( $calculated_time ); ?>"
							/>
							<span style="color: #475569; font-weight: 500;"><?php esc_html_e( 'minutes', 'spicecraft-core' ); ?></span>
							<span style="display: inline-block; padding: 2px 8px; background: #f1f5f9; border: 1px solid #cbd5e1; border-radius: 4px; font-size: 12px; color: #64748b;">
								<?php
								printf(
									/* translators: %d: calculated minutes */
									esc_html__( 'Auto-calculated: ~%d min read (based on 200 wpm)', 'spicecraft-core' ),
									$calculated_time
								);
								?>
							</span>
						</div>
						<p class="description">
							<?php esc_html_e( 'Leave blank to use the automatically calculated reading time from content word count.', 'spicecraft-core' ); ?>
						</p>
					</td>
				</tr>

				<!-- 4. Curated Related Articles -->
				<tr>
					<th scope="row">
						<label for="sc_post_related_ids" style="font-weight: 600; color: #1e293b;">
							<?php esc_html_e( 'Curated Related Articles', 'spicecraft-core' ); ?>
						</label>
					</th>
					<td>
						<?php
						$all_posts = get_posts( array(
							'post_type'      => 'post',
							'post_status'    => 'publish',
							'numberposts'    => 50,
							'exclude'        => array( $post->ID ),
							'orderby'        => 'title',
							'order'          => 'ASC',
						) );
						?>
						<?php if ( ! empty( $all_posts ) ) : ?>
							<select
								id="sc_post_related_ids"
								name="_sc_post_related_ids[]"
								multiple="multiple"
								style="width: 100%; max-width: 550px; height: 120px; border-radius: 4px; border-color: #cbd5e1;"
							>
								<?php foreach ( $all_posts as $p_item ) : ?>
									<option
										value="<?php echo esc_attr( $p_item->ID ); ?>"
										<?php echo in_array( $p_item->ID, $related_ids, true ) ? 'selected="selected"' : ''; ?>
									>
										<?php echo esc_html( $p_item->post_title ); ?> (ID: <?php echo esc_html( $p_item->ID ); ?>)
									</option>
								<?php endforeach; ?>
							</select>
							<p class="description">
								<?php esc_html_e( 'Hold Ctrl (Windows) or Cmd (Mac) to select up to 3 specific related articles. If unselected, related articles are determined automatically by category and tags.', 'spicecraft-core' ); ?>
							</p>
						<?php else : ?>
							<p style="color: #64748b; font-style: italic; margin: 0;">
								<?php esc_html_e( 'No other published articles are currently available to link.', 'spicecraft-core' ); ?>
							</p>
						<?php endif; ?>
					</td>
				</tr>
			</table>
		</div>
		<?php
	}

	/**
	 * Save Article metadata.
	 *
	 * @param int     $post_id Post ID.
	 * @param WP_Post $post    Post object.
	 */
	public function save_post_meta( $post_id, $post ) {
		// Nonce check
		if ( ! isset( $_POST[ self::NONCE_NAME ] ) || ! wp_verify_nonce( $_POST[ self::NONCE_NAME ], self::NONCE_ACTION ) ) {
			return;
		}

		// Autosave check
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		// Capability check
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		// 1. Featured Article
		$is_featured = ! empty( $_POST['_sc_post_is_featured'] ) ? '1' : '0';
		update_post_meta( $post_id, '_sc_post_is_featured', $is_featured );

		// 2. Subtitle / Deck
		if ( isset( $_POST['_sc_post_subtitle'] ) ) {
			$subtitle = sanitize_text_field( wp_unslash( $_POST['_sc_post_subtitle'] ) );
			update_post_meta( $post_id, '_sc_post_subtitle', $subtitle );
		}

		// 3. Reading Time Override
		if ( isset( $_POST['_sc_post_reading_time'] ) ) {
			$raw_time = sanitize_text_field( wp_unslash( $_POST['_sc_post_reading_time'] ) );
			$clean_time = ! empty( $raw_time ) ? absint( $raw_time ) : '';
			update_post_meta( $post_id, '_sc_post_reading_time', $clean_time );
		}

		// 4. Curated Related Articles
		if ( isset( $_POST['_sc_post_related_ids'] ) && is_array( $_POST['_sc_post_related_ids'] ) ) {
			$clean_related = array_map( 'absint', array_filter( $_POST['_sc_post_related_ids'] ) );
			update_post_meta( $post_id, '_sc_post_related_ids', array_slice( $clean_related, 0, 5 ) );
		} else {
			delete_post_meta( $post_id, '_sc_post_related_ids' );
		}
	}

	/**
	 * Add custom columns to edit.php post list table.
	 *
	 * @param array $columns Existing columns.
	 * @return array Modified columns.
	 */
	public function add_admin_columns( $columns ) {
		$new_cols = array();
		foreach ( $columns as $k => $v ) {
			$new_cols[ $k ] = $v;
			if ( 'title' === $k ) {
				$new_cols['sc_featured']     = __( 'Featured', 'spicecraft-core' );
				$new_cols['sc_reading_time'] = __( 'Read Time', 'spicecraft-core' );
			}
		}
		return $new_cols;
	}

	/**
	 * Render custom column output for Posts.
	 *
	 * @param string $column  Column identifier.
	 * @param int    $post_id Post ID.
	 */
	public function render_admin_columns( $column, $post_id ) {
		if ( 'sc_featured' === $column ) {
			$is_featured = '1' === (string) get_post_meta( $post_id, '_sc_post_is_featured', true );
			if ( $is_featured ) {
				echo '<span class="dashicons dashicons-star-filled" style="color: #eab308; font-size: 20px;" title="' . esc_attr__( 'Featured Article', 'spicecraft-core' ) . '"></span>';
			} else {
				echo '<span class="dashicons dashicons-star-empty" style="color: #cbd5e1; font-size: 20px;" title="' . esc_attr__( 'Standard Article', 'spicecraft-core' ) . '"></span>';
			}
		} elseif ( 'sc_reading_time' === $column ) {
			$mins = spicecraft_get_post_reading_time( $post_id );
			printf( esc_html__( '%d min read', 'spicecraft-core' ), $mins );
		}
	}

	/**
	 * Register sortable columns.
	 *
	 * @param array $columns Sortable columns.
	 * @return array
	 */
	public function register_sortable_columns( $columns ) {
		$columns['sc_featured'] = 'sc_featured';
		return $columns;
	}
}
