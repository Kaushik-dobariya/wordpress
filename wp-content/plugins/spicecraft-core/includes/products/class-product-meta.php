<?php
/**
 * SpiceCraft Core - FMCG Product Data & Meta Box Architecture
 *
 * Provides a clean, organized, tabbed admin interface for managing:
 * - Product Badges & Key Highlights
 * - Pack Sizes Quick Entry
 * - FMCG Parameters (Form, Origin, Shelf Life)
 * - Repeatable Specifications Table
 * - Ingredients & Culinary Usage
 * - Storage Instructions
 * - Repeatable Nutrition Facts Table
 *
 * @package SpiceCraft_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class SpiceCraft_Product_Meta {

	/**
	 * Singleton Instance
	 *
	 * @var SpiceCraft_Product_Meta|null
	 */
	private static $instance = null;

	/**
	 * Nonce Action
	 */
	const NONCE_ACTION = 'spicecraft_save_product_meta';

	/**
	 * Nonce Field Name
	 */
	const NONCE_FIELD = 'spicecraft_product_meta_nonce';

	/**
	 * Get Singleton Instance
	 *
	 * @return SpiceCraft_Product_Meta
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
		add_action( 'add_meta_boxes', array( $this, 'register_meta_box' ) );
		add_action( 'save_post_product', array( $this, 'save_product_meta' ), 10, 2 );
	}

	/**
	 * Register Product Meta Box
	 */
	public function register_meta_box() {
		add_meta_box(
			'spicecraft_product_fmcg_meta',
			__( 'SpiceCraft — FMCG Product Specifications & Catalog Data', 'spicecraft-core' ),
			array( $this, 'render_meta_box' ),
			'product',
			'normal',
			'high'
		);
	}

	/**
	 * Render Product Meta Box
	 *
	 * @param WP_Post $post Current post object.
	 */
	public function render_meta_box( $post ) {
		wp_nonce_field( self::NONCE_ACTION, self::NONCE_FIELD );

		$post_id = $post->ID;

		// Badges & Highlights
		$badge_label = get_post_meta( $post_id, '_sc_badge_label', true );
		if ( empty( $badge_label ) ) {
			$badge_label = get_post_meta( $post_id, '_spicecraft_badge', true );
		}
		$badge_style   = get_post_meta( $post_id, '_sc_badge_style', true );
		$highlights    = get_post_meta( $post_id, '_sc_highlights', true );
		$highlights    = is_array( $highlights ) ? $highlights : array();
		$pack_sizes    = get_post_meta( $post_id, '_sc_pack_sizes', true );

		// FMCG Parameters
		$form          = get_post_meta( $post_id, '_sc_form', true );
		$origin_country= get_post_meta( $post_id, '_sc_country_of_origin', true );
		$origin_region = get_post_meta( $post_id, '_sc_origin_region', true );
		$shelf_life    = get_post_meta( $post_id, '_sc_shelf_life', true );
		$specs         = get_post_meta( $post_id, '_sc_specifications', true );
		$specs         = is_array( $specs ) ? $specs : array();

		// Ingredients, Usage & Storage
		$ingredients   = get_post_meta( $post_id, '_sc_ingredients', true );
		$usage         = get_post_meta( $post_id, '_sc_usage_instructions', true );
		$storage       = get_post_meta( $post_id, '_sc_storage_instructions', true );

		// Nutrition
		$serving_size  = get_post_meta( $post_id, '_sc_serving_size', true );
		$nutrition     = get_post_meta( $post_id, '_sc_nutrition_data', true );
		$nutrition     = is_array( $nutrition ) ? $nutrition : array();
		?>
		<div class="sc-metabox-wrapper">

			<!-- Tabs Navigation -->
			<div class="sc-metabox-tabs">
				<button type="button" class="sc-metabox-tab-btn is-active" data-tab="tab-overview">
					<?php esc_html_e( 'Badges & Highlights', 'spicecraft-core' ); ?>
				</button>
				<button type="button" class="sc-metabox-tab-btn" data-tab="tab-fmcg">
					<?php esc_html_e( 'Origin & Specs', 'spicecraft-core' ); ?>
				</button>
				<button type="button" class="sc-metabox-tab-btn" data-tab="tab-ingredients">
					<?php esc_html_e( 'Ingredients & Storage', 'spicecraft-core' ); ?>
				</button>
				<button type="button" class="sc-metabox-tab-btn" data-tab="tab-nutrition">
					<?php esc_html_e( 'Nutrition Facts Table', 'spicecraft-core' ); ?>
				</button>
			</div>

			<!-- Tab 1: Badges, Highlights & Pack Sizes -->
			<div class="sc-metabox-panel is-active" id="tab-overview">
				<div class="sc-form-row">
					<div class="sc-col">
						<label for="sc_badge_label"><strong><?php esc_html_e( 'Product Card Badge Label', 'spicecraft-core' ); ?></strong></label>
						<input type="text" name="_sc_badge_label" id="sc_badge_label" value="<?php echo esc_attr( $badge_label ); ?>" class="widefat" placeholder="<?php esc_attr_e( 'e.g. 100% PURE, ORGANIC, BEST SELLER, EXPORT GRADE', 'spicecraft-core' ); ?>" />
						<p class="description"><?php esc_html_e( 'Leave empty if no badge applies to this batch.', 'spicecraft-core' ); ?></p>
					</div>
					<div class="sc-col">
						<label for="sc_badge_style"><strong><?php esc_html_e( 'Badge Colorway Theme', 'spicecraft-core' ); ?></strong></label>
						<select name="_sc_badge_style" id="sc_badge_style" class="widefat">
							<option value="secondary" <?php selected( $badge_style, 'secondary' ); ?>><?php esc_html_e( 'Forest Botanical Green (100% Pure / Natural)', 'spicecraft-core' ); ?></option>
							<option value="primary" <?php selected( $badge_style, 'primary' ); ?>><?php esc_html_e( 'Terracotta / Burgundy (Export Grade)', 'spicecraft-core' ); ?></option>
							<option value="accent" <?php selected( $badge_style, 'accent' ); ?>><?php esc_html_e( 'Saffron / Gold (Best Seller / Popular)', 'spicecraft-core' ); ?></option>
							<option value="dark" <?php selected( $badge_style, 'dark' ); ?>><?php esc_html_e( 'Peppercorn Charcoal (Special Reserve)', 'spicecraft-core' ); ?></option>
						</select>
					</div>
				</div>

				<div class="sc-form-row">
					<div class="sc-col-full">
						<label for="sc_pack_sizes"><strong><?php esc_html_e( 'Pack Sizes Quick Entry (Comma-separated)', 'spicecraft-core' ); ?></strong></label>
						<input type="text" name="_sc_pack_sizes" id="sc_pack_sizes" value="<?php echo esc_attr( $pack_sizes ); ?>" class="widefat" placeholder="<?php esc_attr_e( 'e.g. 100g, 200g, 500g, 1kg, 25kg Bulk', 'spicecraft-core' ); ?>" />
						<p class="description"><?php esc_html_e( 'Used for catalog pack-size selectors and WhatsApp enquiry messaging. Complements WooCommerce attributes.', 'spicecraft-core' ); ?></p>
					</div>
				</div>

				<div class="sc-form-row">
					<div class="sc-col-full">
						<label><strong><?php esc_html_e( 'Product Key Highlights (Repeatable)', 'spicecraft-core' ); ?></strong></label>
						<p class="description"><?php esc_html_e( 'Key bullet points displayed prominently on the product detail page.', 'spicecraft-core' ); ?></p>

						<div id="sc-highlights-container" class="sc-repeatable-list">
							<?php if ( ! empty( $highlights ) ) : ?>
								<?php foreach ( $highlights as $highlight ) : ?>
									<div class="sc-repeatable-row">
										<span class="dashicons dashicons-menu sc-drag-handle"></span>
										<input type="text" name="_sc_highlights[]" value="<?php echo esc_attr( $highlight ); ?>" class="widefat" />
										<button type="button" class="button sc-remove-row-btn">&times;</button>
									</div>
								<?php endforeach; ?>
							<?php endif; ?>
						</div>
						<p><button type="button" class="button button-secondary" id="sc-add-highlight-btn">+ <?php esc_html_e( 'Add Highlight', 'spicecraft-core' ); ?></button></p>
					</div>
				</div>
			</div>

			<!-- Tab 2: FMCG Parameters & Repeatable Specs Table -->
			<div class="sc-metabox-panel" id="tab-fmcg" style="display:none;">
				<div class="sc-form-row">
					<div class="sc-col">
						<label for="sc_form"><strong><?php esc_html_e( 'Product Physical Form', 'spicecraft-core' ); ?></strong></label>
						<input type="text" name="_sc_form" id="sc_form" value="<?php echo esc_attr( $form ); ?>" class="widefat" placeholder="<?php esc_attr_e( 'e.g. Whole Seeds, Fine Ground Powder, Flakes, Blended Masala', 'spicecraft-core' ); ?>" />
					</div>
					<div class="sc-col">
						<label for="sc_shelf_life"><strong><?php esc_html_e( 'Shelf Life', 'spicecraft-core' ); ?></strong></label>
						<input type="text" name="_sc_shelf_life" id="sc_shelf_life" value="<?php echo esc_attr( $shelf_life ); ?>" class="widefat" placeholder="<?php esc_attr_e( 'e.g. 12 Months from manufacture', 'spicecraft-core' ); ?>" />
					</div>
				</div>

				<div class="sc-form-row">
					<div class="sc-col">
						<label for="sc_country"><strong><?php esc_html_e( 'Country of Origin', 'spicecraft-core' ); ?></strong></label>
						<input type="text" name="_sc_country_of_origin" id="sc_country" value="<?php echo esc_attr( $origin_country ); ?>" class="widefat" placeholder="<?php esc_attr_e( 'e.g. India', 'spicecraft-core' ); ?>" />
					</div>
					<div class="sc-col">
						<label for="sc_region"><strong><?php esc_html_e( 'Source Region / Valley', 'spicecraft-core' ); ?></strong></label>
						<input type="text" name="_sc_origin_region" id="sc_region" value="<?php echo esc_attr( $origin_region ); ?>" class="widefat" placeholder="<?php esc_attr_e( 'e.g. Kashmir Valley, Idukki Kerala, Saurashtra Gujarat', 'spicecraft-core' ); ?>" />
					</div>
				</div>

				<div class="sc-form-row">
					<div class="sc-col-full">
						<label><strong><?php esc_html_e( 'Manufacturing & Quality Specifications (Custom Table)', 'spicecraft-core' ); ?></strong></label>
						<p class="description"><?php esc_html_e( 'Add custom specifications rows (e.g., Moisture Content, Processing Method, Volatile Oil, Grade, Foreign Matter).', 'spicecraft-core' ); ?></p>

						<table class="widefat striped sc-repeatable-table" id="sc-specs-table">
							<thead>
								<tr>
									<th style="width: 40%;"><?php esc_html_e( 'Specification Parameter / Label', 'spicecraft-core' ); ?></th>
									<th><?php esc_html_e( 'Specification Value', 'spicecraft-core' ); ?></th>
									<th style="width: 60px; text-align: center;"><?php esc_html_e( 'Action', 'spicecraft-core' ); ?></th>
								</tr>
							</thead>
							<tbody>
								<?php if ( ! empty( $specs ) ) : ?>
									<?php foreach ( $specs as $idx => $row ) : ?>
										<tr>
											<td><input type="text" name="_sc_specifications[<?php echo esc_attr( $idx ); ?>][label]" value="<?php echo esc_attr( $row['label'] ?? '' ); ?>" class="widefat" placeholder="<?php esc_attr_e( 'e.g. Processing Method', 'spicecraft-core' ); ?>" /></td>
											<td><input type="text" name="_sc_specifications[<?php echo esc_attr( $idx ); ?>][value]" value="<?php echo esc_attr( $row['value'] ?? '' ); ?>" class="widefat" placeholder="<?php esc_attr_e( 'e.g. Cryogenic Cold Ground', 'spicecraft-core' ); ?>" /></td>
											<td style="text-align: center;"><button type="button" class="button sc-remove-row-btn">&times;</button></td>
										</tr>
									<?php endforeach; ?>
								<?php endif; ?>
							</tbody>
						</table>
						<p><button type="button" class="button button-secondary" id="sc-add-spec-btn">+ <?php esc_html_e( 'Add Specification Row', 'spicecraft-core' ); ?></button></p>
					</div>
				</div>
			</div>

			<!-- Tab 3: Ingredients, Usage & Storage -->
			<div class="sc-metabox-panel" id="tab-ingredients" style="display:none;">
				<div class="sc-form-row">
					<div class="sc-col-full">
						<label for="sc_ingredients"><strong><?php esc_html_e( 'Ingredients Declaration', 'spicecraft-core' ); ?></strong></label>
						<textarea name="_sc_ingredients" id="sc_ingredients" rows="4" class="widefat" placeholder="<?php esc_attr_e( 'e.g. 100% Pure Sun-Dried Kashmiri Red Chillies (Capsicum annuum). No artificial colors or preservatives.', 'spicecraft-core' ); ?>"><?php echo esc_textarea( $ingredients ); ?></textarea>
						<p class="description"><?php esc_html_e( 'Mandatory for blended masalas and mixed seasonings.', 'spicecraft-core' ); ?></p>
					</div>
				</div>

				<div class="sc-form-row">
					<div class="sc-col-full">
						<label for="sc_usage"><strong><?php esc_html_e( 'Usage / Culinary Suggestions', 'spicecraft-core' ); ?></strong></label>
						<textarea name="_sc_usage_instructions" id="sc_usage" rows="4" class="widefat" placeholder="<?php esc_attr_e( 'e.g. Ideal for authentic curries, tandoori marinades, and biryanis. Add during tempering or sauté with onions for brilliant natural red color.', 'spicecraft-core' ); ?>"><?php echo esc_textarea( $usage ); ?></textarea>
					</div>
				</div>

				<div class="sc-form-row">
					<div class="sc-col-full">
						<label for="sc_storage"><strong><?php esc_html_e( 'Storage Instructions', 'spicecraft-core' ); ?></strong></label>
						<textarea name="_sc_storage_instructions" id="sc_storage" rows="3" class="widefat" placeholder="<?php esc_attr_e( 'Store in a cool, dry place away from direct sunlight. Once opened, transfer to an airtight container.', 'spicecraft-core' ); ?>"><?php echo esc_textarea( $storage ); ?></textarea>
					</div>
				</div>
			</div>

			<!-- Tab 4: Nutrition Facts Table -->
			<div class="sc-metabox-panel" id="tab-nutrition" style="display:none;">
				<div class="sc-form-row">
					<div class="sc-col">
						<label for="sc_serving_size"><strong><?php esc_html_e( 'Serving Size Reference Label', 'spicecraft-core' ); ?></strong></label>
						<input type="text" name="_sc_serving_size" id="sc_serving_size" value="<?php echo esc_attr( $serving_size ); ?>" class="widefat" placeholder="<?php esc_attr_e( 'e.g. Per 100g or Serving Size: 10g', 'spicecraft-core' ); ?>" />
					</div>
					<div class="sc-col" style="display: flex; align-items: flex-end;">
						<button type="button" class="button button-secondary" id="sc-load-std-nutrition-btn">
							<?php esc_html_e( 'Pre-fill Standard Spice Nutrients', 'spicecraft-core' ); ?>
						</button>
					</div>
				</div>

				<div class="sc-form-row">
					<div class="sc-col-full">
						<table class="widefat striped sc-repeatable-table" id="sc-nutrition-table">
							<thead>
								<tr>
									<th style="width: 45%;"><?php esc_html_e( 'Nutrient Name', 'spicecraft-core' ); ?></th>
									<th style="width: 30%;"><?php esc_html_e( 'Amount / Value', 'spicecraft-core' ); ?></th>
									<th style="width: 15%;"><?php esc_html_e( 'Unit', 'spicecraft-core' ); ?></th>
									<th style="width: 10%; text-align: center;"><?php esc_html_e( 'Action', 'spicecraft-core' ); ?></th>
								</tr>
							</thead>
							<tbody>
								<?php if ( ! empty( $nutrition ) ) : ?>
									<?php foreach ( $nutrition as $idx => $row ) : ?>
										<tr>
											<td><input type="text" name="_sc_nutrition_data[<?php echo esc_attr( $idx ); ?>][nutrient]" value="<?php echo esc_attr( $row['nutrient'] ?? '' ); ?>" class="widefat" placeholder="<?php esc_attr_e( 'e.g. Energy', 'spicecraft-core' ); ?>" /></td>
											<td><input type="text" name="_sc_nutrition_data[<?php echo esc_attr( $idx ); ?>][value]" value="<?php echo esc_attr( $row['value'] ?? '' ); ?>" class="widefat" placeholder="<?php esc_attr_e( 'e.g. 350', 'spicecraft-core' ); ?>" /></td>
											<td><input type="text" name="_sc_nutrition_data[<?php echo esc_attr( $idx ); ?>][unit]" value="<?php echo esc_attr( $row['unit'] ?? '' ); ?>" class="widefat" placeholder="<?php esc_attr_e( 'kcal, g, mg', 'spicecraft-core' ); ?>" /></td>
											<td style="text-align: center;"><button type="button" class="button sc-remove-row-btn">&times;</button></td>
										</tr>
									<?php endforeach; ?>
								<?php endif; ?>
							</tbody>
						</table>
						<p><button type="button" class="button button-secondary" id="sc-add-nutrient-btn">+ <?php esc_html_e( 'Add Nutrient Row', 'spicecraft-core' ); ?></button></p>
					</div>
				</div>
			</div>

		</div><!-- .sc-metabox-wrapper -->
		<?php
	}

	/**
	 * Secure Save Product Meta
	 *
	 * @param int     $post_id Post ID.
	 * @param WP_Post $post    Post object.
	 */
	public function save_product_meta( $post_id, $post ) {
		// 1. Verify Nonce
		if ( ! isset( $_POST[ self::NONCE_FIELD ] ) || ! wp_verify_nonce( sanitize_key( $_POST[ self::NONCE_FIELD ] ), self::NONCE_ACTION ) ) {
			return;
		}

		// 2. Prevent Autosave/Revisions
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}
		if ( wp_is_post_revision( $post_id ) ) {
			return;
		}

		// 3. Verify Capabilities
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		// 4. Sanitize & Save Scalar Fields
		$text_fields = array(
			'_sc_badge_label'          => 'sanitize_text_field',
			'_sc_badge_style'          => 'sanitize_text_field',
			'_sc_pack_sizes'           => 'sanitize_text_field',
			'_sc_form'                 => 'sanitize_text_field',
			'_sc_country_of_origin'    => 'sanitize_text_field',
			'_sc_origin_region'        => 'sanitize_text_field',
			'_sc_shelf_life'           => 'sanitize_text_field',
			'_sc_serving_size'         => 'sanitize_text_field',
			'_sc_ingredients'          => 'sanitize_textarea_field',
			'_sc_usage_instructions'   => 'sanitize_textarea_field',
			'_sc_storage_instructions' => 'sanitize_textarea_field',
		);

		foreach ( $text_fields as $meta_key => $sanitize_fn ) {
			if ( isset( $_POST[ $meta_key ] ) ) {
				$val = call_user_func( $sanitize_fn, $_POST[ $meta_key ] );
				if ( '' !== $val ) {
					update_post_meta( $post_id, $meta_key, $val );
				} else {
					delete_post_meta( $post_id, $meta_key );
				}
			}
		}

		// Sync legacy badge key for backwards compatibility
		if ( isset( $_POST['_sc_badge_label'] ) ) {
			$badge_val = sanitize_text_field( $_POST['_sc_badge_label'] );
			if ( '' !== $badge_val ) {
				update_post_meta( $post_id, '_spicecraft_badge', $badge_val );
			} else {
				delete_post_meta( $post_id, '_spicecraft_badge' );
			}
		}

		// 5. Sanitize & Save Repeatable Highlights Array
		if ( isset( $_POST['_sc_highlights'] ) && is_array( $_POST['_sc_highlights'] ) ) {
			$clean_highlights = array();
			foreach ( $_POST['_sc_highlights'] as $hl ) {
				$clean_hl = sanitize_text_field( $hl );
				if ( ! empty( $clean_hl ) ) {
					$clean_highlights[] = $clean_hl;
				}
			}
			if ( ! empty( $clean_highlights ) ) {
				update_post_meta( $post_id, '_sc_highlights', $clean_highlights );
			} else {
				delete_post_meta( $post_id, '_sc_highlights' );
			}
		} else {
			delete_post_meta( $post_id, '_sc_highlights' );
		}

		// 6. Sanitize & Save Repeatable Specifications Table
		if ( isset( $_POST['_sc_specifications'] ) && is_array( $_POST['_sc_specifications'] ) ) {
			$clean_specs = array();
			foreach ( $_POST['_sc_specifications'] as $row ) {
				$label = isset( $row['label'] ) ? sanitize_text_field( $row['label'] ) : '';
				$val   = isset( $row['value'] ) ? sanitize_text_field( $row['value'] ) : '';
				if ( ! empty( $label ) && ! empty( $val ) ) {
					$clean_specs[] = array(
						'label' => $label,
						'value' => $val,
					);
				}
			}
			if ( ! empty( $clean_specs ) ) {
				update_post_meta( $post_id, '_sc_specifications', $clean_specs );
			} else {
				delete_post_meta( $post_id, '_sc_specifications' );
			}
		} else {
			delete_post_meta( $post_id, '_sc_specifications' );
		}

		// 7. Sanitize & Save Repeatable Nutrition Table
		if ( isset( $_POST['_sc_nutrition_data'] ) && is_array( $_POST['_sc_nutrition_data'] ) ) {
			$clean_nutrition = array();
			foreach ( $_POST['_sc_nutrition_data'] as $row ) {
				$nutrient = isset( $row['nutrient'] ) ? sanitize_text_field( $row['nutrient'] ) : '';
				$val      = isset( $row['value'] ) ? sanitize_text_field( $row['value'] ) : '';
				$unit     = isset( $row['unit'] ) ? sanitize_text_field( $row['unit'] ) : '';
				if ( ! empty( $nutrient ) && '' !== $val ) {
					$clean_nutrition[] = array(
						'nutrient' => $nutrient,
						'value'    => $val,
						'unit'     => $unit,
					);
				}
			}
			if ( ! empty( $clean_nutrition ) ) {
				update_post_meta( $post_id, '_sc_nutrition_data', $clean_nutrition );
			} else {
				delete_post_meta( $post_id, '_sc_nutrition_data' );
			}
		} else {
			delete_post_meta( $post_id, '_sc_nutrition_data' );
		}
	}
}
